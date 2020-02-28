<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2019/1/15 15:06
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\AutoGenerate;
use App\Http\Toolkit\RESPONSE;
use App\Models\RegisterAuthorizationModel;
use Illuminate\Http\Request;

class AdminController extends ApiController
{
	
	use AutoGenerate;

	/**
	 * 生成认证授权码
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function createAuthCode(Request $request){
	
		$this->getUser();
		
		$count = RegisterAuthorizationModel::where('status',0)->where('expires_time','>',date('Y-m-d H:i:s'))->count();
		if($count > 0){
			return $this->app_response(RESPONSE::WARNING,$this->say('!023'));
		}

		$date_time = date('Y-m-d H:i:s');
		$data = [
			'code'			=> sha1(time().$this->create_password().mt_rand(9999,99999)),
			'expires_time'	=> date('Y-m-d H:i:s',time() + 60 * 30),
			'created_at'	=> $date_time,
			'updated_at'	=> $date_time
		];
		
		$id = RegisterAuthorizationModel::insertGetId($data);
		
		if($id == null){
			return $this->error($this->say('!024'));
		}
		
		return $this->app_response(RESPONSE::SUCCESS,$this->say('!025'),$data['code']);
	
	}
	
}