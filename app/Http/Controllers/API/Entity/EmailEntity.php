<?php
namespace App\Http\Controllers\API\Entity;

use Illuminate\Support\Facades\Log;

class EmailEntity
{

	/**
	 * 邮件发送日志id
	 *
	 * @var null
	 */
	public $mail_log_id = null;

	/**
	 * 邮件类型
	 *
	 * @var null
	 */
	public $mail_type = null;

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
    public $mail_channel = 'default';

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
	 * @param string $mail_type_code
	 * @param array $mail_data
	 * @param string $mail_to
	 * @param string|null $attach_path
	 * @param int $mail_log_id
	 */
	public function __construct(string $mail_type_code,array $mail_data,string $mail_to,string $attach_path = null,int $mail_log_id = null)
	{

		$config = config('mailtype.'.strval($mail_type_code));
		if($config == null){
			Log::info('Email entity get mail config error');
		}

        if(isset($config['update_table'])){
            $this->update_table = $config['update_table'];
        }

		$this->mail_type = $mail_type_code;
        if(array_key_exists('channel',$config)){
            $this->mail_channel = $config['channel'];
        }
		$this->subject = $config['subject'];
		$this->view = $config['view'];
		foreach ($config['parameters'] as $parameter){
			$this->data[$parameter] = '';
		}

		foreach ($this->data as $key => $value){
			if(!array_key_exists($key,$mail_data)){
				Log::info($this->mail_type.':mail missing parameter '.$key);
			}
			$this->data[$key] = $mail_data[$key];
		}

		$this->mail_to = $mail_to;
		$this->attach_path = $attach_path;
		$this->mail_log_id = $mail_log_id;

	}

}
