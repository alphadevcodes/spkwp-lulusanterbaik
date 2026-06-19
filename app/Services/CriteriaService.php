<?php

namespace App\Services;

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
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $sortField = $filters['sortField'] ?? 'code';
        $sortDirection = $filters['sortDirection'] ?? 'asc';

        return Criteria::query()
            ->search($filters['search'] ?? null)
            ->category($filters['category'] ?? null)
            ->attribute($filters['attribute'] ?? null)
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
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
        return DB::transaction(fn () => Criteria::create($data));
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
     * Hapus banyak criteria sekaligus (bulk delete).
     *
     * @param  array<int, int|string>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return Criteria::query()->whereIn('id', $ids)->delete();
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
            'form.code' => ['required', 'string', 'max:20', Rule::unique('criterias', 'code')->ignore($ignoreId)],
            'form.name' => ['required', 'string', 'max:255'],
            'form.category' => ['required', Rule::in(array_column(\App\Enums\CriteriaCategory::options(), 'value'))],
            'form.attribute' => ['required', Rule::in(array_column(\App\Enums\CriteriaAttribute::options(), 'value'))],
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
