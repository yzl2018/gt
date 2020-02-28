<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/24 17:34
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Toolkit;


use Illuminate\Http\Request;

trait AppRequest
{
	
	/**
	 * 请求的接口
	 *
	 * @var
	 */
	protected $request_api;
	
	/**
	 * 原始请求数据
	 *
	 * @var
	 */
	protected $request_data = null;
	
	/**
	 * 请求接口所需参数
	 *
	 * @var
	 */
	protected $api_parameters = null;
	
	/**
	 * 合法构造后的参数
	 *
	 * @var
	 */
	protected $rebuild_params = null;
	
	/**
	 * 请求参数及数据验证方法
	 *
	 * @param Request $request
	 * @param array|null $parameters
	 * @return bool|string
	 */
	protected function AuthAndBuildParams(Request $request,array $parameters = null){
		
		$auth_result = true;
		$this->request_api = $request->getRequestUri();
		if(strtoupper($request->method()) != 'GET'){
			if($parameters == null){
				$auth_result = "Not configured legal parameters";
			}
			$this->request_data = $request->input();
		}
		return $auth_result;
		
	}
	
	/**
	 * 验证请求参数是否合法
	 */
	protected function AuthRequestParameters(){
	
	}
	
	/**
	 * 验证请求数据是否合法
	 */
	protected function AuthRequestData(){
	
	}
}