<?php

namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CurlHttpRequest;
use App\Http\Toolkit\RESPONSE;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends ApiController
{
    use CurlHttpRequest;
	/**
	 * 响应数据的键名数组
	 *
	 * @var array
	 */
	protected $response_data_keys = [
		'id',
		'code',
		'name',
		'email',
		'phone',
		'user_type_code',
		'user_code',
		'login_fail_times',
		'active_status',
		'language_type_code',
		'created_at'
	];

	/**
	 * 获取所有用户信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showAllUsers(Request $request){

		$this->getUser();
        $this->getSearchParams($request);

		if($this->isAdmin()){
			$list = UsersModel::select($this->response_data_keys)
                ->orderBy('created_at','desc')
                ->when($this->is_paginate,function($query){
                    return $query->paginate($this->page_items);
                },function($query){
                    return $query->get();
                });
		}
		else{
			$list = UsersModel::where('user_type_code',config($this->customer_code_config))
                ->select($this->response_data_keys)
                ->orderBy('created_at','desc')
                ->when($this->is_paginate,function($query){
                    return $query->paginate($this->page_items);
                },function($query){
                    return $query->get();
                });
		}

		return $this->app_response(RESPONSE::SUCCESS,'get users success',$list);
	}

	/**
	 * --------------------------------------------------------------------------
	 * 用户获取自己的基本信息
	 * --------------------------------------------------------------------------
	 * @route  /api/user/info
	 * @method GET
	 * @param Request $request
	 * Request data [null]
	 *
	 * @return \Illuminate\Http\Response such as:{code:int,message:string,data:[]|{}|null}
	 * Response data [json]:
	 * {
	 * 		code:int 		//响应状态码 只有 0xFFF 表示成功 其它均为失败
	 * 		message:string 	//响应信息
	 * 		data:{ 			//响应数据
	 * 			id：int											//用户id
	 *  		code：string										//用户编码
	 * 			name：string										//用户昵称
	 *  		email: string									//用户邮箱
	 *  		phone：string									//用户手机号码
	 *  		user_type_code：int enum(0xB010,0xB011,0xB012)	//用户类型 (0xB010：管理员,0xB011：客服,0xB012：客户)
	 *  		user_code：string								//B系统中用户唯一编码
	 *  		login_fail_times：int							//登录失败次数
	 *  		active_status：int enum(0,1)						//激活状态 (0：未激活,1：已激活)
	 *  		language_type_code：string enum('CN','EN')		//使用的语言编码 ('CN'：中文,'EN'：English)
	 *  		created_at：DateTime fmt('Y-m-d H:i:s')			//生成时间
	 * 			updated_at: DateTime fmt('Y-m-d H:i:s')			//更新时间
	 * 		}
	 * }
	 *
	 */
	public function getUserInfo(Request $request){

		$this->getUser();

		return $this->app_response(RESPONSE::SUCCESS,'get user info success',$this->user);
	}

	/**
	 * 更新自己的用户信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateUserInfo(Request $request){

		$this->getUser();

		$isUpdate = UsersModel::where('id',$this->user['id'])->update($request->input());

		if($isUpdate > 0){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!030'));
		}

		return $this->app_response(RESPONSE::UPDATE_FAIL,$this->say('!029'));
	}

    /**
     * 更新用户自己的登陆密码
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateLoginPassword(Request $request){

        $this->getUser();

        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');

        $params = [
            'email'		=> $this->user['email'],
            'password'	=> $old_password
        ];

        $params['sign'] = $this->create_simple_sign($params);
//		$result = $this->http_post_json_data(config('system.mall.server_root').'/api/auth/user',$params);
//		$res = json_decode($result,true);
//
//		if($res['flag'] == false){
//			return $this->app_response(RESPONSE::WARNING,$this->say('!031'));
//		}

        $data = [
            'password'	=> bcrypt($new_password)
        ];
        $isUpdate = UsersModel::where('id',$this->user['id'])->update($data);

        if($isUpdate > 0){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!033'));
        }

        return $this->app_response(RESPONSE::UPDATE_FAIL,$this->say('!032'));
    }

    /**
     * 更新用户自己的支付密码
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateOperatePassword(Request $request){

        $this->getUser();

        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');

        $count = UsersModel::where('id',$this->user['id'])->where('operate_password',$old_password)->count();
        if($count == 0){
            return $this->message($this->say('!034'));
        }

        $data = [
            'operate_password'	=> $new_password
        ];

        $isUpdate = UsersModel::where('id',$this->user['id'])->update($data);

        if($isUpdate > 0){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!033'));
        }

        return $this->app_response(RESPONSE::UPDATE_FAIL,$this->say('!032'));

    }

	/**
	 * 关闭或打开用户登陆的锁定状态
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function changeUserStatus(Request $request){

        $this->getUser();

        $count = UsersModel::where('id',$this->user['id'])->where('operate_password',$request->input('operate_password'))->count();
        if($count == 0){
            return $this->message($this->say('!034'));
        }

		$uid = $request->input('uid');
		$params = [
			'active_status'	=> $request->input('status')
		];
		$isChanged = UsersModel::where('id',$uid)->update($params);

		if($isChanged > 0){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!030'));
		}
		return $this->app_response(RESPONSE::UPDATE_FAIL,$this->say('!029'));
	}

	/**
	 * 解锁指定id的用户账户
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function unlockUser(Request $request){

		$this->getUser();

		$count = UsersModel::where('id',$this->user['id'])->where('operate_password',$request->input('operate_password'))->count();
		if($count == 0){
			return $this->message($this->say('!034'));
		}

		$uid = $request->input('uid');

		$hasTheUser = UsersModel::where('id',$uid)->where('email',$request->input('email'))->count();
		if($hasTheUser == 0){
			return $this->message($this->say('!035'));
		}

		$login_fail_times = UsersModel::where('id',$uid)->value('login_fail_times');
		if($login_fail_times == 0){
			return $this->message($this->say('!036'));
		}

		$isUnlock = UsersModel::where('id',$uid)->update(['login_fail_times' => 0]);

		if($isUnlock > 0){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!037'));
		}

		return $this->app_response(RESPONSE::UPDATE_FAIL,$this->say('!038'));
	}

	/**
	 * 用户切换使用的语言
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function changeLanguage(Request $request){

		$this->getUser();

		$language_type_code =  $request->input('language_code');
		if($language_type_code == $this->user['language_type_code']){
			return $this->message($this->say('!039'));
		}

		$isChanged = UsersModel::where('id',$this->user['id'])->update(['language_type_code'=>$language_type_code]);

		if($isChanged > 0){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!040'));
		}

		return $this->app_response(RESPONSE::UPDATE_FAIL,$this->say('!041'));
	}

    /**
     * 获取用户是否有访问支付测试页面的权限
     *
     * @return mixed
     */
    public function getTestPaymentAccessPermission(){

        $this->getUser();

        $allow_users = config('system.payment_test.allow_users');
        if($allow_users != '*' && !in_array($this->user['email'],$allow_users)){
            return $this->message('You have no such permission,access denied.');
        }

        return $this->app_response(RESPONSE::SUCCESS,'You can access the test page.');

    }

}
