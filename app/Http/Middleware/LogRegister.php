<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\GetIpLocation;
use App\Models\RegisterActivationLogModel;
use Closure;
use Illuminate\Support\Facades\Log;

class LogRegister
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

    	$date_time = date('Y-m-d H:i:s');

    	$params = [
    		'client_ip'	=> $ip,
			'ip_address'	=> $this->getIpAddress($ip),
			'parameters'	=> json_encode($request->input()),
			'created_at'	=> $date_time,
			'updated_at'	=> $date_time
		];

    	if(property_exists($response,'original')){
			$resp = $response->original;
            if(is_object($resp)){
				if(property_exists($resp,'code')){
					$params['response_code'] = $resp->code;
				}
				if(property_exists($resp,'message')){
					$params['response_message'] = $resp->message;
				}
			}
		}

		try{
			RegisterActivationLogModel::insert($params);
		}
		catch (\Exception $e){
    		Log::error('===log register===',[
    			'msg'	=> $e->getMessage(),
				'trace'	=> $e->getTrace()
			]);
		}

        return $response;
    }

}
