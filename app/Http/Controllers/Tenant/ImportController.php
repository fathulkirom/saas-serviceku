<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ImportLog;
use App\Services\Importers\CustomerImporter;
use App\Services\Importers\ProductImporter;
use App\Services\Importers\DeviceImporter;
use Illuminate\Http\Request;

/**
 * Import Controller — Sprint 7.5E.
 */
class ImportController extends Controller
{
    /** Show import center page */
    public function index()
    {
        $logs = ImportLog::latest()->take(20)->get();
        return inertia('Pengaturan/ImportCenter', ['logs' => $logs]);
    }

    /** Preview import data */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'entity' => 'required|in:customer,product,device',
            'rows' => 'required|array|min:1|max:100',
        ]);

        $importer = $this->resolveImporter($data['entity']);
        $preview = $importer->preview($data['rows']);

        return response()->json($preview);
    }

    /** Import data */
    public function import(Request $request)
    {
        $data = $request->validate([
            'entity' => 'required|in:customer,product,device',
            'rows' => 'required|array|min:1',
            'file_name' => 'nullable|string',
        ]);

        $importer = $this->resolveImporter($data['entity']);
        $totalRows = count($data['rows']);

        // Large imports go to queue
        if ($totalRows > 500) {
            $importer->queue($data['rows'], $data['entity'], auth()->id());
            return response()->json(['status' => 'queued', 'total_rows' => $totalRows, 'message' => "{$totalRows} baris dikirim ke queue untuk diproses."]);
        }

        // Small imports process inline
        $result = $importer->import($data['rows']);

        ImportLog::create([
            'entity_type' => $data['entity'],
            'file_name' => $data['file_name'] ?? 'upload.csv',
            'total_rows' => $totalRows,
            'success_count' => $result['success_count'],
            'error_count' => $result['error_count'],
            'duplicate_count' => $result['duplicate_count'],
            'errors' => $result['errors'] ?? null,
            'status' => $result['status'],
            'created_by' => auth()->id(),
        ]);

        return response()->json($result);
    }

    private function resolveImporter(string $entity): \App\Services\BaseImporter
    {
        return match ($entity) {
            'customer' => app(CustomerImporter::class),
            'product' => app(ProductImporter::class),
            'device' => app(DeviceImporter::class),
            default => throw new \InvalidArgumentException("Unknown entity: {$entity}"),
        };
    }
}
