<?php

namespace App\Jobs;

use App\Http\Controllers\Jobs\AiCaricatureJobController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AICaricatureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(public int $job_id)
    {

    }

    public function handle(): void
    {
        (new AiCaricatureJobController())->instantSend($this->job_id);
    }
}
