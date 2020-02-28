<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/25 18:01
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Toolkit;


trait Message
{
	/**
	 * 语言类型编码
	 *
	 * @var null
	 */
	protected $language_type_code = null;
	
	/**
	 * 系统默认语言配置文件
	 *
	 * @var string
	 */
	protected $language_type_config = "system.mall.language";
	
	/**
	 * 系统语言信息配置文件
	 *
	 * @var string
	 */
	protected $language_message_config = "message.";
	
	/**
	 * 默认错误语言
	 *
	 * @var string
	 */
	protected $default_error_word = "message.!10001.";
	
	/**
	 * 信息配置的起始字符
	 *
	 * @var string
	 */
	protected $message_start_char = "!";
	
	/**
	 * 动态修改系统默认配置语言
	 *
	 * @param $language
	 */
	public function setLanguage($language){
		
		$this->language_type_code = strtoupper($language);
		
		if(in_array($this->language_type_code,['CN','EN'])){
			$sys_lang = config($this->language_type_config);
			
			if(strtoupper($language) !== $sys_lang){
				config([$this->language_type_config => strtoupper($language)]);
			}
		}
	}
	
	/**
	 * 输出系统提示语言
	 *
	 * @param $code_or_message
	 * @return \Illuminate\Config\Repository|mixed
	 */
	public function say($code_or_message){
	
		if(starts_with($code_or_message,$this->message_start_char)){
			
			if($this->language_type_code == null){
				$this->language_type_code = config($this->language_type_config);
			}
			
			$words = $this->language_message_config.$code_or_message.".".$this->language_type_code;
			
			$message = config($words);
			
			if($message == null){
				return config($this->default_error_word.$this->language_type_code);
			}
			
			return $message;
		}
		
		return $code_or_message;
	
	}
}