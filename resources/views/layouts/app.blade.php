<x-layouts::app.sidebar 
    :title="$title ?? null"
    :criteria-count="\App\Models\Criteria::count()"
    :alternative-count="\App\Models\Alternative::count()">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>