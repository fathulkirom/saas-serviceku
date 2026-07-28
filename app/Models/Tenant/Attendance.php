<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['user_id', 'shift_id', 'date', 'clock_in', 'clock_out', 'status', 'notes'];

    protected $casts = ['date' => 'date', 'clock_in' => 'datetime', 'clock_out' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
