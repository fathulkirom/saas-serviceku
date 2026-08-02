<?php

namespace App\Events\Entity;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Request is created.
 * Subscribers: Automation, Timeline, Activity, Dashboard, Notification, Webhook
 */
class RequestCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly \App\Models\Tenant\Request $request,
    ) {}
}

class RequestUpdated
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly \App\Models\Tenant\Request $request) {}
}

class RequestCancelled
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly \App\Models\Tenant\Request $request) {}
}

class RequestCompleted
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly \App\Models\Tenant\Request $request) {}
}
