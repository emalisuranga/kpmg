<?php
namespace App\Http\Controllers\API\v1\MemberChange;
use App\Http\Controllers\Controller;
use App\CompanyChangeRequestItem;
use Illuminate\Http\Request;
use App\Http\Helper\_helper;
use App\CompanyItemChange;
use App\CompanyDocuments;
use App\CompanyPostfix;
use App\CompanyMember;
use App\CompanyFirms;
use App\CourtCase;
use App\ShareGroup;
use App\Documents;
use App\CompanyDocumentStatus;
use App\Secretary;
use App\SecretaryFirm;
use App\SecretaryCertificate;
use App\Address;
use App\Setting;
use App\Company;
use App\CompanyStatus;
use App\CompanyCertificate;
use App\Country;
use App\People;
use App\Share;
use App\Order;
use App\User;
use Storage;
use Cache;
use App;
use URL;
use PDF;


class DirectorSecretaryController extends Controller
{
    use _helper;

    // for load all company director secretary details...
    public function loadMemberData (Request $request){

        if(!(($request->companyId)&&($request->email))){
            return response()->json([
                'message' => 'We can \'t find members',
                'status' =>false,
            ], 200);
        }elseif(($request->companyId)&&($request->email)){

            $update_compnay_updated_at = array(
                'updated_at' => date('Y-m-d H:i:s', time())
            );
            Company::where('id', $request->companyId)
            ->update($update_compnay_updated_at);
                   
            //$loggedUserEmail = $request->email;
            $loggedUserEmail = $this->clearEmail($request->email);
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
            $createdUserId = Company::where('id', $request->companyId)->value('created_by');

            $companyInfo = Company::where('id', $request->companyId)->first();
            $incoDate = $companyInfo->incorporation_at;
            $companyType = $this->settings($companyInfo->type_id,'id');

            if($companyType->key == 'COMPANY_TYPE_OVERSEAS' || $companyType->key == 'COMPANY_TYPE_OFFSHORE') {

                return response()->json([
                    'message' => 'company type not allowed.',
                    'status' =>false,
                    'data' => array(
                        'companytypeValid' => false
                    ),
                   
                ], 200);
    
            }

            $inArray1 = array(
                $this->settings('DERECTOR', 'key')->id,
                $this->settings('SECRETARY', 'key')->id
            );
    
    
            $membs = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
            ->where('company_members.company_id',$request->companyId)
            ->where('company_members.email',$loggedUserEmail)
            ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->whereIn('company_members.designation_type', $inArray1)
            ->get();
    
            $membfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
            ->where('company_member_firms.company_id',$request->companyId)
            ->where('company_member_firms.email',$loggedUserEmail)
            ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->where('company_member_firms.type_id', $this->settings('SECRETARY','key')->id)
            ->get();
    
            if( !( (count($membs) > 0) || (count($membfirms) > 0) ) ) {
                return response()->json([
                    'message' => 'Invalid Profile for this company.',
                    'status' =>false,
                    'data' => array(
                        'createrValid' => false
                    ),
                   
                ], 200);
            }

           // if($loggedUserId === $createdUserId){
            if(true){
                $changeRequestID = '';
                $changeRequest = CompanyChangeRequestItem::where('company_id', $request->companyId)
                                                           ->where('request_type', $this->settings('DIRECTOR_SECRETORY_CHANGE','key')->id)
                                                           ->whereNotIn('status', array(
                                                                                $this->settings('COMPANY_CHANGE_APPROVED','key')->id,
                                                                                $this->settings('COMPANY_CHANGE_REJECTED','key')->id,
                                                                               
                                                                                
                                                                    ) 
                                                            )
                                                         ->first();
                if(!isset($changeRequest->id)){
                    $createChangeReqItem = new CompanyChangeRequestItem();
                    $createChangeReqItem->company_id = $request->companyId;
                    $createChangeReqItem->request_by = $loggedUserId;
                    $createChangeReqItem->request_type =  $this->settings('DIRECTOR_SECRETORY_CHANGE','key')->id;
                    $createChangeReqItem->status =  $this->settings('COMPANY_CHANGE_PROCESSING','key')->id;
                    $createChangeReqItem->save();
                    $changeRequestID = $createChangeReqItem->id;
                }else{
                    $changeRequestID = CompanyChangeRequestItem::where('company_id', $request->companyId)
                                                                ->where('request_type', $this->settings('DIRECTOR_SECRETORY_CHANGE','key')->id)
                                                                ->whereNotIn('status', array(
                                                                                    $this->settings('COMPANY_CHANGE_APPROVED','key')->id,
                                                                                    $this->settings('COMPANY_CHANGE_REJECTED','key')->id,
                                                                                    
                                                                                    
                                                                        ) 
                                                                   )
                                                               ->value('id');
                }   

                $requestInfo = CompanyChangeRequestItem::where('company_id', $request->companyId)
                                                        ->where('request_type', $this->settings('DIRECTOR_SECRETORY_CHANGE','key')->id)
                                                        ->whereNotIn('status', array(
                                                                                    $this->settings('COMPANY_CHANGE_APPROVED','key')->id,
                                                                                    $this->settings('COMPANY_CHANGE_REJECTED','key')->id,
                                                                                    
                                                                                    
                                                                        ) 
                                                        )->first();
                                                            
                if($requestInfo->status == $this->settings('COMPANY_CHANGE_RESUBMISSION','key')->id){
                    $changeRequest = CompanyChangeRequestItem::leftJoin('settings','company_change_requests.signed_by_table_type','=','settings.id')->where('company_change_requests.id',$changeRequestID)->get(['company_change_requests.signed_by','settings.key as tableType']);
                    $signedby = $changeRequest[0]['signed_by'];
                    $signedbytype = $changeRequest[0]['tableType'];
                }
                else{
                    $signedby = null;
                    $signedbytype = null;
                }                                        
                
                

                $directorList = CompanyMember::where('company_id',$request->companyId)
                                             ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                             ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                               ->get();

                $secretaryList = CompanyMember::where('company_id',$request->companyId)
                                              ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                              ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                ->get();

                $secretaryFirmList = CompanyFirms::where('company_id',$request->companyId)
                                                 ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                 ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                   ->get();

                $oldDirectorList = CompanyMember::where('company_id',$request->companyId)
                                                ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                // ->where(function ($query) {
                                                //     $query->where('ceased_reason', '=', '')
                                                //         ->orWhere('ceased_reason', '=', NULL);
                                                //         })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                ->get();
                                                //  ->get(['id','first_name','last_name','email']);
                                                //->whereIn('ceased_reason', [null,''])

                $oldSecretaryList = CompanyMember::where('company_id',$request->companyId)
                                                 ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                                //  ->where(function ($query) {
                                                //      $query->where('ceased_reason', '=', '')
                                                //          ->orWhere('ceased_reason', '=', NULL);
                                                //         })
                                                 ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                 ->get();
                                                  // ->get(['id','first_name','last_name','email']);

                $oldSecretaryFirmList = CompanyFirms::where('company_id',$request->companyId)
                                                    ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                    // ->where(function ($query) {
                                                    //     $query->where('ceased_reason', '=', '')
                                                    //         ->orWhere('ceased_reason', '=', NULL);
                                                    //         })
                                                    ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                    ->get();
                                                    //  ->get(['id','name']);


             ////////////// signed by array bulding ////////////////////////// 
                

                $oldMemberList = CompanyMember::where('company_id',$request->companyId)
                                                ->where('designation_type','!=',$this->settings('SHAREHOLDER','key')->id)
                                                // ->where(function ($query) {
                                                //     $query->where('ceased_reason', '=', '')
                                                //         ->orWhere('ceased_reason', '=', NULL);
                                                //         })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                ->get();
                $oldSecretaryfirmList = CompanyFirms::where('company_id',$request->companyId)
                                                    ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                    // ->where(function ($query) {
                                                    //     $query->where('ceased_reason', '=', '')
                                                    //         ->orWhere('ceased_reason', '=', NULL);
                                                    //         })
                                                    ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                    ->get();

                $newMembers = CompanyMember::leftJoin('addresses','company_members.address_id','=','addresses.id')
                                           ->leftJoin('settings','company_members.designation_type','=','settings.id')
                                              ->where('company_members.company_id',$request->companyId)
                                              ->where('company_members.designation_type','!=',$this->settings('SHAREHOLDER','key')->id)
                                              ->where('company_members.status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                ->get(['company_members.id','company_members.first_name','company_members.last_name','company_members.designation_type','company_members.email','company_members.date_of_appointment','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city','settings.value as value']);

                $newMemberFirms = CompanyFirms::leftJoin('addresses','company_member_firms.address_id','=','addresses.id')
                                              ->leftJoin('settings','company_member_firms.type_id','=','settings.id')
                                                 ->where('company_member_firms.company_id',$request->companyId)
                                                 ->where('company_member_firms.type_id','!=',$this->settings('SHAREHOLDER','key')->id)
                                                 ->where('company_member_firms.status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                   ->get(['company_member_firms.id','company_member_firms.name','company_member_firms.email','company_member_firms.date_of_appointment','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city','settings.value as value']);
                                                                    
                
                $date = array();

                // foreach ($newMembers as $key => $value) {
                //     $date[] = [
                //           "id" => $value->id,
                //           "_id" => $value->id .'-'. 0,
                //           'type' => 0,
                //           "title" => $value->title,
                //           "first_name" => $value->first_name,
                //           "name" => $value->first_name .' '. $value->last_name,
                //           "last_name" => $value->last_name,
                //           "designation" => ($value->designation_type == $this->settings('SECRETARY','key')->id) ? 'Secretary' : 'Director',
                //     ];
                //   }
      
                //   foreach ($newMemberFirms as $key => $value) {
                //     $date[] = [
                //           "id" => $value->id,
                //           "_id" => $value->id .'-'. 1,
                //           'type' => 1,
                //           "title" => '',
                //           "first_name" => $value->name,
                //           "name" => $value->name,
                //           "last_name" => '',
                //           "designation" => 'Secretary Firm',
                //     ];
                //   }

                foreach($oldMemberList as $member){

                    $isMemberEdited = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('old_record_id',$member['id'])
                                                      ->first();

                    $isMemberDeleted = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$member['id'])
                                                      ->first();                                  
                    // $directorID =   $director['id'];
                    // $newdirectorID =   null;
                    // $isdeleted =   null;

                    if($isMemberEdited){

                        $newEditedMember = CompanyMember::where('id',$isMemberEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        $member =   $newEditedMember;

                        // $directorID =   $isDirectorEdited->old_record_id;
                        // $newdirectorID =   $newEditedDirector->id;                    

                    }
                    if($isMemberDeleted){

                        continue;                    

                    }
                    
                    
                    $address ='';
                    $forAddress = '';
                    if( $member->address_id) {
                       $address = Address::where('id',$member->address_id)->first();
                    }
                    if( $member->foreign_address_id) {
                       $forAddress = Address::where('id', $member->foreign_address_id)->first();
                    }

                    

              $date[] = [
                    "id" => $member->id,
                    "_id" => $member->id .'-'. 0,
                    'type' => 0,
                    "title" => $member->title,
                    "first_name" => $member->first_name,
                    "name" => $member->first_name .' '. $member->last_name,
                    "last_name" => $member->last_name,
                    "designation" => ($member->designation_type == $this->settings('SECRETARY','key')->id) ? 'Secretary' : 'Director',
              ];
        }
        foreach($oldSecretaryfirmList as $sec){
                    $isSecEdited = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('old_record_id',$sec['id'])
                                                      ->first();
                    $isSecDeleted = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('item_id',$sec['id'])
                                                      ->first();  

                    // $secID =   $sec['id'];
                    // $newsecID =   null;
                    // $isdeleted =   null;

                    if($isSecEdited){

                        $newEditedSec = CompanyFirms::where('id',$isSecEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        $sec =   $newEditedSec;

                        // $secID =   $isSecEdited->old_record_id;
                        // $newsecID =   $newEditedSec->id;                    

                    }
                    if($isSecDeleted){

                        continue;                    

                    }

                    

                    $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;
                    if(!$sec->foreign_address_id){
                        $address = Address::where('id',$address_id)->first();
                    }else{
                    $address = Address::where('id',$address_id)->first();
                    }

                    $date[] = [
                        "id" => $sec->id,
                        "_id" => $sec->id .'-'. 1,
                        'type' => 1,
                        "title" => '',
                        "first_name" => $sec->name,
                        "name" => $sec->name,
                        "last_name" => '',
                        "designation" => 'Secretary Firm',
                  ];
                }                                
                                               
                ///// signed by array bulding end     ////////////////////////                               

                $company_info = Company::where('id',$request->companyId)->first();
                $companyType = $this->settings($company_info->type_id,'id');
                $dir_count = 0;
                $director_as_sh_count =0;
                $director_as_sec_count = 0;
                $directors = array();
                $olddirectors = array();

                foreach($oldDirectorList as $director){

                    $isDirectorEdited = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('old_record_id',$director['id'])
                                                      ->first();

                    $isDirectorDeleted = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$director['id'])
                                                      ->first();                                  
                    $directorID =   $director['id'];
                    $newdirectorID =   null;
                    $isdeleted =   null;
                    if($isDirectorEdited){

                        $newEditedDirector = CompanyMember::where('id',$isDirectorEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        $director =   $newEditedDirector;
                        $directorID =   $isDirectorEdited->old_record_id;
                        $newdirectorID =   $newEditedDirector->id;                    

                    }
                    if($isDirectorDeleted){

                        $isdeleted =   true;                    

                    }               
                    $dir_count++;                            
                    $director_nic_or_pass = ($director->is_srilankan  =='yes') ? $director->nic : $director->passport_no;
                    $director_nic_or_pass_field = ($director->is_srilankan  =='yes') ? 'nic' : 'passport_no';
       
                    //director as a secrotory list
                    $directors_as_sec = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                               ->where('company_id', $request->companyId)
                                               ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                               ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                               ->get()
                                               ->count();                            
                   if($directors_as_sec){
                       $director_as_sec_count ++;
                   }                         
       
                   //director as a shareholder list
                   $directors_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                              ->where('company_id', $request->companyId)
                                              ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                              ->where('is_beneficial_owner','no')
                                              ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                              ->get()
                                              ->count();
                    
                    if($directors_as_sh){
                        $director_as_sh_count ++;
                    } 
                    $address ='';
                    $forAddress = '';
                    if( $director->address_id) {
                       $address = Address::where('id',$director->address_id)->first();
                    }
                    if( $director->foreign_address_id) {
                       $forAddress = Address::where('id', $director->foreign_address_id)->first();
                    }               
                    $can_director_as_sec = true;
                    $sec_reg_no = '';
       
                   if( $director->nic  && ( $companyType->key =='COMPANY_TYPE_PUBLIC' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_32' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_34' ))  {                               
                      
                       $members_sec_nic_lower =Secretary::where('nic', strtolower($director->nic))->first();
                       $members_sec_nic_lowercount = Secretary::where('nic', strtolower($director->nic))->count();                       
                       $members_sec_nic_upper =Secretary::where('nic', strtoupper($director->nic))->first();
                       $members_sec_nic_uppercount = Secretary::where('nic',strtoupper($director->nic))->count();                       
                       $members_sec = ($members_sec_nic_lowercount ) ? $members_sec_nic_lower : $members_sec_nic_upper;                
                       $sec_reg_no = isset($members_sec->certificate_no) && $members_sec->certificate_no  ? $members_sec->certificate_no : '';
                       $can_director_as_sec = ($sec_reg_no) ? true : false;
                   }               
                    $rec = array(
                       'id' => $directorID,
                       'newid' => $newdirectorID,
                       'isdeleted' => $isdeleted,
                       'type' => ($director->is_srilankan  =='yes' ) ? 'local' : 'foreign',               
                       'firstname' => $director->first_name,
                       'lastname' => $director->last_name,
                       'title' => $director->title,               
                       'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                       'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                       'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                       'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                       'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                       'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',               
                       'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                       'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                       'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                       'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                       'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',               
                       'nic'       => $director->nic,
                       'passport'  => $director->passport_no,
                        // 'country'   =>($address->country) ? $address->country : '',
                       'country'  => ( $director->foreign_address_id)  ? $forAddress->country : $address->country,
                       'passport_issued_country'   => $director->passport_issued_country,
                       // 'share'     => $director->no_of_shares, 
                       'date'      => '1970-01-01' == $director->date_of_appointment ? null : $director->date_of_appointment,
                       'changedate'      => '1970-01-01' == $director->date_of_change ? null : $director->date_of_change,
                       'phone' => $director->telephone,
                       'mobile' => $director->mobile,
                       'email' => $director->email,
                       'occupation' => $director->occupation,
                       'directors_as_sec' =>$directors_as_sec,
                       'directors_as_sh' => $directors_as_sh,
                       'can_director_as_sec' => $can_director_as_sec,
                       'secRegDate' => $sec_reg_no                              
                    );
                    $olddirectors[] = $rec;
        }
                foreach($directorList as $director){               
                            $dir_count++;                            
                            $director_nic_or_pass = ($director->is_srilankan  =='yes') ? $director->nic : $director->passport_no;
                            $director_nic_or_pass_field = ($director->is_srilankan  =='yes') ? 'nic' : 'passport_no';
               
                            //director as a secrotory list
                            $directors_as_sec = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->get()
                                                       ->count();                            
                           if($directors_as_sec){
                               $director_as_sec_count ++;
                           }                         
               
                           //director as a shareholder list
                           $directors_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                      ->where('company_id', $request->companyId)
                                                      ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                                      ->where('is_beneficial_owner','no')
                                                      ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                      ->get()
                                                      ->count();
                            
                            if($directors_as_sh){
                                $director_as_sh_count ++;
                            } 
                            $address ='';
                            $forAddress = '';
                            if( $director->address_id) {
                               $address = Address::where('id',$director->address_id)->first();
                            }
                            if( $director->foreign_address_id) {
                               $forAddress = Address::where('id', $director->foreign_address_id)->first();
                            }               
                            $can_director_as_sec = true;
                            $sec_reg_no = '';
               
                           if( $director->nic  && ( $companyType->key =='COMPANY_TYPE_PUBLIC' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_32' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_34' ))  {                               
                              
                               $members_sec_nic_lower =Secretary::where('nic', strtolower($director->nic))->first();
                               $members_sec_nic_lowercount = Secretary::where('nic', strtolower($director->nic))->count();                       
                               $members_sec_nic_upper =Secretary::where('nic', strtoupper($director->nic))->first();
                               $members_sec_nic_uppercount = Secretary::where('nic',strtoupper($director->nic))->count();                       
                               $members_sec = ($members_sec_nic_lowercount ) ? $members_sec_nic_lower : $members_sec_nic_upper;                
                               $sec_reg_no = isset($members_sec->certificate_no) && $members_sec->certificate_no  ? $members_sec->certificate_no : '';
                               $can_director_as_sec = ($sec_reg_no) ? true : false;
                           }               
                            $rec = array(
                               'id' => $director['id'],
                               'type' => ($director->is_srilankan  =='yes' ) ? 'local' : 'foreign',               
                               'firstname' => $director->first_name,
                               'lastname' => $director->last_name,
                               'title' => $director->title,               
                               'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                               'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                               'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                               'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                               'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                               'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',               
                               'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                               'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                               'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                               'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                               'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',               
                               'nic'       => $director->nic,
                               'passport'  => $director->passport_no,
                                // 'country'   =>($address->country) ? $address->country : '',
                               'country'  => ( $director->foreign_address_id)  ? $forAddress->country : $address->country,
                               'passport_issued_country'   => $director->passport_issued_country,
                               // 'share'     => $director->no_of_shares, 
                               'date'      => '1970-01-01' == $director->date_of_appointment ? null : $director->date_of_appointment,
                               'phone' => $director->telephone,
                               'mobile' => $director->mobile,
                               'email' => $director->email,
                               'occupation' => $director->occupation,
                               'directors_as_sec' =>$directors_as_sec,
                               'directors_as_sh' => $directors_as_sh,
                               'can_director_as_sec' => $can_director_as_sec,
                               'secRegDate' => $sec_reg_no                              
                            );
                            $directors[] = $rec;
                }
                $sec_count =0;
                $secs = array();
                $oldsecs = array();

                foreach($oldSecretaryList as $sec){
                    $isSecEdited = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('old_record_id',$sec['id'])
                                                      ->first();
                    $isSecDeleted = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$sec['id'])
                                                      ->first();                                  
                    $secID =   $sec['id'];
                    $newsecID =   null;
                    $isdeleted =   null;
                    if($isSecEdited){

                        $newEditedSec = CompanyMember::where('id',$isSecEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        $sec =   $newEditedSec;
                        $secID =   $isSecEdited->old_record_id;
                        $newsecID =   $newEditedSec->id;                    

                    }
                    if($isSecDeleted){

                        $isdeleted =   true;                    

                    }

                    $sec_nic_or_pass = ($sec->is_srilankan  =='yes') ? $sec->nic : $sec->passport_no;
                    $sec_nic_or_pass_field = ($sec->is_srilankan  =='yes') ? 'nic' : 'passport_no';         
                    $sec_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                        ->where('company_id', $request->companyId)
                        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                        ->where('nic',$sec->nic)
                        ->whereNull('company_member_firm_id' )
                        ->get();
                    $sec_as_sh_count = $sec_as_sh->count();
                    $sec_sh_comes_from_director = false;
                    $sec_count ++;     
                    $address ='';
                    $forAddress = '';
                    if( $sec->address_id) {
                        $address = Address::where('id',$sec->address_id)->first();
                    }
                    if( $sec->foreign_address_id) {
                        $forAddress = Address::where('id', $sec->foreign_address_id)->first();
                    }            
                    $firm_info = array();
                    if($sec->company_member_firm_id){
                        $firm_info = CompanyFirms::where('id',$sec->company_member_firm_id)->first();            
                        $firm_address = Address::where('id', $firm_info->address_id)->first();            
                        $firm_info['address']=$firm_address;            
                    }            
                    $rec = array(
                    'id' => $secID,
                    'newid' => $newsecID,
                    'isdeleted' => $isdeleted,
                    'type' => ($sec->is_srilankan =='yes' ) ? 'local' : 'foreign',
                    'firstname' => $sec->first_name,
                    'lastname' => $sec->last_name,
                    'title' => $sec->title,
                    'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                    'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                    'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                    'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                    'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                    'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
                    'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                    'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                    'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                    'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                    'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
                    'nic'       => $sec->nic,
                    'passport'  => $sec->passport_no,
                    // 'country'   =>($address->country) ? $address->country : '',
                    'country'  => ( $sec->foreign_address_id && isset( $forAddress->country) )  ? $forAddress->country : $address->country,
                    //  'country'  => ( $sec->foreign_address_id && isset( $forAddress->country) ) ? 'test' : 'fail',
                    'passport_issued_country'   => $sec->passport_issued_country,
                    //'share'     =>0,
                    'date'      => '1970-01-01' == $sec->date_of_appointment ? null : $sec->date_of_appointment,
                    'changedate'      => '1970-01-01' == $sec->date_of_change ? null : $sec->date_of_change,
                    'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
                    'regDate'      => ($sec->is_registered_secretary =='yes' || $companyType->key =='COMPANY_TYPE_PUBLIC' ) ? $sec->secretary_registration_no :'',
                    'phone' => $sec->telephone,
                    'mobile' => $sec->mobile,
                    'email' => $sec->email,
                    'occupation' => $sec->occupation,
                    //  'secType' => ( $sec->is_natural_person == 'yes') ? 'natural' : 'firm',
                    'secType' => 'natural',
                    'secCompanyFirmId' => $sec->company_member_firm_id,
                    'sec_as_sh' => $sec_as_sh_count,
                    'sec_sh_comes_from_director' => $sec_sh_comes_from_director,
                    'firm_info' =>$firm_info,
                    'pvNumber' => ($sec->company_member_firm_id) ? $firm_info['registration_no'] : '',
                    'firm_name' => ($sec->company_member_firm_id) ? $firm_info['name'] : '',
                    'firm_province' => ($sec->company_member_firm_id) ? $firm_address['province'] : '',
                    'firm_district' => ($sec->company_member_firm_id) ? $firm_address['district'] : '',
                    'firm_city' => ($sec->company_member_firm_id) ? $firm_address['city'] : '',
                    'firm_localAddress1' => ($sec->company_member_firm_id) ? $firm_address['address1'] : '',
                    'firm_localAddress2' => ($sec->company_member_firm_id) ? $firm_address['address2'] : '',
                    'firm_postcode' => ($sec->company_member_firm_id) ? $firm_address['postcode'] : ''            
                    );
                    $oldsecs[] = $rec;
                }
                foreach($secretaryList as $sec){        
                    $sec_nic_or_pass = ($sec->is_srilankan  =='yes') ? $sec->nic : $sec->passport_no;
                    $sec_nic_or_pass_field = ($sec->is_srilankan  =='yes') ? 'nic' : 'passport_no';         
                    $sec_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                        ->where('company_id', $request->companyId)
                        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                        ->where('nic',$sec->nic)
                        ->whereNull('company_member_firm_id' )
                        ->get();
                    $sec_as_sh_count = $sec_as_sh->count();
                    $sec_sh_comes_from_director = false;
                    $sec_count ++;     
                    $address ='';
                    $forAddress = '';
                    if( $sec->address_id) {
                        $address = Address::where('id',$sec->address_id)->first();
                    }
                    if( $sec->foreign_address_id) {
                        $forAddress = Address::where('id', $sec->foreign_address_id)->first();
                    }            
                    $firm_info = array();
                    if($sec->company_member_firm_id){
                        $firm_info = CompanyFirms::where('id',$sec->company_member_firm_id)->first();            
                        $firm_address = Address::where('id', $firm_info->address_id)->first();            
                        $firm_info['address']=$firm_address;            
                    }            
                    $rec = array(
                    'id' => $sec['id'],
                    'type' => ($sec->is_srilankan =='yes' ) ? 'local' : 'foreign',
                    'firstname' => $sec->first_name,
                    'lastname' => $sec->last_name,
                    'title' => $sec->title,
                    'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                    'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                    'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                    'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                    'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                    'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
                    'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                    'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                    'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                    'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                    'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
                    'nic'       => $sec->nic,
                    'passport'  => $sec->passport_no,
                    // 'country'   =>($address->country) ? $address->country : '',
                    'country'  => ( $sec->foreign_address_id && isset( $forAddress->country) )  ? $forAddress->country : $address->country,
                    //  'country'  => ( $sec->foreign_address_id && isset( $forAddress->country) ) ? 'test' : 'fail',
                    'passport_issued_country'   => $sec->passport_issued_country,
                    //'share'     =>0,
                    'date'      => '1970-01-01' == $sec->date_of_appointment ? null : $sec->date_of_appointment,
                    'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
                    'regDate'      => ($sec->is_registered_secretary =='yes' || $companyType->key =='COMPANY_TYPE_PUBLIC' ) ? $sec->secretary_registration_no :'',
                    'phone' => $sec->telephone,
                    'mobile' => $sec->mobile,
                    'email' => $sec->email,
                    'occupation' => $sec->occupation,
                    //  'secType' => ( $sec->is_natural_person == 'yes') ? 'natural' : 'firm',
                    'secType' => 'natural',
                    'secCompanyFirmId' => $sec->company_member_firm_id,
                    'sec_as_sh' => $sec_as_sh_count,
                    'sec_sh_comes_from_director' => $sec_sh_comes_from_director,
                    'firm_info' =>$firm_info,
                    'pvNumber' => ($sec->company_member_firm_id) ? $firm_info['registration_no'] : '',
                    'firm_name' => ($sec->company_member_firm_id) ? $firm_info['name'] : '',
                    'firm_province' => ($sec->company_member_firm_id) ? $firm_address['province'] : '',
                    'firm_district' => ($sec->company_member_firm_id) ? $firm_address['district'] : '',
                    'firm_city' => ($sec->company_member_firm_id) ? $firm_address['city'] : '',
                    'firm_localAddress1' => ($sec->company_member_firm_id) ? $firm_address['address1'] : '',
                    'firm_localAddress2' => ($sec->company_member_firm_id) ? $firm_address['address2'] : '',
                    'firm_postcode' => ($sec->company_member_firm_id) ? $firm_address['postcode'] : ''            
                    );
                    $secs[] = $rec;
                }
                
                $sec_firm_count =0;
                $oldsecs_firms = array();
                foreach($oldSecretaryFirmList as $sec){
                    $isSecEdited = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('old_record_id',$sec['id'])
                                                      ->first();
                    $isSecDeleted = CompanyItemChange::where('request_id',$requestInfo->id)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('item_id',$sec['id'])
                                                      ->first();                                  
                    $secID =   $sec['id'];
                    $newsecID =   null;
                    $isdeleted =   null;
                    if($isSecEdited){

                        $newEditedSec = CompanyFirms::where('id',$isSecEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        $sec =   $newEditedSec;
                        $secID =   $isSecEdited->old_record_id;
                        $newsecID =   $newEditedSec->id;                    

                    }
                    if($isSecDeleted){

                        $isdeleted =   true;                    

                    }

                    $sec_as_sh_count =  ( intval( $sec->sh_firm_of ) > 0 )  ? 1 : 0 ;
                    $sec_firm_count++;
                    $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;
                    if(!$sec->foreign_address_id){
                        $address = Address::where('id',$address_id)->first();
                    }else{
                    $address = Address::where('id',$address_id)->first();
                    }
                    $rec = array(
                    'id' => $secID,
                    'newid' => $newsecID,
                    'isdeleted' => $isdeleted,
                    'type' => ($address->country != 'Sri Lanka') ? 'foreign' : 'local',
                    'pvNumber' => $sec->registration_no,
                    'firm_name' => $sec->name,
                    'firm_province' =>  ( $address->province) ? $address->province : '',
                    'firm_district' =>  ($address->district) ? $address->district : '',
                    'firm_city' =>  ( $address->city) ? $address->city : '',
                    'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
                    'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
                    'firm_country'      => ($address->country) ? $address->country : '',
                    'firm_postcode' => ($address->postcode) ? $address->postcode : '',
                    'firm_email' => $sec->email,
                    'firm_phone' => $sec->phone,
                    'firm_mobile' => $sec->mobile,
                    'firm_date'  => $sec->date_of_appointment,
                    'firm_date_change'  => $sec->date_of_change,
                    'sec_as_sh' => $sec_as_sh_count,
                    'secType' => 'firm',
                    'secBenifList' => array(
                        'ben' => array()
                    )
                    );
                    $oldsecs_firms[] = $rec;
                }

                $sec_firm_count =0;
                $secs_firms = array();
                foreach($secretaryFirmList as $sec){
                    $sec_as_sh_count =  ( intval( $sec->sh_firm_of ) > 0 )  ? 1 : 0 ;
                    $sec_firm_count++;
                    $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;
                    if(!$sec->foreign_address_id){
                        $address = Address::where('id',$address_id)->first();
                    }else{
                    $address = Address::where('id',$address_id)->first();
                    }
                    $rec = array(
                    'id' => $sec['id'],
                    'type' => ($address->country != 'Sri Lanka') ? 'foreign' : 'local',
                    'pvNumber' => $sec->registration_no,
                    'firm_name' => $sec->name,
                    'firm_province' =>  ( $address->province) ? $address->province : '',
                    'firm_district' =>  ($address->district) ? $address->district : '',
                    'firm_city' =>  ( $address->city) ? $address->city : '',
                    'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
                    'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
                    'firm_country'      => ($address->country) ? $address->country : '',
                    'firm_postcode' => ($address->postcode) ? $address->postcode : '',
                    'firm_email' => $sec->email,
                    'firm_phone' => $sec->phone,
                    'firm_mobile' => $sec->mobile,
                    'firm_date'  => $sec->date_of_appointment,
                    'sec_as_sh' => $sec_as_sh_count,
                    'secType' => 'firm',
                    'secBenifList' => array(
                        'ben' => array()
                    )
                    );
                    $secs_firms[] = $rec;
                }

                $case = CourtCase::where('company_court_cases.company_id',$request->companyId)
            ->where('company_court_cases.request_id',$changeRequestID)
            ->first();

                $countryList= Cache::rememberForever('countryList', function () {
                                                    return Country::all();
                                                         });


                $core_groups_list = array();
                $core_groups = ShareGroup::where('type','core_share')
                                         ->where('company_id', $request->companyId)->get();
                    if(count($core_groups)){
                        foreach($core_groups as $g ){        
                            $grec = array(
                                'group_id' => $g->id,
                                'group_name' => "$g->name ($g->no_of_shares)"
                                        );
                    $core_groups_list[] = $grec;
                        }
                    }

                    $form18Cost;
                    $form19Cost;
                    $form20Cost = $this->settings('PAYMENT_FORM20','key')->value;
                    $form18CostKey;
                    $form19CostKey;
                    if($companyType->key =='COMPANY_TYPE_PUBLIC'){
                        $form18Cost = $this->settings('PAYMENT_PUBLIC_FORM18','key')->value;
                        $form19Cost = $this->settings('PAYMENT_PUBLIC_FORM19','key')->value;
                        $form18CostKey = 'PAYMENT_PUBLIC_FORM18';
                        $form19CostKey = 'PAYMENT_PUBLIC_FORM19';                      
                    }else if($companyType->key =='COMPANY_TYPE_PRIVATE'){
                        $form18Cost = $this->settings('PAYMENT_PRIVATE_FORM18','key')->value;
                        $form19Cost = $this->settings('PAYMENT_PRIVATE_FORM19','key')->value;
                        $form18CostKey = 'PAYMENT_PRIVATE_FORM18';
                        $form19CostKey = 'PAYMENT_PRIVATE_FORM19';                       
                    }else if($companyType->key =='COMPANY_TYPE_UNLIMITED'){
                        $form18Cost = $this->settings('PAYMENT_UNLIMITED_FORM18','key')->value;
                        $form19Cost = $this->settings('PAYMENT_UNLIMITED_FORM19','key')->value; 
                        $form18CostKey = 'PAYMENT_UNLIMITED_FORM18';
                        $form19CostKey = 'PAYMENT_UNLIMITED_FORM19';                 
                    }else if($companyType->key =='COMPANY_TYPE_GUARANTEE_32'){
                        $form18Cost = $this->settings('PAYMENT_GA32_FORM18','key')->value;
                        $form19Cost = $this->settings('PAYMENT_GA32_FORM19','key')->value;
                        $form18CostKey = 'PAYMENT_GA32_FORM18';
                        $form19CostKey = 'PAYMENT_GA32_FORM19';                   
                    }else if($companyType->key =='COMPANY_TYPE_GUARANTEE_34'){
                        $form18Cost = $this->settings('PAYMENT_GA34_FORM18','key')->value;
                        $form19Cost = $this->settings('PAYMENT_GA34_FORM19','key')->value;
                        $form18CostKey = 'PAYMENT_GA34_FORM18';
                        $form19CostKey = 'PAYMENT_GA34_FORM19';                   
                    }else if($companyType->key =='COMPANY_TYPE_OVERSEAS'){
                        $form18Cost = $this->settings('PAYMENT_PUBLIC_FORM18','key')->value;
                        $form19Cost = $this->settings('PAYMENT_PUBLIC_FORM19','key')->value; 
                        $form18CostKey = 'PAYMENT_PUBLIC_FORM18';
                        $form19CostKey = 'PAYMENT_PUBLIC_FORM19';                  
                    }else if($companyType->key =='COMPANY_TYPE_OFFSHORE'){
                        $form18Cost = $this->settings('PAYMENT_PUBLIC_FORM18','key')->value;
                        $form19Cost = $this->settings('PAYMENT_PUBLIC_FORM19','key')->value; 
                        $form18CostKey = 'PAYMENT_PUBLIC_FORM18';
                        $form19CostKey = 'PAYMENT_PUBLIC_FORM19';                  
                    }

                    

                $external_comment_key_id = $this->settings('COMMENT_EXTERNAL','key')->id;
              
                $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                      ->where('comment_type', $external_comment_key_id )
                                      ->where('request_id', $changeRequestID)
                                      ->orderBy('id', 'DESC')
                                      ->first();
                $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                        ?  $external_comment_query->comments
                                        : '';
                $regsecs    = Secretary::leftJoin('secretary_certificates','secretary_certificates.secretary_id','=','secretaries.id')
                ->where('secretaries.status',$this->settings('SECRETARY_APPROVED','key')->id)
                ->where('secretary_certificates.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->get(['secretaries.id','secretaries.nic','secretary_certificates.certificate_no as certno']);
                
                $regsecfirms    = SecretaryFirm::leftJoin('secretary_certificates','secretary_certificates.firm_id','=','secretary_firm.id')
                ->where('secretary_firm.status',$this->settings('SECRETARY_APPROVED','key')->id)
                ->where('secretary_certificates.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->get(['secretary_firm.id','secretary_firm.registration_no as regno','secretary_certificates.certificate_no as certno']);
                    
                return response()->json([
                    'message' => 'success',
                    'status' =>true,
                    'changeRequestID' =>$changeRequestID,
                    'incoDate' =>$incoDate,
                    'external_global_comment' => $external_global_comment,
                    'moduleStatus' => isset( $requestInfo->status ) && $requestInfo->status && isset($this->settings($requestInfo->status,'id')->key) ? $this->settings($requestInfo->status,'id')->key : '',
                  //  'moduleStatus' =>  'COMPANY_CHANGE_RESUBMISSION',
                    'processStatus' => isset($companyInfo->status) && $companyInfo->status && isset($this->settings($companyInfo->status,'id')->key) ? $this->settings($companyInfo->status,'id')->key : '',
                    'oldDirectorList' =>$olddirectors,
                    'oldSecretaryList' =>$oldsecs,
                    'oldSecretaryFirmList' =>$oldsecs_firms,
                    'countryList'     => $countryList,
                    'data'   => array(
                        'directorList' => $directors,
                        'secretaryFirmList' =>$secs_firms,
                        'secretaryList' =>$secs,
                        'coreShareGroups' => $core_groups_list,
                        'form18Cost' =>$form18Cost,
                        'form19Cost' =>$form19Cost,
                        'form20Cost' =>$form20Cost,
                        'form18CostKey' =>$form18CostKey,
                        'form19CostKey' =>$form19CostKey,
                        'companyTypeKey' =>$companyType->key,
                        'regsecs'     => $regsecs,
                        'regsecfirms'     => $regsecfirms,
                        'members'     => $date,
                        'case'     => $case,
                        'signedby' => $signedby,
                        'signedbytype' => $signedbytype,
                    )
                ], 200);
            }else{
                return response()->json([
                    'message' => 'unauthorized user detected',
                    'status' =>false,
                ], 404); 
            }              
        }
    }

    public function saveCourtData (Request $request){

        $type = $request->type;
        $court_status = $request->court_status;
        $caseId = $request->caseId;
        if($type == 'submit'){
    
            $caseid = NULL;
    
            if( intval($caseId) && $court_status == 'yes' ){
                $case = CourtCase::find($caseId);
                $case->company_id = $request->id;
                $case->request_id = $request->reqid;
                $case->court_status = $request->court_status;
                $case->court_name = $request->court_name;
                $case->court_date = $request->court_date;
                $case->court_case_no = $request->court_case_no;
                $case->court_penalty = $request->court_penalty;
                $case->court_period = $request->court_period;
                $case->court_discharged = $request->court_discharged;
                $case->save();
                
                $caseid = $caseId;
            }       
            elseif( intval($caseId) && $court_status == 'no'){
                $case = CourtCase::find($caseId);
                $case->delete();       
            }
            elseif( (!intval($caseId)) && $court_status == 'yes'){
                $case = new CourtCase;
                $case->company_id = $request->id;
                $case->request_id = $request->reqid;
                $case->court_status = $request->court_status;
                $case->court_name = $request->court_name;
                $case->court_date = $request->court_date;
                $case->court_case_no = $request->court_case_no;
                $case->court_penalty = $request->court_penalty;
                $case->court_period = $request->court_period;
                $case->court_discharged = $request->court_discharged;
                $case->save();    
                
                $caseid = $case->id;
            }
    
            // $approvedRequests = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
            //         ->where('company_change_requests.company_id', $request->id)
            //         ->where('company_change_requests.request_type', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE', 'key')->id)
            //         ->where('company_change_requests.status', '=', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_APPROVED', 'key')->id)
            //         ->get();
    
            // penalty charges calculation //
    
            //mindate function

            $pendingmembers = CompanyMember::where('company_id',$request->id)
                                             ->where(function($q) {
                                                $q->where('designation_type','=',$this->settings('DERECTOR', 'key')->id)
                                                  ->orWhere('designation_type','=',$this->settings('SECRETARY', 'key')->id);})
                                             ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                               ->get();

            $pendingfirms = CompanyFirms::where('company_id',$request->id)
                                                 ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                 ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                   ->get();

            $pendingeditmembers = CompanyMember::where('company_id',$request->id)
                                             ->where(function($q) {
                                                $q->where('designation_type','=',$this->settings('DERECTOR', 'key')->id)
                                                  ->orWhere('designation_type','=',$this->settings('SECRETARY', 'key')->id);})
                                             ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                               ->get();                                       

            $pendingeditfirms = CompanyFirms::where('company_id',$request->id)
                                                 ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                 ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                   ->get();

            $pendingdeletemembers = CompanyMember::where('company_id',$request->id)
                                             ->where(function($q) {
                                                $q->where('designation_type','=',$this->settings('DERECTOR', 'key')->id)
                                                  ->orWhere('designation_type','=',$this->settings('SECRETARY', 'key')->id);})
                                             ->where(function($q) {
                                                    $q->where('ceased_date','!=',NULL)
                                                      ->orWhere('ceased_date','!=',NULL);})   
                                             ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                               ->get();                                       

            $pendingdeletefirms = CompanyFirms::where('company_id',$request->id)
                                                 ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                 ->where(function($q) {
                                                    $q->where('ceased_date','!=',NULL)
                                                      ->orWhere('ceased_date','!=',NULL);})
                                                 ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                   ->get();

            if(count($pendingmembers) > 0 || count($pendingfirms) > 0 || count($pendingeditmembers) > 0 || count($pendingeditfirms) > 0 || count($pendingdeletemembers) > 0 || count($pendingdeletefirms) > 0){
    
                $dates = array();
                foreach ($pendingmembers as $value) {
                    $dates[] = $value->date_of_appointment;
                }
                foreach ($pendingfirms as $value) {
                    $dates[] = $value->date_of_appointment;
                }
                foreach ($pendingeditmembers as $value) {
                    $dates[] = $value->date_of_change;
                }
                foreach ($pendingeditfirms as $value) {
                    $dates[] = $value->date_of_change;
                }
                foreach ($pendingdeletemembers as $value) {
                    $dates[] = $value->ceased_date;
                }
                foreach ($pendingdeletefirms as $value) {
                    $dates[] = $value->ceased_date;
                }
                
                $min = min(array_map('strtotime', $dates));
                // usort($dates, function($a, $b) {
                //     $dateTimestamp1 = strtotime($a);
                //     $dateTimestamp2 = strtotime($b);
                
                //     return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
                // });
                $mindate = date('Y-m-d', $min);
                $todayDate = date("Y-m-d");
    
    
    
                $updated_date = strtotime($todayDate);
                $resolution_date = strtotime($mindate);
    
    
               $date_gaps = ($updated_date - $resolution_date) / (60*60*24);
               $date_gaps = intval($date_gaps);
    
    
               $min_date_gap = $this->settings('FORM_20_DELAY_PERIOD','key')->value;
               $increment_gap_dates = 30;
               $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_20_INITIAL','key')->value );
               $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_20_INCREMENT','key')->value );
               $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_20_MAX','key')->value );
       
               $increment_gaps = 0;
       
               $penalty_value = 0;
            //    if(count($approvedRequests) > 0){
               if(true){
    
                if($date_gaps < $min_date_gap ) {
                    return response()->json(['status' => true,'msg' => 'less10gap', 'gap'=>$date_gaps, 'penaly_charge'=>0 ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);
                }
       
                $increment_gaps = ( $date_gaps % $increment_gap_dates == 0 ) ? $date_gaps / $increment_gap_dates : intval($date_gaps / $increment_gap_dates) + 1;
                $penalty_value  = $penalty_value + $init_panalty;
       
                   if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                       $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
                   }
       
                   $penalty_value =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;
    
                   return response()->json(['status' => true,'msg' => 'alwaysPenalty', 'gap'=>$date_gaps, 'penaly_charge'=>$penalty_value ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);
    
               }
               else {
                return response()->json(['status' => true,'msg' => '1sttime', 'gap'=>$date_gaps, 'penaly_charge'=>0 ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);
               }

            }
            else{
                return response()->json(['status' => true,'msg' => 'onlyRemove','penaly_charge'=>0 ,'caseid'=>$caseid ], 200);
            }
       
                
    
        }
        elseif($type == 'resubmit'){
            $caseid = NULL;
            if( intval($caseId) && $court_status == 'yes' ){
                $case = CourtCase::find($caseId);
                $case->court_name = $request->court_name;
                $case->court_date = $request->court_date;
                $case->court_case_no = $request->court_case_no;
                $case->court_penalty = $request->court_penalty;
                $case->court_period = $request->court_period;
                $case->court_discharged = $request->court_discharged;
                $case->save();

                $caseid = $case->id;
                
            }
            return response()->json(['status' => true,'msg' => 'resubmit case updated','penaly_charge'=>0 ,'caseid'=>$caseid ], 200);

        }
    
    
    
    }

    function penaltyCal ($comId){

        // $type = $request->type;
        // $court_status = $request->court_status;
        // $caseId = $request->caseId;
    
    
        // penalty charges calculation //
    
            //mindate function

            $pendingmembers = CompanyMember::where('company_id',$comId)
                                             ->where(function($q) {
                                                $q->where('designation_type','=',$this->settings('DERECTOR', 'key')->id)
                                                  ->orWhere('designation_type','=',$this->settings('SECRETARY', 'key')->id);})
                                             ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                               ->get();

            $pendingfirms = CompanyFirms::where('company_id',$comId)
                                                 ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                 ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                   ->get();

            $pendingeditmembers = CompanyMember::where('company_id',$comId)
                                             ->where(function($q) {
                                                $q->where('designation_type','=',$this->settings('DERECTOR', 'key')->id)
                                                  ->orWhere('designation_type','=',$this->settings('SECRETARY', 'key')->id);})
                                             ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                               ->get();                                       

            $pendingeditfirms = CompanyFirms::where('company_id',$comId)
                                                 ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                 ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                   ->get();

            $pendingdeletemembers = CompanyMember::where('company_id',$comId)
                                             ->where(function($q) {
                                                $q->where('designation_type','=',$this->settings('DERECTOR', 'key')->id)
                                                  ->orWhere('designation_type','=',$this->settings('SECRETARY', 'key')->id);})
                                             ->where(function($q) {
                                                    $q->where('ceased_date','!=',NULL)
                                                      ->orWhere('ceased_date','!=',NULL);})   
                                             ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                               ->get();                                       

            $pendingdeletefirms = CompanyFirms::where('company_id',$comId)
                                                 ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                 ->where(function($q) {
                                                    $q->where('ceased_date','!=',NULL)
                                                      ->orWhere('ceased_date','!=',NULL);})
                                                 ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                   ->get();                                       
    
            if(count($pendingmembers) > 0 || count($pendingfirms) > 0 || count($pendingeditmembers) > 0 || count($pendingeditfirms) > 0 || count($pendingdeletemembers) > 0 || count($pendingdeletefirms) > 0){
    
                $dates = array();
                foreach ($pendingmembers as $value) {
                    $dates[] = $value->date_of_appointment;
                }
                foreach ($pendingfirms as $value) {
                    $dates[] = $value->date_of_appointment;
                }
                foreach ($pendingeditmembers as $value) {
                    $dates[] = $value->date_of_change;
                }
                foreach ($pendingeditfirms as $value) {
                    $dates[] = $value->date_of_change;
                }
                foreach ($pendingdeletemembers as $value) {
                    $dates[] = $value->ceased_date;
                }
                foreach ($pendingdeletefirms as $value) {
                    $dates[] = $value->ceased_date;
                }
                
                $min = min(array_map('strtotime', $dates));
                // usort($dates, function($a, $b) {
                //     $dateTimestamp1 = strtotime($a);
                //     $dateTimestamp2 = strtotime($b);
                
                //     return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
                // });
                $mindate = date('Y-m-d', $min);
                $todayDate = date("Y-m-d");
    
    
    
                $updated_date = strtotime($todayDate);
                $resolution_date = strtotime($mindate);
    
    
               $date_gaps = ($updated_date - $resolution_date) / (60*60*24);
               $date_gaps = intval($date_gaps);
    
    
               $min_date_gap = $this->settings('FORM_20_DELAY_PERIOD','key')->value;
               $increment_gap_dates = 30;
               $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_20_INITIAL','key')->value );
               $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_20_INCREMENT','key')->value );
               $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_20_MAX','key')->value );
       
               $increment_gaps = 0;
       
               $penalty_value = 0;
            //    if(count($approvedRequests) > 0){
               if(true){
    
                if($date_gaps < $min_date_gap ) {
                    return $penalty_value;
                }
       
                $increment_gaps = ( $date_gaps % $increment_gap_dates == 0 ) ? $date_gaps / $increment_gap_dates : intval($date_gaps / $increment_gap_dates) + 1;
                $penalty_value  = $penalty_value + $init_panalty;
       
                   if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                       $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
                   }
       
                   $penalty_value =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;
    
                   return $penalty_value;
    
               }
               else {
                return $penalty_value;
               }
    
            }
            else{
                return false;
            }
    
    
    
    }

    function inputSignby(Request $request){

        $company_id = $request->companyId;
        $signby = $request->signby;
        $requestId = $request->requestId;

        if($signby){
            $arr = explode("-",$signby);
            $membid = (int)$arr[0];
            $type = (int)$arr[1];

            if ($type == 0 ){
                $signbyid = $membid;
                $signbytype = $this->settings('COMPANY_MEMBERS','key')->id;
            }
            else{
                $signbyid = $membid;
                $signbytype = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
            }

            CompanyChangeRequestItem::where('id',$requestId)
                        ->update(['signed_by' => $signbyid,
                                  'signed_by_table_type' => $signbytype,  
                                 ]);
            $penalty_value = $this->penaltyCal($company_id);
            if(!$penalty_value){
                $case = CourtCase::where('company_court_cases.company_id',$company_id)
                ->where('company_court_cases.request_id',$requestId)
                ->first();
                if($case){
                    $case->delete();

                }

            }                     

            return response()->json([
                    'message' => 'successfully signby inserted & penalty calculated',
                    'status' =>true,
                    'penalty_value' => $penalty_value,
                ], 200);                     
        }
        else{
            return response()->json([
                'message' => 'signby error',
                'status' =>false,
            ], 404);
        }

    }
    
    function revertMemberData(Request $request){

        $company_id = $request->companyId;
        $type = $request->type;
        $oldid = $request->id;
        $requestId = $request->requestId;

        if($type == 'director'){

            $isDirectorEdited = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('old_record_id',$oldid)
                                                      ->first();

            $isDirectorDeleted = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$oldid)
                                                      ->first();

                    if($isDirectorEdited){

                        $newEditedDirector = CompanyMember::where('id',$isDirectorEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();
                        if($newEditedDirector){

                            //if director is a secretory

                            $director_is_srilankan = $newEditedDirector->is_srilankan;
                            $director_nic_or_pass = ($director_is_srilankan  =='yes') ? $newEditedDirector->nic : $newEditedDirector->passport_no;
                            $director_nic_or_pass_field = ($director_is_srilankan  =='yes') ? 'nic' : 'passport_no';
               
                        
                            $directors_as_sec_exist = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
                            if($directors_as_sec_exist){
                                $directors_as_sec_existItem = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$directors_as_sec_exist->id)
                                                      ->delete();
                                $directors_as_sec_existDelete = CompanyMember::where('id',$directors_as_sec_exist->id)
                                                ->delete();

                                }

                            //if director is a secretory    
                                                       
                            $newEditedDirectorDelete = CompanyMember::where('id',$isDirectorEdited->item_id)
                                                ->delete();
                        }
                        $isDirectorEditedItem = CompanyItemChange::where('id',$isDirectorEdited->id)
                                                      ->delete();                                          

                    }
                    elseif($isDirectorDeleted){

                        CompanyMember::where('id',$isDirectorDeleted->item_id)
                        ->update(['ceased_reason' => NULL,
                                  'ceased_date' => NULL,  
                                  'ceased_reason_other' => NULL,  
                                 ]);
                                 
                        $isDirectorDeletedItem = CompanyItemChange::where('id',$isDirectorDeleted->id)
                                 ->delete();         

                    }

                    return response()->json([
                    'message' => 'successfully director member reverted',
                    'status' =>true,
                ], 200);

        }

        elseif($type == 'sec'){

            $isSecEdited = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('old_record_id',$oldid)
                                                      ->first();

            $isSecDeleted = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$oldid)
                                                      ->first();                                  
                    
                    if($isSecEdited){

                        $newEditedSec = CompanyMember::where('id',$isSecEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();
                        if($newEditedSec){
                            //if secretory is a director

                            $director_is_srilankan = $newEditedSec->is_srilankan;
                            $director_nic_or_pass = ($director_is_srilankan  =='yes') ? $newEditedSec->nic : $newEditedSec->passport_no;
                            $director_nic_or_pass_field = ($director_is_srilankan  =='yes') ? 'nic' : 'passport_no';
               
                        
                            $sec_as_director_exist = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
                            if($sec_as_director_exist){

                                $sec_as_director_existItem = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$sec_as_director_exist->id)
                                                      ->delete();
                                $sec_as_director_existDelete = CompanyMember::where('id',$sec_as_director_exist->id)
                                                ->delete();

                                }

                            //if director is a secretory


                            $newEditedSecDelete = CompanyMember::where('id',$isSecEdited->item_id)
                                                ->delete();
                        }
                        $isSecEditedItem = CompanyItemChange::where('id',$isSecEdited->id)
                                                      ->delete();                                          

                    }
                    elseif($isSecDeleted){

                        CompanyMember::where('id',$isSecDeleted->item_id)
                        ->update(['ceased_reason' => NULL,
                                  'ceased_date' => NULL,
                                  'ceased_reason_other' => NULL,  
                                 ]);
                                 
                        $isSecDeletedItem = CompanyItemChange::where('id',$isSecDeleted->id)
                                 ->delete();         

                    }
                    
                    return response()->json([
                    'message' => 'successfully sec member reverted',
                    'status' =>true,
                ], 200);

        }

        elseif($type == 'secfirm'){

            $isSecfirmEdited = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('old_record_id',$oldid)
                                                      ->first();

            $isSecfirmDeleted = CompanyItemChange::where('request_id',$requestId)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('item_id',$oldid)
                                                      ->first();                                                                           
                    
                    if($isSecfirmEdited){

                        $newEditedSecfirm = CompanyFirms::where('id',$isSecfirmEdited->item_id)
                                                ->where('company_id',$request->companyId)
                                                ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        if($newEditedSecfirm){
                            $newEditedSecfirmDelete = CompanyFirms::where('id',$isSecfirmEdited->item_id)
                                                ->delete();
                        }
                        $isSecfirmEditedItem = CompanyItemChange::where('id',$isSecfirmEdited->id)
                                                      ->delete();                                          

                    }
                    elseif($isSecfirmDeleted){

                        CompanyFirms::where('id',$isSecfirmDeleted->item_id)
                        ->update(['ceased_reason' => NULL,
                                  'ceased_date' => NULL,
                                  'ceased_reason_other' => NULL,  
                                 ]);
                                 
                        $isSecfirmDeletedItem = CompanyItemChange::where('id',$isSecfirmDeleted->id)
                                 ->delete();         

                    }
                    
                    return response()->json([
                    'message' => 'successfully sec firm reverted',
                    'status' =>true,
                ], 200);

        }



    }

    // function checkRegno(Request $request){
    //     $company_id = $request->companyId;
    //     $nic = $request->nic;
    //     $regno = $request->regno;
    //     $requestId = $request->requestId;
    //     $type = $request->type;
         
    //     if($type == 'sec'){
    //         $secretary = Secretary::where('nic',$nic)
    //         ->where('status',$this->settings('SECRETARY_APPROVED','key')->id)
    //         ->first();
    //         if($secretary){
    //             $secretarycert = SecretaryCertificate::where('secretary_id',$secretary->id)
    //             ->where('certificate_no',$regno)
    //         ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
    //         ->first();

    //         if($secretarycert){
    //             return response()->json([
    //                 'message' => 'Have approved sec in that regno and in nic',
    //                 'status' =>true,
    //             ], 200);
    //         }
    //         else{
    //             return response()->json([
    //                 'message' => 'No approved sec in that regno but in nic',
    //                 'status' =>false,
    //             ], 200);
    //         }
                
    //         }
    //         else{
    //             return response()->json([
    //                 'message' => 'No approved sec in that nic',
    //                 'status' =>false,
    //             ], 200);
    
    //         }
    //     }
    //     elseif($type == 'secfirm'){

    //     }

        

    // }
    
    function editMemberData(Request $request){

        $company_id = $request->companyId;
        $type = $request->type;
        $oldid = $request->id;
        $requestId = $request->requestId;
        
        $loginUserEmail = $this->clearEmail($request->loginUser);
        $direcotrList = array();
        $secList = array();
        $shareHolderList = array();

        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');
        if($type == 'director'){

            //loop through change director list
        foreach($request->directors['directors'] as $director ){
            if($director['id'] == $oldid){

                $addressId= null;
            $forAddressId = null;               
            if($director['province'] || $director['district'] ||  $director['city'] || $director['localAddress1'] || $director['localAddress2'] || $director['postcode'] ) {
             $address = new Address;
             $address->province = $director['province'];
             $address->district =  $director['district'];
             $address->city =  $director['city'];
             $address->address1 =  $director['localAddress1'];
             $address->address2 =  $director['localAddress2'];
             $address->postcode = $director['postcode'];
             $address->country = ($director['type'] == 'foreign') ? $director['country'] :  'Sri Lanka';              
             $address->save();
             $addressId = $address->id;
            }

            if($director['forProvince'] ||  $director['forCity'] || $director['forAddress1'] || $director['forAddress2'] || $director['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $director['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $director['forCity'];
             $forAddress->address1 =  $director['forAddress1'];
             $forAddress->address2 =  $director['forAddress2'];
             $forAddress->postcode = $director['forPostcode'];
             $forAddress->country =  $director['country'];              
             $forAddress->save();
             $forAddressId = $forAddress->id;
            }

          

          //if director is a secretory

          $director_is_srilankan = $director['type'] != 'local' ?  'no' : 'yes';
          $director_nic_or_pass = ($director_is_srilankan  =='yes') ? strtoupper($director['nic']) : $director['passport'];
          $director_nic_or_pass_field = ($director_is_srilankan  =='yes') ? 'nic' : 'passport_no';
               
                        
            $directors_as_sec_exist = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
             if($directors_as_sec_exist) {
                $dir_to_sec_address = new Address;
                $dir_to_sec_address->province = $director['province'];
                $dir_to_sec_address->district =  $director['district'];
                $dir_to_sec_address->city =  $director['city'];
                $dir_to_sec_address->address1 =  $director['localAddress1'];
                $dir_to_sec_address->address2 =  $director['localAddress2'];
                $dir_to_sec_address->postcode = $director['postcode'];
                $dir_to_sec_address->country = 'Sri Lanka';                
                $dir_to_sec_address->save();
                $secAddressId = $dir_to_sec_address->id;

                $directors_as_sec = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
                $isNewDirectorasSec;
                if(isset($directors_as_sec->id) && $directors_as_sec->id ){
                        $dir_sec = CompanyMember::find($directors_as_sec->id);
                        $isNewDirectorasSec = false;
                }else{
                        $dir_sec = new CompanyMember;
                         $isNewDirectorasSec = true;
                    }                                       
   
                        $dir_sec->company_id = $company_id;
                        $dir_sec->designation_type = $this->settings('SECRETARY','key')->id;
                        $dir_sec->is_srilankan = 'yes';
                        $dir_sec->title = $director['title'];
                        $dir_sec->first_name = $director['firstname'];
                        $dir_sec->last_name =$director['lastname'];
                        $dir_sec->address_id = $secAddressId;
                        $dir_sec->nic = strtoupper($director['nic']);
    
                        $dir_sec->passport_issued_country ='Sri Lanka';
                        $dir_sec->telephone =$director['phone'];
                        $dir_sec->mobile =$director['mobile'];
                        $dir_sec->email =$director['email'];
                        $dir_sec->occupation =$director['occupation'];
                      //  $dir_sec->no_of_shares ='0';
                        $dir_sec->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
                        $dir_sec->date_of_change = date('Y-m-d',strtotime($director['changedate']) );
                        $dir_sec->is_registered_secretary = isset($director['secRegDate']) ? 'yes'  : 'no';
                        $dir_sec->secretary_registration_no = isset($director['secRegDate']) ? $director['secRegDate'] : NULL;
                        $dir_sec->is_natural_person ="yes";
                        $dir_sec->status = $this->settings('COMMON_STATUS_EDIT','key')->id;
                        $dir_sec->save();
                        $newDirSecID = $dir_sec->id;

                        if($isNewDirectorasSec){
                            $itemChange = new CompanyItemChange;
                            $itemChange->request_id = $requestId;
                            $itemChange->changes_type = $this->settings('EDIT','key')->id;
                            $itemChange->item_id =  $newDirSecID;
                            $itemChange->old_record_id =  $directors_as_sec_exist->id;
                            $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                            $itemChange->save();
                        }

             }
             
             //if director is a secretory end

             
         


         $isNewDirector;
         if(isset($director['newid']) && $director['newid'] ){
             $updateDirector = CompanyMember::find($director['newid']);
             $isNewDirector = false;
         }else{
             $updateDirector = new CompanyMember;
             $isNewDirector = true;
         }
         $updateDirector->company_id = $company_id;
         $updateDirector->designation_type =  $this->settings('DERECTOR','key')->id;
         $updateDirector->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
         $updateDirector->first_name = $director['firstname'];
         $updateDirector->last_name = $director['lastname'];            
         $company_info = Company::where('id',$company_id)->first(); 
         $process_status = $this->settings($company_info->status,'id')->key;
         $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
         if(!$process_status_val){
             $updateDirector->title = $director['title'];
             $updateDirector->nic = strtoupper($director['nic']);
             $updateDirector->passport_no = $director['passport'];
         }            
         $updateDirector->address_id = $addressId;
         $updateDirector->foreign_address_id =  $forAddressId;
         $updateDirector->passport_issued_country = isset( $director['passport_issued_country']) ? $director['passport_issued_country'] : $director['country'];
         $updateDirector->telephone = $director['phone'];
         $updateDirector->mobile =$director['mobile'];
         $updateDirector->email = $director['email'];
        // $updateDirector->foreign_address_id =($director['type'] !='local') ? $addressId: 0;
         $updateDirector->occupation = $director['occupation'];
        // $updateDirector->no_of_shares = $director['share'];
         $updateDirector->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
         $updateDirector->date_of_change = date('Y-m-d',strtotime($director['changedate']) );
         $updateDirector->status = $this->settings('COMMON_STATUS_EDIT','key')->id;
         $updateDirector->save();

         if($isNewDirector){
             $itemChange = new CompanyItemChange;
             $itemChange->request_id = $requestId;
             $itemChange->changes_type = $this->settings('EDIT','key')->id;
             $itemChange->item_id =  $updateDirector->id;
             $itemChange->old_record_id =  $director['id'];
             $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
             $itemChange->save();
         }             

          //add to peoples table

         //  if( $director['nic'] ){
         //     $check_people = People::where('nic', $director['nic'] )->count();
         //     if($check_people == 0 ){
         //         $people = new People;
         //         $people->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
         //         $people->title = $this->settings('TITLE_MR','key')->id;
         //         $people->first_name = $director['firstname'];
         //         $people->last_name =$director['lastname'];
         //         $people->address_id = $addressId;
         //         $people->foreign_address_id =  $forAddressId;
         //         $people->nic = strtoupper($director['nic']);
         //         $people->passport_issued_country ='Sri Lanka';
         //         $people->telephone =$director['phone'];
         //         $people->mobile =$director['mobile'];
         //         $people->email =$director['email'];
         //         $people->occupation =$director['occupation'];
         //         $people->sex ='male';
         //         $people->status =1;
         //         $people->save();
         //     }
         // }


         //add to peoples table

         // if( $director['passport'] ){
         //     $check_people = People::where('passport_no', $director['passport'] )->count();
         //     if($check_people == 0 ){
         //         $people = new People;
         //         $people->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
         //         $people->title = $this->settings('TITLE_MR','key')->id;
         //         $people->first_name = $director['firstname'];
         //         $people->last_name =$director['lastname'];
         //         $people->address_id = $addressId;
         //         $people->foreign_address_id =  $forAddressId;
         //         $people->passport_no = strtoupper($director['passport']);
         //         $people->passport_issued_country =$director['passport_issued_country'];
         //         $people->telephone =$director['phone'];
         //         $people->mobile =$director['mobile'];
         //         $people->email =$director['email'];
         //         $people->occupation =$director['occupation'];
         //         $people->sex ='male';
         //         $people->status =1;
         //         $people->save();
         //     }
         // }
         return response()->json([
            'message' => 'Successfully Saved Data for director',
            'status' =>true,            
        ], 200);

            }

            
     }

        }
        
        if($type == 'sec'){
        //loop through change secrotory list
        foreach($request->secretories['secs'] as $sec ){
            if($sec['id'] == $oldid){

             $companyFirmAddressId = null;
             $addressId= null;
             $forAddressId = null;     
            if( $sec['secType'] == 'firm' ) {

                $companyFirmAddress = new Address;
                $companyFirmAddress->province = $sec['firm_province'];
                $companyFirmAddress->district =  $sec['firm_district'];
                $companyFirmAddress->city =  $sec['firm_city'];
                $companyFirmAddress->address1 =  $sec['firm_localAddress1'];
                $companyFirmAddress->address2 =  $sec['firm_localAddress2'];
                $companyFirmAddress->postcode = $sec['firm_postcode'];
                $companyFirmAddress->country = isset($sec['firm_country'] ) ? $sec['firm_country'] : 'Sri Lanka';              
                $companyFirmAddress->save();
                $companyFirmAddressId = $companyFirmAddress->id;

                $isNewSecretaryFirm;
                if(isset($sec['newid']) && $sec['newid'] ){
                    $updateSec = CompanyFirms::find($sec['newid']);
                    $isNewSecretaryFirm = false;
                }else{
                    $updateSec = new CompanyFirms;
                    $isNewSecretaryFirm = true;
                }
            } else {

                if($sec['province'] || $sec['district'] ||  $sec['city'] || $sec['localAddress1'] || $sec['localAddress2'] || $sec['postcode'] ) {
                 $address = new Address;
               //  $address->id = 9999;
                 $address->province = $sec['province'];
                 $address->district =  $sec['district'];
                 $address->city =  $sec['city'];
                 $address->address1 =  $sec['localAddress1'];
                 $address->address2 =  $sec['localAddress2'];
                 $address->postcode = $sec['postcode'];
                 $address->country =  'Sri Lanka';               
                 $address->save();
                 $addressId = $address->id;
                }
                
                $postcodecheck =  ($companyType->key === 'COMPANY_TYPE_OVERSEAS' || $companyType->key === 'COMPANY_TYPE_OFFSHORE') ? true : isset($sec['forPostcode']);
                if(
                   ( isset( $sec['forProvince'] ) && isset($sec['forCity']) && isset($sec['forAddress1']) && isset($sec['forAddress2']) && $postcodecheck ) && 
                   ( $sec['forProvince'] ||  $sec['forCity'] || $sec['forAddress1'] || $sec['forAddress2'] || $sec['forPostcode'] )
                    
                ) {
                 $forAddress = new Address;
               //  $address->id = 9999;
                 $forAddress->province = $sec['forProvince'];
                 $forAddress->district = null;
                 $forAddress->city =  $sec['forCity'];
                 $forAddress->address1 =  $sec['forAddress1'];
                 $forAddress->address2 =  $sec['forAddress2'];
                 $forAddress->postcode = $sec['forPostcode'];
                 $forAddress->country =  $sec['country'];
               
                 $forAddress->save();
                 $forAddressId = $forAddress->id;
                }

                //if secretory is a director

          $director_is_srilankan = $sec['type'] != 'local' ?  'no' : 'yes';
          $director_nic_or_pass = ($director_is_srilankan  =='yes') ? strtoupper($sec['nic']) : $sec['passport'];
          $director_nic_or_pass_field = ($director_is_srilankan  =='yes') ? 'nic' : 'passport_no';
               
                        
            $directors_as_sec_exist = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
             if($directors_as_sec_exist) {
                $dir_to_sec_address = new Address;
                $dir_to_sec_address->province = $sec['province'];
                $dir_to_sec_address->district =  $sec['district'];
                $dir_to_sec_address->city =  $sec['city'];
                $dir_to_sec_address->address1 =  $sec['localAddress1'];
                $dir_to_sec_address->address2 =  $sec['localAddress2'];
                $dir_to_sec_address->postcode = $sec['postcode'];
                $dir_to_sec_address->country = 'Sri Lanka';                
                $dir_to_sec_address->save();
                $secAddressId = $dir_to_sec_address->id;

                $directors_as_sec = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
                $isNewDirectorasSec;
                if(isset($directors_as_sec->id) && $directors_as_sec->id ){
                        $dir_sec = CompanyMember::find($directors_as_sec->id);
                        $isNewDirectorasSec = false;
                }else{
                        $dir_sec = new CompanyMember;
                         $isNewDirectorasSec = true;
                    }                                       
   
                        $dir_sec->company_id = $company_id;
                        $dir_sec->designation_type = $this->settings('DERECTOR','key')->id;
                        $dir_sec->is_srilankan = 'yes';
                        $dir_sec->title = $sec['title'];
                        $dir_sec->first_name = $sec['firstname'];
                        $dir_sec->last_name =$sec['lastname'];
                        $dir_sec->address_id = $secAddressId;
                        $dir_sec->nic = strtoupper($sec['nic']);
    
                        $dir_sec->passport_issued_country ='Sri Lanka';
                        $dir_sec->telephone =$sec['phone'];
                        $dir_sec->mobile =$sec['mobile'];
                        $dir_sec->email =$sec['email'];
                        $dir_sec->occupation =$sec['occupation'];
                      //  $dir_sec->no_of_shares ='0';
                        $dir_sec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                        $dir_sec->date_of_change = date('Y-m-d',strtotime($sec['changedate']) );
                        $dir_sec->is_natural_person ="yes";
                        $dir_sec->status = $this->settings('COMMON_STATUS_EDIT','key')->id;
                        $dir_sec->save();
                        $newDirSecID = $dir_sec->id;

                        if($isNewDirectorasSec){
                            $itemChange = new CompanyItemChange;
                            $itemChange->request_id = $requestId;
                            $itemChange->changes_type = $this->settings('EDIT','key')->id;
                            $itemChange->item_id =  $newDirSecID;
                            $itemChange->old_record_id =  $directors_as_sec_exist->id;
                            $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                            $itemChange->save();
                        }

             }
             
             //if secretory is a director end

                $isNewSecretary;
                if(isset($sec['newid']) && $sec['newid'] ){
                    $updateSec = CompanyMember::find($sec['newid']);
                    $isNewSecretary = false;
                }else{
                    $updateSec = new CompanyMember;
                    $isNewSecretary = true;
                }
            }

            $newSecShareHolderID = null;  

            if( $sec['secType'] == 'firm' ) {

                if(isset($sec['newid']) && $sec['newid'] ){
                    $cf = CompanyFirms::find($sec['newid']);
                    $sec_as_sh_count =  ( isset($cf->sh_firm_of) &&  intval( $cf->sh_firm_of ) > 0 )  ? 1 : 0 ;
                } else {
                    $sec_as_sh_count = 0;
                }
                $updateSec->email  = $sec['firm_email'];
                $updateSec->mobile = $sec['firm_mobile'];
                $updateSec->phone  = $sec['firm_phone'];
                $updateSec->date_of_appointment = $sec['firm_date'];
                $updateSec->date_of_change = $sec['firm_date_change'];
                $updateSec->company_id = $company_id;
                $updateSec->address_id = $companyFirmAddressId;
                $updateSec->type_id = $this->settings('SECRETARY','key')->id;
                $updateSec->status = $this->settings('COMMON_STATUS_EDIT','key')->id;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes'; 

                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                    $updateSec->registration_no = $sec['pvNumber'];
                    $updateSec->name = $sec['firm_name'];
                }

                if($sec_as_sh_count == 0) {
                    $updateSec->sh_firm_of = ( $newSecShareHolderID ) ? $newSecShareHolderID : null;;
                }
                $updateSec->save();
                    if($isNewSecretaryFirm){
                        $itemChange = new CompanyItemChange;
                        $itemChange->request_id = $requestId;
                        $itemChange->changes_type = $this->settings('EDIT','key')->id;
                        $itemChange->item_id =  $updateSec->id;
                        $itemChange->old_record_id =  $sec['id'];
                        $itemChange->item_table_type =  $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                        $itemChange->save();
                    }               
            }else {
                $updateSec->company_id = $company_id;
                $updateSec->designation_type = $this->settings('SECRETARY','key')->id;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
                $updateSec->title = $sec['title'];
                
                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                 //   $updateSec->title = $sec['title'];
                  //  $updateSec->first_name = $sec['firstname'];
                  //  $updateSec->last_name = $sec['lastname']; 
                    $updateSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
                    $updateSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
                }
                $updateSec->first_name = $sec['firstname'];
                $updateSec->last_name = $sec['lastname'];
                $updateSec->address_id = $addressId;
                $updateSec->foreign_address_id = $forAddressId;
                $updateSec->passport_issued_country = isset( $sec['passport_issued_country'] ) ? $sec['passport_issued_country']  : null ;
                $updateSec->telephone = $sec['phone'];
                $updateSec->mobile =$sec['mobile'];
                $updateSec->email = $sec['email'];
            // $updateSec->foreign_address_id =($sec['type'] !='local') ? $addressId: 0;
                $updateSec->occupation = $sec['occupation'];
            //  $updateSec->no_of_shares =0;
                $updateSec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                $updateSec->date_of_change = date('Y-m-d',strtotime($sec['changedate']) );
                $updateSec->is_registered_secretary = ($sec['isReg'] == true ) ? 'yes' : 'no';
                $updateSec->secretary_registration_no =  (isset($sec['regDate']) && $sec['regDate'] ) ? $sec['regDate'] : NULL;
                $updateSec->status = $this->settings('COMMON_STATUS_EDIT','key')->id;    
                $updateSec->is_natural_person = $sec['secType'] =='natural' ? 'yes' : 'no';
              //  $updateSec->company_member_firm_id = $companyFirmId;
                $updateSec->save();
                    if($isNewSecretary){
                        $itemChange = new CompanyItemChange;
                        $itemChange->request_id = $requestId;
                        $itemChange->changes_type = $this->settings('EDIT','key')->id;
                        $itemChange->item_id =  $updateSec->id;
                        $itemChange->old_record_id =  $sec['id'];
                        $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                        $itemChange->save();
                    }   
                 //add to peoples table

                // $check_people = People::where('nic', $sec['nic'] )->count();
                // if($check_people == 0 ){

                //     $people = new People;
                //     $people->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
                //     $people->title = $this->settings('TITLE_MR','key')->id;
                //     $people->first_name = $sec['firstname'];
                //     $people->last_name =$sec['lastname'];
                //     $people->address_id = $addressId;
                //     $people->nic = strtoupper($sec['nic']);

                //     $people->passport_issued_country ='Sri Lanka';
                //     $people->telephone =$sec['phone'];
                //     $people->mobile =$sec['mobile'];
                //     $people->email =$sec['email'];
                //     $people->occupation =$sec['occupation'];
                //     $people->sex ='male';
                //     $people->status =1;
                //     $people->save();

                // }
            }
            return response()->json([
                'message' => 'Successfully Saved Data for sec',
                'status' =>true,            
            ], 200);
        }            
        }
        
    }
    
    if($type == 'secfirm'){
        //loop through change secrotory list
        foreach($request->secfirms['secs'] as $sec ){
            if($sec['id'] == $oldid){

             $companyFirmAddressId = null;
             $addressId= null;
             $forAddressId = null;     
            if( $sec['secType'] == 'firm' ) {

                $companyFirmAddress = new Address;
                $companyFirmAddress->province = $sec['firm_province'];
                $companyFirmAddress->district =  $sec['firm_district'];
                $companyFirmAddress->city =  $sec['firm_city'];
                $companyFirmAddress->address1 =  $sec['firm_localAddress1'];
                $companyFirmAddress->address2 =  $sec['firm_localAddress2'];
                $companyFirmAddress->postcode = $sec['firm_postcode'];
                $companyFirmAddress->country = isset($sec['firm_country'] ) ? $sec['firm_country'] : 'Sri Lanka';              
                $companyFirmAddress->save();
                $companyFirmAddressId = $companyFirmAddress->id;

                $isNewSecretaryFirm;
                if(isset($sec['newid']) && $sec['newid'] ){
                    $updateSec = CompanyFirms::find($sec['newid']);
                    $isNewSecretaryFirm = false;
                }else{
                    $updateSec = new CompanyFirms;
                    $isNewSecretaryFirm = true;
                }
            } else {

                if($sec['province'] || $sec['district'] ||  $sec['city'] || $sec['localAddress1'] || $sec['localAddress2'] || $sec['postcode'] ) {
                 $address = new Address;
               //  $address->id = 9999;
                 $address->province = $sec['province'];
                 $address->district =  $sec['district'];
                 $address->city =  $sec['city'];
                 $address->address1 =  $sec['localAddress1'];
                 $address->address2 =  $sec['localAddress2'];
                 $address->postcode = $sec['postcode'];
                 $address->country =  'Sri Lanka';               
                 $address->save();
                 $addressId = $address->id;
                }
                
                $postcodecheck =  ($companyType->key === 'COMPANY_TYPE_OVERSEAS' || $companyType->key === 'COMPANY_TYPE_OFFSHORE') ? true : isset($sec['forPostcode']);
                if(
                   ( isset( $sec['forProvince'] ) && isset($sec['forCity']) && isset($sec['forAddress1']) && isset($sec['forAddress2']) && $postcodecheck ) && 
                   ( $sec['forProvince'] ||  $sec['forCity'] || $sec['forAddress1'] || $sec['forAddress2'] || $sec['forPostcode'] )
                    
                ) {
                 $forAddress = new Address;
               //  $address->id = 9999;
                 $forAddress->province = $sec['forProvince'];
                 $forAddress->district = null;
                 $forAddress->city =  $sec['forCity'];
                 $forAddress->address1 =  $sec['forAddress1'];
                 $forAddress->address2 =  $sec['forAddress2'];
                 $forAddress->postcode = $sec['forPostcode'];
                 $forAddress->country =  $sec['country'];
               
                 $forAddress->save();
                 $forAddressId = $forAddress->id;
                }

                //if secretory is a director

          $director_is_srilankan = $sec['type'] != 'local' ?  'no' : 'yes';
          $director_nic_or_pass = ($director_is_srilankan  =='yes') ? strtoupper($sec['nic']) : $sec['passport'];
          $director_nic_or_pass_field = ($director_is_srilankan  =='yes') ? 'nic' : 'passport_no';
               
                        
            $directors_as_sec_exist = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
             if($directors_as_sec_exist) {
                $dir_to_sec_address = new Address;
                $dir_to_sec_address->province = $sec['province'];
                $dir_to_sec_address->district =  $sec['district'];
                $dir_to_sec_address->city =  $sec['city'];
                $dir_to_sec_address->address1 =  $sec['localAddress1'];
                $dir_to_sec_address->address2 =  $sec['localAddress2'];
                $dir_to_sec_address->postcode = $sec['postcode'];
                $dir_to_sec_address->country = 'Sri Lanka';                
                $dir_to_sec_address->save();
                $secAddressId = $dir_to_sec_address->id;

                $directors_as_sec = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                                       ->where('company_id', $request->companyId)
                                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                                       ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                                       ->first();
                $isNewDirectorasSec;
                if(isset($directors_as_sec->id) && $directors_as_sec->id ){
                        $dir_sec = CompanyMember::find($directors_as_sec->id);
                        $isNewDirectorasSec = false;
                }else{
                        $dir_sec = new CompanyMember;
                         $isNewDirectorasSec = true;
                    }                                       
   
                        $dir_sec->company_id = $company_id;
                        $dir_sec->designation_type = $this->settings('DERECTOR','key')->id;
                        $dir_sec->is_srilankan = 'yes';
                        $dir_sec->title = $sec['title'];
                        $dir_sec->first_name = $sec['firstname'];
                        $dir_sec->last_name =$sec['lastname'];
                        $dir_sec->address_id = $secAddressId;
                        $dir_sec->foreign_address_id = $directors_as_sec_exist->foreign_address_id;
                        $dir_sec->nic = strtoupper($sec['nic']);
    
                        $dir_sec->passport_issued_country ='Sri Lanka';
                        $dir_sec->telephone =$sec['phone'];
                        $dir_sec->mobile =$sec['mobile'];
                        $dir_sec->email =$sec['email'];
                        $dir_sec->occupation =$sec['occupation'];
                      //  $dir_sec->no_of_shares ='0';
                        $dir_sec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                        $dir_sec->date_of_change = date('Y-m-d',strtotime($sec['changedate']) );
                        $dir_sec->is_natural_person ="yes";
                        $dir_sec->status = $this->settings('COMMON_STATUS_EDIT','key')->id;
                        $dir_sec->save();
                        $newDirSecID = $dir_sec->id;

                        if($isNewDirectorasSec){
                            $itemChange = new CompanyItemChange;
                            $itemChange->request_id = $requestId;
                            $itemChange->changes_type = $this->settings('EDIT','key')->id;
                            $itemChange->item_id =  $newDirSecID;
                            $itemChange->old_record_id =  $directors_as_sec_exist->id;
                            $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                            $itemChange->save();
                        }

             }
             
             //if secretory is a director end

                $isNewSecretary;
                if(isset($sec['newid']) && $sec['newid'] ){
                    $updateSec = CompanyMember::find($sec['newid']);
                    $isNewSecretary = false;
                }else{
                    $updateSec = new CompanyMember;
                    $isNewSecretary = true;
                }
            }

            $newSecShareHolderID = null;  

            if( $sec['secType'] == 'firm' ) {

                if(isset($sec['newid']) && $sec['newid'] ){
                    $cf = CompanyFirms::find($sec['newid']);
                    $sec_as_sh_count =  ( isset($cf->sh_firm_of) &&  intval( $cf->sh_firm_of ) > 0 )  ? 1 : 0 ;
                } else {
                    $sec_as_sh_count = 0;
                }
                $updateSec->email  = $sec['firm_email'];
                $updateSec->mobile = $sec['firm_mobile'];
                $updateSec->phone  = $sec['firm_phone'];
                $updateSec->date_of_appointment = $sec['firm_date'];
                $updateSec->date_of_change = $sec['firm_date_change'];
                $updateSec->company_id = $company_id;
                $updateSec->address_id = $companyFirmAddressId;
                $updateSec->type_id = $this->settings('SECRETARY','key')->id;
                $updateSec->status = $this->settings('COMMON_STATUS_EDIT','key')->id;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes'; 

                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                    $updateSec->registration_no = $sec['pvNumber'];
                    $updateSec->name = $sec['firm_name'];
                }

                if($sec_as_sh_count == 0) {
                    $updateSec->sh_firm_of = ( $newSecShareHolderID ) ? $newSecShareHolderID : null;;
                }
                $updateSec->save();
                    if($isNewSecretaryFirm){
                        $itemChange = new CompanyItemChange;
                        $itemChange->request_id = $requestId;
                        $itemChange->changes_type = $this->settings('EDIT','key')->id;
                        $itemChange->item_id =  $updateSec->id;
                        $itemChange->old_record_id =  $sec['id'];
                        $itemChange->item_table_type =  $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                        $itemChange->save();
                    }               
            }else {
                $updateSec->company_id = $company_id;
                $updateSec->designation_type = $this->settings('SECRETARY','key')->id;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
                $updateSec->title = $sec['title'];
                
                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                 //   $updateSec->title = $sec['title'];
                  //  $updateSec->first_name = $sec['firstname'];
                  //  $updateSec->last_name = $sec['lastname']; 
                    $updateSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
                    $updateSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
                }
                $updateSec->first_name = $sec['firstname'];
                $updateSec->last_name = $sec['lastname'];
                $updateSec->address_id = $addressId;
                $updateSec->foreign_address_id = $forAddressId;
                $updateSec->passport_issued_country = isset( $sec['passport_issued_country'] ) ? $sec['passport_issued_country']  : null ;
                $updateSec->telephone = $sec['phone'];
                $updateSec->mobile =$sec['mobile'];
                $updateSec->email = $sec['email'];
            // $updateSec->foreign_address_id =($sec['type'] !='local') ? $addressId: 0;
                $updateSec->occupation = $sec['occupation'];
            //  $updateSec->no_of_shares =0;
                $updateSec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                $updateSec->date_of_change = date('Y-m-d',strtotime($sec['changedate']) );
                $updateSec->is_registered_secretary = ($sec['isReg'] == true ) ? 'yes' : 'no';
                $updateSec->secretary_registration_no =  (isset($sec['regDate']) && $sec['regDate'] ) ? $sec['regDate'] : NULL;
                $updateSec->status = $this->settings('COMMON_STATUS_EDIT','key')->id;    
                $updateSec->is_natural_person = $sec['secType'] =='natural' ? 'yes' : 'no';
              //  $updateSec->company_member_firm_id = $companyFirmId;
                $updateSec->save();
                    if($isNewSecretary){
                        $itemChange = new CompanyItemChange;
                        $itemChange->request_id = $requestId;
                        $itemChange->changes_type = $this->settings('EDIT','key')->id;
                        $itemChange->item_id =  $updateSec->id;
                        $itemChange->old_record_id =  $sec['id'];
                        $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                        $itemChange->save();
                    }   
                 //add to peoples table

                // $check_people = People::where('nic', $sec['nic'] )->count();
                // if($check_people == 0 ){

                //     $people = new People;
                //     $people->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
                //     $people->title = $this->settings('TITLE_MR','key')->id;
                //     $people->first_name = $sec['firstname'];
                //     $people->last_name =$sec['lastname'];
                //     $people->address_id = $addressId;
                //     $people->nic = strtoupper($sec['nic']);

                //     $people->passport_issued_country ='Sri Lanka';
                //     $people->telephone =$sec['phone'];
                //     $people->mobile =$sec['mobile'];
                //     $people->email =$sec['email'];
                //     $people->occupation =$sec['occupation'];
                //     $people->sex ='male';
                //     $people->status =1;
                //     $people->save();

                // }
            }
            return response()->json([
                'message' => 'Successfully Saved Data for secfirm',
                'status' =>true,            
            ], 200);
        }            
        }
        
    }
        return response()->json([
            'message' => 'not in any array unSuccessfully Saved Data',
            'status' =>true,            
        ], 200);
    }

    function saveMemberData(Request $request){

        $company_id = $request->companyId;
        $requestId = $request->requestId;
        
        $loginUserEmail = $this->clearEmail($request->loginUser);
        $direcotrList = array();
        $secList = array();
        $shareHolderList = array();

        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');
        //loop through add director list
        foreach($request->directors['directors'] as $director ){

               $addressId= null;
               $forAddressId = null;               
               if($director['province'] || $director['district'] ||  $director['city'] || $director['localAddress1'] || $director['localAddress2'] || $director['postcode'] ) {
                $address = new Address;
                $address->province = $director['province'];
                $address->district =  $director['district'];
                $address->city =  $director['city'];
                $address->address1 =  $director['localAddress1'];
                $address->address2 =  $director['localAddress2'];
                $address->postcode = $director['postcode'];
                $address->country = ($director['type'] == 'foreign') ? $director['country'] :  'Sri Lanka';              
                $address->save();
                $addressId = $address->id;
               }

               if($director['forProvince'] ||  $director['forCity'] || $director['forAddress1'] || $director['forAddress2'] || $director['forPostcode'] ) {
                $forAddress = new Address;
                $forAddress->province = $director['forProvince'];
                $forAddress->district = null;
                $forAddress->city =  $director['forCity'];
                $forAddress->address1 =  $director['forAddress1'];
                $forAddress->address2 =  $director['forAddress2'];
                $forAddress->postcode = $director['forPostcode'];
                $forAddress->country =  $director['country'];              
                $forAddress->save();
                $forAddressId = $forAddress->id;
               }

             //if director as a shareholder

            //  if( 
            //       (isset($director['isShareholder']) &&  $director['isShareholder'] ) ||
            //       ( isset($director['isShareholderEdit']) &&  $director['isShareholderEdit'] ) 
            //  ){
            //     if( 
            //         ( isset($director['shareType']) && $director['shareType'] ) ||
            //         ( isset($director['shareTypeEdit']) && $director['shareTypeEdit'] )
            //      ){
            //         $shareHolderAddressId = null;
            //         $shareHolderAddressForId = null;

            //         if($director['province'] || $director['district'] || $director['city'] || $director['localAddress1'] || $director['localAddress2'] || $director['postcode']  ) {
            //             $dir_to_share_address = new Address;
            //             $dir_to_share_address->province = $director['province'];
            //             $dir_to_share_address->district =  $director['district'];
            //             $dir_to_share_address->city =  $director['city'];
            //             $dir_to_share_address->address1 =  $director['localAddress1'];
            //             $dir_to_share_address->address2 =  $director['localAddress2'];
            //             $dir_to_share_address->postcode = $director['postcode'];
            //             $dir_to_share_address->country = 'Sri Lanka';
            //             $dir_to_share_address->save();
            //             $shareHolderAddressId = $dir_to_share_address->id;
            //         }
            //         if($director['forProvince'] || $director['forCity'] || $director['forAddress1'] || $director['forAddress2'] || $director['forPostcode']  ) {
            //             $dir_to_share_for_address = new Address;
            //             $dir_to_share_for_address->province = $director['forProvince'];
            //             $dir_to_share_for_address->city =  $director['forCity'];
            //             $dir_to_share_for_address->address1 =  $director['forAddress1'];
            //             $dir_to_share_for_address->address2 =  $director['forAddress2'];
            //             $dir_to_share_for_address->postcode = $director['forPostcode'];
            //             $dir_to_share_for_address->country =  $director['country'];
            //             $dir_to_share_for_address->save();
            //             $shareHolderAddressForId = $dir_to_share_for_address->id;
            //         }
            //         if( 
            //             ( isset($director['shareType']) &&  $director['shareType'] == 'single' && isset($director['noOfSingleShares']) &&  intval($director['noOfSingleShares']) ) ||
            //             ( isset($director['shareTypeEdit']) && $director['shareTypeEdit'] == 'single' && $director['noOfSingleSharesEdit'] &&  intval($director['noOfSingleSharesEdit']) )
            //         ) {
            //             $dir_shareholder = new CompanyMember;
            //             $dir_shareholder->company_id = $company_id;
            //             $dir_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
            //             $dir_shareholder->is_srilankan = ( isset( $director['type'] ) && $director['type'] == 'foreign' ) ? 'no' : 'yes';
            //             $dir_shareholder->title = $director['title'];
            //             $dir_shareholder->first_name = $director['firstname'];
            //             $dir_shareholder->last_name =$director['lastname'];
            //             $dir_shareholder->address_id = $shareHolderAddressId;
            //             $dir_shareholder->foreign_address_id = $shareHolderAddressForId;
            //             $dir_shareholder->nic = strtoupper($director['nic']);
            //             $dir_shareholder->passport_no = strtoupper($director['passport']);    
            //             $dir_shareholder->passport_issued_country = ( isset( $director['type'] ) && $director['type'] == 'foreign' && isset($director['passport_issued_country']) ) ? $director['passport_issued_country'] :  'Sri Lanka';
            //             $dir_shareholder->telephone =$director['phone'];
            //             $dir_shareholder->mobile =$director['mobile'];
            //             $dir_shareholder->email =$director['email'];
            //             $dir_shareholder->occupation =$director['occupation'];
            //             $dir_shareholder->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
            //             $dir_shareholder->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
            //             $dir_shareholder->save();
            //             $newDirShareHolderID = $dir_shareholder->id;
            //             $singleShares=0;

            //             $itemChange = new CompanyItemChange;
            //             $itemChange->request_id = $requestId;
            //             $itemChange->changes_type = $this->settings('ADD','key')->id;
            //             $itemChange->item_id =  $newDirShareHolderID;
            //             $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
            //             $itemChange->save();

            //             if(isset($director['noOfSingleShares']) &&  intval($director['noOfSingleShares'])){
            //                 $singleShares = intval($director['noOfSingleShares']);
            //             }
            //             if( isset($director['noOfSingleSharesEdit']) &&  intval($director['noOfSingleSharesEdit']) ){
            //                 $singleShares = intval($director['noOfSingleSharesEdit']);
            //             }    
            //             //add to single share group
            //             $dir_shareholder_sharegroup = new ShareGroup;
            //             $dir_shareholder_sharegroup->type ='single_share';
            //             $dir_shareholder_sharegroup->name ='single_share_no_name';
            //             $dir_shareholder_sharegroup->no_of_shares =$singleShares;
            //             $dir_shareholder_sharegroup->status = 1;
            //             $dir_shareholder_sharegroup->company_id = $company_id;    
            //             $dir_shareholder_sharegroup->save();
            //             $dir_shareholder_sharegroupID = $dir_shareholder_sharegroup->id;    
            //             //add to share table
            //             $dir_shareholder_share = new Share;
            //             $dir_shareholder_share->company_member_id = $newDirShareHolderID;
            //             $dir_shareholder_share->group_id = $dir_shareholder_sharegroupID;
            //             $dir_shareholder_share->save();
            //         }    
            //         if(
            //            ( isset($director['shareType']) &&  $director['shareType'] == 'core' &&  isset($director['coreGroupSelected']) && intval( $director['coreGroupSelected']) ) || 
            //            ( isset($director['shareTypeEdit']) &&  $director['shareTypeEdit'] == 'core' &&  isset($director['coreGroupSelectedEdit']) && intval( $director['coreGroupSelectedEdit']) ) 
                        
            //         ){
            //             $dir_shareholder = new CompanyMember;
            //             $dir_shareholder->company_id = $company_id;
            //             $dir_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
            //             $dir_shareholder->is_srilankan = ( isset( $director['type'] ) && $director['type'] == 'foreign' ) ? 'no' : 'yes';
            //             $dir_shareholder->title = $director['title'];
            //             $dir_shareholder->first_name = $director['firstname'];
            //             $dir_shareholder->last_name =$director['lastname'];
            //             $dir_shareholder->address_id = $shareHolderAddressId;
            //             $dir_shareholder->foreign_address_id = $shareHolderAddressForId;
            //             $dir_shareholder->nic = strtoupper($director['nic']);    
            //             $dir_shareholder->passport_no = strtoupper($director['passport']);
            //             $dir_shareholder->passport_issued_country = ( isset( $director['type'] ) && $director['type'] == 'foreign' && isset($director['passport_issued_country']) ) ? $director['passport_issued_country'] :  'Sri Lanka';
            //             $dir_shareholder->telephone =$director['phone'];
            //             $dir_shareholder->mobile =$director['mobile'];
            //             $dir_shareholder->email =$director['email'];
            //             $dir_shareholder->occupation =$director['occupation'];
            //           //  $dir_shareholder->no_of_shares ='100';
            //             $dir_shareholder->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
            //             $dir_shareholder->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
            //             $dir_shareholder->save();
            //             $newDirShareHolderID = $dir_shareholder->id;
            //             $selectedShareGroup='';
            //             if(isset($director['coreGroupSelected']) && intval( $director['coreGroupSelected'])){
            //                 $selectedShareGroup =  $director['coreGroupSelected'];
            //             }
            //             if(isset($director['coreGroupSelectedEdit']) && intval( $director['coreGroupSelectedEdit'])){
            //                 $selectedShareGroup =  $director['coreGroupSelectedEdit'];
            //             }
            //             //add to share table
            //             $dir_shareholder_share = new Share;
            //             $dir_shareholder_share->company_member_id = $newDirShareHolderID;
            //             $dir_shareholder_share->group_id =intval( $selectedShareGroup);
            //             $dir_shareholder_share->save();

            //             $itemChange = new CompanyItemChange;
            //             $itemChange->request_id = $requestId;
            //             $itemChange->changes_type = $this->settings('ADD','key')->id;
            //             $itemChange->item_id =  $newDirShareHolderID;
            //             $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
            //             $itemChange->save();
            //         }    
            //         if( 
            //             ( isset($director['shareType']) && $director['shareType'] == 'core' && empty( $director['coreGroupSelected'])  && $director['coreShareGroupName'] && intval($director['coreShareValue']) ) ||
            //             ( isset($director['shareTypeEdit']) && $director['shareTypeEdit'] == 'core' && empty( $director['coreGroupSelectedEdit'])  && $director['coreShareGroupNameEdit'] && intval($director['coreShareValueEdit']) )
            //         ) {
            //             $dir_shareholder = new CompanyMember;
            //             $dir_shareholder->company_id = $company_id;
            //             $dir_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
            //             $dir_shareholder->is_srilankan = ( isset( $director['type'] ) && $director['type'] == 'foreign' ) ? 'no' : 'yes';
            //             $dir_shareholder->title = $director['title'];
            //             $dir_shareholder->first_name = $director['firstname'];
            //             $dir_shareholder->last_name =$director['lastname'];
            //             $dir_shareholder->address_id = $shareHolderAddressId;
            //             $dir_shareholder->foreign_address_id = $shareHolderAddressForId;
            //             $dir_shareholder->nic = strtoupper($director['nic']);
            //             $dir_shareholder->passport_no = strtoupper($director['passport']);
            //             $dir_shareholder->passport_issued_country = ( isset( $director['type'] ) && $director['type'] == 'foreign' && isset($director['passport_issued_country']) ) ? $director['passport_issued_country'] :  'Sri Lanka';
            //             $dir_shareholder->telephone =$director['phone'];
            //             $dir_shareholder->mobile =$director['mobile'];
            //             $dir_shareholder->email =$director['email'];
            //             $dir_shareholder->occupation =$director['occupation'];
            //           //  $dir_shareholder->no_of_shares ='100';
            //             $dir_shareholder->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
            //             $dir_shareholder->status = $this->settings('COMMON_STATUS_PENDING','key')->id;    
            //             $dir_shareholder->save();
            //             $newDirShareHolderID = $dir_shareholder->id;

            //             $itemChange = new CompanyItemChange;
            //             $itemChange->request_id = $requestId;
            //             $itemChange->changes_type = $this->settings('ADD','key')->id;
            //             $itemChange->item_id =  $newDirShareHolderID;
            //             $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
            //             $itemChange->save();

            //             $coreShareGroupName='';
            //             $coreShareValue = '';
            //             if(isset($director['shareType']) && $director['shareType'] == 'core' && empty( $director['coreGroupSelected'])  && $director['coreShareGroupName'] && intval($director['coreShareValue'])){

            //                 $coreShareGroupName = $director['coreShareGroupName'];
            //                 $coreShareValue = $director['coreShareValue'];
            //             }
            //             if(isset($director['shareTypeEdit']) && $director['shareTypeEdit'] == 'core' && empty( $director['coreGroupSelectedEdit']) && $director['coreShareGroupNameEdit'] && intval($director['coreShareValueEdit'])){

            //                 $coreShareGroupName = $director['coreShareGroupNameEdit'];
            //                 $coreShareValue = $director['coreShareValueEdit'];
            //             }    
            //             //add to single share group
            //             $dir_shareholder_sharegroup = new ShareGroup;
            //             $dir_shareholder_sharegroup->type ='core_share';
            //             $dir_shareholder_sharegroup->name = $coreShareGroupName;
            //             $dir_shareholder_sharegroup->no_of_shares =intval( $coreShareValue );
            //             $dir_shareholder_sharegroup->company_id = $company_id;
            //             $dir_shareholder_sharegroup->status = 1;
    
            //             $dir_shareholder_sharegroup->save();
            //             $dir_shareholder_sharegroupID = $dir_shareholder_sharegroup->id;
    
            //             //add to share table
            //             $dir_shareholder_share = new Share;
            //             $dir_shareholder_share->company_member_id = $newDirShareHolderID;
            //             $dir_shareholder_share->group_id = $dir_shareholder_sharegroupID;
            //             $dir_shareholder_share->save();
            //         }
            //     }  
            //  }

              //end if director is a shareholder

             //if director is a secretory
             if( ( isset($director['isSec']) &&  $director['isSec'] ) || (isset($director['isSecEdit']) &&  $director['isSecEdit']) ){

                $dir_to_sec_address = new Address;
                $dir_to_sec_address->province = $director['province'];
                $dir_to_sec_address->district =  $director['district'];
                $dir_to_sec_address->city =  $director['city'];
                $dir_to_sec_address->address1 =  $director['localAddress1'];
                $dir_to_sec_address->address2 =  $director['localAddress2'];
                $dir_to_sec_address->postcode = $director['postcode'];
                $dir_to_sec_address->country = 'Sri Lanka';                
                $dir_to_sec_address->save();
                $secAddressId = $dir_to_sec_address->id;

                $dir_sec = new CompanyMember;
                        $dir_sec->company_id = $company_id;
                        $dir_sec->designation_type = $this->settings('SECRETARY','key')->id;
                        $dir_sec->is_srilankan = 'yes';
                        $dir_sec->title = $director['title'];
                        $dir_sec->first_name = $director['firstname'];
                        $dir_sec->last_name =$director['lastname'];
                        $dir_sec->address_id = $secAddressId;
                        $dir_sec->nic = strtoupper($director['nic']);
    
                        $dir_sec->passport_issued_country ='Sri Lanka';
                        $dir_sec->telephone =$director['phone'];
                        $dir_sec->mobile =$director['mobile'];
                        $dir_sec->email =$director['email'];
                        $dir_sec->occupation =$director['occupation'];
                      //  $dir_sec->no_of_shares ='0';
                        $dir_sec->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
                        $dir_sec->is_registered_secretary = isset($director['secRegDate']) ? 'yes'  : 'no';
                        $dir_sec->secretary_registration_no = isset($director['secRegDate']) ? $director['secRegDate'] : NULL;
                        $dir_sec->is_natural_person ="yes";
                        $dir_sec->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                        $dir_sec->save();
                        $newDirSecID = $dir_sec->id;

                        $itemChange = new CompanyItemChange;
                        $itemChange->request_id = $requestId;
                        $itemChange->changes_type = $this->settings('ADD','key')->id;
                        $itemChange->item_id =  $newDirSecID;
                        $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                        $itemChange->save();
            }
            $isNewDirector;
            if(isset($director['id']) && $director['id'] ){
                $updateDirector = CompanyMember::find($director['id']);
                $isNewDirector = false;
            }else{
                $updateDirector = new CompanyMember;
                $isNewDirector = true;
            }
            $updateDirector->company_id = $company_id;
            $updateDirector->designation_type =  $this->settings('DERECTOR','key')->id;
            $updateDirector->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';            
            $company_info = Company::where('id',$company_id)->first(); 
            $process_status = $this->settings($company_info->status,'id')->key;
            $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
            $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
            $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
            $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
            if(!$process_status_val){
                $updateDirector->title = $director['title'];
                $updateDirector->first_name = $director['firstname'];
                $updateDirector->last_name = $director['lastname'];
                $updateDirector->nic = strtoupper($director['nic']);
                $updateDirector->passport_no = $director['passport'];
            }            
            $updateDirector->address_id = $addressId;
            $updateDirector->foreign_address_id =  $forAddressId;
            $updateDirector->passport_issued_country = isset( $director['passport_issued_country']) ? $director['passport_issued_country'] : $director['country'];
            $updateDirector->telephone = $director['phone'];
            $updateDirector->mobile =$director['mobile'];
            $updateDirector->email = $director['email'];
           // $updateDirector->foreign_address_id =($director['type'] !='local') ? $addressId: 0;
            $updateDirector->occupation = $director['occupation'];
           // $updateDirector->no_of_shares = $director['share'];
            $updateDirector->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
            $updateDirector->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
            $updateDirector->save();

            if($isNewDirector){
                $itemChange = new CompanyItemChange;
                $itemChange->request_id = $requestId;
                $itemChange->changes_type = $this->settings('ADD','key')->id;
                $itemChange->item_id =  $updateDirector->id;
                $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                $itemChange->save();
            }             

             //add to peoples table

            //  if( $director['nic'] ){
            //     $check_people = People::where('nic', $director['nic'] )->count();
            //     if($check_people == 0 ){
            //         $people = new People;
            //         $people->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
            //         $people->title = $this->settings('TITLE_MR','key')->id;
            //         $people->first_name = $director['firstname'];
            //         $people->last_name =$director['lastname'];
            //         $people->address_id = $addressId;
            //         $people->foreign_address_id =  $forAddressId;
            //         $people->nic = strtoupper($director['nic']);
            //         $people->passport_issued_country ='Sri Lanka';
            //         $people->telephone =$director['phone'];
            //         $people->mobile =$director['mobile'];
            //         $people->email =$director['email'];
            //         $people->occupation =$director['occupation'];
            //         $people->sex ='male';
            //         $people->status =1;
            //         $people->save();
            //     }
            // }


            //add to peoples table

            // if( $director['passport'] ){
            //     $check_people = People::where('passport_no', $director['passport'] )->count();
            //     if($check_people == 0 ){
            //         $people = new People;
            //         $people->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
            //         $people->title = $this->settings('TITLE_MR','key')->id;
            //         $people->first_name = $director['firstname'];
            //         $people->last_name =$director['lastname'];
            //         $people->address_id = $addressId;
            //         $people->foreign_address_id =  $forAddressId;
            //         $people->passport_no = strtoupper($director['passport']);
            //         $people->passport_issued_country =$director['passport_issued_country'];
            //         $people->telephone =$director['phone'];
            //         $people->mobile =$director['mobile'];
            //         $people->email =$director['email'];
            //         $people->occupation =$director['occupation'];
            //         $people->sex ='male';
            //         $people->status =1;
            //         $people->save();
            //     }
            // }
        }
        
        //loop through add secrotory list
        foreach($request->secretories['secs'] as $sec ){

             $companyFirmAddressId = null;
             $addressId= null;
             $forAddressId = null;     
            if( $sec['secType'] == 'firm' ) {

                $companyFirmAddress = new Address;
                $companyFirmAddress->province = $sec['firm_province'];
                $companyFirmAddress->district =  $sec['firm_district'];
                $companyFirmAddress->city =  $sec['firm_city'];
                $companyFirmAddress->address1 =  $sec['firm_localAddress1'];
                $companyFirmAddress->address2 =  $sec['firm_localAddress2'];
                $companyFirmAddress->postcode = $sec['firm_postcode'];
                $companyFirmAddress->country = isset($sec['firm_country'] ) ? $sec['firm_country'] : 'Sri Lanka';              
                $companyFirmAddress->save();
                $companyFirmAddressId = $companyFirmAddress->id;

                $isNewSecretaryFirm;
                if(isset($sec['id']) && $sec['id'] ){
                    $updateSec = CompanyFirms::find($sec['id']);
                    $isNewSecretaryFirm = false;
                }else{
                    $updateSec = new CompanyFirms;
                    $isNewSecretaryFirm = true;
                }
            } else {

                if($sec['province'] || $sec['district'] ||  $sec['city'] || $sec['localAddress1'] || $sec['localAddress2'] || $sec['postcode'] ) {
                 $address = new Address;
               //  $address->id = 9999;
                 $address->province = $sec['province'];
                 $address->district =  $sec['district'];
                 $address->city =  $sec['city'];
                 $address->address1 =  $sec['localAddress1'];
                 $address->address2 =  $sec['localAddress2'];
                 $address->postcode = $sec['postcode'];
                 $address->country =  'Sri Lanka';               
                 $address->save();
                 $addressId = $address->id;
                }
                
                $postcodecheck =  ($companyType->key === 'COMPANY_TYPE_OVERSEAS' || $companyType->key === 'COMPANY_TYPE_OFFSHORE') ? true : isset($sec['forPostcode']);
                if(
                   ( isset( $sec['forProvince'] ) && isset($sec['forCity']) && isset($sec['forAddress1']) && isset($sec['forAddress2']) && $postcodecheck ) && 
                   ( $sec['forProvince'] ||  $sec['forCity'] || $sec['forAddress1'] || $sec['forAddress2'] || $sec['forPostcode'] )
                    
                ) {
                 $forAddress = new Address;
               //  $address->id = 9999;
                 $forAddress->province = $sec['forProvince'];
                 $forAddress->district = null;
                 $forAddress->city =  $sec['forCity'];
                 $forAddress->address1 =  $sec['forAddress1'];
                 $forAddress->address2 =  $sec['forAddress2'];
                 $forAddress->postcode = $sec['forPostcode'];
                 $forAddress->country =  $sec['country'];
               
                 $forAddress->save();
                 $forAddressId = $forAddress->id;
                }
                $isNewSecretary;
                if(isset($sec['id']) && $sec['id'] ){
                    $updateSec = CompanyMember::find($sec['id']);
                    $isNewSecretary = false;
                }else{
                    $updateSec = new CompanyMember;
                    $isNewSecretary = true;
                }
            }

            $newSecShareHolderID = null;           
            //if sec as a shareholder
            // if(
            //     ( isset($sec['isShareholder']) &&  $sec['isShareholder'] ) || 
            //     ( isset($sec['isShareholderEdit']) &&  $sec['isShareholderEdit'] )
            // ){
            //     if(
            //          ( isset($sec['shareType']) && $sec['shareType'] ) || 
            //          ( isset($sec['shareTypeEdit']) && $sec['shareTypeEdit'] )
            //     ){

            //      if($sec['secType'] == 'firm'){
            //             $secFirmId = ( isset($sec['id']) && $sec['id'] ) ? $sec['id'] : $updateSec->id;
            //             if(isset($sec['id']) && $sec['id'] ){
            //                 $cf = CompanyFirms::find($sec['id']);
                           
            //                 $sec_as_sh_count =  ( isset($cf->sh_firm_of) &&  intval( $cf->sh_firm_of ) > 0 )  ? 1 : 0 ;
            //             } else {
            //                 $sec_as_sh_count = 0;
            //             }
            //         if( $sec_as_sh_count  == 0 ) { // check shareholdr firm

            //             $sec_shareholder = new CompanyFirms;
            //             $sec_shareholder->registration_no = $sec['pvNumber'];
            //             $sec_shareholder->name = $sec['firm_name'];
            //             $sec_shareholder->email = $sec['firm_email'];
            //             $sec_shareholder->mobile = $sec['firm_mobile'];
            //             $sec_shareholder->phone = $sec['firm_phone'];
            //             $sec_shareholder->date_of_appointment = $sec['firm_date'];
            //             $sec_shareholder->company_id = $company_id;
            //             $sec_shareholder->address_id = $companyFirmAddressId;
            //             $sec_shareholder->type_id = $this->settings('SHAREHOLDER','key')->id;
            //             $sec_shareholder->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
            //           //  $sec_shareholder->sec_firm_id = $secFirmId;
            //             $sec_shareholder->save();
            //             $newSecShareHolderID = $sec_shareholder->id;

            //             $itemChange = new CompanyItemChange;
            //             $itemChange->request_id = $requestId;
            //             $itemChange->changes_type = $this->settings('ADD','key')->id;
            //             $itemChange->item_id =  $newSecShareHolderID;
            //             $itemChange->item_table_type =  $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
            //             $itemChange->save();

            //             //ADD BENIFICIARY OWNER
            //             if( isset($sec['secBenifList']['ben'])  &&  is_array($sec['secBenifList']['ben'])) {
            //                 //first remove all records of benif
            //                 CompanyMember::where('company_id', $company_id)
            //                             ->where('company_member_firm_id', $newSecShareHolderID )
            //                             ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
            //                             ->where('is_beneficial_owner', 'yes')
            //                             ->delete();
            //                 foreach(  $sec['secBenifList']['ben'] as $ben ) {
            //                     $benAddress = new Address;
            //                     $benAddress->province = $ben['province'];
            //                     $benAddress->district =  ($ben['type'] == 'local' ) ? $ben['district'] : null;
            //                     $benAddress->city =  $ben['city'];
            //                     $benAddress->address1 =  $ben['localAddress1'];
            //                     $benAddress->address2 =  $ben['localAddress2'];
            //                     $benAddress->postcode = $ben['postcode'];
            //                     $benAddress->country =  ($ben['type'] == 'local' ) ? 'Sri Lanka' : $ben['country'];
                
            //                     $benAddress->save();
            //                     $benAddress_id = $benAddress->id;
            //                     $benuUser = new CompanyMember;
            //                     $benuUser->company_id = $company_id;
            //                     $benuUser->designation_type = $this->settings('SHAREHOLDER','key')->id;
            //                     $benuUser->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
            //                     $benuUser->title = $ben['title'];
            //                     $benuUser->first_name = $ben['firstname'];
            //                     $benuUser->last_name = $ben['lastname'];
            //                     $benuUser->address_id = $benAddress_id;
            //                     $benuUser->nic = ( $ben['type'] == 'local' ) ? strtoupper($ben['nic']) : null;
            //                     $benuUser->passport_no = ( $ben['type'] == 'local' ) ? null : $ben['passport'];
            //                     $benuUser->passport_issued_country = ( $ben['type'] == 'local' )  ? null : $ben['country'];
            //                     $benuUser->telephone = $ben['phone'];
            //                     $benuUser->mobile =$ben['mobile'];
            //                     $benuUser->email = $ben['email'];
            //                     $benuUser->is_beneficial_owner = 'yes';
            //                     $benuUser->company_member_firm_id = $newSecShareHolderID;                            
            //                     $benuUser->occupation = $ben['occupation'];
            //                     $benuUser->date_of_appointment = date('Y-m-d',strtotime($ben['date']) );
            //                     $benuUser->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
            //                     $benuUser->save();
            //                     $benUserId = $benuUser->id;
                                
            //                     $itemChange = new CompanyItemChange;
            //                     $itemChange->request_id = $requestId;
            //                     $itemChange->changes_type = $this->settings('ADD','key')->id;
            //                     $itemChange->item_id =  $benUserId;
            //                     $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
            //                     $itemChange->save();
            //                     }         
            //                 }
            //             } //end check of already have shareholder firm
            //           }else{

            //             $sec_shareholder = new CompanyMember;
            //             $sec_shareholder->company_id = $company_id;
            //             $sec_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
            //             $sec_shareholder->is_srilankan = 'yes';
            //             $sec_shareholder->title = 'Mr.'; //$sec['title'];
            //             $sec_shareholder->first_name = $sec['firstname'];
            //             $sec_shareholder->last_name =$sec['lastname'];
            //             $sec_shareholder->address_id = $addressId;
            //             $sec_shareholder->nic = strtoupper($sec['nic']);    
            //             $sec_shareholder->passport_issued_country ='Sri Lanka';
            //             $sec_shareholder->telephone =$sec['phone'];
            //             $sec_shareholder->mobile =$sec['mobile'];
            //             $sec_shareholder->email =$sec['email'];
            //             $sec_shareholder->occupation =$sec['occupation'];
            //           //  $sec_shareholder->no_of_shares ='100';
            //             $sec_shareholder->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
            //             $sec_shareholder->status = $this->settings('COMMON_STATUS_PENDING','key')->id;    
            //             $sec_shareholder->save();
            //             $newSecShareHolderID = $sec_shareholder->id;

            //             $itemChange = new CompanyItemChange;
            //             $itemChange->request_id = $requestId;
            //             $itemChange->changes_type = $this->settings('ADD','key')->id;
            //             $itemChange->item_id =  $newSecShareHolderID;
            //             $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
            //             $itemChange->save();
            //           }    
            //         if( 
            //             ( isset($sec['shareType']) && $sec['shareType'] == 'single' && isset($sec['noOfSingleShares']) && intval($sec['noOfSingleShares']) ) || 
            //             ( isset($sec['shareTypeEdit']) && $sec['shareTypeEdit'] == 'single' && isset($sec['noOfSingleSharesEdit']) && intval($sec['noOfSingleSharesEdit']) )
                        
            //         ) {
            //             $singleShares=0;
            //             if(isset($sec['noOfSingleShares']) &&  intval($sec['noOfSingleShares'])){
            //                 $singleShares = intval($sec['noOfSingleShares']);
            //             }
            //             if( isset($sec['noOfSingleSharesEdit']) &&  intval($sec['noOfSingleSharesEdit']) ){
            //                 $singleShares = intval($sec['noOfSingleSharesEdit']);
            //             }  
            //             //add to single share group
            //             $sec_shareholder_sharegroup = new ShareGroup;
            //             $sec_shareholder_sharegroup->type ='single_share';
            //             $sec_shareholder_sharegroup->name ='single_share_no_name';
            //             $sec_shareholder_sharegroup->no_of_shares = $singleShares;
            //             $sec_shareholder_sharegroup->company_id = $company_id;
            //             $sec_shareholder_sharegroup->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
    
            //             $sec_shareholder_sharegroup->save();
            //             $sec_shareholder_sharegroupID = $sec_shareholder_sharegroup->id;    
            //             //add to share table    
            //             $sec_shareholder_share = new Share;
            //             if( $sec['secType'] == 'firm'){
            //                 $sec_shareholder_share->company_firm_id = $newSecShareHolderID;
            //             }else {
            //                 $sec_shareholder_share->company_member_id = $newSecShareHolderID;
            //             }
            //             $sec_shareholder_share->group_id = $sec_shareholder_sharegroupID;
            //             $sec_shareholder_share->save();
            //         }    
            //         if(
            //            ( isset( $sec['shareType']) &&  $sec['shareType'] == 'core' &&  isset($sec['coreGroupSelected']) && intval( $sec['coreGroupSelected']) ) || 
            //            ( isset( $sec['shareTypeEdit']) &&  $sec['shareTypeEdit'] == 'core' &&  isset($sec['coreGroupSelectedEdit']) && intval( $sec['coreGroupSelectedEdit']) )
                    
            //         ){    
            //             $selectedShareGroup='';
            //             if(isset($sec['coreGroupSelected']) && intval( $sec['coreGroupSelected'])){
            //                 $selectedShareGroup =  $sec['coreGroupSelected'];
            //             }
            //             if(isset($sec['coreGroupSelectedEdit']) && intval( $sec['coreGroupSelectedEdit'])){
            //                 $selectedShareGroup =  $sec['coreGroupSelectedEdit'];
            //             }    
            //             //add to share table
            //             $sec_shareholder_share = new Share;
            //             if( $sec['secType'] == 'firm'){
            //                 $sec_shareholder_share->company_firm_id = $newSecShareHolderID;
            //             }else {
            //                 $sec_shareholder_share->company_member_id = $newSecShareHolderID;
            //             }
            //             $sec_shareholder_share->group_id =intval($selectedShareGroup );
            //             $sec_shareholder_share->save();
            //         }    
            //         if( 
            //             ( isset( $sec['shareType'] ) &&  $sec['shareType'] == 'core' && empty( $sec['coreGroupSelected'])  && $sec['coreShareGroupName'] && intval($sec['coreShareValue']) ) || 
            //             ( isset( $sec['shareTypeEdit'] ) &&  $sec['shareTypeEdit'] == 'core' && empty( $sec['coreGroupSelectedEdit'])  && $sec['coreShareGroupNameEdit'] && intval($sec['coreShareValueEdit']) )
                        
            //         ) {    
            //             $coreShareGroupName='';
            //             $coreShareValue = '';
            //             if(isset($sec['shareType']) && $sec['shareType'] == 'core' && empty( $sec['coreGroupSelected'])  && $sec['coreShareGroupName'] && intval($sec['coreShareValue'])){

            //                 $coreShareGroupName = $sec['coreShareGroupName'];
            //                 $coreShareValue = $sec['coreShareValue'];
            //             }
            //             if(isset($sec['shareTypeEdit']) && $sec['shareTypeEdit'] == 'core' && empty( $sec['coreGroupSelectedEdit']) && $sec['coreShareGroupNameEdit'] && intval($sec['coreShareValueEdit'])){

            //                 $coreShareGroupName = $sec['coreShareGroupNameEdit'];
            //                 $coreShareValue = $sec['coreShareValueEdit'];
            //             }        
            //             //add to single share group
            //             $sec_shareholder_sharegroup = new ShareGroup;
            //             $sec_shareholder_sharegroup->type ='core_share';
            //             $sec_shareholder_sharegroup->name = $coreShareGroupName;
            //             $sec_shareholder_sharegroup->no_of_shares =intval( $coreShareValue );
            //             $sec_shareholder_sharegroup->company_id = $company_id;
            //             $sec_shareholder_sharegroup->status = 1;    
            //             $sec_shareholder_sharegroup->save();
            //             $sec_shareholder_sharegroupID = $sec_shareholder_sharegroup->id;
    
            //             //add to share table
            //             $sec_shareholder_share = new Share;
            //             if( $sec['secType'] == 'firm'){
            //                 $sec_shareholder_share->company_firm_id = $newSecShareHolderID;
            //             }else {
            //                 $sec_shareholder_share->company_member_id = $newSecShareHolderID;
            //             }
            //             $sec_shareholder_share->group_id = $sec_shareholder_sharegroupID;
            //             $sec_shareholder_share->save();
            //         }
            //    }    
            // } //end if sesc is a shareholder

            if( $sec['secType'] == 'firm' ) {

                if(isset($sec['id']) && $sec['id'] ){
                    $cf = CompanyFirms::find($sec['id']);
                    $sec_as_sh_count =  ( isset($cf->sh_firm_of) &&  intval( $cf->sh_firm_of ) > 0 )  ? 1 : 0 ;
                } else {
                    $sec_as_sh_count = 0;
                }
                $updateSec->email  = $sec['firm_email'];
                $updateSec->mobile = $sec['firm_mobile'];
                $updateSec->phone  = $sec['firm_phone'];
                $updateSec->date_of_appointment = $sec['firm_date'];
                $updateSec->company_id = $company_id;
                $updateSec->address_id = $companyFirmAddressId;
                $updateSec->type_id = $this->settings('SECRETARY','key')->id;
                $updateSec->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes'; 

                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                    $updateSec->registration_no = $sec['pvNumber'];
                    $updateSec->name = $sec['firm_name'];
                }

                if($sec_as_sh_count == 0) {
                    $updateSec->sh_firm_of = ( $newSecShareHolderID ) ? $newSecShareHolderID : null;;
                }
                $updateSec->save();
                    if($isNewSecretaryFirm){
                        $itemChange = new CompanyItemChange;
                        $itemChange->request_id = $requestId;
                        $itemChange->changes_type = $this->settings('ADD','key')->id;
                        $itemChange->item_id =  $updateSec->id;
                        $itemChange->item_table_type =  $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                        $itemChange->save();
                    }               
            }else {
                $updateSec->company_id = $company_id;
                $updateSec->designation_type = $this->settings('SECRETARY','key')->id;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
            // $updateSec->title = $sec['title'];
                
                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                    $updateSec->title = $sec['title'];
                    $updateSec->first_name = $sec['firstname'];
                    $updateSec->last_name = $sec['lastname']; 
                    $updateSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
                    $updateSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
                }
                $updateSec->address_id = $addressId;
                $updateSec->foreign_address_id = $forAddressId;
                $updateSec->passport_issued_country = isset( $sec['passport_issued_country'] ) ? $sec['passport_issued_country']  : null ;
                $updateSec->telephone = $sec['phone'];
                $updateSec->mobile =$sec['mobile'];
                $updateSec->email = $sec['email'];
            // $updateSec->foreign_address_id =($sec['type'] !='local') ? $addressId: 0;
                $updateSec->occupation = $sec['occupation'];
            //  $updateSec->no_of_shares =0;
                $updateSec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                $updateSec->is_registered_secretary = ($sec['isReg'] == true ) ? 'yes' : 'no';
                $updateSec->secretary_registration_no =  (isset($sec['regDate']) && $sec['regDate'] ) ? $sec['regDate'] : NULL;
                $updateSec->status = $this->settings('COMMON_STATUS_PENDING','key')->id;    
                $updateSec->is_natural_person = $sec['secType'] =='natural' ? 'yes' : 'no';
              //  $updateSec->company_member_firm_id = $companyFirmId;
                $updateSec->save();
                    if($isNewSecretary){
                        $itemChange = new CompanyItemChange;
                        $itemChange->request_id = $requestId;
                        $itemChange->changes_type = $this->settings('ADD','key')->id;
                        $itemChange->item_id =  $updateSec->id;
                        $itemChange->item_table_type =  $this->settings('COMPANY_MEMBERS','key')->id;
                        $itemChange->save();
                    }   
                 //add to peoples table

                // $check_people = People::where('nic', $sec['nic'] )->count();
                // if($check_people == 0 ){

                //     $people = new People;
                //     $people->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
                //     $people->title = $this->settings('TITLE_MR','key')->id;
                //     $people->first_name = $sec['firstname'];
                //     $people->last_name =$sec['lastname'];
                //     $people->address_id = $addressId;
                //     $people->nic = strtoupper($sec['nic']);

                //     $people->passport_issued_country ='Sri Lanka';
                //     $people->telephone =$sec['phone'];
                //     $people->mobile =$sec['mobile'];
                //     $people->email =$sec['email'];
                //     $people->occupation =$sec['occupation'];
                //     $people->sex ='male';
                //     $people->status =1;
                //     $people->save();

                // }
            }            
        }       

        //loop through add shareholder list
        // foreach($request->shareholders['shs'] as $shareholder ){

        //     $address = new Address;
        //     $forAddress = new Address;
        //     $addressId= null;
        //     $forAddressId = null;    

        //     if( $shareholder['shareholderType'] === 'natural' ){
        //         if( $shareholder['province'] || $shareholder['district'] || $shareholder['city'] || $shareholder['localAddress1'] || $shareholder['localAddress2'] || $shareholder['postcode'] ) {
        //             $address->province = $shareholder['province'];
        //             $address->district =  ($shareholder['type'] == 'local') ? $shareholder['district'] : null;
        //             $address->city =  $shareholder['city'];
        //             $address->address1 =  $shareholder['localAddress1'];
        //             $address->address2 =  $shareholder['localAddress2'];
        //             $address->postcode =  $shareholder['postcode'];
        //             $address->country =  'Sri Lanka';
        //         }
                
        //     } else {
        //         $address->province = $shareholder['firm_province'];
        //         $address->district =  ( $shareholder['type'] == 'local') ? $shareholder['firm_district'] : '' ;
        //         $address->city =  $shareholder['firm_city'];
        //         $address->address1 =  $shareholder['firm_localAddress1'];
        //         $address->address2 =  $shareholder['firm_localAddress2'];
        //         $address->postcode = $shareholder['firm_postcode'];
        //         $address->country = $shareholder['country'];
        //     }
        //     $address->save();
        //     $addressId = $address->id;
        //     if( $shareholder['shareholderType'] === 'natural' ){

        //             if( @$shareholder['forProvince'] || @$shareholder['forCity'] || @$shareholder['forAddress1'] || @$shareholder['forAddress2'] || @$shareholder['forPostcode']) {
        //                 $forAddress->province = @$shareholder['forProvince'];
        //                 $forAddress->city =  @$shareholder['forCity'];
        //                 $forAddress->address1 =  @$shareholder['forAddress1'];
        //                 $forAddress->address2 =  @$shareholder['forAddress2'];
        //                 $forAddress->postcode =  @$shareholder['forPostcode'];
        //                 $forAddress->country =   $shareholder['country'];
        //                 $forAddress->save();
        //                 $forAddressId = $forAddress->id;
        //             }
        //     }
        //     if ( $shareholder['shareholderType'] === 'natural' ) {

        //         if(isset($shareholder['id']) && $shareholder['id'] ){
        //             $updateSh = CompanyMember::find($shareholder['id']);
        //         }else{
        //             $updateSh = new CompanyMember;
        //         }
        //         $updateSh->company_id = $company_id;
        //         $updateSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
        //         $updateSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                
        //         $company_info = Company::where('id',$company_id)->first(); 
        //         $process_status = $this->settings($company_info->status,'id')->key;
        //         $process_status_val =  ( $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
        //         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
        //         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
        //         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
               
        //         $updateSh->title = $shareholder['title'];
        //         $updateSh->first_name = $shareholder['firstname'];
        //         $updateSh->last_name = $shareholder['lastname'];
        //         $updateSh->nic = strtoupper($shareholder['nic']);
        //         $updateSh->passport_no = $shareholder['passport'];
        //         $updateSh->address_id = $addressId;
        //         $updateSh->foreign_address_id = $forAddressId;
        //         $updateSh->passport_issued_country = isset($shareholder['passport_issued_country']) ?  $shareholder['passport_issued_country'] : $shareholder['country'];
        //         $updateSh->telephone = $shareholder['phone'];
        //         $updateSh->mobile =$shareholder['mobile'];
        //         $updateSh->email = $shareholder['email'];
        //         $updateSh->occupation = $shareholder['occupation'];
        //         $updateSh->date_of_appointment = date('Y-m-d',strtotime($shareholder['date']) );
        //         $updateSh->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
        //         $updateSh->save();

        //         $shareHolderId = ( isset($shareholder['id']) && $shareholder['id'] ) ? $shareholder['id'] : $updateSh->id;

        //           //add to peoples table
        //      if( $shareholder['nic'] ){
        //         $check_people = People::where('nic', $shareholder['nic'] )->count();
        //         if($check_people == 0 ){
        //             $people = new People;
        //             $people->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
        //             $people->title = $this->settings('TITLE_MR','key')->id;
        //             $people->first_name = $shareholder['firstname'];
        //             $people->last_name =$shareholder['lastname'];
        //             $people->address_id = $addressId;
        //             $people->nic = strtoupper($shareholder['nic']);
        //             $people->passport_issued_country ='Sri Lanka';
        //             $people->telephone =$shareholder['phone'];
        //             $people->mobile =$shareholder['mobile'];
        //             $people->email =$shareholder['email'];
        //             $people->occupation =$shareholder['occupation'];
        //             $people->sex ='male';
        //             $people->status =1;
        //             $people->save();
        //         }
        //     }
        //     //add to peoples table
        //     if( $shareholder['passport'] ){
        //         $check_people = People::where('passport_no', $shareholder['passport'] )->count();
        //         if($check_people == 0 ){
        //             $people = new People;
        //             $people->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
        //             $people->title = $this->settings('TITLE_MR','key')->id;
        //             $people->first_name = $shareholder['firstname'];
        //             $people->last_name =$shareholder['lastname'];
        //             $people->address_id = $addressId;
        //             $people->passport_no = strtoupper($shareholder['passport']);
        //             $people->passport_issued_country =$shareholder['country'];
        //             $people->passport_issued_country = isset($shareholder['passport_issued_country']) ?  $shareholder['passport_issued_country'] : $shareholder['country'];
        //             $people->telephone =$shareholder['phone'];
        //             $people->mobile =$shareholder['mobile'];
        //             $people->email =$shareholder['email'];
        //             $people->occupation =$shareholder['occupation'];
        //             $people->sex ='male';
        //             $people->status =1;
        //             $people->save();
        //         }
        //     }            
        //     } else {
        //         if(isset($shareholder['id']) && $shareholder['id'] ){
        //             $updateSh = CompanyFirms::find($shareholder['id']);
        //         }else{
        //             $updateSh = new CompanyFirms;
        //         }
        //         $company_info = Company::where('id',$company_id)->first(); 
        //         $process_status = $this->settings($company_info->status,'id')->key;
        //         $process_status_val =  ( $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
        //         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
        //         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
        //         $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
        //         //if(!$process_status_val){
        //             $updateSh->registration_no = $shareholder['pvNumber'];
        //             $updateSh->name = $shareholder['firm_name'];                    
        //         //}                
        //         $updateSh->email = $shareholder['firm_email'];
        //         $updateSh->mobile = $shareholder['firm_mobile'];
        //         $updateSh->date_of_appointment = $shareholder['firm_date'];
        //         $updateSh->company_id = $company_id;
        //         $updateSh->address_id = $addressId;
        //         $updateSh->type_id = $this->settings('SHAREHOLDER','key')->id;
        //         $updateSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
        //         $updateSh->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
        //         $updateSh->save();

        //         $shareHolderId = ( isset($shareholder['id']) && $shareholder['id'] ) ? $shareholder['id'] : $updateSh->id;

        //         if( isset($shareholder['benifiList']['ben'])  &&  is_array($shareholder['benifiList']['ben'])) {

        //             //first remove all records of benif
        //             CompanyMember::where('company_id', $company_id)
        //                           ->where('company_member_firm_id', $shareHolderId)
        //                           ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        //                           ->where('is_beneficial_owner', 'yes')
        //                           ->delete();

        //             foreach(  $shareholder['benifiList']['ben'] as $ben ) {

        //                 $benAddress = new Address;
        //                 $benAddress->province = $ben['province'];
        //                 $benAddress->district =  ($ben['type'] == 'local' ) ? $ben['district'] : null;
        //                 $benAddress->city =  $ben['city'];
        //                 $benAddress->address1 =  $ben['localAddress1'];
        //                 $benAddress->address2 =  $ben['localAddress2'];
        //                 $benAddress->postcode = $ben['postcode'];
        //                 $benAddress->country =  ($ben['type'] == 'local' ) ? 'Sri Lanka' : $ben['country'];
        
        //                 $benAddress->save();
        //                 $benAddress_id = $benAddress->id;

        //                 $benuUser = new CompanyMember;
        //                 $benuUser->company_id = $company_id;
        //                 $benuUser->designation_type = $this->settings('SHAREHOLDER','key')->id;
        //                 $benuUser->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
        //                 $benuUser->title = $ben['title'];
        //                 $benuUser->first_name = $ben['firstname'];
        //                 $benuUser->last_name = $ben['lastname'];
        //                 $benuUser->address_id = $benAddress_id;
        //                 $benuUser->nic = ( $ben['type'] == 'local' ) ? strtoupper($ben['nic']) : null;
        //                 $benuUser->passport_no = ( $ben['type'] == 'local' ) ? null : $ben['passport'];
        //                 $benuUser->passport_issued_country = ( $ben['type'] == 'local' )  ? null : $ben['country'];
        //                 $benuUser->telephone = $ben['phone'];
        //                 $benuUser->mobile =$ben['mobile'];
        //                 $benuUser->email = $ben['email'];
        //                 $benuUser->is_beneficial_owner = 'yes';
        //                 $benuUser->company_member_firm_id = $shareHolderId;
                    
        //                 $benuUser->occupation = $ben['occupation'];
        //                 $benuUser->date_of_appointment = date('Y-m-d',strtotime($ben['date']) );
        //                 $benuUser->status = $this->settings('COMMON_STATUS_PENDING','key')->id;

        //                 $benuUser->save();
        //                 $benUserId = $benuUser->id;

        //                 //add to peoples table
        //                 if( $ben['nic'] ){
        //                     $check_people = People::where('nic', $ben['nic'] )->count();
        //                     if($check_people == 0 ){

        //                         $people = new People;
        //                         $people->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
        //                         $people->title = $this->settings('TITLE_MR','key')->id;
        //                         $people->first_name = $ben['firstname'];
        //                         $people->last_name =$ben['lastname'];
        //                         $people->address_id = $benAddress_id;
        //                         $people->nic = strtoupper($ben['nic']);

        //                         $people->passport_issued_country ='Sri Lanka';
        //                         $people->telephone =$ben['phone'];
        //                         $people->mobile =$ben['mobile'];
        //                         $people->email =$ben['email'];
        //                         $people->occupation =$ben['occupation'];
        //                         $people->sex ='male';
        //                         $people->status =1;
        //                         $people->save();
        //                     }
        //                 }

        //                 //add to peoples table
        //                 if( $ben['passport'] ){
        //                     $check_people = People::where('passport_no', $ben['passport'] )->count();
        //                     if($check_people == 0 ){
        //                         $people = new People;
        //                         $people->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
        //                         $people->title = $this->settings('TITLE_MR','key')->id;
        //                         $people->first_name = $ben['firstname'];
        //                         $people->last_name =$ben['lastname'];
        //                         $people->address_id = $benAddress_id;
        //                         $people->passport_no = strtoupper($ben['passport']);
        //                         $people->passport_issued_country =$ben['country'];
        //                         $people->telephone =$ben['phone'];
        //                         $people->mobile =$ben['mobile'];
        //                         $people->email =$ben['email'];
        //                         $people->occupation =$ben['occupation'];
        //                         $people->sex ='male';
        //                         $people->status =1;
        //                         $people->save();
        //                     }
        //                 }
        //             } 
        //         }             
        //     }

        //   //  $shareHolderId = ( isset($shareholder['id']) && $shareholder['id'] ) ? $shareholder['id'] : $updateSh->id;

        //     if(  $shareholder['shareType'] == 'single' && intval($shareholder['noOfShares']) ) {

        //         if(isset($shareholder['id']) && $shareholder['id'] ){

        //            /* if($shareholder['shareholderType']  == 'natural'){
        //                 $shareRow = Share::where('company_member_id', $shareholder['id'] )->first();
        //             }else{
        //                 $shareRow = Share::where('company_firm_id', $shareholder['id'] )->first();
        //             }

        //             $shareholder_share = Share::find($shareRow['id']);*/

        //             if($shareholder['shareholderType']  == 'natural'){
        //                Share::where('company_member_id', $shareholder['id'] )->delete();
        //             }else{
        //                 Share::where('company_firm_id', $shareholder['id'] )->delete();
        //             }
        //             $shareholder_share = new Share;                  
                   
        //         }else{
        //             $shareholder_share = new Share;
        //         }

        //         if(isset($shareholder['id']) && $shareholder['id']  ){
        //               //  $shareholder_sharegroup = ShareGroup::find($shareRow['group_id']);

        //                $shareholder_sharegroup = new ShareGroup;

        //                 $shareholder_sharegroup->type ='single_share';
        //                 $shareholder_sharegroup->name ='single_share_no_name';
        //                 $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfShares'] );
        //                 $shareholder_sharegroup->company_id = $company_id;
        //                 $shareholder_sharegroup->status = 1;
    
        //                 $shareholder_sharegroup->save();
        //                 $shareholder_sharegroupID =  $shareholder_sharegroup->id;
        //         }else{

        //             $shareholder_sharegroup = new ShareGroup;
        //             $shareholder_sharegroup->type ='single_share';
        //             $shareholder_sharegroup->name ='single_share_no_name';
        //             $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfShares'] );
        //             $shareholder_sharegroup->company_id = $company_id;
        //             $shareholder_sharegroup->status = 1;
        //             $shareholder_sharegroup->save();
        //             $shareholder_sharegroupID = $shareholder_sharegroup->id;
        //         }
  
        //         //add to share table
                
        //         if ( $shareholder['shareholderType']  == 'natural' ) {
        //           $shareholder_share->company_member_id = $shareHolderId;
        //         }else{
                    
        //           $shareholder_share->company_firm_id = $shareHolderId;
        //         }
        //         $shareholder_share->group_id = $shareholder_sharegroupID;
        //         $shareholder_share->save();
        //     }

        //     if($shareholder['shareType'] == 'core' && isset($shareholder['coreGroupSelected']) &&  intval( $shareholder['coreGroupSelected']) ){

        //         if(isset($shareholder['id']) && $shareholder['id'] ){

        //             if ( $shareholder['shareholderType']  == 'natural' ) {
        //                 $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
        //                 $singleGroups = array();
        //                 if($companyGroupsCount) {
        //                     $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
        //                     foreach($companyGroups as $g ){
        //                         $singleGroups[] = $g['id'];
        //                     }        
        //                     Share::whereIn('group_id', $singleGroups )->where('company_member_id', $shareHolderId )->delete();
        //                 }        
        //               }else{
        
        //                 $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
        //                 $singleGroups = array();
        //                 if($companyGroupsCount) {
        //                     $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
        //                     foreach($companyGroups as $g ){
        //                         $singleGroups[] = $g['id'];
        //                     }        
        //                     Share::whereIn('group_id', $singleGroups )->where('company_firm_id', $shareHolderId )->delete();
        //                 }
        //             }

        //             if ( $shareholder['shareholderType']  == 'natural' ) {
        //                 $shareRow = Share::where('company_member_id', $shareholder['id'] )->first();
        //             }else {
        //                 $shareRow = Share::where('company_firm_id', $shareholder['id'] )->first();
        //             }
        //             $shareholder_share = Share::find($shareRow['id']);
        //         }else{
        //             $shareholder_share = new Share;
        //         }
        //         if(isset($shareholder['id']) && $shareholder['id'] ){
        //             $shareholder_sharegroup = ShareGroup::find($shareRow['group_id']);
        //         }else{
        //             $shareholder_sharegroup = new ShareGroup;
        //         }
        //         //add to share table               
        //         if ( $shareholder['shareholderType']  == 'natural' ) {
        //            $shareholder_share->company_member_id = $shareHolderId;
        //         }else{
        //             $shareholder_share->company_firm_id = $shareHolderId;
        //         }
        //         $shareholder_share->group_id =intval( $shareholder['coreGroupSelected']);
        //         $shareholder_share->save();
        //     }
        //     if(
        //       $shareholder['shareType'] == 'core' &&
        //        ( empty( $shareholder['coreGroupSelected'])  ||  !intval( $shareholder['coreGroupSelected']) )  &&
        //         isset( $shareholder['coreShareGroupName']) && 
        //         $shareholder['coreShareGroupName'] && 
        //       intval($shareholder['noOfSharesGroup']) ) {              

        //       if ( $shareholder['shareholderType']  == 'natural' ) {            
        //         $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
        //         $singleGroups = array();
        //             if($companyGroupsCount) {
        //                 $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
        //                 foreach($companyGroups as $g ){
        //                     $singleGroups[] = $g['id'];
        //                 }
        //                 Share::whereIn('group_id', $singleGroups )->where('company_member_id', $shareHolderId )->delete();
        //             }
        //             }else{
        //                 $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
        //                 $singleGroups = array();
        //                 if($companyGroupsCount) {
        //                     $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
        //                     foreach($companyGroups as $g ){
        //                         $singleGroups[] = $g['id'];
        //                     }
        //                     Share::whereIn('group_id', $singleGroups )->where('company_firm_id', $shareHolderId )->delete();
        //                 }
        //             }   
        //         //add to single share group
        //         $shareholder_sharegroup = new ShareGroup;
        //         $shareholder_sharegroup->type ='core_share';
        //         $shareholder_sharegroup->name = $shareholder['coreShareGroupName'];
        //         $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfSharesGroup'] );
        //         $shareholder_sharegroup->company_id = $company_id;
        //         $shareholder_sharegroup->status = 1;

        //         $shareholder_sharegroup->save();
        //         $shareholder_sharegroupID = $shareholder_sharegroup->id;

        //         //add to share table
        //         $shareholder_share = new Share;
        //         if ( $shareholder['shareholderType']  == 'natural' ) {
        //             $shareholder_share->company_member_id = $shareHolderId;
        //           }else{
        //             $shareholder_share->company_firm_id = $shareHolderId;
        //           }
        //         $shareholder_share->group_id = $shareholder_sharegroupID;
        //         $shareholder_share->save();
        //     }            
        // }    
        return response()->json([
            'message' => 'Successfully Saved Data',
            'status' =>true,            
        ], 200);
    }

