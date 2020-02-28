<?php
namespace App\Http\Controllers\API\Entity;

class MailableEntity
{

	/**
	 * 邮件发送日志id
	 *
	 * @var null
	 */
	public $mail_log_id = null;

	/**
	 * 邮件主题
	 *
	 * @var null
	 */
	public $subject = null;

	/**
	 * 邮件发送使用的邮局通道
	 *
	 * @var null
	 */
	public $mail_channel = null;

	/**
	 * 邮件视图
	 *
	 * @var null
	 */
	public $view  = null;

	/**
	 * 邮件数据
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * 附件路径
	 *
	 * @var null
	 */
	public $attach_path = null;

	/**
	 * 邮件收件人
	 *
	 * @var null
	 */
	public $mail_to = null;

	/**
	 * 待更新邮件状态的表格
	 *
	 * @var null
	 */
	public $update_table = null;

	/**
	 * EmailEntity constructor.
	 *
	 * @param $mail_channel
	 * @param $mail_type_view
	 * @param array $mail_data
	 * @param string $mail_to
	 * @param int $mail_log_id
	 * @param string|null $attach_path
	 */
	public function __construct($mail_channel,$mail_type_view,array $mail_data,string $mail_to,int $mail_log_id,string $attach_path = null)
	{

		$this->mail_channel = $mail_channel;

		if(!empty($mail_type_view->update_table)){
			$this->update_table = json_decode($mail_type_view->update_table,true);
		}

		$this->subject = $mail_type_view->subject;
		$this->view = $mail_type_view->view;
		$this->data = $mail_data;
		$this->mail_to = $mail_to;
		$this->attach_path = $attach_path;
		$this->mail_log_id = $mail_log_id;

	}

}