<?php

use App\Http\Controllers\Api\EraController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/eras', [EraController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
