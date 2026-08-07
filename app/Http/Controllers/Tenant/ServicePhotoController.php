<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServicePhoto;
use App\Models\Tenant\ActivityLog;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicePhotoController extends Controller
{
    /**
     * Sprint v3.0D: Upload photos to service.
     * Authorization: must be able to update the service (owner/admin/assigned tech).
     * Branch scope: enforced via service's branch_id.
     */
    public function store(Request $request, Service $service)
    {
        $user = auth()->user();

        // Authorization: delegate to ServicePolicy::update()
        $this->authorize('update', $service);

        // Branch scope: user must belong to the same branch as service (or be owner/admin)
        $this->ensureBranchAccess($service, $user);

        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
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
                'uploaded_by' => $user->id,
            ]);
            $uploaded[] = $photo;
        }

        ActivityLog::log('service_photo', 'Upload ' . count($uploaded) . ' foto untuk service #' . $service->tracking_code, $service);

        return back()->with('success', count($uploaded) . ' foto berhasil diupload.');
    }

    /**
     * Sprint v3.0D: Delete photo from service.
     * Authorization:
     * - Owner/Admin/Manager: can delete any photo
     * - Assigned Technician: can delete photos on their own service
     * - Photo must belong to the service (cross-service attack prevention)
     * - Branch scope enforced
     */
    public function destroy(Service $service, ServicePhoto $servicePhoto)
    {
        $user = auth()->user();

        // Belt-and-suspenders: photo must belong to the service in the URL
        if ((int) $servicePhoto->service_id !== (int) $service->id) {
            abort(404, 'Foto tidak ditemukan pada servis ini.');
        }

        // Authorization: owner/admin/manager can delete any; technician only their own
        $isOwner = in_array($user->role, ['owner', 'admin', 'manager']);
        $isAssignedTech = $service->technician_id === $user->id;

        if (!$isOwner && !$isAssignedTech) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus foto ini.');
        }

        // Branch scope
        $this->ensureBranchAccess($service, $user);

        // Attempt storage deletion (safe — if file doesn't exist, just log and continue)
        $photoPath = $servicePhoto->photo_path;
        if ($photoPath && !str_starts_with($photoPath, 'http')) {
            // Local file — try to delete
            $deleted = Storage::disk('public')->delete($photoPath);
        }

        $servicePhoto->delete();

        ActivityLog::log('service_photo', "Hapus foto #{$servicePhoto->id} dari service #{$service->tracking_code} oleh {$user->name}", $service);

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    /**
     * Sprint v3.0D: Ensure user has branch access to the service.
     * Owner/Admin bypass branch check. Others must match.
     */
    private function ensureBranchAccess(Service $service, $user): void
    {
        if (in_array($user->role, ['owner', 'admin'])) {
            return;
        }
        if ($user->branch_id && (int) $user->branch_id !== (int) $service->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke servis cabang lain.');
        }
    }
}
