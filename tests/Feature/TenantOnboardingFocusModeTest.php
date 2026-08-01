<?php

namespace Tests\Feature;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Service;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TenantOnboardingFocusModeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_onboarding_focus_mode_is_true_for_new_tenant(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($owner);

        $response = $this->get(route('services.index'));

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Services/Index')
            ->where('onboarding_focus_mode', true)
        );
    }

    public function test_onboarding_focus_mode_is_false_after_data_exists(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        Customer::create([
            'name' => 'Pelanggan Pertama',
            'phone' => '081234567890',
            'branch_id' => $branch->id,
        ]);

        $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DITERIMA,
        ]);

        $this->actingAs($owner);

        $response = $this->get(route('services.index'));

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Services/Index')
            ->where('onboarding_focus_mode', false)
        );
    }
}
