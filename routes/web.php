<?php

use App\Livewire\Board;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// The kanban board is the main page after logging in.
Route::get('dashboard', Board::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
