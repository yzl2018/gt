<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodeConfigTableSeeder extends Seeder
{

	/**
	 * 编码配置表
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'name'			=> 'word',
			'prefix'		=> ':',
			'random_bits'	=> 0,
			'code_bits'		=> 5,
			'start_val'		=> 699,
			'latest_val'	=> 699
		],//文字编码配置
		[
			'name'			=> 'user',
			'prefix'		=> 'U',
			'random_bits'	=> 0,
			'code_bits'		=> 6,
			'start_val'		=> 1001,
			'latest_val'	=> 1001
		],//用户编码配置
		[
			'name'			=> 'industry',
			'prefix'		=> 'I',
			'random_bits'	=> 0,
			'code_bits'		=> 3,
			'start_val'		=> 11,
			'latest_val'	=> 11
		],//行业编码配置
		[
			'name'			=> 'store',
			'prefix'		=> 'S',
			'random_bits'	=> 0,
			'code_bits'		=> 3,
			'start_val'		=> 13,
			'latest_val'	=> 13
		],//商家编码配置
		[
			'name'			=> 'goods',
			'prefix'		=> 'G',
			'random_bits'	=> 0,
			'code_bits'		=> 4,
			'start_val'		=> 109,
			'latest_val'	=> 109
		],//商品编码配置
		[
			'name'			=> 'order',
			'prefix'		=> 'O',
			'random_bits'	=> 2,
			'code_bits'		=> 7,
			'start_val'		=> 1605,
			'latest_val'	=> 1605
		],//订单编码配置
        [
            'name'			=> 'virtual_cards',
            'prefix'		=> 'C',
            'random_bits'	=> 0,
            'code_bits'		=> 4,
            'start_val'		=> 121,
            'latest_val'	=> 131
        ],//虚拟卡片编码配置
        [
            'name'			=> 'web_site',
            'prefix'		=> 'WS',
            'random_bits'	=> 0,
            'code_bits'		=> 3,
            'start_val'		=> 002,
            'latest_val'	=> 002
        ],//站点编码配置
        [
            'name'			=> 'purchase',
            'prefix'		=> 'P',
            'random_bits'	=> 0,
            'code_bits'		=> 7,
            'start_val'		=> 1101,
            'latest_val'	=> 1101
        ],//站点编码配置
	];

	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "code_config";

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
        		DB::table(self::$table_name)->insert($value);
			}
		}
    }
}