    // to generate director and secretary pdf ...
    public function generateMemberPDF (Request $request){

      

        if($request->type ==='director'){
            if(isset($request->memberId)){
                $directorDetails = CompanyMember::where('id',$request->memberId)->first();
                $fname = $directorDetails['first_name'];
                $lname = $directorDetails['last_name'];
                $fullName =  $fname .' '. $lname  ;
                $addressId = $directorDetails['address_id'];
                $address = Address::where('id',$addressId)->first();
                if(isset($address)){
                   // $address1 = $address['address1'];
                  //  $address2 = $address['address2'];
                   // $city = $address['city'];
                  //  $rAddress = $address1 .' '. $address2 .' '. $city ;

                    $address1 = $address['address1'].',<br/>';
                    $address2 = ($address['address2']) ? $address['address2'].',<br/>' : '';
                    $city = $address['city'].',<br/>';
                    $postcode = 'postcode:'.$address['postcode'];
                    $rAddress = $address1 . $address2 . $city . $postcode ;
                }else{
                    $rAddress = '';
                }
                $companyId = $directorDetails['company_id'];

                $companyCertificate = CompanyCertificate::where('company_id', $companyId)
                ->where('is_sealed', 'yes')
                ->first();
                $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

                $companyDetails = Company::where('id',$companyId)->first();
                $companyName = $companyDetails ->name;
                $occupation = $directorDetails['occupation'];
                $nic = $directorDetails['nic'];
                $passport = $directorDetails['passport_no'];
                $passport_issued_country = $directorDetails['passport_issued_country'];
                $doa = $directorDetails['date_of_appointment'];
                $createdUserId = Company::where('id', $companyId)->value('created_by');
                $createdUserDetails = User::where('id', $createdUserId)->first();
                $loginPeopleId = $createdUserDetails->people_id;
                $usrDetails = People::where('id',$loginPeopleId)->first();
                $ufname = $usrDetails['first_name'];
                $ulname = $usrDetails['last_name'];
                $ufullName =  $ufname .' '. $ulname  ;
                $uemail = $usrDetails['email'];
                $umobile = $usrDetails['mobile'];
                $utelephone = $usrDetails['telephone'];
                $usrAddressId = $usrDetails->address_id;  
                $usrAddress = Address::where('id', $usrAddressId)->first();
                if(isset($usrAddress)){
                    $uaddress1 = $usrAddress['address1'].',<br/>';
                    $uaddress2 = ($usrAddress['address2']) ? $usrAddress['address2'].',<br/>' : '';
                    $ucity = $usrAddress['city'].',<br/>';
                    $postcode = 'postcode:'.$usrAddress['postcode'];
                    $urAddress = $uaddress1 . $uaddress2 . $ucity . $postcode ;
                }else{
                    $urAddress = '';
                }
                $paymentDate= '';
                $payment_row = Order::where('module_id', $companyId)
                                    ->where('module', $this->settings('MODULE_INCORPORATION','key')->id)
                                    ->first();
                $paymentDate = isset($payment_row->updated_at) ? strtotime($payment_row->updated_at) : '';
                $data = [
                    'public_path' => public_path(),
                    'eroc_logo' => url('/').'/images/forms/eroc.png',
                    'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                    'css_file' => url('/').'/images/forms/form1/form1.css',
                    'comname' => $companyName,
                    'postfix' => $companyDetails->postfix,
                    'name' => $fullName,
                    'fname' =>  $fname,
                    'lname' =>  $lname,
                    'raddress' =>  $rAddress,
                    'occupation' => $occupation,
                    'nic' =>  $nic,
                    'passport' =>  $passport,
                    'passport_issued_country' => $passport_issued_country,
                    'doa' => $doa,
                    'paymentdate' => $paymentDate,
                    'ufullname' =>  $ufullName,
                    'uemail' => $uemail,
                    'umobile' =>  $umobile,
                    'utelephone' =>  $utelephone,
                    'uraddress' => $urAddress, 
                    'certificate_no' => $certificate_no ,
                    'directorDetails' => $directorDetails                 
                ];            
                $pdf = PDF::loadView('diretor-secretary-change/form18', $data);
                return $pdf->stream('form18.pdf'); 
            }else{            
                return response()->json([
                    'message' => 'We can \'t find a director.',
                    'status' =>false,
                ], 200);
            }
        }else if($request->type ==='secretary'){
            if(isset($request->memberId)){
                $secretaryDetails = CompanyMember::where('id',$request->memberId)->first();
                $fname = $secretaryDetails['first_name'];
                $lname = $secretaryDetails['last_name'];
                $fullName =  $fname .' '. $lname  ;
                $addressId = $secretaryDetails['address_id'];
                $address = Address::where('id',$addressId)->first();
                if(isset($address)){
                    //$address1 = $address['address1'];
                   // $address2 = $address['address2'];
                  //  $city = $address['city'];
                  //  $rAddress = $address1 .' '. $address2 .' '. $city ;
                    $address1 = $address['address1'].',<br/>';
                    $address2 = ($address['address2']) ? $address['address2'].',<br/>' : '';
                    $city = $address['city'].',<br/>';
                    $postcode = 'postcode:'.$address['postcode'];
                    $rAddress = $address1 . $address2 . $city . $postcode ;
                    
                }else{
                    $rAddress = '';
                }
                $companyId = $secretaryDetails['company_id'];
                $companyCertificate = CompanyCertificate::where('company_id', $companyId)
                ->where('is_sealed', 'yes')
                ->first();
                $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';
                $companyDetails = Company::where('id',$companyId)->first();
                $companyName = $companyDetails ->name;
                $doa = $secretaryDetails['date_of_appointment'];
                $regNum = $secretaryDetails['secretary_registration_no'];
                $createdUserId = Company::where('id', $companyId)->value('created_by');
                $createdUserDetails = User::where('id', $createdUserId)->first();
                $loginPeopleId = $createdUserDetails->people_id;
                $usrDetails = People::where('id',$loginPeopleId)->first();
                $ufname = $usrDetails['first_name'];
                $ulname = $usrDetails['last_name'];
                $ufullName =  $ufname .' '. $ulname  ;
                $uemail = $usrDetails['email'];
                $umobile = $usrDetails['mobile'];
                $utelephone = $usrDetails['telephone'];
                $usrAddressId = $usrDetails->address_id;  
                $usrAddress = Address::where('id', $usrAddressId)->first();
                if(isset($usrAddress)){
                  //  $uaddress1 = $usrAddress['address1'];
                  //  $uaddress2 = $usrAddress['address2'];
                 //   $ucity = $usrAddress['city'];
                 //   $urAddress = $uaddress1 .' '. $uaddress2 .' '. $ucity ;
                    $uaddress1 = $usrAddress['address1'].',<br/>';
                    $uaddress2 = ($usrAddress['address2']) ? $usrAddress['address2'].',<br/>' : '';
                    $ucity = $usrAddress['city'].',<br/>';
                    $postcode = 'postcode:'.$usrAddress['postcode'];
                    $urAddress = $uaddress1 . $uaddress2 . $ucity . $postcode ;
                }else{
                    $urAddress = '';
                }
                $paymentDate= '';
                $payment_row = Order::where('module_id', $companyId)
                                    ->where('module', $this->settings('MODULE_INCORPORATION','key')->id)
                                    ->first();
                $paymentDate = isset($payment_row->updated_at) ? strtotime($payment_row->updated_at) : '';
                $data = [
                    'comname' => $companyName,
                    'regnum' => $regNum,
                    'postfix' => $companyDetails->postfix,
                    'name' => $fullName,
                    'fname' =>  $fname,
                    'lname' =>  $lname,
                    'raddress' =>  $rAddress,
                    'doa' => $doa,
                    'paymentdate' => $paymentDate,
                    'ufullname' =>  $ufullName,
                    'uemail' => $uemail,
                    'umobile' =>  $umobile,
                    'utelephone' =>  $utelephone,
                    'uraddress' => $urAddress,    
                    'certificate_no' => $certificate_no               
                ];            
                $pdf = PDF::loadView('diretor-secretary-change/form19', $data);
                return $pdf->stream('form19.pdf');     
            }else{            
                return response()->json([
                    'message' => 'We can \'t find a secretary.',
                    'status' =>false,
                ], 200);
            }
        }else if($request->type ==='secretaryFirm'){
            if(isset($request->memberId)){
                $secretaryFirmDetails = CompanyFirms::where('id',$request->memberId)->first();
                $fname = $secretaryFirmDetails['name'];
                $lname = '';
                $fullName =  $fname;
                $addressId = $secretaryFirmDetails['address_id'];
                $address = Address::where('id',$addressId)->first();
                if(isset($address)){
                 //   $address1 = $address['address1'];
                //    $address2 = $address['address2'];
                 //   $city = $address['city'];
                 //   $rAddress = $address1 .' '. $address2 .' '. $city ;

                    $address1 = $address['address1'].',<br/>';
                    $address2 = ($address['address2']) ? $address['address2'].',<br/>' : '';
                    $city = $address['city'].',<br/>';
                    $postcode = 'postcode:'.$address['postcode'];
                    $rAddress = $address1 . $address2 . $city . $postcode ;
                }else{
                    $rAddress = '';
                }
                $companyId = $secretaryFirmDetails['company_id'];
                $companyCertificate = CompanyCertificate::where('company_id', $companyId)
                ->where('is_sealed', 'yes')
                ->first();
                $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';
                $companyDetails = Company::where('id',$companyId)->first();
                $companyName = $companyDetails->name;
                $doa = $secretaryFirmDetails['date_of_appointment'];
                $regNum = $secretaryFirmDetails['registration_no'];
                $createdUserId = Company::where('id', $companyId)->value('created_by');
                $createdUserDetails = User::where('id', $createdUserId)->first();
                $loginPeopleId = $createdUserDetails->people_id;
                $usrDetails = People::where('id',$loginPeopleId)->first();
                $ufname = $usrDetails['first_name'];
                $ulname = $usrDetails['last_name'];
                $ufullName =  $ufname .' '. $ulname;
                $uemail = $usrDetails['email'];
                $umobile = $usrDetails['mobile'];
                $utelephone = $usrDetails['telephone'];
                $usrAddressId = $usrDetails->address_id;  
                $usrAddress = Address::where('id', $usrAddressId)->first();
                if(isset($usrAddress)){
                  //  $uaddress1 = $usrAddress['address1'];
                  //  $uaddress2 = $usrAddress['address2'];
                   // $ucity = $usrAddress['city'];
                   // $urAddress = $uaddress1 .' '. $uaddress2 .' '. $ucity ;
                    $uaddress1 = $usrAddress['address1'].',<br/>';
                    $uaddress2 = ($usrAddress['address2']) ? $usrAddress['address2'].',<br/>' : '';
                    $ucity = $usrAddress['city'].',<br/>';
                    $postcode = 'postcode:'.$usrAddress['postcode'];
                    $urAddress = $uaddress1 . $uaddress2 . $ucity . $postcode ;
                }else{
                    $urAddress = '';
                }
                $paymentDate= '';
                $payment_row = Order::where('module_id', $companyId)
                                    ->where('module', $this->settings('MODULE_INCORPORATION','key')->id)
                                    ->first();
                $paymentDate = isset($payment_row->updated_at) ? strtotime($payment_row->updated_at) : '';
                $data = [
                    'comname' => $companyName,
                    'regnum' => $regNum,
                    'postfix' => $companyDetails->postfix,
                    'name' => $fullName,
                    'fname' =>  $fname,
                    'lname' =>  $lname,
                    'raddress' =>  $rAddress,
                    'doa' => $doa,
                    'paymentdate' => $paymentDate,
                    'ufullname' =>  $ufullName,
                    'uemail' => $uemail,
                    'umobile' =>  $umobile,
                    'utelephone' =>  $utelephone,
                    'uraddress' => $urAddress,   
                    'certificate_no' => $certificate_no                
                ];            
                $pdf = PDF::loadView('diretor-secretary-change/form19', $data);
                return $pdf->stream('form19.pdf'); 
            }else{            
                return response()->json([
                    'message' => 'We can \'t find a secretary firm.',
                    'status' =>false,
                ], 200);
            }
        }else if($request->type ==='form20'){
            if(isset($request->memberId) && isset($request->requestId)){




                $companyID = $request->memberId;
                $requestID = $request->requestId;
                $companyCertificate = CompanyCertificate::where('company_id', $companyID)
                ->where('is_sealed', 'yes')
                ->first();
                $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

                // newly added
                $directorListCount = CompanyMember::where('company_id',$companyID)
                ->where('designation_type',$this->settings('DERECTOR','key')->id)
                ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                  ->count();

                $secretaryListCount = CompanyMember::where('company_id',$companyID)
                                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                ->count();
                $secretaryFirmListCount = CompanyFirms::where('company_id',$companyID)
                                ->where('type_id',$this->settings('SECRETARY','key')->id)
                                ->where('status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                  ->count();

                // changed                            
                $directorChangeListCount = CompanyMember::where('company_id',$companyID)
                ->where('designation_type',$this->settings('DERECTOR','key')->id)
                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                  ->count();

                $secretaryChangeListCount = CompanyMember::where('company_id',$companyID)
                                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                ->count();
                $secretaryChangeFirmListCount = CompanyFirms::where('company_id',$companyID)
                                ->where('type_id',$this->settings('SECRETARY','key')->id)
                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                  ->count();
                                  
                // changed detail array bulding
                
                $editedCMitems = CompanyItemChange::where('request_id',$requestID)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->get();
                 $editedCFitems = CompanyItemChange::where('request_id',$requestID)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->get();                                                       
                $editedDirectors = array();
                $editedSecs = array();
                $editedSecfirms = array();
                foreach ($editedCMitems as $key => $value) {
                    $old = CompanyMember::where('id',$value->old_record_id)
                                ->first();
                    $oldaddress = Address::where('id',$old->address_id)
                                ->first();            
                    $new = CompanyMember::where('id',$value->item_id)
                                ->first();
                    $newaddress = Address::where('id',$new->address_id)
                                ->first();            

                    if($old->designation_type == $this->settings('DERECTOR','key')->id){
                        $editedDirectors[] = [
                            "oldid" => $old->id,
                            "newid" => $new->id,
                            'type' => 0,
                            "old_title" => $old->title,
                            "new_title" => $new->title,
                            "old_firstname" => $old->first_name,
                            "old_lastname" => $old->last_name,
                            "new_firstname" => $new->first_name,
                            "new_lastname" => $new->last_name,
                            "old_address" => $oldaddress->address1 . ',' . $oldaddress->address2 . ',' . $oldaddress->city,
                            "new_address" => $newaddress->address1 . ',' . $newaddress->address2 . ',' . $newaddress->city,
                            "date" => $new->date_of_change,
                      ];

                    }
                    elseif($old->designation_type == $this->settings('SECRETARY','key')->id){
                        $editedSecs[] = [
                            "oldid" => $old->id,
                            "newid" => $new->id,
                            'type' => 0,
                            "old_title" => $old->title,
                            "new_title" => $new->title,
                            "old_firstname" => $old->first_name,
                            "old_lastname" => $old->last_name,
                            "new_firstname" => $new->first_name,
                            "new_lastname" => $new->last_name,
                            "old_address" => $oldaddress->address1 . ',' . $oldaddress->address2 . ',' . $oldaddress->city,
                            "new_address" => $newaddress->address1 . ',' . $newaddress->address2 . ',' . $newaddress->city,
                            "date" => $new->date_of_change,
                      ];
                    }

                }

                foreach ($editedCFitems as $key => $value) {
                    $old = CompanyFirms::where('id',$value->old_record_id)
                                ->first();
                    $oldaddress = Address::where('id',$old->address_id)
                                ->first();            
                    $new = CompanyFirms::where('id',$value->item_id)
                                ->first();
                    $newaddress = Address::where('id',$new->address_id)
                                ->first();            

                    if($old->type_id == $this->settings('SECRETARY','key')->id){
                        $editedSecfirms[] = [
                            "oldid" => $old->id,
                            "newid" => $new->id,
                            'type' => 0,
                            "title" => $value->title,
                            "old_name" => $old->name,
                            "new_name" => $new->name,
                            "old_address" => $oldaddress->address1 . ',' . $oldaddress->address2 . ',' . $oldaddress->city,
                            "new_address" => $newaddress->address1 . ',' . $newaddress->address2 . ',' . $newaddress->city,
                            "date" => $new->date_of_change,
                      ];

                    }

                }

                /// all directors and secretaries array building

                $oldMemberList = CompanyMember::where('company_id',$companyID)
                                                ->where('designation_type','!=',$this->settings('SHAREHOLDER','key')->id)
                                                // ->where(function ($query) {
                                                //     $query->where('ceased_reason', '=', '')
                                                //         ->orWhere('ceased_reason', '=', NULL);
                                                //         })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                ->get();
                $oldSecretaryFirmList = CompanyFirms::where('company_id',$companyID)
                                                    ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                    // ->where(function ($query) {
                                                    //     $query->where('ceased_reason', '=', '')
                                                    //         ->orWhere('ceased_reason', '=', NULL);
                                                    //         })
                                                    ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                    ->get();                                
                $activemembers = array();                                     
                $activesecs_firms = array();                                     
                foreach($oldMemberList as $member){

                    $isMemberEdited = CompanyItemChange::where('request_id',$requestID)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('old_record_id',$member['id'])
                                                      ->first();

                    $isMemberDeleted = CompanyItemChange::where('request_id',$requestID)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBERS','key')->id)
                                                      ->where('item_id',$member['id'])
                                                      ->first();                                  
                    // $directorID =   $director['id'];
                    // $newdirectorID =   null;
                    // $isdeleted =   null;

                    if($isMemberEdited){

                        $newEditedMember = CompanyMember::where('id',$isMemberEdited->item_id)
                                                ->where('company_id',$companyID)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        $member =   $newEditedMember;

                        // $directorID =   $isDirectorEdited->old_record_id;
                        // $newdirectorID =   $newEditedDirector->id;                    

                    }
                    if($isMemberDeleted){

                        continue;                    

                    }
                    
                    
                    $address ='';
                    $forAddress = '';
                    if( $member->address_id) {
                       $address = Address::where('id',$member->address_id)->first();
                    }
                    if( $member->foreign_address_id) {
                       $forAddress = Address::where('id', $member->foreign_address_id)->first();
                    }

                    $rec = array(
                    //    'id' => $directorID,
                    //    'newid' => $newdirectorID,
                    //    'isdeleted' => $isdeleted,
                       'type' => ($member->is_srilankan  =='yes' ) ? 'local' : 'foreign',               
                       'firstname' => $member->first_name,
                       'lastname' => $member->last_name,
                       'fullname' => $member->first_name . ' ' . $member->last_name,
                       'title' => $member->title,               
                       'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                       'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                       'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                       'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                       'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                       'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',               
                       'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                       'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                       'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                       'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                       'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',               
                       'resaddress' => $address->address1 . ',' . $address->address2 . ',' . $address->city,               
                       'nic'       => $member->nic,
                       'passport'  => $member->passport_no,
                        // 'country'   =>($address->country) ? $address->country : '',
                       'country'  => ( $member->foreign_address_id)  ? $forAddress->country : $address->country,
                       'passport_issued_country'   => $member->passport_issued_country,
                       // 'share'     => $director->no_of_shares, 
                       'date'      => '1970-01-01' == $member->date_of_appointment ? null : $member->date_of_appointment,
                       'changedate'      => '1970-01-01' == $member->date_of_change ? null : $member->date_of_change,
                       'phone' => $member->telephone,
                       'mobile' => $member->mobile,
                       'email' => $member->email,
                       'occupation' => $member->occupation,
                       'designation_type' => ($member->designation_type == $this->settings('SECRETARY','key')->id) ? 'Secretary' : 'Director',
                    //    'directors_as_sh' => $directors_as_sh,
                    //    'can_director_as_sec' => $can_director_as_sec,
                    //    'secRegDate' => $sec_reg_no                              
                    );
                    $activemembers[] = $rec;
        }
        foreach($oldSecretaryFirmList as $sec){
                    $isSecEdited = CompanyItemChange::where('request_id',$requestID)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('old_record_id',$sec['id'])
                                                      ->first();
                    $isSecDeleted = CompanyItemChange::where('request_id',$requestID)
                                                      ->where('changes_type',$this->settings('DELETE','key')->id)
                                                      ->where('item_table_type',$this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                                                      ->where('item_id',$sec['id'])
                                                      ->first();  

                    // $secID =   $sec['id'];
                    // $newsecID =   null;
                    // $isdeleted =   null;

                    if($isSecEdited){

                        $newEditedSec = CompanyFirms::where('id',$isSecEdited->item_id)
                                                ->where('company_id',$companyID)
                                                ->where('type_id',$this->settings('SECRETARY','key')->id)
                                                ->where(function ($query) {
                                                    $query->where('ceased_reason', '=', '')
                                                        ->orWhere('ceased_reason', '=', NULL);
                                                        })                                                
                                                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                ->first();

                        $sec =   $newEditedSec;

                        // $secID =   $isSecEdited->old_record_id;
                        // $newsecID =   $newEditedSec->id;                    

                    }
                    if($isSecDeleted){

                        continue;                    

                    }

                    

                    $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;
                    if(!$sec->foreign_address_id){
                        $address = Address::where('id',$address_id)->first();
                    }else{
                    $address = Address::where('id',$address_id)->first();
                    }
                    $rec = array(
                    // 'id' => $secID,
                    // 'newid' => $newsecID,
                    // 'isdeleted' => $isdeleted,
                    'type' => ($address->country != 'Sri Lanka') ? 'foreign' : 'local',
                    'pvNumber' => $sec->registration_no,
                    'firm_name' => $sec->name,
                    'firm_address' => $address->address1 . ',' . $address->address2 . ',' . $address->city,
                    'firm_province' =>  ( $address->province) ? $address->province : '',
                    'firm_district' =>  ($address->district) ? $address->district : '',
                    'firm_city' =>  ( $address->city) ? $address->city : '',
                    'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
                    'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
                    'firm_country'      => ($address->country) ? $address->country : '',
                    'firm_postcode' => ($address->postcode) ? $address->postcode : '',
                    'firm_email' => $sec->email,
                    'firm_phone' => $sec->phone,
                    'firm_mobile' => $sec->mobile,
                    'firm_date'  => $sec->date_of_appointment,
                    'firm_date_change'  => $sec->date_of_change,
                    'secType' => 'Secretary Firm'
                    );
                    $activesecs_firms[] = $rec;
                }                                
                /// all directors and secs array end
                
                
                ///signed by member
                $changeRequest = CompanyChangeRequestItem::where('id',$requestID)->first();

        if($changeRequest->signed_by_table_type == $this->settings('COMPANY_MEMBERS','key')->id){

            $member = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.id',$changeRequest->signed_by)
       ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

       $date = array();
                      
        $date[] = [
                    "id" => $member[0]['id'],
                    "type" => 0,
                    "title" => '',
                    "first_name" => $member[0]['first_name'],
                    "last_name" => $member[0]['last_name'],
                    "designation" => $member[0]['designation'],
                    ];
                        

        }
        else{
            $member = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.id',$changeRequest->signed_by)
       ->get(['company_member_firms.id','company_member_firms.name','settings.value as designation']);

       $date = array();
                      
        $date[] = [
                    "id" => $member[0]['id'],
                    "type" => 1,
                    "title" => '',
                    "first_name" => $member[0]['name'],
                    "last_name" => '',
                    "designation" => $member[0]['designation'],
                    ];

        }
                ///signed by member

                $companyDetails = Company::where('id',$companyID)->first();
                $companyName = $companyDetails->name;
                $postfix =  $companyDetails->postfix;
                $createdUserId = Company::where('id', $companyID)->value('created_by');
                $createdUserDetails = User::where('id', $createdUserId)->first();
                $loginPeopleId = $createdUserDetails->people_id;
                $usrDetails = People::where('id',$loginPeopleId)->first();
                $ufname = $usrDetails['first_name'];
                $ulname = $usrDetails['last_name'];
                $ufullName =  $ufname .' '. $ulname;
                $uemail = $usrDetails['email'];
                $umobile = $usrDetails['mobile'];
                $utelephone = $usrDetails['telephone'];
                $todayDate = date("Y-m-d");

                $newMembers = CompanyMember::leftJoin('addresses','company_members.address_id','=','addresses.id')
                                           ->leftJoin('settings','company_members.designation_type','=','settings.id')
                                              ->where('company_members.company_id',$companyID)
                                              ->where('company_members.designation_type','!=',$this->settings('SHAREHOLDER','key')->id)
                                              ->where('company_members.status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                ->get(['company_members.first_name','company_members.last_name','company_members.email','company_members.date_of_appointment','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city','settings.value as value']);

                $newMemberFirms = CompanyFirms::leftJoin('addresses','company_member_firms.address_id','=','addresses.id')
                                              ->leftJoin('settings','company_member_firms.type_id','=','settings.id')
                                                 ->where('company_member_firms.company_id',$companyID)
                                                 ->where('company_member_firms.type_id','!=',$this->settings('SHAREHOLDER','key')->id)
                                                 ->where('company_member_firms.status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                                   ->get(['company_member_firms.name','company_member_firms.email','company_member_firms.date_of_appointment','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city','settings.value as value']);

                $removedMembers = CompanyMember::leftJoin('company_change_requests','company_members.company_id','=','company_change_requests.company_id')
                                               ->leftJoin('company_item_changes','company_members.id','=','company_item_changes.item_id')
                                               ->leftJoin('addresses','company_members.address_id','=','addresses.id')
                                               ->leftJoin('settings','company_members.designation_type','=','settings.id')
                                                  ->where('company_members.company_id',$companyID)
                                                  ->where('company_item_changes.request_id',$requestID)
                                                  ->where('company_members.ceased_reason','!=','')
                                                  ->where('company_members.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                  ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)                                                
                                                  ->get(['company_members.id','company_members.first_name','company_members.last_name','company_members.ceased_reason','company_members.ceased_date','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city','settings.value as value']);
                                                  

                $removedFirms = CompanyFirms::leftJoin('company_change_requests','company_member_firms.company_id','=','company_change_requests.company_id')
                                            ->leftJoin('company_item_changes','company_member_firms.id','=','company_item_changes.item_id')
                                            ->leftJoin('addresses','company_member_firms.address_id','=','addresses.id')
                                            ->leftJoin('settings','company_member_firms.type_id','=','settings.id')
                                               ->where('company_member_firms.company_id',$companyID)
                                               ->where('company_item_changes.request_id',$requestID)
                                               ->where('company_member_firms.ceased_reason','!=','')
                                               ->where('company_member_firms.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                               ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)                                                    
                                                 ->get(['company_member_firms.id','company_member_firms.name','company_member_firms.ceased_reason','company_member_firms.ceased_date','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city','settings.value as value']);
                

                $removedDirCount = CompanyMember::leftJoin('company_change_requests','company_members.company_id','=','company_change_requests.company_id')
                                               ->leftJoin('company_item_changes','company_members.id','=','company_item_changes.item_id')
                                               ->leftJoin('addresses','company_members.address_id','=','addresses.id')
                                               ->leftJoin('settings','company_members.designation_type','=','settings.id')
                                               ->where('company_members.designation_type',$this->settings('DERECTOR','key')->id)
                                                  ->where('company_members.company_id',$companyID)
                                                  ->where('company_item_changes.request_id',$requestID)
                                                  ->where('company_members.ceased_reason','!=','')
                                                  ->where('company_members.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                  ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)                                                
                                                  ->count();
                $removedSecCount = CompanyMember::leftJoin('company_change_requests','company_members.company_id','=','company_change_requests.company_id')
                                               ->leftJoin('company_item_changes','company_members.id','=','company_item_changes.item_id')
                                               ->leftJoin('addresses','company_members.address_id','=','addresses.id')
                                               ->leftJoin('settings','company_members.designation_type','=','settings.id')
                                               ->where('company_members.designation_type',$this->settings('SECRETARY','key')->id)
                                                  ->where('company_members.company_id',$companyID)
                                                  ->where('company_item_changes.request_id',$requestID)
                                                  ->where('company_members.ceased_reason','!=','')
                                                  ->where('company_members.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                  ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)                                                
                                                  ->count();
                $removedFirmCount = CompanyFirms::leftJoin('company_change_requests','company_member_firms.company_id','=','company_change_requests.company_id')
                                                  ->leftJoin('company_item_changes','company_member_firms.id','=','company_item_changes.item_id')
                                                  ->leftJoin('addresses','company_member_firms.address_id','=','addresses.id')
                                                  ->leftJoin('settings','company_member_firms.type_id','=','settings.id')
                                                  ->where('company_member_firms.type_id',$this->settings('SECRETARY','key')->id)
                                                     ->where('company_member_firms.company_id',$companyID)
                                                     ->where('company_item_changes.request_id',$requestID)
                                                     ->where('company_member_firms.ceased_reason','!=','')
                                                     ->where('company_member_firms.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                                     ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)                                                    
                                                       ->count();                                  
                                                  
                
                
                

                $data = [
                    'comname' => $companyName,
                    'postfix' => $postfix,
                    'newMembers' => $newMembers,
                    'member' => $date,
                    'activemembers' => $activemembers,
                    'activesecs_firms' => $activesecs_firms,
                    'todayDate' => $todayDate,
                    'editedDirectors' => $editedDirectors,
                    'editedSecs' => $editedSecs,
                    'editedSecfirms' => $editedSecfirms,
                    'newMemberFirms' => $newMemberFirms,
                    'removedMembers' => $removedMembers,
                    'removedFirms' => $removedFirms,
                    'ufullname' =>  $ufullName,
                    'uemail' => $uemail,
                    'umobile' =>  $umobile,
                    'utelephone' =>  $utelephone,
                    'certificate_no' => $certificate_no,
                    'directorChanged' => ( ($directorListCount + $directorChangeListCount + $removedDirCount) >0 ),
                    'secChagned' => ( ($secretaryListCount + $secretaryChangeListCount + $removedSecCount) > 0 || ($secretaryFirmListCount + $secretaryChangeFirmListCount + $removedFirmCount) > 0  )
                ];            
                $pdf = PDF::loadView('diretor-secretary-change/form20', $data);
                return $pdf->stream('form20.pdf');
            }else{            
                return response()->json([
                    'message' => 'We can \'t find a secretary firm.',
                    'status' =>false,
                ], 200);
            }
        }
    }

    private function getPostfixValues( $postfix_en_value ){
        $postix_values = CompanyPostfix::where('postfix', $postfix_en_value)->first();   
        return array(
            'postfix_si' => ( isset($postix_values->postfix_si) && $postix_values->postfix_si) ? $postix_values->postfix_si : '',
            'postfix_ta' => ( isset($postix_values->postfix_ta) && $postix_values->postfix_ta ) ? $postix_values->postfix_ta : '',
        );   
    }

    function removeJustAddedMember(Request  $request ){

        $new_director_doc = Documents::where('key', 'DIRSEC_CHANGE_FORM18')->first();
        $new_sec_doc = Documents::where('key', 'DIRSEC_CHANGE_FORM19')->first();

        if($request->type ==='individual'){
            $memberId = $request->userId;
            $company_id = $request->companyId;
            $requestId = $request->requestId;
            $memberInfo = CompanyMember::where('id', $memberId)->first();
            $memberType = $this->settings($memberInfo->designation_type,'id')->key;

            //delete member document
            $query = CompanyDocuments::query();
            $query->where('company_id', $company_id );

            if($memberType === 'DIRECTOR') {
                $query->where('document_id',$new_director_doc->id);
            }
            if($memberType === 'SECRETARY') {
                $query->where('document_id',$new_sec_doc->id);
            }
            $query->where('company_member_id', $memberId );
            $query->delete();



            $remove = CompanyMember::where('id', $memberId)->delete();
            $removeItem = CompanyItemChange::where('item_id', $memberId)
            ->where('request_id', $requestId)
            ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
            ->delete();        
            if($remove && $removeItem){
            return response()->json([
                'message' => 'Successfully deleted',
                'status' =>true             
            ], 200);
            }else{
            return response()->json([
                'message' => 'Please try again',
                'status' =>false            
            ], 200);
            }
        }else if($request->type ==='firm'){
            $firmId = $request->userId;
            $company_id = $request->companyId;
            $requestId = $request->requestId;

            $memberInfo = CompanyFirms::where('id', $memberId)->first();
            $memberType = $this->settings($memberInfo->type_id,'id')->key;

            //delete member document
            $query = CompanyDocuments::query();
            $query->where('company_id', $company_id );

            if($memberType === 'DIRECTOR') {
                $query->where('document_id',$new_director_doc->id);
            }
            if($memberType === 'SECRETARY') {
                $query->where('document_id',$new_sec_doc->id);
            }
            $query->where('company_firm_id', $memberId );
            $query->delete();

            $remove = CompanyFirms::where('id', $firmId)->delete();
            $removeItem = CompanyItemChange::where('item_id', $firmId)
            ->where('request_id', $requestId)
            ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
            ->delete();        
            if($remove && $removeItem){
            return response()->json([
                'message' => 'Successfully deleted',
                'status' =>true             
            ], 200);
            }else{
            return response()->json([
                'message' => 'Please try again',
                'status' =>false            
            ], 200);
            }
        }else if($request->type ==='oldIndividual'){
            $memberId = $request->userId;
            $reqId = $request->companyId;
            $removeRequest = CompanyItemChange::updateOrCreate(
                            [
                                'item_id' =>  $memberId
                            ],
                            [
                                'request_id' => $reqId,
                                'changes_type' => $this->settings('DELETE','key')->id,
                                'item_table_type' => $this->settings('COMPANY_MEMBERS','key')->id,                                
                            ]);
            CompanyMember::where('id',$memberId)
                        ->update(['ceased_reason' => $request->reason,
                                  'ceased_reason_other' => $request->reason == 'Other' ? $request->reasonOther : null,  
                                  'ceased_date' => $request->date,  
                                 ]);                            
            if($removeRequest){
            return response()->json([
                'message' => 'Successfully requested',
                'status' =>true             
            ], 200);
            }else{
            return response()->json([
                'message' => 'Please try again',
                'status' =>false            
            ], 404);
            }
        }else if($request->type ==='oldFirm'){
            $firmId = $request->userId;
            $reqId = $request->companyId;
            $removeRequest = CompanyItemChange::updateOrCreate(
                            [
                                'item_id' =>  $firmId
                            ],
                            [
                                'request_id' => $reqId,
                                'changes_type' => $this->settings('DELETE','key')->id,
                                'item_table_type' => $this->settings('COMPANY_MEMBER_FIRMS','key')->id,                                
                            ]);
            CompanyFirms::where('id',$firmId)
                       ->update(['ceased_reason' => $request->reason,
                                'ceased_reason_other' => $request->reason == 'Other' ? $request->reasonOther : null,
                                 'ceased_date' => $request->date,  
                                ]);                               
            if($removeRequest){
            return response()->json([
                'message' => 'Successfully requested',
                'status' =>true             
            ], 200);
            }else{
            return response()->json([
                'message' => 'Please try again',
                'status' =>false            
            ], 200);
            }
        }             
    }

    public function memberUploadUpdatePdf(Request $request){

        if(isset($request)){
    
        $fileName =  uniqid().'.pdf';
        $token = md5(uniqid());
    
        $comId = $request->comId;
        $socDocId = $request->docId;
        $docType = $request->docType;
        $pdfName = $request->filename;
    
        $path = 'company/'.$comId;
        $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');
    
    
        CompanyDocuments::where('id', $request->docId)
            ->update(['status' => $this->settings('DOCUMENT_PENDING','key')->id,
            'name' => $pdfName,
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

    // to uplaod pdf files...
    public function memberUploadPdf (Request $request){
        if(isset($request)){
            $fileName =  uniqid().'.pdf';
            $token = md5(uniqid());    
            $companyId = $request->companyId;
            $requestId = $request->requestId;
            $memberID = $request->memberID;
            $firmID = $request->firmID;
            $docType = $request->docType;
            $pdfName = $request->filename; 
            $path = 'memberChange/'.$companyId;
            $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');
            $docId;
            if($docType=='form18Upload'){
                $docId = Documents::where('key','DIRSEC_CHANGE_FORM18')->value('id');
                $description=NULL;
            }elseif($docType=='form19Upload' || $docType=='form19UploadFirm'){
                $docId = Documents::where('key','DIRSEC_CHANGE_FORM19')->value('id');
                $description=NULL;              
            }elseif($docType=='form20Upload'){
                $docId = Documents::where('key','DIRSEC_CHANGE_FORM20')->value('id');
                $description=NULL;             
            }elseif($docType=='resignationUpload'){
                $docId = Documents::where('key','DIRSEC_CHANGE_RESIGNATION_LETTER')->value('id');
                $description = $request->description;
                if($description=='undefined'){
                    $description=NULL;
                }             
            }elseif($docType=='extraUpload'){
                $docId = Documents::where('key','EXTRA_DOCUMENT')->value('id');
                $description = $request->description;
                if($description=='undefined'){
                    $description=NULL;
                }             
            }


            $query = CompanyDocuments::query();
            $query->where('company_id', $companyId );
            $query->where('document_id',$docId);
            $query->where('request_id', $requestId);

            if ($memberID) {
            $query->where('company_member_id', $memberID );
            $query->delete();
            }
            if ($firmID) {
            $query->where('company_firm_id', $firmID );
            $query->delete();
            }
            if ($docType=='form20Upload') {
            $docId = Documents::where('key','DIRSEC_CHANGE_FORM20')->value('id');    
            $query->where('document_id', $docId );
            $query->delete();
            }


            $memDoc = new CompanyDocuments;
            $memDoc->company_id = $companyId;
            $memDoc->document_id = $docId;
            $memDoc->no_of_pages = 1;
            $memDoc->name = $pdfName;
            $memDoc->file_description = $description;
            $memDoc->file_token = $token;
            $memDoc->path = $path;
            if($memberID){
                $memDoc->company_member_id = $memberID;
            }else if($firmID){
                $memDoc->company_firm_id = $firmID;
            }
            $memDoc->request_id = $requestId;            
            $memDoc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
            $memDoc->save();            
            $memdocId = $memDoc->id;    
              return response()->json([
                'message' => 'File uploaded successfully.',
                'status' =>true,
                'name' =>basename($path),
                'doctype' =>$docType,
                'docid' =>$memdocId, // for delete pdf...
                'token' =>$token,
                'memberID' =>$memberID,
                'firmID' =>$firmID,
                'pdfname' =>$pdfName,
            ], 200);    
        }
    }

    // to delete and delete update pdf files...
    function deleteMemberPdf(Request $request){
        if(isset($request)){
        $docId = $request->documentId;
        $type = $request->type;
        $docstatusid = CompanyDocumentStatus::where('company_document_id', $docId)->first();
        if($docstatusid){
            if($type =='form18Upload'){
    
                $document = CompanyDocuments::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);          
            }
            elseif($type =='form19Upload' || $type =='form19UploadFirm'){
                $document = CompanyDocuments::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);
            }
            elseif($type =='form20Upload'){
                $document = CompanyDocuments::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);
            }
            elseif($type =='resignationUpload'){
                $document = CompanyDocuments::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);
            }
            elseif($type =='extraUpload'){
                $document = CompanyDocuments::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);
            }
            else{
                $docstatusid = CompanyDocumentStatus::where('company_document_id', $docId)->first();
    
                $document = CompanyDocuments::where('id', $docId)->first();
                    if($docstatusid->status == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id){
    
                        $delete = Storage::disk('sftp')->delete($document->path);
                    CompanyDocuments::where('id', $docId)
                    ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                    'name' => NULL,
                    'file_token' => NULL,
                    'path' => NULL]);
    
                    }
                    else{
    
                        $delete = Storage::disk('sftp')->delete($document->path);
                    CompanyDocuments::where('id', $docId)
                    ->update(['status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
                    'name' => NULL,
                    'file_token' => NULL,
                    'path' => NULL]);
    
                    }
    
                // $document = CompanyDocuments::where('id', $docId)->first();
                // $delete = Storage::disk('sftp')->delete($document->path);
                // CompanyDocuments::where('id', $docId)
                // ->update(['status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
                // 'name' => NULL,
                // 'file_token' => NULL,
                // 'path' => NULL]);
            }
    
            
    
        }
        else{
            $document = CompanyDocuments::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);
            $remove = CompanyDocuments::where('id', $docId)->delete();
        }
        return response()->json([
            'message' => 'File emptied successfully.',
            'status' =>true,
        ], 200);
        }
    }

    // to delete pdf files...
    // public function deleteMemberPdf (Request $request){
    //     if(isset($request)){
    //         $docId = $request->documentId;
    //         if($docId){
    //             $document = CompanyDocuments::where('id', $docId)->first();
    //             $delete = Storage::disk('sftp')->delete($document->path);
    //             $remove = CompanyDocuments::where('id', $docId)->delete();
    //         }
    //         return response()->json([
    //             'message' => 'File removed successfully.',
    //             'status' =>true,
    //         ], 200);
    //     }
    // }

    // for load member uploaded files...
    public function memberFileLoad(Request $request){
        if(isset($request)){
            if($request->type=='memberChange'){
            $companyId = $request->companyId;
            $requestId = $request->requestId;
            $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                            ->leftJoin('company_document_status', function ($join) {
                                                $join->on('company_documents.id', '=', 'company_document_status.company_document_id')
                                                ->where(function ($query) {
                                                    $query->where('company_document_status.status', '=', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id)
                                                      ->orWhere('company_document_status.status', '=', $this->settings('DOCUMENT_REQUESTED', 'key')->id);
                                                })
                                                ->where('company_document_status.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);})
                                              ->leftJoin('settings','company_documents.status','=','settings.id')
                                              ->where('company_documents.company_id',$companyId)
                                              ->where('company_documents.request_id',$requestId)
                                              ->where('company_documents.status', '!=' , $this->settings('DOCUMENT_DELETED','key')->id)
                                              ->get(['company_documents.id','company_documents.name','company_documents.file_token','company_documents.file_description','settings.value as value','settings.key as setkey','company_documents.company_member_id','company_documents.company_firm_id','documents.key as dockey','company_document_status.comments as document_comment','company_document_status.status as document_status','company_document_status.comment_type as document_comment_type']);
            if(isset($uploadedPdf)){
                return response()->json([
                    'file' => $uploadedPdf,
                    'status' =>true,
                    'data'   => array(
                        'file'     => $uploadedPdf,
                        'resubmission_status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                        'request_status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
                        'external_comment_type' => $this->settings('COMMENT_EXTERNAL','key')->id,
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


    function resubmit(Request $request ) {

        $company_id = $request->companyId;

        $changeRequest = CompanyChangeRequestItem::where('company_id', $request->companyId)
                                                                            ->whereIn('status', array(
                                                                                $this->settings('COMPANY_CHANGE_PROCESSING','key')->id,
                                                                                $this->settings('COMPANY_CHANGE_RESUBMISSION','key')->id
                                                                            ) 
                                                            )
                                                         ->first();

        $request_id = isset($changeRequest->id) && $changeRequest->id ? $changeRequest->id : null;

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        
        $update =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('COMPANY_CHANGE_RESUBMITTED', 'key')->id]);

        if($update) {
            return response()->json([
                'message' => 'Successfully Resubmitted.',
                'status' =>true,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        } else {
            return response()->json([
                'message' => 'Failed Resubmitting. Please try again later.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        }
        
    }


    






}
