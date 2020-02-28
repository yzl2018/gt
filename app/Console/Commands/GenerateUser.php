<?php

namespace App\Console\Commands;

use App\Http\Toolkit\AutoGenerate;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\DataValidator;
use App\Models\CodeConfigModel;
use App\Models\UsersModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateUser extends Command
{
	use DataValidator,AutoGenerate,CommunicateWithB;

    /**
     * The name and signature of the console command.
	 * 45072：管理员
	 * 45073：客服
	 * 45074：客户
     *
     * @var string
     */
    protected $signature = 'create:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user';

	/**
	 * 用户类型数组
	 *
	 * @var array
	 */
    private $user_types = [
    	'Administrator' => 0xB010,
		'Customer Service' => 0xB011,
		'Customer' => 0xB012
	];

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

		$user_type = $this->choice('What type of user do you want to create?',['Administrator','Customer Service','Customer']);
        if(!array_key_exists($user_type,$this->user_types)){
        	$this->error('Invalid user type');
		}
        else{
        	$info = "You are creating ".$user_type;
        	$this->info($info);
        	$name = $this->ask('What is the '.$user_type.'`s name?');
        	$hasTheName = UsersModel::where('name',$name)->count() > 0;
        	if($hasTheName){
        		$this->error('Sorry,the name is already exists.');
			}else{
				$email = $this->ask('What is the '.$user_type.'`s email?');
				if(!$this->EmailValidator($email)){
					$this->error('Please enter the correct format email address!');
				}else{
					$hasTheEmail = UsersModel::where('email',$email)->count() > 0;
					if($hasTheEmail){
						$this->error('Sorry, the email has been registered!');
					}else{
						$phone = $this->ask('What is the '.$user_type.'`s phone?');
						if(!$this->PhoneValidator($phone)){
							$this->error('Please enter the correct format phone number!');
						}else{
							$hasThePhone = UsersModel::where('phone',$phone)->count() > 0;
							if($hasThePhone){
								$this->error('Sorry,the phone has been registered!');
							}else{
								$password = $this->ask('What is the '.$user_type.'`s login password?');
								if(!$this->PasswordValidator($password)){
									$this->error('The password must contain letters and numbers and cannot be less than 6 digits!');
								}else{
									$params = [
										'code'				=> CodeConfigModel::getUniqueCode('user'),
										'name'				=> $name,
										'email'				=> $email,
										'phone'				=> $phone,
										'password'			=> bcrypt(md5($password)),
										'user_type_code'	=> $this->user_types[$user_type],
										'safety_code'		=> sha1(time().$this->create_password()),
										'latest_login_time'	=> time(),
										'created_at'		=> date('Y-m-d H:i:s'),
										'updated_at'		=> date('Y-m-d H:i:s'),
										'active_status'		=> 1
									];
									if($this->user_types[$user_type] == 0xB012){
										$params['operate_password'] = md5('123456');//客户的默认操作密码设置为:123456
									}else{
										$params['operate_password'] = md5('147258369');//客户的默认操作密码设置为:147258369
									}

									$resp = $this->registerUser($this->user_types[$user_type],$params['safety_code'],$email);
									if(is_null(json_decode($resp))){
										Log::error(__FUNCTION__,$resp);
										$this->error('request B system exception:'.$resp);
									}
									else{
										$res = json_decode($resp,true);
										if($res['code'] == $this->b_return_success_code){
											$params['user_code'] = $res['data']['ucode'];
											$isCreate = UsersModel::insertGetId($params);
											if($isCreate){
												$this->info('Create the '.$user_type.' successfully.');
											}else{
												$this->error('Create the '.$user_type.' failed.');
											}
										}
										else{
											$this->error('request B system error:'.$res['message']);
										}
									}

								}
							}
						}
					}
				}
			}
		}

    }
}
