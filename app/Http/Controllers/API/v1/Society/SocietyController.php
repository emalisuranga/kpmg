<?php

namespace App\Http\Controllers\API\v1\Society;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Society;
use App\User;
use App\Address;
use App\SocietyMember;
use App\Setting;
use App\SocietyDocument;
use App\SocietyDocumentStatus;
use App\SocietyStatus;
use App\Documents;
use App\DocumentsGroup;
use App\Http\Helper\_helper;
use Storage;
use PDF;



class SocietyController extends Controller
{
    use _helper;
    public function saveSocietyData (Request $request){

        $peopleId = User::where('email', $request->input('email'))->value('id');

        $approval;
        if($request->input('approval_need')){
            $approval = 1;
        }
        else{
            $approval = 0;
        }

        $society = new Society();
        $society->name_of_society = $request->input('name_of_society');
        $society->place_of_office = $request->input('place_of_office');
        $society->whole_of_the_objects = $request->input('whole_of_the_objects');
        $society->funds = $request->input('funds');
        $society->terms_of_admission = $request->input('terms_of_admission');
        $society->condition_under_which_any = $request->input('condition_under_which_any');
        $society->fines_and_foreitures = $request->input('fines_and_foreitures');
        $society->mode_of_holding_meetings = $request->input('mode_of_holding_meetings');
        $society->manner_of_rules = $request->input('manner_of_rules');
        $society->investment_of_funds = $request->input('investment_of_funds');
        $society->keeping_accounts = $request->input('keeping_accounts');
        $society->audit_of_the_accounts = $request->input('audit_of_the_accounts');
        $society->annual_returns = $request->input('annual_returns');
        $society->number_of_members = $request->input('number_of_members');
        $society->inspection_of_the_books = $request->input('inspection_of_the_books');
        $society->appointment_and_removal_committee = $request->input('appointment_and_removal_committee');
        $society->disputes_manner = $request->input('disputes_manner');
        $society->case_of_society = $request->input('case_of_society');
        $society->created_by = $peopleId;
        $society->name = $request->input('name');
        $society->name_si = $request->input('sinhalaName');
        $society->name_ta = $request->input('tamilname');
        $society->address = $request->input('address');
        $society->address_si = $request->input('adsinhalaName');
        $society->address_ta = $request->input('adtamilname');
        $society->type_id = 1;
        $society->approval_need = $approval;
        $society->abbreviation_desc = $request->input('abreviations');
        $society->status = $this->settings('SOCIETY_PROCESSING','key')->id;
        $society->save();
        $societyid = $society->id;


        $presidents = $request->input('presidentsArr');
        foreach($presidents as $president){
            if(!empty($president)){

                $address = new Address();
                $address->address1 = $president['localAddress1'];
                $address->address2 = $president['localAddress2'];
                $address->province = $president['province'];
                $address->district = $president['district'];
                $address->city = $president['city'];
                $address->gn_division = $president['gnDivision'];
                $address->postcode = $president['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $memb = new SocietyMember();
                $memb->address_id = $address->id;
                $memb->full_name = $president['fullname'];
                $memb->email = $president['email'];
                $memb->is_affidavit = $president['is_affidavit'];
                $memb->divisional_secretariat = $president['divisional_secretariat'];
                $memb->designation = $president['designation_soc'];
                $memb->society_id = $societyid;
                $memb->designation_type = 1;
                $memb->type = 1;
                $memb->nic = $president['nic'];
                $memb->contact_no = $president['contact_number'];
                
                $memb->save(); 


            }
            
        }

        $secretaries = $request->input('secretariesArr');
        foreach($secretaries as $secretary){
            if(!empty($secretary)){

                $address = new Address();
                $address->address1 = $secretary['localAddress1'];
                $address->address2 = $secretary['localAddress2'];
                $address->province = $secretary['province'];
                $address->district = $secretary['district'];
                $address->city = $secretary['city'];
                $address->gn_division = $secretary['gnDivision'];
                $address->postcode = $secretary['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $memb = new SocietyMember();
                $memb->address_id = $address->id;
                $memb->full_name = $secretary['fullname'];
                $memb->email = $secretary['email'];
                $memb->is_affidavit = $secretary['is_affidavit'];
                $memb->divisional_secretariat = $secretary['divisional_secretariat'];
                $memb->designation = $secretary['designation_soc'];
                $memb->society_id = $societyid;
                $memb->designation_type = 2;
                $memb->type = 1;
                $memb->nic = $secretary['nic'];
                $memb->contact_no = $secretary['contact_number'];
                
                $memb->save(); 


            }
            
        }

        $treasurers = $request->input('treasurersArr');
        foreach($treasurers as $treasurer){
            if(!empty($treasurer)){

                $address = new Address();
                $address->address1 = $treasurer['localAddress1'];
                $address->address2 = $treasurer['localAddress2'];
                $address->province = $treasurer['province'];
                $address->district = $treasurer['district'];
                $address->city = $treasurer['city'];
                $address->gn_division = $treasurer['gnDivision'];
                $address->postcode = $treasurer['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $memb = new SocietyMember();
                $memb->address_id = $address->id;
                $memb->full_name = $treasurer['fullname'];
                $memb->email = $treasurer['email'];
                $memb->is_affidavit = $treasurer['is_affidavit'];
                $memb->divisional_secretariat = $treasurer['divisional_secretariat'];
                $memb->designation = $treasurer['designation_soc'];
                $memb->society_id = $societyid;
                $memb->designation_type = 3;
                $memb->type = 1;
                $memb->nic = $treasurer['nic'];
                $memb->contact_no = $treasurer['contact_number'];
                
                $memb->save(); 


            }
            
        }

        $addits = $request->input('additsArr');
        foreach($addits as $addit){
            if(!empty($addit)){

                $address = new Address();
                $address->address1 = $addit['localAddress1'];
                $address->address2 = $addit['localAddress2'];
                $address->province = $addit['province'];
                $address->district = $addit['district'];
                $address->city = $addit['city'];
                $address->gn_division = $addit['gnDivision'];
                $address->postcode = $addit['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $memb = new SocietyMember();
                $memb->address_id = $address->id;
                $memb->full_name = $addit['fullname'];
                $memb->email = $addit['email'];
                $memb->is_affidavit = $addit['is_affidavit'];
                $memb->divisional_secretariat = $addit['divisional_secretariat'];
                $memb->designation = $addit['designation_soc'];
                $memb->society_id = $societyid;
                $memb->designation_type = 4;
                $memb->type = 1;
                $memb->nic = $addit['nic'];
                $memb->contact_no = $addit['contact_number'];
                
                $memb->save(); 


            }
            
        }

        $membs = $request->input('membsArr');
        foreach($membs as $member){
            if(!empty($member)){
                if($member['type']==1){

                $address = new Address();
                $address->address1 = $member['localAddress1'];
                $address->address2 = $member['localAddress2'];
                $address->province = $member['province'];
                $address->district = $member['district'];
                $address->city = $member['city'];
                $address->gn_division = $member['gnDivision'];
                $address->postcode = $member['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $memb = new SocietyMember();
                $memb->address_id = $address->id;
                $memb->full_name = $member['fullname'];
                $memb->email = $member['email'];
                $memb->is_affidavit = $member['is_affidavit'];
                $memb->divisional_secretariat = $member['divisional_secretariat'];
                $memb->designation = $member['designation_soc'];
                $memb->society_id = $societyid;
                $memb->designation_type = 5;
                $memb->type = 1;
                $memb->nic = $member['nic'];
                $memb->contact_no = $member['contact_number'];
                
                $memb->save();

                }
                elseif($member['type']==2){

                    $address = new Address();
                $address->address1 = $member['localAddress1'];
                $address->address2 = $member['localAddress2'];
                $address->province = $member['province'];
                $address->district = $member['district'];
                $address->city = $member['city'];
                $address->postcode = $member['postcode'];
                $address->country = $member['country'];
                $address->save(); 
                
                $memb = new SocietyMember();
                $memb->address_id = $address->id;
                $memb->full_name = $member['fullname'];
                $memb->email = $member['email'];
                $memb->is_affidavit = $member['is_affidavit'];
                $memb->divisional_secretariat = $member['divisional_secretariat'];
                $memb->designation = $member['designation_soc'];
                $memb->society_id = $societyid;
                $memb->designation_type = 5;
                $memb->type = 2;
                $memb->contact_no = $member['contact_number'];
                $memb->passport_no = $member['passport'];
                $memb->save();

                }
               
            }
            
        }
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'socID' => $societyid,
        ], 200);



    }

    // update society data using society id number...resubmitSociety
    public function updateSocietyData (Request $request){

        $approval;
        if($request->input('approval_need')){
            $approval = 1;
        }
        else{
            $approval = 0;
        }

        Society::where('id', $request->socId)
        ->update(['name_of_society' => $request->input('name_of_society'),
        'place_of_office' => $request->input('place_of_office'),
        'whole_of_the_objects' => $request->input('whole_of_the_objects'),
        'funds' => $request->input('funds'),
        'terms_of_admission' => $request->input('terms_of_admission'),
        'condition_under_which_any' => $request->input('condition_under_which_any'),
        'fines_and_foreitures' => $request->input('fines_and_foreitures'),
        'mode_of_holding_meetings' => $request->input('mode_of_holding_meetings'),
        'manner_of_rules' => $request->input('manner_of_rules'),
        'investment_of_funds' => $request->input('investment_of_funds'),
        'keeping_accounts' => $request->input('keeping_accounts'),
        'audit_of_the_accounts' => $request->input('audit_of_the_accounts'),
        'annual_returns' => $request->input('annual_returns'),
        'number_of_members' => $request->input('number_of_members'),
        'inspection_of_the_books' => $request->input('inspection_of_the_books'),
        'appointment_and_removal_committee' => $request->input('appointment_and_removal_committee'),
        'disputes_manner' => $request->input('disputes_manner'),
        'case_of_society' => $request->input('case_of_society'),
        'name' => $request->input('name'),
        'name_si' => $request->input('sinhalaName'),
        'name_ta' => $request->input('tamilname'),
        'address' => $request->input('address'),
        'address_si' => $request->input('adsinhalaName'),
        'address_ta' => $request->input('adtamilname'),
        'abbreviation_desc' => $request->input('abreviations'),
        'approval_need' => $approval]);
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
        ], 200);



    }

    // resubmit status change in society using society id number...
    public function resubmitSociety (Request $request){



        Society::where('id', $request->socId)
        ->update(['status' => $this->settings('SOCIETY_RESUBMITTED','key')->id]);
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
        ], 200);



    }


    // load society data using society id number...
    public function loadSocietyData(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Society.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $societyDetails = Society::where('id',$request->id)->first();

        

        if($societyDetails){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc society...
                'data'   => array(
                                'society'     => $societyDetails                       
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a Society.',
                'status' =>false,
            ], 200);
        }

    }

    // load society comments using society id number...
    public function loadSocietyComments(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Society.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $comments = SocietyStatus::join('settings', 'settings.id', '=', 'society_statuses.status')
            ->where('society_statuses.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
            ->where('society_statuses.society_id', $request->id)
            ->orderBy('society_statuses.id','DESC')
            ->select(
                'settings.value as name',
                'society_statuses.comments',
                'society_statuses.updated_at')
            ->limit(1)    
            ->get();

        if($comments){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc society...
                'data'   => array( 
                                'comments'    => $comments
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'No Comments Yet.',
                'status' =>false,
            ], 200);
        }

    }


    // for load individual and firm registered secretary data to profile card...
