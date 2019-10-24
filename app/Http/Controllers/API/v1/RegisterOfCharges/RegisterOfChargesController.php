<?php
namespace App\Http\Controllers\API\v1\RegisterOfCharges;
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

use App\RegisterOfCharges;
use App\RegisterOfChargesRecords;

class RegisterOfChargesController extends Controller
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

       
        $RegisterOfChargesRecord =  RegisterOfCharges::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();

            
        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

        if( !( $moduleStatus === 'REGISTER_OF_CHARGES_PROCESSING' ||  $moduleStatus === 'REGISTER_OF_CHARGES_RESUBMIT' ) ) {

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

        

        /****** record list *****/
        $record_count =0;

        $record_list_count = RegisterOfChargesRecords::where('company_id',$request->companyId)
                                            ->where('status',$this->settings('REGISTER_OF_CHARGES','key')->id)
                                            ->where('charge_record_id',$RegisterOfChargesRecord->id)
                                            ->count();
        $record_list = RegisterOfChargesRecords::where('company_id',$request->companyId)
                                            ->where('status',$this->settings('REGISTER_OF_CHARGES','key')->id)
                                            ->where('charge_record_id',$RegisterOfChargesRecord->id)
                                            ->get();

                                           

                                           
        

        $records = array();
        if($record_list_count) {
            foreach($record_list as $sr){

                $record_count++;                     
        
                $rec = array(
                'id' => $sr->id,
                'company_id' =>  $sr->company_id,
                'total_amount_secured' => $sr->total_amount_secured,
                'satisfaction_amount' => $sr->satisfaction_amount,
                'date_of_issue_series' =>  $sr->date_of_issue_series,
                'amount_commisison_allowance' =>  $sr->amount_commisison_allowance,
                'amount_issue_series' =>  $sr->amount_issue_series,
                'date_of_resolutions' =>  $sr->date_of_resolutions,
                'manager_date_of_ceasing' =>  $sr->manager_date_of_ceasing,
                'manager_name' =>  $sr->manager_name,
                'manager_date_of_appointment' =>  $sr->manager_date_of_appointment,
                'date_of_deed' =>  $sr->date_of_deed,
                'description_of_property' =>  $sr->description_of_property,
                'name_of_trustee' =>  $sr->name_of_trustee
                );
            

                $records[] = $rec;
            }

        }

        //print_r($request_id);
       // die();


        $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

        $postfix_values = $this->getPostfixValues($company_info->postfix);

        $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
         $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

         $external_global_comment = '';


         $form_11 = Documents::where('key', 'FORM_11')->first();
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
                        'certificate_no' => $certificate_no,
                        'request_id' => $request_id,
                        'processStatus' => $this->settings($company_info->status,'id')->key,
                        'moduleStatus' => $moduleStatus,
                        'companyType'    =>$companyType,
                        'loginUser'     => $userPeople,
                        'loginUserAddress'=> $userAddress,
                        'register_charges' => $records,
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'RegisterOfChargesRecord' => $RegisterOfChargesRecord,
                        'external_global_comment' => $external_global_comment,

                        'downloadDocs' => $this->generate_report($request->companyId,array(

                            'company_info' => $company_info,
                            'certificate_no' => $certificate_no,
                            'companyType' => $this->settings($company_info->type_id,'id'),
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'register_charges' => $records,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                            'RegisterOfChargesRecord' => $RegisterOfChargesRecord,
                           
                        )),
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                        'form11_payment' => $this->settings('PAYMENT_REGISTER_OF_CHARGES_FORM11','key')->value,
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
        $this->settings('REGISTER_OF_CHARGES_APPROVED','key')->id,
        $this->settings('REGISTER_OF_CHARGES_REJECTED','key')->id
    );
   
    $record_count = RegisterOfCharges::where('company_id', $company_id)
                              ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count === 1 ) {
        $record = RegisterOfCharges::where('company_id', $company_id)
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
        $this->settings('REGISTER_OF_CHARGES_APPROVED','key')->id,
        $this->settings('REGISTER_OF_CHARGES_REJECTED','key')->id
    );
    $request_type =  $this->settings('REGISTER_OF_CHARGES','key')->id;

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
            $company_info = Company::where('id', $company_id)->first();
            $year = date('Y',time());

            $request = new CompanyChangeRequestItem;
            $request->company_id = $company_id;
            $request->request_type = $request_type;
            $request->status = $this->settings('REGISTER_OF_CHARGES_PROCESSING','key')->id;
            $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
            $request->save();
            $request_id =  $request->id;

            $record = new RegisterOfCharges;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('REGISTER_OF_CHARGES_PROCESSING','key')->id;
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
  
    $file_name_key = 'form11';
    $file_name = 'FORM 11';


    $data = $info_array;
                  
    $directory = "register-of-charges/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form11';
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
        
        $request_type =  $this->settings('REGISTER_OF_CHARGES','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_11 = Documents::where('key', 'FORM_11')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_11->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_11->id;
        $file_row['file_description'] = $form_11->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_11->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        if($request->status == 'REGISTER_OF_CHARGES_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        $regChargeGroup = DocumentsGroup::where('request_type', 'REGISTER_OF_CHARGES')->first();
        $regChargeDocuments = Documents::where('document_group_id', $regChargeGroup->id)
                                            // ->where('key', '!=' , 'FORM_11')
                                            ->get();
        $regChargeDocumentsCount = Documents::where('document_group_id', $regChargeGroup->id)
                                               // ->where('key', '!=' , 'FORM_11')
                                                ->count();

        if($regChargeDocumentsCount){
            foreach($regChargeDocuments as $other_doc ) {

                if($form_11->id === $other_doc->id) {
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
                if($request->status == 'REGISTER_OF_CHARGES_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    
        return $generated_files;
    
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

        $charge_record = RegisterOfCharges::where('company_id', $company_id)
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


        $call_count = RegisterOfChargesRecords::where('company_id', $company_id)
                                                ->where('status',$this->settings('REGISTER_OF_CHARGES','key')->id)
                                                ->where('charge_record_id', $charge_record->id)
                                                ->count();
        if($call_count){
            $calls = RegisterOfChargesRecords::where('company_id', $company_id)
                                                ->where('status',$this->settings('REGISTER_OF_CHARGES','key')->id)
                                                ->where('charge_record_id', $charge_record->id)
                                                ->get();
            foreach($calls as $d ) {
                
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('REGISTER_OF_CHARGES_TABLE','key')->id)
                 ->delete();
                 RegisterOfChargesRecords::where('id', $d->id)
                             ->where('status', $this->settings('REGISTER_OF_CHARGES','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

      

        //loop through add director list
        foreach($request->register_charges['record'] as $sr ){

            $newSr = new RegisterOfChargesRecords;
            $newSr->company_id = $company_id;
            $newSr->total_amount_secured = floatval( $sr['total_amount_secured'] );
            $newSr->satisfaction_amount = floatval( $sr['satisfaction_amount'] );
            $newSr->date_of_issue_series = $sr['date_of_issue_series'];
            $newSr->amount_commisison_allowance =  floatval($sr['amount_commisison_allowance']);
            $newSr->amount_issue_series =  floatval($sr['amount_issue_series']);
            $newSr->date_of_resolutions =  $sr['date_of_resolutions'];
            $newSr->manager_date_of_ceasing =  $sr['manager_date_of_ceasing'];
            $newSr->manager_name =  $sr['manager_name'];
            $newSr->manager_date_of_appointment =  $sr['manager_date_of_appointment'];
            $newSr->date_of_deed =  $sr['date_of_deed'];
            $newSr->description_of_property =  $sr['description_of_property'];
            $newSr->name_of_trustee =  $sr['name_of_trustee'];
            $newSr->status =  $this->settings('REGISTER_OF_CHARGES','key')->id;
            $newSr->charge_record_id =  $charge_record->id;
            $newSr->save();
            $new_sr_id = $newSr->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_sr_id;
            $change->item_table_type = $this->settings('REGISTER_OF_CHARGES_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

      }

      $share_summery = array(
        'date_of_registration' => $request->date_of_registration ? $request->date_of_registration : null,
        'document_serial_no' => $request->document_serial_no ? $request->document_serial_no : null,
        'date_of_creation_of_charge' => $request->date_of_creation_of_charge ? $request->date_of_creation_of_charge : null,
        'date_of_acquisition_of_property' => $request->date_of_acquisition_of_property ? $request->date_of_acquisition_of_property : null,
        'amount_secured_by_charge' => floatval($request->amount_secured_by_charge) ? floatval($request->amount_secured_by_charge) : null,
        'short_particulars_of_charge' => $request->short_particulars_of_charge ? $request->short_particulars_of_charge : null,
        'person_name_entitled' => $request->person_name_entitled ? $request->person_name_entitled : null,
    );
    RegisterOfCharges::where('company_id', $company_id)
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

        $registerOfChargesRecord =  RegisterOfCharges::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($registerOfChargesRecord->status) && $registerOfChargesRecord->status === $this->settings('REGISTER_OF_CHARGES_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Call on Shares Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = RegisterOfCharges::where('request_id', $request_id)->update(['status' => $this->settings('REGISTER_OF_CHARGES_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('REGISTER_OF_CHARGES_RESUBMITTED', 'key')->id]);

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
        
            $path = 'register-of-charges/'.substr($company_id,0,2).'/'.$request_id;
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