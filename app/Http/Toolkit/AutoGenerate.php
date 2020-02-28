<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/19 14:36
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Toolkit;


trait AutoGenerate
{

	/**
	 * 待生成的密码
	 *
	 * @var string
	 */
	private $password;

	/**
	 * 字符集
	 *
	 * @var array
	 */
	private $chars = [
		'a','A','0','b','B','1','c','C','2','d','D','3','e','E','4','f','F','5','g','G','6',
		'h','H','7','i','I','8','j','J','9','k','K','0','l','L','1','m','M','2','n','N','3',
		'o','O','4','p','P','5','q','Q','6','r','R','7','s','S','8','t','T','9','u','U','0',
		'v','V','1','w','W','3','x','X','5','y','Y','7','z','Z','9'
	];

	/**
	 * 密码类型
	 *
	 * @var array
	 */
	private $password_type = [
		0,//大小写混合
		1,//全部大写
		-1//全部小写
	];

	/**
	 * 获取毫秒数
	 *
	 * @return mixed
	 */
	protected function get_milliseconds(){
		return explode('.',microtime(true))[1];
	}

	/**
	 * 获取毫秒级时间戳
	 *
	 * @return float
	 */
	protected function get_micro_time(){
		list($msec, $sec) = explode(' ', microtime());
		return  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	}

	/**
	 * 生成编码
	 *
	 * @param $config
	 * @return string
	 */
	protected static function generate_code($config){

		$code = $config->prefix;

        if($config->prefix == 'U'){
            $code .= dechex(intval(config('system.mall.code')));
        }

		if($config->random_bits > 0){
			$code .= self::random_value($config->random_bits);
		}

		return $code.str_pad($config->latest_val,$config->code_bits,0,STR_PAD_LEFT);

	}

	/**
	 * 生成指定位数的随机数
	 *
	 * @param int $length
	 * @return int
	 */
	private static function random_value(int $length = 6){

		$min_rand = pow(10,$length-1) - 1;
		$max_rand = pow(10,$length) - 1;

		return mt_rand($min_rand,$max_rand);
	}

	/**
	 * 自动生成安全码
	 * @return string
	 */
	protected function CreateSafetyCode(){
		return md5(time().$this->create_password());
	}

    /**
     * 随机生成固定位数的数字验证码
     *
     * @param int $length
     * @return int
     */
    protected function generateNumericCode(int $length = 6){

        return mt_rand(
            pow(10,($length-1)),
            pow(10,$length)-1
        );

    }

	/**
	 * 自动生成随机密码
	 *
	 * @param int $length
	 * @param int $type
	 * @return string
	 */
	protected function create_password(int $length = 8,int $type = 0){
		if(!in_array($type,$this->password_type)){
			$type = 0;
		}

		$keys = array_rand($this->chars,$length);

		foreach ($keys as $key){
			$this->password .= $this->chars[$key];
		}

		if($type == 1){
			return strtoupper($this->password);
		}

		if($type == -1){
			return strtolower($this->password);
		}

		return $this->password;
	}

    /**
     * 生成简单加密签名
     *
     * @param array $params
     * @return string
     */
    protected function create_simple_sign(array $params){

        $security_key = config('system.mall.access_safety_code','xfbe*&VCYh38i4ht98v34');

        ksort($params);
        $keys_str = implode('#&%',array_keys($params));
        $values_str = implode('@$#',array_values($params));

        return hash_hmac('sha256',$keys_str.$values_str,$security_key);

    }

    /**
     * 验证简单数据签名
     *
     * @param array $params
     * @param string $sign
     * @return bool
     */
    protected function verify_simple_sign(array $params,string $sign){

        $simple_sign = $this->create_simple_sign($params);

        if($simple_sign == $sign){
            return true;
        }

        return false;

    }

    /**
     * 将数字转换成字母
     *
     * @param int $num
     * @return string
     */
    protected function number_to_chars(int $num){

        $chars_arr = [
            1=>'a',
            2=>'b',
            3=>'c',
            4=>'d',
            5=>'e',
            6=>'f',
            7=>'g',
            8=>'h',
            9=>'i',
            0=>'j'
        ];

        $nums_arr=explode("\r\n",chunk_split($num,1));
        $str = "";

        foreach($nums_arr as $n){
            if($n!=''){
                $str.=$chars_arr[$n];
            }
        }

        return $str;

    }

}
