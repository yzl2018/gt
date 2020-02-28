<?php
namespace App\Http\Controllers\API\External;

use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\AutoGenerate;
use App\Http\Toolkit\GetIpLocation;
use App\Http\Toolkit\RESPONSE;
use App\Models\SystemUsersModel;
use App\Models\RegisterAuthorizationModel;
use Illuminate\Http\Request;

class RegisterController
{
	use AppResponse,GetIpLocation,AutoGenerate;

	/**
	 * header token prefix
	 *
	 * @var string
	 */
	private $token_prefix = "X-API-TOKEN";

	public function register(Request $request){

		if(!$request->has('token_name') || !$request->has('authorization_code')){
			return $this->app_response(RESPONSE::WARNING,'Missing parameters');
		}

		$token_name = $request->input('token_name');
		$auth_code = $request->input('authorization_code');

		$sys_ip = $request->getClientIp();
		$count = SystemUsersModel::where('sys_ip',$sys_ip)->count();
		if($count > 0){
			return $this->app_response(RESPONSE::WARNING,'You have registered an access user');
		}

		if(!starts_with($token_name,$this->token_prefix)){
			return $this->message('Token name format error.');
		}

		//判断授权码是否有效
		$auth_info = RegisterAuthorizationModel::where('code',$auth_code)->first();
		if($auth_info == null){
			return $this->error('The registration authorization code does not exist');
		}
		if($auth_info->status != 0){
			return $this->error('The registration authorization code has been used');
		}
		if(date('Y-m-d H:i:s') > $auth_info->expires_time){
			return $this->error('The registration authorization code has expired');
		}
		RegisterAuthorizationModel::where('id',$auth_info->id)->update(['status'=>1]);

		//创建外部系统访问用户
		$communication_key = $this->create_password(32);
		$date_time = date('Y-m-d H:i:s');
		$sys_user = [
			'sys_ip'				=> $sys_ip,
			'header_token_name'		=> $token_name,
			'ip_address'			=> $this->getIpAddress($sys_ip),
			'authorization_code'	=> $auth_code,
			'communication_key'		=> sha1($communication_key),
			'created_at'			=> $date_time,
			'updated_at'			=> $date_time
		];
		$id = SystemUsersModel::insertGetId($sys_user);
		if($id < 1){
			return $this->app_response(RESPONSE::REGISTER_FAIL,'Register failed,please try again later.');
		}

		//返回注册成功的用户实例
		$return_user = [
			'name'					=> config('system.mall.name'),
			'code'					=> config('system.mall.code'),
			'access_safety_code'	=> config('system.mall.access_safety_code'),
			'token_name'			=> 	$token_name,
			'authorization_code'	=> $auth_code,
			'communication_key'		=> $communication_key
		];
		return $this->app_response(RESPONSE::SUCCESS,'Register system user success',$return_user);

	}

}