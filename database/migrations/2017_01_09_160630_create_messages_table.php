<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->nullable();
            $table->integer('channel_id')->nullable();
            $table->integer('type_id');
            $table->string('list_id')->nullable();
            $table->string('message_id')->nullable();
            $table->integer('assign_id')->nullable();
            $table->enum('status', array('UNREAD','PROCESS','COMPLETE'))->nullable();
            $table->text('labels', 65535)->nullable();
            $table->string('label')->nullable();
            $table->string('from')->nullable();
            $table->string('from_name')->nullable();
            $table->string('to')->nullable();
            $table->string('date')->nullable();
            $table->text('subject', 65535)->nullable();
            $table->text('title_email', 65535)->nullable();
            $table->enum('related', array('0','1'))->nullable()->default('0');
            $table->enum('required', array('0','1'))->nullable()->default('1');
            $table->enum('read', array('0','1'))->nullable()->default('0');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->integer('dont_reply')->nullable()->default(0);
            $table->mediumText('channel_message_fields')->nullable();
            $table->mediumText('content')->nullable();
            $table->string('channel_order_number')->nullable();
            $table->string('channel_url')->nullable();
            $table->integer('is_auto_reply')->default(0)->comment('0 无需自动发,1 自动发,2 未检查');
            $table->string('country')->comment('wish消息国家')->nullable();
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
        Schema::drop('messages');
    }

}
