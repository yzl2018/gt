<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SysLanguageWordsTableSeeder extends Seeder
{
	/**
	 * 语言文字
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'word_code'				=> ':00001',
			'language_type_code'	=> 'CN',
			'word'					=> '管理员',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00001',
			'language_type_code'	=> 'EN',
			'word'					=> 'Administrator',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00002',
			'language_type_code'	=> 'CN',
			'word'					=> '客服',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00002',
			'language_type_code'	=> 'EN',
			'word'					=> 'Customer service',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00003',
			'language_type_code'	=> 'CN',
			'word'					=> '客户',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00003',
			'language_type_code'	=> 'EN',
			'word'					=> 'Customer',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00004',
			'language_type_code'	=> 'CN',
			'word'					=> '美元',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00004',
			'language_type_code'	=> 'EN',
			'word'					=> 'Dollar',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00005',
			'language_type_code'	=> 'CN',
			'word'					=> '人民币',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00005',
			'language_type_code'	=> 'EN',
			'word'					=> 'Chinese unit of currency',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00006',
			'language_type_code'	=> 'CN',
			'word'					=> '日元',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00006',
			'language_type_code'	=> 'EN',
			'word'					=> 'The Japanese yen',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00007',
			'language_type_code'	=> 'CN',
			'word'					=> '美食',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00007',
			'language_type_code'	=> 'EN',
			'word'					=> 'Delicious food',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00008',
			'language_type_code'	=> 'CN',
			'word'					=> '旅游',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00008',
			'language_type_code'	=> 'EN',
			'word'					=> 'Travel',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00009',
			'language_type_code'	=> 'CN',
			'word'					=> '服装',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
		[
			'word_code'				=> ':00009',
			'language_type_code'	=> 'EN',
			'word'					=> 'Clothing',
			'created_at'			=> '',
			'updated_at'			=> ''
		],
	];
	
	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "sys_language_words";
	
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		foreach (self::$init_data as $key => $value){
			$count = DB::table(self::$table_name)
				->where('word_code',$value['word_code'])
				->where('language_type_code',$value['language_type_code'])
				->count();
			if($count == 0){
				$time = date('Y-m-d H:i:s');
				$value['created_at'] = $value['updated_at'] = $time;
				DB::table(self::$table_name)->insert($value);
			}
		}
    }
}
