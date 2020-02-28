<?php

namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\RESPONSE;
use App\Models\MerchantSecurityModel;
use Illuminate\Http\Request;

class MerchantsController extends ApiController
{
	use CommunicateWithB;
	
	/**
	 * 获取所有商户的密钥信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse|mixed
	 */
	public function showMerchantsSecurity(Request $request){
		
		$this->getUser();
		
		$result =  $this->get_data_from_B('merchant_info',$this->user['user_code'],$this->user['safety_code']);
		
		if($result['code'] != RESPONSE::SUCCESS){
			return \response()->json($result);
		}
		
		$merchants = $result['data']['merchants'];
		
		$list = [];
		foreach ($merchants as $key => $value) {
			$obj = [
				'ucode' 			=> $value['ucode'],
				'mercode' 			=> $value['mercode'],
				'channels_group'	=> $value['channels_group']
			];
			$mer = MerchantSecurityModel::where('merchant_code', $value['mercode'])->first();
			if ($mer != null) {
				$obj['SecurityKey'] = $mer->security_key;
				$obj['SecuritySalt'] = $mer->security_salt;
			} else {
				$obj['SecurityKey'] = "";
				$obj['SecuritySalt'] = "";
			}
			array_push($list,$obj);
		}
		
		return $this->app_response(RESPONSE::SUCCESS,'get merchants security success',$list);
	}
	
	/**
	 * 配置商户的密钥
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function configSecurity(Request $request){
		
		$this->getUser();
		
		$mer_sec = MerchantSecurityModel::where('merchant_code',$request->input('merchant_code'))->first();
		if($mer_sec != null && $mer_sec->security_key != null && $mer_sec->security_salt != null){
			return $this->message($this->say('!078'));
		}
		
		$params = $request->input();
		$params['security_key'] = $this->CreateSafetyCode();
		$params['security_salt'] = $this->create_password(18);
		
		$params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');
		$id = MerchantSecurityModel::insertGetId($params);
		if($id){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!076'),$params['merchant_code']);
		}
		
		return $this->app_response(RESPONSE::WARNING,$this->say('!077'),$params['merchant_code']);
	
	}
	
}