<div>
    {{-- ===== Header & Breadcrumbs ===== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">
                Perhitungan & Ranking
            </h1>

            <p class="mt-1 text-sm text-zinc-500">
                Hasil perangkingan menggunakan metode Weighted Product (WP).
            </p>
        </div>

        <div class="self-start md:self-auto">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">
                    Dashboard
                </flux:breadcrumbs.item>

                <flux:breadcrumbs.item>
                    Ranking
                </flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>
    </div>

    {{-- ===== Prasyarat tidak terpenuhi ===== --}}
    @if (! empty($errors))
    <flux:callout variant="danger" icon="exclamation-circle" class="mt-6">
        <flux:callout.heading>Perhitungan belum bisa dijalankan</flux:callout.heading>
        <flux:callout.text>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </flux:callout.text>
    </flux:callout>
    @endif

    {{-- ===== Tombol aksi ===== --}}
    <div class="mt-6 flex items-center gap-3">
        <flux:button
            wire:click="calculate"
            variant="primary"
            icon="calculator"
            :disabled="! empty($errors)">
            {{ $hasCalculated ? 'Hitung Ulang' : 'Hitung Ranking' }}
        </flux:button>

        @if ($hasCalculated)
        <flux:button wire:click="resetCalculation" variant="ghost" icon="x-mark">
            Reset
        </flux:button>
        @endif
    </div>

    @if ($hasCalculated)
    {{-- ===== Data tidak lengkap (dikecualikan) ===== --}}
    @if (! empty($excluded))
    <flux:callout variant="warning" icon="exclamation-triangle" class="mt-6">
        <flux:callout.heading>{{ count($excluded) }} alternatif dikecualikan dari perangkingan</flux:callout.heading>
        <flux:callout.text>
            Data nilai belum lengkap (terdapat nilai kosong atau 0) untuk:
            {{ collect($excluded)->pluck('studentName')->join(', ', ', dan ') }}.
            Lengkapi nilainya di halaman Alternatif, lalu hitung ulang.
        </flux:callout.text>
    </flux:callout>
    @endif

    {{-- ===== Langkah 1: Perbaikan Bobot ===== --}}
    <div class="mt-8">
        <flux:heading size="lg">Langkah 1 — Perbaikan Bobot Kriteria</flux:heading>
        <flux:subheading>
            Bobot dinormalisasi agar total = 1. Total bobot mentah saat ini: {{ $totalRawWeight }}%.
        </flux:subheading>

        <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Nama Kriteria</flux:table.column>
                    <flux:table.column>Atribut</flux:table.column>
                    <flux:table.column class="text-center">Bobot Awal</flux:table.column>
                    <flux:table.column class="text-center">Bobot Ternormalisasi</flux:table.column>
                    <flux:table.column class="text-center">Pangkat (±w)</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($weights as $w)
                    <flux:table.row :key="'weight-'.$w['criteriaId']">
                        <flux:table.cell class="font-mono text-sm">{{ $w['code'] }}</flux:table.cell>
                        <flux:table.cell>{{ $w['name'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$w['attribute']->color()">
                                {{ $w['attribute']->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-center">{{ $w['rawWeight'] }}%</flux:table.cell>
                        <flux:table.cell class="text-center">{{ number_format($w['normalizedWeight'], 4) }}</flux:table.cell>
                        <flux:table.cell class="text-center font-mono">
                            {{ $w['signedExponent'] >= 0 ? '+' : '' }}{{ number_format($w['signedExponent'], 4) }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    {{-- ===== Langkah 2 & 3: Vektor S dan V ===== --}}
    <div class="mt-8">
        <flux:heading size="lg">Langkah 2 & 3 — Vektor S dan Vektor V</flux:heading>
        <flux:subheading>
            Vektor S = perkalian nilai kriteria yang dipangkatkan bobot. Vektor V = preferensi relatif (S dibagi total S).
        </flux:subheading>

        <div class="mt-3 overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Nama Siswa</flux:table.column>
                    @foreach ($weights as $w)
                    <flux:table.column class="text-center">{{ $w['code'] }}</flux:table.column>
                    @endforeach
                    <flux:table.column class="text-center">Vektor S</flux:table.column>
                    <flux:table.column class="text-center">Vektor V</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($rankings as $r)
                    <flux:table.row :key="'vector-'.$r['alternativeId']">
                        <flux:table.cell class="font-mono text-sm">{{ $r['code'] }}</flux:table.cell>
                        <flux:table.cell>{{ $r['studentName'] }}</flux:table.cell>
                        @foreach ($weights as $w)
                        <flux:table.cell class="text-center text-zinc-600 dark:text-zinc-400">
                            {{ $r['values'][$w['criteriaId']] ?? '-' }}
                        </flux:table.cell>
                        @endforeach
                        <flux:table.cell class="text-center font-mono">{{ number_format($r['vectorS'], 6) }}</flux:table.cell>
                        <flux:table.cell class="text-center font-mono font-semibold">{{ number_format($r['vectorV'], 6) }}</flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ 4 + count($weights) }}" class="py-10 text-center text-zinc-500">
                            Tidak ada alternatif dengan data lengkap untuk dihitung.
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    {{-- ===== Hasil Akhir: Ranking ===== --}}
    <div class="mt-8 mb-8">
        <flux:heading size="lg">Hasil Akhir — Perangkingan</flux:heading>
        <flux:subheading>
            Diurutkan berdasarkan nilai Vektor V tertinggi. Nilai tertinggi adalah alternatif terbaik.
        </flux:subheading>

        <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-16 text-center">Peringkat</flux:table.column>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Nama Siswa</flux:table.column>
                    <flux:table.column class="text-center">Vektor V</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($rankings as $r)
                    <flux:table.row :key="'rank-'.$r['alternativeId']">
                        <flux:table.cell class="text-center">
                            @if ($r['rank'] === 1)
                            <flux:badge color="amber" size="lg">🏆 1</flux:badge>
                            @else
                            <span class="font-semibold">{{ $r['rank'] }}</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="font-mono text-sm">{{ $r['code'] }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $r['studentName'] }}</flux:table.cell>
                        <flux:table.cell class="text-center font-mono font-semibold">
                            {{ number_format($r['vectorV'], 6) }}
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="py-10 text-center text-zinc-500">
                            Belum ada hasil ranking.
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
    @endif
</div>