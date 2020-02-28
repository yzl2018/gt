<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2019/1/3 10:43
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Toolkit;


use Illuminate\Support\Facades\Log;

trait CommunicateWithB
{
	/**
	 * B系统网址
	 *
	 * @var string
	 */
	private $b_web_site;

	/**
	 * B系统返回的成功响应状态码
	 *
	 * @var string
	 */
	private $b_return_success_code = '0';

	/**
	 * B系统安全加密信息
	 *
	 * @var array
	 */
	private $b_security = [];

	/**
	 * 系统安全码
	 *
	 * @var string
	 */
	protected $sys_security_code;

	/**
	 * curl请求超时时间
	 *
	 * @var int
	 */
	protected $http_time_out = 30;

	/**
	 * 支付请求页面地址
	 *
	 * @var string
	 */
	private $do_pay_uri;

	/**
	 * MD5 加密密钥
	 *
	 * @var string
	 */
	private $md5_sec_key;

	/**
	 * B系统请求接口
	 *
	 * @var array
	 */
	private $b_api = [//B系统接口
		'register'                  => '/api/user/register',//注册用户
		'pay_api'                   => '/api/transaction/buy',//美元支付接口
		'cny_pay_api'               => '/api/transaction/cny-buy',//人民币支付接口
		'merchant_info'             => '/api/user/all-merchants-info',//获取所有商户信息
		'active_voucher'            => '/api/voucher/activate',//激活美元兑换券接口
		'active_cny_voucher'        => '/api/voucher/activate-card',//激活人民币兑换券接口
		'active_voucher_usd'        => '/api/voucher/activate-b',//激活美元兑换券接口
		'active_voucher_cny'      	=> '/api/voucher/activate-card-b',//激活人民币兑换券接口
		'voucher_seckey'            => '/api/voucher/get-secode',//获取兑换密码
		'get_all_cards'             => '/api/voucher/all-gcards',//获取客户充值卡（客户/管理员）
		'apply_refund'				=> '/api/refund/request-refund',//申请退款接口
		'query_voucher'				=> '/api/voucher/inquiry',//商户查询兑换券状态
        'new_active_api'			=> '/api/voucher/activate-gcard',//人民币兑换券激活接口(推送通知)
	];

	/**
	 * CommunicateWithB constructor.
	 */
	public function __construct()
	{

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

	}

	/**
	 * 获取请求接口地址
	 *
	 * @param $name
	 * @return bool|string
	 */
	protected function get_b_api($name){

		if(!isset($this->b_api[$name])){
			return false;
		}

		return $this->b_web_site.$this->b_api[$name];

	}

	/**
	 * 注册用户
	 *
	 * @param $type
	 * @param $seckey
	 * @param $email
	 * @return string
	 */
	public function registerUser($type,$seckey,$email){

		$user_type = [
			0xB010 => 'admin',
			0xB011 => 'admin',
			0xB012 => 'customer'
		];

		$email_str = json_encode(['email'=>$email]);
		$headers = $this->create_register_auth($user_type[$type],$seckey,$email_str);
		$params = ['shop_id'=>config('system.mall.code'),'type'=>$user_type[$type],'seckey'=>$seckey,'attach_info'=>$email_str];
		$resp = $this->http_post_data($this->b_web_site.$this->b_api['register'],$params,$headers);
		return $resp;

	}

	/**
	 * 生成注册认证请求头
	 *
	 * @param $type
	 * @param $seckey
	 * @param $email_str
	 * @return array
	 */
	public function create_register_auth($type,$seckey,$email_str){

		$str = "";
		$data = ['shop_id'=>config('system.mall.code'),'type'=>$type,'seckey'=>$seckey,'attach_info'=>$email_str];
		ksort($data);
		foreach ($data as $key => $val){
			if($str == ""){
				$str .= $key."=".urlencode($val);
			}else{
				$str .= "&".$key."=".urlencode($val);
			}
		}
		$token = hash_hmac('sha256',$this->b_security['token_prefix'].$str,$this->sys_security_code);
		return [$this->b_security['register_token_key'].":".$token];

	}

