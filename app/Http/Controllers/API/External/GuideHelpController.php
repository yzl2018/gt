<?php
namespace App\Http\Controllers\API\External;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CurlHttpRequest;
use App\Http\Toolkit\DataValidator;
use App\Http\Toolkit\EncryptTools;
use App\Http\Toolkit\GetIpLocation;
use App\Http\Toolkit\RESPONSE;
use App\Models\MerchantSecurityModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuideHelpController extends  ApiController
{

	use GetIpLocation,DataValidator,CurlHttpRequest;

	/**
	 * @var string
	 */
	private $client_ip = '127.0.0.1';

	/**
	 * @var null
	 */
	private $access_token = null;

	/**
	 * @var string
	 */
	private $request_api = '/api/guide/sent-mail';

	/**
	 * @var array
	 */
	private $guide_types = [
		'purchase'		=> 0xE307,//58119
		'activation'	=> 0xE308,//58120
	];

	/**
	 * @var null
	 */
	private $md5_sec_key = null;

	/**
	 * @var int
	 */
	private $success_code = 0xE10;

	/**
	 * @var string
	 */
	private $forbidden_msg = "You have no permission to access.";

	/**
	 * @var array
	 */
	private $request_params = [
		'MerchantCode'	=> '',
		'CustomerEmail'	=> '',
		'GuideType'		=> ''
	];

	/**
	 * 显示发送引导邮件的视图
	 *
	 * @param Request $request
	 * @param string $mer_code
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
	 */
	public function viewOperation(Request $request,string $mer_code){

		$mer_status = MerchantSecurityModel::where('merchant_code',$mer_code)->value('status');
		if(empty($mer_status)){
			return 'Access denied.';
		}

		if($mer_status != 1){
			return 'Your channel has been closed.';
		}

		$this->md5_sec_key = config('system.mall.md5_sec_key');

		$this->client_ip = $this->getUserIp();

		$this->access_token = $this->getAccessToken($this->client_ip);

		return view('guide.sendmail',[
			'mer_code'	=> $mer_code,
			'client_ip'	=> $this->client_ip,
			'access_token'	=> $this->access_token
		]);

	}

	/**
	 * 预备发送引导邮件
	 *
	 * @param Request $request
	 * @param string $guide_type
	 * @return mixed
	 */
	public function prepareSendMail(Request $request,string $guide_type){

		if(!array_key_exists($guide_type,$this->guide_types)){
			return $this->message('Invalid request.');
		}

		if(!$request->has('email') || !$request->has('mer_code') || !$request->has('client_ip') || !$request->has('access_token')){
			return $this->message('Invalid request.');
		}
		
		$this->md5_sec_key = config('system.mall.md5_sec_key');

		$email = $request->input('email');
		$mer_code = $request->input('mer_code');
		$client_ip = $request->input('client_ip');
		$access_token = $request->input('access_token');

		if($this->EmailValidator($email) == false){
			return $this->message('Invalid format of email.');
		}

		if(empty($client_ip)){
			return $this->message('Invalid request.');
		}

		$sign_token = $this->getAccessToken($client_ip);
		if($access_token !== $sign_token){
			return $this->message($this->forbidden_msg);
		}

		$this->request_params['MerchantCode'] = $mer_code;
		$this->request_params['CustomerEmail'] = $email;
		$this->request_params['GuideType'] = $this->guide_types[$guide_type];
		$this->request_params['ClientIp']	= $client_ip;

		$merchant = MerchantSecurityModel::where('merchant_code',$mer_code)->first();
		$tool = new EncryptTools($merchant->security_key,$merchant->security_salt);
		$params = $tool->createRequestData($this->request_params);

		return $this->app_response(RESPONSE::SUCCESS,'Prepare complete',$params);

	}

	/**
	 * @param string $ip
	 * @return string
	 */
	private function getAccessToken(string $ip){

		return hash_hmac('sha256',$ip,$this->md5_sec_key);

	}

}