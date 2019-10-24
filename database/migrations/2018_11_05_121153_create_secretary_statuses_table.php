<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretaryStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secretary_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('secretary_id')->nullable();
            $table->unsignedInteger('firm_id')->nullable();
            $table->unsignedSmallInteger('status');
            $table->text('comments')->nullable();
            $table->unsignedSmallInteger('comment_type');
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
        Schema::dropIfExists('secretary_statuses');
    }
}
