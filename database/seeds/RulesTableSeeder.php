<?php

use Illuminate\Database\Seeder;
use App\Rule;
use App\Setting;

class RulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $gov = Setting::where('key','RULE_TYPE_GOV')->first();
        $data = array(
		    array('type_id' => $gov->id, 'word'=>'Mihin'),
		    array('type_id' => $gov->id, 'word'=>'Samurdhi'),
		    array('type_id'=> $gov->id, 'word'=>'Divineguma'),
		    array('type_id'=> $gov->id, 'word'=>'Nara'),
		    array('type_id'=> $gov->id, 'word'=>'Nenasala'),
		    array('type_id'=> $gov->id, 'word'=>'Mother of Sri Lanka')
			);
        $setting = Rule::insert($data);

        $restricted = Setting::where('key','RULE_TYPE_RESTRICTED')->first();
        $data = array(
		    array('type_id' => $restricted->id, 'word'=>'President'),
		    array('type_id' => $restricted->id, 'word'=>'Presidential'),
		    array('type_id'=> $restricted->id, 'word'=>'Municipal'),
		    array('type_id'=> $restricted->id, 'word'=>'Incorporated'),
		    array('type_id'=> $restricted->id, 'word'=>'Co-operative'),
		    array('type_id'=> $restricted->id, 'word'=>'Society'),
		    array('type_id'=> $restricted->id, 'word'=>'National'),
		    array('type_id'=> $restricted->id, 'word'=>'State'),
		    array('type_id'=> $restricted->id, 'word'=>'Sri Lanka'),
			);
		$setting = Rule::insert($data);
		
		$suitability = Setting::where('key','RULE_TYPE_NAME_SUITABILITY')->first();
        $data = array(
			array('type_id'=> $suitability->id, 'word'=>'International School'),
		    array('type_id'=> $suitability->id, 'word'=>'international College'),
		    array('type_id'=> $suitability->id, 'word'=>'University'),
		    array('type_id'=> $suitability->id, 'word'=>'Church'),
		);
		$setting = Rule::insert($data);
		
		
		$omit = Setting::where('key','RULE_TYPE_OMIT_WORDS')->first();
        $data = array(
			array('type_id'=> $omit->id, 'word'=>'the'),
		    array('type_id'=> $omit->id, 'word'=>'and'),
		    array('type_id'=> $omit->id, 'word'=>'&'),
		    array('type_id'=> $omit->id, 'word'=>'Company'),
		    array('type_id'=> $omit->id, 'word'=>'Limited'),
		    array('type_id'=> $omit->id, 'word'=>'Unlimited'),
		);
		$setting = Rule::insert($data);


        $sp = Setting::where('key','RULE_TYPE_SPECIAL_PERMISION')->first();
        $data = array(
		    array('type_id' => $sp->id, 'word'=>'Bank','permission_from'=> 'Central Bank'),
		    array('type_id' => $sp->id, 'word'=>'Banking','permission_from'=> 'Central Bank'),
		    array('type_id'=> $sp->id, 'word'=>'Finance','permission_from'=> 'Central Bank'),
		    array('type_id'=> $sp->id, 'word'=>'Financial ','permission_from'=> 'Central Bank'),
		    array('type_id'=> $sp->id, 'word'=>'Insurance','permission_from'=> 'Insurance Board of Sri Lanka'),
		    array('type_id'=> $sp->id, 'word'=>'Architecture','permission_from'=> 'Sri Lanka Institute of Architects'),
		    array('type_id'=> $sp->id, 'word'=>'Architect','permission_from'=> 'Sri Lanka Institute of Architects'),
		    array('type_id'=> $sp->id, 'word'=>'Architectural','permission_from'=> 'Sri Lanka Institute of Architects'),
		    array('type_id'=> $sp->id, 'word'=>'Sri Architecturally','permission_from'=> 'Sri Lanka Institute of Architects'),
			);
		$setting = Rule::insert($data);
		
		$group = Setting::where('key','RULE_TYPE_GROUPS')->first();
        $data = array(
			array('type_id'=> $group->id, 'word'=>'Singer'),
		    array('type_id'=> $group->id, 'word'=>'Soft Logic'),
		    array('type_id'=> $group->id, 'word'=>'Unilever'),
		    array('type_id'=> $group->id, 'word'=>'Auto Miraj'),
		    array('type_id'=> $group->id, 'word'=>'Associated Motorways'),
		    array('type_id'=> $group->id, 'word'=>'L O L C'),
		    array('type_id'=> $group->id, 'word'=>'Arpico'),
		    array('type_id'=> $group->id, 'word'=>'Richard Pieris'),
		    array('type_id'=> $group->id, 'word'=>'Nawaloka'),
		    array('type_id'=> $group->id, 'word'=>'Keelles'),
		    array('type_id'=> $group->id, 'word'=>'John Keells'),
		    array('type_id'=> $group->id, 'word'=>'George Steuart'),
		    array('type_id'=> $group->id, 'word'=>'Mackwood'),
		    array('type_id'=> $group->id, 'word'=>'M D Gunasena'),
		    array('type_id'=> $group->id, 'word'=>'D Samson'),
		    array('type_id'=> $group->id, 'word'=>'Mclerance'),
		    array('type_id'=> $group->id, 'word'=>'Aitken Spence'),
		    array('type_id'=> $group->id, 'word'=>'Brandis'),
		    array('type_id'=> $group->id, 'word'=>'Siddhalepa'),
		    array('type_id'=> $group->id, 'word'=>'Hirdaramani'),
		    array('type_id'=> $group->id, 'word'=>'Hayleys'),
		    array('type_id'=> $group->id, 'word'=>'Lallan'),
		    array('type_id'=> $group->id, 'word'=>'Hemas'),
		    array('type_id'=> $group->id, 'word'=>'Dilmah'),
		);
		$setting = Rule::insert($data);
    }
}