public function loadRegisteredSocietyData(Request $request){
    if($request){
        $loggedUserEmail = $request->input('loggedInUser');
        $loggedUserId = User::where('email', $loggedUserEmail)->value('id');

        $society = Society::leftJoin('settings','societies.status','=','settings.id')
                    ->leftJoin('society_certificates','societies.id','=','society_certificates.society_id')
                    ->where('societies.created_by',$loggedUserId)
                    ->orderBy('societies.id','DESC')
                    ->get(['societies.id','societies.name','societies.name_si','societies.name_ta','societies.address','societies.address_si','societies.address_ta','society_certificates.certificate_no as certificate_no','societies.abbreviation_desc','societies.approval_need','societies.created_at','settings.key as status','settings.value as value']);
      
        
       
        if($society){
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true,
                'data'   => array(
                                'society'     => $society                      
                            )
            ], 200);
        }
    }
}

// for individual society payments...
public function societyPay(Request $request){
    if(isset($request)){
        $socId = $request->socId;
        $societyPay =  array(
            'status'    => $this->settings('SOCIETY_PENDING','key')->id
        );
        Society::where('id',  $socId)->update($societyPay);
        
        return response()->json([
            'message' => 'Payment Successful.',
            'status' =>true,
        ], 200);
    }
}

//for view society pdf...
public function generate_pdf(Request $request) {

    if(isset($request->societyid) && isset($request->mainmemberid)){ 

        $societyid = $request->societyid;
        $societyRecord = Society::where('id',$societyid)->first();

        $president = SocietyMember::where('id',$request->mainmemberid)->first();
        $presidentAddRecord = Address::where('id',$president->address_id)->first();

        // $secretary = SocietyMember::where('society_id',$societyid)->where('designation_type',2)->first();
        // $secretaryAddRecord = Address::where('id',$secretary->address_id)->first();

        // $treasurer = SocietyMember::where('society_id',$societyid)->where('designation_type',3)->first();
        // $treasurerAddRecord = Address::where('id',$treasurer->address_id)->first();

        // $memberlist=array();
        // $members = SocietyMember::where('society_id',$societyid)->where('designation_type',4)->limit(5)->get();

        // foreach($members as $member)
        // {   
        //     $imember = array();

        //     $memberAddRecord = Address::where('id',$member->address_id)->first();

        //     $imember['m_full_name'] = $member->first_name." ".$member->last_name;
        //     $imember['m_personal_address'] = $memberAddRecord->address1." ".$memberAddRecord->address2." ".$memberAddRecord->city;
        //     $imember['m_district'] = $memberAddRecord->district;
        //     $imember['m_province'] = $memberAddRecord->province;
        //     $imember['m_gn_division'] = $memberAddRecord->gn_division;
        //     array_push($memberlist,$imember);
        // }

        $fieldset = array(
            'society_name' => $societyRecord->name,
            'p_full_name' => $president->full_name,
            'p_personal_address' =>$presidentAddRecord->address1." ".$presidentAddRecord->address2." ".$presidentAddRecord->city,
            'p_district' => $presidentAddRecord->district,
            'p_province' => $presidentAddRecord->province,
            'p_gn_division' => $presidentAddRecord->gn_division,
            // 's_full_name' => $secretary->first_name." ".$secretary->last_name,
            // 's_personal_address' =>$secretaryAddRecord->address1." ".$secretaryAddRecord->address2." ".$secretaryAddRecord->city,
            // 's_district' => $secretaryAddRecord->district,
            // 's_province' => $secretaryAddRecord->province,
            // 's_gn_division' => $secretaryAddRecord->gn_division,
            // 't_full_name' => $treasurer->first_name." ".$treasurer->last_name,
            // 't_personal_address' =>$treasurerAddRecord->address1." ".$treasurerAddRecord->address2." ".$treasurerAddRecord->city,
            // 't_district' =>$treasurerAddRecord->district,
            // 't_province' =>$treasurerAddRecord->province,
            // 't_gn_division' => $treasurerAddRecord->gn_division,
            // 'member'=>$memberlist,
    
        );

   
        if($request->mainmemberid){  
            $pdf = PDF::loadView('society-forms/p_affidavit',$fieldset);
            return $pdf->stream('affidavit.pdf');
        }
        // elseif($request->mainmemberid == 1){
        //     $pdf = PDF::loadView('society-forms/s_affidavit',$fieldset);
        //     return $pdf->stream('s_affidavit.pdf');
        // }elseif($request->mainmemberid == 2){
        //     $pdf = PDF::loadView('society-forms/t_affidavit',$fieldset);
        //     return $pdf->stream('t_affidavit.pdf');
        // }elseif($request->mainmemberid == 3){
        //     $pdf = PDF::loadView('society-forms/m1_affidavit',$fieldset);
        //     return $pdf->stream('m1_affidavit.pdf');
        // }elseif($request->mainmemberid == 4){
        //     $pdf = PDF::loadView('society-forms/m2_affidavit',$fieldset);
        //     return $pdf->stream('m2_affidavit.pdf');
        // }elseif($request->mainmemberid == 5){
        //     $pdf = PDF::loadView('society-forms/m3_affidavit',$fieldset);
        //     return $pdf->stream('m3_affidavit.pdf');
        // }elseif($request->mainmemberid == 6){
        //     $pdf = PDF::loadView('society-forms/m4_affidavit',$fieldset);
        //     return $pdf->stream('m4_affidavit.pdf');
        // }elseif($request->mainmemberid == 7){
        //     $pdf = PDF::loadView('society-forms/m5_affidavit',$fieldset);
        //     return $pdf->stream('m5_affidavit.pdf');
        // }
        else{            
            return response()->json([
                'message' => 'We can \'t find a Regular User.',
                'status' =>false,
            ], 200);
        }
           
    }else{            
        return response()->json([
            'message' => 'We can \'t find a User or Society.',
            'status' =>false,
        ], 200);
    }    
    
}


