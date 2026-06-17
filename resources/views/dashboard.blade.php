<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">
                    Dashboard Overview
                </h1>

                <p class="mt-1 text-sm text-zinc-500">
                    Monitor statistics and decision support system performance.
                </p>
            </div>

            <div class="self-start md:self-auto">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('dashboard') }}">
                        Dashboard
                    </flux:breadcrumbs.item>

                    <flux:breadcrumbs.item>
                        Overview
                    </flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

        </div>

        {{-- Stats Cards --}}
        {{-- Hero Section --}}
        <div class="grid gap-6 lg:grid-cols-12">

            {{-- Welcome Card --}}
            <flux:card class="lg:col-span-8 overflow-hidden">
                <div class="flex flex-col-reverse items-center justify-between gap-8 lg:flex-row">

                    <div class="max-w-xl">
                        <h2 class="text-3xl font-bold tracking-tight">
                            Welcome Back, Admin 👋
                        </h2>

                        <p class="mt-3 text-zinc-500">
                            Monitor criteria, alternatives, users, and SPK calculations
                            from one centralized dashboard.
                        </p>

                        <div class="mt-6 flex gap-3">
                            <flux:button variant="primary">
                                Start Calculation
                            </flux:button>

                            <flux:button variant="ghost">
                                View Report
                            </flux:button>
                        </div>
                    </div>

                    {{-- Animated Illustration --}}
                    <div class="shrink-0">
                        <img
                            src="https://undraw.co/api/illustrations/dashboard.svg"
                            alt="Dashboard"
                            class="h-48 animate-float">
                    </div>

                </div>
            </flux:card>

            {{-- KPI Cards --}}
            <div class="lg:col-span-4">
                <div class="grid gap-4 sm:grid-cols-2">

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">
                                    Criteria
                                </p>

                                <h3 class="mt-2 text-3xl font-bold">
                                    12
                                </h3>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <flux:icon name="clipboard-document-list" />
                            </div>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">
                                    Alternatives
                                </p>

                                <h3 class="mt-2 text-3xl font-bold">
                                    28
                                </h3>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                <flux:icon name="squares-2x2" />
                            </div>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">
                                    Users
                                </p>

                                <h3 class="mt-2 text-3xl font-bold">
                                    8
                                </h3>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                <flux:icon name="users" />
                            </div>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-500">
                                    Calculations
                                </p>

                                <h3 class="mt-2 text-3xl font-bold">
                                    56
                                </h3>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                <flux:icon name="calculator" />
                            </div>
                        </div>
                    </flux:card>

                </div>
            </div>

        </div>
        {{-- Chart --}}
        <div class="grid gap-6 lg:grid-cols-12">

            {{-- Chart --}}
            <flux:card class="lg:col-span-8">

                <div class="mb-6">
                    <h2 class="text-lg font-semibold">
                        SPK Ranking Overview
                    </h2>

                    <p class="text-sm text-zinc-500">
                        Alternative ranking result
                    </p>
                </div>

                <div class="h-96">
                    <canvas id="spkChart"></canvas>
                </div>

            </flux:card>

            {{-- Summary --}}
            <flux:card class="lg:col-span-4">

                <h2 class="mb-6 text-lg font-semibold">
                    System Summary
                </h2>

                <div class="space-y-5">

                    <div>
                        <p class="text-sm text-zinc-500">
                            Best Alternative
                        </p>

                        <p class="text-2xl font-bold">
                            Alternative A
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-zinc-500">
                            Highest Score
                        </p>

                        <p class="text-2xl font-bold text-emerald-600">
                            0.92
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-zinc-500">
                            Total Criteria Weight
                        </p>

                        <p class="text-xl font-semibold">
                            100%
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-zinc-500">
                            Last Calculation
                        </p>

                        <p class="font-semibold">
                            Today
                        </p>
                    </div>

                </div>

            </flux:card>

        </div>

        {{-- Bottom Section --}}
        <div class="grid gap-6 lg:grid-cols-2">

            <flux:card>
                <div class="mb-4">
                    <h2 class="font-semibold">
                        Top Alternatives
                    </h2>
                </div>

                <div class="space-y-4">

                    <div class="flex items-center justify-between">
                        <span>Alternative A</span>
                        <flux:badge color="emerald">
                            0.92
                        </flux:badge>
                    </div>

                    <div class="flex items-center justify-between">
                        <span>Alternative B</span>
                        <flux:badge color="blue">
                            0.87
                        </flux:badge>
                    </div>

                    <div class="flex items-center justify-between">
                        <span>Alternative C</span>
                        <flux:badge color="amber">
                            0.82
                        </flux:badge>
                    </div>

                </div>
            </flux:card>

            <flux:card>
                <div class="mb-4">
                    <h2 class="font-semibold">
                        System Summary
                    </h2>
                </div>

                <div class="space-y-4 text-sm">

                    <div class="flex justify-between">
                        <span>Total Criteria Weight</span>
                        <span class="font-semibold">100%</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Calculated Alternatives</span>
                        <span class="font-semibold">28</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Last Calculation</span>
                        <span class="font-semibold">Today</span>
                    </div>

                </div>
            </flux:card>

        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('spkChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    'Alternative A',
                    'Alternative B',
                    'Alternative C',
                    'Alternative D',
                    'Alternative E'
                ],
                datasets: [{
                    label: 'Score',
                    data: [
                        0.92,
                        0.87,
                        0.82,
                        0.75,
                        0.69
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    </script>
    @endpush
</x-layouts::app>