<?php

namespace App\Events\Entity;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;

class ServiceCreated  { use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\Service $service) {} }
class ServiceAssigned { use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\Service $service) {} }
class ServiceCompleted{ use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\Service $service) {} }
class ServiceCancelled { use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\Service $service) {} }

class WorkOrderCreated  { use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\WorkOrder $workOrder) {} }
class WorkOrderCompleted{ use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\WorkOrder $workOrder) {} }

class PaymentReceived   { use Dispatchable, SerializesModels; public function __construct(
    public readonly Tenant\Sale $sale, public readonly float $amount, public readonly string $method) {} }

class CustomerCreated   { use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\Customer $customer) {} }
class CustomerMerged    { use Dispatchable, SerializesModels; public function __construct(
    public readonly Tenant\Customer $into, public readonly Tenant\Customer $from) {} }
class BranchCreated     { use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\Branch $branch) {} }
class UserInvited       { use Dispatchable, SerializesModels; public function __construct(public readonly Tenant\User $user) {} }

class AttachmentUploaded{ use Dispatchable, SerializesModels; public function __construct(
    public readonly string $entityType, public readonly int $entityId, public readonly string $filePath) {} }
