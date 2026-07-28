<?php

namespace App\Jobs;

use App\Models\Tenant\Sale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public Sale $sale, public string $email) {}

    public function handle(): void
    {
        if (!$this->sale->pdf_url) {
            app(\App\Jobs\GenerateInvoicePdf::class, ['sale' => $this->sale])->handle();
            $this->sale->refresh();
        }

        $pdfPath = storage_path('app/public/' . str_replace('/storage/', '', $this->sale->pdf_url));

        Mail::send([], [], function ($message) use ($pdfPath) {
            $message->to($this->email)
                ->subject('Invoice #' . $this->sale->id)
                ->attach($pdfPath, ['as' => 'invoice-' . $this->sale->id . '.pdf', 'mime' => 'application/pdf']);
        });
    }
}
