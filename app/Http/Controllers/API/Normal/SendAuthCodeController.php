<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Toolkit\MailChannelsDispatch;
use App\Mail\MAIL;
use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\DataValidator;
use App\Http\Toolkit\RESPONSE;
use App\Models\AuthCodesModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;

class SendAuthCodeController extends ApiController
{

	use MailChannelsDispatch;

	/**
	 * 操作发送类型
	 *
	 * @var array
	 */
    private $operate_type = [
    	0xEA01	=> '注册账号',	//59905
        0xEA02	=> '重置密码'	//59906
	];

	/**
	 * 发送操作认证码
	 *
	 * @param Request $request
	 * @return mixed
	 */
    public function sendAuthCode(Request $request){

        if(!$request->has('email') || !$request->has('operate_type')){
            return $this->message('Missing parameters');
        }
    	$operate_type = $request->input('operate_type');
        $email = $request->input('email');

        if(empty($email)){
            return $this->message('Error email');
        }

    	if(!array_key_exists($operate_type,$this->operate_type)){
			return $this->message($this->say('!096'));
		}

		if(!$this->EmailValidator($email)){
			return $this->message($this->say('!006'));
		}

        if($operate_type == 0xEA01){
            $id = UsersModel::where('email',$email)->value('id');
            if(!empty($id)){
                return $this->message($this->say('!003'));
            }
        }

        else if($operate_type == 0xEA02){
            $id = UsersModel::where('email',$email)->value('id');
            if(empty($id)){
                return $this->message($this->say('!105'));
            }
        }

		$date_time = date('Y-m-d H:i:s');
		$data = [
			'user_name'		=> $email,
			'auth_code'		=> $this->generateNumericCode(),
			'operate_type'	=> $operate_type,
			'expires_time'	=> date('Y-m-d H:i:s',time() + 60 * 10),
			'created_at'	=> $date_time,
			'updated_at'	=> $date_time
		];

        $id = AuthCodesModel::insertGetId($data);
        if(empty($id)){
        	return $this->app_response(RESPONSE::WARNING,$this->say('!097'));
		}

//        $mail_type_code = $this->roundRobinArr([0xE014,0xE015],100);
//        //$mail_type_code = 0xE014;
//        $redis_key = $this->dispatchMailJob($mail_type_code,//使用配置文件派发邮件任务
//			[
//				'type_name'		   => $this->operate_type[$operate_type],
//				'code'             => $data['auth_code'],
//				'expires_time'	   => $data['expires_time']
//			],$email);
//
//        if(empty($redis_key)){
//			return $this->app_response(RESPONSE::WARNING,$this->say('!098'));
//		}

		$dispatch = $this->autoDispatchMailJob(MAIL::mail_send_auth_code,//使用数据库配置派发邮件任务
			[
				'type_name'		   => $this->operate_type[$operate_type],
				'code'             => $data['auth_code'],
				'expires_time'	   => $data['expires_time']
			],$email);

        if($dispatch['success'] == false){
			return $this->app_response(RESPONSE::WARNING,$this->say('!098'));
		}

		return $this->app_response(RESPONSE::SUCCESS,$this->say('!099'),['expires_time'=>$data['expires_time']]);

    }

}
