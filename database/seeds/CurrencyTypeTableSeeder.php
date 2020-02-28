<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencyTypeTableSeeder extends Seeder
{
	
	/**
	 * 货币类型
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'code'				=> 'USD',
			'name_word_code'	=> ':0004',
			'symbol'			=> '$',
			'b_number'			=> 0xC001
		],
		[
			'code'				=> 'CNY',
			'name_word_code'	=> ':0005',
			'symbol'			=> '￥',
			'b_number'			=> 0xC002
		],
		[
			'code'				=> 'JPY',
			'name_word_code'	=> ':0006',
			'symbol'			=> '￥',
			'b_number'			=> 0xC003
		]
	];
	
	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "currency_type";
	
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
