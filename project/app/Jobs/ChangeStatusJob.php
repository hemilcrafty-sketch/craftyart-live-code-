<?php

namespace App\Jobs;

use App\Http\Controllers\EmailController;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::find($this->orderId);

        if ($order && $order->status === 'pending') {
            $order->status = 'failed';
            $order->save();
            EmailController::sendPurchaseDropoutEmail($order);
        }
    }
}
