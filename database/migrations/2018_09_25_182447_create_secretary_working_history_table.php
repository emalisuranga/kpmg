<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretaryWorkingHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secretary_working_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('secretary_id');
            $table->string('company_name');
            $table->string('position');
            $table->string('from');
            $table->string('to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secretary_working_history');
    }
}
