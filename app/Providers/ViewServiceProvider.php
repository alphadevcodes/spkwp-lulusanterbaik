<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Criteria;
use App\Models\Alternative;
use Illuminate\Support\Facades\View;


class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        View::composer('layouts::app.sidebar', function ($view) {
            $view->with([
                'criteriaCount' => Criteria::count(),
                'alternativeCount' => Alternative::count(),
            ]);
        });
    }
}
