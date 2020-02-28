<?php

namespace App\Console\Commands;

use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\RESPONSE;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorrectCardsInfo extends Command
{
	use CommunicateWithB;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'correct:cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Correct cards information with request B system';

	/**
	 * 起始时间
	 *
	 * @var string
	 */
    private $begin_date = '2019-01-01';

	/**
	 * 结束时间
	 *
	 * @var null
	 */
    private $end_date = null;

	/**
	 * A系统中充值卡已退还的状态值
	 *
	 * @var int
	 */
    private $a_refunded_status = -1;

	/**
	 * B系统中充值卡未使用的状态值
	 *
	 * @var int
	 */
    private $b_unused_status = 0;

	/**
	 * B系统中充值卡已使用的状态值
	 *
	 * @var int
	 */
    private $b_used_status = 1;

	/**
	 * B系统中充值卡已退还的状态值
	 *
	 * @var int
	 */
    private $b_refunded_status = 3;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
		$this->b_security = config('system.security');
		$this->md5_sec_key = config('system.mall.md5_sec_key');

		if(env('APP_LIVE')){
			$this->b_web_site = config('system.b_web_site.live');
			$this->sys_security_code = config('system.security_code.live');
			$this->do_pay_uri = config('system.mall.live_url').config('system.mall.do_pay_uri');
		}
		else{
			$this->b_web_site = config('system.b_web_site.demo');
			$this->sys_security_code = config('system.security_code.demo');
			$this->do_pay_uri = config('system.mall.demo_url').config('system.mall.do_pay_uri');
		}
		//获取最早未使用的充值卡的创建时间
		$created_at = DB::table('cash_cards')
			->where('use_status','=',0)
			->where('card_value','>=',config('system.goods.CNY.buy_limit',3000))
			->orderBy('created_at','asc')
			->limit(1)
			->value('created_at');
		if($created_at){
			$this->begin_date = date('Y-m-d',strtotime($created_at));
		}
        $this->end_date = date('Y-m-d',time()+60*60*24);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

    	$user = DB::table('users')->where('user_type_code',config('system.user.admin.code'))->first();
    	if(empty($user)){
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				'get cards info from B' => "暂无可用管理员账号"
			]);
		}
		
		//从B系统查询所有已退款的充值卡信息
		$result = $this->post_data_from_B('get_all_cards',[
			'begin_date'	=> $this->begin_date,
			'end_date'		=> $this->end_date,
			'type'			=> $this->b_refunded_status
		],$user->user_code,$user->safety_code);
		
		if($result['code'] != RESPONSE::SUCCESS){
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				'get cards info from B' => $result['message']
			]);
		}

		else{
			$cards_list = $result['data'];
			//筛选出已退还的充值卡
			$has_returned_cards = [];
			foreach ($cards_list as $index => $card){
				if($card['status'] == $this->b_refunded_status){
					array_push($has_returned_cards,$card);
				}
			}
		
			foreach ($has_returned_cards as $index => $card){
				$use_status = DB::table('cash_cards')->where('card_no',$card['voucher'])->value('use_status');
				if($use_status !== null && $use_status != $this->a_refunded_status){
					try{
						DB::table('cash_cards')->where('card_no',$card['voucher'])->update(['use_status'=>$this->a_refunded_status]);
						Log::info("Correct cards info",[
							'update cards to refunded'	=> $card['voucher']
						]);
					}
					catch(\Exception $e){
						Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
							'update cards to refunded error' => $card['voucher']
						]);
					}
				}
			}
		}

    }
}
