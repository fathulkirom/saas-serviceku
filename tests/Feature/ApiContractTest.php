<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiContractTest extends TestCase
{
    public function test_track_returns_404_json_on_central_domain()
    {
        $response = $this->getJson('/api/track/TEST123');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Tenant not found'])
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_services_returns_401_json_without_accept_header()
    {
        // Tanpa Accept: application/json — harus tetap 401 JSON, bukan redirect 302
        $response = $this->get('/api/services');

        $response->assertStatus(401);
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_services_returns_401_json_with_accept_header()
    {
        $response = $this->getJson('/api/services');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_services_returns_401_json_with_invalid_bearer_token()
    {
        // Token invalid harus 401 JSON, bukan 500 (butuh tabel personal_access_tokens)
        $response = $this->getJson('/api/services', [
            'Authorization' => 'Bearer invalid-token-xyz',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_service_returns_401_json_without_accept_header()
    {
        $response = $this->get('/api/services/1');

        $response->assertStatus(401);
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