public function generate_App_pdf(Request $request) {

       $societyid = $request->societyid;
       $societyRecord = Society::where('id',$societyid)->first();

       $memberlist=array();
    
       $members = SocietyMember::where('society_id',$societyid)->where('is_affidavit',1)->get();
       foreach($members as $member)
       {   
           $imember = array();

           $memberAddRecord = Address::where('id',$member->address_id)->first();

           $imember['m_full_name'] = $member->full_name;
           $imember['m_des'] = $member->designation;
           $imember['m_personal_address'] = $memberAddRecord->address1." ".$memberAddRecord->address2." ".$memberAddRecord->city;
           $imember['m_contact_number'] = $member->contact_no;
           array_push($memberlist,$imember);
       }
  
       
       $fieldset = array(
            'english_name_of_society' =>$societyRecord->name,
            'name_of_society' => $societyRecord->name_of_society, 
            'place_of_office' => $societyRecord->place_of_office, 
            'whole_of_the_objects' => $societyRecord->whole_of_the_objects,
            'funds' => $societyRecord->funds, 
            'terms_of_admission' => $societyRecord->terms_of_admission, 
            'condition_under_which_any' => $societyRecord->condition_under_which_any,
            'fines_and_foreitures' => $societyRecord->fines_and_foreitures, 
            'mode_of_holding_meetings' => $societyRecord->mode_of_holding_meetings, 
            'manner_of_rules' => $societyRecord->manner_of_rules, 
            'investment_of_funds' => $societyRecord->investment_of_funds, 
            'keeping_accounts' => $societyRecord->keeping_accounts, 
            'audit_of_the_accounts' => $societyRecord->audit_of_the_accounts,
            'annual_returns' => $societyRecord->annual_returns,
            'number_of_members' => $societyRecord->number_of_members,
            'inspection_of_the_books' => $societyRecord->inspection_of_the_books,
            'appointment_and_removal_committee' => $societyRecord->appointment_and_removal_committee,
            'disputes_manner' => $societyRecord->disputes_manner, 
            'case_of_society' => $societyRecord->case_of_society,
            'member'=>$memberlist,
            

        );
    
    
    
    
     $pdf = PDF::loadView('society-forms/application',$fieldset);

    

    $pdf->stream('application.pdf');

    
}

