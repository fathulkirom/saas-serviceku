<?php

namespace App\Events\Entity;

use App\Models\Tenant\StockAdjustment;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\StockTransfer;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockAdjusted { use Dispatchable, SerializesModels; public function __construct(public readonly StockAdjustment $adjustment) {} }
class StockOpnameCompleted { use Dispatchable, SerializesModels; public function __construct(public readonly StockOpname $opname) {} }
class StockTransferred { use Dispatchable, SerializesModels; public function __construct(public readonly StockTransfer $transfer) {} }
class SerialAssigned { use Dispatchable, SerializesModels; public function __construct(public readonly ProductSerial $serial) {} }
class TechnicianStockTransferred { use Dispatchable, SerializesModels; public function __construct(public readonly Product $product, public readonly int $technicianId, public readonly int $quantity) {} }
