<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypeSeeder extends Seeder
{
	/**
	 * 邮件类型
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
        [
            'code'		=> 0xE010,
            'name'		=> '注册激活邮件',
            'config'	=> 'mailtype.register.active'
        ],
        [
            'code'		=> 0xE011,
            'name'		=> '注册激活成功邮件',
            'config'	=> 'mailtype.register.success'
        ],
        [
            'code'		=> 0xE012,
            'name'		=> '客户购买兑换券邮件',
            'config'	=> 'mailtype.shopping'
        ],
        [
            'code'		=> 0xE013,
            'name'		=> '充值卡激活成功邮件',
            'config'	=> 'mailtype.convert'
        ],
        [
            'code'		=> 0xE014,
            'name'		=> '发送验证码邮件',
            'config'	=> 'mailtype.authcode'
        ],
	];

	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "mail_type";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		foreach (self::$init_data as $key => $value){
			$count = DB::table(self::$table_name)->where('code',$value['code'])->count();
			if($count == 0) {
				DB::table(self::$table_name)->insert($value);
			}
		}
    }
}
