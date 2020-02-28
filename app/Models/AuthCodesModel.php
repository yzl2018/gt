<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthCodesModel extends Model
{
    /**
     * 编码配置信息表
     *
     * @var string
     */
    protected $table = "operate_auth_codes";

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'id','user_name','auth_code','status','expires_time','created_at','updated_at'
    ];

}
