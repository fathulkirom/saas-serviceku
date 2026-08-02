<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Base Importer — Sprint 7.5E.
 * Handles chunked import with validation, preview, rollback.
 *
 * Usage: Extend and implement mapRow() + validate() + import().
 */
abstract class BaseImporter
{
    protected array $errors = [];
    protected array $duplicates = [];
    protected int $successCount = 0;
    protected int $totalRows = 0;

    /** Map a row from the file to an associative array */
    abstract protected function mapRow(array $row): array;

    /** Validate a mapped row. Return null if valid, or error message. */
    abstract protected function validate(array $mapped): ?string;

    /** Import a single validated row */
    abstract protected function importOne(array $mapped): void;

    /** Preview — returns validation results without importing */
    public function preview(array $rows): array
    {
        $preview = ['total' => count($rows), 'valid' => 0, 'errors' => [], 'duplicates' => []];
        $this->totalRows = count($rows);

        foreach ($rows as $idx => $row) {
            try {
                $mapped = $this->mapRow($row);
                $error = $this->validate($mapped);
                if ($error) {
                    $preview['errors'][] = ['row' => $idx + 1, 'message' => $error];
                } else {
                    $preview['valid']++;
                }
            } catch (\Throwable $e) {
                $preview['errors'][] = ['row' => $idx + 1, 'message' => $e->getMessage()];
            }
        }

        return $preview;
    }

    /** Import all rows — wrapped in a transaction */
    public function import(array $rows, int $chunkSize = 100): array
    {
        $this->totalRows = count($rows);
        $this->errors = [];
        $this->duplicates = [];
        $this->successCount = 0;

        DB::beginTransaction();
        try {
            foreach (array_chunk($rows, $chunkSize) as $chunk) {
                foreach ($chunk as $idx => $row) {
                    try {
                        $mapped = $this->mapRow($row);
                        $error = $this->validate($mapped);
                        if ($error) {
                            $this->errors[] = ['row' => $idx + 1, 'message' => $error];
                            continue;
                        }
                        $this->importOne($mapped);
                        $this->successCount++;
                    } catch (\Throwable $e) {
                        $this->errors[] = ['row' => $idx + 1, 'message' => $e->getMessage()];
                    }
                }
            }

            if (count($this->errors) > 0 && $this->successCount === 0) {
                DB::rollBack();
                return $this->result('rolled_back');
            }

            DB::commit();
            return $this->result('completed');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errors[] = ['row' => 0, 'message' => $e->getMessage()];
            return $this->result('failed');
        }
    }

    /** Queue import for large datasets */
    public function queue(array $rows, string $entityType, int $userId): void
    {
        \App\Jobs\ImportData::dispatch($entityType, $rows, $userId);
    }

    protected function result(string $status): array
    {
        return [
            'status' => $status,
            'total_rows' => $this->totalRows,
            'success_count' => $this->successCount,
            'error_count' => count($this->errors),
            'duplicate_count' => count($this->duplicates),
            'errors' => $this->errors,
        ];
    }
}
