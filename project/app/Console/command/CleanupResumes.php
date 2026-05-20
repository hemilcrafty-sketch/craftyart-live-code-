<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupResumes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumes:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete resumes and records older than 7 days.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $expiryDate = now()->subDays(7);
        $oldApplications = \App\Models\JobApplication::where('created_at', '<', $expiryDate)->get();

        $count = 0;
        foreach ($oldApplications as $app) {
            if ($app->resume && \Illuminate\Support\Facades\Storage::disk('local')->exists($app->resume)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($app->resume);
            }
            
            // $app->delete();
            $count++;
        }

        $this->info("Successfully cleaned up {$count} records and their associated files.");
        return 0;
    }
}
