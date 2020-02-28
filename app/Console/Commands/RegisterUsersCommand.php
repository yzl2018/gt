<?php

namespace App\Console\Commands;

use App\Http\Toolkit\CommunicateWithB;
use App\Models\UsersModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterUsersCommand extends Command
{

	use CommunicateWithB;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-register users of database';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->b_security = config('system.security');
		$this->md5_sec_key = config('system.mall.md5_sec_key');

		if(env('APP_LIVE')){
			$this->b_web_site = config('system.b_web_site.live');
			$this->sys_security_code = config('system.security_code.live');
			$this->do_pay_uri = config('system.mall.live_url').config('system.mall.do_pay_uri');
		}
		else{
			$this->b_web_site = config('system.b_web_site.demo');
			$this->sys_security_code = config('system.security_code.demo');
			$this->do_pay_uri = config('system.mall.demo_url').config('system.mall.do_pay_uri');
		}
	}

	/**
	 * 定义睡眠等待时间
	 *
	 * @var int
	 */
    private $micro_seconds = 1000000;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

    	//$old_mall_code = $this->ask('What\'s the code of the old mall?');

    	$todo_number = $this->ask('How many rows of user data do you want to process at a time?');

    	$users = DB::table('old_system_users')->limit($todo_number)->get();

    	//$users = UsersModel::where('user_code','like','%'.$old_mall_code.'%')->orWhereNull('user_code')->limit($todo_number)->get();

    	if(count($users) == 0){
			$this->warn('No user data to be re-registered');
		}

		else{
    		$total = count($users);$success = 0;$fail = 0;$i = 0;$exits = 0;
    		foreach ($users as $user){
    			$user_exists = DB::table('users')->where('email',$user->email)->count();
    			if(!$user_exists){
					usleep($this->micro_seconds);

					$i++;
					$resp = $this->registerUser($user->user_type_code,$user->safety_code,$user->email);
					if(is_null(json_decode($resp))){
						Log::error(__FUNCTION__,['register exception'=>$resp]);
						$this->error('request B system exception:'.$resp);
					}
					else{
						$res = json_decode($resp,true);
						if($res['code'] == $this->b_return_success_code){

							$params = [
								'code'	=> $user->code,
								'name'	=> $user->name,
								'email'	=> $user->email,
								'password'	=> $user->password,
								'operate_password'	=> $user->operate_password,
								'user_type_code'	=> $user->user_type_code,
								'user_code'		=> $res['data']['ucode'],
								'safety_code'	=> $user->safety_code,
								'active_status'	=> 1,
								'language_type_code'	=> 'CN',
								'created_at'	=> date('Y-m-d H:i:s')
							];

							if($user->phone){
								$params['phone'] = $user->phone;
							}

							$isUpdate = DB::table('users')->insertGetId($params);
							if($isUpdate){
								$this->info($i.') '.$user->email.' register successful ucode:'.$params['user_code']);
								$success++;
							}else{
								$fail++;
							}

						}
					}
				}
				else{
    				$exits++;
				}
			}

			$this->info('Register total users:'.$total.' ,Successful:'.$success.' ,failed:'.$fail.',exists:'.$exits);

		}

    }
}
