<?php

use Illuminate\Database\Seeder;
use App\AdminPermission;
class PrivilageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
		    array('name' => 'name-recommandation', 'display_name'=> 'Name Recommandation for approval','description' => 'Name Recommandation for approval'),
            array('name' => 'confirm-name-approval', 'display_name'=> 'Confirm Name approval','description' => 'Confirm Name approval'),
            array('name' => 'incorporation-recommandation', 'display_name'=> 'Recommandation for approval','description' => 'Recommandation for Incorporation approval'),
            array('name' => 'confirm-incorporation-approval', 'display_name'=> 'Confirm Incorporation approval','description' => 'Confirm Incorporation approval'),
			);
        $setting = AdminPermission::insert($data);
    }
}
