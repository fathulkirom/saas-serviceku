<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class DailyDeposit extends Model
{
    protected $fillable = [
        'branch_id',
        'amount',
        'deposit_date',
        'created_by',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deposit_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
