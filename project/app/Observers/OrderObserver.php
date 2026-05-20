<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        // WebSocket broadcasting enabled for real-time updates
        // Only broadcast pending and failed orders (for main order list)
        // Success orders are shown in Purchase History only
        if ($order->is_deleted == 0 && in_array($order->status, ['pending', 'failed'])) {
            \Log::info('OrderObserver: Broadcasting new order', [
                'order_id' => $order->id, 
                'status' => $order->status,
                'payment_method' => 'PhonePe/Razorpay'
            ]);
            
            // Use direct HTTP API call instead of Pusher library
            \App\Http\Controllers\WebSocketBroadcastController::broadcastOrderCreatedDirect($order);
        } else if ($order->is_deleted == 0 && in_array($order->status, ['success', 'paid'])) {
            \Log::info('OrderObserver: Success order created (not broadcasting to main list)', [
                'order_id' => $order->id, 
                'status' => $order->status
            ]);
        }
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        // Check if status changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;
            
            \Log::info('OrderObserver: Order status changed', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            // If status changed from pending/failed to success/paid, broadcast removal
            if (in_array($oldStatus, ['pending', 'failed']) && !in_array($newStatus, ['pending', 'failed'])) {
                \Log::info('OrderObserver: Broadcasting order removal', ['order_id' => $order->id]);
                \App\Http\Controllers\WebSocketBroadcastController::broadcastOrderStatusChanged($order, $oldStatus, $newStatus);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        //
    }
}
