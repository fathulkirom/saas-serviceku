<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::setValue('app_name', 'ServiceKU', 'general');
        SystemSetting::setValue('app_description', 'SaaS Multi-Tenant Service Center Management', 'general');
        SystemSetting::setValue('registration_open', 'true', 'registration');
        SystemSetting::setValue('require_approval', 'false', 'registration');
        SystemSetting::setValue('default_plan_slug', 'trial', 'registration');
        SystemSetting::setValue('default_trial_days', '14', 'registration');
        SystemSetting::setValue('maintenance_mode', 'false', 'maintenance');
        SystemSetting::setValue('maintenance_message', 'System sedang dalam perawatan. Silakan coba lagi nanti.', 'maintenance');
        SystemSetting::setValue('max_tenants', '100', 'general');
        SystemSetting::setValue('notify_email', 'admin@serviceku.app', 'general');

        // Payment gateway settings default
        SystemSetting::setValue('payment_gateway', 'manual', 'payment');
        SystemSetting::setValue('payment_auto_confirm', 'false', 'payment');
        SystemSetting::setValue('midtrans_merchant_id', '', 'payment');
        SystemSetting::setValue('midtrans_client_key', '', 'payment');
        SystemSetting::setValue('midtrans_server_key', '', 'payment');
        SystemSetting::setValue('midtrans_is_production', 'false', 'payment');
        SystemSetting::setValue('payment_instructions', 'Transfer ke rekening berikut:\n\nBCA: 1234567890 a.n. PT ServiceKU\nMandiri: 9876543210 a.n. PT ServiceKU', 'payment');
        SystemSetting::setValue('bank_name_1', 'BCA', 'payment');
        SystemSetting::setValue('bank_account_name_1', 'PT ServiceKU', 'payment');
        SystemSetting::setValue('bank_account_number_1', '1234567890', 'payment');
        SystemSetting::setValue('bank_name_2', 'Mandiri', 'payment');
        SystemSetting::setValue('bank_account_name_2', 'PT ServiceKU', 'payment');
        SystemSetting::setValue('bank_account_number_2', '9876543210', 'payment');

        // Mail settings default (menggunakan log untuk development)
        SystemSetting::setValue('mail_driver', 'log', 'mail');
        SystemSetting::setValue('mail_host', 'smtp.gmail.com', 'mail');
        SystemSetting::setValue('mail_port', '587', 'mail');
        SystemSetting::setValue('mail_encryption', 'tls', 'mail');
        SystemSetting::setValue('mail_from_address', 'notifications@serviceku.my.id', 'mail');
        SystemSetting::setValue('mail_from_name', 'ServiceKU', 'mail');
    }
}
