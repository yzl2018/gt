<?php
namespace App\Http\Controllers\API\Payment;

use App\Http\Toolkit\MailChannelsDispatch;
use App\Mail\MAIL;
use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CommunicateWithB;
use App\Models\CashCardsModel;
use App\Models\PaymentOrdersModel;
use App\Models\PurchaseRecordsModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;

class PaymentController extends ApiController
{
	use MailChannelsDispatch,CommunicateWithB;

	/**
	 * 存放充值卡信息的数组
	 *
	 * @var array
	 */
	private $new_vouchers = [];

    /**
     * 客户邮箱
     *
     * @var null
     */
    private $customer_email = null;

	/**
	 * 测试异步通知
	 */
	public function testNotify(){

		$params = [
			'vouchers'	=> [
				'CNMN19011NP5L9WYN0'	=> '0UYPOV'
			],
			'order_id'	=> '802001003o013o0111o98840001613',
			'trans_id'	=> 'a882124e-2b1c-437f-a6c5-f55012ccb6cd'
		];

		$uri = "http://103.117.132.40/api/gateway/notify";

		$resp = $this->http_post_B($uri,$params);
		print_r($resp);

	}

	/**
	 * 测试同步通知
	 */
	public function testCallBack(){

		$params = [
			"merCode" => "99987020084",
			"bizType" => "000201",
			"txnSubType" => "01",
			"orderId" => "20180313111929282400",
			"payCardType" => "01",
			"signature" => "H2pSMGnoBSA3AjyryCuB8mexZBPeXspdX8HehcQOhTwbZ7QJweHQsDYehqItSMcbZHcHH7Wk+pdvtxeRclb1onvS+QmpyRMNDFIoUKVbr315AdrBuO7INP0ZKZbguyTtZ/k55VKXLEsBR5jQ8Ew3h5ODR7ZrdYXZ4t3h41GlS890LLAlskuzbEWvrkkVmaclq+WxvCdzYJIL/Tv9MoDf+Hvrc0m3Se1ZoGTlWJAEnTL1MJ2zon05QEKIy5IMEq9tKUGMl0THmq8cynvJ4l5T6p0ozvvR6ODw+Y10tegmcAxvm7s1pUTewKSFolJlhgbZjOh2u/L3lGSeNW3kSESmQQ==",
			"accNo" => "622700****1354",
			"channel" => "001",
			"txnType" => "01",
			"queryId" => "411803131118292461088",
			"reserved" => "",
			"reqReserved" => "",
			"respMsg" => "System is not started or temporarily down, please try again later",
			"txnTime" => "20180313111829",
			"merId" => "607070282990003",
			"respCode" => "00",
			"signMethod" => "01",
			"txnAmt" => "100",
			"orderDesc" => ""
		];

		$uri = "http://103.117.132.40/api/gateway/callback";

		$resp = $this->http_post_B($uri,$params);
		print_r($resp);

	}

	/**
	 * 订单支付同步通知
	 */
	public function callback(Request $request){

		$response_data = [
			'merCode'       => '',
			'bizType'       => '',
			'txnSubType'    => '',
			'orderId'       => '',
			'payCardType'   => '',
			'signature'     => '',
			'channel'       => '',
			'txnType'       => '',
			'channelType'   => '',
			'queryId'       => '',
			'reserved'      => '',
			'respMsg'       => '',
			'txnTime'       => '',
			'merId'         => '',
			'signMethod'    => '',
			'respCode'      => '',
			'txnAmt'        => '',
			'orderDesc'     => NULL
		];

		$params = $request->input();


		foreach ($response_data as $key => $value){
			if($value != NULL){
				if(!array_key_exists($key,$params)){
					exit("Forbidden!");
				}
			}
		}

        if($params['respCode'] == '00'){
            return view('success',[
                'order_id'		=> $params['orderId'],
                'order_amount'	=> $params['txnAmt']/100,
                'order_time'	=> $params['txnTime']
            ]);
        }
        else{
            return view('fail',[
                'order_id'	=> $params['orderId'],
                'code'=>$params['respCode'],
                'message'=>$params['respMsg']
            ]);
        }

	}

