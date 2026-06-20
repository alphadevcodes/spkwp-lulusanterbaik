<?php

namespace App\Services\WP;

use Illuminate\Support\Collection;

readonly class WpCalculationResult
{
    /**
     * @param  Collection<int, WpCriteriaWeight>  $weights
     * @param  Collection<int, WpRankResult>  $rankings  Hanya alternative lengkap, terurut V tertinggi -> terendah.
     * @param  Collection<int, WpRankResult>  $excluded  Alternative yang dikecualikan karena data tidak lengkap (rank = null).
     */
    public function __construct(
        public Collection $weights,
        public Collection $rankings,
        public Collection $excluded,
        public int $totalRawWeight,
    ) {}
}