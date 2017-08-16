<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\ReplyModel;
use Channel;

class SendChannelMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reply:send {ids=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '回复消息 状态的 NEW 的记录';

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
        //
        $ids =  $this->argument('ids');

        if($ids != 'all'){
            $replies_ids = explode(',', $ids);
            $replies = ReplyModel::whereIn('id', $replies_ids)->get();
        }else{
            $replies = ReplyModel::where('status', 'NEW')->get();
        }

        if(! $replies->isEmpty()){
            foreach($replies as $reply){
                $account = $reply->message->account;
                $adpter = Channel::driver($account->channel->api_type, $account->api_config);
                if($adpter->sendMessages($reply)){
                    $this->info('#reply_id '. $reply->id . ' SENT');
                }else{
                    $this->comment('#reply_id '. $reply->id . ' FAIL');
                }
            }
        }
    }
}
