<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServicePhoto extends Model
{
    protected $fillable = [
        'service_id',
        'photo_path',
        'keterangan',
        'uploaded_by',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path && str_starts_with($this->photo_path, 'http')) {
            return $this->photo_path;
        }
        return $this->photo_path ? '/storage/' . $this->photo_path : null;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
