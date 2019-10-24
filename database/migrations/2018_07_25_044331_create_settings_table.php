<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('setting_type_id');
            $table->string('key',50)->unique();
            $table->string('value');
            $table->string('value_si')->nullable();
            $table->string('value_ta')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->enum('status', ['active', 'deactive'])->default('active');
            $table->foreign('setting_type_id')->references('id')->on('setting_types')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
