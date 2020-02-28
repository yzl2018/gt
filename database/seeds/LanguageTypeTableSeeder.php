<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageTypeTableSeeder extends Seeder
{
	/**
	 * 语言类型
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'code'	=> 'CN',
			'name'	=> '中文'
		],
		[
			'code'	=> 'EN',
			'name'	=> 'English'
		]
	];
	
	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "language_type";
	
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