	/**
	 * 用于请求B系统的 A系统后台中转接口
	 * @method post
	 *
	 * @param string $b_api
	 * @param array $params
	 * @param string $user_code
	 * @param string $safety_code
	 * @return mixed
	 */
	protected function post_data_from_B(string $b_api,array $params,string $user_code,string $safety_code){

		if(!isset($this->b_api[$b_api])){
			return ['code'=>RESPONSE::WARNING,'message'=>'未配置关于此接口的B系统请求地址','data'=>null];
		}

		$uri = $this->b_web_site.$this->b_api[$b_api];
		$headers = $this->create_user_auth($user_code,$safety_code);

        try{
            $msg = $this->http_post_B($uri,$params,$headers);
        }
        catch(\Exception $e){
            return ['code'=>RESPONSE::UN_EXCEPTED,'message'=>$e->getMessage()];
        }
		if(is_null(json_decode($msg))){
			$error = 'B system response exception';
			if(env('APP_DEBUG')){
				$error .= " : ".$msg;
			}
			return ['code'=>RESPONSE::UN_EXCEPTED,'message'=>$error,'data'=>null];
		}

		$resp = json_decode($msg,true);
		if($resp['code'] == 0){
			return ['code'=>RESPONSE::SUCCESS,'message'=>$resp['message'],'data'=>$resp['data']];
		}

		return ['code'=>RESPONSE::WARNING,'message'=>$resp['message'],'data'=>$resp['data']];
	}

	/**
	 * 用于请求B系统的 A系统后台中转接口
	 * @method get
	 *
	 * @param string $b_api
	 * @param string $user_code
	 * @param string $safety_code
	 * @return array|mixed
	 */
	protected function get_data_from_B(string $b_api,string $user_code,string $safety_code){

		if(!isset($this->b_api[$b_api])){
			return ['code'=>RESPONSE::WARNING,'message'=>'未配置关于此接口的B系统请求地址','data'=>null];
		}

		$uri = $this->b_web_site.$this->b_api[$b_api];

		$headers = $this->create_user_auth($user_code,$safety_code);
		$msg = $this->http_get_B($uri,$headers);
		if(is_null(json_decode($msg))){
			$error = 'B system response exception';
			if(env('APP_DEBUG')){
				$error .= " : ".$msg;
			}
			return ['code'=>RESPONSE::UN_EXCEPTED,'message'=>$error,'data'=>null];
		}

		$resp = json_decode($msg,true);
		if($resp['code'] == 0){
			return ['code'=>RESPONSE::SUCCESS,'message'=>$resp['message'],'data'=>$resp['data']];
		}

		return ['code'=>RESPONSE::WARNING,'message'=>$resp['message'],'data'=>$resp['data']];
	}

	/**
	 * 生成用户认证请求头
	 *
	 * @param $ucode
	 * @param $seckey
	 * @return array
	 */
	private function create_user_auth($ucode,$seckey){

		$token_info = base64_encode(json_encode(['ucode'=>$ucode,'type'=>$this->b_security['sys_type']]));
		$security_str = $this->b_security['token_prefix'].$ucode.$seckey;
		$token_sec = hash_hmac('sha256',$security_str,$this->sys_security_code);
		return [$this->b_security['user_token_key'].":".$token_info.".".$token_sec];

	}

	/**
	 * 验证回调通知请求头
	 *
	 * @param $token
	 */
	public function verify_header_token($token){
		if($token == NULL || $token == ""){
			exit("Illegal notify");
		}
		$arr = explode('.',$token);
		$token_info = json_decode(base64_decode($arr[0]));
		if(!isset($token_info->ucode) || !isset($token_info->type)){
			exit("Illegal notify");
		}
		if($token_info->type != $this->b_security['b_notify_header_info']['type']){
			exit("Illegal notify");
		}
		if($token_info->ucode != $this->b_security['b_notify_header_info']['ucode']){
			exit("Illegal notify");
		}
		if($token != $this->create_B_header_token()){
			exit("Verify fail");
		}
	}

