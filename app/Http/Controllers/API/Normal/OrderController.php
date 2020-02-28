<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\Entity\OverdueRecords;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\CurlHttpRequest;
use App\Http\Toolkit\DomainLimitTradeAmount;
use App\Http\Toolkit\MD5Sign;
use App\Http\Toolkit\RESPONSE;
use App\Jobs\ProcessOverdueOrders;
use App\Models\CodeConfigModel;
use App\Models\GoodsInfoModel;
use App\Models\PaymentOrdersModel;
use App\Models\PurchaseRecordsModel;
use App\Models\VirtualCardsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends ApiController
{
	use CommunicateWithB,CurlHttpRequest,DomainLimitTradeAmount;

	/**
	 * 订单号分割符
	 *
	 * @var string
	 */
	private $decimal_symbol = "o";

    /**
     * 是否是支付测试
     *
     * @var bool
     */
    private $is_test_pay = false;

	/**
	 * 请求b系统的接口
	 *
	 * @var string
	 */
	private $request_b_api;

	/**
	 * 订单支付请求B系统的参数
	 *
	 * @var array
	 */
	private $request_b_parameters = [
		'order'         => '',//订单号
		'vouchers'      => [],//兑换券
		'front_url'     => '',//同步跳转地址
		'back_url'      => ''//异步通知地址
	];

    /**
     * 购买商品、生成订单、跳转支付三步一体的支付接口
     *
     * @param Request $request
     * @return mixed
     */
    public function paymentForGoods(Request $request){

        //生成购买订单
        $this->getUser();

        $allow_users = config('system.payment_test.allow_users');
        if($allow_users == '*'){
			$this->is_test_pay = true;
		}
        else if(in_array($this->user['email'],$allow_users)){
            $this->is_test_pay = true;
        }

        if(!$request->has('goods_type') || !$request->has('goods_info_code') || !$request->has('buy_number')){
            return $this->message('Missing parameters.');
        }

		$attach_info = [
			'email'	=> $this->user['email']
		];
        $params = $request->input();
        $type = $request->input('goods_type');

        unset($params['goods_type']);
		
		if(intval($params['buy_number']) != $params['buy_number']){
			return $this->message('Invalid buy number,must be a positive integer');
		}

        if(strpos($params['goods_info_code'],'G') === 0 && $type != 0){
            $type = 0;
        }

        if(strpos($params['goods_info_code'],'C') === 0 && $type != 1){
            $type =1;
        }

        if($type == 0){
            $goods_info = GoodsInfoModel::where('code',$params['goods_info_code'])->first();
            if(empty($goods_info)){
                return $this->message('!119');
            }
            $params['store_info_code'] = $goods_info->store_info_code;
			$attach_info['original_price'] = $goods_info->original_price;
        }
        else{
            $goods_info = VirtualCardsModel::where('code',$params['goods_info_code'])->first();
            if(empty($goods_info)){
                return $this->message('!119');
            }
            $params['store_info_code'] = config('system.mall.code');
			$attach_info['original_price'] = $goods_info->price;
        }

		$attach_info['name_word'] = $this->getWordsByCode($goods_info->name_word_code);
		$attach_info['litpic'] = $goods_info->litpic;
		$params['attach_info'] = json_encode($attach_info);

        $params['code'] = CodeConfigModel::getUniqueCode('purchase');
        $params['users_id'] = $this->user['id'];
        $params['users_code'] = $this->user['code'];

        if($this->is_test_pay == false){
            if($params['buy_number'] < $goods_info->buy_limit){
                return $this->message($this->say('!010').":".$goods_info->buy_limit);
            }

            if($params['buy_number'] > $goods_info->buy_stop){
                return $this->message($this->say('!011').":".$goods_info->buy_stop);
            }
        }

        $params['price'] = $goods_info->price;
        $params['total_amount'] = intval($params['price'] * $params['buy_number']);
        $params['currency_type_code'] = $goods_info->currency_type_code;
        $params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');
        $params['order_time_out'] = date('Y-m-d H:i:s',time() + $this->order_expires_seconds);
		
		$currency_access = $this->checkCurrencyForAccess($params['currency_type_code']);
		if($currency_access['denied']){
			return $this->message($currency_access['message']);
		}

        if($this->is_test_pay == false){
            $check_res = $this->checkTradeTotalMoney($request->getHttpHost(),$params['total_amount']);
            if($check_res['fail']){
                return $this->app_response(RESPONSE::BUY_FAIL,$check_res['message']);
            }
        }

        $purchase_id = PurchaseRecordsModel::insertGetId($params);
        if(empty($purchase_id)){
            return $this->app_response(RESPONSE::WARNING,$this->say('!013'));
        }

//        try{
//            $pur_records = new OverdueRecords('purchase_records','code',$params['code'],'payment_status');
//            ProcessOverdueOrders::dispatch($pur_records)->delay(now()->addMinutes($this->overdue_delay_minutes));
//        }
//        catch(\Exception $e){
//            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
//                'Failed to dispatch queue task:' => $pur_records,
//                'error message'	=> $e->getMessage()
//            ]);
//        }

        //生成支付订单
        $purchase = PurchaseRecordsModel::find($purchase_id);

        if($purchase->payment_status == 1){
            return $this->message($this->say('!014'));
        }

        $new_order = $this->createNewOrder($purchase);
        if($new_order['failed']){
            return $this->message($new_order['message']);
        }

        $web_site = "http://".$request->getHttpHost();
        $this->request_b_parameters['order'] = $new_order['data']['order_no'];
        $this->request_b_parameters['vouchers'] = [$purchase->price => $purchase->buy_number];
        $this->request_b_parameters['front_url'] = $web_site.config('system.mall.front_url');
        $this->request_b_parameters['back_url'] = $web_site.config('system.mall.back_url');

        if($purchase->currency_type_code == 'USD'){
            $this->request_b_api = 'pay_api';
        }

        if($purchase->currency_type_code == 'CNY'){
            $this->request_b_api = 'cny_pay_api';
        }

        //请求获取支付参数并跳转到支付页面
        return $this->request_b_payment();

    }

	/**
	 * 获取时间段内的所有订单记录的接口
	 *
	 * @uri	/api/order/list
	 *
	 * @param Request $request
	 * request data [json]:
	 * {
	 * 		begin_date:DateTime //起始时间 fmt('Y-m-d H:i:s')	[可不传]
	 * 		end_date:DateTime	//结束时间 fmt('Y-m-d H:i:s')	[可不传]
	 * 		is_paginate:bool	//是否分页 默认为true			[可不传]
	 * 		page_items:int		//每页项数 必须为正整数			[可不传]
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * response data [json]:
	 * {
	 * 	  code:string //响应状态码 只有 0xFFF 表示成功 其它均为失败
	 *    data:[ //响应数据
	 * 		 {
	 * 			id:int							//订单记录id
	 * 			order_code:string				//订单编码
	 * 			users_id:int					//用户id
	 * 			purchase_records_id:int			//购买记录id
	 * 			order_no:string					//订单编号
	 * 			order_amount:numeric(15,2)		//订单金额
	 * 			order_time:DateTime				//订单时间 fmt('Y-m-d H:i:s')
	 * 			order_time_out:DateTime			//订单过期时间 fmt('Y-m-d H:i:s')
	 * 			currency_type_code:string		//货币类型	 enum('USD','CNY')
	 * 			trade_no:string					//交易流水号
	 * 			trade_status:int				//交易状态(0:未支付,1:支付成功,-1:已过期)
	 * 			success_time:DateTime			//支付成功时间 fmt('Y-m-d H:i:s')
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function showOrdersList(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

        $list = PaymentOrdersModel::where('payment_orders.created_at','>=',$this->begin_date)
            ->where('payment_orders.created_at','<=',$this->end_date)
            ->leftJoin('users','payment_orders.users_id','=','users.id')
            ->select(['payment_orders.*','users.email'])
            ->orderBy('payment_orders.created_at','desc')
            ->when($this->is_paginate,function($query){
                return $query->paginate($this->page_items);
            },function($query){
                return $query->get();
            });

		return $this->app_response(RESPONSE::SUCCESS,'get orders list success',$list);

	}

	/**
	 * 获取指定购买记录的所有订单记录的接口
	 *
	 * @uri	/api/order/list-of-purchase
	 *
	 * @param Request $request
	 * request data [json]:
	 * {
	 * 		purchase_id:int		//购买记录id
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * response data [json]:
	 * {
	 * 	  code:string //响应状态码 只有 0xFFF 表示成功 其它均为失败
	 *    data:[ //响应数据
	 * 		 {
	 * 			id:int							//订单记录id
	 * 			order_code:string				//订单编码
	 * 			users_id:int					//用户id
	 * 			purchase_records_id:int			//购买记录id
	 * 			order_no:string					//订单编号
	 * 			order_amount:numeric(15,2)		//订单金额
	 * 			order_time:DateTime				//订单时间 fmt('Y-m-d H:i:s')
	 * 			order_time_out:DateTime			//订单过期时间 fmt('Y-m-d H:i:s')
	 * 			currency_type_code:string		//货币类型	 enum('USD','CNY')
	 * 			trade_no:string					//交易流水号
	 * 			trade_status:int				//交易状态(0:未支付,1:支付成功,-1:已过期)
	 * 			success_time:DateTime			//支付成功时间 fmt('Y-m-d H:i:s')
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function ordersOfPurchase(Request $request){

		$this->getUser();
		$purchase_id = $request->input('purchase_id');

		$list = PaymentOrdersModel::where('purchase_records_id',$purchase_id)->get();

		if(count($list) == 0){
			return $this->app_response(RESPONSE::NOT_EXIST,$this->say('!088'));
		}

		return $this->app_response(RESPONSE::SUCCESS,'get orders list success',$list);

	}

	/**
	 * 订单支付
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse|mixed
	 */
	public function payment(Request $request){

		$this->getUser();

        if(!$request->has('purchase_id')){
            return $this->message('Missing parameters.');
        }

		$purchase_id = $request->input('purchase_id');
		$purchase = PurchaseRecordsModel::find($purchase_id);

		if($purchase->payment_status == 1){
			return $this->message($this->say('!014'));
		}

        if($purchase->payment_status == -1){
            return $this->message($this->say('!120'));
        }

		$new_order = $this->createNewOrder($purchase);
		if($new_order['failed']){
			return $this->message($new_order['message']);
		}

        $web_site = "http://".$request->getHttpHost();
		$this->request_b_parameters['order'] = $new_order['data']['order_no'];
		$this->request_b_parameters['vouchers'] = [$purchase->price => $purchase->buy_number];
		$this->request_b_parameters['front_url'] = $web_site.config('system.mall.front_url');
		$this->request_b_parameters['back_url'] = $web_site.config('system.mall.back_url');

		if($purchase->currency_type_code == 'USD'){
			$this->request_b_api = 'pay_api';
		}

		if($purchase->currency_type_code == 'CNY'){
			$this->request_b_api = 'cny_pay_api';
		}

		return $this->request_b_payment();

	}

	/**
	 * 请求B系统 获取上游平台支付参数
	 *
	 * response data [json]:
	 * {
		"code": 4095,
		"message": "ok",
		"data": {
			"url": "https://pay.moovpay.com/gateway/api/frontTrans.do",
			"method": "post",
			"data": {
				"signMethod": "01",
				"txnType": "01",
				"txnSubType": "01",
				"bizType": "000201",
				"channelType": "07",
				"channel": "001",
				"merCode": "99987020135",
				"merId": "607070253980012",
				"frontUrl": "http://my.benefitpick.com/api/gateway/callback",
				"backUrl": "http://113.10.167.40:32951/api/transaction/c-notify/10001001",
				"orderId": "802001003o013o0111o98840001613",
				"txnTime": "20190121105900",
				"accNo": null,
				"txnAmt": 70000,
				"signature": "e49d8589737b329f6a9cc20145d393103040f5a6e2fef038fdb6fd2d03be0ae5",
				"payTimeout": "20190121113100",
				"reqReserved": null,
				"frontFailUrl": null,
				"customerIp": null,
				"reserved": null
			}
		}
	 }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function request_b_payment(){

		$result = $this->post_data_from_B($this->request_b_api,$this->request_b_parameters,$this->user['user_code'],$this->sys_security_code);

		if($result['code'] != RESPONSE::SUCCESS){
			return \response()->json($result);
		}

		return view('payment.dopay',['params'=>$result['data']]);

	}

	/**
	 * 生成新的支付订单
	 *
	 * @param $purchase
	 * @return array
	 */
	private function createNewOrder($purchase){

		$order_code = CodeConfigModel::getUniqueCode('order');

        //商城简称+商城编号+用户编号+下单编码+订单编码(+MID简称)
        $order_no = config('system.mall.short')//商城简称
            .config('system.mall.code')//商城编号
            .substr($purchase->users_code,1)//用户编号
            .substr($purchase->code,1)//下单编码
            .substr($order_code,1);//订单编码

		$time = time();

		$update_data = [
			'payment_times'		=> $purchase->payment_times + 1,
			//'payment_orders_id'	=> $order_no,
			'updated_at'		=> date('Y-m-d H:i:s',$time)
		];

		$is_update = PurchaseRecordsModel::where('id',$purchase->id)->update($update_data);
		if($is_update == 0){
			return ['failed' => true,'message' => $this->say('!079')];
		}

		$params = [
            'purchase_code'			=> $purchase->code,
			'order_code'			=> $order_code,
			'users_id'				=> $purchase->users_id,
			'purchase_records_id'	=> $purchase->id,
			'order_no'				=> $order_no,
			'order_amount'			=> $purchase->total_amount,
			'order_time'			=> date('Y-m-d H:i:s',$time),
			'order_time_out'		=> date('Y-m-d H:i:s',$time + $this->order_expires_seconds),
			'currency_type_code'	=> $purchase->currency_type_code,
			'created_at'			=> date('Y-m-d H:i:s',$time),
			'updated_at'			=> date('Y-m-d H:i:s',$time)
		];

		$id = PaymentOrdersModel::insertGetId($params);
		if($id > 0){

//            try{
//                $pay_orders = new OverdueRecords('payment_orders','order_code',$order_code,'trade_status');
//                ProcessOverdueOrders::dispatch($pay_orders)->delay(now()->addMinutes($this->overdue_delay_minutes));
//            }
//            catch(\Exception $e){
//                Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
//                    'Failed to dispatch queue task:' => $pay_orders,
//                    'error message'	=> $e->getMessage()
//                ]);
//            }

			return ['failed' => false,'data' => $params];
		}

		return ['failed' => true,'message' => $this->say('!015')];
	}

}
