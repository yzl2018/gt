<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailViewsSeeder extends Seeder
{

	/**
	 * 邮件视图
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'view'			=> 'emails.authcode',//邮件视图
			'subject'		=> '操作认证码邮件',//邮件主题
			'parameters'	=> ['type_name','code','expires_time']//邮件数据参数
		],
		[
			'view'			=> 'emails.shopping',//邮件视图
			'subject'		=> '客户购买兑换券邮件',//邮件主题
			'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
			'update_table'	=> [
				'table_name'	=> 'cash_cards',
				'key_name'		=> 'mail_redis_key',
				'value_name'	=> 'email_notice_status'
			]
		],
		[
			'view'			=> 'emails.convert',//邮件视图
			'subject'		=> '客户消费兑换券邮件',//邮件主题
			'parameters'	=> ['VoucherNo','VoucherValue','Currency'],//邮件数据参数
			'update_table'	=> [
				'table_name'	=> 'cash_cards',
				'key_name'		=> 'sms_redis_key',
				'value_name'	=> 'sms_notice_status'
			]
		],
		[
            'view'			=> 'emails.registerSuccess',//邮件视图
            'subject'		=> '注册成功邮件',//邮件主题
            'parameters'	=> ['CustomerEmail','LoginPassword']//邮件数据参数
        ]
	];

	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "mail_views";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		foreach (self::$init_data as $key => $value){
			$count = DB::table(self::$table_name)->where('view',$value['view'])->count();
			if($count == 0) {
				$value['parameters'] = json_encode($value['parameters']);
				if(isset($value['update_table'])){
					$value['update_table'] = json_encode($value['update_table']);
				}
				DB::table(self::$table_name)->insert($value);
			}
		}
    }
}
