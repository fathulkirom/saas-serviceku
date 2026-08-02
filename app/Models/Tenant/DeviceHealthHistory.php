<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class DeviceHealthHistory extends Model
{
    protected $table = 'device_health_history';
    public $timestamps = false;

    protected $fillable = ['device_id', 'service_id', 'metric', 'value', 'unit', 'recorded_by', 'notes', 'recorded_at'];
    protected $casts = ['recorded_at' => 'datetime'];

    public function device()  { return $this->belongsTo(Device::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }

    public static function metrics(): array
    {
        return [
            'battery_health' => ['label' => 'Battery Health', 'unit' => '%'],
            'charging_current' => ['label' => 'Charging Current', 'unit' => 'A'],
            'charging_voltage' => ['label' => 'Charging Voltage', 'unit' => 'V'],
            'temperature' => ['label' => 'Temperature', 'unit' => '°C'],
            'screen_brightness' => ['label' => 'Screen Brightness', 'unit' => '%'],
        ];
    }

    public function scopeBatteryHistory($q, int $deviceId)
    {
        return $q->where('device_id', $deviceId)->where('metric', 'battery_health')->orderBy('recorded_at');
    }
}
