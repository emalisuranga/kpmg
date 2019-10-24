<?php
namespace App\Http\Controllers\API\v1\CallOnShares;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyPostfix;
use App\Company;
use App\CompanyCertificate;
use App\Address;
use App\Setting;
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

use App\ShareCalls;
use App\ShareCallRecords;
use App\CourtCase;

class CallOnSharesController extends Controller
{
    use _helper;



    private function getPanaltyCharge( $company_id , $request_id ) {

        $record = ShareCalls::where('request_id', $request_id)->first();

        $call_records = ShareCallRecords::where('call_share_id', $record->id )->get();

        $obligation_date_arr = array();

        $res_date = '';

        if(isset($call_records[0]->id)) {
            foreach($call_records as $c ) {

                if($c->date_of_performance) {
                    $obligation_date_arr[] = strtotime($c->date_of_performance);
                }

            }
            $res_date = min($obligation_date_arr);
        }
       
    
        $penalty_value = 0;
       

        $min_date_gap = 10;
        $increment_gap_dates = 30;
        $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_7_INITIAL','key')->value );
        $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_7_INCREMENT','key')->value );
        $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_7_MAX','key')->value );
    
    
        $today =  time();
    
        if( $res_date )  {
    
            $date_gap =  intval( ($today - $res_date) / (24*60*60) );
    
            if($date_gap < $min_date_gap ) {
                return 0;
            }
    
            $increment_gaps = ( $date_gap % $increment_gap_dates == 0 ) ? $date_gap / $increment_gap_dates : intval($date_gap / $increment_gap_dates) + 1;
            $penalty_value  = $penalty_value + $init_panalty;
    
            if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
            }
    
        }
    
