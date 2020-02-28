<?php

namespace App\Jobs;

use App\Http\Controllers\API\Entity\EmailEntity;

use App\Mail\MailFrom;
use App\Mail\MailTo;
use App\Mail\MyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * 可邮寄实体
	 *
	 * @var EmailEntity
	 */
    protected $mail;

	/**
	 * 邮件队列名称标识
	 *
	 * @var string
	 */
    private $queue_key = 'QueueSendEmail';

	/**
	 * 邮件发送状态
	 *
	 * @var int
	 */
    private $send_status = 1;

	/**
	 * 发送失败的原因
	 *
	 * @var null
	 */
    private $fail_reason = null;

    /**
     * Create a new job instance.
     *
	 * @param EmailEntity $mail
     * @return void
     */
    public function __construct(EmailEntity $mail)
    {
        $this->mail = $mail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

    	//每3秒处理一次Redis队列任务
    	Redis::throttle($this->mail->mail_channel)->allow(1)->every(3)->then(function(){
			$send_time = date('Y-m-d H:i:s');
			try{

                //设置邮件发送的邮局
                $mail_from = new MailFrom($this->mail->mail_channel);

                //设置发送邮件
                $mail_to = new MailTo($this->mail->mail_to,$this->mail->view,$this->mail->data,$this->mail->subject,$this->mail->attach_path);

                //生成邮件发送实例
                $mail = new MyMail($mail_from,$mail_to);

                //发送邮件
                $this->send_status = $mail->send();
			}
			catch(\Exception $e){
				Log::error('SendEmail',[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
				$this->fail_reason = $e->getMessage();
				$this->send_status = -1;
			}

            if(empty($this->mail->mail_log_id)){
                Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                    'Send Time' => $send_time,
                    'Message' => 'The mail log id is null'
                ]);
            }
            else{
                //更新邮件发送日志
                $log_data = [
                    'send_status' => $this->send_status,
                    'send_time' => $send_time,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                if (!empty($this->fail_reason)) {
                    $log_data['fail_reason'] = $this->fail_reason;
                }
                try{
                    DB::table('send_mail_log')->where('id', $this->mail->mail_log_id)->update($log_data);
                }
                catch(\Exception $e){
                    Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                        'Update send mail log' => $e->getMessage(),
                        'trace' => $e->getTrace()
                    ]);
                }

                $redis_key = DB::table('send_mail_log')->where('id', $this->mail->mail_log_id)->value('redis_key');
                //更新指定表格的邮件发送状态
                if (empty($redis_key)) {
                    Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                        'Send Time:' => $send_time,
                        'Message' => 'The mail redis key is null'
                    ]);
                } else if (empty($this->mail->update_table)) {
//                    Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
//                        'Send Time:' => $send_time,
//                        $this->mail->update_table => ':The update table is null'
//                    ]);
                } else {
                    if (isset($this->mail->update_table['table_name']) && isset($this->mail->update_table['key_name']) && isset($this->mail->update_table['value_name'])) {
                        if (Schema::hasTable($this->mail->update_table['table_name'])) {
                            if (Schema::hasColumns($this->mail->update_table['table_name'], [$this->mail->update_table['key_name'], $this->mail->update_table['value_name']])) {
                                try {
                                    DB::table($this->mail->update_table['table_name'])
                                        ->where($this->mail->update_table['key_name'], $redis_key)
                                        ->update([$this->mail->update_table['value_name'] => $this->send_status]);
                                } catch (\Exception $e) {
                                    Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                                        'Update '.$this->mail->update_table['table_name'] => $e->getMessage(),
                                        'trace' => $e->getTrace()
                                    ]);
                                }
                            } else {
                                Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                                    'Missing Columns:' => [$this->mail->update_table['key_name'], $this->mail->update_table['value_name']]
                                ]);
                            }
                        } else {
                            Log::error('===' . __FILE__ . ' (line:' . __LINE__ . ')===', [
                                'The table is not exists:' => $this->mail->update_table['table_name']
                            ]);
                        }
                    }
                }
            }

		},function(){
    		$this->release(3);
		});

    }
}
