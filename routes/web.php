<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Web\TicketController;
use App\Http\Controllers\Web\CannedResponseController;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource('tickets', TicketController::class)
        ->names([
            'index' => 'tickets.index',
            'create' => 'tickets.create',
            'store' => 'tickets.store',
            'show' => 'tickets.show',
            'edit' => 'tickets.edit',
            'update' => 'tickets.update',
        ]);
    
    Route::post('tickets/{ticket}/reply', [TicketController::class, 'reply'])
        ->name('tickets.reply');
    
    Route::get('tickets/{ticket}/attachments/{attachment}', [TicketController::class, 'downloadAttachment'])
        ->name('tickets.attachments.download');
});

Route::middleware(['role:admin,support'])->prefix('support')->name('support.')->group(function () {
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('{supportTicket}', [TicketController::class, 'show'])->name('show');
        Route::put('{supportTicket}', [TicketController::class, 'update'])->name('update');
        Route::post('{supportTicket}/reply', [TicketController::class, 'reply'])
            ->name('reply');
        Route::post('/{ticket}/assign', [TicketController::class, 'assign'])
            ->name('tickets.assign');
        
        Route::post('/{ticket}/resolve', [TicketController::class, 'resolve'])
            ->name('tickets.resolve');
        
        Route::post('/{ticket}/reopen', [TicketController::class, 'reopen'])
            ->name('tickets.reopen');
    });
    Route::resource('canned-responses', CannedResponseController::class)
        ->names([
            'index' => 'canned-responses.index',
            'create' => 'canned-responses.create',
            'store' => 'canned-responses.store',
            'edit' => 'canned-responses.edit',
            'update' => 'canned-responses.update',
            'destroy' => 'canned-responses.destroy',
        ]);
});
require __DIR__.'/settings.php';
