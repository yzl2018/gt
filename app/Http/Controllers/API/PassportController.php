<?php
namespace App\Http\Controllers\API;

use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\AutoGenerate;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\DataValidator;
use App\Http\Toolkit\Message;
use App\Http\Toolkit\RESPONSE;
use App\Models\CodeConfigModel;
use App\Models\RegisterAuthorizationModel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\UsersModel;

class PassportController
{
	use Message,AppResponse,AutoGenerate,DataValidator,CommunicateWithB;

	/**
	 * 密码授权请求地址
	 *
	 * @var string
	 */
	private $password_auth_uri = "/oauth/token";

	/**
	 * 用户登陆密码
	 *
	 * @var null
	 */
	private $login_password = null;

	/**
	 * 用户注册
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function register(Request $request){

		$validator = Validator::make($request->all(), [
			'language_type_code'	=> 'required',
			'email' 				=> 'required',
			'phone'					=> 'required',
			'password' 				=> 'required',
		]);

		if ($validator->fails()) {
			return $this->app_response(RESPONSE::REGISTER_FAIL,$validator->errors());
		}
		$input = $request->all();

		$this->setLanguage($input['language_type_code']);

        if(!isset($input['user_type_code'])){
            $input['user_type_code'] = 0xB012;//默认创建客户
        }

        if(isset($input['name'])){
            if(mb_strlen($input['name']) > 16){
                return $this->message($this->say('!114'));
            }
        }

        if($input['user_type_code'] != 0xB012){
            return $this->error($this->say('!020'));
        }

		if(!$this->EmailValidator($input['email'])){
			return $this->message($this->say('!006'));
		}

		if(!$this->PhoneValidator($input['phone'])){
			return $this->message($this->say('!007'));
		}

		if(!$this->PasswordValidator($input['password'])) {
            return $this->message($this->say('!008'));
        }

		if($input['user_type_code'] == 0xB012){//验证客户注册认证码
			if(!$request->has('verify_code')){
				return $this->error($this->say('!100'));
			}
			$verify_code = $request->input('verify_code');
			unset($input['verify_code']);
			$verify_res = $this->verifyCusCode($input['email'],$verify_code);
			if($verify_res['flag'] == false){
				return $this->message($verify_res['message']);
			}
		}

		$hasTheEmail = UsersModel::where('email',$input['email'])->count();
		if($hasTheEmail > 0){
			return $this->message($this->say('!003'));
		}

		$hasThePhone = UsersModel::where('phone',$input['phone'])->count();
		if($hasThePhone > 0){
			return $this->message($this->say('!004'));
		}

		$this->login_password = $input['password'];

		$input['code'] = CodeConfigModel::getUniqueCode('user');
		if($input['code'] == false){
			return $this->error('Error generating user code');
		}

		$input['safety_code'] = sha1(time().$this->create_password());//生成安全码

		$input['password'] = bcrypt($input['password']);
		if($input['user_type_code'] == 0xB012){
			$input['operate_password'] = md5('123456');//客户的默认操作密码设置为:123456
		}
		else{
			$input['operate_password'] = md5('147258369');//非客户的默认操作密码设置为:147258369
		}

		$time = time();
		$input['latest_login_time'] = $time;

		$input['active_status'] = 1;//认证码验证成功则将其设置为已激活状态

		$resp = $this->registerUser($input['user_type_code'],$input['safety_code'],$input['email']);
		if(is_null(json_decode($resp))){
			Log::error(__FUNCTION__,$resp);
			return $this->app_response(RESPONSE::REQUEST_B_ERROR,$this->say('!087'));
		}
		$res = json_decode($resp,true);
		if($res['code'] != $this->b_return_success_code){
			if($res['code'] < $this->b_error_code_critical){
				return $this->app_response($res['code'],$res['message']);
			}
			else{
				return $this->app_response(RESPONSE::REQUEST_B_ERROR,$res['message']);
			}
		}

		$input['user_code'] = $res['data']['ucode'];

		$user = User::create($input);
		if(empty($user)){
			return $this->app_response(RESPONSE::REGISTER_FAIL,$this->say('!087'));
		}

		$name = "";
		if(isset($input['name'])){
		    $name = $input['name'];
        }

		$user_login_time = $input['code'].$time;
		$info = [
		    'name'      => $name,
			'email'		=> $input['email'],
			'phone'		=> $input['phone'],
			'language'	=> $this->language_type_code,
			'user_type_code'	=> $input['user_type_code'],
		];

		$token_info = $this->getAccessToken($request,$input['email'],$this->login_password);
		if($token_info == false){
			$token = $user->createToken('mall_users')->accessToken.$user_login_time;
		}else{
			$token 		=  $token_info['access_token'].$user_login_time;
		}
		$success['user_info'] 	= $info;

		return $this->app_response(RESPONSE::SUCCESS,$this->say('!009'),$success,[
			'Set-Cookie' => "X-API-BP-TOKEN={$token};expires=".gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT",time()+config('system.mall.cookie_expires_time'))
		]);
	}

	/**
	 * 用户登陆
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function login(Request $request){

		if(!$request->has('username') || !$request->has('password')){
			return $this->message('Missing parameters.');
		}

		if($request->has('language')){
			$language = strtoupper($request->input('language'));
			$this->setLanguage($language);
		}

		$username = $request->input('username');
		$password = $request->input('password');

		if($this->PhoneValidator($username)){//判断输入的用户名是手机
			$name_key = "phone";
			$flag = Auth::attempt(['phone' => $username,'password' => $password]);
		}else if($this->EmailValidator($username)){//判断输入的用户名是邮箱
			$name_key = "email";
			$flag = Auth::attempt(['email' => $username,'password' => $password]);
		}else{
			return $this->message($this->say('!017'));
		}

		$login_fail_times = UsersModel::where($name_key,$username)->value('login_fail_times');
		if($login_fail_times != null && $login_fail_times >= 5){
			return $this->error($this->say('!061'));
		}

		if($flag){

			$user = $request->user();

			$time = time();
			UsersModel::where('id',$user->id)->update(['latest_login_time' => $time]);

			$user_login_time = $user->code.$time;

			$info = [
			    'name'      => $user->name,
				'email'		=> $user->email,
				'phone'		=> $user->phone,
				'language'	=> $user->language_type_code,
				'user_type_code'	=> $user->user_type_code,
				'latest_login_time' =>$user->latest_login_time
			];

			if($request->has('language')){
				$info['language'] = $this->language_type_code;
			}

			$token_info = $this->getAccessToken($request,$user->email,$password);

			if(empty($token_info)){
				$token = $user->createToken('mall_users')->accessToken.$user_login_time;
			}else{
				$token = $token_info['access_token'].$user_login_time;
			}
			$success['user_info'] = $info;

			return $this->app_response(RESPONSE::SUCCESS,$this->say('!019'),$success, [
			    'Set-Cookie' => "X-API-BP-TOKEN={$token};expires=".gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT",time()+config('system.mall.cookie_expires_time'))
            ]);

		}

		$this->recordFailTimes($name_key,$username);

		return $this->message($this->say('!018'));
	}

	/**
	 * 记录用户登陆失败次数
	 *
	 * @param $name_key
	 * @param $name_val
	 */
	private function recordFailTimes($name_key,$name_val){

		$count = UsersModel::where($name_key,$name_val)->count();
		if($count > 0){
			DB::table('users')->where($name_key,$name_val)->increment('login_fail_times');
		}

	}

