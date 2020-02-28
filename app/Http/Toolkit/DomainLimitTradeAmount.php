<?php
namespace App\Http\Toolkit;

trait DomainLimitTradeAmount
{

	/**
	 * 获取当前网址对应编码
	 *
	 * @param string $host
	 * @return mixed
	 */
	public function getThisSiteCode(string $host){

		$site_to_codes = config('system.domain.site_to_code');

		if(array_key_exists($host,$site_to_codes)){
			return $site_to_codes[$host];
		}

		return $site_to_codes['default'];

	}

	/**
	 * 获取编码对应网址
	 *
	 * @param string $site_code
	 * @return mixed
	 */
	public function getWebSiteOfCode(string $site_code){

		$code_to_sites = config('system.domain.code_to_site');
		if(array_key_exists($site_code,$code_to_sites)){
			return $code_to_sites[$site_code];
		}

		return $code_to_sites['default'];

	}

	/**
	 * 检验交易金额是否合法
	 *
	 * @param $host
	 * @param $total_amount
	 * @return array
	 */
	public function checkTradeTotalMoney($host,$total_amount){

		$site_code = $this->getThisSiteCode($host);
		$site_limit_trade_amount = config('system.domain.limit_trade_amount');
		$limit_amount = $site_limit_trade_amount['default'];

		if(array_key_exists($site_code,$site_limit_trade_amount)){
			$limit_amount = $site_limit_trade_amount[$site_code];
		}

		if($total_amount < $limit_amount){
			return ['fail'=>true,'message'=>'总交易金额不能少于'.$limit_amount.'元'];
		}

		return ['fail'=>false,'message'=>'success'];

	}

}