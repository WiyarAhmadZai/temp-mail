<?php

use App\Http\Controllers\TempEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TempEmailController::class, 'index'])->name('home');
Route::post('/generate', [TempEmailController::class, 'generate'])
    ->middleware('throttle:generate-email')
    ->name('email.generate');
Route::get('/inbox', [TempEmailController::class, 'inbox'])->name('inbox');
Route::get('/inbox/{id}', [TempEmailController::class, 'showMessage'])->name('message.show');
Route::get('/api/poll', [TempEmailController::class, 'poll'])->name('api.poll');
