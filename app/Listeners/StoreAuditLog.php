<?php

namespace App\Listeners;

use App\Events\AuditEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class StoreAuditLog
{
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
    public function handle(AuditEvent $event): void
    {
        DB::table('audits')->insert([
            'entity' => $event->entity,
            'entity_id' => $event->entity_id ?? 0,
            'action' => $event->action,
            'data' => json_encode($event->data),
            'user_id' => $event->userId,
            'ip' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
