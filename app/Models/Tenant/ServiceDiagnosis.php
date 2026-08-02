<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceDiagnosis extends Model
{
    protected $fillable = ['service_id', 'customer_complaint', 'findings', 'cause', 'solution', 'estimated_cost', 'estimated_minutes', 'diagnosed_by'];
    protected $casts = ['estimated_cost' => 'decimal:2'];

    public function service() { return $this->belongsTo(Service::class); }
    public function diagnostician() { return $this->belongsTo(User::class, 'diagnosed_by'); }
}
