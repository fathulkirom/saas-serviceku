<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\WaGatewayConfig;
use App\Models\Tenant\ActivityLog;
use App\Models\GoogleDriveToken;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return redirect()->route('pengaturan.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'primary_color' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'paper_size' => 'nullable|in:a4,a5,thermal_80,thermal_58',
        ]);
        foreach ($validated as $key => $value) {
            TenantSetting::setValue($key, $value);
        }
        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|mimes:png,jpg,jpeg|max:2048']);
        $path = $request->file('logo')->store('logos', 'public');
        TenantSetting::setValue('logo', '/storage/' . $path);
        return back()->with('success', 'Logo berhasil diupload.');
    }

    public function updateMaintenance(Request $request)
    {
        $validated = $request->validate([
            'maintenance_mode' => 'required|boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        TenantSetting::setValue('maintenance_mode', $validated['maintenance_mode'] ? 'true' : 'false');
        TenantSetting::setValue('maintenance_message', $validated['maintenance_message'] ?? '');

        $status = $validated['maintenance_mode'] ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::log('maintenance', 'Mode maintenance ' . $status);

        return back()->with('success', 'Mode maintenance ' . $status . '.');
    }

    public function updateWhatsappGateway(Request $request)
    {
        $tenantId = tenancy()->tenant->id;

        $validated = $request->validate([
            'provider' => 'required|in:fonnte,wablas',
            'api_key' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'template_service_received' => 'nullable|string',
            'template_service_finished' => 'nullable|string',
        ]);

        WaGatewayConfig::updateOrCreate(
            ['tenant_id' => $tenantId],
            [
                'provider' => $validated['provider'],
                'api_key' => $validated['api_key'] ?? '',
                'is_active' => $validated['is_active'] ?? false,
                'template_service_received' => $validated['template_service_received'] ?? '',
                'template_service_finished' => $validated['template_service_finished'] ?? '',
            ]
        );

        ActivityLog::log('wa_gateway', 'Konfigurasi WhatsApp Gateway diperbarui');

        return back()->with('success', 'Konfigurasi WhatsApp Gateway berhasil disimpan.');
    }

    public function getWhatsappLink(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:500',
        ]);

        $whatsappNumber = $request->phone ?? TenantSetting::getValue('whatsapp_number', '');
        $message = $request->message ?? '';

        $phone = preg_replace('/[^0-9]/', '', $whatsappNumber);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        $url = 'https://wa.me/' . $phone;
        if ($message) {
            $url .= '?text=' . urlencode($message);
        }

        return response()->json(['url' => $url]);
    }

    public function updateLayoutPreferences(Request $request)
    {
        if (!auth()->user()->canManageSettings()) {
            abort(403, 'Hanya owner yang dapat mengubah preferensi tampilan.');
        }

        $validated = $request->validate([
            'layout' => 'required|in:sidebar,topbar,slim-sidebar',
            'menu_style' => 'required|in:expanded,grouped',
            'sidebar_position' => 'nullable|in:left,right',
            'sidebar_hidden' => 'nullable|boolean',
            'visible_groups' => 'nullable|array',
            'visible_groups.*' => 'string',
        ]);

        $user = auth()->user();
        $preferences = array_merge($user->ui_preferences ?? [], $validated);
        $user->update(['ui_preferences' => $preferences]);

        return back()->with('success', 'Preferensi tampilan berhasil disimpan.');
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|string',
        ]);

        TenantSetting::setValue('theme', $validated['theme']);

        return back()->with('success', 'Tema berhasil diubah.');
    }
}
