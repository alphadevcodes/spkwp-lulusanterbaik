<div>
    {{-- ===== Header & Breadcrumbs ===== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">
                Management Alternatif
            </h1>

            <p class="mt-1 text-sm text-zinc-500">
                Data siswa beserta nilai per kriteria.
            </p>
        </div>

        <div class="self-start md:self-auto">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">
                    Dashboard
                </flux:breadcrumbs.item>

                <flux:breadcrumbs.item>
                    Alternatif
                </flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>
    </div>

    {{-- ===== Peringatan: nilai belum lengkap ===== --}}
    @if ($this->hasIncompleteValues)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mt-6">
            <flux:callout.heading>Ada alternatif yang belum lengkap nilainya</flux:callout.heading>
            <flux:callout.text>
                Pastikan setiap alternatif memiliki nilai untuk seluruh kriteria sebelum melakukan perhitungan SPK.
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- ===== Toolbar: search, add button ===== --}}
    <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.400ms="search"
                placeholder="Cari kode atau nama siswa..."
                icon="magnifying-glass"
                class="sm:max-w-xs"
            />

            @if ($search)
                <flux:button wire:click="resetFilters" variant="ghost" size="sm" icon="x-mark">
                    Reset
                </flux:button>
            @endif
        </div>

        <flux:button wire:click="create" variant="primary" icon="plus">
            Tambah Alternatif
        </flux:button>
    </div>

    {{-- ===== Table ===== --}}
    <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'code'"
                    :direction="$sortDirection"
                    wire:click="sortBy('code')"
                >
                    Kode
                </flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'student_name'"
                    :direction="$sortDirection"
                    wire:click="sortBy('student_name')"
                >
                    Nama Siswa
                </flux:table.column>
                @foreach ($this->criteriaColumns as $criteria)
                    <flux:table.column class="text-center">
                        {{ $criteria->code }}
                    </flux:table.column>
                @endforeach
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($alternatives as $alternative)
                    @php
                        $valuesByCriteria = $alternative->values->pluck('value', 'criteria_id');
                    @endphp
                    <flux:table.row :key="$alternative->id" wire:loading.class.delay="opacity-50">
                        <flux:table.cell class="font-mono text-sm">{{ $alternative->code }}</flux:table.cell>
                        <flux:table.cell>{{ $alternative->student_name }}</flux:table.cell>
                        @foreach ($this->criteriaColumns as $criteria)
                            <flux:table.cell class="text-center text-zinc-600 dark:text-zinc-400">
                                {{ $valuesByCriteria[$criteria->id] ?? '-' }}
                            </flux:table.cell>
                        @endforeach
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button
                                    wire:click="edit({{ $alternative->id }})"
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil"
                                    inset
                                />
                                <flux:button
                                    wire:click="confirmDelete({{ $alternative->id }})"
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    inset
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ 3 + $this->criteriaColumns->count() }}" class="py-10 text-center text-zinc-500">
                            Tidak ada data alternatif.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- ===== Pagination ===== --}}
    <div class="mt-4">
        {{ $alternatives->links() }}
    </div>

    {{-- ===== Modal: Create / Edit ===== --}}
    <flux:modal wire:model.self="showModal" class="md:w-[28rem]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $isEditing ? 'Edit Alternatif' : 'Tambah Alternatif' }}
                </flux:heading>
                <flux:subheading>
                    {{ $isEditing ? 'Perbarui detail alternatif di bawah ini.' : 'Lengkapi detail alternatif baru.' }}
                </flux:subheading>
            </div>

            <flux:input
                wire:model="form.code"
                label="Kode"
                placeholder="A1"
                maxlength="50"
            />

            <flux:input
                wire:model="form.student_name"
                label="Nama Siswa"
                placeholder="Misal: Budi Santoso"
            />

            <flux:separator />

            <flux:subheading>Nilai per Kriteria</flux:subheading>

            <div class="space-y-4">
                @foreach ($this->criteriaColumns as $criteria)
                    <flux:input
                        wire:model="form.values.{{ $criteria->id }}"
                        label="{{ $criteria->name }} ({{ $criteria->code }})"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                @endforeach
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showModal', false)">
                    Batal
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Alternatif' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ===== Modal: Konfirmasi Hapus ===== --}}
    <flux:modal wire:model.self="showDeleteModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Alternatif?</flux:heading>
                <flux:subheading>
                    Tindakan ini tidak dapat dibatalkan. Alternatif beserta seluruh nilainya akan dihapus secara permanen.
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
