<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ['service_id', 'technician_id', 'amount', 'percentage', 'status', 'paid_at', 'paid_by'];
    protected $casts = ['amount' => 'decimal:2', 'percentage' => 'decimal:2', 'paid_at' => 'datetime'];
    const COMMISSION_PERCENTAGE = 10;

    public static function autoCreateForService(Service $service): void
    {
        // BR-FIX-04: warranty rework services never auto-generate commission —
        // a fully-covered warranty rework must not duplicate original revenue.
        if ($service->is_warranty_claim) return;

        if (!$service->technician_id) return;
        if (self::where('service_id', $service->id)->exists()) return;

        $baseAmount = $service->service_charge + $service->spareparts()->sum('subtotal');
        $amount = round($baseAmount * self::COMMISSION_PERCENTAGE / 100);

        if ($amount <= 0) return;

        self::create([
            'service_id' => $service->id,
            'technician_id' => $service->technician_id,
            'amount' => $amount,
            'percentage' => self::COMMISSION_PERCENTAGE,
            'status' => 'pending',
        ]);

        ActivityLog::log('commission', 'Komisi otomatis: Rp ' . number_format($amount, 0, ',', '.') . ' untuk ' . ($service->technician?->name ?? 'teknisi'), $service);
    }

    public function service() { return $this->belongsTo(Service::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function payer() { return $this->belongsTo(User::class, 'paid_by'); }
}
