<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageForemailTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_foremail', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('assign_id')->nullable();
			$table->integer('message_id')->nullable();
			$table->string('to');
			$table->string('to_email');
			$table->string('to_useremail', 50)->nullable();
			$table->string('title')->nullable();
			$table->text('content', 65535)->nullable();
			$table->enum('status', array('NEW','FAIL','SENT'))->nullable()->default('NEW');
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
        Schema::drop('message_foremail');
    }

}
