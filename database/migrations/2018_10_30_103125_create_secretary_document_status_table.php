<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretaryDocumentStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secretary_document_status', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('secretary_document_id');
            $table->unsignedSmallInteger('status');
            $table->string('comments');
            $table->unsignedSmallInteger('comment_type')->nullable();
            $table->unsignedInteger('created_by');
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
        Schema::dropIfExists('secretary_document_status');
    }
}
