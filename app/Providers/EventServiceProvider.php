<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Listeners\DispatchCertificateIssue;
use App\Events\AuditEvent;
use App\Listeners\StoreAuditLog;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        OrderPaid::class => [
            DispatchCertificateIssue::class,
        ],
        AuditEvent::class => [
            StoreAuditLog::class,
        ],
    ];

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
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
