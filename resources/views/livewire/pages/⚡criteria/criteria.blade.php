<div>
    {{-- ===== Header & Breadcrumbs ===== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">
                Management Criteria
            </h1>

            <p class="mt-1 text-sm text-zinc-500">
                Monitor all criteria.
            </p>
        </div>

        <div class="self-start md:self-auto">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">
                    Dashboard
                </flux:breadcrumbs.item>

                <flux:breadcrumbs.item>
                    Criteria
                </flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>
    </div>

    {{-- ===== Toolbar: search, filter, total weight, add button ===== --}}
    <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.400ms="search"
                placeholder="Cari kode atau nama kriteria..."
                icon="magnifying-glass"
                class="sm:max-w-xs" />

            <flux:select wire:model.live="category" placeholder="Semua Kategori" class="sm:max-w-[160px]">
                <flux:select.option value="">Semua Kategori</flux:select.option>
                @foreach ($this->categoryOptions as $option)
                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="attribute" placeholder="Semua Atribut" class="sm:max-w-[160px]">
                <flux:select.option value="">Semua Atribut</flux:select.option>
                @foreach ($this->attributeOptions as $option)
                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                @endforeach
            </flux:select>

            @if ($search || $category || $attribute)
            <flux:button wire:click="resetFilters" variant="ghost" size="sm" icon="x-mark">
                Reset
            </flux:button>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <flux:badge :color="$this->totalWeight === 100 ? 'green' : 'amber'" size="lg">
                Total Bobot: {{ $this->totalWeight }}%
            </flux:badge>

            <flux:button wire:click="create" variant="primary" icon="plus">
                Tambah Kriteria
            </flux:button>
        </div>
    </div>

    {{-- ===== Table ===== --}}
    <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'code'"
                    :direction="$sortDirection"
                    wire:click="sortBy('code')">
                    Kode
                </flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'name'"
                    :direction="$sortDirection"
                    wire:click="sortBy('name')">
                    Nama
                </flux:table.column>
                <flux:table.column>Kategori</flux:table.column>
                <flux:table.column>Atribut</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'weight'"
                    :direction="$sortDirection"
                    wire:click="sortBy('weight')">
                    Bobot
                </flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($criterias as $criteria)
                <flux:table.row :key="$criteria->id" wire:loading.class.delay="opacity-50">
                    <flux:table.cell class="font-mono text-sm">{{ $criteria->code }}</flux:table.cell>
                    <flux:table.cell>{{ $criteria->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$criteria->category === 'utama' ? 'blue' : 'zinc'">
                            {{ ucfirst($criteria->category) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$criteria->attribute->value === 'benefit' ? 'green' : 'red'">
                            {{ ucfirst($criteria->attribute->value) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $criteria->weight }}%</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button
                                wire:click="edit({{ $criteria->id }})"
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                                inset />
                            <flux:button
                                wire:click="confirmDelete({{ $criteria->id }})"
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                inset />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-10 text-center text-zinc-500">
                        Tidak ada data kriteria.
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- ===== Pagination ===== --}}
    <div class="mt-4">
        {{ $criterias->links() }}
    </div>

    {{-- ===== Modal: Create / Edit ===== --}}
    <flux:modal wire:model.self="showModal" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $isEditing ? 'Edit Kriteria' : 'Tambah Kriteria' }}
                </flux:heading>
                <flux:subheading>
                    {{ $isEditing ? 'Perbarui detail kriteria di bawah ini.' : 'Lengkapi detail kriteria baru.' }}
                </flux:subheading>
            </div>

            <flux:input
                wire:model="form.code"
                label="Kode"
                placeholder="C1"
                maxlength="20" />

            <flux:input
                wire:model="form.name"
                label="Nama Kriteria"
                placeholder="Misal: Harga, Kualitas, Jarak" />

            <flux:select wire:model="form.category" label="Kategori">
                @foreach ($this->categoryOptions as $option)
                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="form.attribute" label="Atribut">
                @foreach ($this->attributeOptions as $option)
                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model="form.weight"
                label="Bobot (%)"
                type="number"
                min="0"
                max="100" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showModal', false)">
                    Batal
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Kriteria' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ===== Modal: Konfirmasi Hapus ===== --}}
    <flux:modal wire:model.self="showDeleteModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Kriteria?</flux:heading>
                <flux:subheading>
                    Tindakan ini tidak dapat dibatalkan. Kriteria akan dihapus secara permanen.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">
                    Batal
                </flux:button>
                <flux:button wire:click="delete" variant="danger">
                    Hapus
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>