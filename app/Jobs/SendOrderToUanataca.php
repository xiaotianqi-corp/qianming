<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\UanatacaApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderToUanataca implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Order $order)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(UanatacaApiService $pscService)
    {
        $identity = $this->order->customer->identity;

        $response = $pscService->requestCertificate($identity, $this->order);

        if ($response->successful()) {
            $this->order->update([
                'status' => 'processing',
                'external_id' => $response->json('request_id')
            ]);
        } else {
            throw new \Exception("Error PSC: " . $response->body());
        }
    }
}
