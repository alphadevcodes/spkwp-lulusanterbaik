<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('make:livewire-crud {name : Nama model (PascalCase), contoh: Criteria}
                                {--no-seeder : Skip generate seeder}
                                {--no-factory : Skip generate factory}
                                {--force : Overwrite files yang sudah ada}')]
#[Description('Generate Livewire 4 CRUD lengkap: Model, Migration, Factory, Seeder, 4 Components + Views, dan snippet Routes')]
class MakeLivewireCrud extends Command
{
    public function handle(): int
    {
        $name = trim($this->argument('name'));

        // Validasi PascalCase
        if (! preg_match('/^[A-Z][a-zA-Z0-9]+$/', $name)) {
            $this->error('  Nama harus PascalCase, contoh: Criteria, ProductCategory, UserRole');
            return self::FAILURE;
        }

        $plural     = Str::plural($name);                  // Criteria → Criteria (atau Products)
        $snake      = Str::snake($name);                   // Criteria → criteria
        $snakePlural = Str::snake($plural);                // Products → products
        $kebab      = Str::kebab($name);                   // Criteria → criteria
        $kebabPlural = Str::kebab($plural);                // Products → products

        $this->newLine();
        $this->components->info("Generating Livewire CRUD untuk [{$name}]...");
        $this->newLine();

        // ── 1. Model + Migration ─────────────────────────────────────────
        $this->components->task('Model & Migration', function () use ($name) {
            $this->callSilent('make:model', [
                'name'        => $name,
                '--migration' => true,
            ]);
        });

        // ── 2. Factory ───────────────────────────────────────────────────
        if (! $this->option('no-factory')) {
            $this->components->task('Factory', function () use ($name) {
                $this->callSilent('make:factory', [
                    'name'    => "{$name}Factory",
                    '--model' => $name,
                ]);
            });
        }

        // ── 3. Seeder ────────────────────────────────────────────────────
        if (! $this->option('no-seeder')) {
            $this->components->task('Seeder', function () use ($name) {
                $this->callSilent('make:seeder', [
                    'name' => "{$name}Seeder",
                ]);
            });
        }

        // ── 4. Livewire Components (index, create, edit, show) ───────────
        // Format: pages::criteria.index → resources/views/pages/criteria/⚡index.blade.php
        $components = ['index', 'create', 'edit', 'show'];

        foreach ($components as $action) {
            $componentName = "pages::{$snakePlural}.{$action}";
            $this->components->task("Livewire → {$componentName}", function () use ($componentName) {
                $this->callSilent('make:livewire', [
                    'name' => $componentName,
                ]);
            });
        }

        // ── 5. Route snippet ─────────────────────────────────────────────
        // pages:: format tidak generate class PHP — route langsung pakai path view
        $routeBlock = $this->buildRouteSnippet($snakePlural, $snake);

        $this->newLine();
        $this->components->info('Semua file berhasil di-generate!');
        $this->newLine();

        $this->line('  <fg=yellow;options=bold>Tambahkan routes berikut ke routes/web.php:</fg=yellow;options=bold>');
        $this->newLine();
        $this->line($routeBlock);
        $this->newLine();

        $this->components->bulletList([
            "Model         → app/Models/{$name}.php",
            "Migration     → database/migrations/xxxx_create_{$snakePlural}_table.php",
            ! $this->option('no-factory') ? "Factory       → database/factories/{$name}Factory.php" : null,
            ! $this->option('no-seeder')  ? "Seeder        → database/seeders/{$name}Seeder.php" : null,
            "Components    → resources/views/pages/{$snakePlural}/",
        ]);

        $this->newLine();

        return self::SUCCESS;
    }

    private function buildRouteSnippet(string $snakePlural, string $snake): string
    {
        $pad = str_repeat(' ', 4);

        return implode(PHP_EOL, [
            "  <fg=gray>// ── " . Str::title(str_replace('_', ' ', $snakePlural)) . " ──────────────────────────────────────────────</fg=gray>",
            "  Route::<fg=yellow>prefix</fg=yellow>('<fg=green>{$snakePlural}</fg=green>')->name('<fg=green>{$snakePlural}.</fg=green>')->group(<fg=cyan>function</fg=cyan> () {",
            "{$pad}    Route::<fg=yellow>get</fg=yellow>('/',              fn () => view('pages.{$snakePlural}.index'))->name('<fg=green>index</fg=green>');",
            "{$pad}    Route::<fg=yellow>get</fg=yellow>('/create',        fn () => view('pages.{$snakePlural}.create'))->name('<fg=green>create</fg=green>');",
            "{$pad}    Route::<fg=yellow>get</fg=yellow>('/{{$snake}}',      fn () => view('pages.{$snakePlural}.show'))->name('<fg=green>show</fg=green>');",
            "{$pad}    Route::<fg=yellow>get</fg=yellow>('/{{$snake}}/edit', fn () => view('pages.{$snakePlural}.edit'))->name('<fg=green>edit</fg=green>');",
            "  });",
        ]);
    }
}