	/**
	 * 订单支付异步通知
	 *
	 * @param Request $request
	 * @return mixed|string
	 */
	public function notify(Request $request){

		$params = json_decode(file_get_contents("php://input"),true);

		$token = $request->header($this->b_security['notify_token_key']);
		if(empty($token)){
			return $this->error('Illegal notify');
		}

		$this->verify_header_token($token);

		$order = PaymentOrdersModel::where('order_no',$params['order_id'])->first();
		if($order->trade_status != 1){
			$update_order = $this->updateOrder($params);
			if($update_order['success'] == false){
				return $update_order['message'];
			}

            $this->customer_email = UsersModel::where('id',$order->users_id)->value('email');
			$purchase_info = PurchaseRecordsModel::where('id',$order->purchase_records_id)->first();
			$attach_info = json_decode($purchase_info->attach_info,true);

//            $mail_type_code = $this->roundRobinArr([0xE002,0xE003,0xE004,0xE005,0xE021,0xE022,0xE023,0xE024,0xE026,0xE027,0xE028,0xE029],1);
//            $mail_type_code = 0xE036;
//            $redis_key1 = $this->dispatchMailJob($mail_type_code,//使用配置文件派发邮件任务
//				[
//					'OrderNo'             => $order->order_no,
//					'Value'				   => $order->order_amount,
//					'Currency'			   => $order->currency_type_code,
//					'CustomerEmail'			=> $this->customer_email,
//					'ProName'			=> $attach_info['name_word']['CN'],
//					'ProPrice'			=> $purchase_info->price,
//					'ProNumber'			=> $purchase_info->buy_number
//				],$this->customer_email);

            $dispatch0 = $this->autoDispatchMailJob(MAIL::mail_send_invoice_info,//使用数据库配置派发邮件任务
				[
					'OrderNo'             => $order->order_no,
					'Value'				   => $order->order_amount,
					'Currency'			   => $order->currency_type_code,
					'CustomerEmail'			=> $this->customer_email,
					'ProName'			=> $attach_info['name_word']['CN'],
					'ProPrice'			=> $purchase_info->price,
					'ProNumber'			=> $purchase_info->buy_number
				],$this->customer_email);

            $dispatch = $this->autoDispatchMailJob(MAIL::mail_send_cards_info,//使用数据库配置派发邮件任务
				[
					'Vouchers'             => $params['vouchers'],
					'Value'				   => $order->order_amount,
					'Currency'			   => $order->currency_type_code
				],$this->customer_email);
            if($dispatch['success']){
				$params['redis_key'] = $dispatch['message'];
			}
		}

		$payment_status = PurchaseRecordsModel::where('id',$order->purchase_records_id)->value('payment_status');
		if($payment_status != 1) {
            $update_purchase = $this->updatePurchase($order->purchase_records_id,$params);
            if($update_purchase['success'] == false){
                return $update_purchase['message'];
            }
		}

		$new_cards = $this->createCards($params);
		if($new_cards['success'] == false){
			return $new_cards['message'];
		}

		return 'ok';
	}

	/**
	 * 更新支付订单
	 *
	 * @param $params
	 * @return array
	 */
	private function updateOrder(array $params){

		$data = [
			'trade_no'		=> $params['trans_id'],
			'success_time' 	=> date('Y-m-d H:i:s'),
			'updated_at'	=> date('Y-m-d H:i:s'),
			'trade_status'	=> 1
		];

		$is_update = PaymentOrdersModel::where('order_no',$params['order_id'])->update($data);
		if($is_update > 0){
			return ['success'=>true,'message'=>'Payment order updated successfully'];
		}

		return ['success' => false,'message' => 'Payment order update failed'];

	}

	/**
	 * 更新购买记录
	 *
	 * @param $purchase_id
	 * @param $params
	 * @return array
	 */
	private function updatePurchase($purchase_id,$params){

		$data = [
			'order_no'	=> $params['order_id'],
			'success_time' 		=> date('Y-m-d H:i:s'),
			'updated_at'		=> date('Y-m-d H:i:s'),
			'payment_status'	=> 1
		];

        $vouchers = $params['vouchers'];
        foreach ($vouchers as $key => $value){
            $data['card_no'] = $key;
        }

		$is_update = PurchaseRecordsModel::where('id',$purchase_id)->update($data);
		if($is_update > 0){
			return ['success'=>true,'message'=>'Purchase records updated successfully'];
		}

		return ['success' => false,'message' => 'Purchase records update failed'];

	}

	/**
	 * 生成充值卡
	 *
	 * @param $params
	 * @return array
	 */
	private function createCards($params){

		$date_time = date($this->time_fmt);

		if (CashCardsModel::query()->where('order_no', $params['order_id'])->exists()) {
            return ['success'=>true,'message'=>'Cards already created'];
        }

		$payment_order = PaymentOrdersModel::where('order_no',$params['order_id'])->first();
		$vouchers = $params['vouchers'];
		$i = 0;
		foreach ($vouchers as $key => $value){
			$this->new_vouchers[$i] = [
				'users_id'				=> $payment_order->users_id,
                'users_email'			=> $this->customer_email,
				'purchase_records_id'	=> $payment_order->purchase_records_id,
                'purchase_code'			=> $payment_order->purchase_code,
				'order_no'				=> $params['order_id'],
				'trade_no'				=> $params['trans_id'],
				'card_no'				=> $key,
				'card_key'				=> $value,
				'card_value'			=> $payment_order->order_amount,
				'currency_type_code'	=> $payment_order->currency_type_code,
				'created_at'			=> $date_time,
				'updated_at'			=> $date_time
			];
			if(isset($params['redis_key'])){
				$this->new_vouchers[$i]['mail_redis_key'] = $params['redis_key'];
			}
			$i++;
		}

		$is_save = CashCardsModel::insert($this->new_vouchers);
		if($is_save){
			return ['success'=>true,'message'=>'Create cards successfully'];
		}

		return ['success' => false,'message' => 'Create cards failed'];

	}

}
