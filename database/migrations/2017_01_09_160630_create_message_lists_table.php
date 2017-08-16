<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_lists', function(Blueprint $table) {
            $table->integer('id')->unsigned();
			$table->integer('account_id')->nullable();
			$table->string('next_page_token')->nullable();
			$table->integer('result_size_estimate')->nullable();
			$table->integer('count')->nullable();
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
        Schema::drop('message_lists');
    }

}
