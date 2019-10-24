<?php

use Illuminate\Database\Seeder;
use App\Address;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
		    array('address1' => 'Templers Road', 'address2'=> 'Templers Place','city' => 'Mount Lavania','district' => 'Colombo','province' => 'Western','country' => 'Sri Lanka'),
            array('address1' => '10 A Mayura Place', 'address2'=> '','city' => 'Colombo 6','district' => 'Colombo','province' => 'Western','country' => 'Sri Lanka'),
            array('address1' => '2ed Cross Street', 'address2'=> 'Templers Place','city' => 'Baddula','district' => 'Baddula','province' => 'Uva','country' => 'Sri Lanka'),
		    
			);
        $setting = Address::insert($data);
    }
}
