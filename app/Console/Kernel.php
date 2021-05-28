<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('word:day')->everyMinute();
        // $schedule->exec("C:\wamp64\bin\php\php7.2.30\php.exe artisan schedule:run")->everyMinute();
        // $schedule->call(function () {
        //     $age = DB::select("SELECT id,age FROM users WHERE DATE_FORMAT(dob,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')");
        //     foreach ($age as $key => $value) {
        //         $age = $value->age + 1;
        //         DB::table('users')->where('id',$value->id)->update(['age' => $age]);
        //     }
        // })->daily();
        // $schedule->call(function () {
        //     $userIds = DB::table('users')->get(['id'])->toArray();
        //     foreach ($userIds as $key => $value) {
        //         $ratings = DB::table("user_ratings")->where("rated_user_id",$value->id)->avg("rating");
        //         DB::table('users')->where('id',$value->id)->update(['trust_rating'=>$ratings]);
        //     }
        // })->everyThirtyMinutes();
        // $schedule->call(function () {
        //     DB::table('categories')->insert(['cat_name' => "hnji"]);
        // })->everyMinute();
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
