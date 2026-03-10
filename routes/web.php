<?php

use App\Http\Controllers\TempEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TempEmailController::class, 'index'])->name('home');
Route::post('/generate', [TempEmailController::class, 'generate'])->name('email.generate');
