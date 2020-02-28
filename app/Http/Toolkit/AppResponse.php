<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/20 10:06
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Toolkit;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait AppResponse
{
	
	/**
	 * 响应状态码
	 *
	 * @var
	 */
	protected $response_code = RESPONSE::UN_EXCEPTED;
	
	/**
	 * 响应信息
	 *
	 * @var string
	 */
	protected $response_message = "Unexpected error";
	
	/**
	 * 响应数据
	 *
	 * @var null
	 */
	protected $response_data = null;
	
	/**
	 * 响应头
	 *
	 * @var array
	 */
	protected $response_header = [];
	
	/**
	 * HTTP 状态码
	 *
	 * @var int
	 */
	protected $statusCode = FoundationResponse::HTTP_OK;
	
	/**
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}
	
	/**
	 * @param $statusCode
	 * @return $this
	 */
	public function setStatusCode($statusCode)
	{
		$this->statusCode = $statusCode;
		return $this;
	}
	
	/**
	 * 响应行为
	 *
	 * @return mixed
	 */
	public function respond()
	{
		
		$response_data = [
			'code'		=> $this->response_code,
			'message'	=> $this->response_message,
			'data'		=> $this->response_data
		];

		return response()->json($response_data, $this->statusCode, $this->response_header)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * 响应信息设置
	 *
	 * @param $config
	 * @param array|null $header
	 * @return mixed
	 */
	public function setResponse($config = null,array $header = null){
		
		if(isset($config['code'])){
			$this->response_code = $config['code'];
		}
		
		if(isset($config['message'])){
			$this->response_message = $config['message'];
		}
		
		if(isset($config['data'])){
			$this->response_data = $config['data'];
		}
		
		if($header != null){
			$this->response_header = $header;
		}
		
		return $this;
		
	}
	
	/**
	 * 响应调试信息
	 *
	 * @param $data
	 * @return mixed
	 */
	public function debug($data){
		
		$this->response_code = RESPONSE::APP_DEBUG;
		$this->response_message = "app debug";
		$this->response_data = $data;
		
		return $this->respond();
		
	}
	
	/**
	 * 响应未预知的错误
	 *
	 * @param $message
	 * @return mixed
	 */
	public function error($message = null){
		
		if($message !== null){
			$this->response_message = $message;
		}
		
		return $this->setResponse()->respond();
		
	}
	
	/**
	 * 响应不存在的错误信息
	 *
	 * @param null $message
	 * @return mixed
	 */
	public function notExist($message = null){
		
		$config = [
			'code'		=> RESPONSE::NOT_EXIST,
			'message'	=> 'This not exist'
		];
		
		if($message != null){
			$config['message'] = $message;
		}
		
		return $this->setResponse($config)->respond();
		
	}
	
	/**
	 * 只响应警告信息
	 *
	 * @param $message
	 * @return mixed
	 */
	public function message(string $message){
		
		$config = [
			'code'		=> RESPONSE::WARNING,
			'message'	=> $message
		];
		
		return $this->setResponse($config)->respond();
		
	}
	
	/**
	 * 统一响应接口
	 *
	 * @param int $code
	 * @param string $message
	 * @param null $data
	 * @param array|null $header
	 * @return mixed
	 */
	public function app_response(int $code,string $message,$data = null,array $header = null){
	
		$config = [
			'code' => $code,
			'message'	=> $message
		];
		
		if($data != null){
			$config['data'] = $data;
		}
		
		return $this->setResponse($config,$header)->respond();
	
	}
	
}