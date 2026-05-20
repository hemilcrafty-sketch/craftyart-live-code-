<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UserMessage implements ShouldBroadcast
{
    use SerializesModels;

    public $userId;
    public $message;

    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Each user has their own private channel
        return new Channel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'message';
    }
}
