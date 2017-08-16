<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\AccountModel;
use App\Models\Message\LabelModel;
use Google_Client;
use Google_Service_Gmail;

class GetGmailLables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:getLabels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Gmail Labels';

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
            $labels = $service->users_labels->listUsersLabels($user);

            foreach ($labels as $label) {
                $newLabel = LabelModel::firstOrNew(['label_id' => $label->id]);
                $newLabel->account_id = $account->id;
                $newLabel->label_id = $label->id;
                $newLabel->name = $label->name;
                $newLabel->message_list_visibility = $label->messageListVisibility;
                $newLabel->label_list_visibility = $label->labelListVisibility;
                $newLabel->type = $label->type;
                $newLabel->save();
                $this->info($label->id.' Saved.');
            }
        }
    }
}
