<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant\ImportLog;

class ImportData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 600; // 10 minutes for large imports

    public function __construct(
        private string $entityType,
        private array $rows,
        private int $userId,
    ) {}

    public function handle(): void
    {
        $importer = match ($this->entityType) {
            'customer' => app(\App\Services\Importers\CustomerImporter::class),
            'product' => app(\App\Services\Importers\ProductImporter::class),
            'device' => app(\App\Services\Importers\DeviceImporter::class),
            default => throw new \InvalidArgumentException("Unknown entity: {$this->entityType}"),
        };

        $result = $importer->import($this->rows, 200);

        ImportLog::create([
            'entity_type' => $this->entityType,
            'file_name' => 'queued_import.csv',
            'total_rows' => count($this->rows),
            'success_count' => $result['success_count'],
            'error_count' => $result['error_count'],
            'duplicate_count' => $result['duplicate_count'],
            'errors' => $result['errors'] ?? null,
            'status' => $result['status'],
            'created_by' => $this->userId,
        ]);
    }
}
