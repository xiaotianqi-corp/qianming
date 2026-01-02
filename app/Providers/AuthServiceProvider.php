<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\CertificateRequest;
use App\Models\Identity;
use App\Models\SupportTicket;
use App\Policies\OrderPolicy;
use App\Policies\CertificateRequestPolicy;
use App\Policies\IdentityPolicy;
use App\Policies\SupportTicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Order::class => OrderPolicy::class,
        CertificateRequest::class => CertificateRequestPolicy::class,
        Identity::class => IdentityPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
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
}
