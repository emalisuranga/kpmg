<?php
namespace App\Http\Controllers\API\v1\CallOnShares;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyPostfix;
use App\Company;
use App\CompanyCertificate;
use App\Address;
use App\Setting;
use App\SettingType;
use App\CompanyMember;
use App\CompanyFirms;
use App\DocumentsGroup;
use App\Documents;
use App\Country;
use App\Share;
use App\ShareGroup;
use App\CompanyDocuments;
use App\CompanyDocumentStatus;
use App\User;
use App\People;
use App\CompanyStatus;
use Storage;
use Cache;
use App;
use URL;
use App\Http\Helper\_helper;
use PDF;
use App\CompanyChangeRequestItem;
use App\CompanyItemChange;
use App\AnnualReturn;
use App\ShareRegister;
use App\AnnualRecords;
use App\AnnualAuditors;
use App\AnnualCharges;
use App\ShareClasses;

use App\Form9;
use App\Form9Records;

class Form9Controller extends Controller
{
    use _helper;
    
    public function loadData(Request $request){
  
        if(!$request->companyId){

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $company_info = Company::where('id',$request->companyId)->first();
        $companyType = $this->settings($company_info->type_id,'id');

        if( ! isset($company_info->id)) {

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }
        if($companyType->key == 'COMPANY_TYPE_GUARANTEE_32' || $companyType->key == 'COMPANY_TYPE_GUARANTEE_34' || $companyType->key == 'COMPANY_TYPE_OVERSEAS' || $companyType->key == 'COMPANY_TYPE_OFFSHORE') {

            return response()->json([
                'message' => 'company type not allowed.',
                'status' =>false,
                'data' => array(
                    'companytypeValid' => false
                ),
               
            ], 200);

        }

        $request_id = $this->valid_calls_on_shares_request_operation($request->companyId);
        $callonSharesRecord =  Form9::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        $moduleStatus = $this->settings($callonSharesRecord->status,'id')->key;

        $signedby = null;
        $signedbytype = null;
        if($request_id){

            $changeRequest = CompanyChangeRequestItem::leftJoin('settings','company_change_requests.signed_by_table_type','=','settings.id')
       ->where('company_change_requests.id',$request_id)
       ->get(['company_change_requests.signed_by','settings.key as tableType']);
       
       $signedby = $changeRequest[0]['signed_by'];
       $signedbytype = $changeRequest[0]['tableType'];
       $dateForm = array();

       if($signedby && $signedbytype){
        $changeRequest = CompanyChangeRequestItem::where('id',$request_id)->first();

        if($changeRequest->signed_by_table_type == $this->settings('COMPANY_MEMBERS','key')->id){

            $member = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.id',$changeRequest->signed_by)
       ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

       $dateForm = array();
                      
        $dateForm[] = [
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

       $dateForm = array();
                      
        $dateForm[] = [
                    "id" => $member[0]['id'],
                    "type" => 1,
                    "title" => '',
                    "first_name" => $member[0]['name'],
                    "last_name" => '',
                    "designation" => $member[0]['designation'],
                    ];

        }

       }


        }

        if( !( $moduleStatus === 'COMPANY_SHARE_FORM9_PROCESSING' ||  $moduleStatus === 'COMPANY_SHARE_FORM9_RESUBMIT' ) ) {

            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }


        
        $loginUserEmail = $this->clearEmail($request->loginUser);
        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->people_id;

        // $loggedUserEmail = $request->email;
        // $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
        // $createdUserId = Company::where('id', $request->id)->value('created_by');
        // checking if user has access

        $inArray = array(
            $this->settings('DERECTOR', 'key')->id,
            $this->settings('SECRETARY', 'key')->id
        );


        $membs = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->companyId)
        ->where('company_members.email',$loginUserEmail)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->whereIn('company_members.designation_type', $inArray)
        ->get();

        $membfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->companyId)
        ->where('company_member_firms.email',$loginUserEmail)
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

        // if($loginUserInfo->id  != $company_info->created_by ) {
        //     return response()->json([
        //         'message' => 'Invalid Profile for this company.',
        //         'status' =>false,
        //         'data' => array(
        //             'createrValid' => false
        //         ),
               
        //     ], 200);
        // }

        $userPeople = People::where('id',$loginUserId)->first();
        $userAddressId = $userPeople->address_id;
        $userAddress = Address::where('id', $userAddressId)->first();

        $company_types = CompanyPostfix::all();
        $company_types = $company_types->toArray();
       
        $companyType = $this->settings($company_info->type_id,'id');

        // company sharholders and sharholder firms arrays

        $shareholdersarray = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->companyId)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
        ->where('company_members.is_srilankan', '=', 'yes')
        ->get(['company_members.nic']);

