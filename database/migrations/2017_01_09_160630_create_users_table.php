<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('parent_id')->nullable();
			$table->string('group', 50)->nullable();
			$table->string('name');
			$table->string('name_en', 25)->nullable();
			$table->string('email')->unique();
			$table->string('password', 60);
			$table->string('remember_token', 100)->nullable();
			$table->integer('is_login')->nullable();
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
        Schema::drop('users');
    }

}
