<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailChannelsSeeder extends Seeder
{

	/**
	 * 初始化数据
	 *
	 * @var array
	 */
	private static $init_data = [

		'benefitpick'	=> [
			'code'		=> 'ali001',
			'name'		=> 'benefitpick',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'service@benefitpick.com',
			'password' => '8577848334af684!',
			'daily_send_limit'	=> 200,
			'queue_key'		=> 'benefitpick'//该邮局通道所使用的队列
		],

		'crmcloud'	=> [
			'code'		=> 'ali002',
			'name'		=> 'crmcloud',
			'driver'	=> 'smtp',
			'host' => "smtp.crmcloudtech.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'service@crmcloudtech.com',
			'password' => '5e6dc09e0cdfbc8!',
			'stream'	=> [
				'ssl'	=> [//设置ssl证书协议认证方式  false表示忽略
					'verify_peer'		=> false,
					'verify_peer_name'	=> false
				]
			],
			'daily_send_limit'	=> 200,
			'queue_key'		=> 'crmcloud'//该邮局通道所使用的队列
		],

		'lighthouse'	=> [
			'code'		=> 'ali003',
			'name'		=> 'lighthouse',
			'driver'	=> 'smtp',
			'host' => "smtp.lighthousemy.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'dmarc@lighthousemy.com',
			'password' => 'ad534e4efad592b!',
			'stream'	=> [
				'ssl'	=> [//设置ssl证书协议认证方式  false表示忽略
					'verify_peer'		=> false,
					'verify_peer_name'	=> false
				]
			],
			'daily_send_limit'	=> 200,
			'queue_key'		=> 'lighthouse'//该邮局通道所使用的队列
		],

		'fxpointcard'	=> [
			'code'		=> 'ali004',
			'name'		=> 'fxpointcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'service@fxpointcard.com',
			'password' => 'Y17182112867.',
			'daily_send_limit'	=> 100,
			'enabled'		=> -1,
			'queue_key'		=> 'fxpointcard'//该邮局通道所使用的队列
		],

		'fxrefillcard'	=> [
			'code'		=> 'ali005',
			'name'		=> 'fxrefillcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'service@fxrefillcard.com',
			'password' => 'Y17182111628.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'fxrefillcard'//该邮局通道所使用的队列
		],

		'forexacard'	=> [
			'code'		=> 'ali006',
			'name'		=> 'forexacard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'service@forexacard.com',
			'password' => 'Y17182110181.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'forexacard'//该邮局通道所使用的队列
		],

		'forexbcard'	=> [
			'code'		=> 'ali007',
			'name'		=> 'forexbcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'service@forexbcard.com',
			'password' => 'Y17182111623.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'forexbcard'//该邮局通道所使用的队列
		],

		'fxcarda'	=> [
			'code'		=> 'ali008',
			'name'		=> 'fxcarda',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'service@fxcarda.com',
			'password' => 'Y17182112868.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'fxcarda'//该邮局通道所使用的队列
		],

		'info_fxpointcard'	=> [
			'code'		=> 'ali009',
			'name'		=> 'info_fxpointcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'info@fxpointcard.com',
			'password' => 'Y17182112867.',
			'daily_send_limit'	=> 100,
			'enabled'		=> -1,
			'queue_key'		=> 'fxpointcard'//该邮局通道所使用的队列
		],

		'info_fxrefillcard'	=> [
			'code'		=> 'ali010',
			'name'		=> 'info_fxrefillcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'info@fxrefillcard.com',
			'password' => 'Y17182111628.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'fxrefillcard'//该邮局通道所使用的队列
		],

		'info_forexacard'	=> [
			'code'		=> 'ali011',
			'name'		=> 'info_forexacard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'info@forexacard.com',
			'password' => 'Y17182110181.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'forexacard'//该邮局通道所使用的队列
		],

		'info_forexbcard'	=> [
			'code'		=> 'ali012',
			'name'		=> 'info_forexbcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'info@forexbcard.com',
			'password' => 'Y17182111623.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'forexbcard'//该邮局通道所使用的队列
		],

		'info_fxcarda'	=> [
			'code'		=> 'ali013',
			'name'		=> 'info_fxcarda',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'info@fxcarda.com',
			'password' => 'Y17182112868.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'fxcarda'//该邮局通道所使用的队列
		],

		'system_fxpointcard'	=> [
			'code'		=> 'ali014',
			'name'		=> 'system_fxpointcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'system@fxpointcard.com',
			'password' => 'Y17182112867.',
			'daily_send_limit'	=> 100,
			'enabled'		=> -1,
			'queue_key'		=> 'fxpointcard'//该邮局通道所使用的队列
		],

		'system_fxrefillcard'	=> [
			'code'		=> 'ali015',
			'name'		=> 'system_fxrefillcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'system@fxrefillcard.com',
			'password' => 'Y17182111628.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'fxrefillcard'//该邮局通道所使用的队列
		],

		'system_forexacard'	=> [
			'code'		=> 'ali016',
			'name'		=> 'system_forexacard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'system@forexacard.com',
			'password' => 'Y17182110181.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'forexacard'//该邮局通道所使用的队列
		],

		'system_forexbcard'	=> [
			'code'		=> 'ali017',
			'name'		=> 'system_forexbcard',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'system@forexbcard.com',
			'password' => 'Y17182111623.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'forexbcard'//该邮局通道所使用的队列
		],

		'system_fxcarda'	=> [
			'code'		=> 'ali018',
			'name'		=> 'system_fxcarda',
			'driver'	=> 'smtp',
			'host' => "smtp.mxhichina.com", // 根据你的邮件服务提供商来填
			'port' => "465", // 同上
			'encryption' => "ssl", // 同上 一般是tls或ssl
			'username' => 'system@fxcarda.com',
			'password' => 'Y17182112868.',
			'daily_send_limit'	=> 100,
			'queue_key'		=> 'fxcarda'//该邮局通道所使用的队列
		],

	];

	/**
	 * table name
	 *
	 * @var string
	 */
	private static $table_name = "mail_channels";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$i = 0;
		foreach (self::$init_data as $key => $value){
			$count = DB::table(self::$table_name)->where('code',$value['code'])->count();
			if($count == 0) {
				if(isset($value['stream'])){
					$value['stream'] = json_encode($value['stream']);
				}
				$value['created_at'] = date('Y-m-d H:i:s',time()+$i);
				DB::table(self::$table_name)->insert($value);
			}
			$i++;
		}
    }
}
