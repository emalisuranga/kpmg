<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretaryFirmPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secretary_firm_partners', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('firm_id');
            $table->string('name');
            $table->text('address');
            $table->text('citizenship');
            $table->string('which_qualified');
            $table->text('professional_qualifications');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secretary_firm_partners');
    }
}
