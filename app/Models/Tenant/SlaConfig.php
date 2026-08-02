<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class SlaConfig extends Model
{
    protected $table = 'sla_configs';
    protected $fillable = ['workflow_key', 'priority', 'target_checking_minutes', 'target_repair_minutes', 'target_qc_minutes', 'target_delivery_minutes', 'escalation_level1_minutes', 'escalation_level2_minutes', 'escalation_level1_role', 'escalation_level2_role', 'is_active'];
    protected $casts = ['is_active' => 'bool'];
}
