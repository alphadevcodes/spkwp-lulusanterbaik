<?php

namespace App\Services\WP;

use App\Enums\CriteriaAttribute;

readonly class WpCriteriaWeight
{
    public function __construct(
        public int $criteriaId,
        public string $code,
        public string $name,
        public CriteriaAttribute $attribute,
        public int $rawWeight,
        public float $normalizedWeight,
        public float $signedExponent,
    ) {}
}