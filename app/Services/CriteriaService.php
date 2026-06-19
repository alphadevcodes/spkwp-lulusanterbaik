<?php

namespace App\Services;

use App\Enums\CriteriaAttribute;
use App\Enums\CriteriaCategory;
use App\Models\Criteria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CriteriaService
{
    /**
     * Ambil daftar criteria dengan search, filter, sort, dan pagination.
     *
     * @param  array{search?: string|null, category?: string|null, attribute?: string|null, sortField?: string, sortDirection?: string}  $filters
     * @return LengthAwarePaginator<int, Criteria>
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $sortField = $filters['sortField'] ?? 'code';
        $sortDirection = $this->normalizeSortDirection($filters['sortDirection'] ?? 'asc');

        return Criteria::query()
            ->search($filters['search'] ?? null)
            ->category($filters['category'] ?? null)
            ->attribute($filters['attribute'] ?? null)
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
     * Total bobot seluruh criteria (berguna untuk validasi total weight = 100 di SPK).
     */
    public function totalWeight(): int
    {
        return (int) Criteria::query()->sum('weight');
    }

    /**
     * Simpan criteria baru.
     *
     * @param  array{code: string, name: string, category: string, attribute: string, weight: int}  $data
     */
    public function create(array $data): Criteria
    {
        return DB::transaction(fn() => Criteria::create($data));
    }

    /**
     * Update criteria yang sudah ada.
     *
     * @param  array{code: string, name: string, category: string, attribute: string, weight: int}  $data
     */
    public function update(Criteria $criteria, array $data): Criteria
    {
        return DB::transaction(function () use ($criteria, $data) {
            $criteria->update($data);

            return $criteria->fresh();
        });
    }

    /**
     * Hapus satu criteria.
     */
    public function delete(Criteria $criteria): bool
    {
        return (bool) $criteria->delete();
    }


    /**
     * Validation rules untuk dipakai di Livewire component (create & update).
     * Dipusatkan di sini supaya tidak duplikasi antara komponen list/modal.
     *
     * @return array<string, mixed>
     */
    public function rules(?int $ignoreId = null): array
    {
        return [
            'form.code' => ['required', 'string', 'max:20', Rule::unique(Criteria::class, 'code')->ignore($ignoreId)],
            'form.name' => ['required', 'string', 'max:255'],
            'form.category' => ['required', Rule::enum(CriteriaCategory::class)],
            'form.attribute' => ['required', Rule::enum(CriteriaAttribute::class)],
            'form.weight' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * Custom validation attribute names (label Bahasa Indonesia untuk pesan error).
     *
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'form.code' => 'kode',
            'form.name' => 'nama',
            'form.category' => 'kategori',
            'form.attribute' => 'atribut',
            'form.weight' => 'bobot',
        ];
    }
}
