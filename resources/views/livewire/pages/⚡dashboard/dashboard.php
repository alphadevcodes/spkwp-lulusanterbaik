<?php

use Livewire\Component;
use App\Models\Criteria;
use App\Models\Alternative;
use App\Models\User;
use App\Services\WpCalculationService;
use Livewire\Attributes\Computed;

new class extends Component
{
    //
    #[Computed]
    public function criteriaCount(): int
    {
        return Criteria::count();
    }

    #[Computed]
    public function alternativeCount(): int
    {
        return Alternative::count();
    }

    #[Computed]
    public function userCount(): int
    {
        return User::count();
    }

    #[Computed]
    public function totalWeight(): int
    {
        return Criteria::sum('weight');
    }

    #[Computed]
    public function wpResult(): array
    {
        return app(WpCalculationService::class)->calculate();
    }

    #[Computed]
    public function rankings(): array
    {
        return $this->wpResult['rankings'];
    }

    #[Computed]
    public function bestAlternative(): ?array
    {
        return $this->rankings[0] ?? null;
    }

    #[Computed]
    public function topThree(): array
    {
        return array_slice($this->rankings, 0, 3);
    }

    #[Computed]
    public function calculatedCount(): int
    {
        return count($this->rankings);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
};