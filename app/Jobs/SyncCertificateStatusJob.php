<?php

namespace App\Jobs;

use App\Models\CertificateRequest;
use App\Services\UanatacaApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCertificateStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(UanatacaApiService $client): void
    {
        $pendingRequests = CertificateRequest::where('status', 'issued')->get();

        foreach ($pendingRequests as $request) {
            $status = $client->checkStatus($request->external_id);
            
            if ($status === 'active') {
                $request->update(['status' => 'active']);
            }
        }
    }
}