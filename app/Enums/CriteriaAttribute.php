<?php

namespace App\Enums;

enum CriteriaAttribute: string
{
    case BENEFIT = 'benefit';
    case COST = 'cost';

    public function label(): string
    {
        return match ($this) {
            self::BENEFIT => 'Benefit',
            self::COST => 'Cost',
        };
    }

    /**
     * Warna badge Flux UI sesuai tipe atribut.
     */
    public function color(): string
    {
        return match ($this) {
            self::BENEFIT => 'green',
            self::COST => 'red',
        };
    }

    /**
     * Untuk dipakai di flux:select / flux:radio sebagai options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    /**
     * Formula normalisasi standar SPK (mis. metode SAW).
     *
     * Benefit: nilai dibagi nilai maksimum kolom (semakin besar semakin baik).
     * Cost: nilai minimum kolom dibagi nilai (semakin kecil semakin baik).
     *
     * @param  float  $value     Nilai aktual alternatif pada kriteria ini.
     * @param  float  $max       Nilai maksimum di kolom kriteria ini (dipakai utk benefit).
     * @param  float  $min       Nilai minimum di kolom kriteria ini (dipakai utk cost).
     */
    public function normalize(float $value, float $max, float $min): float
    {
        return match ($this) {
            self::BENEFIT => $max > 0 ? $value / $max : 0.0,
            self::COST => $value > 0 ? $min / $value : 0.0,
        };
    }

    /**
     * Tanda pangkat bobot untuk metode Weighted Product (WP).
     *
     * Kriteria benefit dipangkatkan dengan bobot POSITIF (+wj),
     * kriteria cost dipangkatkan dengan bobot NEGATIF (-wj),
     * sehingga nilai cost yang besar otomatis memperkecil hasil perkalian Vektor S.
     */
    public function weightSign(): int
    {
        return match ($this) {
            self::BENEFIT => 1,
            self::COST => -1,
        };
    }
}
