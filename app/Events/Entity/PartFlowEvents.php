<?php

namespace App\Events\Entity;

use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServicePartReturn;
use App\Models\Tenant\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartRequested { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class PartRequestCancelled { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class PartUsed { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class PartReturned { use Dispatchable, SerializesModels; public function __construct(public readonly ServicePartReturn $return) {} }
class ServiceProfitCalculated { use Dispatchable, SerializesModels; public function __construct(public readonly Service $service, public readonly float $profit) {} }
