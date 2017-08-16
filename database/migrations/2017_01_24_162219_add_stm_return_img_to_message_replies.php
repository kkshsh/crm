<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStmReturnImgToMessageReplies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_replies', function (Blueprint $table) {
            $table->string('smt_return_img')->comment('速卖通平台返回的图片地址')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_replies', function (Blueprint $table) {
            $table->dropColumn(['smt_return_img']);
        });
    }
}
