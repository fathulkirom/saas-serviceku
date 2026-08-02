<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomerMessageTemplate extends Model
{
    protected $fillable = ['name', 'key', 'channel', 'subject', 'body', 'variables', 'is_active', 'is_system'];
    protected $casts = ['variables' => 'json', 'is_active' => 'bool', 'is_system' => 'bool'];

    /**
     * Interpolate variables in the template body.
     * Replaces {{customer_name}}, {{device}}, {{service_number}}, {{amount}}, {{warranty_date}}
     */
    public function render(array $data): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace("{{{$key}}}", $value ?? '', $body);
        }
        return $body;
    }

    /** Seed system templates */
    public static function seedSystem(): void
    {
        $templates = [
            ['name' => 'Service Ready', 'key' => 'service_ready', 'channel' => 'whatsapp',
                'body' => "Halo {{customer_name}},\n\nUnit {{device}} Anda sudah selesai diservis.\nSilakan diambil di cabang kami.\n\nTerima kasih,\nServiceKU", 'is_system' => true],
            ['name' => 'Quotation Waiting', 'key' => 'quotation_waiting', 'channel' => 'whatsapp',
                'body' => "Halo {{customer_name}},\n\nEstimasi biaya servis {{device}}:\nRp {{amount}}\n\nMohon konfirmasi untuk melanjutkan.\nTerima kasih.", 'is_system' => true],
            ['name' => 'Warranty Reminder', 'key' => 'warranty_reminder', 'channel' => 'whatsapp',
                'body' => "Halo {{customer_name}},\n\nGaransi unit {{device}} Anda akan berakhir pada {{warranty_date}}.\nSegera lakukan servis berkala.\n\nTerima kasih.", 'is_system' => true],
            ['name' => 'Follow Up', 'key' => 'follow_up', 'channel' => 'whatsapp',
                'body' => "Halo {{customer_name}},\n\nBagaimana kondisi unit {{device}} setelah diservis?\nKami ingin memastikan semuanya berfungsi dengan baik.\n\nTerima kasih.", 'is_system' => true],
            ['name' => 'Pickup Reminder', 'key' => 'pickup_reminder', 'channel' => 'whatsapp',
                'body' => "Halo {{customer_name}},\n\nUnit {{device}} Anda sudah siap diambil sejak {{service_number}}.\nSilakan datang ke cabang kami.\n\nTerima kasih.", 'is_system' => true],
        ];
        foreach ($templates as $t) {
            self::firstOrCreate(['key' => $t['key']], $t);
        }
    }
}
