<div>
    {{-- ===== Header & Breadcrumbs ===== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">
                Perangkingan Siswa
            </h1>
            <p class="mt-1 text-sm text-zinc-500">
                Sistem akan membandingkan nilai setiap siswa untuk menentukan siapa yang terbaik.
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

    {{-- ===== Penjelasan singkat cara kerja, bahasa awam ===== --}}
    <flux:callout variant="secondary" icon="light-bulb" class="mt-6">
        <flux:callout.heading>Bagaimana cara kerjanya?</flux:callout.heading>
        <flux:callout.text>
            Setiap kriteria (misalnya nilai rapor dan nilai ujian) punya tingkat kepentingan (bobot) yang berbeda.
            Sistem akan menggabungkan seluruh nilai siswa sesuai bobot tersebut, lalu menghasilkan satu skor akhir.
            Siswa dengan skor tertinggi adalah yang terbaik.
        </flux:callout.text>
    </flux:callout>

    {{-- ===== Prasyarat tidak terpenuhi ===== --}}
    @if (! empty($errors))
    <flux:callout variant="danger" icon="exclamation-circle" class="mt-6">
        <flux:callout.heading>Belum bisa dihitung</flux:callout.heading>
        <flux:callout.text>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </flux:callout.text>
    </flux:callout>
    @endif

    {{-- =====================================================
         PREVIEW DATA -- ringan, bukan tabel angka teknis
         ===================================================== --}}

    {{-- ===== Kriteria Penilaian ===== --}}
    <div class="mt-8">
        <flux:heading size="lg">Kriteria Penilaian</flux:heading>
        <flux:subheading>
            Aspek yang dinilai, beserta seberapa besar pengaruhnya terhadap skor akhir.
        </flux:subheading>

        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->criteria as $c)
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-semibold">{{ $c->name }}</span>
                    <flux:badge size="sm" :color="$c->attribute->color()">
                        {{ $c->attribute->label() }}
                    </flux:badge>
                </div>

                <p class="mt-1 text-xs text-zinc-500">
                    {{ $c->attribute->value === 'benefit'
                            ? '🔺 Semakin tinggi nilainya, semakin baik'
                            : '🔻 Semakin rendah nilainya, semakin baik' }}
                </p>

                <div class="mt-3 flex items-center gap-2">
                    <div class="h-2 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                        <div class="h-full bg-blue-500" style="width: {{ $c->weight }}%"></div>
                    </div>
                    <span class="text-xs font-medium text-zinc-500 w-12 text-right">{{ $c->weight }}%</span>
                </div>
            </div>
            @empty
            <p class="col-span-full py-8 text-center text-zinc-500">Belum ada data kriteria.</p>
            @endforelse
        </div>
    </div>

    {{-- ===== Data Nilai Siswa ===== --}}
    <div class="mt-8">
        <flux:heading size="lg">Data Nilai Siswa</flux:heading>
        <flux:subheading>
            Nilai yang sudah diinput untuk setiap siswa pada masing-masing kriteria.
        </flux:subheading>

        <div class="mt-3 overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Nama Siswa</flux:table.column>
                    @foreach ($this->criteria as $c)
                    <flux:table.column class="text-center">{{ $c->name }}</flux:table.column>
                    @endforeach
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->alternatives as $alt)
                    <flux:table.row :key="'alternative-'.$alt->id">
                        <flux:table.cell class="font-mono text-sm">{{ $alt->code }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $alt->student_name }}</flux:table.cell>
                        @foreach ($this->criteria as $c)
                        @php
                        $value = $alt->criterias->firstWhere('id', $c->id)?->pivot->value;
                        @endphp
                        <flux:table.cell class="text-center">
                            @if ($value !== null)
                            {{ $value }}
                            @else
                            <span class="text-zinc-400">Belum diisi</span>
                            @endif
                        </flux:table.cell>
                        @endforeach
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ 2 + count($this->criteria) }}" class="py-10 text-center text-zinc-500">
                            Belum ada data siswa.
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    {{-- ===== Tombol aksi ===== --}}
    <div class="mt-6 flex items-center gap-3">
        <flux:button
            wire:click="calculate"
            variant="primary"
            icon="calculator"
            :disabled="! empty($errors)">
            {{ $hasCalculated ? 'Hitung Ulang' : 'Hitung & Lihat Ranking' }}
        </flux:button>

        @if ($hasCalculated)
        <flux:button wire:click="resetCalculation" variant="ghost" icon="x-mark">
            Reset
        </flux:button>
        @endif
    </div>

    {{-- =====================================================
         HASIL -- fokus ke pemenang, bukan angka teknis
         ===================================================== --}}
    @if ($hasCalculated)
    {{-- ===== Data tidak lengkap (dikecualikan) ===== --}}
    @if (! empty($excluded))
    <flux:callout variant="warning" icon="exclamation-triangle" class="mt-8">
        <flux:callout.heading>{{ count($excluded) }} siswa belum bisa dirangking</flux:callout.heading>
        <flux:callout.text>
            Nilai belum lengkap untuk:
            {{ collect($excluded)->pluck('studentName')->join(', ', ', dan ') }}.
            Lengkapi nilainya di halaman Data Siswa, lalu hitung ulang.
        </flux:callout.text>
    </flux:callout>
    @endif

    @php
    $rankingList = collect($rankings);
    $topThree = $rankingList->take(3);
    $rest = $rankingList->slice(3);
    $maxScore = $rankingList->first()['vectorV'] ?? 0;
    @endphp

    @if ($rankingList->isNotEmpty())
    {{-- ===== Podium 3 Besar ===== --}}
    <div class="mt-8">
        <flux:heading size="lg">🏆 Hasil Ranking</flux:heading>
        <flux:subheading>
            Siswa dengan skor tertinggi adalah yang terbaik.
        </flux:subheading>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
            @foreach ($topThree as $r)
            <div
                @class([ 'rounded-2xl border p-5 text-center' , 'border-amber-300 bg-amber-50 dark:bg-amber-950/20 sm:order-2 sm:scale-105 sm:py-7'=> $r['rank'] === 1,
                'border-zinc-200 dark:border-zinc-700 sm:order-1' => $r['rank'] === 2,
                'border-zinc-200 dark:border-zinc-700 sm:order-3' => $r['rank'] === 3,
                ])
                >
                <div class="text-4xl">
                    {{ match ($r['rank']) {
                                    1 => '🥇',
                                    2 => '🥈',
                                    default => '🥉',
                                } }}
                </div>
                <div class="mt-2 font-semibold">{{ $r['studentName'] }}</div>
                <div class="text-xs text-zinc-500 font-mono">{{ $r['code'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- ===== Sisa Ranking (skor sebagai bar visual) ===== --}}
        @if ($rest->isNotEmpty())
        <div class="mt-6 divide-y divide-zinc-100 dark:divide-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
            @foreach ($rest as $r)
            <div class="flex items-center gap-4 px-4 py-3">
                <span class="w-8 shrink-0 text-center font-semibold text-zinc-500">{{ $r['rank'] }}</span>
                <span class="w-40 shrink-0 truncate font-medium">{{ $r['studentName'] }}</span>
                <div class="h-2 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                    <div
                        class="h-full bg-blue-500"
                        style="width: {{ $maxScore > 0 ? min(100, ($r['vectorV'] / $maxScore) * 100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @else
    <flux:callout variant="secondary" icon="information-circle" class="mt-8">
        <flux:callout.text>
            Tidak ada siswa dengan data lengkap untuk dirangking.
        </flux:callout.text>
    </flux:callout>
    @endif

    {{-- =====================================================
             DETAIL TEKNIS -- disembunyikan, untuk yang perlu verifikasi
             ===================================================== --}}
    <div class="mt-8 mb-8" x-data="{ open: false }">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
            <button
                type="button"
                @click="open = !open"
                class="flex w-full items-center justify-between gap-2 px-4 py-3 text-left">
                <span class="flex items-center gap-2 font-medium">
                    <flux:icon name="document-text" class="size-5 text-zinc-500" />
                    Lihat proses perhitungan lengkap (opsional)
                </span>
                <flux:icon
                    name="chevron-down"
                    class="size-4 text-zinc-500 transition-transform"
                    x-bind:class="open ? 'rotate-180' : ''" />
            </button>

            <div x-show="open" x-collapse class="border-t border-zinc-200 dark:border-zinc-700 px-4 pb-4">
                {{-- Langkah 1: Perbaikan Bobot --}}
                <div class="mt-4">
                    <flux:heading size="sm">Langkah 1 — Perbaikan Bobot Kriteria</flux:heading>
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

                {{-- Langkah 2 & 3: Vektor S dan V --}}
                <div class="mt-8">
                    <flux:heading size="sm">Langkah 2 & 3 — Vektor S dan Vektor V</flux:heading>
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

                {{-- Tabel ranking lengkap (semua siswa, urutan angka) --}}
                <div class="mt-8">
                    <flux:heading size="sm">Tabel Ranking Lengkap</flux:heading>

                    <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column class="w-16 text-center">Peringkat</flux:table.column>
                                <flux:table.column>Kode</flux:table.column>
                                <flux:table.column>Nama Siswa</flux:table.column>
                                <flux:table.column class="text-center">Vektor V</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($rankings as $r)
                                <flux:table.row :key="'rank-full-'.$r['alternativeId']">
                                    <flux:table.cell class="text-center font-semibold">{{ $r['rank'] }}</flux:table.cell>
                                    <flux:table.cell class="font-mono text-sm">{{ $r['code'] }}</flux:table.cell>
                                    <flux:table.cell class="font-medium">{{ $r['studentName'] }}</flux:table.cell>
                                    <flux:table.cell class="text-center font-mono font-semibold">
                                        {{ number_format($r['vectorV'], 6) }}
                                    </flux:table.cell>
                                </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>