// main 8 members load societyMemberLoad
public function societyMemberLoad(Request $request){

    if(isset($request)){
        $socID = $request->input('societyid');
        $members = SocietyMember::where('society_id',$socID)->where('society_members.is_affidavit',1)->get();


        if($members){
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true,
                'data'   => array(
                                'member'     => $members                      
                            )
            ], 200);
        }
    }

}

// main 8 members load with adresses societyMemberLoad
public function societyMemberLoadWithAddress(Request $request){

    if(isset($request)){
        $socID = $request->input('societyid');
        $members = SocietyMember::Join('addresses','society_members.address_id','=','addresses.id')
                    ->where('society_members.society_id',$socID)
                    ->get(['society_members.id','society_members.full_name','society_members.email','society_members.is_affidavit','society_members.type','society_members.divisional_secretariat','society_members.nic','society_members.contact_no','society_members.designation_type','society_members.designation','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city','addresses.district as district','addresses.province as province','addresses.country as country','addresses.postcode as postcode','addresses.gn_division as gnDivision']);


        if($members){
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true,
                'data'   => array(
                                'member'     => $members                      
                            )
            ], 200);
        }
    }
    

}


//for upload society pdf...
public function societyUploadPdf(Request $request){

    if(isset($request)){

    $fileName =  uniqid().'.pdf';
    $token = md5(uniqid());

    $socId = $request->socId;
    $docType = $request->docType;
    $pdfName = $request->filename;

    $path = 'society/'.$socId;
    $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');

    
    $docId;
    if($docType=='applicationUpload'){
        $docIdArray = Documents::where('key','SOCIETY_APPLICATION')->select('id')->first();
    $docId = $docIdArray->id;
    }elseif($docType=='affidavitUpload'){
        $docIdArray = Documents::where('key','SOCIETY_AFFIDAVIT')->select('id')->first();
    $docId = $docIdArray->id;   
    }elseif($docType=='approvalUpload'){
        $docIdArray = Documents::where('key','SOCIETY_APPROVAL_LETTER')->select('id')->first();
    $docId = $docIdArray->id;  
    }
    elseif($docType=='bankUpload'){
        $docIdArray = Documents::where('key','SOCIETY_BANK_LETTER')->select('id')->first();
    $docId = $docIdArray->id;
    }
    elseif($docType=='constitutionUpload'){
        $docIdArray = Documents::where('key','SOCIETY_CONSTITUTION')->select('id')->first();
    $docId = $docIdArray->id; 
    }
    elseif($docType=='copyUpload'){
        $docIdArray = Documents::where('key','SOCIETY_NIC_PASSPORT')->select('id')->first();
    $docId = $docIdArray->id; 
    }
    elseif($docType=='listUpload'){
        $docIdArray = Documents::where('key','SOCIETY_LIST')->select('id')->first();
    $docId = $docIdArray->id; 
    }
    elseif($docType=='otherUpload'){
        $docIdArray = Documents::where('key','SOCIETY_OTHER')->select('id')->first();
    $docId = $docIdArray->id; 
    }
    elseif($docType=='listobUpload'){
        $docIdArray = Documents::where('key','SOCIETY_OFFICE_BARER')->select('id')->first();
    $docId = $docIdArray->id; 
    }

    

    $socDoc = new SocietyDocument;
    $socDoc->document_id = $docId;
    $socDoc->society_id = $socId;
    $socDoc->name = $pdfName;
    $socDoc->member_id = $request->description;
    $socDoc->file_token = $token;
    $socDoc->path = $path;
    $socDoc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
    $socDoc->save();
    
    $socdocId = $socDoc->id;

      return response()->json([
        'message' => 'File uploaded now successfully.',
        'status' =>true,
        'name' =>basename($path),
        'doctype' =>$docType,
        'docid' =>$socdocId, // for delete pdf...
        'token' =>$token,
        'pdfname' =>$pdfName,
        'docArray' => $docId
        ], 200);

    }

}

