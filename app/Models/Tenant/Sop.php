<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    protected $table = 'sops';

    protected $fillable = [
        'title',
        'content',
        'target_roles',
        'version',
        'is_mandatory',
        'is_active',
        'created_by',
        'branch_id',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function readLogs()
    {
        return $this->hasMany(SopReadLog::class);
    }

    public function hasBeenReadBy(int $userId): bool
    {
        return $this->readLogs()->where('user_id', $userId)->exists();
    }
}
