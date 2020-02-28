<?php

namespace App\Models;

use App\Http\Toolkit\AutoGenerate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CodeConfigModel extends Model
{
	use AutoGenerate;
	/**
	 * 编码配置信息表
	 *
	 * @var string
	 */
	protected $table = "code_config";

	/**
	 * config code names
	 *
	 * @var array
	 */
	protected static $code_names = ['word','user','industry','store','goods','order','virtual_cards','web_site','purchase'];

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id','name','prefix','random_bits','code_bits','start_val','latest_val'
	];

	/**
	 * 生成指定名称的唯一编码
	 *
	 * @param string $name
	 * @return bool|string
	 */
	public static function getUniqueCode(string $name){

		if(!in_array($name,self::$code_names)){
			return false;
		}

		DB::beginTransaction();
		$config = self::where('name',$name)->lockForUpdate()->first();
		$code = self::generate_code($config);
		$data['latest_val'] = intval($config->latest_val) + 1;
		$isSave = self::where('name',$name)->update($data);
		DB::commit();
		if($isSave){
			return $code;
		}

		return false;
	}

}
