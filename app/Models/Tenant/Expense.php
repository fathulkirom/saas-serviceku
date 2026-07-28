<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'branch_id',
        'description',
        'amount',
        'expense_date',
        'category',
        'user_id',
        'created_by',
        'photo',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) return null;
        if (str_starts_with($this->photo, 'http')) return $this->photo;
        return '/storage/' . $this->photo;
    }

    const CATEGORIES = [
        'operasional' => 'Operasional',
        'gaji' => 'Gaji',
        'listrik' => 'Listrik',
        'sewa' => 'Sewa',
        'marketing' => 'Marketing',
        'lainnya' => 'Lainnya',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
