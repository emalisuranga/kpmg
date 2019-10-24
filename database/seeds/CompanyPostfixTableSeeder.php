<?php

use Illuminate\Database\Seeder;
use App\CompanyPostfix;
use App\Setting;

class CompanyPostfixTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $private = Setting::where('key','COMPANY_TYPE_PRIVATE')->first();
        $data = array(
		    array('company_type_id' => $private->id, 'postfix' => '(PVT) LTD'),
		    array('company_type_id' => $private->id, 'postfix' => '(PRIVATE) LTD')
		    
			);
        $setting = CompanyPostfix::insert($data);
        
        $public = Setting::where('key','COMPANY_TYPE_PUBLIC')->first();
        $data = array(
		    array('company_type_id' => $public->id, 'postfix' => 'PLC'),
		    array('company_type_id' => $public->id, 'postfix' => 'LTD'),
		    array('company_type_id' => $public->id, 'postfix' => 'LIMITED'),
		    array('company_type_id' => $public->id, 'postfix' => 'PUBLIC LIMITED COMPANY')
		    
            );
        $setting = CompanyPostfix::insert($data);
        
        $guarantee32 = Setting::where('key','COMPANY_TYPE_GUARANTEE_32')->first();
        $data = array(
		    array('company_type_id' => $guarantee32->id, 'postfix' => 'LTD'),
		    array('company_type_id' => $guarantee32->id, 'postfix' => 'LIMITED'),
		    array('company_type_id' => $guarantee32->id, 'postfix' => 'GUARANTEE'),
		    array('company_type_id' => $guarantee32->id, 'postfix' => '(GUARANTEE) LIMITED'),
		    
			);    
        $setting = CompanyPostfix::insert($data);
    }
}
