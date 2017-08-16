<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEbayCasesListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ebay_cases_lists', function(Blueprint $table) {
            $table->increments('id');
			$table->string('case_id')->nullable();
			$table->string('status')->nullable();
			$table->string('type')->nullable();
			$table->string('buyer_id')->nullable();
			$table->string('seller_id')->nullable();
			$table->string('item_id')->nullable();
			$table->string('item_title')->nullable();
			$table->string('transaction_id')->nullable();
			$table->integer('case_quantity')->nullable();
			$table->float('case_amount')->nullable();
			$table->string('respon_date')->nullable();
			$table->string('creation_date')->nullable();
			$table->string('last_modify_date')->nullable();
			$table->string('global_id')->nullable();
			$table->string('open_reason')->nullable();
			$table->string('decision')->nullable();
			$table->string('decision_date')->nullable();
			$table->string('fvf_credited')->nullable();
			$table->float('agreed_renfund_amount')->nullable();
			$table->integer('buyer_expection')->nullable();
			$table->string('detail_status')->nullable();
			$table->string('tran_date')->nullable();
			$table->float('tran_price')->nullable();
			$table->text('content', 16777215)->nullable();
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
        Schema::drop('ebay_cases_lists');
    }

}