	/**
	 * 生成B系统回调通知请求头TOKEN
	 *
	 * @return string
	 */
	private function create_B_header_token(){
		$token_info = base64_encode(json_encode($this->b_security['b_notify_header_info']));
		$token_sec = hash_hmac('sha256',$token_info,$this->sys_security_code);
		return $token_info.".".$token_sec;
	}

	/**
	 * 作用：模拟POST表单提交
	 * Content-Type:application/json; charset=utf-8
	 * @param $uri
	 * @param $data
	 * @param $headers
	 * @return string
     * @throws \Exception
	 */
	private function http_post_B(string $uri,$data,array $headers = []){
		if(is_array($data)){
			$data = json_encode($data);
		}
		else{
			$data = strval($data);
		}
		$headers[] = 'Content-Type: application/json; charset=utf-8';
		$headers[] = 'Content-Length: ' . strlen($data);
		$ch = curl_init ();  //初始化curl
		curl_setopt ( $ch, CURLOPT_HTTPHEADER,$headers);
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);  //提交数据
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_URL, $uri );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,$this->http_time_out-2);//尝试连接的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_TIMEOUT,$this->http_time_out);//脚本最大执行的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_POST, 1 );  //使用post请求
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true);  //重定向地址也输出
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//让其不验证ssl证书
		curl_setopt ($ch,  CURLOPT_SSL_VERIFYHOST,2);
		$resp = curl_exec ( $ch ); //得到返回值
		$return_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if(curl_error($ch)){
            Log::error(__FUNCTION__,[
                    'request_uri'	=> $uri,
                    'curl_exception'=>curl_error($ch)]
            );
            throw new \Exception('The network is out of order, please try again later.');
		}
		curl_close ( $ch );  //关闭
		return $resp;
	}

	/**
	 * 作用：模拟GET请求
	 * @param $uri
	 * @param $headers
	 * @return string
	 */
	private function http_get_B(string $uri,array $headers = []){
		//初始化
		$curl = curl_init();
		//设置抓取的url
		curl_setopt($curl, CURLOPT_URL, $uri);
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT,$this->http_time_out-2);//尝试连接的超时时间 单位/秒
		curl_setopt ( $curl, CURLOPT_TIMEOUT,$this->http_time_out);//脚本最大执行的超时时间 单位/秒
		//设置头文件的信息作为数据流输出
		curl_setopt($curl, CURLOPT_HEADER, 0);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ( $curl, CURLOPT_HTTPHEADER,$headers);
		//执行命令
		$data = curl_exec($curl);
		$request_header = curl_getinfo( $curl, CURLINFO_HEADER_OUT);
		$return_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
		if(curl_error($curl)){
			Log::error(__FUNCTION__,[
					'request_uri'	=> $uri,
					'curl_exception'=>curl_error($curl)]
			);
			$data = "An exception occurs when curl requests：".date('Y-m-d H:i:s');
		}
		if($data == "" || $data == NULL){
			$data = "RESPONSE：".$data." HTTP CODE：".$return_code;
		}
		//关闭URL请求
		curl_close($curl);
		//显示获得的数据
		return $data;
	}

	/**
	 * 作用：模拟POST表单提交
	 * @param $uri
	 * @param $data
	 * @param $headers
	 * @return string
	 */
	private function http_post_data($uri,$data,$headers = []){
		$ch = curl_init ();  //初始化curl
		curl_setopt ( $ch, CURLOPT_URL, $uri );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,$this->http_time_out-2);//尝试连接的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_TIMEOUT,$this->http_time_out);//脚本最大执行的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_POST, 1 );  //使用post请求
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER,$headers);
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);  //提交数据
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true);  //重定向地址也输出
		$resp = curl_exec ( $ch ); //得到返回值
		$return_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if(curl_error($ch)){
			Log::error(__FUNCTION__,[
					'request_uri'	=> $uri,
					'curl_exception'=>curl_error($ch)]
			);
			$resp = "An exception occurs when curl requests：".date('Y-m-d H:i:s');
		}
		if($resp == "" || $resp == NULL){
			$resp = "RESPONSE：".$resp." HTTP CODE：".$return_code;
		}
		curl_close ( $ch );  //关闭
		return $resp;
	}

}