        $shareholdersarrayforeign = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->companyId)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
        ->where('company_members.is_srilankan', '=', 'no')
        ->get(['company_members.passport_no']);

        $sharefirmsarray = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->companyId)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_member_firms.registration_no']);

        $nicarray = array();
            foreach ($shareholdersarray as $value) {
              $nicarray[] = $value->nic;
            }

        $passportarray = array();
            foreach ($shareholdersarrayforeign as $value) {
              $passportarray[] = $value->passport_no;
            }    

        $regnoarray = array();
            foreach ($sharefirmsarray as $value) {
              $regnoarray[] =$value->registration_no;
            }    

        // company shareholders and shareholder firms arrays end

        $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->companyId)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

        $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->companyId)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_member_firms.id','company_member_firms.name']);

        $date = array();
            foreach ($members as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 0,
                    "name" => $value->first_name .' '. $value->last_name,
                    'type' => 0,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
              ];
            }

            foreach ($memberfirms as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 1,
                    "name" => $value->name,
                    'type' => 1,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
              ];
            }

        
        $shareholders = array();
        $shareholder_list_count = CompanyMember::where('company_id',$request->companyId)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->whereNull('company_member_firm_id' )
        ->where('status',1)
        ->count();

        if($shareholder_list_count) {
            $shareholder_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                ->whereNull('company_member_firm_id' )
                ->where('status',1)
                ->get();
            foreach($shareholder_list as $sh ) {
               // $shareholders[$sh->id] = $sh->first_name. ' '. $sh->last_name;

                $shareholders[] = array(
                    'id' => $sh->id,
                    'name' => $sh->first_name. ' '. $sh->last_name
                );
            }

        }

        $shareholder_firms = array();
        $shareholder_firm_list_count = CompanyFirms::where('company_id',$request->companyId)
          ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
          ->where('status',1)
          ->count();
 
        if($shareholder_firm_list_count) {
             $shareholder_firm_list = CompanyFirms::where('company_id',$request->companyId)
                 ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
                 ->where('status',1)
                 ->get();
                 foreach($shareholder_firm_list as $sh ) {
                
                    $shareholder_firms[] = array(
                        'id' => $sh->id,
                        'name' => $sh->name. '( '. $sh->registration_no. ')'
                    );
                
                }
          }


        /******share record list *****/
        $record_count =0;
        $share_call_record_list = Form9Records::where('status',$this->settings('COMPANY_SHARE_FORM9','key')->id)
                                            ->where('form9_record_id', $callonSharesRecord->id)
                                            ->get();
        
        

        $shareCalls = array();
        foreach($share_call_record_list as $sr){

            $record_count++;
    
            $rec = array(
            'id' => $sr['id'],
            'company_id' =>  $sr->company_id,
            'shareholder_id' => $sr->shareholder_id,
            'person_name' => $sr->person_name,
            'date' =>  $sr->date,
            'aquire_or_redeemed' =>  $sr->aquire_or_redeemed,
            'norm_type' =>  $sr->norm_type,
            'person_type' =>  $sr->person_type,
            'nic' =>  $sr->nic,
            'regno' =>  $sr->regno,
            'passno' =>  $sr->passno,
            'other_share_class' =>  $sr->other_share_class,
            'aquire_or_redeemed_value' => floatval($sr->aquire_or_redeemed_value),
            'share_class' =>  $sr->share_class
            );
         

            if($sr->shareholder_type == 'firm' ) {

                $shareholder_rec = CompanyFirms::where('id',$sr->shareholder_id)
                 ->where('status',1)
                 ->first();

             //    $rec['shareholder_name'] = isset($shareholder_rec->id) ? $shareholder_rec->name : '';

            }

            if($sr->shareholder_type == 'natural' ) {

                $shareholder_rec = CompanyMember::where('id',$sr->shareholder_id)
                 ->where('status',1)
                 ->first();

             //    $rec['shareholder_name'] = isset($shareholder_rec->id) ? $shareholder_rec->first_name. ' '. $shareholder_rec->last_name : '';

            }

            $shareCalls[] = $rec;
        }

        $shareTypeSettingGroup = SettingType::where('key','SHARE_TYPES')->first();
        $share_type_list = Setting::where('setting_type_id', $shareTypeSettingGroup->id)->get();
        $share_types = array();
        foreach($share_type_list as $type ) {
            $row = array();
            $row['id'] = $type->id;
            $row['key'] = $type->key;
            $row['value'] = $type->value;

            $share_types[] = $row;
        }



        
     

        $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

        $postfix_values = $this->getPostfixValues($company_info->postfix);

        $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
        $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

       
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
      
        $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                        ->where('comment_type', $external_comment_type_id )
                                                        ->where('request_id', $request_id)
                                                        ->orderBy('id', 'DESC')
                                                        ->first();
        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                          ?  $external_comment_query->comments
                                          : '';

            return response()->json([
                    'message' => 'Data is successfully loaded.',
                    'status' =>true,
                    'data'   => array(
                        'createrValid' => true,  
                        'companyInfo'  => $company_info,
                        'nicarray'     => $nicarray,
                        'passportarray'     => $passportarray,
                        'regnoarray'     => $regnoarray,
                        'members'     => $date,
                        'signedby' => $signedby,
                        'signedbytype' => $signedbytype,
                        'certificate_no' => $certificate_no,
                        'request_id' => $request_id,
                        'processStatus' => $this->settings($company_info->status,'id')->key,
                        'moduleStatus' => $moduleStatus,
                        'companyType'    =>$companyType,
                        'loginUser'     => $userPeople,
                        'loginUserAddress'=> $userAddress,
                        'share_calls' => $shareCalls,
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'shareholders' => $shareholders,
                        'shareholder_list_count' => $shareholder_list_count,
                        'shareholder_firms' => $shareholder_firms,
                        'shareholder_firm_list_count' => $shareholder_firm_list_count,
                        'callonSharesRecord' => $callonSharesRecord,
                        'external_global_comment' => $external_global_comment,
                        'share_types' => $share_types,

                        'downloadDocs' => $this->generate_calls_on_shares_report($request->companyId,array(

                            'company_info' => $company_info,
                            'certificate_no' => $certificate_no,
                            'companyType' => $this->settings($company_info->type_id,'id'),
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'share_calls' => $shareCalls,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                            'callonSharesRecord' => $callonSharesRecord,
                            'member' => $dateForm,
                           
                        )),
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                        'uploadOther64or67Docs' => $this->files_for_other_sr_docs($request->companyId),
                        'uploadOther31Docs' => $this->files_for_other_cf_docs($request->companyId),
                        'uploadOtherCoDocs' => $this->files_for_other_co_docs($request->companyId),
                        'form7_payment' => $this->settings('PAYMENT_FORM9','key')->value,
                        'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                        'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                        'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                        )
                ], 200);
          
    }

    function uploadedDocs($companyId){
          
        $uploaded_docs = array();
        
        $company_info = Company::where('id',$companyId)->first();


        $companyTypeKey = $this->settings($company_info->type_id,'id')->key;

        $docs = $this->documents();
        $docs_type_ids=array();


        if( isset($docs[$companyTypeKey]['upload'])){
            foreach($docs[$companyTypeKey]['upload'] as $doc){
                $docs_type_ids[] = $doc['dbid'];
            }
        }
        $requested_doc_status = array(
            $this->settings('DOCUMENT_PENDING','key')->id,
            $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            $this->settings('DOCUMENT_APPROVED','key')->id
        );
   
        $doc =CompanyDocuments::where('company_id', $companyId)
                        ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
                        ->get();

        if(count($doc)){
                
            foreach($doc as $d ){
                if($d->company_member_id){

                    $uploaded_docs[$d->document_id][ $d->company_member_id ] = (isset($d->path)) ? basename($d->path) : '';
                }elseif($d->company_firm_id){
                    $uploaded_docs[$d->document_id][ 'firm-'.$d->company_firm_id ] = (isset($d->path)) ? basename($d->path) : '';
                }else if(isset($d->multiple_id) && $d->multiple_id >=0){
                    $uploaded_docs[$d->document_id][ $d->multiple_id ] = (isset($d->path)) ? basename($d->path) : '';
                }else{
                    $uploaded_docs[$d->document_id]  = (isset($d->path)) ? basename($d->path) : '';
                }  
                
            }
        }
    
      return $uploaded_docs;
    }

    function uploadedDocsWithToken($companyId){
       
        $uploaded_docs = array();
    
        $company_info = Company::where('id',$companyId)->first();


        $companyTypeKey = $this->settings($company_info->type_id,'id')->key;

        $docs = $this->documents();
        $docs_type_ids=array();
        if( isset($docs[$companyTypeKey]['upload'])){
            foreach($docs[$companyTypeKey]['upload'] as $doc){
            $docs_type_ids[] = $doc['dbid'];
            }
        }
 
       
        $doc =CompanyDocuments::where('company_id', $companyId)
        ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
                        ->get();

        if(count($doc)){
            
        foreach($doc as $d ){
            if($d->company_member_id){

                $uploaded_docs[$d->document_id][ $d->company_member_id ] = (isset($d->file_token)) ? $d->file_token : '';
            }elseif($d->company_firm_id){
                $uploaded_docs[$d->document_id][ 'firm-'.$d->company_firm_id ] = (isset($d->file_token)) ? $d->file_token : '';
            }else if(isset($d->multiple_id) && $d->multiple_id >=0){
                $uploaded_docs[$d->document_id][ $d->multiple_id ] = (isset($d->file_token)) ?$d->file_token : '';
            }else{
                $uploaded_docs[$d->document_id]  = (isset($d->file_token)) ? $d->file_token : '';
            }  
                
        }
        }
 

        return $uploaded_docs;
    }


    function uploadedDocsWithNoOfPages($companyId){
    
            $uploaded_docs = array();

            $company_info = Company::where('id',$companyId)->first();


            $companyTypeKey = $this->settings($company_info->type_id,'id')->key;

            $docs = $this->documents();
            $docs_type_ids=array();
            if( isset($docs[$companyTypeKey]['upload'])){
                foreach($docs[$companyTypeKey]['upload'] as $doc){
                $docs_type_ids[] = $doc['dbid'];
                }
            }


            $doc =CompanyDocuments::where('company_id', $companyId)
            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
                            ->get();

            if(count($doc)){
                
            foreach($doc as $d ){
                if($d->company_member_id){

                @   $uploaded_docs[$d->document_id][ $d->company_member_id ] = (isset($d->no_of_pages)) ? $d->no_of_pages : '';
                }elseif($d->company_firm_id){
                @   $uploaded_docs[$d->document_id][ 'firm-'.$d->company_firm_id ] = (isset($d->no_of_pages)) ? $d->no_of_pages : '';
                }else if(isset($d->multiple_id) && $d->multiple_id >=0){
                @ $uploaded_docs[$d->document_id][ $d->multiple_id ] = (isset($d->no_of_pages)) ?$d->no_of_pages : '';
                }else{
                    @  $uploaded_docs[$d->document_id]  = (isset($d->no_of_pages)) ? $d->no_of_pages : '';
                }  
                    
            }
        }

        return $uploaded_docs;
    }


  private function getCompanyPostFix($type_id) {
     $company_types = CompanyPostfix::all();
   
     foreach($company_types as $type ) {
         if($type->company_type_id == $type_id ) {

            return array(

                 'postfix' => $type->postfix,
                 'postfix_si' => $type->postfix_si,
                 'postfix_ta' => $type->postfix_ta,

            );
         }
     }

      return array(

        'postfix' => '',
        'postfix_si' => '',
        'postfix_ta' => '',

   );


  }

  private function getPostfixValues( $postfix_en_value ){

     $postix_values = CompanyPostfix::where('postfix', $postfix_en_value)->first();

     return array(
         'postfix_si' => ( isset($postix_values->postfix_si) && $postix_values->postfix_si) ? $postix_values->postfix_si : '',
         'postfix_ta' => ( isset($postix_values->postfix_ta) && $postix_values->postfix_ta ) ? $postix_values->postfix_ta : '',
     );

  }

  private function has_calls_on_shares_record($company_id) {
    
    $accepted_request_statuses = array(
        $this->settings('COMPANY_SHARE_FORM9_APPROVED','key')->id,
        $this->settings('COMPANY_SHARE_FORM9_REJECTED','key')->id
    );
   
    $record_count = Form9::where('company_id', $company_id)
                              ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count === 1 ) {
        $record = Form9::where('company_id', $company_id)
        ->whereNotIn('status', $accepted_request_statuses )
         ->first();

        return $record->request_id;
    } else {
        return false;
    }
}

  private function valid_calls_on_shares_request_operation($company_id){

    $accepted_request_statuses = array(
        $this->settings('COMPANY_SHARE_FORM9_APPROVED','key')->id,
        $this->settings('COMPANY_SHARE_FORM9_REJECTED','key')->id
    );
   
    $request_type =  $this->settings('COMPANY_SHARE_FORM9','key')->id;

    $exist_request_id = $this->has_calls_on_shares_record($company_id);

    if($exist_request_id) {

        $request_count = CompanyChangeRequestItem::where('request_type',$request_type)
                           ->where('company_id', $company_id)
                           ->where('id', $exist_request_id)
                           ->whereNotIn('status', $accepted_request_statuses )
                           ->count();
        if($request_count !== 1) { // request not in processing or  resubmit stage
            return false;
        } else {
            return $exist_request_id;
        }
         
    } else {
            $company_info = Company::where('id', $company_id)->first();
            $year = date('Y',time());

            $request = new CompanyChangeRequestItem;
            $request->company_id = $company_id;
            $request->request_type = $request_type;
            $request->status = $this->settings('COMPANY_SHARE_FORM9_PROCESSING','key')->id;
            $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
            $request->save();
            $request_id =  $request->id;

            $record = new Form9;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('COMPANY_SHARE_FORM9_PROCESSING','key')->id;
            $record->save();
            $record_id =  $record->id;

            $update_compnay_updated_at = array(
                'updated_at' => date('Y-m-d H:i:s', time())
            );
            Company::where('id', $company_id)
            ->update($update_compnay_updated_at);

            if($record_id && $request_id ) {
                return $request_id;
            }else{
                return false;
            }

    }
    
}

  function generate_calls_on_shares_report($company_id, $info_array=array()){

    $generated_files = array(
          'docs' => array(),
    );
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    if(!$request_id) {
        return $generated_files;
    }
  
    $file_name_key = 'form9';
    $file_name = 'FORM 09';


    $data = $info_array;
                  
    $directory = "form9/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form9';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;

    return $generated_files;
  }
  ////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////

  function uploadOtherResubmittedDocs(Request $request){
        
    $company_id = $request->company_id;
    $multiple_id = $request->multiple_id;
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    $file_name =  uniqid().'.pdf'; uniqid().'.pdf';


    $size = $request->file('uploadFile')->getClientSize() ;
    $ext = $request->file('uploadFile')->getClientMimeType();

    if('application/pdf' !== $ext ){

         return response()->json([
             'message' => 'Please upload your files with pdf format.',
             'status' =>false,
             'error'  => 'yes'
             
             
         ], 200);
    }

    if( $size >= 1024 * 1024 * 4) {

         return response()->json([
             'message' => 'You can upload document only up to 4 MB.',
             'status' =>false,
             'error'  => 'yes'
             
             
         ], 200);
    }

    $path = 'form9/other-docs/'.substr($company_id,0,2);
    $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');

    $token = md5(uniqid());

      
     //$form_other_docs = Documents::where('key', 'FORM9_OTHER_DOCUMENTS')->first();


       $update_arr = array(
            'file_token' => $token,
            'path' => $path,
            'status' => $this->settings('DOCUMENT_PENDING','key')->id,
       );
       CompanyDocuments::where('company_id', $company_id)
       ->where('multiple_id', $multiple_id)
       //->where('document_id',$form_other_docs->id )
       ->where('request_id',$request_id)
       ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id)
        ->update($update_arr);


       return response()->json([
        'message' => 'File uploaded successfully.',
        'status' =>true,
        'name' =>basename($path),
        'error'  => 'no',
    ], 200);


}

