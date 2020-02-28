<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperateTypeTableSeeder extends Seeder
{
	/**
	 * 操作类型初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [
		[
			'code'	=> 0xD11,//3345
			'name'	=> '注册账号'
		],
		[
			'code'	=> 0xD12,//3346
			'name'	=> '重置密码'
		],
		[
			'code'	=> 0xD13,//3347
			'name'	=> '登陆系统'
		],
		[
			'code'	=> 0xD14,//3348
			'name'	=> '购买商品'
		],
		[
			'code'	=> 0xD15,//3349
			'name'	=> '支付订单'
		],
		[
			'code'	=> 0xD16,//3350
			'name'	=> '查看充值卡密码'
		],
		[
			'code'	=> 0xD17,//3351
			'name'	=> '激活充值卡'
		],
		[
			'code'	=> 0xD18,//3352
			'name'	=> '修改登陆密码'
		],
		[
			'code'	=> 0xD19,//3353
			'name'	=> '更新信息'
		],
		[
			'code'	=> 0xD20,//3360
			'name'	=> '补充资料'
		],
		[
			'code'	=> 0xD21,//3361
			'name'	=> '申请退款'
		],
		[
			'code'	=> 0xD22,//3362
			'name'	=> '冻结/解冻用户'
		],
		[
			'code'	=> 0xD23,//3363
			'name'	=> '解锁用户'
		],
		[
			'code'	=> 0xD24,//3364
			'name'	=> '切换语言'
		],
		[
			'code'	=> 0xD24,//3364
			'name'	=> '切换语言'
		],
		[
			'code'	=> 0xD25,//3365
			'name'	=> '创建'
		],
		[
			'code'	=> 0xD26,//3366
			'name'	=> '更新'
		],
		[
			'code'	=> 0xD27,//3367
			'name'	=> '更新客户信息'
		],
		[
			'code'	=> 0xD28,//3368
			'name'	=> '创建行业'
		],
		[
			'code'	=> 0xD29,//3369
			'name'	=> '更新行业'
		],
		[
			'code'	=> 0xD30,//3376
			'name'	=> '创建商家'
		],
		[
			'code'	=> 0xD31,//3377
			'name'	=> '更新商家'
		],
		[
			'code'	=> 0xD32,//3378
			'name'	=> '上传样品'
		],
		[
			'code'	=> 0xD33,//3379
			'name'	=> '更新样品'
		],
		[
			'code'	=> 0xD34,//3380
			'name'	=> '新增商品'
		],
		[
			'code'	=> 0xD35,//3381
			'name'	=> '更新商品'
		],
		[
			'code'	=> 0xD36,//3382
			'name'	=> '上传商品图片'
		],
		[
			'code'	=> 0xD37,//3383
			'name'	=> '更新商品信息'
		],
		[
			'code'	=> 0xD38,//3384
			'name'	=> '移除商品'
		],
		[
			'code'	=> 0xD39,//3385
			'name'	=> '配置商户密钥'
		],
	];
	
	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "operate_type";
	
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
