<?php

namespace App\Events\Entity;

use App\Models\Tenant\CustomerCommunication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerCommunicationSent
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly CustomerCommunication $communication) {}
}

class CustomerCommunicationFailed
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly CustomerCommunication $communication) {}
}
