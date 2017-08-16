<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\AccountModel;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_ModifyMessageRequest;

class SetMessageRead extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:setRead';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Messages Read';

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
            $readMessages = $account->messages()
                ->where('status', '<>', 'UNREAD')
                ->where('read', 0)
                ->get();
            foreach ($readMessages as $message) {
                $modify = new Google_Service_Gmail_ModifyMessageRequest();
                $modify->setRemoveLabelIds(['UNREAD']);
                $service->users_messages->modify($user, $message->message_id, $modify);
                $message->read = 1;
                $message->save();
                $this->info('Message #' . $message->id . ' Read.');
            }
        }
    }
}
