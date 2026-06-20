<?php

namespace App\Services;

use App\Enums\CriteriaAttribute;
use App\Models\Alternative;
use App\Models\Criteria;
use App\Services\WP\WpCalculationResult;
use App\Services\WP\WpCriteriaWeight;
use App\Services\WP\WpRankResult;
use Illuminate\Support\Collection;

class WpCalculationService
{
    /**
     * Jalankan perhitungan WP penuh: normalisasi bobot, hitung Vektor S & V,
     * lalu urutkan ranking dari nilai V tertinggi ke terendah.
     *
     * Semua criteria diikutkan dalam perhitungan tanpa memandang category
     * (Utama/Tambahan) -- category hanya label deskriptif untuk pengelompokan
     * di UI, bukan filter bisnis. Yang menentukan kontribusi tiap criteria
     * murni weight (bobot) dan attribute (benefit/cost) miliknya.
     *
     * Query yang dijalankan: 1 query criteria + 1 query alternative dengan
     * eager load values (2 query total), tidak peduli berapa banyak alternative/criteria.
     */
    public function calculate(): WpCalculationResult
    {
        $criterias = Criteria::query()
            ->select('id', 'code', 'name', 'attribute', 'weight')
            ->orderBy('code')
            ->get();

        $weights = $this->normalizeWeights($criterias);

        $alternatives = Alternative::query()
            ->select('id', 'code', 'student_name')
            ->with(['values:id,alternative_id,criteria_id,value'])
            ->orderBy('code')
            ->get();

        [$rankings, $excluded] = $this->calculateRankings($alternatives, $weights);

        return new WpCalculationResult(
            weights: $weights,
            rankings: $rankings,
            excluded: $excluded,
            totalRawWeight: (int) $criterias->sum('weight'),
        );
    }

    /**
     * Langkah 1: Perbaikan bobot — normalisasi tiap weight criteria supaya total = 1,
     * lalu hitung tanda pangkatnya (+ untuk benefit, - untuk cost).
     *
     * @param  Collection<int, Criteria>  $criterias
     * @return Collection<int, WpCriteriaWeight>
     */
    protected function normalizeWeights(Collection $criterias): Collection
    {
        $totalWeight = (int) $criterias->sum('weight');

        return $criterias->map(function (Criteria $criteria) use ($totalWeight) {
            $normalized = $totalWeight > 0 ? $criteria->weight / $totalWeight : 0.0;

            /** @var CriteriaAttribute $attribute */
            $attribute = $criteria->attribute;
            $signedExponent = $normalized * $attribute->weightSign();

            return new WpCriteriaWeight(
                criteriaId: $criteria->id,
                code: $criteria->code,
                name: $criteria->name,
                attribute: $attribute,
                rawWeight: $criteria->weight,
                normalizedWeight: $normalized,
                signedExponent: $signedExponent,
            );
        })->values();
    }

    /**
     * Langkah 2 & 3: Hitung Vektor S (perkalian nilai dipangkatkan bobot bertanda)
     * dan Vektor V (preferensi relatif) untuk setiap alternative, lalu urutkan rank.
     *
     * Alternative yang punya nilai 0 atau kosong pada SALAH SATU criteria
     * dikecualikan dari ranking (bukan dinetralkan diam-diam), karena:
     * - Pada criteria cost, 0 dipangkatkan bobot negatif scr matematis = pembagian
     *   dengan nol (undefined / infinite), tidak bisa dihitung sama sekali.
     * - Pada criteria benefit, 0 membuat seluruh Vektor S alternative tsb otomatis
     *   menjadi 0 walau nilai kriteria lain tinggi — ini biasanya menandakan data
     *   belum lengkap diisi, bukan penilaian yang valid.
     * Alternative yang dikecualikan tetap dikembalikan (rank = null, isComplete =
     * false) supaya terlihat jelas di UI, alih-alih hilang tanpa keterangan atau
     * menyebabkan exception saat runtime.
     *
     * @param  Collection<int, Alternative>  $alternatives
     * @param  Collection<int, WpCriteriaWeight>  $weights
     * @return array{0: Collection<int, WpRankResult>, 1: Collection<int, WpRankResult>}
     */
    protected function calculateRankings(Collection $alternatives, Collection $weights): array
    {
        $complete = collect();
        $excluded = collect();

        foreach ($alternatives as $alternative) {
            $valuesByCriteria = $alternative->values->pluck('value', 'criteria_id');

            $hasIncompleteValue = $weights->contains(
                fn(WpCriteriaWeight $weight) => (float) ($valuesByCriteria[$weight->criteriaId] ?? 0) <= 0
            );

            $values = $weights->mapWithKeys(
                fn(WpCriteriaWeight $weight) => [$weight->criteriaId => (float) ($valuesByCriteria[$weight->criteriaId] ?? 0)]
            )->toArray();

            if ($hasIncompleteValue) {
                $excluded->push(new WpRankResult(
                    alternativeId: $alternative->id,
                    code: $alternative->code,
                    studentName: $alternative->student_name,
                    values: $values,
                    vectorS: 0.0,
                    vectorV: 0.0,
                    rank: null,
                    isComplete: false,
                ));

                continue;
            }

            $s = $weights->reduce(
                fn(float $carry, WpCriteriaWeight $weight) => $carry * ($values[$weight->criteriaId] ** $weight->signedExponent),
                1.0
            );

            $complete->push([
                'alternative' => $alternative,
                'values' => $values,
                's' => $s,
            ]);
        }

        $totalS = $complete->sum('s');

        $ranked = $complete
            ->map(function (array $row) use ($totalS) {
                $row['v'] = $totalS > 0 ? $row['s'] / $totalS : 0.0;

                return $row;
            })
            ->sortByDesc('v')
            ->values();

        $rankings = $ranked->map(function (array $row, int $index) {
            /** @var Alternative $alternative */
            $alternative = $row['alternative'];

            return new WpRankResult(
                alternativeId: $alternative->id,
                code: $alternative->code,
                studentName: $alternative->student_name,
                values: $row['values'],
                vectorS: $row['s'],
                vectorV: $row['v'],
                rank: $index + 1,
                isComplete: true,
            );
        });

        return [$rankings, $excluded->values()];
    }

    /**
     * Validasi pra-syarat sebelum perhitungan boleh dijalankan.
     * SPK WP butuh: minimal 1 criteria, minimal 2 alternative, dan total bobot = 100.
     * Semua criteria dihitung tanpa memandang category.
     *
     * @return array<int, string> Daftar pesan error; kosong berarti valid.
     */
    public function validatePrerequisites(): array
    {
        $errors = [];

        $criteriaCount = Criteria::query()->count();
        $alternativeCount = Alternative::query()->count();
        $totalWeight = (int) Criteria::query()->sum('weight');

        if ($criteriaCount === 0) {
            $errors[] = 'Belum ada kriteria yang dibuat.';
        }

        if ($alternativeCount < 2) {
            $errors[] = 'Minimal dibutuhkan 2 alternatif untuk melakukan perangkingan.';
        }

        if ($criteriaCount > 0 && $totalWeight !== 100) {
            $errors[] = "Total bobot kriteria saat ini {$totalWeight}%, harus tepat 100% sebelum perhitungan.";
        }

        return $errors;
    }
}
