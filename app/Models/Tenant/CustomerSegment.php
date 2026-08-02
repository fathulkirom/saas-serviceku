<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomerSegment extends Model
{
    protected $fillable = ['name', 'key', 'description', 'rules', 'color', 'is_active', 'is_system'];
    protected $casts = ['rules' => 'json', 'is_active' => 'bool', 'is_system' => 'bool'];

    /** Apply segment rules to get matching customer IDs */
    public function getCustomerIds(): array
    {
        if (empty($this->rules)) return [];

        $query = Customer::query();

        foreach ($this->rules as $rule) {
            $field = $rule['field'] ?? null;
            $op = $rule['operator'] ?? '=';
            $value = $rule['value'] ?? null;

            match ($field) {
                'total_spending' => $query->whereHas('sales', fn($q) => $q->where('status', 'paid'), $op, $value),
                'service_count'  => $query->whereHas('services', fn($q) => $q->selectRaw('COUNT(*)'), $op, $value),
                'last_visit_days'=> $query->whereHas('services', fn($q) => $q->where('created_at', $op === 'gt' ? '<' : '>', now()->subDays((int)$value))),
                'is_member'      => $query->where('is_member', $value),
                default          => null,
            };
        }

        return $query->pluck('id')->toArray();
    }

    /** Seed default system segments */
    public static function seedSystem(): void
    {
        $segments = [
            ['name' => 'VIP', 'key' => 'vip', 'rules' => json_encode([['field' => 'total_spending', 'operator' => 'gt', 'value' => 5000000]]), 'color' => '#F59E0B', 'is_system' => true],
            ['name' => 'Loyal', 'key' => 'loyal', 'rules' => json_encode([['field' => 'service_count', 'operator' => 'gt', 'value' => 5]]), 'color' => '#10B981', 'is_system' => true],
            ['name' => 'Inactive', 'key' => 'inactive', 'rules' => json_encode([['field' => 'last_visit_days', 'operator' => 'gt', 'value' => 90]]), 'color' => '#9CA3AF', 'is_system' => true],
            ['name' => 'New', 'key' => 'new', 'rules' => json_encode([['field' => 'service_count', 'operator' => '=', 'value' => 1]]), 'color' => '#3B82F6', 'is_system' => true],
        ];
        foreach ($segments as $seg) {
            self::firstOrCreate(['key' => $seg['key']], $seg);
        }
    }
}
