<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageGenerationStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public string $status;
    public string $message;
    public ?string $imageUrl;
    public string $userId;

    public function __construct(
        string  $status,
        string  $message,
        ?string $imageUrl,
        string  $userId
    )
    {
        $this->status = $status;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->userId = $userId;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'ImageGenerationStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'imageUrl' => $this->imageUrl,
        ];
    }
}
