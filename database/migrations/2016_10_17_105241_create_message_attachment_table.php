<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageAttachmentTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_attachment', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('message_id')->nullable();
			$table->string('gmail_message_id')->nullable();
			$table->string('filename')->nullable();
			$table->string('filepath')->nullable();
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
        Schema::drop('message_attachment');
    }

}
