<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CustomerTag extends Model
{
    protected $fillable = ['name', 'color', 'icon', 'is_system'];

    public function customers() { return $this->belongsToMany(Customer::class, 'customer_tag_pivot'); }

    /** Built-in system tags */
    public static function seedSystem(): void
    {
        $tags = [
            ['name' => 'VIP', 'color' => '#F59E0B', 'icon' => 'star', 'is_system' => true],
            ['name' => 'Corporate', 'color' => '#3B82F6', 'icon' => 'building', 'is_system' => true],
            ['name' => 'Reseller', 'color' => '#8B5CF6', 'icon' => 'repeat', 'is_system' => true],
            ['name' => 'Frequent Repair', 'color' => '#EF4444', 'icon' => 'wrench', 'is_system' => true],
            ['name' => 'Complaint Risk', 'color' => '#DC2626', 'icon' => 'alert-triangle', 'is_system' => true],
            ['name' => 'New Customer', 'color' => '#10B981', 'icon' => 'user-plus', 'is_system' => true],
            ['name' => 'Blacklist', 'color' => '#374151', 'icon' => 'slash', 'is_system' => true],
        ];
        foreach ($tags as $tag) {
            self::firstOrCreate(['name' => $tag['name']], $tag);
        }
    }
}
