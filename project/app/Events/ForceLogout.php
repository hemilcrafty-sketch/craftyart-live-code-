<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ForceLogout implements ShouldBroadcast
{
    use SerializesModels;

    public string $userId;
    public string $deviceId;

    public function __construct($userId, $deviceId)
    {
        $this->userId = $userId;
        $this->deviceId = $deviceId;
    }

    public function broadcastOn(): array
    {
        return [ new PrivateChannel('user-' . $this->userId) ];
    }

    public function broadcastAs(): string
    {
        return 'force.logout';
    }

    public function broadcastWith(): array
    {
        return [
            'userId' => $this->userId,
            'deviceId' => $this->deviceId,
            'message' => 'You have been logged out from another device.',
            'timestamp' => now()->toISOString(),
            'type' => 'force_logout'
        ];
    }
}
