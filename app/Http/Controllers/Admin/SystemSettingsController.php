<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\SystemLog;
use App\Services\FeatureFlagService;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function index()
    {
        return inertia("Admin/Settings", [
            "settings" => [
                "general" => SystemSetting::getGroup("general"),
                "registration" => SystemSetting::getGroup("registration"),
                "maintenance" => SystemSetting::getGroup("maintenance"),
                "mail" => SystemSetting::getGroup("mail"),
            ],
            "featureFlags" => FeatureFlagService::all(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            "app_name" => "required|string|max:255",
            "app_description" => "nullable|string",
            "registration_open" => "required|in:true,false",
            "require_approval" => "required|in:true,false",
            "default_plan_slug" => "required|string|exists:plans,slug",
            "default_trial_days" => "required|integer|min:0|max:365",
            "maintenance_mode" => "required|in:true,false",
            "maintenance_message" => "nullable|string",
            "max_tenants" => "required|integer|min:1|max:99999",
            "notify_email" => "nullable|email",
            "mail_driver" => "nullable|in:log,smtp",
            "mail_host" => "nullable|string|max:255",
            "mail_port" => "nullable|integer|min:1|max:65535",
            "mail_encryption" => "nullable|in:tls,ssl,null",
            "mail_username" => "nullable|string|max:255",
            "mail_password" => "nullable|string|max:255",
            "mail_from_address" => "nullable|email",
            "mail_from_name" => "nullable|string|max:255",
        ]);

        foreach ($validated as $key => $value) {
            $group = match (true) {
                str_starts_with($key, 'mail_') => 'mail',
                in_array($key, ['registration_open', 'require_approval', 'default_plan_slug', 'default_trial_days']) => 'registration',
                in_array($key, ['maintenance_mode', 'maintenance_message']) => 'maintenance',
                default => 'general',
            };
            SystemSetting::setValue($key, $value, $group);
        }

        \App\Services\MailConfigService::apply();
        SystemLog::info("System settings updated");
        return back()->with("success", "Pengaturan sistem berhasil disimpan.");
    }

    /**
     * Update feature flags.
     */
    public function updateFeatureFlags(Request $request)
    {
        $validated = $request->validate([
            'feature_two_factor_auth' => 'required|in:true,false',
            'feature_email_verification' => 'required|in:true,false',
            'feature_custom_fields' => 'required|in:true,false',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::setValue($key, $value, 'features');
        }

        FeatureFlagService::resetCache();
        SystemLog::info("Feature flags updated");

        return back()->with("success", "Feature flags berhasil diperbarui.");
    }

    public function testMail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $success = \App\Services\MailConfigService::test($request->email);
        if ($success) {
            SystemLog::info("Test email sent to {$request->email}");
            return back()->with("success", "✅ Email test berhasil dikirim ke {$request->email}. Cek inbox/spam Anda.");
        }
        SystemLog::error("Test email failed to {$request->email}");
        return back()->with("error", "❌ Gagal mengirim email. Periksa konfigurasi SMTP Anda.");
    }

    public function logs(Request $request)
    {
        $query = SystemLog::latest();
        if ($request->filled("level")) $query->where("level", $request->level);
        if ($request->filled("type")) $query->where("type", $request->type);
        if ($request->filled("date_from")) $query->whereDate("created_at", ">=", $request->date_from);
        if ($request->filled("date_to")) $query->whereDate("created_at", "<=", $request->date_to);

        return inertia("Admin/Logs", [
            "logs" => $query->paginate(30),
            "filters" => $request->only(["level", "type", "date_from", "date_to"]),
        ]);
    }

    public function clearLogs()
    {
        SystemLog::truncate();
        SystemLog::info("System logs cleared by admin");
        return back()->with("success", "Log sistem berhasil dibersihkan.");
    }
}
