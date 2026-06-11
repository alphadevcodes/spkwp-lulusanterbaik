<div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">

    <div>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-900">
            Criteria
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Manage evaluation criteria and their weighting.
        </p>
    </div>

    <div class="flex items-center gap-2">

        <flux:button
            variant="primary"
            icon="plus"
            wire:click="create"
        >
            Add Criteria
        </flux:button>

    </div>

</div>