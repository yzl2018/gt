<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailShipped extends Mailable
{
    use Queueable, SerializesModels;
    
	/**
	 * MailShipped constructor.
	 * @param array $mail_type
	 * @param array $mail_data
	 * @param string $file_path
	 */
    public function __construct(array $mail_type,array $mail_data,$file_path = null)
    {
    	
    	$this->view = $mail_type['view'];
		$this->subject = $mail_type['subject'];
		$this->viewData = $mail_data;
		if($file_path != null){
			$this->attach($file_path);
		}
		
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		return $this;
    }
}
