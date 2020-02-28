<?php
namespace App\Http\Controllers\API\Entity;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MyMailable
{
	/**
	 * 邮件发送者
	 * @var \App\Mail\MailFrom
	 */
	private $from;

	/**
	 * 邮件接收者
	 * @var \App\Mail\MailTo
	 */
	private $to;

	/**
	 * MyMail constructor.
	 * @param MailableFrom $from
	 * @param MailableTo $to
	 */
	public function __construct(MailableFrom $from,MailableTo $to)
	{
		$this->from = $from;
		$this->to = $to;
	}

	/**
	 * 发送邮件
	 * @return bool
     * @throws \Exception
	 */
	public function send(){
		header("Content-Type:text/html;charset=utf-8");
		$backup = Mail::getSwiftMailer(); // 备份原有Mailer
		// 设置send_mail_log邮箱账号
		$transport = new \Swift_SmtpTransport($this->from->host, $this->from->port, $this->from->encryption);
		$transport->setUsername($this->from->user_name);
		$transport->setPassword($this->from->password);
		if($this->from->stream != null){
			$transport->setStreamOptions($this->from->stream);
		}
		$mailer = new \Swift_Mailer($transport);
		Mail::setSwiftMailer($mailer);
		$result = 1;
		try{
			Mail::send($this->to->view,
				$this->to->data,
				function($message)	{
					$message->from($this->from->from['address'],$this->from->from['name']);
					if($this->to->attachment != ''){
						$message->attach($this->to->attachment);
					}
					$message->to($this->to->receiver)->subject($this->to->subject);
				}
			);
            Mail::setSwiftMailer($backup); // 发送后还原Mailer
		}catch (\Exception $e){
            Mail::setSwiftMailer($backup); // 发送后还原Mailer
			throw new \Exception($e->getMessage());
		}
		return $result;
	}
}