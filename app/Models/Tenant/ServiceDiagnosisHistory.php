<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceDiagnosisHistory extends Model
{
    protected $table = 'service_diagnosis_history';
    public $timestamps = false;

    protected $fillable = ['service_id', 'technician_id', 'old_diagnosis', 'new_diagnosis', 'reason'];
    protected $casts = ['created_at' => 'datetime'];

    public function service() { return $this->belongsTo(Service::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }

    /** Record a diagnosis change — append-only, never overwrite */
    public static function record(int $serviceId, array $oldDiagnosis, array $newDiagnosis, string $reason, ?int $techId = null): self
    {
        $record = static::create([
            'service_id' => $serviceId,
            'technician_id' => $techId ?? auth()->id(),
            'old_diagnosis' => json_encode($oldDiagnosis),
            'new_diagnosis' => json_encode($newDiagnosis),
            'reason' => $reason,
        ]);

        event(new \App\Events\Entity\DiagnosisRevisionRecorded($record));
        return $record;
    }
}
