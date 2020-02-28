<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\GetIpLocation;
use App\Models\UserOperateLogModel;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogUserOperate
{
	use AppResponse,GetIpLocation;
	/**
	 * 用户实体
	 *
	 * @var \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	private $user;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
	 * @param string $type
     * @return mixed
     */
    public function handle($request, Closure $next,string $type = null)
    {

    	$response = $next($request);

        //$ip = $request->getClientIp();
        $ip = $this->getUserIp();

    	if(empty($type)){
    		Log::error('===log user operate===','用户操作日志中间件未传入有效类型参数');
		}

		else{

			$this->user = Auth::user();

			$type_code = config('system.operate_type.'.$type,$type);

			$params = [
				'users_id'			=> '',
				'client_ip'			=> $ip,
				'ip_address'		=> $this->getIpAddress($ip),
				'operate_type_code'	=> $type_code,
				'api'				=> $request->getRequestUri(),
				'method'			=> $request->getMethod(),
				'parameters'		=> json_encode($request->input()),
				'time'				=> date('Y-m-d H:i:s')
			];

			if(property_exists($response,'original')){
				if($type == 'view_voucher_key'){
					$response->original->data = "******";
				}
				$params['response'] = json_encode($response->original,JSON_UNESCAPED_UNICODE);
			}
			else{
				$params['response'] = json_encode($response,JSON_UNESCAPED_UNICODE);
			}

			if(isset($this->user['id']) && !empty($this->user['id'])){
				$params['users_id'] = $this->user['id'];
				try{
					UserOperateLogModel::insert($params);
				}
				catch (\Exception $e){
					Log::error('===log user operate===',[
						'msg'	=> $e->getMessage(),
						'trace'	=> $e->getTrace()
					]);
				}
			}

		}

        return $response;
    }
}
