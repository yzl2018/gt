<?php

namespace App\Console\Commands;

use App\Http\Toolkit\MailChannelsDispatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckMailChannels extends Command
{

	use MailChannelsDispatch;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:channels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check mail channels for use';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //TODO 检测并恢复发送异常的邮局通道
		$exception_channels = DB::table('mail_channels')->where('enabled','=',2)->get();
		if($exception_channels->isNotEmpty()){
			foreach ($exception_channels as $channel){
				$this->testDispatchMailJob($channel);
			}
		}

		//TODO 检测并恢复满额或无可用通道的通道组
		$unabled_channels_groups = DB::table('mail_channels_group')->where('status','!=',0)->get();
		if($unabled_channels_groups->isNotEmpty()){
			foreach ($unabled_channels_groups as $group){
				$include_channels = json_decode($group->include_channels,true);
				$channels_count = DB::table('mail_channels')
					->whereIn('code',$include_channels)
					->whereColumn('daily_send_limit','<=','daily_send_times')
					->whereIn('enabled',[0,1])
					->count();
				if($channels_count > 0){
					DB::table('mail_channels_group')->where('id',$group->id)->update(['status'=>0]);
				}
			}
		}
    }

}
