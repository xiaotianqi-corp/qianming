<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\SignaturePlanController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\IdentityController;
use App\Http\Controllers\Api\IdentityDocumentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CommercialDocumentController;
use App\Http\Controllers\Api\SecureIdentityController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\SupportTicketController;

/*
|--------------------------------------------------------------------------
| Rutas de Usuario Autenticado
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Webhooks (Externos)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/uanataca', [WebhookController::class, 'uanataca'])
    ->name('webhooks.uanataca');

/*
|--------------------------------------------------------------------------
| Módulo de Soporte
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('support')
    ->name('support.')
    ->group(function () {
        Route::get('/tickets', [SupportTicketController::class, 'index'])->name('index');
        Route::post('/tickets', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
    });

/*
|--------------------------------------------------------------------------
| Módulo de Cumplimiento e Interno
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:compliance,support,admin'])
    ->prefix('internal')
    ->name('internal.')
    ->group(function () {
        Route::prefix('compliance')->name('compliance.')->group(function () {
            Route::get('/identities', [SecureIdentityController::class, 'index'])->name('identities.index');
            Route::get('/identities/{identity}', [SecureIdentityController::class, 'show'])->name('identities.show');
            Route::patch('/identities/{identity}/verify', [SecureIdentityController::class, 'verify'])->name('identities.verify');
            Route::get('/documents/{document}/view', [SecureIdentityController::class, 'viewDocument'])->name('documents.view');
            Route::delete('/identities/{identity}', [SecureIdentityController::class, 'destroy'])->name('identities.destroy');
            
            Route::get('/audit-logs', function() {
                return \App\Models\AuditEvent::orderBy('created_at', 'desc')->paginate(50);
            })->name('audit.logs');
        });

        Route::prefix('support')->name('support.')->group(function () {
            Route::prefix('tickets')->name('tickets.')->group(function () {
                Route::get('/', [SupportTicketController::class, 'index'])->name('index');
                Route::post('/', [SupportTicketController::class, 'store'])->name('store');
                Route::get('/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
                Route::patch('/{supportTicket}', [SupportTicketController::class, 'update'])->name('update');
            });
        });
    });

/*
|--------------------------------------------------------------------------
| API V1 - Rutas Públicas con Rate Limiting
|--------------------------------------------------------------------------
*/
Route::prefix('public')->middleware('throttle:60,1')->group(function () {

    // --- Audit ---
    Route::get('/audit-logs', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/{auditEvent}', [AuditController::class, 'show'])->name('audit.show');

    // --- Catálogo & Productos ---
    Route::get('products/signatures', [SignaturePlanController::class, 'index'])->name('public.products.index');
    Route::get('catalog/signatures', [ProductController::class, 'signatures'])->name('public.catalog.signatures');

    // --- Países ---
    Route::prefix('countries')->name('public.countries.')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('index');
        Route::post('/', [CountryController::class, 'store'])->name('store');
        Route::get('{country}', [CountryController::class, 'show'])->name('show');
        Route::put('{country}', [CountryController::class, 'update'])->name('update');
        Route::delete('{country}', [CountryController::class, 'destroy'])->name('destroy');
    });

    // --- Identidades (Subscritores) ---
    Route::prefix('identities')->name('public.identities.')->group(function () {
        Route::get('/', [IdentityController::class, 'index'])->name('index');
        Route::post('/', [IdentityController::class, 'store'])->name('store');
        Route::get('{identity}', [IdentityController::class, 'show'])->name('show');
        Route::put('{identity}', [IdentityController::class, 'update'])->name('update');
        Route::delete('{identity}', [IdentityController::class, 'destroy'])->name('destroy');
    });

    // --- Documentos de Identidad (Archivos) ---
    Route::prefix('identity-documents')->name('public.documents.')->group(function () {
        Route::get('/', [IdentityDocumentController::class, 'index'])->name('index');
        Route::post('/', [IdentityDocumentController::class, 'store'])->name('store');
        Route::get('{identityDocument}', [IdentityDocumentController::class, 'show'])->name('show');
        Route::put('{identityDocument}', [IdentityDocumentController::class, 'update'])->name('update');
        Route::delete('{identityDocument}', [IdentityDocumentController::class, 'destroy'])->name('destroy');
    });

    // --- Órdenes de Compra ---
    Route::prefix('orders')->name('public.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('{order}', [OrderController::class, 'show'])->name('show');
        Route::put('{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('{order}', [OrderController::class, 'destroy'])->name('destroy');
    });

    // --- Facturación / Documentos Comerciales ---
    Route::prefix('commercial-documents')->name('public.commercial.')->group(function () {
        Route::get('/', [CommercialDocumentController::class, 'index'])->name('index');
        Route::post('/', [CommercialDocumentController::class, 'store'])->name('store');
        Route::get('{commercialDocument}', [CommercialDocumentController::class, 'show'])->name('show');
        Route::put('{commercialDocument}', [CommercialDocumentController::class, 'update'])->name('update');
        Route::delete('{commercialDocument}', [CommercialDocumentController::class, 'destroy'])->name('destroy');
    });
});