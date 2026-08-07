<?php

namespace App\Events\Entity;

use App\Models\Tenant\ServiceWarrantyClaim;
use App\Models\Tenant\ServiceDiagnosisHistory;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarrantyClaimCreated { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceWarrantyClaim $claim) {} }
class WarrantyClaimApproved { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceWarrantyClaim $claim) {} }
class WarrantyClaimRejected { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceWarrantyClaim $claim) {} }
class WarrantyClaimResolved { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceWarrantyClaim $claim) {} }
class WarrantyRefunded { use Dispatchable, SerializesModels; public function __construct(public readonly \App\Models\Tenant\SaleRefund $refund) {} }
class DiagnosisRevisionRecorded { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceDiagnosisHistory $history) {} }
class RepairReopened { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceWarrantyClaim $claim) {} }
class DeviceUnclaimed { use Dispatchable, SerializesModels; public function __construct(public readonly int $serviceId, public readonly int $daysWaiting) {} }
