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
        $mail = SystemSetting::getGroup("mail");
        // MAIL-UNIFY-01: never ship the raw SMTP password to the frontend.
        if (!empty($mail['mail_password'])) {
            $mail['mail_password'] = '••••••••';
        }

        return inertia("Admin/Settings", [
            "settings" => [
                "general" => SystemSetting::getGroup("general"),
                "registration" => SystemSetting::getGroup("registration"),
                "maintenance" => SystemSetting::getGroup("maintenance"),
                "mail" => $mail,
                // PILOT-MAIL-04R — transactional mail (Resend) status.
                // Masked only — never the raw API key.
                "mail_resend" => \App\Services\TransactionalMailService::status(),
            ],
            "featureFlags" => FeatureFlagService::allWithMeta(),
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
            // PILOT-MAIL-04R — transactional mail (Resend) settings.
            // MAIL-UNIFY-01: canonical provider switch now supports smtp too.
            "mail_resend_provider" => "nullable|in:off,resend,smtp",
            "mail_resend_api_key" => "nullable|string|max:255",
            "mail_resend_from_address" => "nullable|email",
            "mail_resend_from_name" => "nullable|string|max:255",
            "mail_resend_reply_to" => "nullable|email",
        ]);

        foreach ($validated as $key => $value) {
            // API key is stored encrypted and handled separately (blank = retain).
            if ($key === 'mail_resend_api_key') {
                continue;
            }
            // MAIL-UNIFY-01: SMTP password is masked on the frontend — blank or
            // the display mask must PRESERVE the stored password (like the
            // Resend API key), never erase it.
            if ($key === 'mail_password' && ($value === null || $value === '' || $value === '••••••••')) {
                continue;
            }
            $group = match (true) {
                str_starts_with($key, 'mail_resend_') => 'mail_resend',
                str_starts_with($key, 'mail_') => 'mail',
                in_array($key, ['registration_open', 'require_approval', 'default_plan_slug', 'default_trial_days']) => 'registration',
                in_array($key, ['maintenance_mode', 'maintenance_message']) => 'maintenance',
                default => 'general',
            };
            SystemSetting::setValue($key, $value, $group);
        }

        // Resend API key: blank = retain existing secret; provided = replace
        // (encrypted at rest — never stored/serialized as plaintext).
        if ($request->filled('mail_resend_api_key')) {
            SystemSetting::setValue(
                'mail_resend_api_key',
                encrypt($request->input('mail_resend_api_key')),
                'mail_resend'
            );
        }

        // MAIL-UNIFY-01: keep the legacy default-mailer driver in sync with the
        // canonical transactional provider so MailConfigService::apply() works.
        $provider = $request->input('mail_resend_provider', \App\Services\TransactionalMailService::PROVIDER_OFF);
        SystemSetting::setValue(
            'mail_driver',
            $provider === \App\Services\TransactionalMailService::PROVIDER_SMTP ? 'smtp' : 'log',
            'mail'
        );

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

        // PILOT-MAIL-04R: route through the canonical transactional mail
        // service (Resend when provider is configured; env mailer otherwise).
        $success = \App\Services\TransactionalMailService::sendTest($request->email);

        // Record honest status for the mail settings UI.
        SystemSetting::setValue(
            'mail_resend_last_test_result',
            $success ? 'success' : 'failed',
            'mail_resend'
        );
        SystemSetting::setValue('mail_resend_last_test_at', now()->toDateTimeString(), 'mail_resend');

        if ($success) {
            SystemLog::info("Test email sent to {$request->email}");
            return back()->with("success", "✅ Email test berhasil dikirim ke {$request->email}. Cek inbox/spam Anda.");
        }

        // MAIL-CONSOLIDATE-01: honest failure — never pretend success or
        // silently test a different provider (legacy SMTP).
        $provider = \App\Services\TransactionalMailService::provider();
        $message = $provider !== 'resend'
            ? '❌ Provider transaksional nonaktif (Off). Aktifkan Resend di pengaturan Email Transaksional.'
            : '❌ Resend belum dikonfigurasi. Lengkapi Resend API Key dan From Email di pengaturan Email Transaksional.';
        SystemLog::error("Test email failed to {$request->email} (provider: {$provider})");
        return back()->with("error", $message);
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
