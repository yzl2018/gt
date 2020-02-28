<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfigurationModel extends Model
{
    /**
     * 系统参数配置表
     *
     * @var string
     */
    protected $table = "system_configuration";

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'key_code','key_value','key_name','data_type','data_options'
    ];

    /**
     * 在数组中隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        'created_at','updated_at'
    ];
}
