<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->unsignedInteger('id')->unique();
            $table->unsignedSmallInteger('type_id');
            $table->string('name')->unique()->index();
            $table->string('name_si')->nullable();
            $table->string('name_ta')->nullable();
            $table->string('postfix');
            $table->string('abbreviation_desc')->nullable();
            $table->unsignedInteger('address_id')->nullable();
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('objective')->nullable();
            $table->unsignedSmallInteger('status');
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
        Schema::dropIfExists('companies');
    }
}
