<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveToMessageAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_accounts', function (Blueprint $table) {
            $table->integer('is_active')->comment('是否有效')->default(0);;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_accounts', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
    }
}
