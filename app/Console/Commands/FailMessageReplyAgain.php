<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Job;
use App\Jobs\SendMessages as queueSendMessage;
use App\Models\Message\ReplyModel;
use Illuminate\Foundation\Bus\DispatchesJobs;


class FailMessageReplyAgain extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reply:again {reply_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '把回复失败的消息重新加入队列处理';

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
        $reply_id =  $this->argument('reply_id'); //reply表 id

        if($reply_id == 'all'){
            $replys = ReplyModel::where('status','FAIL')->get();
            if(!$replys->isEmpty()){
                foreach($replys as $reply){
                    $job = new queueSendMessage($reply);
                    $job = $job->onQueue('SendMessages');
                    $this->dispatch($job);
                    $this->info($reply->id.'onQueue(SendMessages).');
                }
            }else{
                $this->comment('not found fail reply_ids.');
            }
        }else{
            $reply = ReplyModel::find($reply_id);
            if(!empty($reply)){
                $job = new queueSendMessage($reply);
                $job = $job->onQueue('SendMessages');
                $this->dispatch($job);

                $this->info($reply->id.'onQueue(SendMessages).');
            }else{
                $this->comment('not found reply_id '.$reply_id.' info.');
            }
        }

        $this->info('finish');

    }
}
