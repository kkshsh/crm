<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\AccountModel;
use Google_Client;
use Google_Service_Gmail;

class GetGmailCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:getCredential {account_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Gmail Credentials';

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
        $account = AccountModel::find($this->argument('account_id'));
        if(! empty($account)){
            $client = new Google_Client();
            $client->setScopes(implode(' ', array(
                Google_Service_Gmail::GMAIL_READONLY,
                Google_Service_Gmail::GMAIL_MODIFY,
                Google_Service_Gmail::GMAIL_COMPOSE,
                Google_Service_Gmail::GMAIL_SEND
            )));
            $client->setAuthConfig($account->secret);
            $client->setAccessType('offline');

            if ($account->token == null) {
                $authUrl = $client->createAuthUrl();
                $this->info("Open the following link in your browser:");
                $this->info($authUrl);
                $authCode = $this->ask('Enter verification code of ' . $account->account . ': ');

                $account->token = $client->authenticate($authCode);
                $account->save();
            }

        }
        exit;

        foreach (AccountModel::all() as $account) {
            $client = new Google_Client();
            $client->setScopes(implode(' ', array(
                Google_Service_Gmail::GMAIL_READONLY,
                Google_Service_Gmail::GMAIL_MODIFY,
                Google_Service_Gmail::GMAIL_COMPOSE,
                Google_Service_Gmail::GMAIL_SEND
            )));
            $client->setAuthConfig($account->secret);
            $client->setAccessType('offline');

            if ($account->token == null) {
                $authUrl = $client->createAuthUrl();
                $this->info("Open the following link in your browser:");
                $this->info($authUrl);
                $authCode = $this->ask('Enter verification code of ' . $account->account . ': ');

                $account->token = $client->authenticate($authCode);
                $account->save();
            }
        }
    }
}
