<?php

namespace App\Events\Entity;

use App\Models\Tenant\ServiceRequiredPart;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartEdited { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class PriorityChanged { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class PartReserved { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class PartAddedToInvoice { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class PartRemovedFromInvoice { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class InvoiceConfirmed { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class InvoicePaid { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class StockReserved { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class StockReleased { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
class WaitingPurchase { use Dispatchable, SerializesModels; public function __construct(public readonly ServiceRequiredPart $part) {} }
