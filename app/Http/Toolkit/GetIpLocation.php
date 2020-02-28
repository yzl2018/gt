<?php
namespace App\Http\Toolkit;

use Illuminate\Support\Facades\Log;

trait GetIpLocation
{

	/**
	 * 淘宝IP查询地址
	 *
	 * @var string
	 * @return
	{
	"code":0,
	"data":{
	"ip":"120.36.254.31",
	"country":"中国",
	"area":"",
	"region":"福建",
	"city":"厦门",
	"county":"XX",
	"isp":"电信",
	"country_id":"CN",
	"area_id":"",
	"region_id":"350000",
	"city_id":"350200",
	"county_id":"xx",
	"isp_id":"100017"
	}
	}
	 */
	private $taobao_ip = "http://ip.taobao.com/service/getIpInfo.php?ip=";

	/**
	 * 百度IP查询地址
	 *
	 * @var string
	 * @return
	{
	    "address": "CN|福建|厦门|None|CHINANET|0|0",
	    "content": {
	        "address_detail": {
	            "province": "福建省",
	            "city": "厦门市",
	            "district": "",
	            "street": "",
	            "street_number": "",
	            "city_code": 194
	        },
	        "address": "福建省厦门市",
	        "point": {
	            "y": "2795265.28",
	            "x": "13147407.51"
	        }
	    },
	    "status": 0
	}
	 */
	private $baidu_ip = "http://api.map.baidu.com/location/ip?ak=F454f8a5efe5e577997931cc01de3974&ip=";

	/**
	 * 太平洋网IP查询地址
	 *
	 * @var string
	 *
	 * @return
	 *
	 * '海南省海口市 联通'
	 *
	 */
	private $pconline_ip = "http://whois.pconline.com.cn/ip.jsp?ip=";

	/**
	 * 超时时间
	 *
	 * @var int
	 */
	//private $http_time_out = 10;

	/**
	 * @var array
	 */
	private $ip_query_func = [
		'getIpAddressByPconline',
		'getIpAddressByBaidu',
		'getIpAddressByTaobao'
	];

	/**
	 * @var null
	 */
	private $ip_address = "Unknown";

	/**
	 * 获取IP归属地
	 *
	 * @param string $ip
	 * @return string
	 */
	private function getIpAddress(string $ip){

		if($ip == '127.0.0.1'){
			return "内网IP";
		}

		foreach ($this->ip_query_func as $func_name){

			$ip_address = $this->$func_name($ip);
			if(!empty($ip_address)){
				$this->ip_address = $ip_address;
				break;
			}

		}

		return $this->ip_address;

	}

	/**
	 * 用太平洋IP查询接口获取IP地址
	 *
	 * @param string $ip
	 * @return mixed|null|string
	 */
	private function getIpAddressByPconline(string $ip){

		$uri = $this->pconline_ip.$ip;

		try{
			$resp = $this->http_get_location($uri);
		}
		catch(\Exception $e){
			Log::error(__FUNCTION__,[
					'request_uri'	=> $uri,
					'curl_exception'=> $e->getMessage()
				]
			);

			return null;
		}

		if(empty($resp)){
			return null;
		}

		return mb_convert_encoding($resp,"UTF-8","GBK");

	}

	/**
	 * 用百度IP查询接口获取IP地址
	 *
	 * @param string $ip
	 * @return null|string
	 */
	private function getIpAddressByBaidu(string $ip){

		$uri = $this->baidu_ip.$ip;

		try{
			$resp = $this->http_get_location($uri);
		}
		catch(\Exception $e){
			Log::error(__FUNCTION__,[
					'request_uri'	=> $uri,
					'curl_exception'=> $e->getMessage()
				]
			);

			return null;
		}

		if(is_null(json_decode($resp))){
			return null;
		}

		$result = json_decode($resp,true);

		if($result['status'] != 0){
			return null;
		}

		return $result['content']['address'];

	}

	/**
	 * 用淘宝IP查询接口获取IP地址
	 *
	 * @param string $ip
	 * @return null|string
	 */
	private function getIpAddressByTaobao(string $ip){

		$uri = $this->taobao_ip.$ip;

		try{
			$resp = $this->http_get_location($uri);
		}
		catch(\Exception $e){
			Log::error(__FUNCTION__,[
					'request_uri'	=> $uri,
					'curl_exception'=> $e->getMessage()
				]
			);

			return null;
		}

		if(is_null(json_decode($resp))){
			return null;
		}

		$result = json_decode($resp,true);

		if($result['code'] != 0){
			return null;
		}

		return $result['data']['region']."省".$result['data']['city']."市";

	}

	private function getIpAddress00(string $ip){

		$location = geoip($ip)->toArray();

		$ip_address = "";
		if(isset($location['country'])){
			$ip_address = $location['country'];
		}
		if(isset($location['state_name'])){
			$ip_address .= '-'.$location['state_name'];
		}
		if(isset($location['city'])){
			$ip_address .= '-'.$location['city'];
		}

		return $ip_address;

	}

	/**
	 * 获取IP
	 */
	private function getUserIp(){
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){

			$cip = $_SERVER["HTTP_CLIENT_IP"];

		}

		elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){

			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];

		}

		elseif(!empty($_SERVER["REMOTE_ADDR"])){

			$cip = $_SERVER["REMOTE_ADDR"];

		}

		else{

			$cip = "127.0.0.1";

		}
		return $cip;
	}

	/**
	 * @param string $uri
	 * @param array $headers
	 * @return mixed|string
	 * @throws \Exception
	 */
	private function http_get_location(string $uri,array $headers = []){
		//初始化
		$curl = curl_init();
		//设置抓取的url
		curl_setopt($curl, CURLOPT_URL, $uri);
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT,10);//尝试连接的超时时间 单位/秒
		curl_setopt ( $curl, CURLOPT_TIMEOUT,10);//脚本最大执行的超时时间 单位/秒
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
			throw new \Exception("An exception occurs when curl requests：".curl_error($curl));
		}
		//关闭URL请求
		curl_close($curl);
		//显示获得的数据
		return $data;
	}

	/**
	 * 自动转化为UTF-8编码格式
	 *
	 * @param string $str
	 * @return null|string|string[]
	 */
	private function convert_to_utf8(string $str){

		if(empty($str)){
			return $str;
		}

		$encode_type = mb_detect_encoding($str,['UTF-8','GBK','LATIN1','BIG5']);

		if($encode_type != 'UTF-8'){
			return mb_convert_encoding($str,'UTF-8',$encode_type);
		}

		return $str;

	}

}
