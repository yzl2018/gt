<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\MailChannelsDispatch;
use App\Http\Toolkit\RESPONSE;
use App\Mail\MAIL;
use App\Models\CashCardsModel;
use App\Models\MailTypesConfigModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplyMailNoticeController extends ApiController
{

	use MailChannelsDispatch;
	/**
	 * 补发 购买充值卡成功邮件 和 激活充值卡成功邮件
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function supplySuccessMail(Request $request){

		$this->getUser();
		$mail_type = $request->input('mail_type');
		$card_id = $request->input('card_id');

        if($this->isCustomer()){
            $own_card = CashCardsModel::where('id',$card_id)->where('users_id',$this->user['id'])->first();
            if(empty($own_card)){
                return $this->message('You have no such permission.');
            }
        }
        else{
            if(!$request->has('operate_password')){
                return $this->message('Missing parameter');
            }
        }
        $operate_password = $request->input('operate_password');

		if(!in_array(strval($mail_type),['1','2'])){
			return $this->message('Mail type error');
		}

        if(!$this->isCustomer()){
            $user_id = UsersModel::where('email',$this->user['email'])->where('operate_password',$operate_password)->value('id');
            if($user_id != $this->user['id']){
                return $this->message($this->say('!034'));
            }
        }

		$card = CashCardsModel::where('id',$card_id)->first();
		if(empty($card)){
			return $this->message('Card id error');
		}

		$customer_email = UsersModel::where('id',$card->users_id)->value('email');

		if($mail_type == 1){//补发购买充值卡成功邮件

            if($card->use_status == 1){
                return $this->message('该充值卡已被使用！');
            }

            if($this->isCustomer()){
                $limit_times = config('system.mall.email_notice_limit');
                if($card->supply_email_times >= $limit_times){
                    return $this->message('抱歉，一张充值卡最多只能补发'.$limit_times.'次邮件通知');
                }
            }

//            $mail_type_code = $this->roundRobinArr([0xE021,0xE022,0xE023,0xE024],1);
//            //$mail_type_code = 0xE030;
//			$redis_key = $this->dispatchMailJob($mail_type_code,//使用配置文件派发邮件任务
//				[
//					'Vouchers'             => [
//						$card->card_no	=> $card->card_key
//					],
//					'Value'				   => $card->card_value,
//					'Currency'			   => $card->currency_type_code
//				],$customer_email,config('system.mall.send_card_key_mail_delay_seconds'));

			$delay_send_seconds = MailTypesConfigModel::where('code',MAIL::mail_supply_cards_info)->value('delay_send_seconds');
            $created_at = $card->created_at;
            $now_time = date($this->time_fmt);
            $allow_supply_time = date($this->time_fmt,strtotime($created_at)+$delay_send_seconds*2);

            if($allow_supply_time > $now_time){
				$seconds_time = strtotime($allow_supply_time) - strtotime($now_time);
				if($seconds_time >= 60){
					return $this->message($seconds_time.' 秒后才能补发该类邮件！');
				}
				else{
					$min_time = ceil($seconds_time/60);
					return $this->message($min_time.' 分钟后才能补发该类邮件！');
				}
			}

			$dispatch = $this->autoDispatchMailJob(MAIL::mail_supply_cards_info,//使用数据库配置派发邮件任务
				[
					'Vouchers'             => [
						$card->card_no	=> $card->card_key
					],
					'Value'				   => $card->card_value,
					'Currency'			   => $card->currency_type_code
				],$customer_email);
			if($dispatch['success']){
				$redis_key = $dispatch['message'];
			}
			else{
				$redis_key = null;
			}

			try{
				CashCardsModel::where('id',$card_id)->update(['mail_redis_key'=>$redis_key,'supply_email_times'=>++$card->supply_email_times]);
			}
			catch (\Exception $e){
				Log::error("Update cash cards email_redis_key",[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
			}

			if($redis_key){
				return $this->app_response(RESPONSE::SUCCESS,$this->say('!118'));
			}
			else{
				return $this->app_response(RESPONSE::SEND_FAIL,$this->say('!117'));
			}
		}

		if($mail_type == 2){//补发激活充值卡成功邮件

            if($card->use_status != 1){
                return $this->message('该充值卡未被使用！');
            }

            if($this->isCustomer()){
                $limit_times = config('system.mall.sms_notice_limit');
                if($card->supply_sms_times >= $limit_times){
                    return $this->message('抱歉，一张充值卡最多只能补发'.$limit_times.'次邮件通知');
                }
            }

//			$redis_key = $this->dispatchMailJob(MAIL::card_active_success,//使用配置文件派发邮件任务
//				[
//					'VoucherNo'     => $card->card_no,
//					'VoucherValue'  => $card->card_value,
//					'Currency'      => $card->currency_type_code
//				],$customer_email);

            $dispatch = $this->autoDispatchMailJob(MAIL::mail_supply_activation_info,
				[
					'VoucherNo'     => $card->card_no,
					'VoucherValue'  => $card->card_value,
					'Currency'      => $card->currency_type_code
				],$customer_email);
            if($dispatch['success']){
            	$redis_key = $dispatch['message'];
			}
			else{
            	$redis_key = null;
			}

			try{
				CashCardsModel::where('id',$card_id)->update(['sms_redis_key'=>$redis_key,'supply_sms_times'=>++$card->supply_sms_times]);
			}
			catch (\Exception $e){
				Log::error("Update cash cards sms_redis_key",[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
			}

			if($redis_key){
				return $this->app_response(RESPONSE::SUCCESS,$this->say('!118'));
			}
			else{
				return $this->app_response(RESPONSE::SEND_FAIL,$this->say('!117'));
			}
		}

		return $this->app_response(RESPONSE::WARNING,'Invalid mail type');

	}

}
