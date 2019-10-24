<?php
namespace App\Http\Controllers\API\v1\specialResolution;
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
use App\SpecialResolution;
use App\CourtCase;


class SpecialResolutionController extends Controller
{
    use _helper;
    
    public function loadData(Request $request){

  
        if(!$request->companyId){

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'type' => 'missing-company-id',
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
                'type' => 'missing-company-info',
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }

        $request_id = $this->valid_request_operation($request->companyId);

        if( $companyType->key == 'COMPANY_TYPE_GUARANTEE_34') {

            $form_prior_approval = Documents::where('key', 'SPECIAL_RESOLUTION_PRIOR_APPROVAL_LETTER')->first();
            $uploadedDoc =  CompanyDocuments::where('company_id', $request->companyId)
                                                ->where('document_id', $form_prior_approval->id )
                                                ->where('status', $this->settings('DOCUMENT_EMAILED','key')->id)
                                                ->orderBy('id', 'DESC')
                                                ->first();
            if(!( isset($uploadedDoc->id) && $uploadedDoc->id ) ) {
                return response()->json([
                    'message' => 'Please get prior approval before proceeding the process.',
                    'status' =>false,
                    'type' => 'missing-prior-approval',
                    'data' => array(
                        'createrValid' => false
                    ),
                   
                ], 200);
            } else {

                $form_prior_approval_request_arr = array(
                    'prior_approval_request_id' =>  $uploadedDoc->request_id
                );

                SpecialResolution::where('company_id', $request->companyId)
                ->where('request_id', $request_id)
                ->update($form_prior_approval_request_arr );

            }

        }

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);
       
        $RegisterOfChargesRecord =  SpecialResolution::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        
        $requset_row =   CompanyChangeRequestItem::where('id',$request_id)
             ->first();

            
            
        $moduleStatus = $this->settings($requset_row->status,'id')->key;

