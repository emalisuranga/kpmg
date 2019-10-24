<?php

namespace App\Http\Controllers\API\v1\PriorApproval;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\CompanyCertificate;
use App\PriorApproval;
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
use App\PriorApprovalCategory;
use App\CompanyBalanceSheetDate;

class PriorApprovalController extends Controller
{
    use _helper;

    public function loadData(Request $request){

        $companyId = $request->companyId;
        $requestId = $request->requestId;

        if(!$companyId){

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $company_info = Company::where('id',$companyId)->first();

        if( ! isset($company_info->id)) {

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }

        $companyCertificate = CompanyCertificate::where('company_id', $companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
        $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

        $payment = $this->settings('PAYMENT_PRIOR_APPROVAL','key')->value;
        $category = PriorApprovalCategory::where('enabled',1)->get();
        $preproposedate = CompanyBalanceSheetDate::where('company_id',$companyId)
                            ->where('status','=',$this->settings('COMMON_STATUS_ACTIVE','key')->id)->first();

        $nextPreproposedate = '';
        $RegisterOfChargesRecord = '';
        $subject = '';
        $balnceSheet = [];
        $moduleStatus = '';
        $external_global_comment = '';

        if($requestId){
            $nextPreproposedate = CompanyBalanceSheetDate::where('company_id',$companyId)
                            ->where('request_id',$requestId)->first();

            $RegisterOfChargesRecord =  PriorApproval::where('company_id', $companyId)
            ->where('request_id', $requestId)->first();

            $subject = PriorApprovalCategory::where('id',$RegisterOfChargesRecord->category_id)->first();

            $RegisterOfCharges = CompanyChangeRequestItem::where('id',$requestId)
                           ->where('company_id', $companyId)
                           ->first();

            $moduleStatus = $this->settings($RegisterOfCharges->status,'id')->key;

            $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
     
               
            $external_comment_query = CompanyStatus::where('company_id',$companyId)
                                                         ->where('comment_type', $external_comment_type_id )
                                                         ->where('request_id', $requestId)
                                                         ->orderBy('id', 'DESC')
                                                         ->first();
            $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                           ?  $external_comment_query->comments
                                           : '';
        }

        if($RegisterOfChargesRecord && $subject){
            $RegisterOfChargesRecord = $RegisterOfChargesRecord->message;
            $subject = $subject->key;
        }

        if($nextPreproposedate){
            $balnceSheet['previous_date'] = $nextPreproposedate->previous_date;
            $balnceSheet['proposed_date'] = $nextPreproposedate->proposed_date;
            $balnceSheet['effected_year'] = $nextPreproposedate->effected_year;
        } else{
            // return $preproposedate;

            if($preproposedate){
               
                 $balnceSheet['proposed_date'] = $preproposedate->proposed_date;

            } else{
                if($company_info->incorporation_at){

                $incorporationYear = $company_info->incorporation_at;
                $incorporation= date('m/d', strtotime($incorporationYear));

                if($incorporation < '03/31'){
                $yearEnd =  date('Y', strtotime($incorporationYear))."-03-31";
                $yearEnd =  date('Y-m-d', strtotime($yearEnd));
                }else {
                $yearEnd = date('Y', strtotime($incorporationYear)) + 1.."-03-31";
                $yearEnd =  date('Y-m-d', strtotime($yearEnd));
                }

                $yearEnd = strtotime($yearEnd);
                $curentDate = strtotime(date('Y-m-d'));

                if($yearEnd < $curentDate){
                    //   return date('m-d');
                    if(date('m-d') < '03-31'){
                       
                        $currentYearEnd =  date('Y', strtotime($incorporationYear))."-03-31";
                        $currentYearEnd =  date('Y-m-d', strtotime($yearEnd));
                        $balnceSheet['proposed_date'] = $currentYearEnd;
                    }else {
                        $currentYearEnd = date('Y') + 1.."-03-31";
                        // $currentYearEnd =  date('Y-m-d', strtotime($yearEnd));
                        $balnceSheet['proposed_date'] = $currentYearEnd;
                    }
                }else{

                }
              } 
            }
             
            // else{
            //      $balnceSheet['proposed_date'] = $preproposedate->proposed_date;
            //     }
            
        }

        // return $balnceSheet;
        
        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $companyId)
        ->update($update_compnay_updated_at);
        
            return response()->json([
                    'message' => 'Data is successfully loaded. load data funtion',
                    'status' =>true,
                    'data'   => array(
                        'createrValid' => true,  
                        'companyInfo'  => $company_info,
                        'certificate_no' => $certificate_no,
                        'request_id' => $requestId,
                        'processStatus' => $this->settings($company_info->status,'id')->key,
                        'moduleStatus' => $moduleStatus,
                        'category' => $category,
                        'payment' => $payment,
                        'record' => $RegisterOfChargesRecord,
                        'subject' => $subject,
                        // 'uploadDocs'   => $this->files_for_upload_docs($request->companyId,$request->requestId),
                        'uploadOtherDocs' => $this->files_for_other_docs($companyId,$requestId),
                        'predate' => $balnceSheet,
                        'test' => $company_info->incorporation_at,
                        'external_global_comment' => $external_global_comment,
                        )
                ], 200);
          
    }

    private function has_request_record($company_id) {
   
            $accepted_request_statuses = array(
                $this->settings('PRIOR_APPROVAL_REJECTED','key')->id,
                $this->settings('PRIOR_APPROVAL_APPROVED','key')->id
            );
            $request_type =  $this->settings('PRIOR_APPROVAL','key')->id;
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
    
    public function submitRecords(Request $request){
        // return $request;
        
            if( !( $request->companyId && $request->message && $request->subject) ) {
                return response()->json([
                    'message' => 'Invalid Parameters.',
                    'status' =>false,
                    'request_id'   => null,
                    'change_id'    => null,
                    'subject' => $request->message
                ], 200);
            }

            $category_id =  PriorApprovalCategory::where('key',$request->subject)
                             ->first();
            $message = $request->message;
            $company_id = $request->companyId;
            $request_id = $request->requestId;
            $subject = $request->subject;
            $PreBalDate = $request->PreBalDate;
            $ProBalDate = $request->ProBalDate;
            $effectiveYear = $request->effectiveYear;


            $request_type =  $this->settings('PRIOR_APPROVAL','key')->id;
            $company_info = Company::where('id', $company_id)->first();

            $user = $this->getAuthUser();

            if(!$request_id){
                $request = new CompanyChangeRequestItem;
                $request->company_id = $company_id;
                $request->request_type = $request_type;
                $request->status = $this->settings('PRIOR_APPROVAL_PROCESSING','key')->id;
                $request->request_by = $user->userid;
                $request->save();
                $request_id = $request->id;
            }

            $priorApproval = PriorApproval::where('request_id',$request_id)
                           ->where('company_id', $request->companyId)
                           ->count();

            $share_summery = array(
            'category_id' => isset($category_id->id) && $category_id->id ? $category_id->id : null,
            'message' => isset($request->message) && $request->message ? $request->message : null,
            );

             $balanceSheet = CompanyBalanceSheetDate::where('request_id',$request_id)
                           ->where('company_id', $request->companyId)
                           ->count();

            if($subject == 'CHANGE_OF_BALANCE_SHEET_DATE'){

                if($balanceSheet == 1){
                    $update_arr = array(
                    'proposed_date' => $ProBalDate,
                    'effected_year' => $effectiveYear,
                    'previous_date' => $PreBalDate,
                    );

                    CompanyBalanceSheetDate::where('company_id', $company_id)
                        ->where('request_id',$request_id)
                        ->update($update_arr);

                }else{
                    if( $PreBalDate && $ProBalDate && $effectiveYear ) {
                    $doc = new CompanyBalanceSheetDate;
                    $doc->proposed_date = $ProBalDate;
                    $doc->effected_year = $effectiveYear;
                    $doc->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                    $doc->company_id = $company_id;
                    $doc->previous_date = $PreBalDate;
                    $doc->request_id = $request_id;
                    $doc->prior_approval = "yes";
                    $doc->save();
                    $new_Bal_She_Id = $doc->id;
                }else{
                        return response()->json([
                        'message' => 'Invalid Parameters.',
                        'status' =>false,
                        'request_id'   => null,
                        'change_id'    => null,
                    ], 200);
                }
                }
                
            }

                
            if($priorApproval != 1){
                $doc = new PriorApproval;
                $doc->company_id = $company_id;
                $doc->request_id = $request_id;
                $doc->category_id = $category_id->id;
                $doc->message = $message;
                $doc->save();
                $new_doc_id = $doc->id;
            }else{
                PriorApproval::where('company_id', $company_id)
                ->where('request_id', $request_id)
                ->update($share_summery);
            }

            return response()->json([
            'message' => 'Data uploaded successfully.',
            'status' =>true,
            'error'  => 'no',
            'priorApproval' => $request->message,
            'request_id' => $request_id

            ], 200);
    }
  
    function files_for_other_docs($company_id,$request_id){

            $generated_files = array(
                    'docs' => array(),
                    'uploadedAll' => false,
                    'doc_id' => 0,
                    'request_id' => $request_id
            );

            if(!$company_id) {
                return array(
                    'docs' => array(),
                    'uploadedAll' => false,
                    'doc_id' => 0
            );
            }

            $company_info = Company::where('id', $company_id)->first();
            $company_status = $this->settings($company_info->status,'id')->key;

      
            // documents list
            $form_other_docs = Documents::where('key', 'PRIOR_APPROVAL_OTHER_DOCUMENTS')->first();
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
                $file_row['applicant_item_id'] = null;
                        
                $uploadeDocStatus = @$docs->status;
                if($company_status == 'PRIOR_APPROVAL_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
                
                $file_row['applicant_item_id'] = $docs->id;
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

                //other documents (those are ususally visible on requesting by the admin )
        $regChargeGroup = DocumentsGroup::where('request_type', 'PRIOR_APPROVAL_DOCUMENT_GROUP')->first();
        $regChargeDocuments = Documents::where('document_group_id', $regChargeGroup->id)
                                           // ->where('key', '!=' , 'FORM_22')
                                            ->get();
                                            
        $regChargeDocumentsCount = Documents::where('document_group_id', $regChargeGroup->id)
                                              //  ->where('key', '!=' , 'FORM_22')
                                                ->count();

        if($regChargeDocumentsCount){
            foreach($regChargeDocuments as $other_doc ) {

                // if($form_prospectus->id === $other_doc->id) {
                //     continue;
                // }
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
                if($company_status == 'PRIOR_APPROVAL_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                    $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                            $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                                    ->where('status',  $uploadeDocStatus )
                                                                    ->where('comment_type', $external_comment_type_id )
                                                                    ->first();
                                $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }
                $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                                    ->where('status',  $uploadeDocStatus )
                                                                    ->where('comment_type', $external_comment_type_id )
                                                                    ->first();
                                $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                
                $file_row['applicant_item_id'] = $uploadedDoc->id;
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

    public function uploadOtherDocs(Request $request){
        
            $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $company_id = $request->company_id;
            $file_description = $request->fileDescription;
            $request_id = (int)$request->request_id;
  
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

    function removeOtherDoc(Request $request){
        
             $file_token = $request->file_token;
        
    
        CompanyDocuments::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
    
        ], 200);
    }

    function submit(Request $request){

            $company_id = $request->companyId;
            $request_id = $request->requestId;
                if(!$request_id ){
        
                return response()->json([
                    'message' => 'Invalid Request.',
                    'status' =>false,
                ], 200);
           }

            $RegisterOfChargesRecord = CompanyChangeRequestItem::where('id',$request_id)
                           ->where('company_id', $request->companyId)
                           ->first();
            $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

            if( !( $moduleStatus === 'PRIOR_APPROVAL_PROCESSING' ) ) {

                return response()->json([
                    'message' => 'Invalid prospectus registration Status.',
                    'status' =>false,
                    'data' => array(
                        'createrValid' => false,
                        'test' => $request_id
                    ),
                
                ], 200);

            }
            // $update1 = PriorApproval::where('request_id', $request_id)->update(['request_id' => $this->settings('PRIOR_APPROVAL_RESUBMITTED', 'key')->id]);
            $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('PRIOR_APPROVAL_PENDING', 'key')->id]);

            if($update2) {
                return response()->json([
                    'message' => 'Successfully Submitted.',
                    'status' =>true,
                    'request_id'   => null,
                    'change_id'    => null,
                ], 200);
        
                exit();
            } else {
                return response()->json([
                    'message' => 'Failed Submitting. Please try again later.',
                    'status' =>false,
                    'request_id'   => null,
                    'change_id'    => null,
                ], 200);
        
                exit();
            }

    }

    function resubmit(Request $request ) {

            $company_id = $request->companyId;
            $request_id = $request->requestId;

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
            if( !( isset($registerOfChargesRecord->status) && $registerOfChargesRecord->status === $this->settings('PRIOR_APPROVAL_RESUBMIT', 'key')->id)){
                return response()->json([
                    'message' => 'Invalid prospectus registration Status.',
                    'status' =>false,
                    'request_id'   => null,
                    'change_id'    => null,
                ], 200);

                exit();
            }

            // $update1 = PriorApproval::where('request_id', $request_id)->update(['request_id' => $this->settings('PRIOR_APPROVAL_RESUBMITTED', 'key')->id]);
            $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('PRIOR_APPROVAL_RESUBMITTED', 'key')->id]);

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

    function getListPriorApproval(Request $request ) {

            $request_type =  $this->settings('PRIOR_APPROVAL','key')->id;
            $approved = $this->settings('PRIOR_APPROVAL_APPROVED' ,'key')->id;
            $record_count = CompanyChangeRequestItem::join('prior_approval','prior_approval.request_id','=','company_change_requests.id')
            // ->whereNotIn('company_change_requests.status', $approved)
            ->where('company_change_requests.company_id', $request->companyId)
            ->where('company_change_requests.request_type',$request_type)
            // ->whereNotIn('company_change_requests.status',$this->settings('PRIOR_APPROVAL_APPROVED' ,'key')->id)
            ->select(
                // 'company_change_requests.*',
               //  'prior_approval.*',
                 'company_change_requests.company_id',
                 'prior_approval.request_id',
                 'prior_approval.category_id',
                 'company_change_requests.updated_at',
                 'company_change_requests.status as request_status'
            )
            ->get();

            $CorrespondenceList = array();

         if(isset($record_count[0]->company_id)){
             foreach($record_count as $corr) {
                 if($this->settings($corr->request_status ,'id')->key != 'PRIOR_APPROVAL_APPROVED'){
                      $comment = PriorApprovalCategory::where('id',$corr->category_id)->first();

                $row = array(
                    'company_id' => $corr->company_id,
                    'request_id' => $corr->request_id,
                    'status' => $this->settings($corr->request_status ,'id')->key,
                    'date' => date('Y-m-d H:i:s', strtotime($corr->updated_at)),
                    'comment' => $comment->category,
                );
        
                $CorrespondenceList[] = $row;
                 }

             }
         }

         return response()->json([
            'message'       => "Successfully listed correspondences.",
            'CorrespondenceList'    => $CorrespondenceList,
            'status'        => true,
            ], 200);
    }

    function removeList(Request $request){

          if(!$request->requestId & !$request->companyId){

            return response()->json([
                'message' => 'missing data',
                'status' =>false,
            ], 200);
        }

        CompanyChangeRequestItem::where('id', $request->requestId)
                            ->delete();

        PriorApproval::where('request_id', $request->requestId)
                        ->where('company_id', $request->companyId)
                        ->delete();

        CompanyBalanceSheetDate::where('request_id', $request->requestId)
                    ->where('company_id', $request->companyId)
                    ->delete();

        return response()->json([
                'message' => 'List removed successfully.',
                'status' =>true,
            ], 200);

                            
    }

     function uploadOtherResubmittedDocs(Request $request){
        
        $company_id = $request->company_id;
        $multiple_id = $request->multiple_id;
        $request_id = $request->request_id;

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
    
        $path = 'workflows/prior-approval/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'PRIOR_APPROVAL_OTHER_DOCUMENTS')->first();


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

    function upload(Request $request){


            $file_name =  uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $company_id = $request->company_id;
            $request_id =  $request->request_id;

            // $request_id = $this->valid_request_operation($company_id);

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

}
