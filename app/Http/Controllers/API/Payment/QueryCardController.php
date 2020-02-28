<?php
namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\RESPONSE;
use App\Http\Toolkit\ServerEncryptTools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\CashCardsModel;
use App\Models\MerchantSecurityModel;
use App\Models\UsersModel;

class QueryCardController extends ApiController
{

	use CommunicateWithB;

	/**
	 * 激活请求接口
	 *
	 * @var null
	 */
	private $query_api = null;

	/**
	 * 用户编码
	 *
	 * @var null
	 */
	private $user_code = null;

	/**
	 * 商户实体
	 *
	 * @var null
	 */
	private $merchant = null;

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
	 * 充值卡实体
	 *
	 * @var null
	 */
	private $cash_card = null;

	/**
	 * CRM 充值卡查询参数
	 *
	 * @var array
	 */
	private $CRM_query_params = [
		'MerchantCode'  => '',//商户号
		'MerchantId'    => '',//商户ID
		'VoucherNo'     => '',//兑换码
		'SignType'      => ''//签名类型
	];

	/**
	 * B系统查询请求参数
	 * @var array
	 */
	private $req_B_params = [
		'ucode'     => '',//User Code
		'ticket'    => '',//兑换码
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
		'09'	=> 'This gateway is only used to activate the dollar currency'
	];

	/**
	 * 充值卡查询接口
	 *
	 * @param Request $request
	 * @return array
	 */
	public function queryVoucher(Request $request){

		$response = $this->doQuery($request);

		$data = $this->encrypt_result($response);

		return ['result'=>$response,'response'=>$data];

	}

	/**
	 * 执行充值卡查询
	 *
	 * @param Request $request
	 * @return mixed|string
	 */
	private function doQuery(Request $request){

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

		try{
			$parameters = $this->getRequestParameters($inputs,$request->getClientIp());
		}
		catch (\Exception $e){
			Log::error(__FUNCTION__, [
                "merchant_ip"	=> $request->getClientIp(),
				"msg" => $e->getMessage(),
				"trace" => $e->getTrace()
			]);

			return 'Invalid request, the request data received is incorrect.';
		}

		$validation = $this->validateParameters($parameters);
		if($validation['failed']){
			return $this->error_message($validation['code']);
		}

		$verify = $this->verifySignData($inputs);
		if($verify['failed']){
			return $this->error_message($validation['code']);
		}

		try{
			$headers = $this->get_headers();
			$resp = $this->http_post_B($this->query_api,$this->req_B_params,$headers);
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
		foreach ($this->CRM_query_params as $key => $value){
			if($value !== NULL){
				if(!array_key_exists($key,$parameters)){
					return ['failed'=>true,'code'=>'01'];
				}
				$this->CRM_query_params[$key] = $parameters[$key];
			}
			else{
				if(isset($parameters[$key])){
					$this->CRM_query_params[$key] = $parameters[$key];
				}
				else{
					unset($this->CRM_query_params[$key]);
				}
			}
		}

		//验证商户号 是否存在
		$this->merchant = MerchantSecurityModel::where('merchant_code',$this->CRM_query_params['MerchantCode'])->first();
		if($this->merchant == null){
			return ['failed'=>true,'code'=>'02'];
		}

		//验证商户密钥是否已经配置
		if(empty($this->merchant->security_key) || empty($this->merchant->security_salt)){
			return ['failed'=>true,'code'=>'03'];
		}

		//验证充值卡号是否存在
		$this->cash_card = CashCardsModel::where('card_no',$this->CRM_query_params['VoucherNo'])->first();
		if($this->cash_card == null){
			return ['failed'=>true,'code'=>'06'];
		}

		$this->user_code = UsersModel::where('id',$this->cash_card->users_id)->value('user_code');
		$this->query_api = $this->b_web_site.$this->b_api['query_voucher'];

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

		$this->req_B_params['ucode'] = MerchantSecurityModel::where('merchant_code',$this->CRM_query_params['MerchantCode'])->value('user_code');
		$this->req_B_params['ticket'] = $this->CRM_query_params['VoucherNo'];

	}

	/**
	 * 处置查询响应信息
	 *
	 * @param $resp
	 * @return mixed
	 */
	private function dispose_response($resp){
		if(is_null(json_decode($resp))){
			$result['Code'] = '07';
			$result['Message'] = 'Unexpected Error';
		}else{
			$res = json_decode($resp);
			if($res->code == '0'){
				$result['VoucherNo'] = $res->data->ticket;
				//$result['Status']  = $res->data->status;
				if($res->data->status == 0){//未使用
					$result['Code'] = "10";
					$result['Message'] = "Unused";
				}else if($res->data->status == 1){//已激活
					$result['Code'] = "00";
					$result['Message'] = "Active Success";
					$result['Currency'] = $this->vou_currency[$res->data->currency];
					$result['VoucherValue'] = $res->data->value;
					$result['MallOrderId'] = $res->data->order_id;
				}else  if($res->data->status == 3){//已退款
					$result['Code'] = "30";
					$result['Message'] = "Refunded";
					$result['Currency'] = $this->vou_currency[$res->data->currency];
					$result['VoucherValue'] = $res->data->value;
					$result['MallOrderId'] = $res->data->order_id;
				}else{
					$result['Code'] = "40";
					$result['Message'] = "Unknown Status";
				}
			}else{
				if($res->code < $this->b_error_code_critical){
					$result['Code'] = $res->code + 10;
					$result['Message'] = $res->message;
				}else{
					$result['Code'] = '07';
					$result['Message'] = $this->response_code['07'];
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
