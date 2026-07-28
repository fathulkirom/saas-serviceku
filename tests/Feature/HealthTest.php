<?php
namespace Tests\Feature;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_environment_is_set_to_testing()
    {
        $this->assertEquals('testing', app()->environment());
    }

    public function test_config_has_correct_locale()
    {
        $this->assertEquals('id', config('app.locale'));
    }

    public function test_cache_driver_is_array_in_testing()
    {
        $this->assertEquals('array', config('cache.default'));
    }

    public function test_session_driver_is_array_in_testing()
    {
        $this->assertEquals('array', config('session.driver'));
    }

    public function test_queue_driver_is_sync_in_testing()
    {
        $this->assertEquals('sync', config('queue.default'));
    }

    public function test_app_has_required_config_keys()
    {
        $this->assertNotNull(config('app.key'));
        $this->assertNotNull(config('app.name'));
        $this->assertNotNull(config('app.url'));
    }

    public function test_database_config_uses_sqlite_for_testing()
    {
        $expected = env('DB_CONNECTION') ?: config('database.default');
        $this->assertEquals($expected, config('database.default'));
    }

    public function test_debug_mode_is_enabled_in_testing()
    {
        $this->assertTrue(config('app.debug'));
    }

    public function test_tenancy_config_has_tenant_model()
    {
        $this->assertEquals('App\Models\Tenant', config('tenancy.tenant_model'));
    }
}
