<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Plan;

class PlanTest extends TestCase
{
    private function makePlan(array $features, ?string $businessType = null): Plan
    {
        return new Plan([
            'name' => 'Test Plan',
            'slug' => 'test',
            'features' => $businessType
                ? [$businessType => $features]
                : $features,
        ]);
    }

    public function test_fillable_contains_expected_fields()
    {
        $plan = new Plan();
        foreach (['name', 'slug', 'price', 'features', 'is_active'] as $field) {
            $this->assertTrue(in_array($field, $plan->getFillable()), "fillable harus berisi {$field}");
        }
    }

    public function test_feature_access_level_full()
    {
        $plan = $this->makePlan(['services' => 'full']);
        $this->assertEquals('full', $plan->featureAccessLevel('services'));
        $this->assertTrue($plan->hasFeature('services'));
    }

    public function test_feature_access_level_boolean_true()
    {
        $plan = $this->makePlan(['customers' => true]);
        $this->assertEquals('full', $plan->featureAccessLevel('customers'));
    }

    public function test_feature_access_level_read_only()
    {
        $plan = $this->makePlan(['reports' => 'read_only']);
        $this->assertEquals('read_only', $plan->featureAccessLevel('reports'));
        $this->assertFalse($plan->hasFeature('reports'));
    }

    public function test_feature_access_level_none()
    {
        $plan = $this->makePlan(['users' => 'none']);
        $this->assertEquals('none', $plan->featureAccessLevel('users'));
    }

    public function test_feature_access_level_missing_returns_none()
    {
        $plan = $this->makePlan(['services' => 'full']);
        $this->assertEquals('none', $plan->featureAccessLevel('unknown_feature'));
    }

    public function test_feature_access_level_business_type_nested()
    {
        $plan = $this->makePlan([
            'services' => 'full',
            'multi_branch' => 'none',
        ], 'service_center');

        $this->assertEquals('full', $plan->featureAccessLevel('services', 'service_center'));
        $this->assertEquals('none', $plan->featureAccessLevel('multi_branch', 'service_center'));
    }

    public function test_max_value_returns_numeric_limit()
    {
        $plan = $this->makePlan(['max_users' => 10]);
        $this->assertEquals(10, $plan->maxValue('max_users'));
    }
}
