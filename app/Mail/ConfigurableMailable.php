<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/10/11 15:05
 * +------------------------------------------------------------------------------
 */

namespace App\Mail;
use Illuminate\Container\Container;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Swift_Mailer;
use Swift_SmtpTransport;

class ConfigurableMailable extends Mailable
{

	/**
	 * Override Mailable functionality to support per-user mail settings
	 *
	 * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
	 * @return void
	 */
	public function send(Mailer $mailer)
	{
		$host      = 'smtp.yandex.com';
		$port      = 465;
		$security  = 'ssl';
		// 配置邮件
		$transport = new Swift_SmtpTransport( $host, $port, $security);
		// 注意看，这里是修改为用户邮箱的配置，$this->config是用户传的数据。
		$transport->setUsername('service@crmcloudtech.com');
		$transport->setPassword('a253298f5a48190!');
		$mailer = new Swift_Mailer($transport);
		
		// 重启Swift_SmtpTransport中ssl的连接要不然会报错
		$mailer->getTransport()->stop();
		
		Container::getInstance()->call([$this, 'build']);

		$mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
			$this->buildFrom($message)
				->buildRecipients($message)
				->buildSubject($message)
				->buildAttachments($message)
				->runCallbacks($message);
		});
	}
}