<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Plan;

class TenantTest extends TestCase
{
    public function test_is_trial_true_when_status_trial()
    {
        $tenant = new Tenant(['subscription_status' => 'trial']);
        $this->assertTrue($tenant->isTrial());
    }

    public function test_is_trial_false_for_other_status()
    {
        $tenant = new Tenant(['subscription_status' => 'active']);
        $this->assertFalse($tenant->isTrial());
    }

    public function test_trial_ended_when_past_trial_date()
    {
        $tenant = new Tenant([
            'trial_ends_at' => now()->subDay(),
        ]);
        $this->assertTrue($tenant->trialEnded());
    }

    public function test_trial_not_ended_when_future_date()
    {
        $tenant = new Tenant([
            'trial_ends_at' => now()->addDay(),
        ]);
        $this->assertFalse($tenant->trialEnded());
    }

    public function test_is_subscription_active()
    {
        // status active -> true (implementasi tidak cek subscription_ends_at)
        $active = new Tenant(['subscription_status' => 'active']);
        $this->assertTrue($active->isSubscriptionActive());

        // trial belum berakhir -> true
        $trial = new Tenant([
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDay(),
        ]);
        $this->assertTrue($trial->isSubscriptionActive());

        // status non-aktif -> false
        $inactive = new Tenant(['subscription_status' => 'suspended']);
        $this->assertFalse($inactive->isSubscriptionActive());
    }

    public function test_get_and_set_business_type()
    {
        $tenant = $this->setUpTenant();
        $tenant->setBusinessType('full_service');
        $this->assertEquals('full_service', $tenant->getBusinessType());
    }

    public function test_get_business_types_returns_list()
    {
        $types = Tenant::getBusinessTypes();
        $this->assertIsArray($types);
        $this->assertNotEmpty($types);
        $this->assertArrayHasKey('full_service', $types);
    }

    public function test_get_business_type_label()
    {
        $tenant = $this->setUpTenant();
        $tenant->setBusinessType('full_service');
        $label = $tenant->getBusinessTypeLabel();
        $this->assertNotEmpty($label);
        $this->assertEquals('🔧 Servis & Jual Sparepart', $label);
    }
}
