<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceQcCheck extends Model
{
    protected $fillable = ['service_id', 'item', 'result', 'notes', 'checked_by'];

    public function service() { return $this->belongsTo(Service::class); }
    public function checker() { return $this->belongsTo(User::class, 'checked_by'); }

    /** Default QC items for electronics repair */
    public static function defaultItems(): array
    {
        return ['Touchscreen', 'Camera Depan', 'Camera Belakang', 'Charging', 'Speaker', 'Microphone', 'Network/WiFi', 'Bluetooth', 'Fingerprint/Face ID', 'Buttons', 'Screen Brightness', 'Battery'];
    }
}