//for update upload society pdf...
public function societyUpdateUploadPdf(Request $request){

    if(isset($request)){

    $fileName =  uniqid().'.pdf';
    $token = md5(uniqid());

    $socId = $request->socId;
    $socDocId = $request->docId;
    $docType = $request->docType;
    $pdfName = $request->filename;

    $path = 'society/'.$socId;
    $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');


    SocietyDocument::where('id', $request->docId)
        ->update(['status' => $this->settings('DOCUMENT_PENDING','key')->id,
        'name' => $pdfName,
        'member_id' => $request->description,
        'file_token' => $token,
        'path' => $path]);
    

      return response()->json([
        'message' => 'File uploaded now successfully.',
        'status' =>true,
        'name' =>basename($path),
        'doctype' =>$docType,
        'docid' =>$socDocId, // for delete pdf...
        'token' =>$token,
        'pdfname' =>$pdfName
        ], 200);

    }

}

// for load society uploaded files...
public function societyFile(Request $request){
    if(isset($request)){
        $type = $request->type;
        if($type == 'submit'){

            $socId = $request->socId;
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
        $uploadedPdf = SocietyDocument::leftJoin('documents','society_documents.document_id','=','documents.id')
                                    ->leftJoin('society_documents_status','society_documents_status.society_document_id','=','society_documents.id')
                                    ->leftJoin('settings','society_documents.status','=','settings.id')
                                   ->where('society_documents.society_id',$socId)
                                   ->get(['society_documents.id','society_documents.name','society_documents.member_id as description','society_documents.file_token','documents.key as docKey','documents.name as docname']);
        if(isset($uploadedPdf)){
            return response()->json([
                'file' => $uploadedPdf,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdf,
                )
            ], 200);

            }

        }
        elseif($type == 'resubmit'){

            $socId = $request->socId;
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
        $uploadedPdf = SocietyDocument::leftJoin('documents','society_documents.document_id','=','documents.id')
                                        ->leftJoin('society_documents_status', function ($join) {
                                            $join->on('society_documents.id', '=', 'society_documents_status.society_document_id')
                                                ->where('society_documents_status.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);})
                                        ->leftJoin('settings','society_documents.status','=','settings.id')
                                        ->where('society_documents.society_id',$socId)
                                        ->where('society_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                        ->get(['society_documents.id','society_documents.name','society_documents.member_id as description','society_documents.status','society_documents.file_token','documents.key as docKey','documents.name as docname','society_documents_status.society_document_id as society_document_id','society_documents_status.comments as comments','settings.value as value','settings.key as setKey']);
        if(isset($uploadedPdf)){
            return response()->json([
                'file' => $uploadedPdf,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdf,
                )
            ], 200);

            }

        }
        
        
        
        

    }else{
        return response()->json([
            'status' =>false,
        ], 200);
    }

}

// for load society uploaded files...
public function societyFileForResubmit(Request $request){
    if(isset($request)){
        
        $socId = $request->socId;
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
        $uploadedPdf = SocietyDocument::leftJoin('documents','society_documents.document_id','=','documents.id')
                                    ->leftJoin('society_documents_status','society_documents_status.society_document_id','=','society_documents.id')
                                    ->leftJoin('settings','society_documents.status','=','settings.id')
                                   ->where('society_documents.society_id',$socId)
                                   ->where('society_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->orWhere('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                                   ->get(['society_documents.id','society_documents.name','society_documents.member_id as description','society_documents.status','society_documents.file_token','documents.key as docKey','documents.name as docname','society_documents_status.society_document_id as society_document_id','society_documents_status.comments as comments','settings.value as value','settings.key as setKey']);
        if(isset($uploadedPdf)){
            return response()->json([
                'file' => $uploadedPdf,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdf,
                )
            ], 200);

            }
        
        

    }else{
        return response()->json([
            'status' =>false,
        ], 200);
    }

}

// for load society uploaded files comments...
public function societyFileComment(Request $request){
    if(isset($request)){
        
        $socId = $request->socId;
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);
        $uploadedPdfComment = SocietyDocumentStatus::Join('settings','society_documents_status.status','=','settings.id')
                                    ->Join('society_documents','society_documents_status.society_document_id','=','society_documents.id')
                                    ->Join('documents','documents.id','=','society_documents.document_id')
                                    ->where('society_documents.society_id',$socId)
                                    ->where('society_documents_status.status','=',$this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id)
                                    ->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                                    ->get(['society_documents_status.society_document_id','society_documents_status.comments','settings.value as value','documents.key as key']);

        if(isset($uploadedPdfComment)){
            return response()->json([
                'file' => $uploadedPdfComment,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdfComment,
                )
            ], 200);

            }
        
        

    }else{
        return response()->json([
            'status' =>false,
        ], 200);
    }

}

