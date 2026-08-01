<?php

namespace Tests\Unit\Models;

use App\Models\Tenant\Service;
use PHPUnit\Framework\TestCase;

class ServiceTransitionTest extends TestCase
{
    public function test_can_transition_from_waiting_allocation_to_working(): void
    {
        $this->assertTrue(Service::canTransition(
            Service::STATUS_MENUNGGU_ALOKASI,
            Service::STATUS_DIKERJAKAN
        ));
    }

    public function test_cannot_transition_from_cancelled_to_working(): void
    {
        $this->assertFalse(Service::canTransition(
            Service::STATUS_CANCEL,
            Service::STATUS_DIKERJAKAN
        ));
    }

    public function test_cannot_transition_to_same_status(): void
    {
        $this->assertFalse(Service::canTransition(
            Service::STATUS_DIKERJAKAN,
            Service::STATUS_DIKERJAKAN
        ));
    }

    public function test_knows_all_declared_statuses(): void
    {
        $statuses = Service::allStatuses();

        $this->assertContains(Service::STATUS_MENUNGGU_ALOKASI, $statuses);
        $this->assertContains(Service::STATUS_SELESAI, $statuses);
        $this->assertContains(Service::STATUS_CLOSE, $statuses);
        $this->assertTrue(Service::isKnownStatus(Service::STATUS_INDENT));
        $this->assertFalse(Service::isKnownStatus('status_tidak_ada'));
    }

    public function test_instance_can_transition_to_uses_current_status(): void
    {
        $service = new Service();
        $service->status = Service::STATUS_ONPARTNER;

        $this->assertTrue($service->canTransitionTo(Service::STATUS_SELESAI));
        $this->assertFalse($service->canTransitionTo(Service::STATUS_DITERIMA));
    }
}
