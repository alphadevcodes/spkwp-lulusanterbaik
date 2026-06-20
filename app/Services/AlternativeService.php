<?php

namespace App\Services;

use App\Models\Alternative;
use App\Models\Criteria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlternativeService
{
    /**
     * Ambil daftar alternative dengan search dan pagination, beserta nilai
     * per criteria (eager loaded, bukan N+1).
     *
     * @param  array{search?: string|null, sortField?: string, sortDirection?: string}  $filters
     * @return LengthAwarePaginator<int, Alternative>
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $sortField = $filters['sortField'] ?? 'code';
        $sortDirection = $this->normalizeSortDirection($filters['sortDirection'] ?? 'asc');

        return Alternative::query()
            ->search($filters['search'] ?? null)
            ->with(['values:id,alternative_id,criteria_id,value'])
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * @return 'asc'|'desc'
     */
    private function normalizeSortDirection(string $direction): string
    {
        return $direction === 'desc' ? 'desc' : 'asc';
    }

    /**
     * Daftar criteria aktif untuk dipakai sebagai header kolom tabel & field form nilai.
     * Dipanggil sekali per request lewat #[Computed] di komponen, bukan di dalam loop.
     *
     * @return \Illuminate\Support\Collection<int, Criteria>
     */
    public function criteriaColumns(): \Illuminate\Support\Collection
    {
        return Criteria::query()
            ->select('id', 'code', 'name', 'weight', 'attribute')
            ->orderBy('code')
            ->get();
    }

    /**
     * Ambil nilai-nilai milik satu alternative, di-index by criteria_id,
     * untuk dipakai mengisi form.values saat edit.
     *
     * @return array<int, string>
     */
    public function valuesForForm(Alternative $alternative): array
    {
        return $alternative->values()
            ->get(['criteria_id', 'value'])
            ->pluck('value', 'criteria_id')
            ->map(fn ($v) => (string) $v)
            ->toArray();
    }

    /**
     * Simpan alternative baru beserta nilai-nilainya dalam satu transaksi.
     *
     * @param  array{code: string, student_name: string}  $data
     * @param  array<int|string, mixed>  $values  [criteria_id => value]
     */
    public function create(array $data, array $values = []): Alternative
    {
        return DB::transaction(function () use ($data, $values) {
            $alternative = Alternative::create($data);

            $this->syncValues($alternative, $values);

            return $alternative;
        });
    }

    /**
     * Update alternative beserta nilai-nilainya dalam satu transaksi.
     *
     * @param  array{code: string, student_name: string}  $data
     * @param  array<int|string, mixed>  $values  [criteria_id => value]
     */
    public function update(Alternative $alternative, array $data, array $values = []): Alternative
    {
        return DB::transaction(function () use ($alternative, $data, $values) {
            $alternative->update($data);

            $this->syncValues($alternative, $values);

            return $alternative->fresh();
        });
    }

    /**
     * Hapus satu alternative. Baris alternative_values terkait ikut terhapus
     * otomatis lewat cascadeOnDelete() di foreign key, jadi tidak ada baris yatim.
     */
    public function delete(Alternative $alternative): bool
    {
        return (bool) $alternative->delete();
    }

    /**
     * Sinkronkan SELURUH nilai criteria milik satu alternative dengan apa yang
     * dikirim dari form, dalam satu operasi DB per arah (bukan loop query):
     *
     * 1. UPSERT baris yang ada nilainya di $values (insert baru / update yang sudah ada).
     * 2. DELETE baris lama yang criteria_id-nya TIDAK ada lagi di $values, supaya
     *    tidak ada baris pivot "nyangkut" untuk criteria yang sudah dikosongkan/
     *    dihapus dari form. Ini yang menjaga alternative_values selalu sinkron
     *    persis dengan apa yang user maksud, bukan hanya additive.
     *
     * @param  array<int|string, mixed>  $values  [criteria_id => value]
     */
    protected function syncValues(Alternative $alternative, array $values): void
    {
        // Buang entri kosong/null supaya tidak nyimpan baris dengan value kosong,
        // dan supaya criteria yang sengaja dikosongkan ikut ter-delete di langkah 2.
        $filtered = collect($values)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->mapWithKeys(fn ($value, $criteriaId) => [(int) $criteriaId => $value]);

        $keptCriteriaIds = $filtered->keys()->all();

        if ($filtered->isNotEmpty()) {
            $rows = $filtered
                ->map(fn ($value, $criteriaId) => [
                    'alternative_id' => $alternative->id,
                    'criteria_id' => $criteriaId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->values()
                ->all();

            // 1 query upsert untuk semua nilai sekaligus.
            DB::table('alternative_values')->upsert(
                $rows,
                uniqueBy: ['alternative_id', 'criteria_id'],
                update: ['value', 'updated_at']
            );
        }

        // Hapus baris lama yang criteria_id-nya tidak lagi dikirim dari form,
        // supaya alternative_values selalu mencerminkan persis data form saat ini.
        DB::table('alternative_values')
            ->where('alternative_id', $alternative->id)
            ->when(
                ! empty($keptCriteriaIds),
                fn ($query) => $query->whereNotIn('criteria_id', $keptCriteriaIds),
            )
            ->delete();
    }

    /**
     * Validation rules untuk dipakai di Livewire component (create & update).
     * Nilai per criteria divalidasi dinamis sesuai criteria yang aktif saat ini,
     * supaya tidak ada criteria_id liar yang lolos dari form.
     *
     * @return array<string, mixed>
     */
    public function rules(?int $ignoreId = null): array
    {
        $rules = [
            'form.code' => ['required', 'string', 'max:50', Rule::unique(Alternative::class, 'code')->ignore($ignoreId)],
            'form.student_name' => ['required', 'string', 'max:150'],
            'form.values' => ['array'],
        ];

        foreach ($this->criteriaColumns() as $criteria) {
            $rules["form.values.{$criteria->id}"] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    /**
     * Custom validation attribute names (label Bahasa Indonesia untuk pesan error).
     *
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        $attributes = [
            'form.code' => 'kode',
            'form.student_name' => 'nama siswa',
        ];

        foreach ($this->criteriaColumns() as $criteria) {
            $attributes["form.values.{$criteria->id}"] = $criteria->name;
        }

        return $attributes;
    }

    /**
     * Cek apakah ada alternative yang belum lengkap nilainya untuk seluruh criteria
     * aktif saat ini. Berguna sebagai peringatan sebelum perhitungan SPK dijalankan,
     * karena SAW/WP/TOPSIS butuh matriks lengkap (tidak boleh ada sel kosong).
     */
    public function hasIncompleteValues(): bool
    {
        $criteriaCount = Criteria::query()->count();

        if ($criteriaCount === 0) {
            return false;
        }

        return Alternative::query()
            ->withCount('values')
            ->get()
            ->contains(fn (Alternative $alt) => $alt->values_count < $criteriaCount);
    }
}
