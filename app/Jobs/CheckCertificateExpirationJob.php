<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckCertificateExpirationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Certificate::query()
            ->where('status', CertificateStatus::ACTIVE)
            ->where('expires_at', '<=', now()->addDays(30))
            ->update(['status' => CertificateStatus::EXPIRING_SOON]);

        Certificate::query()
            ->whereIn('status', [
                CertificateStatus::ACTIVE,
                CertificateStatus::EXPIRING_SOON
            ])
            ->where('expires_at', '<', now())
            ->update(['status' => CertificateStatus::EXPIRED]);
    }
}
