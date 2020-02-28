<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/29 10:20
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Toolkit;


trait DataValidator
{
	/**
	 * 验证手机号码的正则表达式
	 * 移动号段: 134,135,136,137,138,139,147,150,151,152,157,158,159,170,178,182,183,184,187,188
	 * 联通号段: 130,131,132,145,155,156,170,171,175,176,185,186
	 * 电信号段: 133,149,153,170,173,177,180,181,189
	 * 共11位
	 *
	 * @var string
	 */
	//protected $mobile_phone_regex = '/^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(19[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$/';
	protected $mobile_phone_regex = '/^\d+$/';//只需满足11位的数字

	/**
	 * 验证邮箱的正则表达式
	 * @前面的字符可以是英文字母和._- ，._-不能放在开头和结尾，且不能连续出现
	 *
	 * @var string
	 */
	protected $email_regex = '/^[a-z0-9]+([._-][a-z0-9]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/';
	//  '/^[a-z0-9]+([._-][a-z0-9]+)*@([0-9a-z]+\.[a-z]{2,14}(\.[a-z]{2})?)$/i';


	/**
	 * 验证密码的正则表达式
	 * 必须包含字母和数字且大于6位
	 *
	 * @var string
	 */
	protected $password_regex = '/^(?![^a-zA-Z]+$)(?!\D+$).{6,64}$/';

	/**
	 * 手机号码验证
	 *
	 * @param string $mobile_phone
	 * @return bool|false|int
	 */
	protected function PhoneValidator(string $mobile_phone){

		if(is_null($mobile_phone)){
			return false;
		}

		return preg_match($this->mobile_phone_regex,$mobile_phone);
	}

	/**
	 * 电子邮箱验证
	 *
	 * @param string $email
	 * @return bool|false|int
	 */
	protected function EmailValidator(string $email){

		if(strpos($email,'@') && strpos($email,'.')){
			return true;
		}
		
		else{
			return false;
		}
		
		//return preg_match($this->email_regex,$email);
	}

	/**
	 * 密码验证
	 *
	 * @param string $password
	 * @return bool|false|int
	 */
	protected function PasswordValidator(string $password){

		if(is_null($password)){
			return false;
		}

		return preg_match($this->password_regex,$password);
	}
}
