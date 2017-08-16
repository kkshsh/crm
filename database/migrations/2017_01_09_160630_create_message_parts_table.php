<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagePartsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_parts', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('message_id')->nullable();
			$table->integer('parent_id')->nullable();
			$table->string('part_id', 10)->nullable();
			$table->string('mime_type')->nullable();
			$table->text('headers', 65535)->nullable();
			$table->string('filename')->nullable();
			$table->text('attachment_id', 65535)->nullable();
			$table->text('body', 65535)->nullable();
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
        Schema::drop('message_parts');
    }

}
