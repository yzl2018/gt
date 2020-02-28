<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeTableSeeder extends Seeder
{
	
	/**
	 * 用户类型
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'code'	=> 0xB010,
			'name_word_code'	=> ':00001'
		],
		[
			'code'	=> 0xB011,
			'name_word_code'	=> ':00002'
		],
		[
			'code'	=> 0xB012,
			'name_word_code'	=> ':00003'
		]
	];
	
	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "user_type";
	
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