    /**
     * 重置密码
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request){

        $validator = Validator::make($request->all(), [
            'email' 					=> 'required',
            //'phone'						=> 'required',
            'verify_code'					=> 'required',
            'new_password' 				=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->app_response(RESPONSE::WARNING,$validator->errors());
        }
        $input = $request->all();

        if(!$this->EmailValidator($input['email'])){
            return $this->message($this->say('!006'));
        }

//		if(!$this->PhoneValidator($input['phone'])){
//			return $this->message($this->say('!007'));
//		}

        if(!$this->PasswordValidator($input['new_password'])){
            return $this->message($this->say('!008'));
        }

//		$phone = DB::table('users')->where('email',$input['email'])->value('phone');
//		if(empty($phone)){
//			return $this->message($this->say('!105'));
//		}
//		if($input['phone'] != $phone){
//			return $this->message($this->say('!106'));
//		}

        $verify_res = $this->verifyCusCode($input['email'],$input['verify_code']);
        if($verify_res['flag'] == false){
            return $this->message($verify_res['message']);
        }

        $is_update = DB::table('users')->where('email',$input['email'])->update(['password'=>bcrypt($input['new_password'])]);
        if($is_update){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!104'));
        }

        return $this->app_response(RESPONSE::WARNING,$this->say('!103'));
    }

    /**
     * 获取登陆认证码
     *
     * @param Request $request
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function getAccessToken(Request $request,$username,$password){

        $client_id = 2;
        $client_secret = DB::table('oauth_clients')->where('id',$client_id)->value('secret');

        $request->request->add([
            'grant_type'	=> 'password',
            'client_id'		=> $client_id,
            'client_secret'	=> $client_secret,
            'username'		=> $username,
            'password'		=> $password,
            'scope'			=> '*',
        ]);

        $proxy = Request::create(
            'oauth/token',
            'POST'
        );

        $response = \Route::dispatch($proxy);

        return json_decode((string)$response->getContent(),true);
    }

	/**
	 * 验证客户认证码
	 *
	 * @param $email
	 * @param $code
	 * @return array
	 */
    private function verifyCusCode($email,$code){

		$start_time = date('Y-m-d H:i:s',time() - 60 * 15);
		$auth_info = DB::table('operate_auth_codes')
			->where('user_name',$email)
			->where('auth_code',$code)
			->where('status',0)
			->where('expires_time','>',$start_time)
			->first();

		if(empty($auth_info)){
			return ['flag'=>false,'message'=>$this->say('!101')];
		}

		if(date('Y-m-d H:i:s') > $auth_info->expires_time){
			return ['flag'=>false,'message'=>$this->say('!102')];
		}

        DB::table('operate_auth_codes')->where('id',$auth_info->id)->update(['status'=>1]);
		return ['flag'=>true,'message'=>'verify success'];

	}

