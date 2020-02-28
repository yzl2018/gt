<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\GetIpLocation;
use App\Models\BNotifyLogModel;
use Closure;
use Illuminate\Support\Facades\Log;

class LogPaymentNotify
{
    use GetIpLocation;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

    	$response = $next($request);

        //$ip = $request->getClientIp();
        $ip = $this->getUserIp();

    	$params = [
    		'sys_ip'			=> $ip,
            'ip_address'		=> $this->getIpAddress($ip),
			'api'				=> $request->getRequestUri(),
			'method'			=> $request->getMethod(),
			'parameters'		=> json_encode($request->input()),
			'time'				=> date('Y-m-d H:i:s')
		];

    	if(property_exists($response,'original')){

    		$result = $response->original;
    		if(is_array($result)){
				$params['response_result'] = json_encode($result);
			}
			else{
				$params['response_result'] = strval($result);
			}

		}

    	try{
			BNotifyLogModel::insert($params);
		}
		catch (\Exception $e){
    		Log::error('===log payment notify===',[
    			'msg'	=> $e->getMessage(),
				'trace'	=> $e->getTrace()
			]);
		}

        return $response;
    }
}
