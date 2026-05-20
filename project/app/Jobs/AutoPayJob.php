<?php

namespace App\Jobs;

use App\Http\Controllers\Payment\Gateways\PhonePeGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoPayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $merchantOrderId;
    private string $merchantSubscriptionId;
    private int $amount;

    public function __construct(
        string $merchantOrderId,
        string $merchantSubscriptionId,
        int    $amount,
    )
    {
        $this->merchantOrderId = $merchantOrderId;
        $this->merchantSubscriptionId = $merchantSubscriptionId;
        $this->amount = $amount;
    }

    public function handle(): void
    {
        PhonePeGateway::triggerManualDebit($this->merchantOrderId, $this->merchantSubscriptionId, $this->amount);
    }

}
