<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GoogleDriveToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class GoogleDriveTokenTest extends TestCase
{
    private function createTenant(): string
    {
        $id = 'gdrive-tenant-' . uniqid();
        DB::connection('central')->table('tenants')->insert([
            'id' => $id,
            'tenant_name' => 'GDrive Tenant',
            'plan_id' => 1,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }

    public function test_fillable_contains_expected_fields()
    {
        $token = new GoogleDriveToken();
        foreach (['tenant_id', 'access_token', 'refresh_token', 'token_expiry', 'root_folder_id', 'connected_email'] as $field) {
            $this->assertTrue(in_array($field, $token->getFillable()), "fillable harus berisi {$field}");
        }
    }

    public function test_create_token_for_tenant()
    {
        $tenantId = $this->createTenant();
        $token = GoogleDriveToken::create([
            'tenant_id' => $tenantId,
            'access_token' => 'ya29.abc123',
            'refresh_token' => '1//refresh-token',
            'root_folder_id' => 'folder-1',
            'connected_email' => 'owner@example.com',
        ]);

        $this->assertDatabaseHas('google_drive_tokens', [
            'id' => $token->id,
            'tenant_id' => $tenantId,
            'connected_email' => 'owner@example.com',
        ], 'central');
    }

    public function test_token_expiry_cast_to_carbon()
    {
        $tenantId = $this->createTenant();
        $token = GoogleDriveToken::create([
            'tenant_id' => $tenantId,
            'access_token' => 'tok',
            'token_expiry' => now()->addHour(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $token->token_expiry);
    }

    public function test_create_throws_on_duplicate_tenant_id()
    {
        $tenantId = $this->createTenant();
        GoogleDriveToken::create(['tenant_id' => $tenantId, 'access_token' => 'a']);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/UNIQUE|unique/i');
        GoogleDriveToken::create(['tenant_id' => $tenantId, 'access_token' => 'b']);
    }
}
