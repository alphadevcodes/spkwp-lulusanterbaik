<?php

use App\Models\Criteria;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
    #[Title('All Criteria')]
    class extends Component
    {
        use WithPagination;
        public string $sortBy = 'created_at';
        public string $sortDirection = 'desc';
        public function sort(string $column): void
        {
            if ($this->sortBy === $column) {
                $this->sortDirection =
                    $this->sortDirection === 'asc'
                    ? 'desc'
                    : 'asc';
                return;
            }
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        public function getCriteriaProperty()
        {
            return Criteria::query()
                ->orderBy($this->sortBy, $this->sortDirection)
                ->paginate(5);
        }
    };
?>

<div class="min-h-screen bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumb --}}
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>
                Dashboard
            </flux:breadcrumbs.item>
            @if(request()->routeIs('criteria'))
            <flux:breadcrumbs.item>
                Criteria
            </flux:breadcrumbs.item>
            @endif
        </flux:breadcrumbs>

        <!-- Page Header -->
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900">
                    Criteria
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage evaluation criteria and their weighting.
                </p>
            </div>
            <!-- Future Action -->
            <div class="flex items-center gap-2">
            </div>
        </div>

        <flux:table :paginate="$this->criteria">

            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'code'"
                    :direction="$sortDirection"
                    wire:click="sort('code')">
                    Code
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'name'"
                    :direction="$sortDirection"
                    wire:click="sort('name')">
                    Criteria
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'category'"
                    :direction="$sortDirection"
                    wire:click="sort('category')">
                    Category
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'attribute'"
                    :direction="$sortDirection"
                    wire:click="sort('attribute')">
                    Attribute
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'weight'"
                    :direction="$sortDirection"
                    wire:click="sort('weight')">
                    Weight
                </flux:table.column>

                <flux:table.column>
                    Actions
                </flux:table.column>

            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->criteria as $criterion)
                <flux:table.row :key="$criterion->id">
                    <flux:table.cell variant="strong">
                        {{ $criterion->code }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $criterion->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            color="zinc">
                            {{ ucfirst($criterion->category) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            :color="$criterion->attribute === 'benefit'
                            ? 'green'
                            : 'red'">
                            {{ ucfirst($criterion->attribute) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ number_format($criterion->weight, 2) }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="pencil-square"
                                wire:click="edit({{ $criterion->id }})" />
                            <flux:button
                                size="sm"
                                variant="danger"
                                icon="trash"
                                wire:click="delete({{ $criterion->id }})"
                                wire:confirm="Delete this criterion?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>