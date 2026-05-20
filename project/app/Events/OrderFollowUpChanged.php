<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderFollowUpChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $followupCall;
    public $followupNote;
    public $followupLabel;
    public $followBy;
    public $empId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($orderId, $followupCall, $followupNote, $followupLabel, $followBy, $empId)
    {
        $this->orderId = $orderId;
        $this->followupCall = $followupCall;
        $this->followupNote = $followupNote;
        $this->followupLabel = $followupLabel;
        $this->followBy = $followBy;
        $this->empId = $empId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('orders');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'order-followup-changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'order_id' => $this->orderId,
            'followup_call' => $this->followupCall,
            'followup_note' => $this->followupNote,
            'followup_label' => $this->followupLabel,
            'follow_by' => $this->followBy,
            'emp_id' => $this->empId,
        ];
    }
}
