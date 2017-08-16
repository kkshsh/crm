<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\AccountModel;
use App\Models\Message\LabelModel;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_ModifyMessageRequest;


class ChangeMutiGmailStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'changeMutiGmailStatus:do{account_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '批量修改消息状态';

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
        $account = AccountModel::find($this->argument('account_id'));
        if(! empty($account)){
            $client = $this->getClient($account);
            $service = new Google_Service_Gmail($client);
            $user = 'me';

            $i = 0;
            $j = 0; //统计信息条数
            $nextPageToken = null;
            $returnAry = [];
            do {
                $i += 1;

                //dd($service->users_labels->listUsersLabels($user));
                $messages = $service->users_messages->listUsersMessages($user,
                    [
                        'labelIds' => ['Label_2', 'UNREAD'],
                        'pageToken' => $nextPageToken,
                        'maxResults' => 10,
                    ]
                );

                $nextPageToken = $messages->nextPageToken;
                foreach ($messages as $key => $message) {
                    $j += 1;

                    //2修改邮件账户的此邮件为已读状态
                    $modify = new Google_Service_Gmail_ModifyMessageRequest();
                    $modify->setRemoveLabelIds(['UNREAD']);
                    $service->users_messages->modify($user, $message->id, $modify);
                    $this->info('Message #' . $message->id . ' change status.');

                }

            } while ($nextPageToken != '');
        }

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


}
