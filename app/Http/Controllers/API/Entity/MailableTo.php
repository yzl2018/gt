<?php
namespace App\Http\Controllers\API\Entity;

class MailableTo
{
	/**
	 * Email receiver
	 * @var String
	 */
	public $receiver;

	/**
	 * Email view
	 * @var String
	 */
	public $view;

	/**
	 * View data
	 * @var array/string/mixed
	 */
	public $data;

	/**
	 * Subject title
	 * @var String
	 */
	public $subject;

	/**
	 * Email attachment
	 * @var String
	 */
	public $attachment = '';

	public function __construct($receiver,$view,$data,$subject,$path = null)
	{
		$this->receiver = $receiver;
		$this->view = $view;
		$this->data = $data;
		$this->subject = $subject;
		if($path != null){
			$this->attachment = $path;
		}
	}
}