function removeOtherDoc(Request $request){

    $file_token = $request->file_token;
    $doc = CompanyDocuments::where('file_token', $file_token)->first();
    $docstatusid = CompanyDocumentStatus::where('company_document_id', $doc->id)->first();
    if($docstatusid){
        $document = CompanyDocuments::where('file_token', $file_token)->first();
            CompanyDocuments::where('file_token', $file_token)
            ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);

    }
    else{
        CompanyDocuments::where('file_token', $file_token)
                     ->delete();

    }
    

    return response()->json([
                    'message' => 'File removed successfully.',
                    'status' =>true,
                    

    ], 200);
}

  function uploadOtherDocs(Request $request){

    $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
    $real_file_name = $request->fileRealName;
    $file_type_id = $request->fileTypeId;
    $company_id = $request->company_id;
    $file_description = $request->fileDescription;
    
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);


    $size = $request->file('uploadFile')->getClientSize() ;
    $ext = $request->file('uploadFile')->getClientMimeType();

    if('application/pdf' !== $ext ){

         return response()->json([
             'message' => 'Please upload your files with pdf format.',
             'status' =>false,
             'error'  => 'yes'
             
             
         ], 200);
    }

    if( $size >= 1024 * 1024 * 4) {

         return response()->json([
             'message' => 'You can upload document only up to 4 MB.',
             'status' =>false,
             'error'  => 'yes'
             
             
         ], 200);
    }

    $path = 'form9/other-docs/'.substr($company_id,0,2);
     $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');

    $token = md5(uniqid());

    $other_doc_count = CompanyDocuments::where('company_id',$company_id)
                        ->where('document_id',$file_type_id )
                        ->count();


    //$old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
      
    $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
    $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
    $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;


       $doc = new CompanyDocuments;
       $doc->document_id = $file_type_id;
       $doc->path = $path;
       $doc->company_id = $company_id;
       $doc->request_id = $request_id;
       $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
       $doc->file_token = $token;
       $doc->multiple_id = mt_rand(1,1555400976);
       $doc->name = $real_file_name;
       $doc->file_description = $file_description;
       $doc->save();
       $new_doc_id = $doc->id;

       return response()->json([
        'message' => 'File uploaded successfully.',
        'status' =>true,
        'name' =>basename($path),
        'error'  => 'no',
    ], 200);


}

