<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSpecimenInfoModel extends Model
{
	/**
	 * 店铺样品信息表
	 *
	 * @var string
	 */
	protected $table = "store_specimen_info";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'store_info_id','name_word_code','title_word_code','features_word_code','photo','introduce_word_code','sort_number'
	];

    /**
     * 获取样品名称
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function name(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','name_word_code')
            //->where('language_type_code','=',config('system.mall.language'))
            ;

    }

    /**
     * 获取样品标题
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function title(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','title_word_code')
            //->where('language_type_code','=',config('system.mall.language'))
            ;

    }

    /**
     * 获取样品特色
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function features(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','features_word_code')
            //->where('language_type_code','=',config('system.mall.language'))
            ;

    }

    /**
     * 获取样品简介
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function introduce(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','introduce_word_code')
            //->where('language_type_code','=',config('system.mall.language'))
            ;

    }

    /**
     * 更改图片访问地址
     *
     * @param $value
     * @return string
     */
    public function getPhotoAttribute($value){

		if(empty($value)){
            return $value;
        }

        return config('system.upload.file_host').$value;

    }

}
