<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditorFirmPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auditor_firm_partners', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('firm_id');
            $table->unsignedInteger('auditor_id');
            $table->text('other_state')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auditor_firm_auditors');
    }
}
