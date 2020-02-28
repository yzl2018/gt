<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\GetIpLocation;
use App\Models\CrmRequestLogModel;
use Closure;
use App\Http\Toolkit\ServerEncryptTools;
use Illuminate\Support\Facades\Log;

class LogCrmRequest
{
	use GetIpLocation;

	/**
	 * 请求日志id
	 *
	 * @var null
	 */
	private $crm_request_log_id = null;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

    	//记录CRM激活请求
        //$ip = $request->getClientIp();
        $ip = $this->getUserIp();

		$tool = new ServerEncryptTools('','');
		$headers = $request->headers->all();
		$content_type = $headers['content-type'][0];

		if($request->has('Sign') && $request->has('Data')){
			$parameters = $request->input();
			$data_from = 'input';
		}
		else if(isset($headers['content-type']) && str_contains($content_type,'x-www-form-urlencoded') && is_string($request->getContent())){
			$content = $request->getContent();
			$content = trim($content);
			$is_encoded = (strpos(substr($content, strlen($content) - 3), '=') === false);
			if ($is_encoded) {
				// get data from input()
				// data may be encoded or not
				$parameters['Sign'] = $request->input('Sign');
				$parameters['Data'] = $request->input('Data');
			}
			else {
				// get data from getContent()
				// $content is not urlencoded
				$params = [];
				$keys = ['Data','Sign'];
				$arrays = explode("&", $content);
				foreach ($arrays as $arr){
					$key = substr($arr,0,4);
					$value = substr($arr,5);
					if(in_array($key, $keys)) {
						$params[$key] = $value;
					}
				}
				$parameters = $params;
			}
            $data_from = 'getContent';
		}
		else if(isset($_POST['Sign']) && isset($_POST['Data'])){
			$parameters = $_POST;
			$data_from = 'POST';
		}
		else if(isset($_REQUEST['Sign']) && isset($_REQUEST['Data'])){
			$parameters = $_REQUEST;
			$data_from = 'REQUEST';
		}
		else{
			$parameters = [];
			$data_from = '';
		}

		$req_params = [
			'ip'				=> $ip,
			'ip_address'		=> $this->getIpAddress($ip),
			'api'				=> $request->getRequestUri(),
			'method'			=> $request->getMethod(),
			'parameters'		=> json_encode($parameters),
			'content_type'		=> $content_type,
			'data_from'			=> $data_from,
			'request_time'		=> date('Y-m-d H:i:s')
		];

		$sign_data = $tool->getResponseData($parameters);
		$req_params['sign_data'] = $sign_data == false?$sign_data:json_encode($sign_data);

		if($sign_data != false){
			if(isset($sign_data['MerchantCode'])){
				$req_params['merchant_code'] = $sign_data['MerchantCode'];
			}
			if(isset($sign_data['CRMOrderNo'])){
				$req_params['crm_order_no'] = $sign_data['CRMOrderNo'];
			}
		}

		try{
			$req_log_id = CrmRequestLogModel::insertGetId($req_params);

			if($req_log_id){
				$this->crm_request_log_id = $req_log_id;
			}

		}
		catch (\Exception $e){
			Log::error('===New crm request log===',[
				'msg'	=> $e->getMessage(),
				'trace'	=> $e->getTrace()
			]);
		}

		//执行激活请求
    	$response = $next($request);

		//记录CRM激活响应日志
		$resp = $response->original;//获取响应数据源

		if($this->crm_request_log_id == null){
			$req_params['response_time'] = date('Y-m-d H:i:s');
			if(isset($resp['result'])){
				$req_params['result'] = json_encode($resp['result']);
			}
			if(isset($resp['response'])){
				$req_params['response'] = json_encode($resp['response']);
			}

			try{
				CrmRequestLogModel::insert($req_params);
			}
			catch (\Exception $e){
				Log::error('===New crm request log again===',[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
			}
		}

		else{
			$res_params = [
				'response_time'	=> date('Y-m-d H:i:s')
			];

			if(isset($resp['result'])){
				$res_params['result'] = json_encode($resp['result']);
			}
			if(isset($resp['response'])){
				$res_params['response'] = json_encode($resp['response']);
			}

			try{
				CrmRequestLogModel::where('id',$this->crm_request_log_id)->update($res_params);
			}
			catch(\Exception $e){
				Log::error('===Update crm request log for response===',[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
			}
		}

        return response()->json($resp['response']);
    }

}
