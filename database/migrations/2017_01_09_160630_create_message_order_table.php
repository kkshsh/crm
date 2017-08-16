<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageOrderTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_order', function(Blueprint $table) {
            $table->integer('id', true);
			$table->integer('com_id')->nullable();
			$table->integer('message_id')->nullable();
			$table->integer('assign_id')->nullable();
			$table->string('ordernum', 50)->nullable();
			$table->string('packageid', 50)->nullable();
			$table->string('sku', 25)->nullable();
			$table->string('price', 11)->nullable();
			$table->integer('qty')->nullable();
			$table->string('com', 100)->nullable();
			$table->string('com_name', 100)->nullable();
			$table->string('content', 100)->nullable();
			$table->softDeletes();
			$table->timestamps();
			$table->string('refund_amount', 11)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('message_order');
    }

}