function files_for_other_co_docs($company_id){

    $generated_files = array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0,
    );
  //  $company_id = $request->company_id;

    if(!$company_id) {
        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }

    $request_id = null;
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    $company_info = Company::where('id', $company_id)->first();
    $company_status = $this->settings($company_info->status,'id')->key;

    $sharecallRow = Form9::where('company_id', $company_id)
            ->where('request_id', $request_id)
            ->first();
    if($sharecallRow->ground){
        $ground_array =    json_decode($sharecallRow->ground);
    if (in_array('CourtOrder', $ground_array,true)){
        $form_other_docs = Documents::where('key', 'FORM9_COURT_ORDER')->first();

            // $delete_form_other_docs_cf = Documents::where('key', 'FORM9_AOA')->first();
            // $other_docs_delete = CompanyDocuments::where('company_id', $company_id)
            //                         ->whereIn('document_id',array($delete_form_other_docs_aoa->id) )
            //                         ->where('request_id', $request_id)
            //                         ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
            //                         ->delete();

    }
    else{
        $delete_form_other_docs_co = Documents::where('key', 'FORM9_COURT_ORDER')->first();
        $other_docs_delete = CompanyDocuments::where('company_id', $company_id)
                                    ->whereIn('document_id',array($delete_form_other_docs_co->id) )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->delete();
                                 

        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }

    }
    else{
        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }        
    
        
        

  
    // documents list
    
    $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
    $generated_files['doc_id'] = $form_other_docs->id;

    $has_all_uploaded_str = '';

   

    $other_docs = CompanyDocuments::where('company_id', $company_id)
                                    ->where('document_id', $form_other_docs->id )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->orderBy('id', 'DESC')
                                    ->get();
    foreach($other_docs as $docs ) {

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $docs->file_description;
        $file_row['file_type'] = '';
        $file_row['multiple_id'] = $docs->multiple_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadeDocStatus = @$docs->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($company_status == 'COMPANY_SHARE_FORM9_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = CompanyDocumentStatus::where('company_document_id', $docs->id )
                                                            ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                            ->where('comment_type', $external_comment_type_id )
                                                            // ->where('multiple_id', $docs->multiple_id )
                                                            ->first();
                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

        }

        $file_row['uploaded_path'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_description : '';
        $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_token : '';

        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                
                
        $generated_files['docs'][] = $file_row;

    }



    $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
    
    return $generated_files;

}

