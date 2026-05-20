<?php

namespace App\Jobs;

use App\Http\Controllers\Payment\Gateways\PhonePeGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PreDebitNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $merchantSubscriptionId;
    private int $amount;
    private bool $autoDebit;

    public function __construct(
        string $merchantSubscriptionId,
        int $amount,
        bool $autoDebit = true
    )
    {
        $this->merchantSubscriptionId = $merchantSubscriptionId;
        $this->amount = $amount;
        $this->autoDebit = $autoDebit;
    }

    public function handle(): void
    {
        PhonePeGateway::sendPreDebitNotification($this->merchantSubscriptionId, $this->amount, $this->autoDebit);
    }

}
