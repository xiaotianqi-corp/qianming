<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\ActivateSignatureProductJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchCertificateIssue implements ShouldQueue
{

    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        ActivateSignatureProductJob::dispatch($event->order);
    }
}
