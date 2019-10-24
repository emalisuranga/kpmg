<?php

namespace App\Http\Controllers\API\v1\OtherCourtOrder;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\CompanyCertificate;
use App\Documents;
use App\CompanyPostfix;
use App\Address;
use App\Setting;
USE App\SettingType;
use App\CompanyMember;
use App\CompanyFirms;
use App\DocumentsGroup;
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

use App\ProspectusRegistration;
use App\Currency;
use App\OthersCourtOrder;

class otherCourtOrderController extends Controller
{
    use _helper;

      function __construct() {
        
        $this->items_per_page = 5;
        $this->corr_items_per_page = 20;
        $this->reqStatus = '';
        $this->request_id = '';
    }

    public function loadData(Request $request){

        $companyId = $request->companyId;
        $request_id = $request->requestId;
        $this->reqStatus = $request->reqStatus;
        $this->request_id = $request_id;
        // return $this->request_id;

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

          $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
         $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

         if(!$request_id){
            //   return $request_id;
              $this->request_id = $this->valid_request_operation($request->companyId,$this->reqStatus);  
          }
        $RegisterOfChargesRecord = CompanyChangeRequestItem::where('id',$this->request_id)
                           ->where('company_id', $request->companyId)
                           ->first();
                           
        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

        if( !( $moduleStatus === 'OTHERS_COURT_ORDER_PROCESSING' ||  $moduleStatus === 'OTHERS_COURT_ORDER_RESUBMIT' ) ) {

            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }

        $external_global_comment = '';
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
     
               
         $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                         ->where('comment_type', $external_comment_type_id )
                                                         ->where('request_id', $this->request_id)
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
                         'request_id' => $this->request_id,
                         'moduleStatus' => $moduleStatus,
                         'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                         'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                        //  'additional' => $this->aditional_docs($request->companyId),
                         'othersCourt_payment' => $this->settings('PAYMENT_OTHERS_COURT_ORDER','key')->value,
                         'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                         'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                         'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                         'external_global_comment' => $external_global_comment,
                        )
                ], 200);
          
    }

    function files_for_upload_docs($company_id){

        $file_cout = 0;

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
                'file_count' => 0

        );

        $request_id = $this->request_id;

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('OTHERS_COURT_ORDER','key')->id;

        $request = CompanyChangeRequestItem::whereIn('request_type',array(  $this->settings('OTHERS_COURT_ORDER','key')->id, $this->settings('OTHERS_COURT_ORDER_LIST','key')->id))
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_prospectus = Documents::where('key', 'OTHERS_COURT_ORDER_DOC')->first();
        $form_other_docs = Documents::where('key', 'OTHERS_COURT_ORDER_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_prospectus->id;

        $has_all_uploaded_str = '';

          
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_prospectus->id )
                                        ->where('status','!=',$this->settings('DOCUMENT_DELETED','key')->id )
                                        ->orderBy('id', 'DESC')
                                        ->get();

        $generated_files['file_count'] = count($uploadedDoc);

        foreach($uploadedDoc as $docs ) {

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $docs->file_description;
        $file_row['file_type'] = '';
        $file_row['multiple_id'] = $docs->multiple_id;
        $file_row['file_description'] = $form_prospectus->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_file'] = $form_prospectus->id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;       
       
        $uploadeDocStatus = @$docs->status;
        if($request->status == 'OTHERS_COURT_ORDER_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = CompanyDocumentStatus::where('company_document_id', $docs->id )
                                                            ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                            ->where('comment_type', $external_comment_type_id )
                                                            ->first();
                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

        }

        $file_row['uploaded_path'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->name) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->name ? $docs->name : '';
        $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                            isset($docs->path ) &&
                                            isset($docs->name) &&
                                            $docs->file_token &&
                                            $docs->path &&
                                            ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                            $docs->name ? $docs->file_token : '';

        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                
                
        $generated_files['docs'][] = $file_row;
    }

        //other documents (those are ususally visible on requesting by the admin )
        $regChargeGroup = DocumentsGroup::where('request_type', 'OTHERS_COURT_ORDER_DOCUMENT_GROUP')->first();
        $regChargeDocuments = Documents::where('document_group_id', $regChargeGroup->id)
                                           // ->where('key', '!=' , 'FORM_22')
                                            ->get();
        $regChargeDocumentsCount = Documents::where('document_group_id', $regChargeGroup->id)
                                              //  ->where('key', '!=' , 'FORM_22')
                                                ->count();

        if($regChargeDocumentsCount){
            foreach($regChargeDocuments as $other_doc ) {

                if($form_prospectus->id === $other_doc->id) {
                    continue;
                }
                if($form_other_docs->id === $other_doc->id ) {
                    continue;
                }


                $is_document_requested =  CompanyDocuments::where('company_id', $company_id)
                ->where('request_id',$request_id)
                ->where('document_id', $other_doc->id )
                ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id , $this->settings('DOCUMENT_APPROVED','key')->id ) )
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
                if($request->status == 'OTHERS_COURT_ORDER_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                 if(isset($uploadeDocStatus) && $uploadeDocStatus ){
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

// return $has_all_uploaded_str;

          $generated_files['uploadedAll'] =   ($has_all_uploaded_str != '' || strpos($has_all_uploaded_str, '0') !== false)  ;
    
       // $generated_files['uploadedAll'] = !(count($generated_files['docs']) == 0);   
        return $generated_files;
    
    }

    function upload(Request $request){
        // return $request;


            $file_name =  uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $company_id = $request->company_id;
            $requestNumber =  $request->requestNumber;
            $file_description = $request->fileDescription;


            // return $requestNumber;

            if(!$requestNumber ){
        
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
        
            // if( $size >= 1024 * 1024 * 10) {
        
            //      return response()->json([
            //          'message' => 'You can upload document only up to 10 MB.',
            //          'status' =>false,
            //          'error'  => 'yes'
                     
                     
            //      ], 200);
            // }
            $path = 'company/'.$company_id;
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
            $token = md5(uniqid());
        
    
            $get_query = CompanyDocuments::query();
            $get_query->where('company_id', $company_id );
            $get_query->where('request_id', $requestNumber);
            $get_query->where('document_id',$file_type_id);
            $old_doc_info = $get_query->first();

            // return $old_doc_info;
        
            // $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
              
            $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
            $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;

            $form_prospectus = Documents::where('key', 'OTHERS_COURT_ORDER_DOC')->first();
            $form_other_docs = Documents::where('key', 'OTHERS_COURT_ORDER_OTHER_DOCUMENTS')->first();

    
            // return $old_doc_info;
            if($old_doc_info){
        if(!($old_doc_info->document_id == $form_prospectus->id || $old_doc_info->document_id == $form_other_docs->id)){

            $query = CompanyDocuments::query();
            $query->where('company_id', $company_id );
            $query->where('request_id', $requestNumber);
            $query->where('document_id',$file_type_id);
            $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
            $query->delete();
        }
    }
        
               $doc = new CompanyDocuments;
               $doc->document_id = $file_type_id;
               $doc->path = $path;
               $doc->company_id = $company_id;
               $doc->request_id = $requestNumber;
               $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
               $doc->file_token = $token;
               $doc->name = $real_file_name;
               $doc->multiple_id = mt_rand(1,1555400976);
               $doc->file_description = $file_description;
               $doc->save();
               $new_doc_id = $doc->id;

               return response()->json([
                'message' => 'File uploaded successfully.',
                'status' =>true,
                'name' =>basename($path),
                'error'  => 'no',
                'test' => $old_doc_info
            ], 200);
        
 
    }

    private function valid_request_operation($company_id, $reqStatus ){

            $accepted_request_statuses = array(
                $this->settings('OTHERS_COURT_ORDER_REJECTED','key')->id,
                $this->settings('OTHERS_COURT_ORDER_APPROVED','key')->id
            );
            $request_type =  $this->settings('OTHERS_COURT_ORDER','key')->id;

            if($reqStatus == 'individual'){
                $exist_request_id = $this->has_request_record($company_id);
                
                if($exist_request_id){
                    $request_count = CompanyChangeRequestItem::where('request_type',$this->settings('OTHERS_COURT_ORDER_LIST','key')->id)
                                ->where('company_id', $company_id)
                                ->where('id', $exist_request_id)
                                ->whereNotIn('status', $accepted_request_statuses )
                                ->count();
                               
                if($request_count !== 1) { // request not in processing or  resubmit stage
                    return false;
                 } else {
                    return $exist_request_id;
                 }
                }else {
                    $user = $this->getAuthUser();

                    $company_info = Company::where('id', $company_id)->first();
                    $year = date('Y',time());

                    $request = new CompanyChangeRequestItem;
                    $request->company_id = $company_id;
                    $request->request_type = $this->settings('OTHERS_COURT_ORDER_LIST','key')->id;
                    $request->status = $this->settings('OTHERS_COURT_ORDER_PROCESSING','key')->id;
                    $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
                    $request->save();

                    $courtOrder = new OthersCourtOrder;
                    $courtOrder->request_id = $request->id;
                    $courtOrder->company_id = $company_id;
                    $courtOrder->user_id = $user->id;;
                    $courtOrder->status = $reqStatus;
                    $courtOrder->save();

                    return $request->id;
                }
            } else {
                    if(!$this->request_id){
                        $user = $this->getAuthUser();

                        $company_info = Company::where('id', $company_id)->first();
                        $year = date('Y',time());

                        $request = new CompanyChangeRequestItem;
                        $request->company_id = $company_id;
                        $request->request_type = $request_type;
                        $request->status = $this->settings('OTHERS_COURT_ORDER_PROCESSING','key')->id;
                        $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
                        $request->save();

                        $courtOrder = new OthersCourtOrder;
                        $courtOrder->request_id = $request->id;
                        $courtOrder->company_id = $company_id;
                        $courtOrder->user_id = $user->id;;
                        $courtOrder->status = $reqStatus;
                        $courtOrder->save();

                        return $request->id;
                    }
            }

            // if($exist_request_id) {
               
            //     $request_count = CompanyChangeRequestItem::where('request_type',$request_type)
            //                     ->where('company_id', $company_id)
            //                     ->where('id', $exist_request_id)
            //                     ->whereNotIn('status', $accepted_request_statuses )
            //                     ->count();
            //     if($request_count !== 1) { // request not in processing or  resubmit stage
            //         return false;
            //     } else {
            //         return $exist_request_id;
            //     }
                
            // } else {
            
    }

    private function has_request_record($company_id) {
   
        $accepted_request_statuses = array(
            $this->settings('OTHERS_COURT_ORDER_REJECTED','key')->id,
            $this->settings('OTHERS_COURT_ORDER_APPROVED','key')->id
        );
        $request_type =  $this->settings('OTHERS_COURT_ORDER_LIST','key')->id;
        $record_count = CompanyChangeRequestItem::where('company_id', $company_id)
                                ->where('request_type',$request_type)
                                ->whereNotIn('status', $accepted_request_statuses )
                                ->count();
       
        if( $record_count === 1 ) {
            $record = CompanyChangeRequestItem::where('company_id', $company_id)
             ->where('request_type',$request_type)
            ->whereNotIn('status', $accepted_request_statuses )
            ->first();
            $court_order = OthersCourtOrder:: where('request_id', $record->id)->first();
            if($court_order->status == 'individual'){
                return $record->id;
            }else {
                return false;
            }


        } else {
            return false;
            // return $record_count;
        }
    }

    function removeDoc(Request $request){

            $company_id = $request->companyId;
            $request_id =$this->request_id;

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

    function uploadOtherDocs(Request $request){
        
        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $company_id = $request->company_id;
        $file_description = $request->fileDescription;
        // return $this->request_id;
        
        
        $request_id = $request->requestNumber;
  
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
    
        $path = 'company/'.$company_id;
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
        $request_id = $this->request_id;

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'OTHERS_COURT_ORDER_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

       

        $other_docs = CompanyDocuments::where('company_id', $company_id)
                                        ->where('document_id', $form_other_docs->id )
                                        ->where('request_id', $request_id)
                                        ->orderBy('id', 'DESC')
                                        ->get();
        foreach($other_docs as $docs ) {

            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $docs->name;
            $file_row['file_type'] = '';
            $file_row['multiple_id'] = $docs->multiple_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
            $file_row['doc_status2'] = $docs->status;
                    
            $uploadeDocStatus = @$docs->status;
            if($company_status == 'OTHERS_COURT_ORDER_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    
            $file_row['uploaded_path'] =  isset($docs->file_token)  &&
                                                isset($docs->path ) &&
                                                isset($docs->name) &&
                                                $docs->file_token &&
                                                $docs->path &&
                                                ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                $docs->name ? $docs->name : '';
            $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                                isset($docs->path ) &&
                                                isset($docs->name) &&
                                                $docs->file_token &&
                                                $docs->path &&
                                                ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                $docs->name ? $docs->file_token : '';
    
            $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                    
                    
            $generated_files['docs'][] = $file_row;

        }
 

        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
    
        return $generated_files;
    
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

    function resubmit(Request $request ) {

        $company_id = $request->companyId;

        $request_id = $this->request_id;

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

            exit();

        }

        // $update1 = ProspectusRegistration::where('request_id', $request_id)->update(['status' => $this->settings('PROSPECTUS_OF_REG_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('OTHERS_COURT_ORDER_RESUBMITTED', 'key')->id]);

        if($update2) {
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

    function uploadOtherResubmittedDocs(Request $request){
        
        $company_id = $request->company_id;
        $multiple_id = $request->multiple_id;
        $request_id = $this->request_id;

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
    
        $path = 'workflows/others-court-order/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'OTHERS_COURT_ORDER_OTHER_DOCUMENTS')->first();


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

    function uploadResubmittedDocs(Request $request){
        
        $company_id = $request->company_id;
        $file_name = $request->fileName;
        $multiple_id = $request->multiple_id;
        $request_id = $this->request_id;

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
    
        $path = 'workflows/others-court-order/main-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'OTHERS_COURT_ORDER_DOC')->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
                'name' => $file_name
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

    function getOthersCourtOrderList(Request $request ){
        
         $query = null;
         $user = $this->getAuthUser();
         $request_type =  $this->settings('OTHERS_COURT_ORDER','key')->id;
         $approved_statuses = array( 
            $this->settings('OTHERS_COURT_ORDER_PROCESSING','key')->id,
            $this->settings('OTHERS_COURT_ORDER_RESUBMIT','key')->id,
         );
       


        $courtOrder = OthersCourtOrder::leftJoin('company_change_requests', 'company_change_requests.id', '=', 'otheres_court_order.request_id')
         ->leftJoin('companies', 'otheres_court_order.company_id', '=', 'companies.id')
         ->where('otheres_court_order.user_id',$user->id )
         ->where('otheres_court_order.status', '=' , 'multiple' )
         ->orderby('company_change_requests.id', 'DESC')
         ->select(
             'otheres_court_order.user_id as user',
             'companies.id as company_id',
            'company_change_requests.id as request_id',
             'companies.name as company_name',
            'company_change_requests.status as status',
            'company_change_requests.updated_at as updated_at'
         )
        ->get();

         $courtOrderList = array();

         if(isset($courtOrder[0]->company_id)){
             foreach($courtOrder as $corr) {
                $row = array(
                    'company_id' => $corr->company_id,
                    'company_name' => $corr->company_name,
                    'reg_no' => $corr->reg_no,
                    'request_id' => $corr->request_id,
                    'status' => $this->settings($corr->status ,'id')->key,
                    'date' => date('Y-m-d H:i:s', strtotime($corr->updated_at)),
                );
        
                $courtOrderList[] = $row;

             }
         }

         return response()->json([
            'message'       => "Successfully listed correspondences.",
            'courtOrderList'    => $courtOrderList,
            'status'        => true,
            ], 200);

            

    }

    function getCompnanies(Request $request ){


        $name_part = trim( $request->namePart);
        $registration_no = trim( $request->registration_no);
        $page = intval($request->page);
        $offset = $page*$this->items_per_page;
        $compStatus = '';

        $approved_statuses = array( 
            $this->settings('COMPANY_STATUS_APPROVED','key')->id,
            $this->settings('COMPANY_FOREIGN_STATUS_APPROVED','key')->id,
            $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id
         );
        
        $query = null;
        $query = Company::query();
        $query->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id');
        if ($name_part) {
          $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');
        }

        if ($registration_no) {
          $query->where('company_certificate.registration_no', 'ilike', '%' . strtoupper($registration_no ). '%');
         }
      
        $query->whereIn('companies.status', $approved_statuses )
        ->orderby('companies.id', 'DESC')
        ->select(
            'companies.id as id',
            'companies.name as name',
            'company_certificate.registration_no as registration_no');
        
        //  $companies = $query->get();
        $companies = $query->limit($this->items_per_page)->offset($offset)->get();

        $result_count = $query->count();

        $companyList = array();
        $user = $this->getAuthUser();
        if(isset($companies[0]->id)){
            foreach($companies as $c ) {


               
                // $request_type =  $this->settings('OTHERS_COURT_ORDER_PROCESSING','key')->id;
                $status = OthersCourtOrder::leftJoin('company_change_requests', 'company_change_requests.id', '=', 'otheres_court_order.request_id')
                                    ->where('otheres_court_order.company_id', $c->id)
                                    // ->where('company_change_requests.status', $request_type)
                                      ->whereIn('company_change_requests.status', array($this->settings('OTHERS_COURT_ORDER_PROCESSING','key')->id, $this->settings('OTHERS_COURT_ORDER_RESUBMIT','key')->id))
                                       ->where('otheres_court_order.user_id', $user->userid)
                                       ->select('company_change_requests.status as status','company_change_requests.id as requstId')
                                       ->first();

                if($status){
                    $compStatus = $this->settings('id',$status->status)->key;
                }
                
                $row = array();
                $row['id'] = $c->id;
                $row['name'] = $c->name;
                // $row['name_si'] = $c->name_si;
                // $row['name_ta'] = $c->name_ta;
                $row['compStatus'] = $compStatus;
                $row['registration_no'] = $c->registration_no;
                if($status){
                    $row['reqId'] = $status->requstId;
                }


                $companyList[] = $row;

            }
        }

        return response()->json([
            'message'       => "Successfully listed companies.",
            'companyList'    => $companyList,
            'status'        => true,
            'count'         => $result_count,
            'total_pages'   => $this->getCompanyPaginatePages($name_part,$registration_no),
            'current_page'  => ($page+1)
            ], 200);

    }

    private function getCompanyPaginatePages($name_part,$registration_no){
        
        $approved_statuses = array( 
            $this->settings('COMPANY_STATUS_APPROVED','key')->id,
            $this->settings('COMPANY_FOREIGN_STATUS_APPROVED','key')->id,
           // $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id
         );
        $query = null;
        $query = Company::query();
        $query->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id');
       // $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');

        if ($name_part) {
            $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');
        }
 
        if ($registration_no) {
             $query->where('company_certificate.registration_no', 'ilike', '%' . strtoupper($registration_no ). '%');
        }

        $query->whereIn('companies.status', $approved_statuses );
        $query->orderby('companies.id', 'DESC');
      
        $result_count = $query->count();

       return  ($result_count % $this->items_per_page == 0  )
                        ? $result_count / $this->items_per_page
                        : intval($result_count / $this->items_per_page) + 1;

    }

    function removeList(Request $request){
        if(!$request->requestId & !$request->companyId){

            return response()->json([
                'message' => 'missing data',
                'status' =>false,
            ], 200);
        }

        OthersCourtOrder::where('id', $request->requestId)
                        ->where('company_id', $request->companyId)
                        ->delete();

        return response()->json([
                'message' => 'List removed successfully.',
                'status' =>true,
            ], 200);
    }

    
}