// to delete pdfs
function deleteSocietyPdf(Request $request){
    if(isset($request)){
    $docId = $request->documentId;
    if($docId){
        $document = SocietyDocument::where('id', $docId)->first();
        $delete = Storage::disk('sftp')->delete($document->path);
       $remove = SocietyDocument::where('id', $docId)->delete();
    }
    return response()->json([
        'message' => 'File removed successfully.',
        'status' =>true,
    ], 200);
    }
}

function deleteSocietyPdfUpdate(Request $request){
    if(isset($request)){
    $docId = $request->documentId;
    $type = $request->type;

    $docstatusid = SocietyDocumentStatus::where('society_document_id', $docId)->first();
    if($docstatusid){
        if($type =='additionalUpload'){

            $document = SocietyDocument::where('id', $docId)->first();
            if($docstatusid->status == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id){

                $delete = Storage::disk('sftp')->delete($document->path);
                SocietyDocument::where('id', $docId)
            ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);

            }
            else{

                $delete = Storage::disk('sftp')->delete($document->path);
                SocietyDocument::where('id', $docId)
            ->update(['status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);

            }      
    }
    else{

        $document = SocietyDocument::where('id', $docId)->first();
        $delete = Storage::disk('sftp')->delete($document->path);
        SocietyDocument::where('id', $docId)
            ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);
    }

    }
    else{
        $document = SocietyDocument::where('id', $docId)->first();
        $delete = Storage::disk('sftp')->delete($document->path);
        $remove = SocietyDocument::where('id', $docId)->delete();
    }
    return response()->json([
        'message' => 'File emptied successfully.',
        'status' =>true,
    ], 200);
    }
}

function getPathConstitution(Request $request){
    if(isset($request)){
    


    return response()->json([
        'message' => 'File removed successfully.',
        'status' =>true,
        'path' => asset('other/constitution.pdf'),
        'path1' => asset('other/STANDARD_CLAUSES.pdf')
    ], 200);
    }
}


}


