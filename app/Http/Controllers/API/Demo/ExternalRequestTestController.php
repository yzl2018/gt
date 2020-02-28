<?php
namespace App\Http\Controllers\API\Demo;

use Illuminate\Support\Facades\Log;

class ExternalRequestTestController
{
	/**
	 * A 系统主机地址
	 *
	 * @var string
	 */
	private $web_site = 'http://www.newmall.com';

	/**
	 * 响应成功状态码
	 *
	 * @var int
	 */
	private $success_code = 0xFFF;

	/**
	 * 请求接口
	 *
	 * @var array
	 */
	private $request_api = [
		'cus_list'			=> '/api/mall/customers-list',
		'goods_list'		=> '/api/mall/goods-list',
		'pur_records'		=> '/api/mall/purchase-list',
		'cash_cards'		=> '/api/mall/cards-list',
		'refund_records'	=> '/api/mall/refund-list',
	];

	/**
	 * A 商城基本信息
	 *
	 * @var array
	 */
	private $mall_info = [
		'name'					=> 'BP 商城系统',
		'code'					=> 0xA010,
		'access_safety_code'	=> '$dsgb3e7r9gh4385jsv98*93j4ygt98d9fb8uoi&&@HUrve0rt',
		'request_time'			=> ''
	];

	/**
	 * 系统用户信息
	 *
	 * @var array
	 */
	private $system_user = [
		'token_name'			=> 'X-API-TOKEN-TEST',
		'authorization_code'	=> '8160807cd727e86eb3f01d2b96ee969fa76fd192',
		'communication_key'		=> '6b60df28a1b2ffd561309810dceb82437dddb602'
	];

	/**
	 * 接口请求参数
	 *
	 * @var array
	 */
	private $parameters = [
		'request_time'			=> ''
	];

	/**
	 * 获取客户
	 */
	public function getCustomersList(){

		$uri = $this->web_site.$this->request_api['cus_list'];
		$this->mall_info['request_time'] = $this->parameters['request_time'] = time();
		//$this->parameters['is_paginate'] = false;
		$this->parameters['page_items'] = 20;
		$headers = [$this->system_user['token_name'].":".$this->create_header_token()];
		$resp = $this->curl_post_json_data($uri,$this->parameters,$headers);

		if(is_null(json_decode($resp))){
			print_r($resp);
		}

		$res = json_decode($resp);
		if($res->code == $this->success_code){//响应成功
			echo "<pre>";
			print_r($res->data);
		}
		else{//响应失败
			echo "<pre>";
			print_r($res);
		}

	}

	/**
	 * 获取商品
	 */
	public function getGoodsList(){

		$uri = $this->web_site.$this->request_api['goods_list'];
		$this->mall_info['request_time'] = $this->parameters['request_time'] = time();
		$headers = [$this->system_user['token_name'].":".$this->create_header_token()];
		$resp = $this->curl_post_json_data($uri,$this->parameters,$headers);

		if(is_null(json_decode($resp))){
			print_r($resp);
		}

		$res = json_decode($resp);
		if($res->code == $this->success_code){//响应成功
			echo "<pre>";
			print_r($res->data);
		}
		else{//响应失败
			echo "<pre>";
			print_r($res);
		}

	}

	/**
	 * 获取购买记录
	 */
	public function getPurchaseRecords(){

		$uri = $this->web_site.$this->request_api['pur_records'];
		$this->mall_info['request_time'] = $this->parameters['request_time'] = time();
		$headers = [$this->system_user['token_name'].":".$this->create_header_token()];
		$resp = $this->curl_post_json_data($uri,$this->parameters,$headers);

		if(is_null(json_decode($resp))){
			print_r($resp);
		}

		$res = json_decode($resp);
		if($res->code == $this->success_code){//响应成功
			echo "<pre>";
			print_r($res->data);
		}
		else{//响应失败
			echo "<pre>";
			print_r($res);
		}

	}

	/**
	 * 获取充值卡记录
	 */
	public function getCashCards(){

		$uri = $this->web_site.$this->request_api['cash_cards'];
		$this->mall_info['request_time'] = $this->parameters['request_time'] = time();
		$headers = [$this->system_user['token_name'].":".$this->create_header_token()];
		$resp = $this->curl_post_json_data($uri,$this->parameters,$headers);

		if(is_null(json_decode($resp))){
			print_r($resp);
		}

		$res = json_decode($resp);
		if($res->code == $this->success_code){//响应成功
			echo "<pre>";
			print_r($res->data);
		}
		else{//响应失败
			echo "<pre>";
			print_r($res);
		}

	}

	/**
	 * 获取退款记录
	 */
	public function getRefundRecords(){

		$uri = $this->web_site.$this->request_api['refund_records'];
		$this->mall_info['request_time'] = $this->parameters['request_time'] = time();
		$headers = [$this->system_user['token_name'].":".$this->create_header_token()];
		$resp = $this->curl_post_json_data($uri,$this->parameters,$headers);

		if(is_null(json_decode($resp))){
			print_r($resp);
		}

		$res = json_decode($resp,true);
		if($res['code'] == $this->success_code){//响应成功
			echo "<pre>";
			print_r($res['data']);
		}
		else{//响应失败
			echo "<pre>";
			print_r($res);
		}

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
		$user_info_token = hash_hmac('sha384',$value_str,$this->system_user['authorization_code']);

		ksort($this->parameters);

		$parameters_str = strrev(base64_encode(json_encode($this->parameters)));
		$parameters_str = $this->replace_data_str($parameters_str);
		$parameters_token = hash_hmac('sha256',$parameters_str,$this->system_user['communication_key']);

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

	/**
	 * 作用：模拟POST表单提交
	 * Content-Type:application/json; charset=utf-8
	 * @param $uri
	 * @param $data
	 * @param $headers
	 * @return string
	 */
	private function curl_post_json_data(string $uri,$data,array $headers = []){
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
		curl_setopt ( $ch, CURLOPT_POST, 1 );  //使用post请求
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true);  //重定向地址也输出
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//让其不验证ssl证书
		curl_setopt ($ch,  CURLOPT_SSL_VERIFYHOST,2);
		$resp = curl_exec ( $ch ); //得到返回值
		if(curl_error($ch)){
			Log::error(__FUNCTION__,['curl_error'=>curl_error($ch)]);
		}
		curl_close ( $ch );  //关闭
		return $resp;
	}

}