    /**
     * 用户认证
     *
     * @param Request $request
     * @return bool
     */
    public function authUser(Request $request){

        if(!$request->has('email') || !$request->has('sign')){
            return response()->json(['flag'=>false,'message'=>'Invalid request']);
        }

        if(!$request->has('password') && !$request->has('operate_password')){
            return response()->json(['flag'=>false,'message'=>'Invalid request']);
        }

        $params = [
            'email'		=> $request->input('email'),
        ];

        if($request->has('password')){
            $params['password'] = $request->input('password');
        }

        else if($request->has('operate_password')){
            $params['operate_password'] = $request->input('operate_password');
        }

        $sign = $request->input('sign');

        if(empty($params['email']) || empty($sign)){
            return response()->json(['flag'=>false,'message'=>'Invalid request']);
        }

        $verify = $this->verify_simple_sign($params,$sign);

        if($verify == false){
            return response()->json(['flag'=>false,'message'=>'Verify fail']);
        }

        if($request->has('password')){
            $flag = Auth::attempt(['email'=>$params['email'],'password'=>$params['password']]);
        }

        else if($request->has('operate_password')){

            $flag = Auth::attempt(['email'=>$params['email'],'operate_password'=>$params['operate_password']]);

        }

        else{
            $flag = false;
        }

        return response()->json(['flag'=>$flag,'message'=>'Auth result']);

    }

}
