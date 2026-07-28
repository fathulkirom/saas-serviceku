<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServicePhoto;
use App\Models\Tenant\ActivityLog;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;

class ServicePhotoController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);
        if (!$driveService->isConnected()) {
            return back()->with('error', 'Upload foto membutuhkan Google Drive. Hubungkan Google Drive di Pengaturan terlebih dahulu.');
        }

        $uploaded = [];
        foreach ($request->file('photos') as $file) {
            $path = $file->store('services/' . $service->id, 'public');
            $driveUrl = $driveService->upload(
                storage_path('app/public/' . $path),
                'service_' . $service->id . '_' . time() . '_' . uniqid() . '.jpg',
                'services'
            );
            $photo = ServicePhoto::create([
                'service_id' => $service->id,
                'photo_path' => $driveUrl ?: $path,
                'keterangan' => $request->keterangan,
                'uploaded_by' => auth()->id(),
            ]);
            $uploaded[] = $photo;
        }

        ActivityLog::log('service_photo', 'Upload ' . count($uploaded) . ' foto untuk service #' . $service->tracking_code);

        return back()->with('success', count($uploaded) . ' foto berhasil diupload.');
    }

    public function destroy(Service $service, ServicePhoto $servicePhoto)
    {
        $servicePhoto->delete();

        ActivityLog::log('service_photo', 'Hapus foto service #' . $service->tracking_code);

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
