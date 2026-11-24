<?php

use App\Http\Controllers\LandingController;
use App\Http\Middleware\SetSiteLocale;
use Illuminate\Support\Facades\Route;

// The same pages once per language: Arabic at "/", English under "/en".
foreach (['ar' => '', 'en' => 'en'] as $locale => $prefix) {
    Route::prefix($prefix)
        ->middleware(SetSiteLocale::class.':'.$locale)
        ->name($locale.'.')
        ->group(function () {
            Route::get('/', LandingController::class)->name('landing');
            Route::get('/map', fn () => view('map'))->name('map');
        });
}
