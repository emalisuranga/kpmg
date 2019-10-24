<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretaryFirmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secretary_firm', function (Blueprint $table) {
            $table->increments('id');
            $table->string('registration_no',20)->unique();
            $table->string('name');
            $table->unsignedInteger('address_id');
            $table->enum('is_undertake_secretary_work', ['yes', 'no'])->default('yes');
            $table->enum('is_unsound_mind', ['yes', 'no'])->default('no');
            $table->enum('is_insolvent_or_bankrupt', ['yes', 'no'])->default('no');
            $table->text('reason',100)->nullable();
            $table->enum('is_competent_court', ['yes', 'no'])->default('no');
            $table->enum('competent_court_type', ['no','pardoned', 'appeal'])->default('no');
            $table->unsignedSmallInteger('status');
            $table->enum('type', ['firm', 'pvt'])->default('firm');
            $table->unsignedInteger('created_by')->nullable();
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
        Schema::dropIfExists('secretary_firm');
    }
}
