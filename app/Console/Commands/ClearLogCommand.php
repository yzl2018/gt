<?php

namespace App\Console\Commands;

use App\Http\Toolkit\Sweeper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearLogCommand extends Command
{
	use Sweeper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clear system`s log';

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
     */
    public function handle()
    {
        Log::channel('sweeper')->info('======sweeper logs======',['msg'=>'Auto clear system\'s log']);
    	$this->autoClearLog();
    }
}
