<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailCahnnelsGroupSeeder extends Seeder
{

	/**
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [

		[
			'code'	=> 'MCG001',
			'name'	=> 'Auth code mail group',
			//benefitpick , lighthouse
			'include_channels'	=> ['ali001','ali003'],
			'repeat_times'		=> 100,
			'using_channel'		=> 'ali001'
		],

		[
			'code'	=> 'MCG002',
			'name'	=> 'Payment success mail group',
			//info_fxrefillcard,info_forexacard,info_forexbcard,info_fxcarda
			'include_channels'	=> ['ali010','ali011','ali012','ali013'],
			'repeat_times'		=> 10,
			'using_channel'		=> 'ali010'
		],

		[
			'code'	=> 'MCG003',
			'name'	=> 'Supply payment success mail group',
			//system_fxrefillcard,system_forexacard,system_forexbcard,system_fxcarda
			'include_channels'	=> ['ali015','ali016','ali017','ali018'],
			'repeat_times'		=> 10,
			'using_channel'		=> 'ali015'
		],

		[
			'code'	=> 'MCG004',
			'name'	=> 'Active success mail group',
			//crmcloud,fxrefillcard
			'include_channels'	=> ['ali002','ali005'],
			'repeat_times'		=> 200,
			'using_channel'		=> 'ali002'
		],

		[
			'code'	=> 'MCG005',
			'name'	=> 'Supply active success mail group',
			//forexacard,forexbcard,fxcarda
			'include_channels'	=> ['ali006','ali007','ali008'],
			'repeat_times'		=> 10,
			'using_channel'		=> 'ali006'
		],

	];

	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "mail_channels_group";

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
				$value['include_channels'] = json_encode($value['include_channels']);
				$value['created_at'] = date('Y-m-d H:i:s',time()+$i);
				DB::table(self::$table_name)->insert($value);
			}
			$i++;
		}
    }
}
