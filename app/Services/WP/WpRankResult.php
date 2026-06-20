<?php

namespace App\Services\WP;

readonly class WpRankResult
{
    /**
     * @param  array<int, float>  $values  Nilai mentah alternative ini per criteria_id, sebelum dipangkatkan.
     */
    public function __construct(
        public int $alternativeId,
        public string $code,
        public string $studentName,
        public array $values,
        public float $vectorS,
        public float $vectorV,
        public ?int $rank,
        public bool $isComplete,
    ) {}
}