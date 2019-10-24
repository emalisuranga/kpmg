<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretaryCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secretary_certificates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('secretary_id')->nullable();
            $table->unsignedInteger('firm_id')->nullable();
            $table->string('certificate_no');
            $table->string('path');
            $table->string('file_token');
            $table->unsignedInteger('sealed_by')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
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
        Schema::dropIfExists('secretary_certificates');
    }
}
