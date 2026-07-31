<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WhatsAppService;

class WhatsAppServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_is_configured_false_without_config()
    {
        $service = new WhatsAppService();
        $this->assertFalse($service->isConfigured());
    }

    public function test_send_returns_false_when_not_configured()
    {
        $service = new WhatsAppService();
        $this->assertFalse($service->send('08123456789', 'Halo'));
    }

    public function test_send_template_returns_false_without_config()
    {
        $service = new WhatsAppService();
        $this->assertFalse($service->sendTemplate('welcome_template', ['name' => 'Budi'], '08123456789'));
    }

    public function test_get_stats_defaults()
    {
        $service = new WhatsAppService();
        $this->assertSame(['success' => 0, 'failed' => 0], $service->getStats());
        $this->assertEquals(0.0, $service->getFailureRate());
    }
}