function files_for_other_cf_docs($company_id){

    $generated_files = array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0,
    );
  //  $company_id = $request->company_id;

    if(!$company_id) {
        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }

    $request_id = null;
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    $company_info = Company::where('id', $company_id)->first();
    $company_status = $this->settings($company_info->status,'id')->key;

    $sharecallRow = Form9::where('company_id', $company_id)
            ->where('request_id', $request_id)
            ->first();
    if($sharecallRow->ground){
        $ground_array =    json_decode($sharecallRow->ground);
    if (in_array('31', $ground_array,true)){
        $form_other_docs = Documents::where('key', 'FORM9_CONSENT')->first();

            // $delete_form_other_docs_cf = Documents::where('key', 'FORM9_AOA')->first();
            // $other_docs_delete = CompanyDocuments::where('company_id', $company_id)
            //                         ->whereIn('document_id',array($delete_form_other_docs_aoa->id) )
            //                         ->where('request_id', $request_id)
            //                         ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
            //                         ->delete();

    }
    else{
        $delete_form_other_docs_cf = Documents::where('key', 'FORM9_CONSENT')->first();
        $other_docs_delete = CompanyDocuments::where('company_id', $company_id)
                                    ->whereIn('document_id',array($delete_form_other_docs_cf->id) )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->delete();
                                 

        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }

    }
    else{
        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }        
    
        
        

  
    // documents list
    
    $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
    $generated_files['doc_id'] = $form_other_docs->id;

    $has_all_uploaded_str = '';

   

    $other_docs = CompanyDocuments::where('company_id', $company_id)
                                    ->where('document_id', $form_other_docs->id )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->orderBy('id', 'DESC')
                                    ->get();
    foreach($other_docs as $docs ) {

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $docs->file_description;
        $file_row['file_type'] = '';
        $file_row['multiple_id'] = $docs->multiple_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadeDocStatus = @$docs->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($company_status == 'COMPANY_SHARE_FORM9_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = CompanyDocumentStatus::where('company_document_id', $docs->id )
                                                            ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                            ->where('comment_type', $external_comment_type_id )
                                                            // ->where('multiple_id', $docs->multiple_id )
                                                            ->first();
                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

        }

        $file_row['uploaded_path'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_description : '';
        $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_token : '';

        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                
                
        $generated_files['docs'][] = $file_row;

    }



    $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
    
    return $generated_files;

}

