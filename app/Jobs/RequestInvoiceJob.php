<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RequestInvoiceJob implements ShouldQueue
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
    public function handle(SpringBillingClient $client): void
    {
        $response = $client->createInvoice($this->order);

        CommercialDocument::create([
            'order_id'   => $this->order->id,
            'country_id' => $this->order->country_id,
            'type'       => $response->type,
            'number'     => $response->number,
            'subtotal'   => $response->subtotal,
            'tax'        => $response->tax,
            'total'      => $response->total,
            'currency'   => $response->currency,
            'status'     => 'issued',
            'external_id'=> $response->external_id,
            'pdf_url'    => $response->pdf_url,
            'xml_url'    => $response->xml_url,
        ]);
    }
}
