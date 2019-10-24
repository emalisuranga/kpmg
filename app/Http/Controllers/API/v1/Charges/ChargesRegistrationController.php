<?php
namespace App\Http\Controllers\API\v1\Charges;
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

use App\RegisterOfCharges;
use App\RegisterOfChargesRecords;
use App\Charges;
use App\DeedItems;
use App\ChargesEntitledPersons;
use App\CourtCase;

use App\UserAttachedCompanies;

class ChargesRegistrationController extends Controller
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

        if( ! isset($company_info->id)) {

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }

        $request_id = $this->valid_request_operation($request->companyId);
        
        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);

       
        $RegisterOfChargesRecord =  Charges::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();

            
             $charge_type_group = SettingType::where('key','CHARGES_TYPES' )->first();
             $charge_types = Setting::where('setting_type_id', $charge_type_group->id)->get();

             $charge_type_selected_value = isset($this->settings($RegisterOfChargesRecord->charge_type,'id')->value) ? $this->settings($RegisterOfChargesRecord->charge_type,'id')->value : '';

            
        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

        if( !( $moduleStatus === 'CHARGES_REGISTRATION_PROCESSING' ||  $moduleStatus === 'CHARGES_REGISTRATION_RESUBMIT' ) ) {

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

       // $directorKeyId = $this->settings('DERECTOR', 'key')->id;
      //  $secKeyId = $this->settings('SECRETARY', 'key')->id;
      //  $otherUserId = $this->settings('LAWYER_BANKER_OTHER_STAKEHOLDER', 'key')->id;

        $loginUserRole = intval($loginUserInfo->stakeholder_role) ? $this->settings( $loginUserInfo->stakeholder_role, 'id')->key : '';







       /* if($loginUserInfo->id  != $company_info->created_by ) {
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


         $form_11 = Documents::where('key', 'FORM_10')->first();
         $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
     
               
         $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                         ->where('comment_type', $external_comment_type_id )
                                                         ->where('request_id', $request_id)
                                                         ->orderBy('id', 'DESC')
                                                         ->first();
        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                           ?  $external_comment_query->comments
                                           : '';
     

        $deedItems = DeedItems::where('request_id', $request_id)->get();
        $deedItemArray = array();

        if(isset( $deedItems[0]->id )){
            foreach($deedItems as $deed ) {
                $row = array(

                    'id' => $deed->id,
                    'deed_no' => $deed->deed_no,
                    'deed_date' => $deed->deed_date,
                    'bank_name' => $deed->bank_name,
                    'bank_branch' => $deed->bank_branch,
                    'amount_secured' => $deed->amount_secured,
                    'lawyers' => $deed->lawyers,
                    'description' => $deed->description,
                );
                $deedItemArray [] = $row;
            }
        }

        $entitledPersons = ChargesEntitledPersons::where('request_id', $request_id)->get();
        $personsArray = array();

        if(isset( $entitledPersons[0]->id )){
            foreach($entitledPersons as $p ) {
                $row = array(

                    'id' => $p->id,
                    'name' => $p->name,
                    'address_1' => $p->address_1,
                    'address_2' => $p->address_2,
                    'address_3' => $p->address_3,
                    'bank_name' => $p->bank_name,
                    'branch_name' => $p->branch_name,
                    'description' => $p->description,
                );
                $personsArray[] = $row;
            }
        }

        $has_penalty = $this->hasPenalty($request->companyId);

        if(!$has_penalty ) {
            $remove =  CourtCase::where('request_id', $request_id)
            ->where('company_id', $request->companyId)
            ->delete();
            $court_data_arr = array(
                'court_status' => null,
                'court_name' => null,
                'court_date' =>null,
                'court_case_no' => null,
                'court_discharged' => null,
                'court_penalty' =>  null,
                'court_period' => null,

        );
        }else {
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
        }
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
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'charge_record' => $RegisterOfChargesRecord,
                        'charge_types' => $charge_types,
                        'deedItems' => $deedItemArray,
                        'entitledPersons' => $personsArray,
                        'external_global_comment' => $external_global_comment,
                        'countries' => Country::whereNotIn('name', array('Sri Lanka'))->get(),
                        'has_penalty' => $has_penalty,
                        'court_data' => $court_data_arr,
                        'directors' =>$directors,
                        'secs' => $secs,
                        'sec_firms' =>$sec_firms,
                        'loginUserRole' => $loginUserRole,

                        'downloadDocs' => $this->generate_report($request->companyId,array(

                            'company_info' => $company_info,
                            'certificate_no' => $certificate_no,
                            'companyType' => $this->settings($company_info->type_id,'id'),
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                            'charge_record' => $RegisterOfChargesRecord,
                            'deedItems' => $deedItemArray,
                            'entitledPersons' => $personsArray,
                            'charge_type_selected_value' => $charge_type_selected_value
                           
                        )),
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                        'form10_payment' => $this->settings('PAYMENT_REGISTER_OF_CHARGES_FORM10','key')->value,
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

  private function has_request_record($company_id) {
    /*$accepted_request_statuses = array(
        $this->settings('REGISTER_OF_CHARGES_PROCESSING','key')->id,
        $this->settings('REGISTER_OF_CHARGES_RESUBMIT','key')->id
    );*/
    $accepted_request_statuses = array(
        $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id,
        $this->settings('CHARGES_REGISTRATION_REJECTED','key')->id
    );

    $record_count = Charges::where('company_id', $company_id)
                              ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count === 1 ) {
        $record = Charges::where('company_id', $company_id)
        ->whereNotIn('status', $accepted_request_statuses )
         ->first();

        return $record->request_id;
    } else {
        return false;
    }
}

  private function valid_request_operation($company_id){

    /*$accepted_request_statuses = array(
        $this->settings('REGISTER_OF_CHARGES_PROCESSING','key')->id,
        $this->settings('REGISTER_OF_CHARGES_RESUBMIT','key')->id
    );*/
    $accepted_request_statuses = array(
        $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id,
        $this->settings('CHARGES_REGISTRATION_REJECTED','key')->id
    );
    $request_type =  $this->settings('CHARGES_REGISTRATION','key')->id;

    $exist_request_id = $this->has_request_record($company_id);

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
            $user = $this->getAuthUser();
            
            $company_info = Company::where('id', $company_id)->first();
            $year = date('Y',time());
            
            $request = new CompanyChangeRequestItem;
            $request->company_id = $company_id;
            $request->request_type = $request_type;
            $request->status = $this->settings('CHARGES_REGISTRATION_PROCESSING','key')->id;
            $request->request_by = $user->userid;
            $request->save();
            $request_id =  $request->id;

            $record = new Charges;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('CHARGES_REGISTRATION_PROCESSING','key')->id;
            $record->save();
            $record_id =  $record->id;

            if($record_id && $request_id ) {
                return $request_id;
            }else{
                return false;
            }

    }
    
}

  function generate_report($company_id, $info_array=array()){

    $generated_files = array(
          'docs' => array(),
    );
    $request_id = $this->valid_request_operation($company_id);

    if(!$request_id) {
        return $generated_files;
    }
  
    $file_name_key = 'form10';
    $file_name = 'FORM 10';


    $data = $info_array;
                  
    $directory = "charges/registration/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form10';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;

    return $generated_files;
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
        
        $request_type =  $this->settings('CHARGES_REGISTRATION','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_10 = Documents::where('key', 'FORM_10')->first();
        $form_other_docs = Documents::where('key', 'CHARGES_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_10->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_10->id;
        $file_row['file_description'] = $form_10->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_10->id )
                                         ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'CHARGES_REGISTRATION_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        $regChargeGroup = DocumentsGroup::where('request_type', 'CHARGES_REGISTRATION')->first();
        $regChargeDocuments = Documents::where('document_group_id', $regChargeGroup->id)
                                           // ->where('key', '!=' , 'FORM_10')
                                            ->get();
        $regChargeDocumentsCount = Documents::where('document_group_id', $regChargeGroup->id)
                                               // ->where('key', '!=' , 'FORM_10')
                                                ->count();

        

        if($regChargeDocumentsCount){
            foreach($regChargeDocuments as $other_doc ) {


                if($form_10->id === $other_doc->id) {
                    continue;
                }
                if($form_other_docs->id === $other_doc->id ) {
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
                if($request->status == 'CHARGES_REGISTRATION_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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



        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
        $generated_files['regChargeDocumentsCount'] = $regChargeDocumentsCount;
        $generated_files['regChargeGroup'] = $regChargeGroup;
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
        $request_id = $this->valid_request_operation($company_id);

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'CHARGES_OTHER_DOCUMENTS')->first();
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
            if($company_status == 'CHARGES_REGISTRATION_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


    function submitDeedItems(Request $request ) {
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

        if(count($request->items['items'])){
            foreach($request->items['items'] as $rec ){

                if(isset($rec['id']) && $rec['id'] ){
                    $itemRec = DeedItems::find($rec['id']);
                }else{
                    $itemRec = new DeedItems;
                }

                $itemRec->request_id = $request_id;
                $itemRec->deed_no = $rec['deed_no'];
                $itemRec->deed_date = $rec['deed_date'];
                $itemRec->bank_name = $rec['bank_name'];
                $itemRec->bank_branch = $rec['bank_branch'];
                $itemRec->amount_secured = $rec['amount_secured'];
                $itemRec->lawyers = $rec['lawyers'];
                $itemRec->description = $rec['description'];
                $itemRec->save();

            }
        }
        return response()->json([
            'message' => 'Successfully updated the items',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => null,
          ], 200);

         exit();
    }

    function removeDeedItem(Request $request) {
        $company_id = $request->companyId;
        $record_id = $request->record_id;
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

       $remove =  DeedItems::where('request_id', $request_id)
                 ->where('id', $record_id)
                 ->delete();
       if($remove) {
        return response()->json([
            'message' => 'Successfully removed the deed item',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => null,
          ], 200);

       
       }else {
        return response()->json([
            'message' => 'Failed removing the deed item',
            'status' =>false,
            'request_id'   => $request_id,
            'change_id'    => null,
          ], 200);
       }
    }



    function submitEntitledPersons(Request $request ) {
        $company_id = $request->companyId;
        $request_id = $this->valid_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Successfully updated the persons',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => null,
              ], 200);
    
             exit();

        }

        if(count($request->persons['items'])){
            foreach($request->persons['items'] as $rec ){

                if(isset($rec['id']) && $rec['id'] ){
                    $itemRec = ChargesEntitledPersons::find($rec['id']);
                }else{
                    $itemRec = new ChargesEntitledPersons;
                }

                $itemRec->request_id = $request_id;
                $itemRec->name = $rec['name'];
                $itemRec->address_1 = $rec['address_1'];
                $itemRec->address_2 = $rec['address_2'];
                $itemRec->address_3 = $rec['address_3'];
                $itemRec->bank_name = $rec['bank_name'];
                $itemRec->branch_name = $rec['branch_name'];
                $itemRec->description = $rec['description'];
                $itemRec->save();

            }
        }
        return response()->json([
            'message' => 'Successfully updated the persons',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => null,
          ], 200);

         exit();

    }

    function removeEntitledPersonItem(Request $request) {
        $company_id = $request->companyId;
        $record_id = $request->record_id;
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

       $remove =  ChargesEntitledPersons::where('request_id', $request_id)
                 ->where('id', $record_id)
                 ->delete();
       if($remove) {
        return response()->json([
            'message' => 'Successfully removed the person',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => null,
          ], 200);

       
       }else {
        return response()->json([
            'message' => 'Failed removing the person',
            'status' =>false,
            'request_id'   => $request_id,
            'change_id'    => null,
          ], 200);
       }
    }

    private function hasPenalty($company_id) {
        $request_id = $this->valid_request_operation($company_id);

        $RegisterOfChargesRecord =  Charges::where('company_id', $company_id)
            ->where('request_id', $request_id)
             ->first();
        

        $charge_date_timestamp = strtotime($RegisterOfChargesRecord->charge_date);
        $today = time();

 
        if($RegisterOfChargesRecord->excecuted_in_srilanka === 'Yes'){
           $submit_within  = intval( $this->settings('DAYS_SUBMIT_FORM10_WHEN_EXECUTED_IN_SRILANKA', 'key')->value );
            $date_gap = intval( ($today - $charge_date_timestamp) / (60*60*24));

            return ($date_gap > $submit_within );

        }

        if($RegisterOfChargesRecord->excecuted_in_srilanka === 'No'){
            $submit_within  = intval( $this->settings('DAYS_SUBMIT_FORM10_WHEN_EXECUTED_NOT_IN_SRILANKA', 'key')->value );
             $date_gap = intval( ($today - $charge_date_timestamp) / (60*60*24));
 
             return ($date_gap > $submit_within );
 
         }

         return false;
        

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
        $companyType = $this->settings($company_info->type_id,'id');

        $charge_record = Charges::where('company_id', $company_id)
        ->where('request_id', $request_id)
         ->first();

         if(!isset($charge_record->id)) { 

            return response()->json([
                'message' => 'Invalid Request having empty charge record.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

       

      $share_summery = array(
        'charge_type' => isset($request->charges['charge_type']) && $request->charges['charge_type'] ? $request->charges['charge_type'] : null,
        'charge_date' => isset($request->charges['charge_date']) && $request->charges['charge_date'] ? $request->charges['charge_date'] : null,
        'excecuted_in_srilanka' => isset($request->charges['excecuted_in_srilanka']) && $request->charges['excecuted_in_srilanka'] ? $request->charges['excecuted_in_srilanka'] : null,
        'excecuted_country' => isset($request->charges['excecuted_country']) && $request->charges['excecuted_country'] && $request->charges['excecuted_in_srilanka'] == 'No' ? $request->charges['excecuted_country'] : null,
        'short_perticular_description' => isset($request->charges['short_perticular_description']) && $request->charges['short_perticular_description'] ? $request->charges['short_perticular_description'] : null,
        'other_details' => isset($request->charges['other_details']) && $request->charges['other_details'] ? $request->charges['other_details'] : null,
        'signing_party_state' =>  isset($request->charges['signing_party_state']) && $request->charges['signing_party_state'] ? $request->charges['signing_party_state'] : null,
        'signing_party_state_other' =>  ($request->charges['signing_party_state'] == 'Other') && $request->charges['signing_party_state_other'] ? $request->charges['signing_party_state_other'] : null,
        'signing_party_name' =>  isset($request->charges['signing_party_name']) && $request->charges['signing_party_name'] ? $request->charges['signing_party_name'] : null,
    );
    Charges::where('company_id', $company_id)
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

        $registerOfChargesRecord =  Charges::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($registerOfChargesRecord->status) && $registerOfChargesRecord->status === $this->settings('CHARGES_REGISTRATION_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Charges registration Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = Charges::where('request_id', $request_id)->update(['status' => $this->settings('CHARGES_REGISTRATION_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('CHARGES_REGISTRATION_RESUBMITTED', 'key')->id]);

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
        
            if( $size > 1024 * 1024 * 4) {
        
                 return response()->json([
                     'message' => 'File size should be less than 4 MB.',
                     'status' =>false,
                     'error'  => 'yes'
                     
                     
                 ], 200);
            }
        
            $path = 'charges/registration/'.substr($company_id,0,2).'/'.$request_id;
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
    
        $path = 'charges/registration/other-docs/'.substr($company_id,0,2);
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
    
        $path = 'charges/registration/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'CHARGES_OTHER_DOCUMENTS')->first();


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

    function removeOtherDoc(Request $request){

        $file_token = $request->file_token;
        
    
        CompanyDocuments::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
    
        ], 200);
    }



    function checkCompanyByRegNumber(Request $request){
        $regNumber = $request->registration_no;

        $user = $this->getAuthUser();


        if($user->stakeholder_role  != $this->settings('LAWYER_BANKER_OTHER_STAKEHOLDER', 'key')->id) {
            return response()->json(['status'=> false, 'message'=> 'Invalid user role!!','company_name' => null, 'company_id' =>  null], 200);
         }
       
        if(!$regNumber) {
            return response()->json(['status'=> false, 'message'=> 'Inavlid Company Registration Number', 'company_name' => '','company_id' =>  null], 200);
        }

        $regInfo = CompanyCertificate::where('registration_no', strtoupper(trim($regNumber)) )->first();

        if(isset($regInfo->company_id)) {

            $registered_companies = $this->get_lawyer_banker_other_regietered_companies();

            $companyInfo = Company::where('id', $regInfo->company_id)->first();

            if( in_array($regInfo->company_id, $registered_companies)) {
                return response()->json(['status'=> false, 'message'=> 'You have already attached to this company', 'company_name' => null, 'company_id' =>  null], 200);
            }

            return response()->json(['status'=> true, 'message'=> 'Company successfully added', 'company_name' => $companyInfo->name,'company_id' =>  $companyInfo->id], 200);
        }else{
            return response()->json(['status'=> false, 'message'=> 'No companies found under this registration number', 'company_name' => null, 'company_id' =>  null], 200);
        }

    }

    private function get_lawyer_banker_other_regietered_companies() {
        $user = $this->getAuthUser();
        $companies = UserAttachedCompanies::where('user_id', $user->userid)->pluck('company_id')->toArray();

        return $companies;
    }

    function addCompaniesToLawyerBankerProfile(Request $request ) {

         $assignedCompanies = $request->assignedCompanies;
        
         $user = $this->getAuthUser();

         if($user->stakeholder_role  != $this->settings('LAWYER_BANKER_OTHER_STAKEHOLDER', 'key')->id) {
            return response()->json(['status'=> false, 'message'=> 'Invalid user role!!'], 200);
         }

        if( isset($assignedCompanies) && is_array($assignedCompanies ) && count($assignedCompanies)) {
            foreach($assignedCompanies as $company_id) {

                $attach  = new UserAttachedCompanies;
                $attach->user_id = $user->userid;
                $attach->company_id = $company_id;
                $attach->save();
            }
         }

         return response()->json(['status'=> true, 'message'=> 'Successfully attach companies to your profile'], 200);
    }

} // end class