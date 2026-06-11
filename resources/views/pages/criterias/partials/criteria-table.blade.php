<flux:table :paginate="$this->criteria">

    <flux:table.columns>

        <flux:table.column
            sortable
            :sorted="$sortBy === 'code'"
            :direction="$sortDirection"
            wire:click="sort('code')"
        >
            Code
        </flux:table.column>

        <flux:table.column
            sortable
            :sorted="$sortBy === 'name'"
            :direction="$sortDirection"
            wire:click="sort('name')"
        >
            Criteria
        </flux:table.column>

        <flux:table.column
            sortable
            :sorted="$sortBy === 'category'"
            :direction="$sortDirection"
            wire:click="sort('category')"
        >
            Category
        </flux:table.column>

        <flux:table.column
            sortable
            :sorted="$sortBy === 'attribute'"
            :direction="$sortDirection"
            wire:click="sort('attribute')"
        >
            Attribute
        </flux:table.column>

        <flux:table.column
            sortable
            :sorted="$sortBy === 'weight'"
            :direction="$sortDirection"
            wire:click="sort('weight')"
        >
            Weight
        </flux:table.column>

        <flux:table.column>
            Actions
        </flux:table.column>

    </flux:table.columns>

    <flux:table.rows>

        @foreach($this->criteria as $criterion)

            <flux:table.row :key="$criterion->id">

                <flux:table.cell variant="strong">
                    {{ $criterion->code }}
                </flux:table.cell>

                <flux:table.cell>
                    {{ $criterion->name }}
                </flux:table.cell>

                <flux:table.cell>
                    <flux:badge size="sm" color="zinc">
                        {{ ucfirst($criterion->category) }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell>
                    <flux:badge
                        size="sm"
                        :color="$criterion->attribute === 'benefit'
                            ? 'green'
                            : 'red'"
                    >
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
                            wire:click="edit({{ $criterion->id }})"
                        />

                        <flux:button
                            size="sm"
                            variant="danger"
                            icon="trash"
                            wire:click="delete({{ $criterion->id }})"
                            wire:confirm="Delete this criterion?"
                        />

                    </div>

                </flux:table.cell>

            </flux:table.row>

        @endforeach

    </flux:table.rows>

</flux:table>