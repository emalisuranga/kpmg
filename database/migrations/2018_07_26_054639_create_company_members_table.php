<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->enum('is_natural_person', ['yes', 'no'])->default('yes');
            $table->unsignedSmallInteger('designation_type');
            $table->unsignedSmallInteger('title')->nullable();
            $table->string('first_name',100);
            $table->string('last_name',100);
            $table->string('other_name',100)->nullable();
            $table->string('nic',20)->nullable();            
            $table->string('passport_no',15)->nullable();
            $table->string('passport_issued_country')->nullable();
            $table->string('telephone',15)->nullable();
            $table->string('mobile',15)->nullable();
            $table->string('email',100)->nullable();
            $table->date('dob')->nullable();
            $table->enum('sex', ['male', 'female'])->default('male');
            $table->enum('is_srilankan', ['yes', 'no'])->default('yes');
            $table->unsignedInteger('address_id');
            $table->unsignedInteger('foreign_address_id')->nullable();
            $table->string('occupation',100)->nullable();
            $table->date('date_of_appointment')->nullable();
            $table->string('designation',100)->nullable();
            $table->enum('is_registered_secretary', ['yes', 'no'])->default('no');
            $table->string('secretary_registration_no',20)->nullable();
            $table->unsignedInteger('company_member_firm_id')->nullable();
            $table->unsignedSmallInteger('status');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')
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
        Schema::dropIfExists('company_members');
    }
}
