<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AutoClearLogTableSeeder extends Seeder
{
	/**
	 * 自动清理日志表格初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'log_table_name'			=> 'register_activation_log',//注册日志
			'date_key'					=> 'created_at',
			'delete_moment_limit_days'	=> 30,
			'delete_forever_limit_days'	=> 60,
			'delete_type'				=> 0
		],
		[
			'log_table_name'			=> 'login_log',//登陆日志
			'date_key'					=> 'login_at',
			'delete_moment_limit_days'	=> 30,
			'delete_forever_limit_days'	=> 60,
			'delete_type'				=> 0
		],
		[
			'log_table_name'			=> 'b_notify_log',//B系统通知日志
			'date_key'					=> 'time',
			'delete_moment_limit_days'	=> 60,
			'delete_forever_limit_days'	=> 180,
			'delete_type'				=> 1
		],
		[
			'log_table_name'			=> 'crm_request_log',//CRM请求日志
			'date_key'					=> 'request_time',
			'delete_moment_limit_days'	=> 60,
			'delete_forever_limit_days'	=> 180,
			'delete_type'				=> 1
		],
		[
			'log_table_name'			=> 'user_operate_log',//用户操作日志
			'date_key'					=> 'time',
			'delete_moment_limit_days'	=> 30,
			'delete_forever_limit_days'	=> 60,
			'delete_type'				=> 0
		],
		[
			'log_table_name'			=> 'send_mail_log',//邮件发送日志
			'date_key'					=> 'created_at',
			'delete_moment_limit_days'	=> 60,
			'delete_forever_limit_days'	=> 180,
			'delete_type'				=> 0
		],
		[
			'log_table_name'			=> 'notify_merchant_card_log',//通知商户充值卡日志
			'date_key'					=> 'created_at',
			'delete_moment_limit_days'	=> 60,
			'delete_forever_limit_days'	=> 180,
			'delete_type'				=> 0
		],
		[
			'log_table_name'			=> 'request_guide_mail_records',//请求发送邮件日志记录
			'date_key'					=> 'request_time',
			'delete_moment_limit_days'	=> 60,
			'delete_forever_limit_days'	=> 120,
			'delete_type'				=> 0
		],
		[
			'log_table_name'			=> 'oauth_access_tokens',//授权认证Token
			'date_key'					=> 'created_at',
			'delete_moment_limit_days'	=> 7,
			'delete_forever_limit_days'	=> 15,
			'delete_type'				=> -1
		],
		[
			'log_table_name'			=> 'oauth_refresh_tokens',//刷新授权认证Token
			'date_key'					=> 'expires_at',
			'delete_moment_limit_days'	=> 7,
			'delete_forever_limit_days'	=> 15,
			'delete_type'				=> -1
		],
		[
			'log_table_name'			=> 'operate_auth_codes',//操作认证码记录表
			'date_key'					=> 'expires_time',
			'delete_moment_limit_days'	=> 7,
			'delete_forever_limit_days'	=> 15,
			'delete_type'				=> -1
		],
	];
	
	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "auto_clear_log_config";
	
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::$init_data as $key => $value){
        	$count = DB::table(self::$table_name)->where('log_table_name',$value['log_table_name'])->count();
        	if($count == 0){
        		DB::table(self::$table_name)->insert($value);
			}
		}
    }
}
