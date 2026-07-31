<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\GoogleDrivePhotoService;
use App\Models\GoogleDriveToken;
use Illuminate\Support\Facades\DB;
use ReflectionProperty;

class GoogleDrivePhotoServiceTest extends TestCase
{
    private function createTenant(): string
    {
        $id = 'photo-tenant-' . uniqid();
        DB::connection('central')->table('tenants')->insert([
            'id' => $id,
            'tenant_name' => 'Photo Tenant',
            'plan_id' => 1,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }

    private function readProperty(object $obj, string $prop): mixed
    {
        $ref = new ReflectionProperty($obj, $prop);
        return $ref->getValue($obj);
    }

    public function test_constructor_without_tenant_has_no_client()
    {
        $service = new GoogleDrivePhotoService();

        $this->assertNull($this->readProperty($service, 'token'));
        $this->assertNull($this->readProperty($service, 'client'));
        $this->assertNull($this->readProperty($service, 'driveService'));
    }

    public function test_constructor_with_tenant_without_token_has_no_client()
    {
        $tenantId = $this->createTenant();
        $service = new GoogleDrivePhotoService($tenantId);

        $this->assertNull($this->readProperty($service, 'token'));
        $this->assertNull($this->readProperty($service, 'client'));
    }

    public function test_constructor_with_unexpired_token_initializes_client()
    {
        $tenantId = $this->createTenant();
        GoogleDriveToken::create([
            'tenant_id' => $tenantId,
            'access_token' => 'ya29.stub-token',
            'refresh_token' => '1//refresh',
            'token_expiry' => now()->addHour(),
            'root_folder_id' => 'folder-1',
        ]);

        $service = new GoogleDrivePhotoService($tenantId);

        $this->assertNotNull($this->readProperty($service, 'token'));
        $this->assertNotNull($this->readProperty($service, 'client'));
        $this->assertNotNull($this->readProperty($service, 'driveService'));
    }

    public function test_get_auth_url_returns_empty_when_client_id_missing()
    {
        config(['services.google.client_id' => null]);
        $service = new GoogleDrivePhotoService();

        $this->assertEquals('', $service->getAuthUrl());
    }

    public function test_upload_returns_null_without_drive_service()
    {
        $service = new GoogleDrivePhotoService();
        $result = $service->upload('/tmp/nonexistent.jpg', 'photo.jpg');

        $this->assertNull($result);
    }
}
