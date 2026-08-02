<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Placeholder PDF generation job.
 * Full implementation in Sprint 7.3.
 */
class GeneratePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private $entity,
        private array $config = [],
    ) {}

    public function handle(): void
    {
        // PDF generation will be implemented with barryvdh/laravel-dompdf
        // For now, record intent
        \App\Models\Tenant\ActivityLog::log(
            'pdf_generated',
            'PDF generated for ' . get_class($this->entity) . ' #' . $this->entity->getKey(),
            $this->entity,
            $this->config,
        );
    }
}
