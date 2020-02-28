<?php
namespace App\Http\Toolkit;


class RESPONSE
{

	const SUCCESS 		= 0xFFF;//成功状态码 4095

	const REQUEST_B_ERROR = 0x081;//请求B系统异常 129
	
	const APP_DEBUG		= 0x071;//调试状态码 113

    const RE_LOGIN      = 0x061;//重新登陆状态码 97

	const UNAUTHORIZED  = 0x051;//未授权 81

	const NOT_FOUND		= 0x041;//未找到 65

	const ACCESS_DENIED	= 0x031;//访问被拒绝 49

	const WARNING 		= 0x021;//警告状态码 33

	const NOT_EXIST 	= 0x011;//不存在状态码 17

	const UN_EXCEPTED 	= 0x000;//未预知的状态码 0

	const GET_FAIL		= 0xF01;//获取失败 3841

	const NEW_FAIL		= 0xF02;//新增失败 3842

	const UPDATE_FAIL	= 0xF03;//更新失败 3843

	const DELETE_FAIL	= 0xF04;//删除失败 3844

	const SEND_FAIL		= 0xF05;//发送失败 3845

	const REGISTER_FAIL = 0xF06;//注册失败 3846

	const ACTIVE_FAIL	= 0xF07;//激活失败 3847

	const BUY_FAIL		= 0xF08;//购买失败 3848

	const PAY_FAIL		= 0xF09;//支付失败 3849

}
