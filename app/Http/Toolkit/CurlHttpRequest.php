<?php
/**
 * Created by PhpStorm.
 * User: 86181
 * Date: 2019/1/5
 * Time: 15:14
 */

namespace App\Http\Toolkit;


use Illuminate\Support\Facades\Log;

trait CurlHttpRequest
{

	/**
	 * curl请求超时时间
	 *
	 * @var int
	 */
	protected $curl_time_out = 30;

	/**
	 * 格式化输出
	 *
	 * @param $params
	 */
	public function pr_array($params){

		echo "<pre>";
		print_r($params);

	}

    /**
     * 作用：模拟POST表单提交
     * Content-Type: application/x-www-form-urlencoded; charset=utf-8
     * @param $uri
     * @param $params
     * @return string
     */
    public function http_post_form_data(string $uri,array $params){
        $data = "";
        foreach ($params as $key => $value){
            if($data == ""){
                $data .= $key."=".$value;
            }else{
                $data .= "&".$key."=".$value;
            }
        }
        $ch = curl_init ();  //初始化curl
        curl_setopt ( $ch, CURLOPT_URL, $uri );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,$this->curl_time_out-2);//尝试连接的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_TIMEOUT,$this->curl_time_out);//脚本最大执行的超时时间 单位/秒
        curl_setopt ( $ch, CURLOPT_POST, 1 );  //使用post请求
        curl_setopt ( $ch, CURLOPT_HEADER, 0);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);  //提交数据
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true);  //重定向地址也输出
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//让其不验证ssl证书
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Content-Length: ' . strlen($data))
        );
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

    /**
     * 作用：以JSON字符编码格式发送请求
     * Content-Type:application/json; charset=utf-8
     * @param $url
     * @param $params
     * @return string
     * @throws \Exception
     */
    public function http_post_json_data(string $url,array $params) {
    	$data = json_encode($params);
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,$this->curl_time_out-2);//尝试连接的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_TIMEOUT,$this->curl_time_out);//脚本最大执行的超时时间 单位/秒
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true);  //重定向地址也输出
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//让其不验证ssl证书
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data))
        );
        $resp = curl_exec ( $ch ); //得到返回值
        $return_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if(curl_error($ch)){
            Log::error(__FUNCTION__,[
                    'request_uri'	=> $url,
                    'curl_exception'=>curl_error($ch)]
            );
            $exception = "[The network is out of order] HTTP CODE：".$return_code." EXCEPTION：".json_encode(curl_error($ch));
            throw new \Exception($exception);
        }
        curl_close ( $ch );  //关闭
        return $resp;
    }

	/**
	 * 作用：模拟POST表单提交
	 * Content-Type: multipart/form-data; charset-utf-8
	 * @param $uri
	 * @param $data
	 * @param $headers
	 * @return string
	 */
	function http_post_multi_data(string $uri,array $data,array $headers = []){
		$ch = curl_init ();  //初始化curl
		curl_setopt ( $ch, CURLOPT_URL, $uri );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT,$this->curl_time_out-2);//尝试连接的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_TIMEOUT,$this->curl_time_out);//脚本最大执行的超时时间 单位/秒
		curl_setopt ( $ch, CURLOPT_POST, 1 );  //使用post请求
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER,$headers);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: multipart/form-data; charset=utf-8'
		));
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);  //提交数据
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true);  //重定向地址也输出
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//让其不验证ssl证书
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
