<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoUnlockUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unlock:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto unlock users overnight.';

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
     * @return mixed
     */
    public function handle()
    {

    	//TODO 将登陆失败次数大于0的用户，登陆失败次数均恢复为0
		try{
			DB::table('users')->where('login_fail_times','>',0)->update(['login_fail_times'=>0]);
		}
		catch (\Exception $e){
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				'Auto unlock users overnight' => $e->getMessage(),
				'trace' => $e->getTrace()
			]);
		}

		return;

    }
}
