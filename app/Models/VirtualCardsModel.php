<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualCardsModel extends Model
{
    /**
     * 商品信息表
     *
     * @var string
     */
    protected $table = "virtual_cards";

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'id','code','name','price','litpic','currency_type_code','buy_limit','buy_stop','created_at','updated_at'
    ];

    /**
     * 在数组中隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        'name_word_code'
    ];

    /**
     * 获取商品名称
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function name(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','name_word_code')
            //->where('language_type_code','=',config('system.mall.language'))
            ;

    }

    /**
     * 更改图片访问地址
     *
     * @param $value
     * @return string
     */
    public function getLitpicAttribute($value){

		if(empty($value)){
            return $value;
        }

        return config('system.upload.file_host').$value;

    }

}
