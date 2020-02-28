<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/5/8 16:58
 * +------------------------------------------------------------------------------
 */
namespace App\Http\Toolkit;
class MD5Sign
{
    /**
     * 密钥
     * @var string
     */
    private $securityKey = '';

    /**
     * MD5Sign constructor.
     * @param $sec
     */
    public function __construct($sec)
    {
        $this->securityKey = $sec;
    }

    /**
     * 签名
     * @param $params
     * @return string
     */
    public function Sign($params){
        $sign_msg = $this->sign_msg($params);
        $sign = strtoupper(md5($sign_msg).md5($this->securityKey));
        return $sign;
    }

    /**
     * 验签
     * @param $params
     * @param $sign
     * @return bool
     */
    public function Verify($params,$sign){
        $md5Sign = $this->Sign($params);
        if($md5Sign == $sign){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 作用：生成签名函数
     * @param $obj
     * @return string
     */
    private function sign_msg($obj){
        foreach($obj as $k => $v){
            if($v != '' && $k != 'SecurityKey'){
                $Parameters[$k] = $v;
            }
        }
        ksort($Parameters);
        return urldecode($this->formatParameter_map($Parameters));
    }

    /**
     * 作用：组合生成参数字符串
     * @param $para_map
     * @return string
     */
    private function formatParameter_map($para_map){
        $buff = '';
        ksort($para_map);
        foreach($para_map as $k => $v){
            if($v != null && $v != ''){
                if($buff == ''){
                    $buff .= $k.'='.$v;
                }else{
                    $buff .= '@'.$k.'='.$v;
                }
            }
        }
        return urlencode($buff);
    }

}