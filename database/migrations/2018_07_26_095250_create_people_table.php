<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('title');
            $table->string('first_name',100);
            $table->string('last_name',100);
            $table->string('other_name',100);
            $table->string('nic',20)->nullable();
            $table->string('passport_no',15)->nullable();
            $table->string('passport_issued_country')->nullable();
            $table->string('telephone',15)->nullable();
            $table->string('mobile',15)->nullable();
            $table->string('email',100)->nullable();
            $table->unsignedInteger('address_id')->nullable();
            $table->unsignedInteger('foreign_address_id')->nullable();
            $table->date('dob')->nullable();
            $table->enum('sex', ['male', 'female'])->default('male');
            $table->enum('is_srilankan', ['yes', 'no'])->default('yes');
            $table->string('occupation',100)->nullable();
            $table->string('profile_pic')->nullable();
            $table->unsignedSmallInteger('status')->default(1);
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
        Schema::dropIfExists('people');
    }
}
