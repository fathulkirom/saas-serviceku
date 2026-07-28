<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $table = 'knowledge_base';

    protected $fillable = [
        'judul',
        'device_type',
        'device_brand',
        'device_model',
        'masalah',
        'solusi',
        'lampiran',
        'created_by',
        'branch_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
