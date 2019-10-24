<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_types', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('key')->unique();
            $table->string('name');
            $table->enum('is_tri_lang', ['yes', 'no'])->default('no');
            $table->enum('output', ['array', 'string'])->default('array');
            $table->enum('is_hidden', ['yes', 'no'])->default('no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setting_types');
    }
}
