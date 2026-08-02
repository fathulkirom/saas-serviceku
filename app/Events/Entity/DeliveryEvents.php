<?php

namespace App\Events\Entity;

use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDelivery;
use App\Models\Tenant\ServiceWarranty;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceReadyPickup { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service) {} }
class PickupCompleted { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceDelivery $delivery) {} }
class PaymentVerified { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceDelivery $delivery) {} }
class ServiceDelivered { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service) {} }
class WarrantyCreated { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceWarranty $warranty) {} }
class WarrantyExpired { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceWarranty $warranty) {} }
