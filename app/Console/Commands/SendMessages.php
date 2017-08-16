<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\AccountModel;
use App\Models\Message\ForemailModel;
use App\Models\Message\SendemailModel;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Tool;

class SendMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Messages';

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
            Google_Service_Gmail::GMAIL_MODIFY,
            Google_Service_Gmail::GMAIL_COMPOSE,
            Google_Service_Gmail::GMAIL_SEND
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

    public function message($from, $fromEmail, $to, $toEmail, $subject, $content)
    {
        $message = 'From: =?utf-8?B?' . base64_encode($from) . '?= <' . $fromEmail . ">\r\n";
        $message .= 'To: =?utf-8?B?' . base64_encode($to) . '?= <' . $toEmail . ">\r\n";
        $message .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/html; charset=utf-8\r\n";
        $message .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        //$content=htmlspecialchars($content);
        $message .= $content . "\r\n";
        $this->info($message);
        return Tool::base64Encode($message);
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
            foreach ($account->replies()->where('message_replies.status', 'NEW')->get() as $reply)
            {
                $this->info($reply->id);
                $from = $account->name;
                $fromEmail = $account->account;
                $to = $reply->to ? $reply->to : $reply->message->from_name;
                $toEmail = $reply->to_email ? $reply->to_email : $reply->message->from;
                $subject = $reply->title;
                $content = nl2br($reply->content)."<br/>".$reply->updatefile;

                $message = new Google_Service_Gmail_Message();
                $message->setRaw($this->message($from, $fromEmail, $to, $toEmail, $subject, $content));
                $result = $service->users_messages->send($user, $message);
                $reply->status = $result->id ? 'SENT' : 'FAIL';
                $reply->save();
                $this->info($result->id . '<' . $account->account . '>' . $reply->title . ' Sent.');
            }

                //转发邮件
                /*
                $message = ForemailModel::find(1);
                $from = 'choies-service';
                $fromEmail = 'service@choies.com';
                $to = $message->to;
                $toEmail = $message->to_email;
                $subject = $message->title;
                $content = nl2br($message->content);
                $obj = new Google_Service_Gmail_Message();
                $obj->setRaw($this->message($from, $fromEmail, $to, $toEmail, $subject, $content));  
                $result = $service->users_messages->send($user, $obj);
                $this->info($result->id);
                exit;
                */

                $Foremails = ForemailModel::all();
                foreach ($Foremails as $k => $foremail) {
                    if($foremail->status=='NEW'){
                        $from ='choies-service';
                        $fromEmail = 'service@choies.com';
                        $to = $foremail->to;
                        $toEmail = $foremail->to_email;
                        $subject = '客户邮箱->'.$foremail->to_useremail;
                        $content = nl2br($foremail->content);
                        $obj = new Google_Service_Gmail_Message();
                        $obj->setRaw($this->message($from, $fromEmail, $to, $toEmail, $subject, $content));  
                        $result = $service->users_messages->send($user, $obj);
                        $foremail->status = $result->id ? 'SENT' : 'FAIL';
                        $foremail->save();
                        $this->info($foremail->id);
                    }
                }

                    $Sendemails = SendemailModel::all();
                    foreach ($Sendemails as $k => $sendemail) {
                        if($sendemail->status=='NEW'){
                            $from ='choies-service';
                            $fromEmail = 'service@choies.com';
                            $to = $foremail->to;
                            $toEmail = $sendemail->to_email;
                            $subject =$sendemail->title;
                            $content = nl2br($sendemail->content);
                            $obj = new Google_Service_Gmail_Message();
                            $obj->setRaw($this->message($from, $fromEmail, $to, $toEmail, $subject, $content));
                            $result = $service->users_messages->send($user, $obj);
                            $sendemail->status = $result->id ? 'SENT' : 'FAIL';
                            $sendemail->save();
                            $this->info($sendemail->id);
                        }
                    }

        }
    }
}
