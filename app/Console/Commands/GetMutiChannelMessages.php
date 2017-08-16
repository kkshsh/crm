<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\AccountModel;
use App\Models\Message\ChannelModel;
use App\Models\MessageModel;
use Channel;
use App\Models\Message\MessageAttachment;


class GetMutiChannelMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:message:get {channel=all} {account=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取多个渠道的消息';

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
        $channel =  $this->argument('channel');
        $account =  $this->argument('account');


        if($account != 'all'){
            $accounts = AccountModel::where('account', $account)->where('is_active', 1)->get();
        }else{
            $channel_ids =  ChannelModel::where(function ($query) use ($channel,$account){
                if($channel != 'all'){
                    $query->where('api_type', $channel);
                }
            })->get()->pluck('id');
            $accounts = AccountModel::whereIn('channel_id', $channel_ids)->where('is_active', 1)->get();
        }
        if(! $accounts->isEmpty()){
            foreach ($accounts as $account){
                $this->info( $account->account . ' get messages >>>>>>>>');
                $adpter = Channel::driver($account->channel->api_type, $account->api_config);
                $messages = $adpter->getMessages(); //获取Message列表

                if(is_array($messages)){
                    foreach ($messages as $message){
                        $msg_new = MessageModel::firstOrNew(['message_id' => $message['message_id']]);
                        if($msg_new->id == null){
                            $msg_new->account_id = $account->id;
                            $msg_new->channel_id = $account->channel_id;
                            $msg_new->message_id = $message['message_id'];
                            if (isset($message['from_name'])) {
                                $msg_new->from_name = $message['from_name'];
                            }
                            $msg_new->labels = $message['labels'];
                            $msg_new->label = $message['label'];
                            $msg_new->from = $message['from'];
                            $msg_new->to = !empty($message['to'])?$message['to']:'';
                            $msg_new->date = $message['date'];
                            $msg_new->subject = $message['subject'];
                            $msg_new->content = $message['content'];
                            $msg_new->channel_message_fields = $message['channel_message_fields'];
                            $msg_new->status  = 'UNREAD';
                            $msg_new->related  = '0';
                            $msg_new->required  = 1;
                            $msg_new->read  = 0;

                            $msg_new->list_id = !empty($message['list_id']) ? $message['list_id'] : '';

                            !empty($message['channel_order_number']) ? $msg_new->channel_order_number=$message['channel_order_number'] : '';

                            $msg_new->save();
                            $this->info('Message #' . $msg_new->message_id . ' Received ');

                            //附件写入
                            $messageInsert = MessageModel::firstOrNew(['message_id' => $message['message_id']]);
                            if($messageInsert){
                                if($message['attachment'] !=''){
                                    foreach ($message['attachment'] as $value){
                                        if($value){
                                            $attachment = MessageAttachment::firstOrNew(['message_id' => $messageInsert->message_id]);
                                            $attachment->message_id =$messageInsert->id;
                                            $attachment->gmail_message_id =$messageInsert->message_id;
                                            $attachment->filename = $value['file_name'];
                                            $attachment->filepath = $value['file_path'];
                                            $attachment->save();
                                        }
                                    }
                                }
                            }

                            /**
                             *  如果是gmail账号就平台修改消息状态为已读
                             */
                            if($account->channel->api_type == 'amazon'){
                                $adpter->changeMessageStatus($account->id, $message['message_id']);
                            }

                        }else{
                            $this->comment('Message #' . $msg_new->message_id . ' alerady exist.');

                        }

                    }

                }

            }
        }else{
            $this->comment('not found any account');
        }
        $this->info('the end');

    }
}
