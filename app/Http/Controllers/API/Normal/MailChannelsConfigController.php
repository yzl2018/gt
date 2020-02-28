<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\Entity\MailableFrom;
use App\Http\Controllers\API\Entity\MailableTo;
use App\Http\Controllers\API\Entity\MyMailable;
use App\Http\Toolkit\DataValidator;
use App\Http\Toolkit\RESPONSE;
use App\Models\MailChannelsGroupModel;
use App\Models\MailChannelsModel;
use App\Models\MailTypesConfigModel;
use App\Models\MailViewsModel;
use Illuminate\Http\Request;

class MailChannelsConfigController extends ApiController
{

	use DataValidator;
	/**
	 * 获取所有邮局通道
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showAllMailChannels(Request $request){

		$list = MailChannelsModel::all();

		return $this->app_response(RESPONSE::SUCCESS,'get mail channels success',$list);

	}

	/**
	 * 获取所有邮局通道组
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showAllMailChannelsGroups(Request $request){

		$list = MailChannelsGroupModel::all();

		return $this->app_response(RESPONSE::SUCCESS,'get mail channels groups success',$list);

	}

	/**
	 * 获取所有邮件视图
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showAllMailViews(Request $request){

		$list = MailViewsModel::all();

		return $this->app_response(RESPONSE::SUCCESS,'get mail views success',$list);

	}

	/**
	 * 获取所有邮件发送类型配置信息
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showAllMailTypesConfig(Request $request){

		$list = MailTypesConfigModel::all();

		return $this->app_response(RESPONSE::SUCCESS,'get mail types config success',$list);

	}

	/**
	 * 获取所有通道的编码名称
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getChannelsCodeName(Request $request){

		$list = MailChannelsModel::pluck('name','code');

		return $this->app_response(RESPONSE::SUCCESS,'get all channels code name success',$list);

	}

	/**
	 * 获取通道组编码所对应包含的通道
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getChannelsOfGroupCode(Request $request){

		$list = MailChannelsGroupModel::pluck('include_channels','code');
		foreach ($list as $code => $channels){
			$list[$code] = json_decode($channels,true);
		}

		return $this->app_response(RESPONSE::SUCCESS,'get include channels of group code success',$list);

	}

	/**
	 * 获取通道的驱动基本信息的路由
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getBaseDriverInfo(Request $request){

		$channel_conf = config('system.mail.channel');
		$channel_info = [
			'driver'	=> $channel_conf['driver'],
			'encryption'	=> $channel_conf['encryption']
		];

		return $this->app_response(RESPONSE::SUCCESS,'get channel driver info success',$channel_info);

	}

	/**
	 * 创建邮局通道
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function createMailChannel(Request $request){

		$permission = $this->authOperatePermission();
		if($permission['authorize'] == false){
			return $this->message($permission['message']);
		}

		//检测通道编码合法性
		$code = $request->input('code');
		if(strlen($code) < 4){
			return $this->message('The channel code is too short.');
		}
		if(strlen($code) > 40){
			return $this->message('The channel code is too long.');
		}
		$count = MailChannelsModel::where('code',$code)->count();
		if($count > 0){
			return $this->message('The channel code is exists.');
		}

		//检测通道名称的合法性
		$name = $request->input('name');
		if(strlen($name) < 4){
			return $this->message('The channel name is too short.');
		}
		if(strlen($name) > 60){
			return $this->message('The channel name is too long.');
		}
		$count = MailChannelsModel::where('name',$name)->count();
		if($count > 0){
			return $this->message('The channel name is exists.');
		}

		//检测邮件驱动参数是否合法
		$driver = $request->input('driver');
		$enabled_drivers = config('system.mail.channel.driver');
		if(!array_key_exists($driver,$enabled_drivers)){
			return $this->message('Invalid driver');
		}

		//检测邮局主机的合法性
		$host = $request->input('host');
		if(!starts_with($host,$driver)){
			return $this->message('Invalid host');
		}
		if(!ends_with($host,'.com')){
			return $this->message('Invalid host');
		}
		if(strlen($host) < 10){
			return $this->message('The host is too short');
		}
		if(strlen($host) > 60){
			return $this->message('The host is too long');
		}

		//检测邮局端口的合法性
		$port = $request->input('port');
		if(!in_array($port,$enabled_drivers[$driver])){
			return $this->message('Invalid port');
		}

		//检测加密协议的合法性
		$encryption = $request->input('encryption');
		$enabled_enc = config('system.mail.channel.encryption');
		if(!in_array($encryption,$enabled_enc)){
			return $this->message('Invalid encryption.');
		}

		//检测用户名的合法性
		$username = $request->input('username');
		if(strlen($username) < 8){
			return $this->message('The username is too short');
		}
		if(strlen($username) > 100){
			return $this->message('The username is too long.');
		}
		if($this->EmailValidator($username) == false){
			return $this->message('Invalid username');
		}

		//检测密码的合法性
		$password = $request->input('password');
		if(strlen($password) < 6){
			return $this->message('The password is too short.');
		}
		if(strlen($password) > 100){
			return $this->message('The password is too long.');
		}

		//检测每日邮件发送数量参数
		$daily_send_limit = $request->input('daily_send_limit');
		if($daily_send_limit < 10){
			return $this->message('The daily_send_limit can not less than 10');
		}

		$queue_key = $request->input('queue_key');
		if(strlen($queue_key) < 6){
			return $this->message('The queue key is too short.');
		}
		if(strlen($queue_key) > 150){
			return $this->message('The queue key is too long.');
		}

		$params = [
			'code'				=> $code,
			'name'				=> $name,
			'driver'			=> $driver,
			'host'				=> $host,
			'port'				=> $port,
			'encryption'		=> $encryption,
			'username'			=> $username,
			'password'			=> $password,
			'daily_send_limit'	=> $daily_send_limit,
			'enabled'			=> -1,
			'queue_key'			=> $queue_key,
			'created_at'		=> date($this->time_fmt)
		];
		$is_ignore = $request->input('is_ignore');
		if($is_ignore == '1'){
			$params['stream'] = json_encode(config('system.mail.channel.stream'));
		}

		$id = MailChannelsModel::insertGetId($params);
		if($id > 0){
			return $this->app_response(RESPONSE::SUCCESS,'SUCCESS');
		}

		return $this->app_response(RESPONSE::NEW_FAIL,'Create the mail channel fail.');

	}

	/**
	 * 更新邮局通道
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function updateMailChannel(Request $request){

		$permission = $this->authOperatePermission();
		if($permission['authorize'] == false){
			return $this->message($permission['message']);
		}

		$code = $request->input('code');
		$channel = MailChannelsModel::where('code',$code)->first();
		if(empty($channel)){
			return $this->message('The mail channel of this code is not exists.');
		}

		$params = [];

		if($request->has('name') && $channel->name != $request->input('name')){
			$name = $request->input('name');
			if(!is_string($name)){
				return $this->message('Data type error of name.');
			}
			if(strlen($name) > 60){
				return $this->message('The channel name is too long.');
			}
			if($channel->name != $name){
				$count = MailChannelsModel::where('name',$name)->count();
				if($count > 0){
					return $this->message('The channel name is exists.');
				}
				$params['name'] = $name;
			}
		}

		$driver = $channel->driver;
		$enabled_drivers = config('system.mail.channel.driver');
		if($request->has('driver') && $channel->driver != $request->input('driver')){
			$driver = $request->input('driver');
			if(!is_string($driver)){
				return $this->message('Data type error of driver.');
			}
			if(!array_key_exists($driver,$enabled_drivers)){
				return $this->message('Invalid driver');
			}
			$params['driver'] = $driver;
		}

		if($request->has('host') && $channel->host != $request->input('host')){
			$host = $request->input('host');
			if(!is_string($host)){
				return $this->message('Data type error of host.');
			}
			if(!starts_with($host,$driver)){
				return $this->message('Invalid host');
			}
			if(!ends_with($host,'.com')){
				return $this->message('Invalid host');
			}
			if(strlen($host) < 10){
				return $this->message('The host is too short');
			}
			if(strlen($host) > 60){
				return $this->message('The host is too long');
			}
			$params['host'] = $host;
		}

		if($request->has('port') && $channel->port != $request->input('port')){
			$port = $request->input('port');
			if(!is_string($port)){
				return $this->message('Data type error of port.');
			}
			if(!in_array($port,$enabled_drivers[$driver])){
				return $this->message('Invalid port');
			}
			$params['port'] = $port;
		}

		if($request->has('encryption') && $channel->encryption != $request->input('encryption')){
			$encryption = $request->input('encryption');
			$enabled_enc = config('system.mail.channel.encryption');
			if(!in_array($encryption,$enabled_enc)){
				return $this->message('Invalid encryption.');
			}
			$params['encryption'] = $encryption;
		}

		if($request->has('username') && $channel->username != $request->input('username')){
			$username = $request->input('username');
			if(!is_string($username)){
				return $this->message('Data type error of username.');
			}
			if(strlen($username) < 8){
				return $this->message('The username is too short');
			}
			if(strlen($username) > 100){
				return $this->message('The username is too long.');
			}
			if($this->EmailValidator($username) == false){
				return $this->message('Invalid username');
			}
			$params['username'] = $username;
		}

		if($request->has('password') && $channel->password != $request->input('password')){
			$password = $request->input('password');
			if(!is_string($password)){
				return $this->message('Data type error of password.');
			}
			if(strlen($password) < 6){
				return $this->message('The password is too short.');
			}
			if(strlen($password) > 100){
				return $this->message('The password is too long.');
			}
			$params['password'] = $password;
		}

		if($request->has('is_ignore')){
			$is_ignore = $request->input('is_ignore');
			if($is_ignore == '1'){
				$stream = json_encode(config('system.mail.channel.stream'));
				if($channel->stream != $stream){
					$params['stream'] = $stream;
				}
			}
			if($is_ignore == '0'){
				$stream = '';
				if($channel->stream != $stream){
					$params['stream'] = $stream;
				}
			}
		}

		if($request->has('daily_send_limit') && $channel->daily_send_limit != $request->input('daily_send_limit')){
			$daily_send_limit = $request->input('daily_send_limit');
			if(!is_int($daily_send_limit)){
				return $this->message('Data type error of daily_send_limit.');
			}
			if($daily_send_limit < 10){
				return $this->message('The daily_send_limit can not less than 10');
			}
			$params['daily_send_limit'] = $daily_send_limit;
		}

		if($request->has('queue_key') && $channel->queue_key != $request->input('queue_key')){
			$queue_key = $request->input('queue_key');
			if(!is_string($queue_key)){
				return $this->message('Data type error of queue_key.');
			}
			if(strlen($queue_key) < 6){
				return $this->message('The queue key is too short.');
			}
			if(strlen($queue_key) > 150){
				return $this->message('The queue key is too long.');
			}
			$params['queue_key'] = $queue_key;
		}

		if(count($params) == 0){
			return $this->message('No valid updatable data.');
		}

		$params['updated_at'] = date($this->time_fmt);
		$is_update = MailChannelsModel::where('code',$code)->update($params);
		if($is_update){
			return $this->app_response(RESPONSE::SUCCESS,'SUCCESS');
		}

		return $this->app_response(RESPONSE::WARNING,'Update the mail channel info fail');

	}

	/**
	 * 切换邮局通道的使用状态
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function toggleMailChannelStatus(Request $request){

		$permission = $this->authOperatePermission();
		if($permission['authorize'] == false){
			return $this->message($permission['message']);
		}

		$code = $request->input('code');
		$current_enabled = MailChannelsModel::where('code',$code)->value('enabled');
		if($current_enabled === null){
			return $this->message('The mail channel of this code is not exists.');
		}

		$enabled = $request->input('status');
		if($current_enabled == $enabled){
			return $this->message('Current status is '.$enabled);
		}

		$is_update = MailChannelsModel::where('code',$code)->update(['enabled'=>$enabled]);
		if($is_update){
			return $this->app_response(RESPONSE::SUCCESS,'SUCCESS');
		}

		return $this->app_response(RESPONSE::WARNING,'Update the mail channel status fail');

	}

	/**
	 * 创建邮局通道组
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function newMailChannelsGroup(Request $request){

		$permission = $this->authOperatePermission();
		if($permission['authorize'] == false){
			return $this->message($permission['message']);
		}

		//检测通道组编码合法性
		$code = $request->input('code');
		if(strlen($code) < 4){
			return $this->message('The channel group code is too short.');
		}
		if(strlen($code) > 40){
			return $this->message('The channel group code is too long.');
		}
		$count = MailChannelsGroupModel::where('code',$code)->count();
		if($count > 0){
			return $this->message('The channel group code is exists.');
		}

		//检测通道组名称的合法性
		$name = $request->input('name');
		if(strlen($name) < 4){
			return $this->message('The channel group name is too short.');
		}
		if(strlen($name) > 100){
			return $this->message('The channel group name is too long.');
		}

		$include_channels = $request->input('include_channels');
		if(count($include_channels) == 0){
			return $this->message('The include channels can not be empty');
		}

		$repeat_times = $request->input('repeat_times');
		if($repeat_times < 1){
			return $this->message('Invalid repeat times');
		}

		$using_channel = $include_channels[0];
		if($request->has('using_channel')){
			$using_channel = $request->input('using_channel');
			if(!is_string($using_channel)){
				return $this->message('Data type error of using channel');
			}
			if(!in_array($using_channel,$include_channels)){
				return $this->message('Invalid using channel');
			}
		}

		$params = [
			'code'	=> $code,
			'name'	=> $name,
			'include_channels'	=> json_encode($include_channels),
			'repeat_times'	=> $repeat_times,
			'using_channel'	=> $using_channel,
			'created_at'	=> date($this->time_fmt)
		];

		$id = MailChannelsGroupModel::insertGetId($params);
		if($id > 0){
			return $this->app_response(RESPONSE::SUCCESS,'SUCCESS');
		}

		return $this->app_response(RESPONSE::WARNING,'Create the mail channel group fail');

	}

	/**
	 * 更新邮局通道组
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function updateMailChannelsGroup(Request $request){

		$permission = $this->authOperatePermission();
		if($permission['authorize'] == false){
			return $this->message($permission['message']);
		}

		//检测通道组编码合法性
		$code = $request->input('code');
		if(strlen($code) < 4){
			return $this->message('The channel group code is too short.');
		}
		if(strlen($code) > 40){
			return $this->message('The channel group code is too long.');
		}
		$channel_group = MailChannelsGroupModel::where('code',$code)->first();
		if(empty($channel_group)){
			return $this->message('The channel group code is not exists.');
		}

		$params = [];

		//检测通道组名称的合法性
		if($request->has('name') && $channel_group->name != $request->input('name')){
			$name = $request->input('name');
			if(!is_string($name)){
				return $this->message('Data type error of name.');
			}
			if(strlen($name) < 4){
				return $this->message('The channel group name is too short.');
			}
			if(strlen($name) > 100){
				return $this->message('The channel group name is too long.');
			}
			$params['name'] = $name;
		}

		$include_channels = json_decode($channel_group->include_channels,true);
		if($request->has('include_channels') && $channel_group->include_channels != $request->input('include_channels')){
			$include_channels = $request->input('include_channels');
			if(!is_array($include_channels)){
				return $this->message('Data type error of include_channels.');
			}
			if(count($include_channels) == 0){
				return $this->message('The include channels can not be empty');
			}
			$params['include_channels'] = json_encode($include_channels);
		}

		if($request->has('repeat_times') && $channel_group->repeat_times != $request->input('repeat_times')){
			$repeat_times = $request->input('repeat_times');
			if(!is_int($repeat_times)){
				return $this->message('Data type error of repeat_times.');
			}
			if($repeat_times < 1){
				return $this->message('Invalid repeat times');
			}
			$params['repeat_times'] = $repeat_times;
		}

		if($request->has('using_channel') && $channel_group->using_channel != $request->input('using_channel')){
			$using_channel = $request->input('using_channel');
			if(!in_array($using_channel,$include_channels)){
				return $this->message('Invalid using channel.');
			}
		}

		if(count($params) == 0){
			return $this->message('No valid updatable data.');
		}

		$is_update = MailChannelsGroupModel::where('code',$code)->update($params);
		if($is_update){
			return $this->app_response(RESPONSE::SUCCESS,'SUCCESS');
		}

		return $this->app_response(RESPONSE::WARNING,'Update the mail channel group fail');

	}

	/**
	 * 配置邮件视图和发送通道组
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function configMailTypes(Request $request){

		$permission = $this->authOperatePermission();
		if($permission['authorize'] == false){
			return $this->message($permission['message']);
		}

		$code = $request->input('code');
		$mail_type = MailTypesConfigModel::where('code',$code)->first();
		if(empty($mail_type)){
			return $this->message('Invalid type code');
		}
		$prepare_channels_groups = json_decode($mail_type->prepare_channels_groups,true);

		$params = [];

		if($request->has('name') && $mail_type->name != $request->input('name')){
			$name = $request->input('name');
			if(!is_string($name)){
				return $this->message('Invalid name');
			}
			if(strlen($name) < 4){
				return $this->message('The mail type name is too short.');
			}
			if(strlen($name) > 100){
				return $this->message('The mail type name is too long.');
			}
			$params['name'] = $name;
		}

        if($request->has('prepare_channels_groups') && $prepare_channels_groups != $request->input('prepare_channels_groups')){
            $prepare_channels_groups = $request->input('prepare_channels_groups');
            if(is_object($prepare_channels_groups) || is_array($prepare_channels_groups)){
                $params['prepare_channels_groups'] = json_encode($prepare_channels_groups);
            }
            else{
                $params['prepare_channels_groups'] = $prepare_channels_groups;
            }
        }

		if(!in_array($mail_type->current_channels_group,$prepare_channels_groups)){
			$params['current_channels_group'] = $prepare_channels_groups[0];
		}

		if($request->has('current_channels_group') && $mail_type->current_channels_group != $request->input('current_channels_group')){
			$current_channels_group = $request->input('current_channels_group');
			if(!is_string($current_channels_group)){
				return $this->message('Invalid current channels group');
			}
			if(!in_array($current_channels_group,$prepare_channels_groups)){
				return $this->message('Invalid current channels group');
			}
			$params['current_channels_group'] = $current_channels_group;
		}

		if($request->has('emergency_channel') && $mail_type->emergency_channel != $request->input('emergency_channel')){
			$emergency_channel = $request->input('emergency_channel');
			if(!is_string($emergency_channel)){
				return $this->message('Invalid emergency channel');
			}
			$enabled = MailChannelsModel::where('code',$emergency_channel)->value('enabled');
			if(empty($enabled)){
				return $this->message('Invalid emergency channel');
			}
			if(!in_array($enabled,[0,1])){
				return $this->message('这个应急通道必须在备用通道组所包含通道之外，且必须可用的邮局通道');
			}
			$list = MailChannelsGroupModel::whereIn('code',$prepare_channels_groups)->pluck('include_channels');
			foreach ($list as $index => $channels){
				$include_channels = json_decode($channels,true);
				if(in_array($emergency_channel,$include_channels)){
					return $this->message('这个应急通道必须在备用通道组所包含通道之外，且必须可用的邮局通道');
				}
			}
			$params['emergency_channel'] = $emergency_channel;
		}

		if($request->has('delay_send_seconds') && $mail_type->delay_send_seconds != $request->input('delay_send_seconds')){
			$delay_send_seconds = $request->input('delay_send_seconds');
			if(preg_match('/^\d+$/',$delay_send_seconds) == false){//非负整数
				return $this->message('Invalid delay send seconds.');
			}
			$max_delay_seconds = config('system.mail.max_delay_seconds');
			if($delay_send_seconds > $max_delay_seconds){
				return $this->message('The mail sent delay seconds can not large than '.$max_delay_seconds);
			}
			$params['delay_send_seconds'] = $delay_send_seconds;
		}

		if(count($params) == 0){
			return $this->message('No valid updatable data.');
		}

		$is_update = MailTypesConfigModel::where('code',$code)->update($params);
		if($is_update){
			return $this->app_response(RESPONSE::SUCCESS,'SUCCESS');
		}

		return $this->app_response(RESPONSE::WARNING,'Update the mail type config fail');

	}

	/**
	 *
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function testMailChannel(Request $request){

		$permission = $this->authOperatePermission();
		if($permission['authorize'] == false){
			return $this->message($permission['message']);
		}

		$code = $request->input('channel_code');
		$receive_email = $request->input('receive_email');

		if($this->EmailValidator($receive_email) == false){
			return $this->message('您输入的邮箱格式不正确');
		}

		$mail_channel = MailChannelsModel::where('code',$code)->first();
		if(empty($mail_channel)){
			return $this->message('The channel of this code is not exists.');
		}

		$mail_from = new MailableFrom($mail_channel);
		$mail_to = new MailableTo($receive_email,'emails.testchannel',[
			'subject'	=> '测试邮局通道',
			'channel_name'	=> $mail_channel->name,
			'driver'		=> $mail_channel->driver,
			'host'		=> $mail_channel->host,
			'port'		=> $mail_channel->port,
			'send_time'	=> date($this->time_fmt)
		],'测试邮局通道');

		$mail = new MyMailable($mail_from,$mail_to);

		try{
			$send_status = $mail->send();
			$message = 'Test Send Success';
		}
		catch(\Exception $e){
			$send_status = -1;
			$message = explode(', with message',$e->getMessage())[0];
		}

		if($send_status == 1){
			return $this->app_response(RESPONSE::SUCCESS,$message);
		}

		return $this->message($message);

	}

	/**
	 * 认证授权用户操作
	 *
	 * @return array
	 */
	private function authOperatePermission(){

		$this->getUser();
		$auth_operate_users = config('system.mail.auth_operate_users');
		if(in_array($this->user['email'],$auth_operate_users)){
			return [
				'authorize'	=> true,
				'message'	=> 'Authorized access'
			];
		}

		return [
			'authorize'	=> false,
			'message'	=> 'You do not have this permission to operate'
		];

	}

}
