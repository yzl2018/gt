<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypesConfigSeeder extends Seeder
{

	/**
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [

		0xE301	=> [
			'code'	=> 0xE301,
			'name'	=> '发送认证码邮件',
			'view_id'	=> '1',
			'current_channels_group'	=> 'MCG001',
			'prepare_channels_groups'	=> ['MCG001'],
			'emergency_channel'			=> 'ali001'//benefitpick
		],

		0xE302	=> [
			'code'	=> 0xE302,
			'name'	=> '购买成功后发送卡号卡密邮件',
			'view_id'	=> '2',
			'current_channels_group'	=> 'MCG002',
			'prepare_channels_groups'	=> ['MCG002'],
			'delay_send_seconds'	=> 120,
			'emergency_channel'			=> 'ali002'//crmcloud
		],

		0xE303	=> [
			'code'	=> 0xE303,
			'name'	=> '补发卡号卡密邮件',
			'view_id'	=> '2',
			'current_channels_group'	=> 'MCG003',
			'prepare_channels_groups'	=> ['MCG003'],
			'delay_send_seconds'	=> 120,
			'emergency_channel'			=> 'ali002'//crmcloud
		],

		0xE304	=> [
			'code'	=> 0xE304,
			'name'	=> '激活成功后发送兑换券消费邮件',
			'view_id'	=> '3',
			'current_channels_group'	=> 'MCG004',
			'prepare_channels_groups'	=> ['MCG004'],
			'emergency_channel'			=> 'ali003'//lighthouse
		],

		0xE305	=> [
			'code'	=> 0xE305,
			'name'	=> '补发兑换券消费邮件',
			'view_id'	=> '3',
			'current_channels_group'	=> 'MCG005',
			'prepare_channels_groups'	=> ['MCG005'],
			'emergency_channel'			=> 'ali003'//lighthouse
		],
		0xE310	=> [
            'code'	=> 0xE310,
            'name'	=> '发送注册成功邮件',
            'view_id'	=> '7',
            'current_channels_group'	=> 'MCG12',
            'prepare_channels_groups'	=> ['MCG12'],
            'emergency_channel'			=> 'outlook004'//lighthouse
        ]

	];

	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "mail_types_config";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$i = 0;
		foreach (self::$init_data as $key => $value){
			$count = DB::table(self::$table_name)->where('code',$value['code'])->count();
			if($count == 0) {
				$value['prepare_channels_groups'] = json_encode($value['prepare_channels_groups']);
				$value['created_at'] = date('Y-m-d H:i:s',time()+$i);
				DB::table(self::$table_name)->insert($value);
			}
			$i++;
		}
    }
}
