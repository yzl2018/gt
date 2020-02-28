<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\RESPONSE;
use App\Models\SystemUsersModel;
use Closure;

class AuthExternalSystem
{
	use AppResponse;
	/**
	 * 系统IP
	 *
	 * @var
	 */
	private $system_ip;

	/**
	 * 请求参数
	 *
	 * @var
	 */
	private $parameters;

	/**
	 * 商城基本认证信息
	 *
	 * @var array
	 */
	private $mall_info = [
		'name'					=> '',
		'code'					=> '',
		'access_safety_code'	=> '',
		'request_time'			=> ''
	];

	/**
	 * AuthSystemUser constructor.
	 */
	public function __construct()
	{

		$this->mall_info['name'] 				= config('system.mall.name');
		$this->mall_info['code'] 				= config('system.mall.code');
		$this->mall_info['access_safety_code'] 	= config('system.mall.access_safety_code');

	}

	/**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

		$this->system_ip = $request->getClientIp();
		$this->parameters = $request->input();

		if(!$request->has('request_time')){
			return $this->error('Bad request');
		}
		$this->mall_info['request_time'] = $request->input('request_time');

		$system_user = SystemUsersModel::where('sys_ip',$this->system_ip)->first();
		//return $this->app_response(RESPONSE::APP_DEBUG,'test token',$this->create_header_token($system_user));
		if(empty($system_user)){
			return $this->error('Access denied');
		}

		if($system_user->status != 1){
			return $this->error('Your access has been turned off');
		}

		$header_token = $request->header($system_user->header_token_name);
		if($header_token == null){
			return $this->error('Invalid request');
		}

		if($header_token != $this->create_header_token($system_user)){
			return $this->message('Unauthorized');
		}

        return $next($request);
    }

	/**
	 * 生成外部系统访问的认证请求头
	 *
	 * @param $user
	 * @return string
	 */
    private function create_header_token($user){

		$mall_info_token = base64_encode(json_encode($this->mall_info));
		$mall_info_token = $this->replace_data_str($mall_info_token);

		ksort($this->mall_info);
		$value_str = implode('*&',array_values($this->mall_info));
		$user_info_token = hash_hmac('sha384',$value_str,$user->authorization_code);

		ksort($this->parameters);

		$parameters_str = strrev(base64_encode(json_encode($this->parameters)));
		$parameters_str = $this->replace_data_str($parameters_str);
		$parameters_token = hash_hmac('sha256',$parameters_str,$user->communication_key);

		return $mall_info_token.".".$user_info_token.$parameters_token.".".$parameters_str;

	}

	/**
	 * 字符串替换
	 *
	 * @param string $data_str
	 * @return mixed|string
	 */
	private function replace_data_str(string $data_str){

		$data_str = str_replace('i', '%', $data_str);
		$data_str = str_replace('z', '*', $data_str);
		$data_str = str_replace('3', '@', $data_str);
		$data_str = str_replace('a', '&', $data_str);
		$data_str = str_replace('w', '#', $data_str);
		$data_str = str_replace('=', 'Q', $data_str);
		return $data_str;

	}
}
