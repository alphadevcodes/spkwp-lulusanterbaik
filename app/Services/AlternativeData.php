<?php

namespace App\Services\Alternative;

readonly class AlternativeData
{
    /**
     * @param  array<string, string>  $values  [criteria_code => value]
     */
    public function __construct(
        public int $id,
        public string $code,
        public string $studentName,
        public array $values,
    ) {}
}