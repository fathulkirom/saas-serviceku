<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\Customer;

class TenantCustomerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_can_create_customer()
    {
        $customer = $this->createCustomer();

        $this->assertNotNull($customer);
        $this->assertEquals('Test Customer', $customer->name);
        $this->assertEquals('08123456789', $customer->phone);
    }

    public function test_can_update_customer()
    {
        $customer = $this->createCustomer();

        $customer->update(['name' => 'Updated Customer', 'phone' => '08999999999']);

        $fresh = $customer->fresh();
        $this->assertEquals('Updated Customer', $fresh->name);
        $this->assertEquals('08999999999', $fresh->phone);
    }

    public function test_can_delete_customer()
    {
        $customer = $this->createCustomer();
        $id = $customer->id;

        $customer->delete();

        $this->assertNull(Customer::find($id));
    }

    public function test_can_register_customer_as_member()
    {
        $customer = $this->createCustomer();

        $customer->update(['is_member' => true, 'card_number' => 'MBR-001']);

        $fresh = $customer->fresh();
        $this->assertTrue($fresh->is_member);
        $this->assertEquals('MBR-001', $fresh->card_number);
    }

    public function test_customer_can_have_services()
    {
        $customer = $this->createCustomer();
        $service = $this->createService(['customer_id' => $customer->id]);

        $this->assertEquals(1, $customer->fresh()->services()->count());
        $this->assertEquals($customer->id, $service->customer_id);
    }
}
