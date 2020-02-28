<?php
namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\MailChannelsDispatch;
use App\Http\Toolkit\RESPONSE;
use App\Http\Toolkit\ServerEncryptTools;
use App\Mail\MAIL;
use App\Models\CashCardsModel;
use App\Models\MerchantSecurityModel;
use App\Models\PurchaseRecordsModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OneActiveCardController extends ApiController
{
	use MailChannelsDispatch,CommunicateWithB;

	/**
	 * 激活请求接口
	 *
	 * @var null
	 */
	private $active_api = null;

	/**
	 * 用户编码
	 *
	 * @var null
	 */
	private $user_code = null;

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
	 * 商户实体
	 *
	 * @var null
	 */
	private $merchant = null;

	/**
	 * 充值卡实体
	 *
	 * @var null
	 */
	private $cash_card = null;

	/**
	 * CRM 激活请求参数
	 *
	 * @var array
	 */
	private $CRM_req_params = [
		'MerchantCode'  	=> '',//商户号
		'MerchantId'    	=> '',//商户ID
		'CRMOrderNo'		=> NULL,//商户 激活订单号
		'VoucherNo'     	=> '',//兑换码
		'VoucherKey'    	=> '',//兑换密码
		'VoucherValue'  	=> NULL,//兑换面值
		'Currency'      	=> '',//货币类型
		'SignType'      	=> ''//签名类型
	];

	/**
	 * B系统兑换请求参数 携带面值和货币类型
	 * @var array
	 */
	private $req_B_params = [
		'cid'		=> '',//商户订单号
		'ucode'     => '',//User Code
		'ticket'    => '',//兑换码
		'amount'    => '',//面值
		'currency'  => '',//货币类型
		'secret'    => ''//兑换密码
	];

	/**
	 * 响应状态码
	 * @var array
	 */
	private $resp_code = [
		'00'    => 'Active success',
		'01'    => 'Missing parameter',
		'02'    => 'Merchant code does not exists',
		'03'    => 'Merchant security key does not configuration',
		'04'    => 'Verify fail',
		'05'    => 'Duplicate active',
		'06'    => 'Voucher number does not exists',
		'07'    => 'Unexpected error',
		'08'	=> 'The length of the active number must be between 8 and 32 bits',
		'09'	=> 'This gateway is only used to activate the dollar currency',
		'605'	=> 'Voucher’s password error',
		'606'	=> 'The currency type of voucher is incorrect',
		'607'	=> 'The amount is not equal to voucher’s value'
	];

	/**
	 * 充值卡激活接口
	 *
	 * @param Request $request
	 * @return array
	 */
	public function activeCard(Request $request){

        try{
            $response = $this->doActive($request);
			//dd($response);
        }
        catch(\Exception $e){
            Log::error(__FUNCTION__,[
                'message'	=> $e->getMessage(),
                'trace'		=> $e->getTrace()
            ]);

            $response = "07:Unexpected error";
        }

		$data = $this->encrypt_result($response);

		return ['result'=>$response,'response'=>$data];

	}

	/**
	 * 激活充值卡
	 *
	 * @param Request $request
	 * @return mixed
	 */
	private function doActive(Request $request){
		try{
			$inputs = $this->getRequestData($request);
		}
		catch (\Exception $e){
			Log::error(__FUNCTION__, [
                "merchant_ip"	=> $request->getClientIp(),
				"msg" => $e->getMessage(),
				"trace" => $e->getTrace()
			]);

			return 'Invalid request, the request data received is incorrect.';
		}

        if(empty($inputs) || count($inputs) == 0){
            $headers = $request->headers->all();
            Log::error("The request data can not be resolve.", [
                "request_ip"	=> $request->getClientIp(),
                "Content-Type" => $headers['content-type'],
                "Content" => $request->getContent()
            ]);
            exit('Verify fail,  the Data format is incorrect.');
        }

		try{
			$parameters = $this->getRequestParameters($inputs,$request->getClientIp());
		}
		catch (\Exception $e){
			Log::error(__FUNCTION__, [
                "merchant_ip"	=> $request->getClientIp(),
				"msg" => $e->getMessage(),
				"trace" => $e->getTrace()
			]);

            return 'Verify fail, the Data format is incorrect.';
		}

		$validation = $this->validateParameters($parameters);
		if($validation['failed']){
			return $this->error_message($validation['code']);
		}
		
		if(env('APP_LIVE') == false && $this->CRM_req_params['MerchantCode'] != '10021'){
			return ['Code'=>'11','Message'=>'Please do not use another merchant code on Demo servers for activation testing.'];
		}

		$currency_access = $this->checkCurrencyForAccess($this->CRM_req_params['Currency']);
		if($currency_access['denied']){
			return ['Code'=>'10','Message'=>$currency_access['message']];
		}

		$verify = $this->verifySignData($inputs);
		if($verify['failed']){
			return $this->error_message($validation['code']);
		}

		try{
			$headers = $this->get_headers();
			$resp = $this->http_post_B($this->active_api,$this->req_B_params,$headers);
		}
		catch (\Exception $e){
			Log::error(__FUNCTION__, [
                "merchant_ip"	=> $request->getClientIp(),
				"msg" => $e->getMessage(),
				"trace" => $e->getTrace()
			]);

			return 'B:Unexpected error.';
		}

		return $this->dispose_response($resp);

	}

	/**
	 * 正确获取请求参数
	 *
	 * @param $inputs
     * @param $ip
	 * @return array|bool
	 * @throws \Exception
	 */
	private function getRequestParameters($inputs,$ip = ""){

        if(empty($inputs)){
            Log::warning(__FUNCTION__,[
                'merchant_ip'	=> $ip,
                'The data received is empty'	=> $inputs
            ]);
            throw new \Exception('The data received is empty.');
        }

		$tool = new ServerEncryptTools('','');
		$parameters = $tool->getResponseData($inputs);
		if($parameters == false){
            Log::warning(__FUNCTION__,[
                'merchant_ip'	=> $ip,
                'The data format is incorrect'	=> $inputs
            ]);
            throw  new \Exception('Failure to properly parse request data');
		}

		return $parameters;

	}

	/**
	 * 验证请求参数是否合法
	 *
	 * @param array $parameters
	 * @return array
	 */
	private function validateParameters(array $parameters){

		//验证参数是否齐全
		foreach ($this->CRM_req_params as $key => $value){
			if($value !== NULL){
				if(!array_key_exists($key,$parameters)){
					return ['failed'=>true,'code'=>'01'];
				}
				$this->CRM_req_params[$key] = $parameters[$key];
			}
			else{
				if(isset($parameters[$key])){
					$this->CRM_req_params[$key] = $parameters[$key];
				}
				else{
					unset($this->CRM_req_params[$key]);
				}
			}
		}

		//验证商户号 是否存在
		$this->merchant = MerchantSecurityModel::where('merchant_code',$this->CRM_req_params['MerchantCode'])->first();
		if($this->merchant == null){
			return ['failed'=>true,'code'=>'02'];
		}

		//验证商户密钥是否已经配置
		if(empty($this->merchant->security_key) || empty($this->merchant->security_salt)){
			return ['failed'=>true,'code'=>'03'];
		}

		//验证激活订单号 长度是否合法
		if(isset($this->CRM_req_params['CRMOrderNo'])){
			if(strlen($this->CRM_req_params['CRMOrderNo']) < 8 || strlen($this->CRM_req_params['CRMOrderNo']) > 32){
				return ['failed'=>true,'code'=>'08'];
			}
		}

		//验证充值卡号是否存在
		$this->cash_card = CashCardsModel::where('card_no',$this->CRM_req_params['VoucherNo'])->first();
		if($this->cash_card == null){
			return ['failed'=>true,'code'=>'06'];
		}

		//验证该充值卡是否已使用
		if($this->cash_card->use_status != 0){
			return ['failed'=>true,'code'=>'05'];
		}

        //若卡号存在且未被使用 则记录尝试激活次数
        try{
            CashCardsModel::where('id',$this->cash_card->id)->update(['attempt_times'=>$this->cash_card->attempt_times+1]);
        }
        catch(\Exception $e){
            Log::error('Update cash cards attempt times',[
                'msg'	=> $e->getMessage(),
                'trace'	=> $e->getTrace()
            ]);
        }

		//验证充值卡密码
//		if($this->cash_card->card_key != $this->CRM_req_params['VoucherKey']){
//			return ['failed'=>true,'code'=>'605'];
//		}

		//验证货币类型是否正确
		if($this->cash_card->currency_type_code != strtoupper($this->CRM_req_params['Currency'])){
			return ['failed'=>true,'code'=>'606'];
		}

		//验证充值金额是否匹配
		if(isset($this->CRM_req_params['VoucherValue'])){
			if(intval($this->cash_card->card_value) != $this->CRM_req_params['VoucherValue']){
				return ['failed'=>true,'code'=>'607'];
			}
		}

		$this->user_code = UsersModel::where('id',$this->cash_card->users_id)->value('user_code');

		$api_key = 'active_voucher_'.strtolower($this->CRM_req_params['Currency']);
		if(isset($this->b_api[$api_key])){
			$this->active_api = $this->b_web_site.$this->b_api[$api_key];
		}

		return ['failed'=>false,'code'=>RESPONSE::SUCCESS];

	}

	/**
	 * 请求数据验签
	 *
	 * @param array $inputs
	 * @return array
	 */
	private function verifySignData(array $inputs){

		$server_tool = new ServerEncryptTools($this->merchant->security_key,$this->merchant->security_salt);
		$server_tool->getResponseData($inputs);//Important,for get request parameters.
		if($server_tool->verify($inputs) == false){
			return ['failed'=>true,'code'=>'04'];
		}

		return ['failed'=>false,'code'=>RESPONSE::SUCCESS];

	}

	/**
	 * 生成认证请求头
	 *
	 * @return array
	 */
	private function get_headers(){

		$this->build_params_b();

        return $this->create_user_auth($this->user_code,$this->sys_security_code);

	}

	/**
	 * 构造B系统所需请求参数
	 */
	private function build_params_b(){

		if(isset($this->CRM_req_params['CRMOrderNo'])){
			$this->req_B_params['cid'] = $this->CRM_req_params['CRMOrderNo'];
		}
        if(env('APP_LIVE') == false && $this->CRM_req_params['MerchantCode'] == '10021'){
			$this->req_B_params['ucode'] = 'mF702DE100006';
		}
		else{
			$this->req_B_params['ucode'] = MerchantSecurityModel::where('merchant_code',$this->CRM_req_params['MerchantCode'])->value('user_code');
		}
		$this->req_B_params['ticket'] = $this->CRM_req_params['VoucherNo'];
		$this->req_B_params['secret'] = $this->CRM_req_params['VoucherKey'];
		if(isset($this->CRM_req_params['VoucherValue'])){
			$this->req_B_params['amount'] = $this->CRM_req_params['VoucherValue'];
		}
		else{
			$this->req_B_params['amount'] = $this->cash_card->card_value;
		}
		$this->req_B_params['currency'] = $this->currency_type[$this->CRM_req_params['Currency']];

	}

    /**
     * 处置兑换响应信息
     *
     * @param $resp
     * @return array
     */
    public function dispose_response($resp){
        $result = [
            'Code'          => '',
            'VoucherNo'     => '',
            'VoucherValue'  => NULL,
            'Currency'      => NULL,
            'Message'       => ''
        ];

        $update_data = [];

        if(is_null(json_decode($resp))){
            $result['Code'] = '07';
            $result['Message'] = 'Unexpected Error';
        }else{
            $res = json_decode($resp);
			//dd($res);
			$use_status = CashCardsModel::where('card_no',$this->CRM_req_params['VoucherNo'])->value('use_status');
			if($res->code == '0'){
				$flag = true;
			}
			else if(($res->code + 10) == 611 && $use_status != 1){
				$flag = true;
			}
			else{
				$flag = false;
			}

            if($flag){
                $result['Code'] = '00';

                $customer_email = UsersModel::where('user_code',$this->user_code)->value('email');
                try{
//                    $mail_type_code = $this->roundRobinArr([0xE013,0xE006],100);
//                    //$mail_type_code = 0xE013;
//                    $redis_key = $this->dispatchMailJob($mail_type_code,//使用配置文件派发邮件任务
//					[
//                        'VoucherNo'     => $res->data->ticket,
//                        'VoucherValue'  => $res->data->value,
//                        'Currency'      => $this->vou_currency[$res->data->currency]
//                    ],$customer_email);

                    $dispatch = $this->autoDispatchMailJob(MAIL::mail_send_activation_info,//使用数据库配置派发邮件任务
						[
							'VoucherNo'     => $res->data->ticket,
							'VoucherValue'  => $res->data->value,
							'Currency'      => $this->vou_currency[$res->data->currency]
						],$customer_email);
                    if($dispatch['success']){
						$redis_key = $dispatch['message'];
					}
					else{
						$redis_key = null;
					}

                }
                catch (\Exception $e){
                    Log::error("Dispatch mail job after activation",[
                        'msg'	=> $e->getMessage(),
                        'trace'	=> $e->getTrace()
                    ]);
                }

                $update_data = [
                    'use_status'	=> 1,
                    'merchant_code'	=> $this->CRM_req_params['MerchantCode'],
                    'success_time'	=> date('Y-m-d H:i:s')
                ];

                if(!empty($redis_key)){
                    $update_data['sms_redis_key'] = $redis_key;
                }

                if(isset($this->CRM_req_params['CRMOrderNo'])){
                    $update_data['crm_order_no'] = $this->CRM_req_params['CRMOrderNo'];
                }

                if(!empty($res->data->crm_order)){
                    $result['CRMOrderNo'] = $res->data->crm_order;
                }
				if(isset($res->data->acc_no)){
                    $result['ClientAccNo'] = $res->data->acc_no;
                }
                $result['MallOrderId'] = $res->data->order_id;
                $result['VoucherNo'] = $res->data->ticket;
                $result['VoucherValue'] = $res->data->value;
                $result['Currency'] = $this->vou_currency[$res->data->currency];
                $result['Message'] = 'Active Success';
				
            }else{
                $result['VoucherNo'] = $this->CRM_req_params['VoucherNo'];
                $result['VoucherValue'] = $this->cash_card->card_value;
                $result['Currency']	= $this->cash_card->currency_type_code;
                if($res->code < $this->b_error_code_critical){
                    $result['Code'] = $res->code + 10;
                    if($result['Code'] == 611){
                        $result['CRMOrderNo'] = $res->data->crm_order;
                    }
                    $result['Message'] = $res->message;
                }else{
                    $result['Code'] = '07';
                    $result['Message'] = $this->resp_code['07'];
                }
            }
        }

        $update_data['activation_message'] = $result['Message'];
        if(!empty($this->cash_card)){
            try{
                CashCardsModel::where('id',$this->cash_card->id)->update($update_data);
            }
            catch (\Exception $e){
                Log::error("Update cash cards after activation",[
                    'msg'	=> $e->getMessage(),
                    'trace'	=> $e->getTrace()
                ]);
            }
            if(isset($update_data['use_status'])){
            	try{
            		PurchaseRecordsModel::where('card_no',$this->CRM_req_params['VoucherNo'])->update(['use_status'=>$update_data['use_status']]);
				}
				catch (\Exception $e){
					Log::error("Update purchase_records's use_status after activation",[
						'msg'	=> $e->getMessage(),
						'trace'	=> $e->getTrace()
					]);
				}
			}
        }

        return $result;
    }

	/**
	 * 生成错误信息
	 *
	 * @param $code
	 * @return mixed
	 */
	private function error_message($code){

		if(array_key_exists($code,$this->resp_code)){
			return ['Code'=>$code,'Message'=>$this->resp_code[$code]];
		}
		else{
			return ['Code'=>$code,'Message'=>'Unknown Error'];
		}

	}

	/**
	 * 返回加密后的激活响应结果
	 *
	 * @param $response
	 * @return array|string
	 */
	private function encrypt_result($response){

		if(is_string($response)){
			return $response;
		}

		if(empty($this->merchant->security_key) || empty($this->merchant->security_salt)){
			return $response;
		}

		$server_tool = new ServerEncryptTools($this->merchant->security_key,$this->merchant->security_salt);

		return $server_tool->createRequestData($response);

	}

}
