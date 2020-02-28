<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteLimitTradeAmountSeeder extends Seeder
{
    /**
     * table name
     *
     * @var string
     */
    private static $table_name = "site_limit_trade_amount";

    /**
     * 初始化数据
     *
     * @var array
     */
    private static $init_data = [
        [
            'code'			=> 'WS001',
            'domain_name'	=> 'default',
            'web_site'		=> '',
            'currency_type'	=> 'CNY',
            'cny_buy_limit'	=> 3000,
            'cny_buy_stop'	=> 499000,
            'usd_buy_limit'	=> 500,
            'usd_buy_stop'	=> 71285,
            'enable_status'	=> 1
        ]
    ];

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
                $value['web_site'] = config('system.mall.server_root');
                $value['created_at'] = $value['updated_at'] = date('Y-m-d H:i:s');
                DB::table(self::$table_name)->insert($value);
            }
        }

    }
}
