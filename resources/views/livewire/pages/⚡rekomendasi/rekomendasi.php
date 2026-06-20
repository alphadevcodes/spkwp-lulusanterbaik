<?php

use App\Services\WpCalculationService;
use Livewire\Attributes\Title;
use Livewire\Component;

new
    #[Title('Perhitungan & Ranking (WP)')]
    class extends Component
    {
        public bool $hasCalculated = false;

        public ?array $errors = null;

        /**
         * Hasil kalkulasi disimpan di property biasa (bukan computed), supaya
         * perhitungan HANYA jalan saat tombol "Hitung" ditekan -- bukan setiap
         * kali komponen re-render. Ini penting karena WP melibatkan operasi
         * pangkat/perkalian yang sebaiknya tidak dijalankan ulang tanpa perlu.
         */
        public ?array $weights = null;

        public ?array $rankings = null;

        public ?array $excluded = null;

        public int $totalRawWeight = 0;

        public function mount(WpCalculationService $service): void
        {
            $this->errors = $service->validatePrerequisites();
        }

        public function calculate(WpCalculationService $service): void
        {
            $this->errors = $service->validatePrerequisites();

            if (! empty($this->errors)) {
                return;
            }

            $result = $service->calculate();

            // Convert ke array murni supaya gampang dipakai di Blade tanpa
            // perlu re-hydrate readonly object lewat Livewire's wire snapshot.
            $this->weights = $result->weights->map(fn($w) => [
                'criteriaId' => $w->criteriaId,
                'code' => $w->code,
                'name' => $w->name,
                'attribute' => $w->attribute,
                'rawWeight' => $w->rawWeight,
                'normalizedWeight' => $w->normalizedWeight,
                'signedExponent' => $w->signedExponent,
            ])->all();

            $this->rankings = $result->rankings->map(fn($r) => [
                'alternativeId' => $r->alternativeId,
                'code' => $r->code,
                'studentName' => $r->studentName,
                'values' => $r->values,
                'vectorS' => $r->vectorS,
                'vectorV' => $r->vectorV,
                'rank' => $r->rank,
            ])->all();

            $this->excluded = $result->excluded->map(fn($r) => [
                'alternativeId' => $r->alternativeId,
                'code' => $r->code,
                'studentName' => $r->studentName,
                'values' => $r->values,
            ])->all();

            $this->totalRawWeight = $result->totalRawWeight;
            $this->hasCalculated = true;
        }

        public function resetCalculation(): void
        {
            $this->hasCalculated = false;
            $this->weights = null;
            $this->rankings = null;
            $this->excluded = null;
        }
    };
