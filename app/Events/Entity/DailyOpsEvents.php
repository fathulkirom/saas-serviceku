<?php

namespace App\Events\Entity;

use App\Models\Tenant\WorkOrder;
use App\Models\Tenant\Service;
use App\Models\Tenant\PartBooking;
use App\Models\Tenant\ServiceReopen;
use App\Models\Tenant\PriceChangeRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// RepairStarted moved to TechnicianWorkflowEvents.php (Sprint 7.5F — duplicate event cleanup)
class RepairPaused { use Dispatchable, SerializesModels; public function __construct(public readonly WorkOrder $workOrder) {} }
class RepairResumed { use Dispatchable, SerializesModels; public function __construct(public readonly WorkOrder $workOrder) {} }
class RepairFinished { use Dispatchable, SerializesModels; public function __construct(public readonly WorkOrder $workOrder) {} }
class WorklogCreated { use Dispatchable, SerializesModels; public function __construct(public readonly WorkOrder $workOrder, public readonly string $description) {} }
class RepairPhotoUploaded { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service, public readonly string $phase) {} }
class BookingCreated { use Dispatchable, SerializesModels; public function __construct(public readonly PartBooking $booking) {} }
class BookingExpired { use Dispatchable, SerializesModels; public function __construct(public readonly PartBooking $booking) {} }
class PriceChanged { use Dispatchable, SerializesModels; public function __construct(public readonly PriceChangeRequest $change) {} }
class PriceApproved { use Dispatchable, SerializesModels; public function __construct(public readonly PriceChangeRequest $change) {} }
class ServiceReopened { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceReopen $reopen) {} }
class ServiceLocked { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service) {} }
class ServiceUnlocked { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service) {} }
