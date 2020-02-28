<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOperateLogModel extends Model
{
	use SoftDeletes;

	/**
	 * 用户操作类型表
	 *
	 * @var string
	 */
	protected $table = "user_operate_log";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'users_id','operate_type_code','client_ip','api','method','parameters','time'
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
     * 获取操作类型名
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function operate_type(){

        return $this->hasOne('App\Models\OperateTypeModel','code','operate_type_code');

    }
}
