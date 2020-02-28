<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\Message;
use App\Http\Toolkit\RESPONSE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;

class AuthPermissionAndParameters
{

    use AppResponse,Message;
    /**
     * 接口权限配置文件
     *
     * @var string
     */
    private $interface_config = "apiinterface.";

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
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->user = Auth::user();

        //return $this->app_response(RESPONSE::APP_DEBUG,'test user info ',$this->user);

		$request_api = $request->getRequestUri();
        if(str_contains($request_api,'?')){
			$api_arr = explode('?',$request_api);
			$request_api = $api_arr[0];
		}

        $permission = config($this->interface_config.$request_api);

        if($permission == null){
            return $this->app_response(RESPONSE::ACCESS_DENIED,$this->say('!005'));
        }

        if(!in_array($this->user['user_type_code'],$permission)){
            return $this->app_response(RESPONSE::ACCESS_DENIED,$this->say('!005'));
        }

        $is_debug = env('APP_DEBUG');

        if($request->method() == 'POST'){

            $config_params = config('request.'.$request_api);

            if($config_params == null){
                return $this->error('未配置此接口的请求参数');
            }

            foreach ($config_params as $key => $value){
                if($value != null && !$request->has($key)){
					if($is_debug){
						return $this->message('缺少参数：'.$key);
					}
					return $this->message('缺少参数');
				}
            }

            foreach ($config_params as $key => $rule_str){
                if(is_string($rule_str)){
                    if(strpos($rule_str,'|') !== false){
                        $rule_arr = explode('|',$rule_str);
                        $rule_func = $rule_arr[0];
                        if(!$rule_func($request->input($key))){
                            if($is_debug){
                                return $this->message($key.'：数据类型不匹配');
                            }
                            return $this->message('数据类型不匹配');
                        }
                        if(strpos($rule_arr[1],',') !== false){
                            $enum = explode(',',$rule_arr[1]);
                            if(!in_array($request->input($key),$enum)){
                                if($is_debug){
                                    return $this->message($key.'：数据值不匹配');
                                }
                                return $this->message('数据值不匹配');
                            }
                        }else{
                            if($request->input($key) != $rule_arr[1]){
                                if($is_debug){
                                    return $this->message($key.'：数据值不匹配');
                                }
                                return $this->message('数据值不匹配');
                            }
                        }
                    }else{
                        if(!$rule_str($request->input($key))){
                            if($is_debug){
                                return $this->message($key.'：数据类型不匹配');
                            }
                            return $this->message('数据类型不匹配');
                        }
                    }
                }
            }
        }

        return $next($request);
    }

}
