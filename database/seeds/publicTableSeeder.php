<?php

use Illuminate\Database\Seeder;
use App\User;

class publicTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $data = array(
		    array('people_id' => 1, 'email'=> 'william@gmail.com','password' => '1ryt'),
		    array('people_id' => 2, 'email'=> 'yohan@gmail.com','password' => 'tryrt2'),
		    array('people_id' => 3, 'email'=> 'petter@gmail.com','password' => '3rty'),
		    
			);
        $setting = User::insert($data);
    }
}
