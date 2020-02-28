<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndutryTableSeeder extends Seeder
{
	/**
	 * 行业类型
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'code'	=> 'I001',
			'name_word_code'	=> ':00007'
		],
		[
			'code'	=> 'I002',
			'name_word_code'	=> ':00008'
		],
		[
			'code'	=> 'I003',
			'name_word_code'	=> ':00009'
		]
	];
	
	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "industry";
	
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
				$time = date('Y-m-d H:i:s');
				$value['created_at'] = $value['updated_at'] = $time;
				DB::table(self::$table_name)->insert($value);
			}
		}
		
	}
}
