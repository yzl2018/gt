<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    	//Log::debug('Kernel command',[
         //   'laravel task ' => date('Y-m-d H:i:s')
        //]);

        //每分钟运行一次设置过期订单的任务
        $schedule->command('check:orders')->everyMinute();
		
		//每5分钟运行一次检测邮局通道的任务
		$schedule->command('check:channels')->everyFiveMinutes()->withoutOverlapping();

        //每小时运行一次矫正充值卡状态的任务
        $schedule->command('correct:cards')->everyThirtyMinutes();

    	//每天 23:00 执行清空日志命令
        $schedule->command('clear:log')->dailyAt('23:00')->withoutOverlapping();
		
		//每天23:00 执行自动解锁用户的命令
		$schedule->command('unlock:users')->dailyAt('23:30')->withoutOverlapping();

    	//运行队列任务
    	$schedule->command('queue:work --tries 3')->everyMinute()->withoutOverlapping();

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
