<?php
namespace App\Http\Toolkit;

use Illuminate\Http\Request;

class Configuration
{
	/**
	 * 配置实例
	 *
	 * @var Configuration
	 */
	static private $config;

	/**
	 * 请求的接口
	 *
	 * @var string
	 */
	private $request_api;

	/**
	 * 请求方式
	 *
	 * @var string
	 */
	private $request_method;

	/**
	 * 获取参数配置的键名
	 *
	 * @var string
	 */
	private $config_key = "request";

	/**
	 * 第一个分割符
	 *
	 * @var string
	 */
	private $first_decimal = "|";

	/**
	 * 第二个分割符
	 *
	 * @var string
	 */
	private $second_decimal = ",";

	/**
	 * 原始请求数据
	 *
	 * @var
	 */
	private $request_data = null;

	/**
	 * 参数配置数组
	 *
	 * @var array
	 */
	private $config_array;

	/**
	 * 合法构造后的参数数据
	 *
	 * @var array
	 */
	private $params_data = [];

	/**
	 * 是否是调试环境
	 *
	 * @var boolean
	 */
	private $is_debug;

	/**
	 * 验证结果
	 *
	 * @var bool
	 */
	private $auth_result = true;

	/**
	 * 错误信息
	 *
	 * @var null/string/array
	 */
	private $error_message = null;

	/**
	 * 构造初始化配置数组
	 *
	 * Configuration constructor.
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->is_debug = env('APP_DEBUG');
		$this->request_api = $request->getRequestUri();
		$this->request_method = $request->method();
		$this->request_data = $request->input();
		$this->config_key .= $this->request_api;
		$this->config_array = config($this->config_key);
		$this->createParams();
	}

	/**
	 * 获取合法请求参数
	 *
	 * @param Request $request
	 * @return array|bool
	 */
	public static function getParams(Request $request){

		self::$config = new Configuration($request);

		if(self::$config->auth_result){
			return self::$config->params_data;
		}

		return self::$config->error_message;
	}

	/**
	 * 生成合法请求参数
	 */
	public function createParams(){

		if($this->request_method == "GET"){
			$this->params_data = $this->request_data;
		}

		else if($this->config_array == null){
			$this->error_message = "未配置此接口的请求参数";
			$this->auth_result = false;
		}

		else{
			foreach ($this->config_array as $key => $value){
				if($this->auth_result){
				    if(is_string($value)){
                        $rules = $this->getRules($value);
                        if($rules['type'] !== "null"){//必填参数
                            if(!isset($this->request_data[$key])){
                                if($this->is_debug){
                                    $this->error_message = '缺少参数：'.$key;
                                }else{
                                    $this->error_message = "缺省参数";
                                }
                                $this->auth_result = false;
                            }
                            else{
                                if(!$this->authData($rules,$this->request_data[$key],$key)){
                                    $this->auth_result = false;
                                }else{
                                    $this->params_data[$key] = $this->request_data[$key];
                                }
                            }
                        }else{//可选填参数
                            if(isset($this->request_data[$key])){
                                if(!$rules['func']($this->request_data[$key])){
                                    if($this->is_debug){
                                        $this->error_message = '数据类型不匹配：'.$key;
                                    }else{
                                        $this->error_message = "数据类型不匹配";
                                    }
                                    $this->auth_result = false;
                                }else{
                                    $this->params_data[$key] = $this->request_data[$key];
                                }
                            }
                        }
                    }

					if(is_array($value)){
                        if(!isset($this->request_data[$key])){
                            if($this->is_debug){
                                $this->error_message = '缺少参数：'.$key;
                            }else{
                                $this->error_message = "缺省参数";
                            }
                            $this->auth_result = false;
                        }
                        else{
                            $this->params_data[$key] = $this->request_data[$key];
                        }
                    }
				}
			}
		}

	}

	/**
	 * 识别参数规则
	 *
	 * @param string $rule_str
	 * @return array
	 */
	private function getRules(string $rule_str){

		$rules = [
			'func'	=> null,
			'type'	=> null,
			'enum'	=> null
		];

		if(strpos($rule_str,$this->first_decimal) !== false){
			$arr = explode($this->first_decimal,$rule_str);
			$rules['func'] = $arr[0];
			if(strpos($arr[1],$this->second_decimal) !== false){
				$rules['enum'] = explode($this->second_decimal,$arr[1]);
			}else{
				$rules['type'] = $arr[1];
			}
		}else{
			$rules['func'] = $rule_str;
		}

		return $rules;
	}

	/**
	 * 参数数据规则验证
	 *
	 * @param array $rules
	 * @param $data
	 * @param null $key
	 * @return bool
	 */
	private function authData(array $rules,$data,$key = null){

		if($rules['func'] === null){
			$this->error_message = "参数规则的格式不正确";
			return false;
		}

		if(!$rules['func']($data)){
			if($this->is_debug){
				$this->error_message = '数据类型不匹配：'.$key;
			}else{
				$this->error_message = "数据类型不匹配";
			}
			return false;
		}

		if($rules['enum'] !== null){
			if(!in_array($data,$rules['enum'])){
				if($this->is_debug){
					$this->error_message = '数据值不匹配：'.$key;
				}else{
					$this->error_message = "数据值不匹配";
				}
				return false;
			}
		}

		return true;
	}
}
