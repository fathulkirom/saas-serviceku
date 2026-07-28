<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Services\GoogleDrivePhotoService;

class GoogleDriveServiceTest extends TestCase
{
    public function test_service_returns_false_when_not_connected()
    {
        $service = new GoogleDrivePhotoService();
        $this->assertFalse($service->isConnected());
    }

    public function test_get_connection_info_returns_null_when_not_connected()
    {
        $service = new GoogleDrivePhotoService();
        $this->assertNull($service->getConnectionInfo());
    }

    public function test_disconnect_does_not_throw_when_not_connected()
    {
        $service = new GoogleDrivePhotoService();
        $service->disconnect();
        $this->assertTrue(true);
    }
}
