<?php

use Illuminate\Database\Seeder;
use App\SettingType;

use Illuminate\Support\Facades\DB;
class SettingTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Truncating Setting table');
        DB::statement('TRUNCATE eroc_setting_types, eroc_settings');

    	$data = array(
		    array('key'=>'COMMON_STATUS','name'=>'Common Status','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
		    array('key'=>'COMPANY_STATUS','name'=>'Company Status','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'COMPANY_TYPES','name'=>'Company Types','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'COMPANY_OBJECTIVE','name'=>'Company Objective','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'COMPANY_REGISTRATION_PROCESS','name'=>'Registration Process','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'COMPANY_SUB_STATUS','name'=>'Company Sub Status','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'RULES_TYPES','name'=>'Rules Types','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'DOCUMENT_STATUS','name'=>'Document Status','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'APPROVED_REJECTED_STATUS','name'=>'Approved or Rejected Status','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'PASS_FAIL_STATUS','name'=>'Pass or fail Status','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'NAME_TITLE','name'=>'Name Title','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'PAYMENTS','name'=>'Payments','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
            array('key'=>'COMPANY_DESIGNATION_TYPE','name'=>'designation type','is_tri_lang' => 'no','output'=>'array','is_hidden' => 'no'),
			);
        $setting = SettingType::insert($data);
    }
}
