<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Service Intake Snapshot — immutable record of device condition at intake.
 * Once created, NEVER updated. Protects business from false complaints.
 */
class ServiceIntakeSnapshot extends Model
{
    protected $table = 'service_intake_snapshots';
    public $timestamps = false;

    protected $fillable = [
        'service_id', 'device_id',
        'customer_complaint', 'condition_summary',
        'photos', 'checklist_snapshot', 'device_health_snapshot',
        'customer_confirmed', 'signature_image', 'confirmed_at',
        'created_by',
    ];

    protected $casts = [
        'photos' => 'json',
        'checklist_snapshot' => 'json',
        'device_health_snapshot' => 'json',
        'customer_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function service() { return $this->belongsTo(Service::class); }
    public function device()  { return $this->belongsTo(Device::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    /** Freeze current state from checklist results + device health */
    public static function capture(Service $service, bool $customerConfirmed = false, ?string $signature = null): self
    {
        $results = ServiceChecklistResult::where('service_id', $service->id)->get();
        $health = DeviceHealthHistory::where('device_id', $service->device_id)
            ->where('service_id', $service->id)->get();

        return static::create([
            'service_id' => $service->id,
            'device_id' => $service->device_id,
            'customer_complaint' => $service->problem_description,
            'condition_summary' => $service->condition_note,
            'photos' => $service->photos()->pluck('photo_path')->toArray(),
            'checklist_snapshot' => $results->toArray(),
            'device_health_snapshot' => $health->toArray(),
            'customer_confirmed' => $customerConfirmed,
            'signature_image' => $signature,
            'confirmed_at' => $customerConfirmed ? now() : null,
            'created_by' => auth()->id(),
        ]);
    }
}
