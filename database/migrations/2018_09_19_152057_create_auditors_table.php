<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auditors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('title');
            $table->string('first_name',100);
            $table->string('last_name',100);
            $table->string('business_name',100)->nullable();
            $table->string('nic',20)->nullable();
            $table->string('passport_no',20)->nullable();
            $table->string('telephone',15)->nullable();
            $table->string('mobile',15)->nullable();
            $table->string('email',100)->nullable();
            $table->date('dob')->nullable();
            $table->enum('sex', ['male', 'female'])->default('male');
            $table->string('nationality',100)->nullable();
            $table->string('race',100)->nullable();
            $table->string('where_domiciled')->nullable();
            $table->date('from_residence_in_srilanka')->nullable();
            $table->date('continuously_residence_in_srilanka')->nullable();
            $table->text('particulars_of_immovable_property')->nullable();
            $table->text('other_facts_to_the_srilanka_domicile')->nullable();
            $table->unsignedInteger('address_id')->nullable();
            $table->unsignedInteger('business_address_id')->nullable();
            $table->text('professional_qualifications')->nullable();
            $table->enum('is_unsound_mind', ['yes', 'no'])->default('no');
            $table->enum('is_insolvent_or_bankrupt', ['yes', 'no'])->default('no');
            $table->text('reason',100)->nullable();
            $table->enum('is_competent_court', ['yes', 'no'])->default('no');
            $table->enum('competent_court_type', ['no','pardoned', 'appeal'])->default('no');
            $table->string('other_details',100)->nullable();
            $table->string('which_applicant_is_qualified',100)->nullable();
            $table->unsignedSmallInteger('status')->default(1);            
            $table->unsignedSmallInteger('created_by')->nullable();
            $table->unsignedSmallInteger('people_id')->nullable();
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
        Schema::dropIfExists('auditors');
    }
}
