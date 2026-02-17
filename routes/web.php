<?php

use App\Livewire\Pages\Connections\ConnectionManager;
use App\Livewire\Pages\Dashboard\Dashboard;
use App\Livewire\Pages\Home\Home;
use App\Livewire\Pages\ScheduleMessages\ScheduleMessages;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::get('dashboard', Dashboard::class)->name('dashboard')->middleware(['auth', 'verified', 'user_vinculed']);

Route::middleware(['auth'])->group(function () {
    // Configurações unificadas
    Route::get('settings', \App\Livewire\Pages\Settings\Index::class)->name('settings');

    Route::middleware(['user_vinculed', 'verified'])->group(function () {
        // Kanban
        Route::get('kanban', \App\Livewire\Pages\Kanban\Board::class)->name('kanban');
        Route::get('kanban3', \App\Livewire\Pages\Kanban3\Board::class)->name('kanban3');

        // Conexões
        Route::get('connections', ConnectionManager::class)->name('connections');

        // Mensagens Agendadas
        Route::get('scheduled-messages', ScheduleMessages::class)->name('scheduled-messages');
    });
});
