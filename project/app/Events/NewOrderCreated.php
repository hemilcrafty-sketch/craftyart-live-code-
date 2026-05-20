<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewOrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(Order $order)
    {
        $this->order = $order->load('user'); // Load user relationship
    }

    public function broadcastOn()
    {
        return new Channel('orders');
    }

    public function broadcastAs()
    {
        return 'new-order-created';
    }

    public function broadcastWith()
    {
        try {
            // Simplified data structure for testing
            return [
                'order' => [
                    'id' => $this->order->id,
                    'user_id' => $this->order->user_id ?? 'unknown',
                    'amount' => $this->order->amount ?? '0',
                    'amount_with_symbol' => '₹' . ($this->order->amount ?? '0'),
                    'status' => $this->order->status ?? 'pending',
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'test' => 'simplified_event'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in NewOrderCreated broadcastWith', [
                'error' => $e->getMessage(),
                'order_id' => $this->order->id ?? 'unknown'
            ]);
            
            // Return minimal data if error occurs
            return [
                'order' => [
                    'id' => $this->order->id ?? 0,
                    'amount_with_symbol' => '₹0',
                    'status' => 'pending',
                    'test' => 'error_fallback'
                ]
            ];
        }
    }
}
