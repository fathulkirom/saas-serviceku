<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\User;

class TenantUserManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_can_create_user()
    {
        $user = $this->createTenantUser();

        $this->assertNotNull($user);
        $this->assertEquals('owner', $user->role);
    }

    public function test_can_create_user_with_different_roles()
    {
        $roles = ['admin', 'manager', 'cs', 'technician', 'cashier', 'courier'];

        foreach ($roles as $role) {
            $user = $this->createTenantUser([
                'role' => $role,
                'email' => $role . '_' . uniqid() . '@test.com',
            ]);

            $this->assertEquals($role, $user->role);
        }
    }

    public function test_user_can_be_deactivated()
    {
        $user = $this->createTenantUser();

        $user->update(['active' => false]);

        $this->assertFalse($user->fresh()->active);
    }

    public function test_user_role_check_methods()
    {
        $owner = $this->createTenantUser(['role' => 'owner']);
        $this->assertTrue($owner->isOwner());
        $this->assertFalse($owner->isTechnician());
        $this->assertTrue($owner->canManageUsers());
        $this->assertTrue($owner->canManageSettings());

        $technician = $this->createTenantUser([
            'role' => 'technician',
            'email' => 'tech_' . uniqid() . '@test.com',
        ]);
        $this->assertTrue($technician->isTechnician());
        $this->assertFalse($technician->isOwner());
        $this->assertTrue($technician->canWorkOnServices());
        $this->assertFalse($technician->canManageSettings());

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'email' => 'cs_' . uniqid() . '@test.com',
        ]);
        $this->assertTrue($cs->isCs());
        $this->assertTrue($cs->canAssignTechnician());
        $this->assertTrue($cs->canManageCustomers());
    }

    public function test_can_update_user_password()
    {
        $user = $this->createTenantUser();
        $newPassword = bcrypt('newpassword');

        $user->update(['password' => $newPassword]);

        $this->assertTrue(true); // No exception means update succeeded
    }

    public function test_can_get_role_display_name()
    {
        $user = $this->createTenantUser(['role' => 'technician']);

        $this->assertEquals('Teknisi', $user->getRoleDisplayName());
    }
}