        if( !( $moduleStatus === 'COMPANY_CHANGE_PROCESSING' ||  $moduleStatus === 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' ) ) {

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


       
        


        $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

        $postfix_values = $this->getPostfixValues($company_info->postfix);

        $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
         $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

         $external_global_comment = '';


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
        if($RegisterOfChargesRecord->signed_party_type == 'Director') {
              $stakeholder_info = CompanyMember::where('id', $RegisterOfChargesRecord->signed_party_id)->first();
              $stakeholder_info = array(
                    'type' => 'Director',
                    'name' => $stakeholder_info->first_name. ' ' . $stakeholder_info->last_name
              );
        } 
        if($RegisterOfChargesRecord->signed_party_type == 'Secretary') {
            $stakeholder_info = CompanyMember::where('id', $RegisterOfChargesRecord->signed_party_id)->first();
            $stakeholder_info = array(
                  'type' => 'Secretary',
                  'name' => $stakeholder_info->first_name. ' ' . $stakeholder_info->last_name
            );
       } 
       if($RegisterOfChargesRecord->signed_party_type == 'Secretary Firm') {
            $stakeholder_info = CompanyFirms::where('id', $RegisterOfChargesRecord->signed_party_id)->first();
            $stakeholder_info = array(
                'type' => 'Secretary Firm',
                'name' => $stakeholder_info->name
            );
        } 

        $penalty_charges = $this->getPanaltyCharge($request->companyId, $request_id);
        $company_address = Address::where('id',$company_info->address_id)->first();


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

           $prefix_key = SettingType::where('key', 'COMPANY_TYPES')->first();
           $compnay_prefixes = Setting::where('setting_type_id', $prefix_key->id )->get();
           $prefixes = array();
           $postfixes = array();

           foreach($compnay_prefixes as $p ) {

                   
               $prefixes[] = array('key' =>$p->key, 'value' => $p->value  );
              


               
               $pf_array = array();
               $company_postfixes = CompanyPostfix::where('company_type_id', $p->id)->get();
               if(isset($company_postfixes[0]->id)){
                   foreach($company_postfixes as $pf) {

                         if( $pf->postfix == $company_info->postfix ) {
                             continue;
                         }
                         $pf_array[] = $pf->postfix;
                   }
               }
               $postfixes[$p->key] =  $pf_array;
           }
           $resolution_options_group = SettingType::where('key', 'SPECIAL_RESOLUTION_TYPES')->first();
           $resolution_options = Setting::where('setting_type_id', $resolution_options_group->id )->where('status','active')->get();
           $resolution_option_array = array();

           foreach($resolution_options as $option ) {

                 $row = array(
                     'key' =>$option->key,
                     'value' => $option->value
                 );
                $resolution_option_array[] = $row; 
           }
        
            return response()->json([
                    'message' => 'Data is successfully loaded.',
                    'status' =>true,
                    'data'   => array(
                        'createrValid' => true,  
                        'companyInfo'  => $company_info,
                        'certificate_no' => $certificate_no,
                        'request_id' => $request_id,
                        'processStatus' => $this->settings($company_info->status,'id')->key,
                        'prefixes' => $prefixes,
                        'postfixes' => $postfixes,
                        'resolution_options' => $resolution_option_array,
                        'moduleStatus' => $moduleStatus,
                        'companyType'    =>$companyType,
                        'loginUser'     => $userPeople,
                        'loginUserAddress'=> $userAddress,
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'record' => $RegisterOfChargesRecord,
                        'selectedType' => isset($RegisterOfChargesRecord->type) && $RegisterOfChargesRecord->type ? $this->settings($RegisterOfChargesRecord->type,'key')->value : '',
                        'selectedPrefix' => isset($RegisterOfChargesRecord->new_prefix) && $RegisterOfChargesRecord->new_prefix ? $this->settings($RegisterOfChargesRecord->new_prefix,'key')->value : $this->settings($companyType->key,'key')->value,
                        'court_data' => $court_data_arr,
                        'external_global_comment' => $external_global_comment,
                        'directors' =>$directors,
                        'secs' => $secs,
                        'sec_firms' =>$sec_firms,
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                        'prospectus_payment' => $this->settings('PAYMENT_REDUCTION_OF_STATED_CAPITAL_FORM39','key')->value,
                        'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                        'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                        'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                        'penalty_value' => $penalty_charges,
                        
                        'downloadDocs' => $this->generate_report($request->companyId,array(
                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'certificate_no' => $certificate_no,
                            'companyType' => $this->settings($company_info->type_id,'id'),
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                            'record' => $RegisterOfChargesRecord,
                            'stakeholder_info' => $stakeholder_info
                           
                        )),
                        )
                ], 200);
          
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

  private function getPanaltyCharge( $company_id , $request_id ) {

    

    $record = SpecialResolution::where('request_id', $request_id)->first();

    $resoultion_date = $record->resolution_passed_date;
    $publish_date = $record->publish_date;
    $publish_status = $record->publish_date;

    $penalty_value = 0;
    if(!$resoultion_date) {
       return 0;
    }

    $min_date_gap = 10;
    $increment_gap_dates = 30;
    $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_39_INITIAL','key')->value );
    $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_39_INCREMENT','key')->value );
    $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_39_MAX','key')->value );

    $res_date = strtotime($resoultion_date);

    $today = ($record->type == 'RESOLUTION_CHANGE_OF_STATUS_AND_ARTICLE') ? strtotime($publish_date) : time();

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


  private function has_request_record($company_id) {
   
    $accepted_request_statuses = array(
        $this->settings('COMPANY_CHANGE_APPROVED','key')->id,
        $this->settings('COMPANY_CHANGE_REJECTED','key')->id
    );
    
    $request_type =  $this->settings('SPECIAL_RESOLUTION','key')->id;

    $record_count = CompanyChangeRequestItem::where('company_id', $company_id)
                              ->where('request_type',$request_type)
                              ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count == 1 ) {
        $record = CompanyChangeRequestItem::where('company_id', $company_id)
        ->where('request_type',$request_type)
        ->whereNotIn('status', $accepted_request_statuses )
         ->first();

        return $record->id;
    } else {
        return false;
    }
}

  private function valid_request_operation($company_id){

    $accepted_request_statuses = array(
        $this->settings('COMPANY_CHANGE_APPROVED','key')->id,
        $this->settings('COMPANY_CHANGE_REJECTED','key')->id
    );
    
    $user = $this->getAuthUser();

    $request_type =  $this->settings('SPECIAL_RESOLUTION','key')->id;

    $exist_request_id = $this->has_request_record($company_id);

   // var_dump($exist_request_id);
   // die();

    if($exist_request_id) {

        $request_count = CompanyChangeRequestItem::where('request_type',$request_type)
                           ->where('company_id', $company_id)
                           ->where('id', $exist_request_id)
                           ->whereNotIn('status', $accepted_request_statuses )
                           ->count();
        if($request_count != 1) { // request not in processing or  resubmit stage
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
            $request->status =$this->settings('COMPANY_CHANGE_PROCESSING', 'key')->id;
            $request->request_by = $user->userid;
            $request->save();
            $request_id =  $request->id;

            $record = new SpecialResolution;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('COMMON_STATUS_DEACTIVE', 'key')->id;
            $record->created_by = $user->userid;
            $record->save();
            $record_id =  $record->id;

            if($record_id && $request_id ) {
                return $request_id;
            }else{
                return false;
            }

    }
    
}

  private function get_article_document_by_changed_type($resolution_record) {

    $prefix = $resolution_record->prefix;
    $doc_key = '';

    if(!$prefix) {
        $company_id = $resolution_record->company_id;
        $company_info = Company::where('id',$company_id)->first();
        $prefix = $this->settings($company_info->type_id,'id')->key;
    }
    
        if($prefix == 'COMPANY_TYPE_PRIVATE') {
           $doc_key = 'PRIVATE_ARTICAL_OF_ASSOCIATION';
        }
        if($prefix == 'COMPANY_TYPE_PUBLIC') {
            $doc_key = 'PUBLIC_ARTICAL_OF_ASSOCIATION';
        }
        if($prefix == 'COMPANY_TYPE_UNLIMITED') {
            $doc_key = 'UNLIMITED_ARTICAL_OF_ASSOCIATION';
        }
        if($prefix == 'COMPANY_TYPE_OFFSHORE') {
            $doc_key = 'OFFSHORE_ARTICAL_OF_ASSOCIATION';
        }
        if($prefix == 'COMPANY_TYPE_OVERSEAS') {
            $doc_key = 'OVERSEAS_ARTICAL_OF_ASSOCIATION';
        }
        if($prefix == 'COMPANY_TYPE_GUARANTEE_32') {
            $doc_key = 'GA32_ARTICAL_OF_ASSOCIATION';
        }
        if($prefix == 'COMPANY_TYPE_GUARANTEE_34') {
            $doc_key = 'GA34_ARTICAL_OF_ASSOCIATION';
        }

        return $doc_key;
  }


   function files_for_upload_docs($company_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
        );

       $request_id = $this->valid_request_operation($company_id);

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('SPECIAL_RESOLUTION','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();
        $resolution_record =  SpecialResolution::where('company_id', $company_id)
        ->where('request_id', $request_id)
         ->first();

        $article_key = $this->get_article_document_by_changed_type($resolution_record);

         // sec/cse doc
         $company_info = Company::where('id',$company_id)->first();
         $companyType = $this->settings($company_info->type_id,'id')->key;

      
        // documents list
        $form_39 = Documents::where('key', 'FORM_39')->first();
        $form_prior_approval = Documents::where('key', 'SPECIAL_RESOLUTION_PRIOR_APPROVAL_LETTER')->first();
        $form_article = Documents::where('key', $article_key)->first();
        $form_gazette = Documents::where('key', 'RESOLUTION_GAZZETE')->first();
        $form_sinhala_ad = Documents::where('key', 'RESOLUTION_SINHALA_NEWS_PAPER_AD')->first();
        $form_english_ad = Documents::where('key', 'RESOLUTION_ENGLISH_NEWS_PAPER_AD')->first();
        $form_tamil_ad = Documents::where('key', 'RESOLUTION_TAMIL_NEWS_PAPER_AD')->first();
        $form_sec_or_cse = Documents::where('key', 'RESOLUTION_SEC_OR_CSE')->first();
        $form_other_docs = Documents::where('key', 'RESOLUTION_OTHER_DOCUMENTS')->first();
        

        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_39->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_39->id;
        $file_row['file_description'] = $form_39->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_39->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


        if( $companyType == 'COMPANY_TYPE_GUARANTEE_34') {

            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_prior_approval->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_prior_approval->id;
            $file_row['file_description'] = $form_prior_approval->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('document_id', $form_prior_approval->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        
        }
        



        if(
            $resolution_record->type == 'RESOLUTION_CHANGE_OF_STATUS_AND_ARTICLE' ||
            $resolution_record->type == 'RESOLUTION_CHANGE_OR_ALTER_ARTICLE'
           ) {

            //sinhala news paper
            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_article->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_article->id;
            $file_row['file_description'] = $form_article->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_article->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

           }

        if($resolution_record->type == 'RESOLUTION_CHANGE_OF_STATUS_AND_ARTICLE') {

            //sinhala news paper
            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_sinhala_ad->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_sinhala_ad->id;
            $file_row['file_description'] = $form_sinhala_ad->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_sinhala_ad->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


            //english news paper
            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_english_ad->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_english_ad->id;
            $file_row['file_description'] = $form_english_ad->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_english_ad->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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



            //tamil news paper
            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_tamil_ad->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_tamil_ad->id;
            $file_row['file_description'] = $form_tamil_ad->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_tamil_ad->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


            //gazzet 
            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_gazette->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_gazette->id;
            $file_row['file_description'] = $form_gazette->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_gazette->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


           
           

            $pb = array('PLC');
            $pq = array('LTD','LIMITED');
            $old_suffix = $company_info->postfix;
            $new_suffix = $resolution_record->new_suffix;

            if( $companyType == 'COMPANY_TYPE_PUBLIC' && ( (in_array($old_suffix,$pb) && in_array($new_suffix, $pq) )  || (in_array($old_suffix,$pq) && in_array($new_suffix, $pb) ) ) ) {

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $form_sec_or_cse->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $form_sec_or_cse->id;
                $file_row['file_description'] = $form_sec_or_cse->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['request_id'] = $request_id;
                $file_row['uploaded_path'] = '';
                $file_row['is_admin_requested'] = false;
                        
                $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                                ->where('request_id',$request_id)
                                                ->where('document_id', $form_sec_or_cse->id )
                                                ->orderBy('id', 'DESC')
                                                ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
                if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
            }


        }


        


        //other documents (those are ususally visible on requesting by the admin )
        $regChargeGroup = DocumentsGroup::where('request_type', 'SPECIAL_RESOLUTION_DOCUMENT_GROUP')->first();
        $regChargeDocuments = Documents::where('document_group_id', $regChargeGroup->id)
                                           // ->where('key', '!=' , 'FORM_22')
                                            ->get();
        $regChargeDocumentsCount = Documents::where('document_group_id', $regChargeGroup->id)
                                              //  ->where('key', '!=' , 'FORM_22')
                                                ->count();

        if($regChargeDocumentsCount){
            foreach($regChargeDocuments as $other_doc ) {

                if($form_39->id === $other_doc->id) {
                    continue;
                }
                if($form_gazette->id === $other_doc->id ) {
                    continue;
                }
                if($form_sinhala_ad->id === $other_doc->id ) {
                    continue;
                }
                if($form_tamil_ad->id === $other_doc->id ) {
                    continue;
                }
                if($form_english_ad->id === $other_doc->id ) {
                    continue;
                }
                if($form_sec_or_cse->id === $other_doc->id ) {
                    continue;
                }
                if($form_article->id === $other_doc->id ) {
                    continue;
                }
                if($form_other_docs->id === $other_doc->id ) {
                    continue;
                }
                if( $form_prior_approval->id === $other_doc->id ) {
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
                if($request->status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

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


    function files_for_other_docs($company_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => true,
                'doc_id' => 0,
        );
   

      if(!$company_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $request_id = null;
        $request_id = $this->valid_request_operation($company_id);

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'RESOLUTION_OTHER_DOCUMENTS')->first();
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
            $file_row['file_name'] = $docs->file_description.'.pdf';
            $file_row['file_type'] = '';
            $file_row['multiple_id'] = $docs->multiple_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadeDocStatus = @$docs->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($company_status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        
        $request_id = $this->valid_request_operation($company_id);

  
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
    
        $path = 'special-resolution/other-docs/'.substr($company_id,0,2);
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
        $request_id = $this->valid_request_operation($company_id);

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
    
        $path = 'special-resolution/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'REDUCTION_STATED_CAPITAL_OTHER_DOCUMENTS')->first();


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


    function submitRecords(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_request_operation($company_id);

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
        $companyType = $this->settings($company_info->type_id,'id')->key;

        $charge_record = SpecialResolution::where('company_id', $company_id)
        ->where('request_id', $request_id)
         ->first();

         if(!isset($charge_record->id)) { 

            return response()->json([
                'message' => 'Invalid Request having empty record.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }
        $no_suffixes = array(
           'COMPANY_TYPE_OFFSHORE',
           'COMPANY_TYPE_OVERSEAS',
           'COMPANY_TYPE_UNLIMITED',
           'COMPANY_TYPE_GUARANTEE_34',
        );
        $prefix = null;
        $suffix = null;
        $publish_date = null;
        $old_prefix = null;
        $old_suffix = null;
        
        if( $request->capital['type'] == 'RESOLUTION_CHANGE_OF_STATUS_AND_ARTICLE') {
            $prefix = ($companyType == $request->capital['new_prefix']) ? null : $request->capital['new_prefix'];
            $suffix = in_array($request->capital['new_prefix'],$no_suffixes) ? null : $request->capital['new_suffix'];
            $publish_date =  $request->capital['publish_date'];
            $old_prefix = $companyType;
            $old_suffix = $company_info->postfix;
        } 
        

      $share_summery = array(
        'type' => $request->capital['type'],
        'new_prefix' =>  $prefix,
        'new_suffix' =>  $suffix,
        'old_prefix' => $old_prefix,
        'old_suffix' => $old_suffix,
        'resolution_passed_date' => $request->capital['resolution_passed_date'],
        'publish_date' => $publish_date,
        'signed_party_type' => isset($request->capital['signed_party_type']) && $request->capital['signed_party_type'] ? $request->capital['signed_party_type'] : null,
        'signed_party_id' => isset($request->capital['signed_party_id']) && intval($request->capital['signed_party_id']) ? intval($request->capital['signed_party_id']) : null,   
       
         
    );
    SpecialResolution::where('company_id', $company_id)
    ->where('request_id', $request_id)
     ->update($share_summery);
     

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }


    function generate_report($company_id, $info_array=array()){

        $generated_files = array(
              'docs' => array(),
        );
        $request_id = $this->valid_request_operation($company_id);
    
        if(!$request_id) {
            return $generated_files;
        }
      
        $file_name_key = 'form39';
        $file_name = 'FORM 39';
    
    
        $data = $info_array;
                      
        $directory = "special-resolution/$request_id";
        Storage::makeDirectory($directory);
    
        $view = 'forms.'.'form39';
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

        $request_id = $this->valid_request_operation($company_id);

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



    function resubmit(Request $request ) {

        $company_id = $request->companyId;

        $request_id = $this->valid_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $registerOfChargesRecord =  CompanyChangeRequestItem::where('company_id', $request->companyId)
            ->where('id', $request_id)
             ->first();
        if( !( isset($registerOfChargesRecord->status) && $registerOfChargesRecord->status === $this->settings('COMPANY_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid stated capital Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

      //  $update1 = ReductionStatedCapital::where('request_id', $request_id)->update(['status' => $this->settings('PROSPECTUS_OF_REG_RESUBMITTED', 'key')->id]);
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

            $request_id = $this->valid_request_operation($company_id);

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
        
            if( $size >= 1024 * 1024 * 4) {
        
                 return response()->json([
                     'message' => 'You can upload document only up to 4 MB.',
                     'status' =>false,
                     'error'  => 'yes'
                     
                     
                 ], 200);
            }
        
            $path = 'special-resolution/'.substr($company_id,0,2).'/'.$request_id;
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
            $request_id = $this->valid_request_operation($company_id);

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