<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/20 17:51
 * +------------------------------------------------------------------------------
 */

namespace App\Mail;

use App\Models\MailTypeModel;
use App\Models\UsersModel;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailShipped;
use App\Models\SendMailLogModel;

class LogAndSendMail
{
	/**
	 * 邮件收件人
	 *
	 * @var
	 */
	private $mail_to;
	
	/**
	 * 邮件配置类型
	 *
	 * @var
	 */
	private $mail_type;
	
	/**
	 * 邮件配置
	 *
	 * @var
	 */
	private $mail_config;
	
	/**
	 * 邮件视图
	 *
	 * @var
	 */
	private $mail_view = "";
	
	/**
	 * 邮件主题
	 *
	 * @var
	 */
	private $mail_subject = "";
	
	/**
	 * 邮件数据参数
	 *
	 * @var
	 */
	private $data_parameters = [];
	
	/**
	 * 邮件数据
	 *
	 * @var
	 */
	private $mail_data;
	
	/**
	 * 用户id
	 *
	 * @var
	 */
	private $user_id;
	
	/**
	 * 邮件类型编码
	 *
	 * @var
	 */
	private $mail_type_code;
	
	/**
	 * 邮件发送结果
	 *
	 * @var array
	 */
	private $send_result = [
		'code'	=> 1,
		'message'	=> null
	];
	
	private $check_flag = true;
	
	/**
	 * 邮件发送类型 0:新发送邮件 1:旧邮件重发
	 *
	 * @var int
	 */
	private $send_type = 0;
	
	/**
	 * 邮件发送日志id
	 *
	 * @var
	 */
	private $mail_log_id;
	
	/**
	 * LogAndSendMail constructor.
	 * @param int $mail_log_id
	 * @param int $user_id
	 * @param int $mail_type_code
	 */
	public function __construct(int $mail_log_id = 0,int $user_id = null,int $mail_type_code = null)
	{
		
		$this->mail_log_id = $mail_log_id;
		$this->user_id = $user_id;
		$this->mail_type_code = $mail_type_code;
		if($mail_log_id != 0){
			$mail_info = SendMailLogModel::findOrFail($this->mail_log_id);
			$this->user_id = $mail_info->users_id;
			$this->mail_type_code = $mail_info->mail_type_code;
		}
		$this->config_mail();
		
	}
	
	/**
	 * 配置邮件参数
	 */
	private function config_mail(){
		
		$this->mail_to = UsersModel::where('id',$this->user_id)->value('email');//获取收件人
		$this->mail_type = MailTypeModel::where('code',$this->mail_type_code)->value('config');//获取邮件配置类型
		
		$this->mail_config = config($this->mail_type);//获取邮件配置信息
		if($this->mail_config == null){
			$this->check_flag = false;
			$this->send_result = [
				'code' => 0,
				'message' => '获取不到邮件配置信息'
			];
		}
		
		if($this->check_flag){
			$this->mail_view = $this->mail_config['view'];//邮件视图
			$this->mail_subject = $this->mail_config['subject'];//邮件主题
			$this->data_parameters = $this->mail_config['parameters'];//邮件数据参数
		}
		
	}
	
	/**
	 * 检查数据参数是否匹配
	 */
	private function checkMailData(){
		
		if(count($this->data_parameters) > 0){
			foreach ($this->data_parameters as $key => $value){
				if($this->check_flag && !array_key_exists($value,$this->mail_data)){
					
					$this->check_flag = false;
					$this->send_result = [
						'code' => 0,
						'message' => '邮件数据参数不匹配'
					];
					
				}
			}
		}
		else{
			$this->check_flag = false;
		}
		
	}
	
	/**
	 * 获取原邮件发送的数据
	 */
	private function getMailData(){
		
		if($this->mail_data == null){
			
			$this->send_type = 1;
			
			if($this->mail_log_id == 0){
				$this->check_flag = false;
				$this->send_result = [
					'code' => 0,
					'message' => '原邮件数据无法正确解析'
				];
			}else{
				$mail_data = SendMailLogModel::where('users_id',$this->user_id)
					->where('mail_type_code',$this->mail_type_code)
					->value('mail_data');
				
				if(is_null(json_decode($mail_data))){
					$this->check_flag = false;
					$this->send_result = [
						'code' => 0,
						'message' => '原邮件数据无法正确解析'
					];
				}
				
				$this->mail_data = json_decode($mail_data);
			}
			
		}
		
	}
	
	/**
	 * 发送邮件
	 *
	 * @param $mail_data
	 * @param $file_path
	 * @return array
	 */
	public function sendMail(array $mail_data = null,string $file_path = null){
		
		$this->mail_data = $mail_data;
		
		$this->getMailData();
		
		if($this->check_flag){
			$this->checkMailData();
		}
		
		if($this->check_flag){
			try{
				
				$this->mail_to = '365956398@qq.com';
				Mail::to($this->mail_to)->send(new MailShipped($this->mail_config,$this->mail_data,$file_path));
				
			}catch (\Exception $e){
				
				$this->send_result = [
					'code'	=> -1,
					'message'	=> $e->getMessage()
				];
				
			}
		}
		
		$this->logMailSend();
		
		return $this->send_result;

	}
	
	/**
	 * 记录邮件发送日志
	 */
	private function logMailSend(){
		
		$mail_log_data = [
			'mail_type_code'	=> $this->mail_type_code,
			'users_id'			=> $this->user_id,
			'subject'			=> $this->mail_subject,
			'mail_view'			=> $this->mail_view,
			'mail_data'			=> json_encode($this->mail_data),
			'to_email'			=> $this->mail_to,
		];
		
		if($this->send_type == 0){
			$mail_log_data['result'] = $this->send_result['code'];
			if($this->send_result['message'] != null){
				$mail_log_data['fail_reason'] = $this->send_result['message'];
			}
			
			SendMailLogModel::create($mail_log_data);
		}
		
		if($this->send_type == 1 && $this->mail_log_id != 0){
			$resend_times = SendMailLogModel::where('id',$this->mail_log_id)->value('resend_times');
			$resend_times++;
			$update_data['resend_times'] = $resend_times;
			$update_data['resend_result'] = $this->send_result['code'];
			if($this->send_result['message'] != null){
				$update_data['resend_response'] = $this->send_result['message'];
			}
			SendMailLogModel::where('id',$this->mail_log_id)->update($update_data);
		}
		
	}
	
}