<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageRepliesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_replies', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('message_id')->nullable();
			$table->string('to');
			$table->string('to_email');
			$table->string('title')->nullable();
			$table->text('content', 65535)->nullable();
			$table->enum('status', array('NEW','SENT','FAIL'))->nullable()->default('NEW');
			$table->timestamps();
			$table->softDeletes();
			$table->string('updatefile')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('message_replies');
    }

}
