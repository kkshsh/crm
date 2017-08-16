<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageAccountsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_accounts', function(Blueprint $table) {
            $table->increments('id');
			$table->string('account')->nullable();
			$table->string('name')->nullable();
			$table->text('secret', 65535)->nullable();
			$table->text('token', 65535)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->integer('channel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('message_accounts');
    }

}
