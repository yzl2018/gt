<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
	use \App\Http\Toolkit\AutoGenerate,\App\Http\Toolkit\CommunicateWithB;
	/**
	 * 用户初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		//初始化一个管理员
		[
			'code'				=> '',
			'name'				=> 'Mall Administrator',
			'email'				=> 'sd288435to@163.com',
			'phone'				=> '13812345678',
			'password'			=> 'fc3dd96619f92c0f2cfb4acff438e4b9',//admin147258369
			'operate_password'	=> 'e40f01afbb1b9ae3dd6747ced5bca532',//147258369
			'user_type_code'	=> 0xB010,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],

		//初始化一个管理员
		[
			'code'				=> '',
			'name'				=> 'Super Admin',
			'email'				=> 'bp001@qq.com',
			'phone'				=> '13512345678',
			'password'			=> 'fc3dd96619f92c0f2cfb4acff438e4b9',//admin147258369
			'operate_password'	=> 'e40f01afbb1b9ae3dd6747ced5bca532',//147258369
			'user_type_code'	=> 0xB010,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],

		//初始化一个管理员
		[
			'code'				=> '',
			'name'				=> 'Admin1',
			'email'				=> 'bp002@qq.com',
			'phone'				=> '13612345678',
			'password'			=> 'fc3dd96619f92c0f2cfb4acff438e4b9',//admin147258369
			'operate_password'	=> 'e40f01afbb1b9ae3dd6747ced5bca532',//147258369
			'user_type_code'	=> 0xB010,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],

		//初始化一个管理员
		[
			'code'				=> '',
			'name'				=> 'Admin1',
			'email'				=> 'bp003@qq.com',
			'phone'				=> '13912345678',
			'password'			=> 'fc3dd96619f92c0f2cfb4acff438e4b9',//admin147258369
			'operate_password'	=> 'e40f01afbb1b9ae3dd6747ced5bca532',//147258369
			'user_type_code'	=> 0xB010,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],

		//初始化一个客服
		[
			'code'				=> '',
			'name'				=> 'Customer Service',
			'email'				=> 'jdofgegw2@163.com',
			'phone'				=> '13112345678',
			'password'			=> '499894d42bab56ff3f7d734e2c2df5f0',//service147258369
			'operate_password'	=> 'e40f01afbb1b9ae3dd6747ced5bca532',//147258369
			'user_type_code'	=> 0xB011,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],

		//初始化一个测试用户
		[
			'code'				=> '',
			'name'				=> 'Test Customer',
			'email'				=> 'm15287654321@163.com',
			'phone'				=> '15912345678',
			'password'			=> 'f516f81a9664064a36ec4eb42920d1d7',//customer123456
			'operate_password'	=> 'e10adc3949ba59abbe56e057f20f883e',//123456
			'user_type_code'	=> 0xB012,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],

		//初始化一个测试用户
		[
			'code'				=> '',
			'name'				=> 'Customer',
			'email'				=> 'td194672513@163.com',
			'phone'				=> '19812345678',
			'password'			=> '8f5fad9068250f90ed47223ac15e7be1',//bp123456
			'operate_password'	=> 'e10adc3949ba59abbe56e057f20f883e',//123456
			'user_type_code'	=> 0xB012,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],
		
		//初始化一个测试客户
		[
			'code'				=> '',
			'name'				=> 'Client',
			'email'				=> 'client@test.com',
			'phone'				=> '18187654321',
			'password'			=> '47ec2dd791e31e2ef2076caf64ed9b3d',//test123456
			'operate_password'	=> 'e10adc3949ba59abbe56e057f20f883e',//123456
			'user_type_code'	=> 0xB012,
			'safety_code'		=> '',
			'created_at'		=> '',
			'updated_at'		=> '',
			'latest_login_time'	=> ''
		],
	];

	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "users";

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{

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
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::$init_data as $key => $value){
        	$count = DB::table(self::$table_name)->where('name',$value['name'])->count();
        	if($count == 0){
        		$user = $value;
        		$time = date('Y-m-d H:i:s');
        		$user['code'] = \App\Models\CodeConfigModel::getUniqueCode('user');
        		$user['password'] = bcrypt($user['password']);
        		$user['safety_code'] = sha1(time().$this->create_password());//生成安全码
				$user['created_at'] = $user['updated_at'] = $time;
				$user['latest_login_time'] = time();

				sleep(1);
				$resp = $this->registerUser($value['user_type_code'],$user['safety_code'],$value['email']);
				if(is_null(json_decode($resp))){
					\Illuminate\Support\Facades\Log::error(__FUNCTION__,['msg'=>$resp]);
				}
				else{
					$res = json_decode($resp,true);
					if($res['code'] == $this->b_return_success_code){
						$user['user_code'] = $res['data']['ucode'];
						$user['active_status'] = 1;
					}
					else{
						\Illuminate\Support\Facades\Log::error(__FUNCTION__,['msg'=>'request B system error:'.$res['message']]);
					}
					DB::table(self::$table_name)->insert($user);
				}

			}
		}
    }
}
