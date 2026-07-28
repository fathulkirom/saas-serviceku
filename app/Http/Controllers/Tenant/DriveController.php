<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;

class DriveController extends Controller
{
    public function connect()
    {
        $service = new GoogleDrivePhotoService(tenancy()->tenant->id);
        $url = $service->getAuthUrl();

        return redirect()->route('pengaturan.index', ['tab' => 'settings']);
    }

    public function callback(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        try {
            $service = new GoogleDrivePhotoService(tenancy()->tenant->id);
            $result = $service->handleCallback($request->code, tenancy()->tenant->id);

            return redirect()->route('settings.index', ['tab' => 'drive'])->with('success', 'Google Drive berhasil terhubung sebagai ' . ($result['connected_email'] ?? ''));
        } catch (\Exception $e) {
            return redirect()->route('settings.index', ['tab' => 'drive'])->with('error', 'Gagal menghubungkan Google Drive: ' . $e->getMessage());
        }
    }

    public function status()
    {
        $service = new GoogleDrivePhotoService(tenancy()->tenant->id);

        return response()->json([
            'connected' => $service->isConnected(),
            'info' => $service->getConnectionInfo(),
        ]);
    }

    public function disconnect()
    {
        $service = new GoogleDrivePhotoService(tenancy()->tenant->id);
        $service->disconnect();

        return redirect()->route('settings.index', ['tab' => 'drive'])->with('success', 'Google Drive berhasil diputuskan.');
    }
}
