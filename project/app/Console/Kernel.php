<?php

namespace App\Console;

use App\Http\Controllers\Jobs\ProcessCheckoutDropController;
use App\Http\Controllers\Jobs\RecentExpiredController;
use App\Http\Controllers\Jobs\WpFeedbackApiController;
use App\Http\Controllers\Api\NotificationController;
use App\Jobs\ProcessCheckoutDropJob;
use App\Jobs\RecentExpiredJob;
use App\Models\Template;
use App\Models\TransactionLog;
use App\Models\UserData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

//        $schedule->call(function () {
//            Template::query()->update(array('trending_views' => 0));
//        })->weekly();
//
//        $schedule->call(function () {
//            $from= Carbon::now()->subDays(10);
//            Template::where("created_at","<", $from)->update(array('latest' => '0'));
//        })->hourly();
//
//        $schedule->call(function () {
//            $singleDatas = TransactionLog::where("status", "1")->get();
//            if($singleDatas != null) {
//                foreach ($singleDatas as $singleDataRow) {
//                    $user_data = UserData::where("uid", $singleDataRow->user_id)->first();
//                    $toDate = Carbon::parse($singleDataRow->created_at)->addDays($user_data->total_validity);
//                    $diffInDays = $singleDataRow->created_at->diffInDays(Carbon::now());
//                    $daysLeft = $user_data->total_validity - $diffInDays;
//                    UserData::where('uid', $user_data->uid)->update(['validity' => $daysLeft]);
//                    if($daysLeft < 1) {
//                        TransactionLog::where("user_id", $user_data->uid)->update(array('status' => '0'));
//                        UserData::where("uid", $user_data->uid)->update(array('is_premium' => '0', 'validity' => '0'));
//                        $user_data = UserData::where("uid", $user_data->uid)->first();
//                    }
//                }
//            }
//        })->hourly();

        $schedule->call(function () {
            app(ProcessCheckoutDropController::class)->instantDropOut();
        })->everyFiveMinutes();

        $schedule->call(function () {
            app(ProcessCheckoutDropController::class)->startProcess();
        })->dailyAt('10:00');

        $schedule->call(function () {
            app(RecentExpiredController::class)->startProcess();
        })->dailyAt('10:00');

        $schedule->call(function () {
            app(WpFeedbackApiController::class)->startProcess();
        })->dailyAt('10:00');

        // $schedule->command(NotificationController::sendMorningNotification())->everyMinute();

        // $schedule->command(NotificationController::sendAfternoonNotification())->dailyAt('13:00');

        // $schedule->command(NotificationController::sendEveningNotification())->dailyAt('16:30');

        // $schedule->command(NotificationController::sendQuoteNotification())->dailyAt('21:00');

        // $schedule->command(NotificationController::sendNightNotification())->dailyAt('22:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
