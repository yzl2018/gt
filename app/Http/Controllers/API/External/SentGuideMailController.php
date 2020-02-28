<?php
namespace App\Http\Controllers\API\External;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\DataValidator;
use App\Http\Toolkit\GetIpLocation;
use App\Http\Toolkit\MailChannelsDispatch;
use App\Http\Toolkit\RESPONSE;
use App\Http\Toolkit\ServerEncryptTools;
use App\Models\MerchantSecurityModel;
use App\Models\RequestGuideMailRecordsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SentGuideMailController extends ApiController
{

	use MailChannelsDispatch,GetIpLocation,DataValidator;

	/**
	 * 商户实体
	 *
	 * @var null
	 */
	private $merchant = null;

	/**
	 * 引导邮件类型
	 *
	 * @var array
	 */
	private $guide_types = [
		0xE307,//purchase 58119
		0xE308,//activation 58120
		0xE309,//purchase and activation 58121
	];

	/**
	 * 单个ip的请求间隔
	 *
	 * @var int
	 */
	private $ip_request_interval = 10;//10秒

	/**
	 * 单个邮箱发送间隔
	 *
	 * @var int
	 */
	private $email_sent_interval = 300;//5分钟

	/**
	 * @var string
	 */
	private $client_ip = '127.0.0.1';

	/**
	 * @var null
	 */
	private $crm_request_log_id = null;

	/**
	 * @var string
	 */
	private $login_uri = '';

	/**
	 * @var string
	 */
	private $merchant_host = '';

	/**
	 * CRM 请求参数
	 *
	 * @var array
	 */
	private $CRM_req_params = [
		'MerchantCode'  	=> '',//商户号
		'CustomerEmail'		=> '',//客户邮箱
		'GuideType'			=> '',//引导邮件类型
		'ClientIp'			=> NULL
	];

	/**
	 * @var array
	 */
	private $update_data = [];

	/**
	 * 响应状态码
	 * @var array
	 */
	private $resp_code = [
		0xE00	=> 'Unexpected Error',//未预期的错误	3584
		0xE01	=> 'Invalid request, the request data received is incorrect.',//非法清求 接收到的清求数据不正确 3585
		0xE02	=> 'Verify fail,  the Data format is incorrect.',//请求的数据格式不正确 3586
		0xE03	=> 'Missing parameter',//缺少参数 3587
		0xE04	=> 'Merchant code does not exists',//商户号不存在 3588
		0xE05	=> 'Merchant security key does not configuration',//未配置商户密钥 3589
		0xE06	=> 'Invalid email format',//非法邮箱格式 3590
		0xE07	=> 'Invalid guide type',//非法引导类型 3591
		0xE08	=> 'Verify fail',//验签失败 3592
		0xE09	=> 'Your requests are too frequent. Please try again after 10 seconds.',//单个IP请求接口的间隔频率不能小于10秒 3593
		0xE0A	=> 'This email has been sent in the last 5 minutes. Please try again later.',//发送给同一邮箱的间隔不能小于5分钟 3594
		0xE0B	=> 'Dispatch mail job fail',//派发邮件任务失败 3595
		0xE0C	=> 'There is no replenishment the template mail of activation for this merchant yet.',//目前还没有为这个商户补充兑换券激活模板邮件 3596
		0xE10	=> 'Dispatch mail job Successful',//派发邮件任务成功 3600
        0xE11	=> '',//禁止商户发送邮件 3601
	];

	public function sendGuidanceMail(Request $request){

		try{
			$inputs = $this->getRequestData($request);
		}
		catch (\Exception $e){
			Log::error(__FUNCTION__, [
				"merchant_ip"	=> $request->getClientIp(),
				"msg" => $e->getMessage(),
				"trace" => $e->getTrace()
			]);

			return $this->resp_message(0xE01);
		}

		if(empty($inputs) || count($inputs) == 0){
			$headers = $request->headers->all();
			Log::error("The request data can not be resolve.", [
				"request_ip"	=> $request->getClientIp(),
				"Content-Type" => $headers['content-type'],
				"Content" => $request->getContent()
			]);


			return $this->resp_message(0xE02);
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

			return $this->resp_message(0xE02);
		}

		$validation = $this->validateParameters($parameters);
		if($validation['failed']){
			return $this->resp_message($validation['code']);
		}

		if(isset($this->CRM_req_params['ClientIp'])){
			$this->client_ip = $this->CRM_req_params['ClientIp'];
		}
		else{
			$this->client_ip = $this->getUserIp();
		}

		$this->createRequestRecord($request,$inputs,$parameters);

		if($this->canThisIpRequest() == false){
			return $this->resp_message(0xE09);
		}

		if($this->canThisEmailBeSent() == false){
			return $this->resp_message(0xE0A);
		}

		$verify = $this->verifySignData($inputs);
		if($verify['failed']){
			return $this->resp_message($validation['code']);
		}

		$this->getLoginUri();

		if($this->CRM_req_params['GuideType'] == 0xE309){
			$this->autoDispatchMailJob(0xE307,[
				'merchant_code'	=> $this->CRM_req_params['MerchantCode'],
				'access_host'	=> $this->merchant_host
			],$this->CRM_req_params['CustomerEmail']);

			$dispatch = $this->autoDispatchMailJob(0xE308,[
				'merchant_code'	=> $this->CRM_req_params['MerchantCode'],
				'access_host'	=> $this->merchant_host
			],$this->CRM_req_params['CustomerEmail'],120);
		}
		else{
			$dispatch = $this->autoDispatchMailJob($this->CRM_req_params['GuideType'],[
				'merchant_code'	=> $this->CRM_req_params['MerchantCode'],
				'access_host'	=> $this->merchant_host
			],$this->CRM_req_params['CustomerEmail']);
		}

		$this->update_data['dispatch_time'] = date($this->time_fmt);

		if($dispatch['success']){
			$this->update_data['dispatch_status'] = 1;
			$this->update_data['redis_key'] = $dispatch['message'];
			return $this->resp_message(0xE10);
		}

		else{
			$this->update_data['dispatch_status'] = -1;
			$this->resp_code[0xE0B] .= ":".$dispatch['message'];
			return $this->resp_message(0xE0B);
		}

	}

	/**
	 * 生成请求记录
	 *
	 * @param Request $request
	 * @param $inputs
	 * @param $parameters
	 */
	private function createRequestRecord(Request $request,$inputs,$parameters){

		$headers = $request->headers->all();
		$content_type = $headers['content-type'][0];

		$req_params = [
			'client_ip'		=> $this->client_ip,
			'ip_address'	=> $this->getIpAddress($this->client_ip),
			'api'				=> $request->getRequestUri(),
			'method'			=> $request->getMethod(),
			'content_type'		=> $content_type,
			'request_time'		=> date($this->time_fmt),
			'request_params'	=> json_encode($inputs),
			'merchant_code'		=> $this->CRM_req_params['MerchantCode'],
			'customer_email'	=> $this->CRM_req_params['CustomerEmail'],
			'guide_type'		=> $this->CRM_req_params['GuideType'],
			'sign_data'			=> json_encode($parameters),
		];

		try{
			$req_log_id = RequestGuideMailRecordsModel::insertGetId($req_params);

			if($req_log_id){
				$this->crm_request_log_id = $req_log_id;
			}

		}
		catch (\Exception $e){
			Log::error('===New crm request mail record===',[
				'msg'	=> $e->getMessage(),
				'trace'	=> $e->getTrace()
			]);
		}

	}

	/**
	 * 检测控制单个ip的请求频率
	 *
	 * @return bool
	 */
	private function canThisIpRequest(){

		$now_time = time();
		if(Cache::store('redis')->has($this->client_ip)){
			$latest_time = Cache::store('redis')->get($this->client_ip);
			if(($now_time - $latest_time) < $this->ip_request_interval){
				return false;
			}
		}

		Cache::store('redis')->put($this->client_ip,$now_time,1);

		return true;

	}

	/**
	 * 检测控制发送给单个邮箱邮件的频率
	 *
	 * @return bool
	 */
	private function canThisEmailBeSent(){

		$now_time = time();
		$cus_email = $this->CRM_req_params['CustomerEmail'];
		if(Cache::store('redis')->has($cus_email)){
			$latest_time = Cache::store('redis')->get($cus_email);
			if(($now_time - $latest_time) < $this->email_sent_interval){
				return false;
			}
		}

		Cache::store('redis')->put($cus_email,$now_time,10);

		return true;

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
					return ['failed'=>true,'code'=>0xE03];
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
			return ['failed'=>true,'code'=>0xE04];
		}

        //禁止商户发送邮件
        //if($this->CRM_req_params['MerchantCode'] == 10035){
        //    return ['failed'=>true,'code'=>0xE11];
       // }


        //验证商户密钥是否已经配置
		if(empty($this->merchant->security_key) || empty($this->merchant->security_salt)){
			return ['failed'=>true,'code'=>0xE05];
		}

		//验证客户邮箱的格式是否正确
		if($this->EmailValidator($this->CRM_req_params['CustomerEmail']) == false){
			return ['failed'=>true,'code'=>0xE06];
		}

		//验证邮件类型是否存在
		if(!in_array($this->CRM_req_params['GuideType'],$this->guide_types)){
			return ['failed'=>true,'code'=>0xE07];
		}

		//验证该商户是否允许发送充值引导邮件模板
//		if($this->CRM_req_params['GuideType'] != 0xE307){
//			$allow_merchants = config('system.sent_activation_guide');
//			if(!in_array($this->merchant->merchant_code,$allow_merchants)){
//				return ['failed'=>true,'code'=>0xE0C];
//			}
//		}

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
			return ['failed'=>true,'code'=>0xE08];
		}

		return ['failed'=>false,'code'=>RESPONSE::SUCCESS];

	}

	/**
	 * 获取商户号对应的客户登陆地址
	 */
	private function getLoginUri(){

		$login_urls = config('system.login_uri');
		$merchant_hosts = config('system.merchant_host');
		$merCode = $this->CRM_req_params['MerchantCode'];

		$this->login_uri = $login_urls['default'];
		$this->merchant_host = $merchant_hosts['default'];

		if(array_key_exists($merCode,$login_urls)){
			$this->login_uri = $login_urls[$merCode];
		}

		if(array_key_exists($merCode,$merchant_hosts)){
			$this->merchant_host = $merchant_hosts[$merCode];
		}

	}

	/**
	 * 生成错误信息
	 *
	 * @param $code
	 * @return mixed
	 */
	private function resp_message($code){

		if(!array_key_exists($code,$this->resp_code)){
			$code = 0xE00;
		}

		$result = ['Code'=>$code,'Message'=>$this->resp_code[$code]];

		$this->update_data['resp_result'] = json_encode($result);

		if($this->crm_request_log_id){
			try{
				RequestGuideMailRecordsModel::where('id',$this->crm_request_log_id)->update($this->update_data);
			}
			catch(\Exception $e){
				Log::error('===Update crm request mail record error===',[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
			}
		}

		if(isset($this->CRM_req_params['ClientIp'])){

			return response()->json($result);

		}

		else{

			if(empty($this->merchant)){
				return $code.':'.$this->resp_code[$code];
			}

			else{
				$server_tool = new ServerEncryptTools($this->merchant->security_key,$this->merchant->security_salt);
				return response()->json($server_tool->createRequestData($result));
			}

		}

	}

}
