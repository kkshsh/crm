<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Message\AccountModel;
use App\Models\Message\MessageModel;
use App\Models\Message\ChannelModel;
use Channel;


class SendMutiMessages extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $reply;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($reply)
    {
        //
        $this->reply = $reply;
       // $this->description = 'Send message to' . $this->reply['to_email'] . '(message_id:' . $this->reply['message_id'] . ') in SYS.';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        if($this->reply){
            $account = $this->reply->message->account;

            if(! empty($account)){
                $adpter = Channel::driver($account->channel->api_type, $account->api_config);
                if($adpter->sendMessages($this->reply)){
                    echo 'success';
                }else{
                    echo 'failed';
                }

            }
        }
    }
}
