<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use Illuminate\Http\Request;

class DomainController extends ApiController
{

	/**
	 * 获取域名交易金额门槛设置
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getAllDomainTrainsLimit(Request $request){

		$domains = config('system.domain');

		$site_to_code = $domains['site_to_code'];

		$limit_trade_amount = $domains['limit_trade_amount'];

		$domain_limits = [];

		foreach ($site_to_code as $domain => $code){
			if(isset($limit_trade_amount[$code])){
				$domain_limits[$domain] = $limit_trade_amount[$code];
			}
			else{
				$domain_limits[$domain] = $limit_trade_amount['default'];
			}
		}
		
		$domain_limits['secondary_to_main']	= $domains['secondary_to_main'];

		return $this->app_response(RESPONSE::SUCCESS,'get domains trains limit success',$domain_limits);

	}

}