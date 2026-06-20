<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('overview','pages::overview')->name('overview');
    Route::livewire('criteria','pages::criteria')->name('criteria');
    Route::livewire('alternative','pages::alternative')->name('alternative');
    Route::livewire('/ranking/calculate', 'pages::rekomendasi')
    ->name('ranking.calculate');

});

require __DIR__.'/settings.php';
