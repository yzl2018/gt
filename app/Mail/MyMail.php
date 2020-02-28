<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/10/12 10:40
 * +------------------------------------------------------------------------------
 */

namespace App\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailFrom;
use App\Mail\MailTo;

class MyMail
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
	 * @param \App\Mail\MailFrom $from
	 * @param \App\Mail\MailTo $to
	 */
	public function __construct(MailFrom $from,MailTo $to)
	{
		$this->from = $from;
		$this->to = $to;
	}

	/**
	 * 发送邮件
	 * @return bool
	 */
	public function send(){
		$backup = Mail::getSwiftMailer(); // 备份原有Mailer
		// 设置邮箱账号
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
		}catch (\Exception $e){
            Log::error(__FUNCTION__,[
                'from_host'	=> $this->from->host,
                'error_exception'	=> $e->getMessage()
            ]);
			$result = -1;
		}
		Mail::setSwiftMailer($backup); // 发送后还原Mailer
		return $result;
	}
}
