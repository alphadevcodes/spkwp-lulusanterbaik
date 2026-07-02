
<div>
    {{-- Simplicity is the consequence of refined emotions. - Jean D'Alembert --}}
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Dashboard Overview</h1>
                <p class="mt-1 text-sm text-zinc-500">
                    Monitor statistics and decision support system performance.
                </p>
            </div>

            <div class="self-start md:self-auto">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Overview</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
        </div>

        {{-- Hero + KPI --}}
        <div class="grid gap-6 lg:grid-cols-12">

            <flux:card class="lg:col-span-8 overflow-hidden">
                <div class="flex flex-col-reverse items-center justify-between gap-8 lg:flex-row">
                    <div class="max-w-xl">
                        <h2 class="text-3xl font-bold tracking-tight">Welcome Back, Admin 👋</h2>
                        <p class="mt-3 text-zinc-500">
                            Monitor criteria, alternatives, users, and SPK calculations from one centralized dashboard.
                        </p>
                        <div class="mt-6 flex gap-3">
                            <flux:button variant="primary" :href="route('ranking.calculate')" wire:navigate>
                                Start Calculation
                            </flux:button>
                            <flux:button variant="ghost">View Report</flux:button>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <img src="https://undraw.co/api/illustrations/dashboard.svg" alt="Dashboard" class="h-48 animate-float">
                    </div>
                </div>
            </flux:card>

            <div class="lg:col-span-4">
                <div class="grid gap-4 sm:grid-cols-2">

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">Criteria</p>
                                <h3 class="mt-2 text-3xl font-bold">{{ $this->criteriaCount }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <flux:icon name="clipboard-document-list" />
                            </div>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">Alternatives</p>
                                <h3 class="mt-2 text-3xl font-bold">{{ $this->alternativeCount }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                <flux:icon name="squares-2x2" />
                            </div>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">Users</p>
                                <h3 class="mt-2 text-3xl font-bold">{{ $this->userCount }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                <flux:icon name="users" />
                            </div>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">Calculations</p>
                                <h3 class="mt-2 text-3xl font-bold">{{ $this->calculatedCount }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                <flux:icon name="calculator" />
                            </div>
                        </div>
                    </flux:card>

                </div>
            </div>
        </div>

        {{-- Chart & Summary --}}
        <div class="grid gap-6 lg:grid-cols-12">

            <flux:card class="lg:col-span-8" wire:ignore>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold">SPK Ranking Overview</h2>
                    <p class="text-sm text-zinc-500">Alternative ranking result</p>
                </div>

                @if ($this->calculatedCount > 0)
                <div class="h-96">
                    <canvas
                        id="spkChart"
                        x-data
                        x-init="renderSpkChart(@js(collect($this->rankings)->pluck('studentName')), @js(collect($this->rankings)->pluck('vectorV')))"
                    ></canvas>
                </div>
                @else
                <div class="flex h-96 items-center justify-center text-zinc-500">
                    Belum ada hasil perhitungan.
                </div>
                @endif
            </flux:card>

            <flux:card class="lg:col-span-4">
                <h2 class="mb-6 text-lg font-semibold">System Summary</h2>

                <div class="space-y-5">
                    <div>
                        <p class="text-sm text-zinc-500">Best Alternative</p>
                        <p class="text-2xl font-bold">
                            {{ $this->bestAlternative['studentName'] ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-zinc-500">Highest Score</p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ isset($this->bestAlternative['vectorV']) ? number_format($this->bestAlternative['vectorV'], 4) : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-zinc-500">Total Criteria Weight</p>
                        <p class="text-xl font-semibold">{{ $this->totalWeight }}%</p>
                    </div>

                    <div>
                        <p class="text-sm text-zinc-500">Last Calculation</p>
                        <p class="font-semibold">
                            {{ $this->calculatedCount > 0 ? 'Live' : 'Belum pernah' }}
                        </p>
                    </div>
                </div>
            </flux:card>

        </div>

        {{-- Bottom Section --}}
        <div class="grid gap-6 lg:grid-cols-2">

            <flux:card>
                <div class="mb-4">
                    <h2 class="font-semibold">Top Alternatives</h2>
                </div>

                <div class="space-y-4">
                    @forelse ($this->topThree as $r)
                    <div class="flex items-center justify-between">
                        <span>{{ $r['studentName'] }}</span>
                        <flux:badge :color="$r['rank'] === 1 ? 'emerald' : ($r['rank'] === 2 ? 'blue' : 'amber')">
                            {{ number_format($r['vectorV'], 4) }}
                        </flux:badge>
                    </div>
                    @empty
                    <p class="text-sm text-zinc-500">Belum ada data.</p>
                    @endforelse
                </div>
            </flux:card>

            <flux:card>
                <div class="mb-4">
                    <h2 class="font-semibold">System Summary</h2>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span>Total Criteria Weight</span>
                        <span class="font-semibold">{{ $this->totalWeight }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Calculated Alternatives</span>
                        <span class="font-semibold">{{ $this->calculatedCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Alternatives</span>
                        <span class="font-semibold">{{ $this->alternativeCount }}</span>
                    </div>
                </div>
            </flux:card>

        </div>

    </div>

    @script
    <script>
        window.renderSpkChart = function (labels, data) {
            if (window.spkChartInstance) {
                window.spkChartInstance.destroy();
            }

            window.spkChartInstance = new Chart(document.getElementById('spkChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Score',
                        data: data,
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        };
    </script>
    @endscript
</div>