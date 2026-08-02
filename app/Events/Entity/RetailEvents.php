<?php

namespace App\Events\Entity;

use App\Models\Tenant\Sale;
use App\Models\Tenant\CashierShift;
use App\Models\Tenant\SaleReturn;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCreated { use Dispatchable, SerializesModels; public function __construct(public readonly Sale $sale) {} }
class SalePaid { use Dispatchable, SerializesModels; public function __construct(public readonly Sale $sale) {} }
class SaleCancelled { use Dispatchable, SerializesModels; public function __construct(public readonly Sale $sale) {} }
class SaleRefunded { use Dispatchable, SerializesModels; public function __construct(public readonly Sale $sale) {} }
class SaleReturned { use Dispatchable, SerializesModels; public function __construct(public readonly SaleReturn $return) {} }
class ShiftOpened { use Dispatchable, SerializesModels; public function __construct(public readonly CashierShift $shift) {} }
class ShiftClosed { use Dispatchable, SerializesModels; public function __construct(public readonly CashierShift $shift) {} }
class DiscountApproved { use Dispatchable, SerializesModels; public function __construct(public readonly Sale $sale, public readonly float $amount) {} }
class SerialSold { use Dispatchable, SerializesModels; public function __construct(public readonly array $serialData) {} }
class SerialReturned { use Dispatchable, SerializesModels; public function __construct(public readonly array $serialData) {} }
