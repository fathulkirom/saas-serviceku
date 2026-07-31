<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\FeatureFlagService;

class FeatureFlagServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        FeatureFlagService::resetCache();
    }

    protected function tearDown(): void
    {
        FeatureFlagService::resetCache();
        parent::tearDown();
    }

    public function test_get_all_flags_returns_expected_flags()
    {
        $flags = FeatureFlagService::getAllFlags();

        $this->assertArrayHasKey('registration', $flags);
        $this->assertArrayHasKey('two_factor_auth', $flags);
        $this->assertArrayHasKey('email_verification', $flags);
        $this->assertArrayHasKey('custom_fields', $flags);
        $this->assertArrayHasKey('maintenance_mode', $flags);
    }

    public function test_is_enabled_uses_default_when_not_set()
    {
        // registration default true
        $this->assertTrue(FeatureFlagService::isEnabled('registration'));
        // email_verification default false
        $this->assertFalse(FeatureFlagService::isEnabled('email_verification'));
        // flag tidak dikenal -> default true
        $this->assertTrue(FeatureFlagService::isEnabled('unknown_flag'));
    }

    public function test_set_and_is_enabled()
    {
        FeatureFlagService::set('email_verification', true);
        $this->assertTrue(FeatureFlagService::isEnabled('email_verification'));

        FeatureFlagService::set('email_verification', false);
        $this->assertFalse(FeatureFlagService::isEnabled('email_verification'));
    }

    public function test_set_unknown_flag_throws()
    {
        $this->expectException(\InvalidArgumentException::class);
        FeatureFlagService::set('not_a_real_flag', true);
    }

    public function test_set_many()
    {
        FeatureFlagService::setMany([
            'email_verification' => true,
            'custom_fields' => false,
        ]);

        $this->assertTrue(FeatureFlagService::isEnabled('email_verification'));
        $this->assertFalse(FeatureFlagService::isEnabled('custom_fields'));
    }

    public function test_all_returns_boolean_values()
    {
        FeatureFlagService::set('two_factor_auth', true);
        FeatureFlagService::set('maintenance_mode', true);

        $all = FeatureFlagService::all();
        $this->assertTrue($all['two_factor_auth']);
        $this->assertTrue($all['maintenance_mode']);
    }

    public function test_is_enabled_for_tenant_without_plan_feature()
    {
        // Tanpa plan feature, hanya cek global flag
        FeatureFlagService::set('custom_fields', true);
        $this->assertTrue(FeatureFlagService::isEnabledForTenant('custom_fields'));

        FeatureFlagService::set('custom_fields', false);
        $this->assertFalse(FeatureFlagService::isEnabledForTenant('custom_fields'));
    }
}
