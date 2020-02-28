<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\RESPONSE;
use App\Models\SystemUsersModel;
use Closure;

class AuthSystemUser
{
	use AppResponse;
	/**
	 * 系统IP
	 *
	 * @var
	 */
	private $system_ip;

	/**
	 * header token key
	 *
	 * @var string
	 */
	private $header_token_key = 'X-API-MALL-TOKEN-';

	/**
	 * communication key
	 *
	 * @var string
	 */
	private $communication_key = 'VJNw984ret34)GDVi34okyt3-0F)_=2';

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
		'request_key'			=> ''
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

		$this->parameters = $request->input();
		if(!$request->has('request_key')){
			return $this->error('Bad request');
		}
		$this->mall_info['request_key'] = $request->input('request_key');

		$this->header_token_key .= $this->mall_info['request_key'];

		$header_token = $request->header($this->header_token_key);
		if($header_token == null){
			return $this->error('Invalid request');
		}

		if($header_token != $this->create_header_token()){
			return $this->message('Unauthorized');
		}

        return $next($request);
    }

	/**
	 * 生成外部系统访问的认证请求头
	 *
	 * @return string
	 */
    private function create_header_token(){

		$mall_info_token = base64_encode(json_encode($this->mall_info));
		$mall_info_token = $this->replace_data_str($mall_info_token);

		ksort($this->mall_info);
		$value_str = implode('*&',array_values($this->mall_info));
		$user_info_token = hash_hmac('sha384',$value_str,$this->mall_info['access_safety_code']);

		ksort($this->parameters);

		$parameters_str = strrev(base64_encode(json_encode($this->parameters)));
		$parameters_str = $this->replace_data_str($parameters_str);
		$parameters_token = hash_hmac('sha256',$parameters_str,$this->communication_key);

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
