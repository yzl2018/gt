<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperateTypeModel extends Model
{
	/**
	 * 用户操作类型表
	 *
	 * @var string
	 */
	protected $table = "operate_type";

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
        'id','code'
    ];
}
