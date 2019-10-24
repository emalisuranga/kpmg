<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call([
         	//LaratrustSeeder::class,
            SettingTypesSeeder::class,
            SettingSeeder::class,
         	//RulesTableSeeder::class,
         //	PrivilageTableSeeder::class,
         	CompanyPostfixTableSeeder::class,
         	CountryTableSeeder::class,
         	//CompanyTableSeeder::class,
         	//peopleTableSeeder::class,
         	//AddressSeeder::class,
         	//publicTableSeeder::class,
         	]);
    }
}
