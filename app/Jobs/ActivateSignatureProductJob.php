<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\CertificateRequest;
use App\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActivateSignatureProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Order $order) 
    {
    }

    public function handle(): void
    {
        foreach ($this->order->items as $item) {
            $certificateRequest = CertificateRequest::create([
                'order_item_id' => $item->id,
                'status' => 'pending',
            ]);

            IssueCertificateJob::dispatch($certificateRequest);
        }

        $this->order->update([
            'status' => OrderStatus::PROCESSING
        ]);
    }
}