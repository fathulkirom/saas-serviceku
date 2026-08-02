<?php

namespace App\Events\Entity;

use App\Models\Tenant\Customer;
use App\Models\Tenant\CustomerNote;
use App\Models\Tenant\CustomerComplaint;
use App\Models\Tenant\Device;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerNoteCreated { use Dispatchable, SerializesModels; public function __construct(public readonly CustomerNote $note) {} }
class CustomerComplaintCreated { use Dispatchable, SerializesModels; public function __construct(public readonly CustomerComplaint $complaint) {} }
class CustomerComplaintResolved { use Dispatchable, SerializesModels; public function __construct(public readonly CustomerComplaint $complaint) {} }

// Sprint 7.3E-H
class ChecklistResultCreated { use Dispatchable, SerializesModels; public function __construct(public readonly \App\Models\Tenant\Service $service) {} }
class ServiceSnapshotCreated { use Dispatchable, SerializesModels; public function __construct(public readonly \App\Models\Tenant\ServiceIntakeSnapshot $snapshot) {} }
class CustomerApprovedCondition { use Dispatchable, SerializesModels; public function __construct(public readonly \App\Models\Tenant\Service $service) {} }
class DeviceMatchedExisting { use Dispatchable, SerializesModels; public function __construct(public readonly \App\Models\Tenant\Device $device) {} }
