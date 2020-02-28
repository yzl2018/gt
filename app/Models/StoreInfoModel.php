<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreInfoModel extends Model
{
	/**
	 * 商家店铺信息表
	 *
	 * @var string
	 */
	protected $table = "store_info";

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'code','industry_id','logo','litpic','grade','tag_label'
    ];

    /**
     * 在数组中隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        'name_word_code','introduce_word_code','address_word_code','evaluation_word_code','created_at','updated_at','deleted_at'
    ];

	/**
	 * 获取商家名称信息
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function name(){

		return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','name_word_code')
			//->where('language_type_code','=',config('system.mall.language'))
			;

	}

	/**
	 * 获取商家简介信息
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function introduce(){

		return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','introduce_word_code')
			//->where('language_type_code','=',config('system.mall.language'))
			;
	}

	/**
	 * 获取商家店铺地址信息
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function address(){

		return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','address_word_code')
			//->where('language_type_code','=',config('system.mall.language'))
			;
	}

    /**
     * 获取店铺的评价信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evaluation(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','evaluation_word_code')
            ;

    }

    /**
     * 获取所属店铺的样品信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function specimens(){

        return $this->hasMany('App\Models\StoreSpecimenInfoModel','store_info_code','code')
            ->with('name','title','features','introduce')
            ->orderBy('sort_number')
            ;

    }

    /**
     * 更改图片访问地址
     *
     * @param $value
     * @return string
     */
    public function getLogoAttribute($value){

        return config('system.upload.file_host').$value;

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
