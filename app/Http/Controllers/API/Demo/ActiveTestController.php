<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2019/1/21 16:27
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Controllers\API\Demo;

use App\Http\Toolkit\CurlHttpRequest;
use Illuminate\Http\Request;
use App\Http\Toolkit\EncryptTools;

class ActiveTestController
{
	use CurlHttpRequest;
	/**
	 * 当前网站地址
	 *
	 * @var string
	 */
	protected $web_site;

	/**
	 * POST 提交过来的数据
	 *
	 * @var array
	 */
	private $post_data = [
		'voucherNo'		=> '',
		'voucherKey'	=> '',
		'voucherValue'	=> '',
		'currency'		=> ''
	];

	/**
	 * 测试商户
	 *
	 * @var array
	 */
	private $test_merchant = [
		'mer_code'	=> '10021',
		'mer_id'	=> '180017',
		'mer_key'	=> '7608958e60c7d1dc1e1e95384fde214e',
		'mer_salt'	=> 'Acd3FgG8J9qr8UX7z9'
	];

	/**
	 * 激活请求地址
	 *
	 * @var string
	 */
	private $submit_uri;

	public function __construct()
	{
		if(env('APP_LIVE')){
			$this->web_site = config('system.mall.live_url');
		}
		else{
			$this->web_site = config('system.mall.demo_url');
		}
		$this->submit_uri = $this->web_site.config('system.mall.do_active');
	}

	/**
	 * 充值卡激活测试接口
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function postActive(){

		return view('demo.post',[
			'active_uri'=>$this->web_site.config('system.mall.test_active_uri')
		]);
		
	}

	/**
	 * 激活充值卡测试接口
	 *
	 * @param Request $request
	 */
	public function doActive(Request $request){

		$params = $request->input();

		if(empty($params)){
			exit('Bad request');
		}

		foreach ($this->post_data as $key => $value){
			if(!array_key_exists($key,$params)){
				exit('Invalid request');
			}
			$this->post_data[$key] = $params[$key];
		}

		$tool = new EncryptTools($this->test_merchant['mer_key'],$this->test_merchant['mer_salt']);

		$active_no = "demotest".date('YmdHis');

		$native = array(
			'MerchantCode'  => $this->test_merchant['mer_code'],//商户号
			'MerchantId'    => $this->test_merchant['mer_id'],//商户ID
			'CRMOrderNo'	=> $active_no,//CRM 激活订单号
			'VoucherNo'     => $this->post_data['voucherNo'],//兑换码
			'VoucherKey'    => $this->post_data['voucherKey'],//兑换密钥
			'VoucherValue'  => $this->post_data['voucherValue'],   //兑换面值
			'Currency'      => $this->post_data['currency'], //货币类型
			'SignType'      => 'MD5'//签名类型
		);

		$parameters = $tool->createRequestData($native);

		$resp = $this->http_post_json_data($this->submit_uri,$parameters);

		$this->pr_array($resp);

		if(is_null(json_decode($resp))){
			exit($resp);
		}else{
			$res = json_decode($resp,true);
			$this->pr_array($res);
			if($tool->verify($res)){
				$return = $tool->getResponseData();
				$this->pr_array($return);
				if($return['Code'] == '00'){//兑换成功
					echo '兑换成功';
					//Success Return Example：{code:'00',VoucherNo:'Du7C9iw8veF1Lw3',VoucherValue:'1000',Currency:'USD',message:'Active Success'}
					//TODO Something of success
				}else{
					echo '兑换失败';
					//TODO Something of fail
				}
			}else{
				exit("Verify fail");
			}
		}

	}

}