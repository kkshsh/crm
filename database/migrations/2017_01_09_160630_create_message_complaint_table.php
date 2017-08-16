<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageComplaintTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_complaint', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('message_id')->nullable();
			$table->string('ordernum', 50)->nullable();
			$table->string('email')->nullable();
			$table->string('settled_name', 50)->nullable();
			$table->string('refund', 50)->nullable();
			$table->char('deleted_at')->nullable();
			$table->timestamps();
			$table->integer('created')->nullable();
			$table->string('ws_return')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('message_complaint');
    }

}
