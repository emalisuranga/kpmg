<?php

use Illuminate\Database\Seeder;
use App\People;

class peopleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $data = array(
		    array('first_name' => 'Justin', 'last_name'=> 'William','address_id' => 1,'nic' => '86128022V'),
		    array('first_name' => 'Sayan', 'last_name'=> 'Yohan','address_id' => 1,'nic' => '87128022V'),
		    array('first_name' => 'John', 'last_name'=> 'Petter','address_id' => 1,'nic' => '85128022V'),
		    
			);
        $setting = People::insert($data);
    }
}
