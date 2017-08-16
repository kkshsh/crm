<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageChannelsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_channels', function(Blueprint $table) {
            $table->increments('id');
			$table->string('name');
			$table->string('alias');
			$table->integer('is_active');
			$table->enum('api_type', array('amazon','ebay','aliexpress'))->default('amazon');
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
        Schema::drop('message_channels');
    }

}
