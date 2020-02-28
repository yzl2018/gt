<?php
namespace App\Http\Controllers\API\Debug;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\MailChannelsDispatch;
use App\Jobs\SendEmail;
use App\Mail\MAIL;
use App\Http\Controllers\API\Entity\EmailEntity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class MessageController extends ApiController
{

	use MailChannelsDispatch;

	/**
	 * 测试队列发送邮件
	 */
	public function testQueueMail(){

//		$redis_key = $this->dispatchMailJob(MAIL::payment_success,
//			[
//				'Vouchers'             => ['CNTN1908WLYLFYOTBD'=>'QSKL53'],
//				'Value'				   => 5000,
//				'Currency'			   => 'CNY'
//			],'weixiusen@126.com',config('system.mall.send_card_key_mail_delay_seconds'));

//		$resp = $this->autoDispatchMailJob(MAIL::mail_send_auth_code,//发送认证码邮件
//			[
//				'type_name'		   => '注册账号',
//				'code'             => '542085',
//				'expires_time'	   => '2019-05-16 16:10:22'
//			],'td194672513@163.com');

//		$mail_channel = DB::table('mail_channels')->where('code','ali002')->first();
//		$resp = $this->testDispatchMailJob($mail_channel);

//		$resp = $this->autoDispatchMailJob(MAIL::mail_send_cards_info,//发送卡号卡密邮件
//			[
//				'Vouchers'             => ['CNMN1901C9UNWFMNLC'=>'UV78OE'],
//				'Value'				   => 70000,
//				'Currency'			   => 'CNY'
//			],'td194672513@163.com');

		$resp = $this->autoDispatchMailJob(MAIL::mail_send_invoice_info,//发送兑换券消费邮件
		[
                'OrderNo'             => 'GT1558065055200007317',
                'Value'				   => 70000,
                'Currency'			   => 'CNY',
                'CustomerEmail'			=> '16416844df3d@163.com',
                'ProName'		=> 'Cle de Peau 肌肤之钥金致乳霜50ml+晶致眼霜15ml',
                'ProPrice'			=> 70000,
                'ProNumber'		=> 1
			],'1595225840@qq.com');

		print_r($resp);

	}

	/**
	 * 查看邮件视图
	 *
	 * @param Request $request
	 * @param $type
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
	 */
	public function showMailView(Request $request,$type){

		switch ($type){
			case 'auth':
				return view('emails.authcode',[
					'type_name'		   => '注册账号',
					'code'             => '542085',
					'expires_time'	   => '2019-05-16 16:10:22'
				]);
				break;
			case 'shopping':
				return view('emails.shopping',[
					'Vouchers'             => ['CNMN1901C9UNWFMNLC'=>'UV78OE'],
					'Value'				   => 70000,
					'Currency'			   => 'CNY'
				]);
				break;
			case 'convert':
				return view('emails.convert',[
					'VoucherNo'     => 'CNMN1901C9UNWFMNLC',
					'VoucherValue'  => 70000,
					'Currency'      => 'CNY'
				]);
				break;
			case 'payment':
				return view('payment.success',[
					'order_id'		=> 'CariTech1558065055200007317	',
					'order_amount'	=> 70000,
					'order_time'	=> '2019-05-16 16:10:22'
				]);
				break;
			case 'invoice':
				return view('emails.invoice',[
					'OrderNo'             => 'Lumo1558065055200007317',
					'Value'				   => 70000,
					'Currency'			   => 'CNY',
					'CustomerEmail'			=> '16416844df3d@163.com',
					'ProName'		=> 'Cle de Peau 肌肤之钥金致乳霜50ml+晶致眼霜15ml',
					'ProPrice'			=> 70000,
					'ProNumber'		=> 1
				]);
				break;
			case 'purchase':
				return view('guide.purchase',[
					'merchant_code'	=> '10043',
					'access_host'	=> 'http://id1.gt-trip.com'
				]);
				break;
			case 'activation':
                return view('guide.activation',[
                    'merchant_code'	=> '10043'
                ]);
				break;
			default:
				return 'Invalid parameter';
				break;

		}

	}
}
