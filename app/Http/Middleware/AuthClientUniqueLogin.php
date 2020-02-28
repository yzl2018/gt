<?php

namespace App\Http\Middleware;

use App\Http\Toolkit\RESPONSE;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

class AuthClientUniqueLogin
{

    /**
     * 携带的客户端信息长度 code + time
     * 如：U4980010081508111111
     *
     * @var int
     */
    private $client_info_len = 20;

    /**
     * 用户编码长度
     *
     * @var int
     */
    private $user_code_len = 10;

    /**
     * cookie token name
     *
     * @var string
     */
    private $cookie_token_name = 'X-API-BP-TOKEN';

    /**
     * Handle an incoming request.
     * 限制客户只能使用唯一客户端登陆
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth_info = $request->cookie($this->cookie_token_name);
        if(empty($auth_info)){
            $auth_info = $request->header('Authorization');
            if(empty($auth_info)){
                return response()->json(['code' => RESPONSE::UNAUTHORIZED,'message' => 'Unauthorized']);
            }
        }

        $str_length = mb_strlen($auth_info);
        $authorization = substr($auth_info,0,$str_length-$this->client_info_len);
        $client_info = substr($auth_info,$str_length-$this->client_info_len);
        $request->headers->set('Authorization','Bearer '.$authorization);
        $user_code = mb_substr($client_info,0,$this->user_code_len);
        $auth_client_time = mb_substr($client_info,$this->user_code_len);

        $latest_login_time = DB::table('users')->where('code',$user_code)->value('latest_login_time');
        if(empty($auth_client_time)){
            return response()->json(['code' => RESPONSE::UNAUTHORIZED,'message' => 'Unauthorized']);
        }
        if($auth_client_time != $latest_login_time){
            return response()->json(['code' => RESPONSE::RE_LOGIN,'message' => 'You have been logged in another place'])
                ->withCookie(Cookie::forget($this->cookie_token_name));
        }
        $diff_time = time() - $latest_login_time;
        $expires_time = config('system.mall.cookie_expires_time');
        if($diff_time > $expires_time){
            return response()->json(['code' => RESPONSE::UNAUTHORIZED,'message' => 'Unauthorized'])
                ->withCookie(Cookie::forget($this->cookie_token_name));
        }
        return $next($request);
    }
}
