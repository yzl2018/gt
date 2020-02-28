<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/10/12 10:44
 * +------------------------------------------------------------------------------
 */

namespace App\Mail;

class MailFrom
{
    use MailChannel;
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
     * @param $channel_switch
     */
    public function __construct($channel_switch)
    {

        if(array_key_exists($channel_switch,$this->mail_channels)){
            $email = $this->mail_channels[$channel_switch];
        }else{
            $email = $this->mail_channels['default'];
        }
		$this->driver = $email['driver'];
		$this->host = $email['host'];
		$this->port = $email['port'];
		$this->encryption = $email['encryption'];
		$this->user_name = $email['username'];
		$this->password = $email['password'];
		$this->from = $email['from'];
		if(isset($email['stream'])){
			$this->stream = $email['stream'];
		}
	}

}
