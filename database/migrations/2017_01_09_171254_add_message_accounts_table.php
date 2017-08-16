<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMessageAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_accounts', function (Blueprint $table) {
            //
            $table->string('aliexpress_member_id');
            $table->string('aliexpress_appkey');
            $table->string('aliexpress_appsecret');
            $table->string('aliexpress_returnurl');
            $table->string('aliexpress_refresh_token');
            $table->string('aliexpress_access_token');
            $table->dateTime('aliexpress_access_token_date');

            $table->string('ebay_developer_account');
            $table->string('ebay_developer_devid');
            $table->string('ebay_developer_appid');
            $table->string('ebay_developer_certid');
            $table->text('ebay_token', 65535);
            $table->string('ebay_eub_developer');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_accounts', function (Blueprint $table) {
            //
        });
    }
}
