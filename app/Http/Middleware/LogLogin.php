<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\GetIpLocation;
use App\Models\LoginLogModel;
use Closure;
use Illuminate\Support\Facades\Log;

class LogLogin
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
    		'client_ip'		=> $ip,
			'ip_address'	=> $this->getIpAddress($ip),
			'login_user'	=> '',
			'login_at'		=> date('Y-m-d H:i:s')
		];

    	if($request->has('username')){
    		$params['login_user'] = $request->input('username');
		}

		if(property_exists($response,'original')){
			$resp = $response->original;
            if(property_exists($resp,'code')){
                $params['response_code'] = $resp->code;
            }
            if(property_exists($resp,'message')){
                $params['response_message'] = $resp->message;
            }
		}

		try{
			LoginLogModel::insert($params);
		}
		catch (\Exception $e){
			Log::error('===log login===',[
				'msg'	=> $e->getMessage(),
				'trace'	=> $e->getTrace()
			]);
		}

        return $response;
    }
}
