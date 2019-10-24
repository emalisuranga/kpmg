<?php
namespace App\Http\Controllers\API\v1\NameChange;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyPostfix;
use App\Company;
use App\CompanyCertificate;
use App\Address;
use App\Setting;
USE App\SettingType;
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
use App\ChangeName;
use App\CourtCase;
use App\OverseaseOfNameChange;

class NoticeOfNameChangeOfOverseasController extends Controller
{
    use _helper;
    
    public function loadData(Request $request){
  
        if(! ( $request->companyId || $request->changeId ) ){

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

        $request_id = $this->valid_request_operation($request->companyId, $request->changeId);
        if(!$request_id){

            return response()->json([
                'message' => 'We can \'t find a request_id.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);

       
        $Record =  OverseaseOfNameChange::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();

            
 
        $moduleStatus = $this->settings($Record->status,'id')->key;

        if( !( $moduleStatus === 'OVERSEAS_NAME_CHANGE_PROCESSING' ||  $moduleStatus === 'OVERSEAS_NAME_CHANGE_RESUBMIT' ) ) {

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

      /*  if($loginUserInfo->id  != $company_info->created_by ) {
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


        $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

        $postfix_values = $this->getPostfixValues($company_info->postfix);

        $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
         $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

         $external_global_comment = '';


         $form_37b = Documents::where('key', 'FORM_37B')->first();
         $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
     
               
         $changeTable = ChangeName::where('id',$request->changeId )->first();    
         $external_comment_query = CompanyStatus::where('company_id',$changeTable->new_company_id)
                                                         ->where('comment_type', $external_comment_type_id )
                                                         ->where('request_id', $request->changeId)
                                                         ->orderBy('id', 'DESC')
                                                         ->first();
        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                           ?  $external_comment_query->comments
                                           : '';
     
        $members = CompanyMember::where('company_id',$request->companyId)
        ->where('status', '=', 1)
        ->where('designation_type', $this->settings('SECRETARY','key')->id)
        ->get(['id','first_name','last_name','title','designation_type']);

        $memberfirms = CompanyFirms::where('company_id',$request->companyId)
        ->where('status', '=', 1)
        ->where('type_id',  $this->settings('SECRETARY','key')->id)
        ->get(['id','name']);

          $date = array();
            foreach ($members as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    'type' => 0,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $this->settings($value->designation_type,'id')->value,
              ];
            }

            foreach ($memberfirms as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    'type' => 1,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
              ];
            }
             $date[] = [
                    'type' => 1,
                    "title" => '',
                    "first_name" => 'Other',
                    "last_name" => '',
                    "designation" => 'other person',
              ];
             if(!$date){            
                return response()->json([
                    'message' => 'We can \'t find a Address.',
                    'status' =>false,
                ], 200);           
            }

            $change_id_details = ChangeName::where('id', $request->changeId)->first();
            $newCompanyInfo = Company::where('id', $change_id_details->new_company_id)->first();

            $court_data = CourtCase::where('company_id', $changeTable->new_company_id)
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
                        'newCompanyName' => $newCompanyInfo->name,
                        'certificate_no' => $certificate_no,
                        'request_id' => $request_id,
                        'processStatus' => $this->settings($company_info->status,'id')->key,
                        'moduleStatus' => $moduleStatus,
                        'companyType'    =>$companyType,
                        'loginUser'     => $userPeople,
                        'loginUserAddress'=> $userAddress,
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'record' => $Record,
                        'external_global_comment' => $external_global_comment,
                        'companyMember' => $date,
                        'court_data' => $court_data_arr,

                        'downloadDocs' => $this->generate_report($request->companyId, $request->changeId ,array(

                            'company_info' => $company_info,
                            'certificate_no' => $certificate_no,
                            'companyType' => $this->settings($company_info->type_id,'id'),
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                            'record' => $Record,
                           
                        )),
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId, $request->changeId ),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId, $request->changeId ),
                        'form37b_payment' => $this->settings('PAYMENT_OVERSEAS_NAME_CHANGE_FORM_37B','key')->value,
                        'penalty' => $this->getPanaltyCharge($request->companyId, $request_id),
                        'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                        'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                        'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                        )
                ], 200);
          
    }

    function updateCourtRecords(Request $request ) {
        $company_id = $request->companyId;
        $change_id = $request->changeId;
        $request_id = $this->valid_request_operation($company_id, $change_id);

        $changeTable = ChangeName::where('id',$change_id )->first();

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
        $record = CourtCase::where('company_id',$changeTable->new_company_id)
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
            $update = CourtCase::where('company_id', $changeTable->new_company_id)
            ->where('request_id', $request_id)
             ->update($share_summery);
    
        } else {
    
                $court = new CourtCase;
                $court->request_id = $request_id;
                $court->company_id = $changeTable->new_company_id;
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


    private function getPanaltyCharge( $company_id , $request_id ) {


        $record =  OverseaseOfNameChange::where('company_id', $company_id)
            ->where('request_id', $request_id)
             ->first();


        $res_date = strtotime( $record->date_of_change );

    
        $penalty_value = 0;
       

        $min_date_gap = 30;
        $increment_gap_dates = 30;
        $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_37B_INITIAL','key')->value );
        $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_37B_INCREMENT','key')->value );
        $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_37B_MAX','key')->value );
    
    
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

  private function has_request_record($company_id, $change_id) {
    
    $accepted_request_statuses = array(
        $this->settings('OVERSEAS_NAME_CHANGE_APPROVED','key')->id,
        $this->settings('OVERSEAS_NAME_CHANGE_REJECTED','key')->id
    );
    $request_type =  $this->settings('OVERSEAS_NAME_CHANGE','key')->id;

    $record_count = OverseaseOfNameChange::where('company_id', $company_id)
                                ->where('change_id',$change_id)
                                ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count === 1 ) {
        $record = OverseaseOfNameChange::where('company_id', $company_id)
        ->where('change_id',$change_id)
        ->whereNotIn('status', $accepted_request_statuses )
         ->first();

        return $record->request_id;
    } else {
        return false;
    }
}

  private function valid_request_operation($company_id,$change_id){

    /*$accepted_request_statuses = array(
        $this->settings('REGISTER_OF_CHARGES_PROCESSING','key')->id,
        $this->settings('REGISTER_OF_CHARGES_RESUBMIT','key')->id
    );*/
    $accepted_request_statuses = array(
        $this->settings('OVERSEAS_NAME_CHANGE_APPROVED','key')->id,
        $this->settings('OVERSEAS_NAME_CHANGE_REJECTED','key')->id
    );
    $request_type =  $this->settings('OVERSEAS_NAME_CHANGE','key')->id;

    $exist_request_id = $this->has_request_record($company_id,$change_id);

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
            $request->status = $this->settings('OVERSEAS_NAME_CHANGE_PROCESSING','key')->id;
            $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
            $request->save();
            $request_id =  $request->id;

            $record = new OverseaseOfNameChange;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->change_id = $change_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('OVERSEAS_NAME_CHANGE_PROCESSING','key')->id;
            $record->save();
            $record_id =  $record->id;

            ChangeName::where('id',$change_id)->update(['is_foreign' => 1]);

            if($record_id && $request_id ) {
                return $request_id;
            }else{
                return false;
            }

    }
    
}

  function generate_report($company_id, $change_id, $info_array=array()){

    $generated_files = array(
          'docs' => array(),
    );
    $request_id = $this->valid_request_operation($company_id, $change_id);

    if(!$request_id) {
        return $generated_files;
    }
  
    $file_name_key = 'form37b';
    $file_name = 'FORM 37';


    $data = $info_array;
                  
    $directory = "overseas/notice-of-name-change/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form37b';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;

    return $generated_files;
  }


  function files_for_upload_docs($company_id,$change_id){

    $generated_files = array(
            'docs' => array(),
            'uploadedAll' => false
    );

    $request_id = $this->valid_request_operation($company_id,$change_id);

    if(!$request_id) {
            return $generated_files;
    }
    
    $request_type =  $this->settings('OVERSEAS_NAME_CHANGE','key')->id;

    $request = CompanyChangeRequestItem::where('request_type',$request_type)
                           ->where('company_id', $company_id)
                           ->where('id', $request_id)
                           ->first();
    $changeTable = ChangeName::where('id',$change_id )->first();

  
    // documents list
    $form_37b = Documents::where('key', 'FORM_37B')->first();
    $form_other_docs = Documents::where('key', 'OVERSEAS_NAME_CHANGE_OTHER_DOCUMENTS')->first();
    $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

    $has_all_uploaded_str = '';

    $file_row = array();
    $file_row['doc_comment'] = '';
    $file_row['doc_status'] = 'DOCUMENT_PENDING';
    $file_row['is_required'] = true;
    $file_row['file_name'] = $form_37b->name;
    $file_row['file_type'] = '';
    $file_row['dbid'] = $form_37b->id;
    $file_row['file_description'] = $form_37b->description;
    $file_row['applicant_item_id'] = null;
    $file_row['member_id'] = null;
    $file_row['request_id'] = $request_id;
    $file_row['uploaded_path'] = '';
    $file_row['is_admin_requested'] = false;
            
    $uploadedDoc =  CompanyDocuments::where('company_id', $changeTable->new_company_id)
                                    ->where('request_id',$request_id)
                                    ->where('document_id', $form_37b->id )
                                    ->orderBy('id', 'DESC')
                                    ->first();
    $uploadeDocStatus = @$uploadedDoc->status;
    $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
    if($request->status == 'OVERSEAS_NAME_CHANGE_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
     $callShareGroup = DocumentsGroup::where('request_type', 'OVERSEAS_NAME_CHANGE')->first();
     $callShareDocuments = Documents::where('document_group_id', $callShareGroup->id)
                                        // ->where('key', '!=' , 'FORM_7')
                                         ->get();
     $callShareDocumentsCount = Documents::where('document_group_id', $callShareGroup->id)
                                            // ->where('key', '!=' , 'FORM_7')
                                             ->count();

     if($callShareDocumentsCount){
         foreach($callShareDocuments as $other_doc ) {


            if($form_37b->id === $other_doc->id ||  $form_other_docs->id === $other_doc->id) {
                continue;
            }


             $is_document_requested =  CompanyDocuments::where('company_id', $changeTable->new_company_id)
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
                     
             $uploadedDoc =  CompanyDocuments::where('company_id', $changeTable->new_company_id )
                                             ->where('request_id',$request_id)
                                             ->where('document_id', $other_doc->id )
                                             ->orderBy('id', 'DESC')
                                             ->first();
             $uploadeDocStatus = @$uploadedDoc->status;
             $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
             if($request->status == 'OVERSEAS_NAME_CHANGE_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

    function files_for_other_docs($company_id, $change_id){

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
        $request_id = $this->valid_request_operation($company_id, $change_id);

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

        $changeTable = ChangeName::where('id',$change_id )->first();
        // documents list
        $form_other_docs = Documents::where('key', 'OVERSEAS_NAME_CHANGE_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

       

        $other_docs = CompanyDocuments::where('company_id', $changeTable->new_company_id)
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
            $file_row['doc_status2'] = $docs->status;
                    
            $uploadeDocStatus = @$docs->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($company_status == 'OVERSEAS_NAME_CHANGE_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                            $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
            }
            if(isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    
            $file_row['uploaded_path'] =    isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->file_description) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->file_description ? $docs->file_description : '';

            $file_row['uploaded_token'] =   isset($docs->file_token)  &&
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

    function submitRecords(Request $request){
        
        $company_id = $request->companyId;
        $change_id = $request->changeId;

        $request_id = $this->valid_request_operation($company_id,$change_id);

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

        $record = OverseaseOfNameChange::where('company_id', $company_id)
        ->where('request_id', $request_id)
         ->first();

         if(!isset($record->id)) { 

            return response()->json([
                'message' => 'Invalid Request having empty charge record.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

       

      $share_summery = array(
        'new_name' => isset($request->overseas_name_change['new_name']) && $request->overseas_name_change['new_name'] ? $request->overseas_name_change['new_name'] : null,
        'date_of_change' => isset($request->overseas_name_change['date_of_change']) && $request->overseas_name_change['date_of_change'] ? $request->overseas_name_change['date_of_change'] : null,
        'auth_person_name' => isset($request->auth_person_name) && $request->auth_person_name ? $request->auth_person_name : null,
        'other_auth_person' => isset($request->auth_person_other_name) && $request->auth_person_other_name ? $request->auth_person_other_name : null,
        'old_name' => $company_info->name
    );
    OverseaseOfNameChange::where('company_id', $company_id)
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
        $change_id = $request->changeId;

        $request_id = $this->valid_request_operation($company_id,$change_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $registerOfChargesRecord =  OverseaseOfNameChange::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($registerOfChargesRecord->status) && $registerOfChargesRecord->status === $this->settings('OVERSEAS_NAME_CHANGE_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Charges registration Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = OverseaseOfNameChange::where('request_id', $request_id)->update(['status' => $this->settings('OVERSEAS_NAME_CHANGE_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('OVERSEAS_NAME_CHANGE_RESUBMITTED', 'key')->id]);

        $changeTable = ChangeName::where('id',$registerOfChargesRecord->change_id )->first();
        if ($changeTable != null) {
            $changeTable->status =  $this->settings('COMPANY_NAME_CHANGE_RESUBMITTED', 'key')->id;
            if ($changeTable->save()) {
                $oldCom = Company::find($changeTable->new_company_id);
                $oldCom->status = $this->settings('COMPANY_NAME_CHANGE_RESUBMITTED', 'key')->id;
                $oldCom->save();
            }
        }
        
        
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

    function uploadOtherDocs(Request $request){
        
        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $company_id = $request->company_id;
        $change_id = $request->changeId;
        $file_description = $request->fileDescription;
        
        $request_id = $this->valid_request_operation($company_id,$change_id);

  
        $size = $request->file('uploadFile')->getClientSize() ;
        $ext = $request->file('uploadFile')->getClientMimeType();
    
        if('application/pdf' !== $ext ){
    
             return response()->json([
                 'message' => 'Please upload your files with pdf format.',
                 'status' =>false,
                 'error'  => 'yes'
                 
                 
             ], 200);
        }
    
        if( $size >= 1024 * 1024 * 10) {
    
             return response()->json([
                 'message' => 'You can upload document only up to 10 MB.',
                 'status' =>false,
                 'error'  => 'yes'
                 
                 
             ], 200);
        }

        $changeTable = ChangeName::where('id',$change_id )->first();
    
        $path = 'company/'.$company_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $other_doc_count = CompanyDocuments::where('company_id',$changeTable->new_company_id)
                            ->where('document_id',$file_type_id )
                            ->count();
    

        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
          
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
    
 
           $doc = new CompanyDocuments;
           $doc->document_id = $file_type_id;
           $doc->path = $path;
           $doc->company_id = $changeTable->new_company_id;
           $doc->request_id = $request_id;
           $doc->change_id = $change_id;
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

function removeOtherDoc(Request $request){

        $file_token = $request->file_token;
        

        CompanyDocuments::where('file_token', $file_token)
                        ->delete();

        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        

        ], 200);
    }

     function uploadOtherResubmittedDocs(Request $request){
        
        $company_id = $request->company_id;
        $change_id = $request->changeId;
        $multiple_id = $request->multiple_id;
        $request_id = $this->valid_request_operation($company_id,$change_id);

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
    
        $path = 'workflows/overseas-name-change/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $changeTable = ChangeName::where('id',$change_id )->first();
          
         $form_other_docs = Documents::where('key', 'OVERSEAS_NAME_CHANGE_OTHER_DOCUMENTS')->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
           );
           CompanyDocuments::where('company_id', $changeTable->new_company_id)
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
            $change_id = $request->changeId;

            $request_id = $this->valid_request_operation($company_id,$change_id);

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
        
            $path = 'overseas/notice-of-name-change/'.substr($company_id,0,2).'/'.$request_id;
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
            $token = md5(uniqid());
            $changeTable = ChangeName::where('id',$change_id )->first();
    
            $get_query = CompanyDocuments::query();
            $get_query->where('company_id', $changeTable->new_company_id );
            $get_query->where('request_id', $request_id);
            $get_query->where('document_id',$file_type_id);
            $old_doc_info = $get_query->first();
        
            $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
              
            $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
            $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
        
        
            $query = CompanyDocuments::query();
            $query->where('company_id', $changeTable->new_company_id );
            $query->where('request_id', $request_id);
            $query->where('document_id',$file_type_id);
            $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
            $query->delete();
                
        
               $doc = new CompanyDocuments;
               $doc->document_id = $file_type_id;
               $doc->path = $path;
               $doc->company_id = $changeTable->new_company_id;
               $doc->request_id = $request_id;
               $doc->change_id = $change_id;
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
            $change_id = $request->changeId;
            $request_id = $this->valid_request_operation($company_id,$change_id);

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