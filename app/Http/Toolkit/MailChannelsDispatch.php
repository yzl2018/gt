<?php
namespace App\Http\Toolkit;

use App\Http\Controllers\API\Entity\MailableEntity;
use App\Jobs\SendMailJob;
use App\Models\MailChannelsGroupModel;
use App\Models\MailChannelsModel;
use App\Models\MailTypesConfigModel;
use App\Models\MailViewsModel;
use App\Models\SendMailLogModel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait MailChannelsDispatch
{

	use DispatchesJobs,DataValidator;

	/**
	 * 邮件数据信息
	 *
	 * @var array
	 */
	private $mail_information = [
		'mail_type_code'	=> '',
		'mail_data'			=> [],
		'customer_email'	=> '',
		'attach_path'		=> null
	];

	/**
	 * 邮件发送类型配置
	 *
	 * @var null
	 */
	private $mail_type_config = null;

	/**
	 * 邮件视图模板信息
	 *
	 * @var null
	 */
	private $mail_type_view = null;

	/**
	 * 邮局通道组
	 *
	 * @var null
	 */
	private $mail_channels_group = null;

	/**
	 * 当前使用的通道组
	 *
	 * @var null
	 */
	private $current_channels_group = null;

	/**
	 * 可用的通道编号
	 *
	 * @var null
	 */
	private $enabled_channel_code = null;

	/**
	 * 可用邮寄通道
	 *
	 * @var null
	 */
	private $mailable_channel = null;

	/**
	 * 邮件日志ID
	 *
	 * @var null
	 */
	private $mail_log_id = null;

	/**
	 * redis key
	 *
	 * @var null
	 */
	private $redis_key = null;

	/**
	 * 派发任务的响应信息
	 *
	 * @var array
	 */
	private $dispatch_response = [
		'success'	=> false,
		'message'	=> 'Unexpected'
	];

	/**
	 * 自动派发邮件队列任务
	 *
	 * @param string $mail_type_code
	 * @param array $mail_data
	 * @param string $customer_email
	 * @param integer $delay_seconds_add
	 * @param string|null $attach_path
	 * @return array
	 */
	protected function autoDispatchMailJob(string $mail_type_code,array $mail_data,string $customer_email,int $delay_seconds_add = 0,string $attach_path = null){

		$this->mail_information['mail_type_code'] = $mail_type_code;
		$this->mail_information['mail_data'] = $mail_data;
		$this->mail_information['customer_email'] = $customer_email;
		$this->mail_information['attach_path'] = $attach_path;

		if($this->checkForDispatch() == false){
			return $this->dispatch_response;
		}

		$this->newSendMailLog();
		$mail = new MailableEntity($this->mailable_channel,$this->mail_type_view,$this->mail_information['mail_data'],
			$this->mail_information['customer_email'],$this->mail_log_id,$this->mail_information['attach_path']);
		//TODO 派发邮件发送任务
		$delay_seconds = intval($this->mail_type_config->delay_send_seconds) + $delay_seconds_add;
		$this->redis_key = $this->dispatch((new SendMailJob($mail))->delay(now()->addSeconds($delay_seconds)));
		$this->updateSendMailLog();

		$this->dispatch_response = [
			'success'	=> true,
			'message'	=> $this->redis_key
		];

		return $this->dispatch_response;

	}
	
	/**
	 * 派发测试通道邮件
	 *
	 * @param $mail_channel
	 * @return array
	 */
	protected function testDispatchMailJob($mail_channel){

		$this->mailable_channel = $mail_channel;
		$this->mail_information['mail_type_code'] = 0xE301;
		$this->mail_information['mail_data'] = [
			'type_name'		   => '测试通道邮件',
			'code'             => $mail_channel->code,
			'expires_time'	   => date('Y-m-d H:i:s')
		];
		$this->mail_information['customer_email'] = 'td194672513@163.com';
		$this->mail_information['attach_path'] = null;

		$this->mail_type_view = MailViewsModel::where('id',1)->first();
		$this->newSendMailLog();
		$mail = new MailableEntity($this->mailable_channel,$this->mail_type_view,$this->mail_information['mail_data'],
			$this->mail_information['customer_email'],$this->mail_log_id,$this->mail_information['attach_path']);

		$this->redis_key = $this->dispatch((new SendMailJob($mail))->delay(now()->addSeconds(0)));
		$this->updateSendMailLog();

		$this->dispatch_response = [
			'success'	=> true,
			'message'	=> $this->redis_key
		];

		return $this->dispatch_response;
	}

	/**
	 * 生成邮件发送日志
	 */
	private function newSendMailLog(){

		$date_time = date('Y-m-d H:i:s');

		$mail_data_copy = $this->mail_information['mail_data'];
		if(isset($mail_data_copy['Vouchers'])){
			$mail_data_copy['Vouchers'] = $this->encryptVoucherKey($mail_data_copy['Vouchers']);
		}

		$mail_log = [
			'mail_type_code'	=> $this->mail_information['mail_type_code'],
			'mail_data'			=> json_encode($mail_data_copy),
			'mail_to'			=> $this->mail_information['customer_email'],
			'created_at'		=> $date_time,
			'updated_at'		=> $date_time
		];

		if(!empty($this->mail_information['attach_path'])){
			$mail_log['attach_path'] = $this->mail_information['attach_path'];
		}
		$this->mail_log_id = SendMailLogModel::insertGetId($mail_log);

	}

	/**
	 * 更新邮件发送日志
	 */
	private function updateSendMailLog(){

		if(is_string($this->redis_key)){
			$data['mail_channel'] = $this->mailable_channel->name;
			$data['dispatch_status'] = 1;
			$data['redis_key']		 = $this->redis_key;
			try{
				SendMailLogModel::where('id',$this->mail_log_id)->update($data);
			}
			catch(\Exception $e){
				Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
					'Update send mail log error' => $data,
				]);
			}
		}

		if(is_object($this->redis_key) || is_array($this->redis_key)){
			$this->redis_key = json_encode($this->redis_key);
		}

	}

	/**
	 * 派发任务前的检查准备工作
	 *
	 * @return bool
	 */
	private function checkForDispatch(){

		$this->mail_type_config = MailTypesConfigModel::where('code',$this->mail_information['mail_type_code'])->first();

		//检查该邮件发送类型是否存在
		if(empty($this->mail_type_config)){
			$this->dispatch_response['message'] = 'The mail type code is not exists.';
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				$this->dispatch_response['message'] => $this->mail_information['mail_type_code'],
			]);
			return false;
		}

		//检测该类邮件发送是否已被关闭
		if($this->mail_type_config->can_be_send == 0){
			$this->dispatch_response['message'] = 'This mail type has been closed.';
			return false;
		}

		$this->mail_type_view = MailViewsModel::where('id',$this->mail_type_config->view_id)->first();

		//检查邮件视图模板数据是否存在
		if(empty($this->mail_type_view)){
			$this->dispatch_response['message'] = 'The mail type view is not exists.';
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				$this->dispatch_response['message'] => $this->mail_type_config->view_id,
			]);
			return false;
		}

		//检查邮件数据参数是否匹配
		$parameters = json_decode($this->mail_type_view->parameters);
		foreach ($parameters as $index => $key){
			if(!array_key_exists($key,$this->mail_information['mail_data'])){
				$this->dispatch_response['message'] = 'The mail data is incorrect.';
				Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
					$this->dispatch_response['message'] => $this->mail_information['mail_data'],
				]);
				return false;
			}
		}

		//检查收件邮箱是否合法
		if($this->EmailValidator($this->mail_information['customer_email']) == false){
			$this->dispatch_response['message'] = 'The format of customer email is incorrect.';
			return false;
		}

		//检查通道组是否存在或可用 若通道组不可用则启用应急邮局通道
		$this->getEnabledMailChannel();
		$this->updateChannelsGroupInfo();
		$this->updateChannelInfo();

		return true;

	}

	/**
	 * 更新通道组信息
	 */
	private function updateChannelsGroupInfo(){

		if($this->enabled_channel_code != $this->mail_type_config->emergency_channel){//非应急通道
			if($this->current_channels_group != $this->mail_type_config->current_channels_group){
				//更新正在使用的通道组
				try{
					MailTypesConfigModel::where('code',$this->mail_information['mail_type_code'])->update([
						'current_channels_group'	=> $this->current_channels_group,
						'updated_at'				=> date('Y-m-d H:i:s')
					]);
				}
				catch(\Exception $e){
					Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
						'Update current_channels_group error' => $e->getMessage()
					]);
				}
			}
			if($this->enabled_channel_code != $this->mail_channels_group->using_channel) {
				//更新正在使用的通道
				try {
					MailChannelsGroupModel::where('code', $this->mail_channels_group['code'])->update([
						'using_channel' => $this->enabled_channel_code,
						'updated_at' => date('Y-m-d H:i:s')
					]);
				} catch (\Exception $e) {
					Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
						'Update using_channel error' => $e->getMessage()
					]);
				}
			}
		}
		else{
			if(!empty($this->mail_channels_group)){
				$include_channels = json_decode($this->mail_channels_group->include_channels,true);
				$channels_num = count($include_channels);//总通道数量
				$enabled_num = MailChannelsModel::whereIn('code',$include_channels)
					->where('enabled','!=',-1)
					->count();//查询可用通道数量
				if($enabled_num == 0 && $this->mail_channels_group->status != -1){//无可用通道
					try{
						MailChannelsGroupModel::where('id',$this->mail_channels_group->id)->update(['status'=>-1]);
					}
					catch(\Exception $e){
						Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
							'Update status error' => $e->getMessage()
						]);
					}
				}
				else{
					$full_num = MailChannelsModel::whereIn('code',$include_channels)
						->whereColumn('daily_send_limit','<=','daily_send_times')
						->count();//查询满额通道数量
					if($full_num < $enabled_num && $this->mail_channels_group->status != 0){
						//通道组内有可用通道未满额
						try{
							MailChannelsGroupModel::where('id',$this->mail_channels_group->id)->update(['status'=>0]);
						}
						catch(\Exception $e){
							Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
								'Update status error' => $e->getMessage()
							]);
						}
					}
					else{
						if($this->mail_channels_group->status != 1){
							//通道组内全部可用通道都满额
							try{
								MailChannelsGroupModel::where('id',$this->mail_channels_group->id)->update(['status'=>1]);
							}
							catch(\Exception $e){
								Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
									'Update status error' => $e->getMessage()
								]);
							}
						}
					}
				}
			}
		}

	}

	/**
	 * 更新通道使用信息
	 */
	private function updateChannelInfo(){

		$this->mailable_channel = MailChannelsModel::where('code',$this->enabled_channel_code)->first();
		$update_data = [
			'send_total'	=> $this->mailable_channel->send_total + 1,
			'updated_at'	=> date('Y-m-d H:i:s')
		];
		$today = date('Y-m-d');
		if($today != $this->mailable_channel->send_date){
			$update_data['send_date'] = $today;
			$update_data['daily_send_times'] = 1;
		}
		else{
			$update_data['daily_send_times'] = $this->mailable_channel->daily_send_times + 1;
		}
		try{
			MailChannelsModel::where('code',$this->enabled_channel_code)->update($update_data);
		}
		catch(\Exception $e){
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				'Update mail channel info error' => $e->getMessage()
			]);
		}

	}

	/**
	 * 获取可用的邮局通道
	 */
	private function getEnabledMailChannel(){

		$this->current_channels_group = $this->mail_type_config->current_channels_group;

		do{
			$this->mail_channels_group = MailChannelsGroupModel::where('code',$this->current_channels_group)->first();
			if(!empty($this->mail_channels_group) && $this->mail_channels_group->status == 0){
				$this->enabled_channel_code = $this->findEnabledChannel($this->current_channels_group);

				if($this->enabled_channel_code != null){
					//如若在某一通道组里找到了可用的通道，则直接跳出循环
					break;
				}
			}

			$this->current_channels_group = $this->findNextChannelsGroup();
			if($this->current_channels_group == null){
				//如若没有可用的通道组，则使用应急通道
				$this->enabled_channel_code = $this->mail_type_config->emergency_channel;
				break;
			}

		}while(empty($this->mail_channels_group) || $this->mail_channels_group->status != 0);

	}

	/**
	 * 查找下一个通道组
	 *
	 * @return string/null
	 */
	private function findNextChannelsGroup(){

		$prepare_channels_groups = json_decode($this->mail_type_config->prepare_channels_groups);

		$group_num = count($prepare_channels_groups);
		$index = array_search($this->mail_type_config->current_channels_group,$prepare_channels_groups);
		$next = $index + 1;
		if($next < $group_num){
			return $prepare_channels_groups[$next];
		}

		return null;
	}

	/**
	 * 找出当前通道组内可用的通道
	 *
	 * @param string $group_code
	 * @return mixed|null
	 */
	private function findEnabledChannel(string $group_code){

		$group_info = MailChannelsGroupModel::where('code',$group_code)->first();

		if(empty($group_info)){
			return null;
		}

		$include_channels = json_decode($group_info->include_channels);
		if(count($include_channels) == 0){//该通道组未配置邮局通道
			return null;
		}

		$channels_arr = MailChannelsModel::whereIn('code',$include_channels)
						->whereIn('enabled',[0,1])
						->whereColumn('daily_send_limit','>','daily_send_times')
						->pluck('code')->toArray();

		if(count($channels_arr) == 0){//该通道组内无可用的通道
			return null;
		}

		return $this->pollingArrayAlgo($channels_arr,$group_info->repeat_times);

	}

	/**
	 * 轮询数组算法，按指定重复次数轮询数组内的元素
	 *
	 * @param array $robin_arr	被轮询的数组
	 * @param int $repeat_times 单个元素一次轮询重复调用次数
	 * @return mixed
	 */
	private function pollingArrayAlgo(array $robin_arr,$repeat_times = 1){

		$length = count($robin_arr);
		if($length == 0){
			return null;
		}
		if($length == 1){
			return $robin_arr[0];
		}

		$robin_key = md5(json_encode($robin_arr));

		$pointer = Cache::store('redis')->rememberForever($robin_key,function(){
			return [
				'index'	=> 0,
				'times'	=> 0
			];
		});

		if($pointer['times'] >= $repeat_times){
			if($pointer['index'] >= $length-1){
				$pointer['index'] = 0;
			}
			else{
				$pointer['index']++;
			}
			$pointer['times'] = 1;
		}
		else{
			$pointer['times']++;
		}


		Cache::store('redis')->forever($robin_key,$pointer);

		return $robin_arr[$pointer['index']];

	}

}