function files_for_other_sr_docs($company_id){

    $generated_files = array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0,
    );
  //  $company_id = $request->company_id;

    if(!$company_id) {
        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }

    $request_id = null;
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    $company_info = Company::where('id', $company_id)->first();
    $company_status = $this->settings($company_info->status,'id')->key;

    $sharecallRow = Form9::where('company_id', $company_id)
            ->where('request_id', $request_id)
            ->first();
    if($sharecallRow->ground){
        $ground_array =    json_decode($sharecallRow->ground);
    if (in_array('64', $ground_array,true) || in_array('67', $ground_array,true)){
        if (($sharecallRow->type_of_64_67) == 'no'){
            $form_other_docs = Documents::where('key', 'FORM9_SR')->first();
            $delete_form_other_docs_aoa = Documents::where('key', 'FORM9_AOA')->first();
            $other_docs_delete = CompanyDocuments::where('company_id', $company_id)
                                    ->whereIn('document_id',array($delete_form_other_docs_aoa->id) )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->delete();

        }
        elseif(($sharecallRow->type_of_64_67) == 'yes'){
            $form_other_docs = Documents::where('key', 'FORM9_AOA')->first();
            $delete_form_other_docs_sr = Documents::where('key', 'FORM9_SR')->first();
            $other_docs_delete = CompanyDocuments::where('company_id', $company_id)
                                    ->whereIn('document_id',array($delete_form_other_docs_sr->id) )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->delete();
        }

    }
    else{
        $delete_form_other_docs_sr = Documents::where('key', 'FORM9_SR')->first();
        $delete_form_other_docs_aoa = Documents::where('key', 'FORM9_AOA')->first();
        $other_docs_delete = CompanyDocuments::where('company_id', $company_id)
                                    ->whereIn('document_id',array($delete_form_other_docs_sr->id,$delete_form_other_docs_aoa->id) )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->delete();
                                 

        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }

    }
    else{
        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }        
    
        
        

  
    // documents list
    
    $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
    $generated_files['doc_id'] = $form_other_docs->id;

    $has_all_uploaded_str = '';

   

    $other_docs = CompanyDocuments::where('company_id', $company_id)
                                    ->where('document_id', $form_other_docs->id )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->orderBy('id', 'DESC')
                                    ->get();
    foreach($other_docs as $docs ) {

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $docs->file_description;
        $file_row['file_type'] = '';
        $file_row['multiple_id'] = $docs->multiple_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadeDocStatus = @$docs->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($company_status == 'COMPANY_SHARE_FORM9_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = CompanyDocumentStatus::where('company_document_id', $docs->id )
                                                            ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                            ->where('comment_type', $external_comment_type_id )
                                                            // ->where('multiple_id', $docs->multiple_id )
                                                            ->first();
                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

        }

        $file_row['uploaded_path'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_description : '';
        $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_token : '';

        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                
                
        $generated_files['docs'][] = $file_row;

    }



    $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
    
    return $generated_files;

}

  function files_for_other_docs($company_id){

    $generated_files = array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0,
    );
  //  $company_id = $request->company_id;

    if(!$company_id) {
        return array(
            'docs' => array(),
            'uploadedAll' => false,
            'doc_id' => 0
    );
    }

    $request_id = null;
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    $company_info = Company::where('id', $company_id)->first();
    $company_status = $this->settings($company_info->status,'id')->key;

  
    // documents list
    $form_other_docs = Documents::where('key', 'FORM9_OTHER_DOCUMENTS')->first();
    $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
    $generated_files['doc_id'] = $form_other_docs->id;

    $has_all_uploaded_str = '';

   

    $other_docs = CompanyDocuments::where('company_id', $company_id)
                                    ->where('document_id', $form_other_docs->id )
                                    ->where('request_id', $request_id)
                                    ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                    ->orderBy('id', 'DESC')
                                    ->get();
    foreach($other_docs as $docs ) {

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $docs->file_description;
        $file_row['file_type'] = '';
        $file_row['multiple_id'] = $docs->multiple_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadeDocStatus = @$docs->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($company_status == 'COMPANY_SHARE_FORM9_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = CompanyDocumentStatus::where('company_document_id', $docs->id )
                                                            ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                            ->where('comment_type', $external_comment_type_id )
                                                            // ->where('multiple_id', $docs->multiple_id )
                                                            ->first();
                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

        }

        $file_row['uploaded_path'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_description : '';
        $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_token : '';

        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                
                
        $generated_files['docs'][] = $file_row;

    }



    $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
    
    return $generated_files;

}


   function files_for_upload_docs($company_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
        );

        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('COMPANY_SHARE_FORM9','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_9 = Documents::where('key', 'FORM_9')->first();
        $form_sr_docs = Documents::where('key', 'FORM9_SR')->first();
        $form_aoa_docs = Documents::where('key', 'FORM9_AOA')->first();
        $form_cf_docs = Documents::where('key', 'FORM9_CONSENT')->first();
        $form_co_docs = Documents::where('key', 'FORM9_COURT_ORDER')->first();
        $form_other_docs = Documents::where('key', 'FORM9_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_9->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_9->id;
        $file_row['file_description'] = $form_9->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_9->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        if($request->status == 'COMPANY_SHARE_FORM9_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                            ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                            ->where('comment_type', $external_comment_type_id )
                                                            ->first();
                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

        }

        $file_row['uploaded_path'] =  isset($uploadedDoc->file_token)  &&
                                            isset($uploadedDoc->path ) &&
                                            isset($uploadedDoc->name) &&
                                            $uploadedDoc->file_token &&
                                            $uploadedDoc->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $uploadedDoc->name ? $uploadedDoc->name : '';
        $file_row['uploaded_token'] =  isset($uploadedDoc->file_token)  &&
                                            isset($uploadedDoc->path ) &&
                                            isset($uploadedDoc->name) &&
                                            $uploadedDoc->file_token &&
                                            $uploadedDoc->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $uploadedDoc->name ? $uploadedDoc->file_token : '';

        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                
                
        $generated_files['docs'][] = $file_row;

         //other documents (those are ususally visible on requesting by the admin )
         $callShareGroup = DocumentsGroup::where('request_type', 'COMPANY_SHARE_FORM9')->first();
         $callShareDocuments = Documents::where('document_group_id', $callShareGroup->id)
                                             ->get();
         $callShareDocumentsCount = Documents::where('document_group_id', $callShareGroup->id)
                                                 ->count();
 
         if($callShareDocumentsCount){
             foreach($callShareDocuments as $other_doc ) {


                if($form_9->id === $other_doc->id) {
                    continue;
                }
                if($form_other_docs->id === $other_doc->id ) {
                    continue;
                }
                if($form_sr_docs->id === $other_doc->id ) {
                    continue;
                }
                if($form_aoa_docs->id === $other_doc->id ) {
                    continue;
                }
                if($form_cf_docs->id === $other_doc->id ) {
                    continue;
                }
                if($form_co_docs->id === $other_doc->id ) {
                    continue;
                }
 
 
                 $is_document_requested =  CompanyDocuments::where('company_id', $company_id)
                 ->where('request_id',$request_id)
                 ->where('document_id', $other_doc->id )
                 ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                 ->orderBy('id', 'DESC')
                 ->count();
 
                 if(!$is_document_requested) {
                     continue;
                 }
 
 
                 $file_row = array();
                 $file_row['doc_comment'] = '';
                 $file_row['doc_status'] = 'DOCUMENT_PENDING';
                 $file_row['is_required'] = true;
                 $file_row['file_name'] = $other_doc->name;
                 $file_row['file_type'] = '';
                 $file_row['dbid'] = $other_doc->id;
                 $file_row['file_description'] = $other_doc->description;
                 $file_row['applicant_item_id'] = null;
                 $file_row['member_id'] = null;
                 $file_row['request_id'] = $request_id;
                 $file_row['uploaded_path'] = '';
                 $file_row['is_admin_requested'] = true;
                         
                 $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                                 ->where('request_id',$request_id)
                                                 ->where('document_id', $other_doc->id )
                                                 ->orderBy('id', 'DESC')
                                                 ->first();
                 $uploadeDocStatus = @$uploadedDoc->status;
                 if($request->status == 'COMPANY_SHARE_FORM9_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                 $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
                 if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id || $uploadeDocStatus == $this->settings('DOCUMENT_REQUESTED','key')->id ) { //if doc is resubmitted
 
                             $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                             ->whereIn('status', array($this->settings('DOCUMENT_REQUESTED','key')->id, $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )  )
                                                                     ->where('comment_type', $external_comment_type_id )
                                                                     ->first();
                                 $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';
 
                 }
 
                 $file_row['uploaded_path'] =  isset($uploadedDoc->file_token)  &&
                                                     isset($uploadedDoc->path ) &&
                                                     isset($uploadedDoc->name) &&
                                                     $uploadedDoc->file_token &&
                                                     $uploadedDoc->path &&
                                                     ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                     $uploadedDoc->name ? $uploadedDoc->name : '';
                 $file_row['uploaded_token'] =  isset($uploadedDoc->file_token)  &&
                                                     isset($uploadedDoc->path ) &&
                                                     isset($uploadedDoc->name) &&
                                                     $uploadedDoc->file_token &&
                                                     $uploadedDoc->path &&
                                                     ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                     $uploadedDoc->name ? $uploadedDoc->file_token : '';
 
                 $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                         
                         
                 $generated_files['docs'][] = $file_row;
 
 
 
                    
 
             }
         }
 






        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
    
        return $generated_files;
    
    }

    function submitShareCallRecords(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }
        // sign by section
        if($request->input('signby')){
            $arr = explode("-",$request->input('signby'));
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

        if( intval($request_id) ){
            $req = CompanyChangeRequestItem::find($request_id);
            $req->signed_by = $signbyid;
            $req->signed_by_table_type = $signbytype;
            $req->save();   
        }

        }
        
        // sign by section

        $loginUserEmail = $this->clearEmail($request->loginUser);
        $direcotrList = array();

        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');

        $sharecallRow = Form9::where('company_id', $company_id)
            ->where('request_id', $request_id)
            ->first();
        if(!isset($sharecallRow->id)) { 

                return response()->json([
                    'message' => 'Invalid Request having no share call row.',
                    'status' =>false,
                    'request_id'   => null,
                    'change_id'    => null,
                ], 200);
    
                 exit();
    
        }


        $call_count = Form9Records::where('status',$this->settings('COMPANY_SHARE_FORM9','key')->id)
                                                ->where('form9_record_id', $sharecallRow->id)
                                                ->count();
        if($call_count){
            $calls = Form9Records::where('status',$this->settings('COMPANY_SHARE_FORM9','key')->id)
                                                ->where('form9_record_id', $sharecallRow->id)
                                                ->get();
            foreach($calls as $d ) {
                
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('FORM9_TABLE','key')->id)
                 ->delete();
                 Form9Records::where('id', $d->id)
                             ->where('status', $this->settings('COMPANY_SHARE_FORM9','key')->id)
                             ->delete();
            }

        }
       // end remore part

      

        //loop through add director list
        foreach($request->call_records['rec'] as $sr ){
            if($sr['norm_type'] == 'Person'){
                if($sr['person_type'] == 'NIC'){
                    $shareholder = CompanyMember::where('company_members.company_id',$request->companyId)
                    ->where('company_members.nic', strtoupper($sr['nic']))
                    ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                    ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
                    ->where('company_members.is_srilankan', '=', 'yes')
                    ->first();
                    
                }
                else{
                    $shareholder = CompanyMember::where('company_members.company_id',$request->companyId)
                    ->where('company_members.passport_no', $sr['passno'])
                    ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                    ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
                    ->where('company_members.is_srilankan', '=', 'no')
                    ->first();

                }

            }
            else{
                $shareholder = CompanyFirms::where('company_member_firms.company_id',$request->companyId)
                    ->where('company_members.registration_no', strtoupper($sr['regno']))
                    ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                    ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
                    ->first();
            }

            $newSr = new Form9Records;
           // $newSr->company_id = $company_id;
            $newSr->shareholder_id = isset($shareholder->id) && intval( $shareholder->id )  ? intval( $shareholder->id ) : null;
            $newSr->person_name = $sr['person_name'];
            $newSr->aquire_or_redeemed = $sr['aquire_or_redeemed'];
            $newSr->norm_type = $sr['norm_type'];
            $newSr->person_type = $sr['person_type'];
            $newSr->nic = strtoupper($sr['nic']);
            $newSr->passno = $sr['passno'];
            $newSr->regno = strtoupper($sr['regno']);
            $newSr->other_share_class = $sr['other_share_class'];
            $newSr->aquire_or_redeemed_value = $sr['aquire_or_redeemed_value'];
            $newSr->date =  $sr['date'];
            $newSr->share_class =  $sr['share_class'];
            $newSr->status =  $this->settings('COMPANY_SHARE_FORM9','key')->id;
            $newSr->form9_record_id = $sharecallRow->id;
            $newSr->save();
            $new_sr_id = $newSr->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_sr_id;
            $change->item_table_type = $this->settings('FORM9_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

      }

      $share_summery = array(
        // 'signing_party_designation' => $request->signing_party_designation ? $request->signing_party_designation : null,
        // 'signing_party_name' => $request->singning_party_name ? $request->singning_party_name : null,
        'ground' => $request->ground ? json_encode($request->ground) : null,
        'type_of_64_67' => $request->type_of_64_67 ? $request->type_of_64_67 : null,
        'total_company_shares' => $request->total_company_shares ? $request->total_company_shares : null,
    );
    Form9::where('company_id', $company_id)
    ->where('request_id', $request_id)
     ->update($share_summery);

     $update_compnay_updated_at = array(
        'updated_at' => date('Y-m-d H:i:s', time())
    );
    Company::where('id', $request->companyId)
    ->update($update_compnay_updated_at);


     

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }



    function resubmit(Request $request ) {

        $company_id = $request->companyId;

        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $callonSharesRecord =  Form9::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($callonSharesRecord->status) && $callonSharesRecord->status === $this->settings('COMPANY_SHARE_FORM9_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = Form9::where('request_id', $request_id)->update(['status' => $this->settings('COMPANY_SHARE_FORM9_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('COMPANY_SHARE_FORM9_RESUBMITTED', 'key')->id]);

        if($update1 && $update2) {
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

/**************************generate downloadable files***********************************/
    private function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
 
    function upload(Request $request){


            $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $company_id = $request->company_id;

            $request_id = $this->valid_calls_on_shares_request_operation($company_id);

            if(!$request_id ){
        
                return response()->json([
                    'message' => 'Invalid Request.',
                    'status' =>false,
                    'error'  => 'yes'
                    
                    
                ], 200);
           }
              
            $size = $request->file('uploadFile')->getClientSize() ;
            $ext = $request->file('uploadFile')->getClientMimeType();
        
            if('application/pdf' !== $ext ){
        
                 return response()->json([
                     'message' => 'Please upload your files with pdf format.',
                     'status' =>false,
                     'error'  => 'yes'
                     
                     
                 ], 200);
            }
        
            if( $size > 1024 * 1024 * 4) {
        
                 return response()->json([
                     'message' => 'File size should be less than 4 MB.',
                     'status' =>false,
                     'error'  => 'yes'
                     
                     
                 ], 200);
            }
        
            $path = 'form9/'.substr($company_id,0,2).'/'.$request_id;
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
            $token = md5(uniqid());
        
    
            $get_query = CompanyDocuments::query();
            $get_query->where('company_id', $company_id );
            $get_query->where('request_id', $request_id);
            $get_query->where('document_id',$file_type_id);
            $old_doc_info = $get_query->first();
        
            $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
              
            $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
            $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
        
        
            $query = CompanyDocuments::query();
            $query->where('company_id', $company_id );
            $query->where('request_id', $request_id);
            $query->where('document_id',$file_type_id);
            $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
            $query->delete();
                
        
               $doc = new CompanyDocuments;
               $doc->document_id = $file_type_id;
               $doc->path = $path;
               $doc->company_id = $company_id;
               $doc->request_id = $request_id;
               $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
               $doc->file_token = $token;
               $doc->name = $real_file_name;
               $doc->save();
               $new_doc_id = $doc->id;

               return response()->json([
                'message' => 'File uploaded successfully.',
                'status' =>true,
                'name' =>basename($path),
                'error'  => 'no',
            ], 200);
        
 
    }


    function removeDoc(Request $request){

            $company_id = $request->companyId;
            $request_id = $this->valid_calls_on_shares_request_operation($company_id);

            if(!$request_id ){
        
                return response()->json([
                    'message' => 'Invalid Request.',
                    'status' =>false,
                ], 200);
           }
            $file_type_id = $request->fileTypeId;
           
    

            CompanyDocuments::where('company_id', $company_id)
                            ->where('request_id',$request_id)
                            ->where('document_id', $file_type_id)
                            ->delete();

            return response()->json([
                            'message' => 'File removed successfully.',
                            'status' =>true,
                            
    
            ], 200);
    }

} // end class