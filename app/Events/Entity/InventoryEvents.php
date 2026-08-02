<?php

namespace App\Events\Entity;

use App\Models\Tenant\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockReceived { use Dispatchable, SerializesModels; public function __construct(public readonly Product $product, public readonly int $quantity) {} }
class StockUsed { use Dispatchable, SerializesModels; public function __construct(public readonly Product $product, public readonly int $quantity) {} }
class StockReturned { use Dispatchable, SerializesModels; public function __construct(public readonly Product $product, public readonly int $quantity) {} }
class LowStockDetected { use Dispatchable, SerializesModels; public function __construct(public readonly Product $product, public readonly int $currentStock, public readonly int $minStock) {} }
