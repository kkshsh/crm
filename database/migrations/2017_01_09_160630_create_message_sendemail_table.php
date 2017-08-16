<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageSendemailTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_sendemail', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('message_id')->nullable();
			$table->string('to');
			$table->string('to_email');
			$table->string('title')->nullable();
			$table->text('content', 65535)->nullable();
			$table->enum('status', array('NEW','FAIL','SENT'))->nullable()->default('NEW');
			$table->string('updatefile')->nullable();
			$table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('message_sendemail');
    }

}
