<?php

namespace App\Http\Controllers\API\v1\StatementOfAffairs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use App\Company;
use App\CompanyCertificate;
use App\CompanyChangeRequestItem;
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
use PDF;
use View;
use App\CompanyItemChange;

use App\ProspectusRegistration;
use App\Currency;

class StatementOfAffairsController extends Controller
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

        $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
        $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

        $request_id = $this->has_request_record($request->companyId);
         

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);

        $moduleStatus = '';
        $external_global_comment = '';

        if($request_id){

            $RegisterOfChargesRecord = CompanyChangeRequestItem::where('id',$request_id)
                           ->where('company_id', $request->companyId)
                           ->first();
            $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;
           

            if( !( $moduleStatus === 'STATEMENT_OF_AFFAIRS_PROCESSING' ||  $moduleStatus === 'STATEMENT_OF_AFFAIRS_RESUBMIT' ) ) {

            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

            }

            $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
     
               
            $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                         ->where('comment_type', $external_comment_type_id )
                                                         ->where('request_id', $request_id)
                                                         ->orderBy('id', 'DESC')
                                                         ->first();
            $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                           ?  $external_comment_query->comments
                                           : '';
        }
       
      $path = asset('other/FORM-25.doc');

            return response()->json([
                    'message' => 'Data is successfully loaded.',
                    'status' =>true,
                    'data'   => array(
                         'createrValid' => true,  
                         'companyInfo'  => $company_info,
                         'certificate_no' => $certificate_no,
                         'request_id' => $request_id,
                         'moduleStatus' => $moduleStatus,
                         'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                         'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                         'othersCourt_payment' => $this->settings('PAYMENT_STATEMENT_OF_AFFAIRS','key')->value,
                         'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                         'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                         'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                         'external_global_comment' => $external_global_comment,
                         'path' => $path
                         
                        )
                ], 200);
          
    }

    private function valid_request_operation($company_id){

            $accepted_request_statuses = array(
                $this->settings('STATEMENT_OF_AFFAIRS_APPROVED','key')->id,
                $this->settings('STATEMENT_OF_AFFAIRS_REJECTED','key')->id
            );
            $request_type =  $this->settings('STATEMENT_OF_AFFAIRS','key')->id;

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
                    $request->status = $this->settings('STATEMENT_OF_AFFAIRS_PROCESSING','key')->id;
                    $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
                    $request->save();

                    return $request->id;
        }
    }

    private function has_request_record($company_id) {
   
        $accepted_request_statuses = array(
                $this->settings('STATEMENT_OF_AFFAIRS_APPROVED','key')->id,
                $this->settings('STATEMENT_OF_AFFAIRS_REJECTED','key')->id
        );
        $request_type =  $this->settings('STATEMENT_OF_AFFAIRS','key')->id;
        $record_count = CompanyChangeRequestItem::where('company_id', $company_id)
                                ->where('request_type',$request_type)
                                ->whereNotIn('status', $accepted_request_statuses )
                                ->count();
       
        if( $record_count === 1 ) {
            $record = CompanyChangeRequestItem::where('company_id', $company_id)
             ->where('request_type',$request_type)
            ->whereNotIn('status', $accepted_request_statuses )
            ->first();

            return $record->id;
        } else {
            return false;
        }
    }

    public function generate_App_pdf(Request $request){

        $comId = $request->companyId;
        $request_id = $this->valid_request_operation($comId);
        

        if(!$request_id){

            return response()->json([
                'message' => 'We can \'t find a request_id.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        // $comId = $request->input('companyId');
        $company = Company::where('id',$comId)->first();
        $company1 = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                    ->where('companies.id',$comId)
                    ->get(['companies.id','companies.name','companies.address_id','company_certificate.registration_no as registration_no']);

        $regNo =   $company1[0]['registration_no'];    

        $fieldset = array(
        'comName' => $company->name,
        'comPostfix' => $company->postfix,
        'comReg' => $company->registration_no,
        'regNo' => $regNo,
        );
        $path = asset('other/constitution.pdf');
        return $path;

        // $pdf = PDF::loadView('statement-of-affairs/helloWorld.docx');
        // $pdf->stream('form-25.pdf');

        // return response()->download(storage_path('TestWordFile.docx'));


     }

     function upload(Request $request){


            $file_name =  uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $company_id = $request->company_id;
            $requestNumber =  $request->requestNumber;

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
            $path = 'company/'.$company_id;
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

    function files_for_upload_docs($company_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
        );

        $request_id = $this->valid_request_operation($company_id);

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('STATEMENT_OF_AFFAIRS','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_prospectus = Documents::where('key', 'FORM_25')->first();
        $form_other_docs = Documents::where('key', 'STATEMENT_OF_AFFAIRS_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_prospectus->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_prospectus->id;
        $file_row['file_description'] = $form_prospectus->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_file'] = $form_prospectus->id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;

        
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_prospectus->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
       
        $uploadeDocStatus = @$uploadedDoc->status;
        if($request->status == 'STATEMENT_OF_AFFAIRS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

        }else{
         //    $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                         //   ->where('status', $uploadeDocStatus )
                                                         //   ->where('comment_type', $external_comment_type_id )
                                                        //    ->first();
                $file_row['doc_comment'] = '';
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
        $regChargeGroup = DocumentsGroup::where('request_type', 'STATEMENT_OF_AFFAIRS_DOCUMENT_GROUP')->first();
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
                ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id , $this->settings('DOCUMENT_APPROVED','key')->id) )
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
                if($request->status == 'STATEMENT_OF_AFFAIRS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                 if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                if($uploadeDocStatus == $this->settings('STATEMENT_OF_AFFAIRS_RESUBMIT','key')->id ) { //if doc is resubmitted

                            $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                                    ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                                    ->where('comment_type', $external_comment_type_id )
                                                                    ->first();
                                $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }else{
                     $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                                    ->where('status', $uploadeDocStatus )
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
        $form_other_docs = Documents::where('key', 'STATEMENT_OF_AFFAIRS_OTHER_DOCUMENTS')->first();
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
                    
            $uploadeDocStatus = @$docs->status;
            if($company_status == 'STATEMENT_OF_AFFAIRS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

        // $update1 = ProspectusRegistration::where('request_id', $request_id)->update(['status' => $this->settings('PROSPECTUS_OF_REG_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('STATEMENT_OF_AFFAIRS_RESUBMITTED', 'key')->id]);

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
    
        $path = 'workflows/statement-of-affairs/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'STATEMENT_OF_AFFAIRS_OTHER_DOCUMENTS')->first();


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


}
