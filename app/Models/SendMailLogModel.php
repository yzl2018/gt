<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SendMailLogModel extends Model
{
    use SoftDeletes;

	/**
	 * 发送邮件日志表
	 *
	 * @var string
	 */
	protected $table = "send_mail_log";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'mail_type_code','users_id','subject','mail_view','mail_data','attach_path','to_email',
		'result','fail_reason','resend_times','resend_result','resend_response'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'deleted_at'
	];

    /**
     * 获取邮件类型
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mail_type(){

        return $this->hasOne('App\Models\MailTypeModel','code','mail_type_code');

    }

}
