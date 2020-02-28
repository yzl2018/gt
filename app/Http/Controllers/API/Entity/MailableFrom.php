<?php
namespace App\Http\Controllers\API\Entity;

class MailableFrom
{

	/**
	 * Email server driver
	 * @var String
	 */
	public $driver;

	/**
	 * Email server host
	 * @var String
	 */
	public $host;

	/**
	 * Email server port
	 * @var String
	 */
	public $port;

	/**
	 * Server encryption
	 * @var String
	 */
	public $encryption;

	/**
	 * Email user name
	 * @var String
	 */
	public $user_name;

	/**
	 * Email password
	 * @var String
	 */
	public $password;

	/**
	 * Email from
	 * @var array
	 */
	public $from = [
		'address'	=> '',
		'name'		=> ''
	];

	/**
	 * transport stream options
	 *
	 * @var null
	 */
	public $stream = null;

	/**
	 * MailFrom constructor.
	 * @param $mail_channel
	 */
	public function __construct($mail_channel)
	{

		$this->driver = $mail_channel->driver;
		$this->host = $mail_channel->host;
		$this->port = $mail_channel->port;
		$this->encryption = $mail_channel->encryption;
		$this->user_name = $mail_channel->username;
		$this->password = $mail_channel->password;
		
		$this->from = [
			'address'	=> $mail_channel->username,
			'name'		=> explode('@',$mail_channel->username)[0]//config('system.mall.name')
		];
		if(!empty($mail_channel->stream)){
			$this->stream = json_decode($mail_channel->stream,true);
		}
	}

}