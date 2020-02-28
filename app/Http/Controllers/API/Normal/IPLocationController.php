<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/27 14:45
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Controllers\API\Normal;

use App\Http\Toolkit\CommunicateWithB;
use Illuminate\Http\Request;
use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\RESPONSE;

class IPLocationController
{
    use AppResponse,CommunicateWithB;

    /**
     * 126IP地址查询
     *
     * @var string
     * @return 'var lo="福建省", lc="厦门市"; var localAddress={city:"厦门市", province:"福建省"}'
     */
    private $ws126_ip = "http://ip.ws.126.net/ipquery?ip=";

    /**
     * 淘宝IP查询地址
     *
     * @var string
     * @return
    {
    "code":0,
    "data":{
    "ip":"120.36.254.31",
    "country":"中国",
    "area":"",
    "region":"福建",
    "city":"厦门",
    "county":"XX",
    "isp":"电信",
    "country_id":"CN",
    "area_id":"",
    "region_id":"350000",
    "city_id":"350200",
    "county_id":"xx",
    "isp_id":"100017"
    }
    }
     */
    private $taobao_ip = "http://ip.taobao.com/service/getIpInfo.php?ip=";

    /**
     * 百度IP查询地址
     *
     * @var string
     * @return
    {
        "address": "CN|福建|厦门|None|CHINANET|0|0",
        "content": {
            "address_detail": {
                "province": "福建省",
                "city": "厦门市",
                "district": "",
                "street": "",
                "street_number": "",
                "city_code": 194
            },
            "address": "福建省厦门市",
            "point": {
                "y": "2795265.28",
                "x": "13147407.51"
            }
        },
        "status": 0
    }
     */
    private $baidu_ip = "http://api.map.baidu.com/location/ip?ak=F454f8a5efe5e577997931cc01de3974&ip=";

    /**
     * 获取IP归属地信息
     *
     * @param Request $request
     * @return string
     */
    public function getIpAddress(Request $request){

        $ip = $this->getUserIp();
        if($request->has('ip') && filter_var($request->input('ip'),FILTER_VALIDATE_IP)){
            $ip = $request->input('ip');
        }
        if($ip == '127.0.0.1'){
            return "内网IP";
        }

        $resp = $this->http_get_B($this->baidu_ip.$ip);

        if(is_null(json_decode($resp))){
            goto taoBao;
        }
        return json_decode($resp,true);

        taoBao:
        $resp = $this->http_get_B($this->taobao_ip.$ip);
        if(is_null(json_decode($resp))){
            return "Unknown";
        }

        return json_decode($resp,true);

    }

    /**
     * 获取客户端访问IP所在地信息
     *
     * @param Request $request
     * @return mixed
     */
    public function getLocation(Request $request){

        $ip = $request->getClientIp();
        if($request->has('ip') && filter_var($request->input('ip'),FILTER_VALIDATE_IP)){
            $ip = $request->input('ip');
        }

        $location = geoip($ip)->toArray();
        return json_encode($location,JSON_UNESCAPED_UNICODE);

    }

    /**
     * 测试请求头
     *
     * @param Request $request
     * @return mixed
     */
    public function testHeader(Request $request){

        $url = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"";
        $headers = $request->headers->all();
        $data = [
            'content-type'	=> $headers['content-type'][0],
            'content'		=> $_POST
        ];
        return $this->app_response(RESPONSE::SUCCESS,'test request',$data);

        $auth_info = $request->header('Authorization');
        $str_length = mb_strlen($auth_info);
        $authorization = substr($auth_info,0,$str_length-10);
        $auth_str = substr($auth_info,$str_length-10);
        $request->headers->set('Authorization',$authorization);
        $auth = $request->header('Authorization');
        $data = [
            'auth'	=> $auth,
            'time'	=> $auth_str
        ];
        return $this->app_response(RESPONSE::SUCCESS,'header.Authorization',$data);

    }

    /**
     * 获取IP
     */
    private function getUserIp(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){

            $cip = $_SERVER["HTTP_CLIENT_IP"];

        }

        elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){

            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];

        }

        elseif(!empty($_SERVER["REMOTE_ADDR"])){

            $cip = $_SERVER["REMOTE_ADDR"];

        }

        else{

            $cip = "127.0.0.1";

        }
        return $cip;
    }
}
