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
    public function calculate(): WpCalculationResult
    {
        /** @var Collection<int, Criteria> $criterias */
        $criterias = Criteria::query()
            ->select('id', 'code', 'name', 'attribute', 'weight')
            ->orderBy('code')
            ->get();

        $weights = $this->normalizeWeights($criterias);

        /** @var Collection<int, Alternative> $alternatives */
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
     * @param Collection<int, Criteria> $criterias
     * @return Collection<int, WpCriteriaWeight>
     */
    protected function normalizeWeights(Collection $criterias): Collection
    {
        $totalWeight = (float) $criterias->sum('weight');

        return $criterias
            ->map(
                /**
                 * @return WpCriteriaWeight
                 */
                function (Criteria $criteria) use ($totalWeight): WpCriteriaWeight {
                    $rawWeight = (int) $criteria->weight;

                    $normalized = $totalWeight > 0
                        ? $rawWeight / $totalWeight
                        : 0.0;

                    /** @var CriteriaAttribute $attribute */
                    $attribute = $criteria->attribute;

                    return new WpCriteriaWeight(
                        criteriaId: $criteria->id,
                        code: $criteria->code,
                        name: $criteria->name,
                        attribute: $attribute,
                        rawWeight: $rawWeight,
                        normalizedWeight: $normalized,
                        signedExponent: $normalized * $attribute->weightSign(),
                    );
                }
            )
            ->values();
    }

    /**
     * @param Collection<int, Alternative> $alternatives
     * @param Collection<int, WpCriteriaWeight> $weights
     * @return array{
     *     0: Collection<int, WpRankResult>,
     *     1: Collection<int, WpRankResult>
     * }
     */
    protected function calculateRankings(
        Collection $alternatives,
        Collection $weights,
    ): array {
        /**
         * @var Collection<int, array{
         *     alternative_id:int,
         *     code:string,
         *     student_name:string,
         *     values:array<int,float|null>,
         *     vectorS:float
         * }> $complete
         */
        $complete = collect();

        /** @var Collection<int, WpRankResult> $excluded */
        $excluded = collect();

        /** @var Alternative $alternative */
        foreach ($alternatives as $alternative) {
            $valuesByCriteria = $alternative->values->pluck('value', 'criteria_id');

            /** @var array<int,float|null> $values */
            $values = [];

            $vectorS = 1.0;
            $isComplete = true;

            /** @var WpCriteriaWeight $weight */
            foreach ($weights as $weight) {
                $rawValue = $valuesByCriteria[$weight->criteriaId] ?? null;

                if ($rawValue === null || $rawValue === '') {
                    $isComplete = false;
                    $values[$weight->criteriaId] = null;
                    continue;
                }

                $value = (float) $rawValue;
                $values[$weight->criteriaId] = $value;

                if ($value <= 0) {
                    $isComplete = false;
                    continue;
                }

                $vectorS *= $value ** $weight->signedExponent;
            }

            if (! $isComplete) {
                $excluded->push(
                    new WpRankResult(
                        alternativeId: $alternative->id,
                        code: $alternative->code,
                        studentName: $alternative->student_name,
                        values: $values,
                        vectorS: 0.0,
                        vectorV: 0.0,
                        rank: null,
                        isComplete: false,
                    )
                );

                continue;
            }

            $complete->push([
                'alternative_id' => $alternative->id,
                'code' => $alternative->code,
                'student_name' => $alternative->student_name,
                'values' => $values,
                'vectorS' => $vectorS,
            ]);
        }

        $totalS = (float) $complete->sum('vectorS');

        /** @var Collection<int, WpRankResult> $rankings */
        $rankings = $complete
            ->sortByDesc('vectorS')
            ->values()
            ->map(
                /**
                 * @param array{
                 *     alternative_id:int,
                 *     code:string,
                 *     student_name:string,
                 *     values:array<int,float|null>,
                 *     vectorS:float
                 * } $row
                 */
                fn (array $row, int $index): WpRankResult => new WpRankResult(
                    alternativeId: $row['alternative_id'],
                    code: $row['code'],
                    studentName: $row['student_name'],
                    values: $row['values'],
                    vectorS: $row['vectorS'],
                    vectorV: $totalS > 0
                        ? $row['vectorS'] / $totalS
                        : 0.0,
                    rank: $index + 1,
                    isComplete: true,
                )
            );

        return [$rankings, $excluded->values()];
    }

    /**
     * @return array<int, string>
     */
    public function validatePrerequisites(): array
    {
        $errors = [];

        $criteriaCount = Criteria::query()->count();
        $alternativeCount = Alternative::query()->count();
        $totalWeight = (float) Criteria::query()->sum('weight');

        if ($criteriaCount === 0) {
            $errors[] = 'Belum ada kriteria yang dibuat.';
        }

        if ($alternativeCount < 2) {
            $errors[] = 'Minimal dibutuhkan 2 alternatif untuk melakukan perangkingan.';
        }

        if ($criteriaCount > 0 && (int) $totalWeight !== 100) {
            $errors[] = "Total bobot kriteria saat ini {$totalWeight}%, harus tepat 100% sebelum perhitungan.";
        }

        return $errors;
    }
}