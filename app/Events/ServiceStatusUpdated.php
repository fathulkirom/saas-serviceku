<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $serviceId;
    public $trackingCode;
    public $status;
    public $message;

    public function __construct($serviceId, $trackingCode, $status, $message)
    {
        $this->serviceId = $serviceId;
        $this->trackingCode = $trackingCode;
        $this->status = $status;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . tenant('id')),
        ];
    }

    public function broadcastAs(): string
    {
        return 'service.updated';
    }
}
