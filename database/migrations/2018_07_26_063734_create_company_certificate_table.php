<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCertificateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_certificate', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->unique();
            $table->string('registration_no',20)->unique();
            $table->unsignedTinyInteger('print_count')->default(0);
            $table->string('path');
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
        Schema::dropIfExists('company_certificate');
    }
}
