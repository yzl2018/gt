<?php
namespace App\Http\Controllers\API\External;

use App\Http\Toolkit\AutoGenerate;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\DataValidator;
use App\Models\CodeConfigModel;
use Illuminate\Http\Request;
use App\Http\Toolkit\ServerEncryptTools;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CRMRegisterCusController
{

	use DataValidator,AutoGenerate,CommunicateWithB;

	/**
	 * 商城编码
	 *
	 * @var null
	 */
	private $shop_code = null;

	/**
	 * 域名站点编号
	 *
	 * @var null
	 */
	private $site_code = null;

	/**
	 * 清求的商户号
	 *
	 * @var null
	 */
	private $request_merchant = null;

	/**
	 * 操作密码
	 *
	 * @var null
	 */
	private $operate_password = null;

	/**
	 * 安全码
	 *
	 * @var null
	 */
	private $safety_code = null;

	/**
	 * 客户在B系统中的ucode
	 *
	 * @var null
	 */
	private $user_code = null;

	/**
     * 允许请求的域名
     *
     * @var array
     */
    private $allow_request_host = [
        'www.newmall.com'  => 'S001'
    ];

    /**
     * 请求参数
     *
     * @var array
     */
    private $crm_request_params = [
		'MerchantCode'      => '',//商户号
		'MerchantId'        => '',//商户ID
		'CustomerName'      => NULL,//客户昵称
		'CustomerEmail'     => '',//客户邮箱
		'CustomerPhone'     => NULL,//客户手机号
		'LoginPassword'     => '',//登陆密码
    ];

	/**
	 * 初始化数据
	 */
	private function initData(){

		$this->shop_code = config('system.mall.code');
		$this->operate_password = md5('bp123456789');//支付密码默认为 bp123456789
		$this->safety_code = md5($this->create_password());

	}

    /**
     * 响应码
     *
     * @var array
     */
    private $response_code = [
        0xAC00 => 'Unexpected Error',//未预期的错误 44032
        0xAC01 => 'Invalid request host',//非法请求 清求的主机地址错误 44033
        0xAC02 => 'Invalid request, the content data received is incorrect.',//非法清求 接收到的清求数据不正确 44034
        0xAC04 => 'Unconfigured merchant code',//未配置的商户号 44036
        0xAC05 => 'Missing parameter',//缺省参数 44037
        0xAC06 => 'Verify fail',//验签失败 44038
        0xAC07 => 'Illegal E-mail',//非法的邮箱格式 44039
        0xAC08 => 'The password format error',//密码必须包含字母和数字且大于6位 44040
        0xAC09 => 'Request B system error',//请求B系统出错 44041
		0xAC0A => 'The system is busy, please try again later.',//请求频率限制 44042
		0xACFC => 'Registration failed',//注册失败 44284
		0xACFD => 'The customer is registered but not activated',//该客户注册过但未激活 44285
		0xACFE => 'The customer has successfully registered',//该客户已成功注册过 44286
        0xACFF => 'Register Success'//注册成功 44287
    ];

	/**
	 * 接口程序主入口
	 *
	 * @param Request $request
	 * @return array|\Illuminate\Http\JsonResponse|string
	 */
    public function run(Request $request){

    	$this->initData();

    	$result = $this->registerCustomer($request);

    	$response = $this->encryptResult($result);

    	if(is_string($response)){
    		return $response;
		}

    	return response()->json($response);

	}

	/**
	 * 注册客户
	 *
	 * @param Request $request
	 * @return array
	 */
    private function registerCustomer(Request $request){

        //判断请求的主机是否合法
//        $host = $request->getHttpHost();
//        if(!array_key_exists($host,$this->allow_request_host)){
//            return $this->responseMsg(0xAC01);
//        }
//        $this->site_code = $this->allow_request_host[$host];

        //检查能否正确获取清求数据
        try {
            $inputs = ServerEncryptTools::getRequestContent($request);
        }
        catch (\Exception $ex) {
            Log::error(__FUNCTION__, [
                "msg" => $ex->getMessage(),
                "trace" => $ex->getTrace()
            ]);

			return $this->responseMsg(0xAC02);

        }

		$req_res = $this->getRequestParams($inputs);

        //检查数据是否有效
		if($req_res['Code'] != '00'){
        	return $req_res;
		}

		//检查数据签名是否正确
		if($this->verifyParams($inputs) == false){
			return $this->responseMsg(0xAC06);
		}

		//检查邮箱格式是否合法
		if(!$this->EmailValidator($this->crm_request_params['CustomerEmail'])){
        	return $this->responseMsg(0xAC07);
		}

		//检查密码是否合法
		if(!$this->PasswordValidator($this->crm_request_params['LoginPassword'])){
        	return $this->responseMsg(0xAC08);
		}

		//检查该邮箱是否已被注册
		$exists_res = $this->emailExists();
		if($exists_res != false){
			return $exists_res;
		}

		//请求B系统注册用户
		$resp = $this->registerUser(config('system.user.customer.code'),$this->safety_code,$this->crm_request_params['CustomerEmail']);

		if(is_null(json_decode($resp))){
			Log::error(__FUNCTION__, [$this->request_merchant->merchant_code => $resp]);
			return $this->responseMsg(0xAC00);
		}
		$res = json_decode($resp);

		if($res->code != 0){
			Log::error(__FUNCTION__, [$this->request_merchant->merchant_code => $res->message]);
			return $this->responseMsg(0xAC09);
		}
		$this->user_code = $res->data->ucode;

		//A系统注册客户
		return $this->registerCusOfMall();

    }

    /**
     * 获取各步骤的请求参数
	 *
     * @param array $req
     * @return array
     */
    private function getRequestParams($req){

        $tool = new ServerEncryptTools('','');
        $params = $tool->getResponseData($req);

        if($params == false) {
            Log::warning('Failed to get parsed data in ServerEncryptTools', $req);
            return $this->responseMsg(0xAC02);
        }

        if(!isset($params['MerchantCode'])){
        	return $this->responseMsg(0xAC05);
		}

		$merchant = DB::table('merchants_security')->where('merchant_code',$params['MerchantCode'])->first();
        if(empty($merchant)){
        	return $this->responseMsg(0xAC04);
		}

		$this->request_merchant = $merchant;

        foreach ($this->crm_request_params as $key => $value){
            if($value !== NULL){
                if(!array_key_exists($key,$params)){
                	return $this->responseMsg(0xAC05);
                }
                $this->crm_request_params[$key] = trim($params[$key]);
            }
            else{
                if(isset($params[$key])){
                    $this->crm_request_params[$key] = trim($params[$key]);
                }
                else{
                	unset($this->crm_request_params[$key]);
				}
            }
        }

        return ['Code'=>'00','Message'=>'Resolved params success'];
    }

	/**
	 * 数据验签
	 *
	 * @param $inputs
	 * @return array|bool
	 */
    private function verifyParams($inputs){

    	if(empty($this->request_merchant)){
    		return $this->responseMsg(0xAC04);
		}

    	$enc_tool = new ServerEncryptTools($this->request_merchant->security_key,$this->request_merchant->security_salt);
    	$enc_tool->getResponseData($inputs);

    	return $enc_tool->verify($inputs);

	}

	/**
	 * 判断待注册的客户邮箱是否已存在
	 *
	 * @return array|bool
	 */
	private function emailExists(){

    	$code = DB::table('users')
			->where('email',$this->crm_request_params['CustomerEmail'])
			->value('code');

    	if(empty($code)){
    		return false;
		}

    	$res_data = md5($this->shop_code).$code;

    	$user_code = DB::table('users')
			->where('email',$this->crm_request_params['CustomerEmail'])
			->value('user_code');

    	if(empty($user_code)){
			return $this->responseMsg(0xACFD,$res_data);
		}

		return $this->responseMsg(0xACFE,$res_data);
	}

	/**
	 * 注册A商城客户
	 *
	 * @return array
	 */
	private function registerCusOfMall(){

		$ucode = CodeConfigModel::getUniqueCode('user');

		$time = date('Y-m-d H:i:s');
		$user = [
			'code'				=> $ucode,
			'email'				=> $this->crm_request_params['CustomerEmail'],
			'phone'				=> '',
			'password'			=> bcrypt(md5($this->crm_request_params['LoginPassword'])),
			'operate_password'	=> $this->operate_password,
			'user_type_code'	=> config('system.user.customer.code'),
			'user_code'			=> $this->user_code,
			'safety_code'		=> $this->safety_code,
			'active_status'		=> 1,
			'created_at'		=> $time,
			'updated_at'		=> $time
		];

		if(isset($this->crm_request_params['CustomerName'])){
			$user['name'] = $this->crm_request_params['CustomerName'];
		}

		if(isset($this->crm_request_params['CustomerPhone'])){
			$user['phone'] = $this->crm_request_params['CustomerPhone'];
		}

		DB::beginTransaction();
		$user_id = DB::table('users')->insertGetId($user);
		DB::commit();

		if(empty($user_id)){
			return $this->responseMsg(0xACFC);
		}

		$res_data = md5($this->shop_code).$ucode;
		return $this->responseMsg(0xACFF,$res_data);

	}

    /**
     * 响应状态信息
     *
     * @param int $code
	 * @param $data
     * @return array
     */
    private function responseMsg(int $code,$data = null){

    	$response = [
    		'Code'		=> 0xAC00,
			'Message'	=> $this->response_code[0xAC00]
		];

        if(array_key_exists($code,$this->response_code)){
			$response['Code'] = $code;
			$response['Message'] = $this->response_code[$code];
        }

        if($data != null){
			$response['CustomerEmail'] = $this->crm_request_params['CustomerEmail'];
			$response['CustomerCode'] = $data;
		}

        return $response;
    }

	/**
	 * 加密签名响应结果
	 *
	 * @param array $result
	 * @return array|string
	 */
	private function encryptResult(array $result){

		if(empty($this->request_merchant)){
			return $result['Code'].':'.$result['Message'];
		}

		$enc_tool = new ServerEncryptTools($this->request_merchant->security_key,$this->request_merchant->security_salt);
		return $enc_tool->createRequestData($result);

	}

}
