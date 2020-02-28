<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTypeModel extends Model
{
	/**
	 * 邮件类型表
	 *
	 * @var string
	 */
	protected $table = "mail_type";

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * 在数组中隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        'id','code','config','created_at','updated_at','deleted_at'
    ];
}
