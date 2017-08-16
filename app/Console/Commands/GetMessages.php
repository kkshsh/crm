<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MessageModel;
use App\Models\Message\AccountModel;
use App\Models\Message\ListModel;
use App\Models\Message\PartModel;
use App\Models\Order\PackageModel;
use Google_Client;
use Google_Service_Gmail;
use Tool;

class GetMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getClient($account)
    {
        $client = new Google_Client();
        $client->setScopes(implode(' ', array(
            Google_Service_Gmail::GMAIL_READONLY
        )));
        $client->setAuthConfig($account->secret);
        $client->setAccessType('offline');
        $client->setAccessToken($account->token);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            $account->token = $client->getAccessToken();
            $account->save();
        }
        return $client;
    }

    public function parseMessageHeader($headers)
    {
        $result = [];

        foreach ($headers as $header) {
            $result[$header->getName()] = $header->getValue();
        }

        return $result;
    }

    public function clearEmail($email)
    {
        $email = str_replace('<', '', $email);
        $email = str_replace('>', '', $email);
        return $email;
    }

    public function saveMessagePayload($service, $messageId, $messageNewId, $payload, $parentId = 0)
    {
        $messagePart = PartModel::firstOrNew([
            'message_id' => $messageNewId,
            'parent_id' => $parentId,
            'part_id' => $payload->getPartId()
        ]);
        $messagePart->message_id = $messageNewId;
        $messagePart->parent_id = $parentId;
        $messagePart->part_id = $payload->getPartId();
        $messagePart->mime_type = $payload->getMimeType();
        $messagePart->headers = serialize($payload->getHeaders());
        $messagePart->filename = $payload->getFilename();
        $messagePart->attachment_id = $payload->getBody()->getAttachmentId();
        $messagePart->body = $payload->getBody()->getData();
        $messagePart->save();

        if ($payload->getFilename()) {
            $attachment = $service->users_messages_attachments->get('me', $messageId, $messagePart->attachment_id);
            @file_put_contents(config('message.attachmentPath') . $messagePart->id . '_' . $messagePart->filename,
                Tool::base64Decode($attachment->data));
        }

        $mimeType = explode('/', $payload->getMimeType());
        if ($mimeType[0] == 'multipart') {
            foreach ($payload->getParts() as $part) {
                $this->saveMessagePayload($service, $messageId, $messageNewId, $part, $messagePart->id);
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (AccountModel::all() as $account) {
            $client = $this->getClient($account);
            $service = new Google_Service_Gmail($client);
            $user = 'me';
            $i = 0;
            $nextPageToken = null;
            do {
                $i += 1;
                $messages = $service->users_messages->listUsersMessages($user,
                    [
                        'labelIds' => ['INBOX', 'UNREAD'],
                        'pageToken' => $nextPageToken
                    ]
                );
                $nextPageToken = $messages->nextPageToken;
                //save list
                $messageList = new ListModel;
                $messageList->account_id = $account->id;
                $messageList->next_page_token = $messages->nextPageToken;
                $messageList->result_size_estimate = $messages->resultSizeEstimate;
                $messageList->count = count($messages);
                $messageList->save();

                foreach ($messages as $message) {
                    $messageNew = MessageModel::firstOrNew(['message_id' => $message->id]);
                    if ($messageNew->id == null) {
                        $messageContent = $service->users_messages->get($user, $message->id);
                        $messagePayload = $messageContent->getPayload();
                        $messageHeader = $this->parseMessageHeader($messagePayload->getHeaders());

                        $messageLabels = $messageContent->getLabelIds();
                        $messageNew->account_id = $account->id;
                        $messageNew->list_id = $messageList->id;
                        $messageNew->message_id = $messageContent->getId();
                        $messageNew->labels = serialize($messageLabels);
                        $messageNew->label = $messageLabels[0];
                        if (isset($messageHeader['From'])) {
                            $messageFrom = explode(' <', $messageHeader['From']);
                            if (count($messageFrom) > 1) {
                                $messageNew->from = $this->clearEmail(str_replace('>', '', $messageFrom[1]));
                                $messageNew->from_name = str_replace('"', '', $messageFrom[0]);
                            } else {
                                $messageNew->from = $this->clearEmail($messageHeader['From']);
                            }
                        }
                        if (isset($messageHeader['To'])) {
                            $messageTo = explode(' <', $messageHeader['To']);
                            if (count($messageTo) > 1) {
                                $messageNew->to = $this->clearEmail(str_replace('>', '', $messageTo[1]));
                            } else {
                                $messageNew->to = $this->clearEmail($messageHeader['To']);
                            }
                        }
                        $messageNew->date = isset($messageHeader['Date']) ? $messageHeader['Date'] : '';
                        $messageNew->subject = isset($messageHeader['Subject']) ? $messageHeader['Subject'] : '';
                        /*
                        //判断subject 是否有值
                        if($messageHeader['Subject']){
                            //截取两个规定字符之间的字符串
                            preg_match_all("|Message from(.*)via|U", $messageHeader['Subject'], $out,PREG_PATTERN_ORDER);
                        }
                        $messageNew->title_email = isset($out[0][0]) ?  $out[0][0] : '';
                        */
                        $messageNew->save();
                        $this->saveMessagePayload($service, $message->id, $messageNew->id, $messagePayload);
                        $k=MessageModel::find($messageNew->id);
                        //找到最近的历史邮件
                        //找到这个历史邮件的assigne
                        //判断如果是 pre-sale@choies.com 这种类型的邮箱不自动分配
                        if($k->from !="pre-sale@choies.com" and $k->from !="livechat@comm100chat.com"){
                            $userId = $k->last->assign_id;
                            //因为id为14的这名客服已经离职所以判断历史邮件的时候总会报错
                            //解决方案改成 把这封邮件手动分配给别人
                            if($userId==14 || $userId==17){
                                $userId=10;
                            }
                            if($userId){
                                $data1['assign_id']=$k->assign_id=$userId;
                                $data1['status']=$k->status="PROCESS";
                                $data1['content']=strip_tags($k->message_content);
                                $k->update($data1);
                            }
                        }else{
                            $data1['content']=strip_tags($k->message_content);
                            $k->update($data1);
                        }


                        //找到关联订单
                        //找到from的邮箱，再用这个邮箱去搜索订单列表对应EMAIL的订单
                        //找到邮件内容，匹配 *********1340 ，用这个订单号去搜索订单列表
                        /*
                        if($k->from=="pre-sale@choies.com"){
                            $body=$k->message_content;
                            preg_match_all('/ordernum:(.*?)<br>/',$body,$match);
                            if(count($match[1])){
                                $orderNums[]=$match[1][0];
                                $k->setRelatedOrders($orderNums);
                            }
                        }
                        */



                        /*
                        $orderNums = $messageNew->guessRelatedOrders($messageNew->from);
                        if($orderNums){
                            $messageNew->setRelatedOrders($orderNums);
                        }else{
                            //把orderNums转化为只有orderNum的数组
                            if($messageNew->to=="pre-sale@choies.com"){
                                $body=$messageNew->message_content;
                                preg_match_all('/ordernum:(.*?)<br>/',$body,$match);
                                if(count($match[1])){
                                    $orderNums[]=$match[1][0];
                                }
                            }
                            if(count($orderNums)){
                                $messageNew->setRelatedOrders($orderNums);
                            }
                        }
                        */
                        $this->info('Message #' . $messageNew->message_id . ' Received.');
                    }
                }
            } while ($nextPageToken != '');
        }
    }
}
