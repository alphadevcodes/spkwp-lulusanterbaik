<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Alternative;

/**
 * Representasi alternative (siswa) yang sudah "flat" — tidak lagi
 * bergantung ke relasi Eloquent mentah, supaya Blade & service
 * perhitungan tidak perlu tahu soal pivot/model.
 */
readonly class AlternativeData
{
    /**
     * @param  array<int, float>  $values  Nilai mentah, keyed by criteria_id.
     */
    public function __construct(
        public int $id,
        public string $code,
        public string $studentName,
        public array $values,
    ) {}

    /**
     * Butuh relasi `values` sudah di-eager-load (lihat AlternativeService::all()),
     * supaya tidak N+1 saat dipanggil per alternative.
     */
    public static function fromModel(Alternative $alternative): self
    {
        return new self(
            id: $alternative->id,
            code: $alternative->code,
            studentName: $alternative->student_name,
            values: $alternative->values
                ->mapWithKeys(fn ($value) => [
                    $value->criteria_id => (float) $value->value,
                ])
                ->all(),
        );
    }

    public function valueFor(int $criteriaId): ?float
    {
        return $this->values[$criteriaId] ?? null;
    }
}