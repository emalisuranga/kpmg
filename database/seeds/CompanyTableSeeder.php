<?php

use Illuminate\Database\Seeder;
use App\Company;
class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $data = array(
		    array('id' => 813181234, 'type_id'=> 17,'name' => 'ROC','postfix' => '(PVT) LTD','status' => 3,'created_by' => 1),
		    array('id' => 813181235, 'type_id'=> 17,'name' => 'IMPERIUM','postfix' => '(PVT) LTD','status' => 3,'created_by' => 1),
		    array('id' => 813181236, 'type_id'=> 17,'name' => 'C.A.T','postfix' => '(PVT) LTD','status' => 3,'created_by' => 1),
		    
			);
        $setting = Company::insert($data);
    }
}
