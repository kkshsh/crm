<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageLabelsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_labels', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('account_id')->nullable();
			$table->string('label_id')->nullable();
			$table->string('name')->nullable();
			$table->string('message_list_visibility')->nullable();
			$table->string('label_list_visibility')->nullable();
			$table->string('type')->nullable();
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
        Schema::drop('message_labels');
    }

}
