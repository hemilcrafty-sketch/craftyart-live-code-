<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ForceLogout implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $userId;
    public $deviceId;

    public function __construct($userId, $deviceId)
    {
        $this->userId = $userId;
        $this->deviceId = $deviceId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'force.logout';
    }
}
