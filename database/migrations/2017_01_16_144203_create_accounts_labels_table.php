<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts_labels', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->nullable();
            $table->string('label_id')->nullable();
            $table->string('name')->nullable();
            $table->string('is_get_mail')->nullable();
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
        Schema::drop('accounts_labels');
    }
}
