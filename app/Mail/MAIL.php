<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/21 12:30
 * +------------------------------------------------------------------------------
 */

namespace App\Mail;


class MAIL
{

	const register_active_user 			= 0xE010;//注册激活邮件 57360

	const register_active_user_success 	= 0xE011;//注册激活成功邮件 57361

	const payment_success				= 0xE012;//支付成功邮件 57362

	const card_active_success			= 0xE013;//充值卡激活成功邮件 57363

    const send_auth_code				= 0xE014;//发送随机数字认证码 57364
	
	const mail_send_auth_code			= 0xE301;//发送认证码邮件 58113

	const mail_send_cards_info			= 0xE302;//购买成功后发送卡号卡密邮件 58114

	const mail_supply_cards_info		= 0xE303;//补发卡号卡密邮件 58115

	const mail_send_activation_info		= 0xE304;//激活成功后发送兑换券消费邮件 58116

	const mail_supply_activation_info	= 0xE305;//补发兑换券消费邮件 58117
	
	const mail_send_invoice_info		= 0xE306;//发送invoice邮件给客户 58118
	
	const mail_send_purchase_guide		= 0xE307;//发送注册购买引导邮件给客户 58119

	const mail_send_activation_guide	= 0xE308;//发送兑换券充值引导邮件给客户 58120
	
	const mail_send_register_success	= 0xE30A;//发送注册成功邮件 58122
}
