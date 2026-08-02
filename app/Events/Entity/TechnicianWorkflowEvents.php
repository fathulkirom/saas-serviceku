<?php

namespace App\Events\Entity;

use App\Models\Tenant\WorkOrder;
use App\Models\Tenant\ServiceDiagnosis;
use App\Models\Tenant\ServiceQuotation;
use App\Models\Tenant\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkOrderAssigned { use Dispatchable, SerializesModels; public function __construct(public readonly WorkOrder $workOrder) {} }
class TechnicianAccepted { use Dispatchable, SerializesModels; public function __construct(public readonly WorkOrder $workOrder) {} }
class DiagnosisCompleted { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceDiagnosis $diagnosis) {} }
class QuotationCreated { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceQuotation $quotation) {} }
class CustomerApprovedRepair { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceQuotation $quotation) {} }
class RepairStarted { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service) {} }
class RepairCompleted { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service) {} }
class QCCompleted { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service) {} }
