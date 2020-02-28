<?php
namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\CurlHttpRequest;
use App\Http\Toolkit\GetIpLocation;
use App\Http\Toolkit\ServerEncryptTools;
use App\Mail\MAIL;
use App\Models\CashCardsModel;
use App\Models\MerchantSecurityModel;
use App\Models\NotifyMerchantCardLogModel;
use App\Models\PaymentOrdersModel;
use App\Models\PurchaseRecordsModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActiveNotifyMerchantController extends ApiController
{
    use CommunicateWithB,CurlHttpRequest,GetIpLocation;

    /**
     * 接收上游的参数
     *
     * @var
     */
    private $receive_params;

    /**
     * 商户信息
     *
     * @var null
     */
    private $merchant = null;

    /**
     * 商户通知地址
     *
     * @var null
     */
    private $notify_url = null;

    /**
     * 通知下游商户的参数
     *
     * @var array
     */
    private $notify_parameters = [
        'Code'          => '',
        'Message'       => '',
        'VoucherNo'     => '',
        'VoucherValue'  => '',
        'Currency'      => '',
        'MallOrderId'	=> '',
        'CRMOrderNo'	=> ''
    ];

    /**
     * 客户id
     *
     * @var null
     */
    private $users_id = null;

    /**
     * 使用状态
     *
     * @var null
     */
    private $use_status = null;

    /**
     * 充值成功通知状态
     *
     * @var null
     */
    private $sms_notice_status = null;

    /**
     * 通知成功响应给上游的信息
     *
     * @var string
     */
    private $success_response_b = "ok";

    /**
     * 通知失败响应给上游的信息
     *
     * @var string
     */
    private $fail_response_b = "error";

    /**
     * 货币类型
     * @var array
     */
    private $vou_currency = [
        0xC001  => 'USD',
        0xC002  => 'CNY'
    ];

    /**
     * 货币符号
     * @var array
     */
    private $currency_type = [
        'USD'   => 0xC001,
        'CNY'   => 0xC002,
    ];

    /**
     * 通知日志id
     *
     * @var null
     */
    private $notify_log_id = null;

    /**
     * 响应信息最长存储长度
     *
     * @var int
     */
    private $max_response_length = 1000;

    /**
     * 接收上游通知并转接下游通知
     *
     * params:[
     * 	ticket: string,      // 兑换券号
    currency: int,       // 兑换券货币类型
    value: numeric(10,2),// 兑换券面值
    order_id: string     // 商城订单号
    crm_order: string,   // 完成激活时商户发送过来的CRMOrderNo
     * ]
     * @param Request $request
     * @return mixed
     */
    public function receiveAndNotifyMerchant(Request $request){

        $this->getNotifyParameters($request);

        //检查充值卡是否存在
        $cards = CashCardsModel::where('card_no',$this->receive_params['ticket'])->first();
        if(empty($cards)){
            Log::warning('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'message' => "this card is not exists.",
                'card info' => $this->receive_params
            ]);

            $this->updateNotifyLog([
                'response_b'	=> $this->fail_response_b,
                'remarks'		=> '该充值卡在商城系统不存在,充值卡卡号：'.$this->receive_params['ticket']
            ]);
            exit($this->fail_response_b);
        }

        $this->users_id = $cards->users_id;
        $this->use_status = $cards->use_status;
        $this->sms_notice_status = $cards->sms_notice_status;

        //检查充值卡有没有更新使用状态
        if($this->use_status != 1){
            try{
                CashCardsModel::where('card_no',$this->receive_params['ticket'])->update(['use_status'=>1]);
            }
            catch(\Exception $e){
                Log::warning('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                    'message' => "Update card use status failed.",
                    'card no.' => $this->receive_params['ticket']
                ]);
            }
			try{
				PurchaseRecordsModel::where('card_no',$this->receive_params['ticket'])->update(['use_status'=>1]);
			}
			catch (\Exception $e){
				Log::error("Update purchase_records's use_status after activation",[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
			}
        }

        //检查充值卡有没有更新通知状态
        if($this->sms_notice_status != 1){
            $customer_email = UsersModel::where('id',$this->users_id)->value('email');
            $redis_key = $this->dispatchMailJob(MAIL::payment_success,
                [
                    'Vouchers'             => $this->receive_params['ticket'],
                    'Value'				   => $this->receive_params['value'],
                    'Currency'			   => $this->vou_currency[$this->receive_params['currency']]
                ],$customer_email);

            try{
                CashCardsModel::where('card_no',$this->receive_params['ticket'])->update(['sms_redis_key'=>$redis_key]);
            }
            catch(\Exception $e){
                Log::warning('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                    'message' => "Update card sms_redis_key failed.",
                    'card no.' => $this->receive_params['ticket']
                ]);
            }
        }

        //检查有没有正确接收到商户信息
        if(empty($cards->merchant_code)){
            $this->updateNotifyLog([
                'response_b'	=> $this->fail_response_b,
                'remarks'		=> '该充值卡激活时没有正确记录有效商户号：'.$cards->merchant_code
            ]);
            exit($this->fail_response_b);
        }
        $this->merchant = MerchantSecurityModel::where('merchant_code',$cards->merchant_code)->first();
        if(empty($this->merchant)){
            $this->updateNotifyLog([
                'response_b'	=> $this->fail_response_b,
                'remarks'		=> '商户号'.$cards->merchant_code.'没有配置密钥'
            ]);
            exit($this->fail_response_b);
        }
        if(empty($cards->merchant_notify_url)){
            $this->updateNotifyLog([
                'response_b'	=> $this->fail_response_b,
                'remarks'		=> '该充值卡激活时，商户 '.$cards->merchant_code.' 没有传接收通知的地址，充值卡卡号：'.$this->receive_params['ticket']
            ]);
            exit($this->fail_response_b);
        }
        $this->notify_url = $cards->merchant_notify_url;

        //检查有没有正确通知到商户
        if($cards->merchant_notify_status == 1){
            $this->updateNotifyLog([
                'response_b'	=> $this->success_response_b,
                'remarks'		=> '商户 '.$cards->merchant_code.' 已经接收到通知并正常响应了，充值卡卡号：'.$this->receive_params['ticket']
            ]);
            exit($this->success_response_b);
        }

        //记录通知次数
        try{
            CashCardsModel::where('card_no',$this->receive_params['ticket'])->update(['merchant_notify_times'=>$cards->merchant_notify_times+1]);
        }
        catch(\Exception $e){
            Log::warning('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'message' => "Failed to update merchant_notify_times.",
                'merchant_notify_times' => $cards->merchant_notify_times+1
            ]);
        }

        //通知商户 充值卡激活信息
        return $this->notifyMerchantCard();

    }

    /**
     * 获取通知信息
     *
     * @param Request $request
     */
    private function getNotifyParameters(Request $request){

        $response = json_decode(file_get_contents("php://input"),true);
        $this->createNotifyLog($request,$response);
        $token = $request->header($this->b_security['notify_token_key']);
        if(empty($token)){
            $this->updateNotifyLog([
                'response_b'	=> $this->fail_response_b,
                'remarks'		=> '非法请求，请求认证头不正确'
            ]);
            exit($this->fail_response_b);
        }

        $this->verify_header_token($token);

        if(!isset($response['code']) || !isset($response['data']) || !isset($response['message'])){
            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'message' => "Notify parameters format error.",
                'notify parameters' => $response
            ]);

            $this->updateNotifyLog([
                'response_b'	=> $this->fail_response_b,
                'remarks'		=> '非法请求，上游通知参数格式不匹配：'.json_encode($response)
            ]);
            exit($this->fail_response_b);
        }

        if($response['code'] != 0){
            $this->updateNotifyLog([
                'response_b'	=> $this->success_response_b,
                'remarks'		=> '通知状态码不正确，正常接收响应的状态码应该是 0,而实际接收到的参数是：'.json_encode($response)
            ]);
            exit($this->success_response_b);
        }

        $this->receive_params = $response['data'];

    }

    /**
     * 创建通知日志
     *
     * @param Request $request
     * @param $receive_params
     */
    private function createNotifyLog(Request $request,$receive_params){

        $ip = $this->getUserIp();

        $params = [
            'sys_ip'			=> $ip,
            'ip_address'		=> $this->getIpAddress($ip),
            'api'				=> $request->getRequestUri(),
            'method'			=> $request->getMethod(),
            'receive_params'	=> json_encode($receive_params),
            'receive_time'		=> date('Y-m-d H:i:s'),
            'created_at'		=> date('Y-m-d H:i:s')
        ];

        try{
            $this->notify_log_id = NotifyMerchantCardLogModel::insertGetId($params);
        }
        catch(\Exception $e){
            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'message' => "Failed to create notify log.",
                'params' => $params
            ]);

            exit($this->success_response_b);
        }

    }

    /**
     * 更新通知日志
     *
     * @param array $update_data
     */
    private function updateNotifyLog(array $update_data){

        try{
            NotifyMerchantCardLogModel::where('id',$this->notify_log_id)->update($update_data);
        }
        catch(\Exception $e){
            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'message' => "Failed to update notify log.",
                'update data' => $update_data
            ]);

            exit('error');
        }

    }

    /**
     * 通知商户 充值卡激活信息
     */
    private function notifyMerchantCard(){

        $this->notify_parameters['Code'] = '00';
        $this->notify_parameters['Message'] = 'the voucher was activated';
        $this->notify_parameters['VoucherNo'] = $this->receive_params['ticket'];
        $this->notify_parameters['VoucherValue'] = $this->receive_params['value'];
        $this->notify_parameters['Currency'] = $this->vou_currency[$this->receive_params['currency']];
        $this->notify_parameters['MallOrderId'] = $this->receive_params['order_id'];
        $this->notify_parameters['CRMOrderNo'] = $this->receive_params['crm_order'];

        $server_tool = new ServerEncryptTools($this->merchant->security_key,$this->merchant->security_salt);
        $notify_params = $server_tool->createRequestData($this->notify_parameters);

        $update_data = [
            'notify_url'	=> $this->notify_url,
            'notify_parameters'	=> json_encode($this->notify_parameters),
            'sign_data'	=> json_encode($notify_params),
            'notify_time'	=> date('Y-m-d H:i:s')
        ];

        try{
            $resp = $this->http_post_json_data($this->notify_url,$notify_params);
            $update_data['crm_response'] = $resp;
        }
        catch(\Exception $e){
            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'title' => "Failed to notify merchant.",
                'message' => $e->getMessage()
            ]);

            $message = $e->getMessage();
            if(!is_string($message)){
                $message = json_encode($message);
            }
            if(strlen($message) > $this->max_response_length){
                $message = substr($message,0,$this->max_response_length);
            }

            $update_data['crm_response'] = $message;
            $update_data['remarks'] = "通知商户时发生异常，异常信息请看通知商户后返回的响应信息";
            $this->updateNotifyInfo(-1,$message);
            $resp = "error";
        }

        if(strtoupper($resp) == 'OK' || strtoupper($resp) == 'SUCCESS'){
            $update_data['response_b'] = $this->success_response_b;
            $update_data['remarks'] = "通知商户响应成功";
            $this->updateNotifyInfo(1,$resp);
            $this->updateNotifyLog($update_data);
            exit($this->success_response_b);
        }

        $update_data['response_b'] = $this->fail_response_b;
        $update_data['remarks'] = "通知商户响应了错误的信息";
        $this->updateNotifyLog($update_data);
        exit($this->fail_response_b);

    }

    /**
     * 更新通知状态和响应信息
     *
     * @param $status
     * @param $response
     */
    private function updateNotifyInfo($status,$response){

        if(!is_string($response)){
            $response = json_encode($response);
        }

        if(strlen($response) > $this->max_response_length){
            $response = substr($response,0,$this->max_response_length);
        }

        try{
            CashCardsModel::where('card_no',$this->receive_params['ticket'])->update([
                'merchant_notify_status'	=> $status,
                'merchant_notify_response'	=> $response
            ]);
        }
        catch(\Exception $e){
            Log::warning('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'message' => "Update card notify info failed.",
                'merchant_notify_status'	=> $status,
                'merchant_notify_response' => $response
            ]);
        }

    }

    /**
     * 生成充值卡
     *
     * @return bool
     */
    private function createCashCards(){

        $date_time = date($this->time_fmt);
        $payment_order = PaymentOrdersModel::where('order_no',$this->receive_params['order_id'])->first();
        $card_key = $this->getCardKeyFromB($payment_order->users_id);
        $this->users_id = $payment_order->users_id;

        $new_card = [
            'users_id'				=> $payment_order->users_id,
            'purchase_records_id'	=> $payment_order->purchase_records_id,
            'purchase_code'			=> $payment_order->purchase_code,
            'order_no'				=> $this->receive_params['order_id'],
            'trade_no'				=> $payment_order['trade_no'],
            'card_no'				=> $this->receive_params['ticket'],
            'card_key'				=> $card_key,
            'card_value'			=> $payment_order->order_amount,
            'currency_type_code'	=> $payment_order->currency_type_code,
            'created_at'			=> $date_time,
            'updated_at'			=> $date_time,
            'use_status'			=> 1,
            'success_time'			=> $date_time
        ];

        $id = CashCardsModel::insertGetId($new_card);
        if(empty($id)){
            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                'message' => "Create cash cards fail",
                'new card'	=> $new_card
            ]);
            return false;
        }

        return true;

    }

    /**
     * 获取充值卡密码
     *
     * @param $users_id
     * @return string
     */
    private function getCardKeyFromB($users_id){

        $user = UsersModel::where('id',$users_id)->first();

        $msg = $this->post_data_from_B($this->b_web_site.$this->b_api['voucher_seckey'],[
            'voucher_id'	=> $this->receive_params['ticket']
        ],$user['user_code'],$user['safety_code']);

        if(is_null(json_decode($msg))){
            return "";
        }

        $resp = json_decode($msg,true);

        return $resp['data'];

    }



}
