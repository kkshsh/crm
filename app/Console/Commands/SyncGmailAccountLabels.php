<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message\AccountModel;
use App\Models\Message\ChannelModel;
use App\Models\AccountLabelModel;
use Google_Client;
use Google_Service_Gmail;

class SyncGmailAccountLabels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncGmailLabels:get {account_id=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步线上gmail账号标签到本地';

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
        if($account && $this->argument('account_id')!= 'all'){

            $client = $this->getClient($account);
            $service = new Google_Service_Gmail($client);
            $user = 'me';
            $labels = $service->users_labels->listUsersLabels($user);



            foreach ($labels as $label) {
                //$newLabel = LabelModel::firstOrNew(['label_id' => $label->id]);

                $model = AccountLabelModel::firstOrNew(['account_id' => $account->id, 'name' => $label->name]);
                if(! $model->exists){
                    $model->account_id = $account->id;
                    $model->label_id = $label->id;;
                    $model->name = $label->name;
                    $model->is_get_mail = 'unget';
                    $model->save();
                    $this->info($label->id.' Saved.');

                }
            }
        } elseif ($this->argument('account_id') == 'all') {
            $channel_ids = ChannelModel::where('api_type', 'amazon')
                ->where('is_active',1)->get()->pluck('id');
            $accounts = AccountModel::where('is_active', 1)
                ->whereIn('channel_id', $channel_ids)->get();

            foreach ($accounts as $account) {
                $this->info($account->name.' Start>>>>');
                $client = $this->getClient($account);
                $service = new Google_Service_Gmail($client);
                $user = 'me';
                $labels = $service->users_labels->listUsersLabels($user);

                foreach ($labels as $label) {
                    //$newLabel = LabelModel::firstOrNew(['label_id' => $label->id]);

                    $model = AccountLabelModel::firstOrNew(['account_id' => $account->id, 'name' => $label->name]);
                    if(! $model->exists){
                        $model->account_id = $account->id;
                        $model->label_id = $label->id;;
                        $model->name = $label->name;
                        $model->is_get_mail = 'unget';
                        $model->save();
                        $this->info($label->id.' Saved.');
                    }
                }
                $this->info($account->name.' End');
            }
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
