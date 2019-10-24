<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_payment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('user_id');
            $table->string('payment_type');
            $table->string('description')->nullable();
            $table->float('amount', 8, 2);
            $table->float('vat', 8, 2);
            $table->float('total', 8, 2);
            $table->string('transaction_id')->nullable();
            $table->string('payment_gateway_name');
            $table->string('payment_gateway_convenience_fee');
            $table->unsignedSmallInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_payment');
    }
}