        return ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;
    
    }
    
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

        if( ! isset($company_info->id)) {

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }

        $request_id = $this->valid_calls_on_shares_request_operation($request->companyId);

      
        $update_compnay_updated_at = array(
           'updated_at' => date('Y-m-d H:i:s', time())
       );
       Company::where('id', $request->companyId)
       ->update($update_compnay_updated_at);


        $callonSharesRecord =  ShareCalls::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        $moduleStatus = $this->settings($callonSharesRecord->status,'id')->key;

        if( !( $moduleStatus === 'CALLS_ON_SHARES_PROCESSING' ||  $moduleStatus === 'CALLS_ON_SHARES_RESUBMIT' ) ) {

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

        /*if($loginUserInfo->id  != $company_info->created_by ) {
            return response()->json([
                'message' => 'Invalid Profile for this company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }*/

        $userPeople = People::where('id',$loginUserId)->first();
        $userAddressId = $userPeople->address_id;
        $userAddress = Address::where('id', $userAddressId)->first();

        $company_types = CompanyPostfix::all();
        $company_types = $company_types->toArray();
       
        $companyType = $this->settings($company_info->type_id,'id');

        
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
        $share_call_record_list = ShareCallRecords::where('company_id',$request->companyId)
                                            ->where('status',$this->settings('CALLS_ON_SHARES','key')->id)
                                            ->where('call_share_id', $callonSharesRecord->id)
                                            ->get();
        
        

        $shareCalls = array();
        foreach($share_call_record_list as $sr){

            $record_count++;                     
    
            $rec = array(
            'id' => $sr['id'],
            'company_id' =>  $sr->company_id,
            'shareholder_id' => $sr->shareholder_id,
            'shareholder_other_name' =>$sr->other_option_shareholder_name,
            'shareholder_type' => $sr->shareholder_type,
            'share_prior_to_this_call' =>  $sr->share_prior_to_this_call,
            'value_respect_of_share' =>  $sr->value_respect_of_share,
            'name_of_shares' =>  $sr->name_of_shares,
            'value_respect_of_total_share' =>  $sr->value_respect_of_total_share,
            'date_of_performance' => $sr->date_of_performance
            );
         

            if($sr->shareholder_id == $this->settings('OPITON_OTHER_INTEGER','key')->value) {
                $rec['shareholder_name'] = $sr->other_option_shareholder_name;
            } else {

                if($sr->shareholder_type == 'firm' ) {

                    $shareholder_rec = CompanyFirms::where('id',$sr->shareholder_id)
                     ->where('status',1)
                     ->first();
    
                     $rec['shareholder_name'] = isset($shareholder_rec->id) ? $shareholder_rec->name : '';
    
                }
    
                if($sr->shareholder_type == 'natural' ) {
    
                    $shareholder_rec = CompanyMember::where('id',$sr->shareholder_id)
                     ->where('status',1)
                     ->first();
    
                     $rec['shareholder_name'] = isset($shareholder_rec->id) ? $shareholder_rec->first_name. ' '. $shareholder_rec->last_name : '';
    
                }
    

            }

            
            $shareCalls[] = $rec;
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

                                          $director_list = CompanyMember::where('company_id',$request->companyId)
                                          ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                          ->where('status',1)
                                          ->orderBy('id','ASC')
                                          ->get();
       $directors = [];
       foreach($director_list as $director) {

           $row = array();
           $row['name'] = $director->first_name.' '.$director->last_name;
           $row['id'] = $director->id;
           $directors[] = $row;

       }

       $sec_list = CompanyMember::where('company_id',$request->companyId)
                                          ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                          ->where('status',1)
                                          ->orderBy('id','ASC')
                                          ->get();
       $secs = [];
       foreach($sec_list as $sec) {

           $row = array();
           $row['name'] = $sec->first_name.' '.$sec->last_name;
           $row['id'] = $sec->id;
           $secs[] = $row;

       }

       $sec_firm_list = CompanyFirms::where('company_id',$request->companyId)
       ->where('type_id',$this->settings('SECRETARY','key')->id)
       ->where('status',1)
       ->orderBy('id','ASC')
       ->get();
       $sec_firms = [];
       foreach($sec_firm_list as $sec) {

           $row = array();
           $row['name'] = $sec->name;
           $row['id'] = $sec->id;
           $sec_firms[] = $row;

       }

       $stakeholder_info = array(
           'type' => '',
           'name' => ''
       );
       if($callonSharesRecord->signing_party_designation == 'Director') {
             $stakeholder_info = CompanyMember::where('id', $callonSharesRecord->signed_party_id)->first();
             $stakeholder_info = array(
                   'type' => 'Director',
                   'name' => $stakeholder_info->first_name. ' ' . $stakeholder_info->last_name
             );
       } 
       if($callonSharesRecord->signing_party_designation == 'Secretary') {
           $stakeholder_info = CompanyMember::where('id', $callonSharesRecord->signed_party_id)->first();
           $stakeholder_info = array(
                 'type' => 'Secretary',
                 'name' => $stakeholder_info->first_name. ' ' . $stakeholder_info->last_name
           );
      } 
      if($callonSharesRecord->signing_party_designation == 'Secretary Firm') {
           $stakeholder_info = CompanyFirms::where('id', $callonSharesRecord->signed_party_id)->first();
           $stakeholder_info = array(
               'type' => 'Secretary Firm',
               'name' => $stakeholder_info->name
           );
       } 

       $penalty_charges = $this->getPanaltyCharge($request->companyId, $request_id);

       $court_data = CourtCase::where('company_id', $request->companyId)
        ->where('request_id', $request_id)
         ->first();
        $court_data_arr = array(
                'court_status' => isset($court_data->court_status) ? $court_data->court_status : null,
                'court_name' => isset($court_data->court_name) ? $court_data->court_name : null,
                'court_date' => isset($court_data->court_date) ? $court_data->court_date : null,
                'court_case_no' => isset($court_data->court_case_no) ? $court_data->court_case_no : null,
                'court_discharged' => isset($court_data->court_discharged) ? $court_data->court_discharged : null,
                'court_penalty' => isset($court_data->court_penalty) ? $court_data->court_penalty : null,
                'court_period' => isset($court_data->court_period) ? $court_data->court_period : null,

        );

            return response()->json([
                    'message' => 'Data is successfully loaded.',
                    'status' =>true,
                    'data'   => array(
                        'createrValid' => true,  
                        'companyInfo'  => $company_info,
                        'certificate_no' => $certificate_no,
                        'request_id' => $request_id,
                        'processStatus' => $this->settings($company_info->status,'id')->key,
                        'moduleStatus' => $moduleStatus,
                        'companyType'    =>$companyType,
                        'loginUser'     => $userPeople,
                        'loginUserAddress'=> $userAddress,
                        'share_calls' => $shareCalls,
                        'court_data' => $court_data_arr,
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
                        'directors' =>$directors,
                        'secs' => $secs,
                        'sec_firms' =>$sec_firms,
                        'penalty_value' => $penalty_charges,

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
                            'stakeholder_info' => $stakeholder_info
                           
                        )),
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                        'form7_payment' => $this->settings('PAYMENT_CALLS_ON_SHARES_FORM7','key')->value,
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
    /*$accepted_request_statuses = array(
        $this->settings('CALLS_ON_SHARES_PROCESSING','key')->id,
        $this->settings('CALLS_ON_SHARES_RESUBMIT','key')->id
    );*/
    $accepted_request_statuses = array(
        $this->settings('CALLS_ON_SHARES_APPROVED','key')->id,
        $this->settings('CALLS_ON_SHARES_REJECTED','key')->id
    );
   
    $record_count = ShareCalls::where('company_id', $company_id)
                              ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count === 1 ) {
        $record = ShareCalls::where('company_id', $company_id)
        ->whereNotIn('status', $accepted_request_statuses )
         ->first();

        return $record->request_id;
    } else {
        return false;
    }
}

  private function valid_calls_on_shares_request_operation($company_id){

    /*$accepted_request_statuses = array(
        $this->settings('CALLS_ON_SHARES_PROCESSING','key')->id,
        $this->settings('CALLS_ON_SHARES_RESUBMIT','key')->id
    );*/
    $accepted_request_statuses = array(
        $this->settings('CALLS_ON_SHARES_APPROVED','key')->id,
        $this->settings('CALLS_ON_SHARES_REJECTED','key')->id
    );
    $request_type =  $this->settings('CALLS_ON_SHARES','key')->id;

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
            $request->status = $this->settings('CALLS_ON_SHARES_PROCESSING','key')->id;
            $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
            $request->save();
            $request_id =  $request->id;

            $record = new ShareCalls;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('CALLS_ON_SHARES_PROCESSING','key')->id;
            $record->save();
            $record_id =  $record->id;

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
  
    $file_name_key = 'form7';
    $file_name = 'FORM 07';

    /**
     * array(
        'company_info' => $company_info,
        'company_address' => $company_address_change,
        'directors' => $directors,
        'secs' => $secs,
        'secFirms' => $secs_firms,
        'shareholders' => $shareholders,
        'shareholderFirms' => $shareholderFirms,
        'share_register' => $shareRegisters,
        'annual_records' => $annualRecords,
        'annual_charges' => $annualCharges,
        'annual_auditors' => $annualAuditors
    );*
    */

    $data = $info_array;
                  
    $directory = "calls-on-shares/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form7';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;

    return $generated_files;
  }


  function updateCourtRecords(Request $request ) {
    $company_id = $request->companyId;

    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    $update = false;

    if(!$request_id) { 

        return response()->json([
            'message' => 'Invalid Request.',
            'status' =>false,
            'request_id'   => null,
            'change_id'    => null,
        ], 200);

         exit();

    }
    $record = CourtCase::where('company_id', $company_id)
    ->where('request_id', $request_id)
     ->count();
    if($record) {

        $share_summery = array(
            'court_status' => $request->court_status,
            'court_name' => ($request->court_status =='no' ) ? null : $request->court_name,
            'court_date' =>($request->court_status =='no' ) ? null : $request->court_date,
            'court_case_no' => ($request->court_status =='no' ) ? null : $request->court_case_no,
            'court_penalty' => ($request->court_status =='no' ) ? null : $request->court_penalty,
            'court_period' => ($request->court_status =='no' ) ? null : $request->court_period,
            'court_discharged' => ($request->court_status =='no' ) ? null : $request->court_discharged,

        );
        $update = CourtCase::where('company_id', $company_id)
        ->where('request_id', $request_id)
         ->update($share_summery);

    } else {

            $court = new CourtCase;
            $court->request_id = $request_id;
            $court->company_id = $company_id;
            $court->court_status = $request->court_status;
            $court->court_name =$request->court_name;
            $court->court_date = $request->court_date;
            $court->court_case_no = $request->court_case_no;
            $court->court_penalty = $request->court_penalty;
            $court->court_period =$request->court_period;
            $court->court_discharged = $request->court_discharged;
            $court->save();

            $update = $court->id;
        
    }

    

     if($update) {
        return response()->json([
            'message' => 'Successfully updated.',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => null,
          ], 200);
     } else {
        return response()->json([
            'message' => 'Failed Updating Court Details. Please try again later',
            'status' =>false,
            'request_id'   => null,
            'change_id'    => null,
          ], 200);
     }

   
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
        
        $request_type =  $this->settings('CALLS_ON_SHARES','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_7 = Documents::where('key', 'FORM_7')->first();
        $form_other_docs = Documents::where('key', 'CALL_SHARES_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_7->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_7->id;
        $file_row['file_description'] = $form_7->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_7->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'CALLS_ON_SHARES_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
         $callShareGroup = DocumentsGroup::where('request_type', 'CALLS_ON_SHARES')->first();
         $callShareDocuments = Documents::where('document_group_id', $callShareGroup->id)
                                            // ->where('key', '!=' , 'FORM_7')
                                             ->get();
         $callShareDocumentsCount = Documents::where('document_group_id', $callShareGroup->id)
                                                // ->where('key', '!=' , 'FORM_7')
                                                 ->count();
 
         if($callShareDocumentsCount){
             foreach($callShareDocuments as $other_doc ) {


                if($form_7->id === $other_doc->id ||  $form_other_docs->id === $other_doc->id) {
                    continue;
                }
 
 
                 $is_document_requested =  CompanyDocuments::where('company_id', $company_id)
                 ->where('request_id',$request_id)
                 ->where('document_id', $other_doc->id )
                 ->whereIn('status', array(
                    $this->settings('DOCUMENT_REQUESTED','key')->id ,
                    $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                    $this->settings('DOCUMENT_PENDING','key')->id,
                    $this->settings('DOCUMENT_APPROVED','key')->id,
                    ) 
                )
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
                 $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
                 if($request->status == 'CALLS_ON_SHARES_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                 $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
                 if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
 
                             $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                                     ->whereIn('status', array($this->settings('DOCUMENT_REQUESTED','key')->id, $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) ) 
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

        $loginUserEmail = $this->clearEmail($request->loginUser);
        $direcotrList = array();

        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');

        $sharecallRow = ShareCalls::where('company_id', $company_id)
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


        $call_count = ShareCallRecords::where('company_id', $company_id)
                                                ->where('status',$this->settings('CALLS_ON_SHARES','key')->id)
                                                ->where('call_share_id', $sharecallRow->id)
                                                ->count();
        if($call_count){
            $calls = ShareCallRecords::where('company_id', $company_id)
                                                ->where('status',$this->settings('CALLS_ON_SHARES','key')->id)
                                                ->where('call_share_id', $sharecallRow->id)
                                                ->get();
            foreach($calls as $d ) {
                
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('CALLS_ON_SHARES_TABLE','key')->id)
                 ->delete();
                 ShareCallRecords::where('id', $d->id)
                             ->where('status', $this->settings('CALLS_ON_SHARES','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

      
        $other_int_option = $this->settings('OPITON_OTHER_INTEGER','key')->value;
        //loop through add director list
        foreach($request->call_records['share'] as $sr ){

            $newSr = new ShareCallRecords;
            $newSr->company_id = $company_id;
            $newSr->shareholder_id = intval( $sr['shareholder_id'] );
            $newSr->shareholder_type = ( $sr['shareholder_type'] === 'firm' ) ? 'firm' : 'natural';
            $newSr->other_option_shareholder_name =  ( intval( $sr['shareholder_id'] ) ==  $other_int_option) ? $sr['shareholder_other_name'] : null;
            $newSr->share_prior_to_this_call = $sr['share_prior_to_this_call'];
            $newSr->value_respect_of_share =  $sr['value_respect_of_share'];
            $newSr->name_of_shares =  $sr['name_of_shares'];
            $newSr->value_respect_of_total_share =  $sr['value_respect_of_total_share'];
            $newSr->status =  $this->settings('CALLS_ON_SHARES','key')->id;
            $newSr->call_share_id = $sharecallRow->id;
            $newSr->date_of_performance = $sr['date_of_performance'];
            $newSr->save();
            $new_sr_id = $newSr->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_sr_id;
            $change->item_table_type = $this->settings('CALLS_ON_SHARES_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

      }

      $share_summery = array(

        'stated_capital' => floatval($request->stated_capital) ? floatval($request->stated_capital) : null,
       // 'total_amount_of_call' => floatval($request->total_amount_of_call) ? floatval($request->total_amount_of_call) : null,
        'signing_party_designation' => $request->signing_party_designation ? $request->signing_party_designation : null,
        'signed_party_id' => $request->singning_party_name ? $request->singning_party_name : null,
    );
    ShareCalls::where('company_id', $company_id)
    ->where('request_id', $request_id)
     ->update($share_summery);
     

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

        $callonSharesRecord =  ShareCalls::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($callonSharesRecord->status) && $callonSharesRecord->status === $this->settings('CALLS_ON_SHARES_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Call on Shares Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = ShareCalls::where('request_id', $request_id)->update(['status' => $this->settings('CALLS_ON_SHARES_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('CALLS_ON_SHARES_RESUBMITTED', 'key')->id]);

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
        
            $path = 'calls-on-shares/'.substr($company_id,0,2).'/'.$request_id;
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
        $form_other_docs = Documents::where('key', 'CALL_SHARES_OTHER_DOCUMENTS')->first();
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
            if($company_status == 'CALLS_ON_SHARES_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    
        $path = 'calls-on-shares/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $other_doc_count = CompanyDocuments::where('company_id',$company_id)
                            ->where('document_id',$file_type_id )
                            ->count();
    

        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
          
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
    
        $path = 'calls-on-shares/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'CALL_SHARES_OTHER_DOCUMENTS')->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
           );
           CompanyDocuments::where('company_id', $company_id)
           ->where('multiple_id', $multiple_id)
           ->where('document_id',$form_other_docs->id )
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
        
    
        CompanyDocuments::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
    
        ], 200);
    }



} // end class