<?php

namespace App\Http\Controllers\API\v1\AuditorStrikeOff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use App\Auditor;
// use App\Company;
// use App\CompanyCertificate;
// use App\CompanyChangeRequestItem;
use App\Documents;
use App\CompanyPostfix;
use App\Address;
use App\Setting;
USE App\SettingType;
// use App\CompanyMember;
// use App\CompanyFirms;
use App\DocumentsGroup;
use App\Country;
use App\Share;
use App\ShareGroup;
// use App\CompanyDocuments;
// use App\CompanyDocumentStatus;
use App\User;
use App\People;
use App\AuditorComment;
use App\OffshoreStrike;
use App\AuditorsStrikeOff;

use App\AuditorChangeRequestItem;
use App\AuditorDocument;
use App\AuditorDocumentStatus;
use Storage;
use Cache;
use App;
use URL;
use PDF;
use View;
use App\CompanyItemChange;

use App\ProspectusRegistration;
use App\Currency;

class AuditorStrikeOffController extends Controller
{
    use _helper;

    public function loadData(Request $request){

        $auditorId = $request->auditorId;
        $auditorType = $request->auditorType;
        $phrase = "";

        if(!$auditorId){

            return response()->json([
                'message' => 'We can \'t find a auditor.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $request_id = $this->valid_request_operation($auditorId , $auditorType);
        
        if($auditorType == 'MODULE_AUDITOR'){
            $auditor_info = Auditor::leftJoin('auditor_certificates','auditors.id','=','auditor_certificates.auditor_id')
                        ->leftJoin('addresses','auditors.address_id','=','addresses.id')
                        ->where('auditors.id',$auditorId)
                        ->get(['auditors.id','auditors.first_name','auditors.last_name','auditors.nic','auditor_certificates.certificate_no as certificate_no','auditors.mobile','auditors.email','addresses.address1','addresses.address2','addresses.city','addresses.district']);  
        } else{
            $auditor_info = AuditorFirm::leftJoin('auditor_certificates','auditor_firms.id','=','auditor_certificates.firm_id')
                        ->leftJoin('addresses','auditor_firms.address_id','=','addresses.id')
                        ->where('auditor_firms.id',$auditorId)
                        ->get(['auditor_firms.id','auditor_firms.first_name','auditor_firms.last_name','auditor_certificates.certificate_no as certificate_no','auditors.mobile','auditors.email','addresses.address1','addresses.address2','addresses.city','addresses.district']); 
        }
          

        $auditorsOffshore = AuditorsStrikeOff::where('request_id',$request_id)
                                ->where('auditors_id', $auditorId)
                                ->where('auditor_type', $auditorType)
                                ->where('strike_off_type',$this->settings('VOLUNTARY_DELISTING','key')->id)
                                ->first();

        if($auditorsOffshore){
            $phrase = $auditorsOffshore->phrase;
        }

        if( ! isset($auditor_info[0])) {

            return response()->json([
                'message' => 'We can \'t find a auditor.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $RegisterOfChargesRecord = AuditorChangeRequestItem::where('id',$request_id)
                           ->where('auditor_id', $auditorId)
                           ->first();
        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

        if( !( $moduleStatus === 'AUDITOR_DELISTING_PROCESSING' ||  $moduleStatus === 'AUDITOR_DELISTING_RESUBMIT') ) {

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
     
        if($auditorType == 'MODULE_AUDITOR'){
            $external_comment_query = AuditorComment::where('auditor_id',$auditorId);
        } else{
            $external_comment_query = AuditorComment::where('firm_id',$auditorId);
        }
        $external_comment_query = $external_comment_query->where('comment_type', $external_comment_type_id )->orderBy('id', 'DESC')->first();

                                                         
        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                           ?  $external_comment_query->comments
                                           : '';
            return response()->json([
                    'message' => 'Data is successfully loaded.',
                    'status' =>true,
                    'data'   => array(
                         'createrValid' => true,  
                         'auditorInfo'  => $auditor_info[0],
                         'request_id' => $request_id,
                         'moduleStatus' => $moduleStatus,
                         'phrase' => $phrase,
                         'uploadDocs'   => $this->files_for_upload_docs($request->auditorId ,$auditorType),
                         'uploadOtherDocs' => $this->files_for_other_docs($request->auditorId ,$auditorType),
                         'external_global_comment' => $external_global_comment,
                        )
                ], 200); 

    }

    private function valid_request_operation($auditorId, $auditorType){

            $accepted_request_statuses = array(
                $this->settings('AUDITOR_DELISTING_APPROVED','key')->id,
                $this->settings('AUDITOR_DELISTING_REJECTED','key')->id
            );
            $request_type =  $this->settings('AUDITOR_DELISTING','key')->id;

            $exist_request_id = $this->has_request_record($auditorId ,$auditorType);
            if($exist_request_id) {
               
                $request_count = AuditorChangeRequestItem::where('request_type',$request_type)
                                ->where('auditor_id', $auditorId)
                                ->where('table_type', $this->settings( $auditorType,'key')->id)
                                ->where('id', $exist_request_id)
                                ->whereNotIn('status', $accepted_request_statuses )
                                ->count();
                if($request_count !== 1) { // request not in processing or  resubmit stage
                    return false;
                } else {
                    return $exist_request_id;
                }
                
            } else {
                $user = auth()->user();

                    $request = new AuditorChangeRequestItem;
                    $request->auditor_id = $auditorId;
                    $request->request_type = $request_type;
                    $request->table_type = $this->settings( $auditorType,'key')->id;
                    $request->status = $this->settings('AUDITOR_DELISTING_PROCESSING','key')->id;
                    $request->request_by = $user->id ;
                    $request->save();

                    return $request->id;
        }
    }

    private function has_request_record($auditorId ,$auditorType) {
   
        $accepted_request_statuses = array(
                $this->settings('AUDITOR_DELISTING_APPROVED','key')->id,
                $this->settings('AUDITOR_DELISTING_REJECTED','key')->id
            );
        $request_type =  $this->settings('AUDITOR_DELISTING','key')->id;
        $record_count = AuditorChangeRequestItem::where('auditor_id', $auditorId)
                                ->where('request_type',$request_type)
                                ->where('table_type', $this->settings( $auditorType,'key')->id)
                                ->whereNotIn('status', $accepted_request_statuses )
                                ->count();
       
        if( $record_count === 1 ) {
            $record = AuditorChangeRequestItem::where('auditor_id', $auditorId)
             ->where('request_type',$request_type)
             ->where('table_type', $this->settings( $auditorType,'key')->id)
            ->whereNotIn('status', $accepted_request_statuses )
            ->first();

            return $record->id;
        } else {
            return false;
        }
    }

    function files_for_upload_docs($auditorId ,$auditorType){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
        );

        $request_id = $this->valid_request_operation($auditorId ,$auditorType);

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('AUDITOR_DELISTING','key')->id;

        $request = AuditorChangeRequestItem::where('request_type',$request_type)
                               ->where('auditor_id', $auditorId)
                               ->where('id', $request_id)
                               ->first();

        // documents list
        $form_prospectus = Documents::where('key', 'FORM_AUDITOR_DELISTING')->first();
        $form_other_docs = Documents::where('key', 'AUDITOR_DELISTING_OTHER_DOCUMENTS')->first();
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
        
        if($auditorType == 'MODULE_AUDITOR'){
            $uploadedDoc =  AuditorDocument::where('auditor_id', $auditorId);
        } else{
            $uploadedDoc =  AuditorDocument::where('firm_id', $auditorId);
        }
        $uploadedDoc = $uploadedDoc->where('document_id', $form_prospectus->id )->orderBy('id', 'DESC')->first();
                
        $uploadeDocStatus = @$uploadedDoc->status;
        if($request->status == 'AUDITOR_DELISTING_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = AuditorDocumentStatus::where('auditor_document_id', $uploadedDoc->id )
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
        $regChargeGroup = DocumentsGroup::where('request_type', 'AUDITOR_DELISTING_DOCUMENT_GROUP')->first();
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

                if($auditorType == 'MODULE_AUDITOR'){
                    $is_document_requested =  AuditorDocument::where('auditor_id', $auditorId);
                } else{
                    $is_document_requested =  AuditorDocument::where('firm_id', $auditorId);
                }
                $is_document_requested = $is_document_requested ->where('document_id', $other_doc->id )
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
                        
                if($auditorType == 'MODULE_AUDITOR'){
                    $uploadedDoc =  AuditorDocument::where('auditor_id', $auditorId);
                } else{
                    $uploadedDoc =  AuditorDocument::where('firm_id', $auditorId);
                }
                $uploadedDoc = $uploadedDoc->where('document_id', $other_doc->id )
                                                ->orderBy('id', 'DESC')
                                                ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                
                if($request->status == 'AUDITOR_DELISTING_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                 if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                            $commentRow = AuditorDocumentStatus::where('auditor_document_id', $uploadedDoc->id )
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

    function files_for_other_docs($auditorId ,$auditorType){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
        );
      //  $company_id = $request->company_id;

        if(!$auditorId) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $request_id = null;
        $request_id = $this->valid_request_operation($auditorId ,$auditorType);

        $auditor_info = AuditorChangeRequestItem::where('auditor_id', $auditorId)
                               ->where('id', $request_id)
                               ->where('table_type', $this->settings( $auditorType,'key')->id)
                               ->first();
        $company_status = $this->settings($auditor_info->status,'id')->key;

        // documents list
        $form_other_docs = Documents::where('key', 'AUDITOR_DELISTING_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

        if($auditorType == 'MODULE_AUDITOR'){
            $other_docs =  AuditorDocument::where('auditor_id', $auditorId);
        } else{
            $other_docs =  AuditorDocument::where('firm_id', $auditorId);
        }
        $other_docs = $other_docs->where('document_id', $form_other_docs->id )
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
            if($company_status == 'AUDITOR_DELISTING_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

    public function generate_App_pdf(Request $request){
        $auditorId = $request->auditorId;
        $auditorType = $request->auditorType;
        $phrase = $request->phrase;
        $request_id = $this->valid_request_operation($auditorId ,$auditorType);
        
        if(!$request_id){

            return response()->json([
                'message' => 'We can \'t find a request_id.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

          if($auditorType == 'MODULE_AUDITOR'){
            $auditor_info = Auditor::leftJoin('auditor_certificates','auditors.id','=','auditor_certificates.auditor_id')
                        ->leftJoin('addresses','auditors.address_id','=','addresses.id')
                        ->where('auditors.id',$auditorId)
                        ->get(['auditors.id','auditors.first_name','auditors.last_name','auditors.nic','auditor_certificates.certificate_no as certificate_no','auditors.mobile','auditors.email','addresses.address1','addresses.address2','addresses.city','addresses.district']); 
                        
        } else{
            $auditor_info = AuditorFirm::leftJoin('auditor_certificates','auditor_firms.id','=','auditor_certificates.firm_id')
                        ->leftJoin('addresses','auditor_firms.address_id','=','addresses.id')
                        ->where('auditor_firms.id',$auditorId)
                        ->get(['auditor_firms.id','auditor_firms.first_name','auditor_firms.last_name','auditor_certificates.certificate_no as certificate_no','auditors.mobile','auditors.email','addresses.address1','addresses.address2','addresses.city','addresses.district']); 
        }  
        
        $auditors_strike_off = AuditorsStrikeOff::where('request_id',$request_id)
                                    ->where('auditors_id', $auditorId)
                                    ->where('auditor_type', $this->settings( $auditorType,'key')->id)
                                    ->where('strike_off_type',$this->settings('VOLUNTARY_DELISTING','key')->id)
                                    ->first();

        if($auditors_strike_off){
           $phrase = $auditors_strike_off->phrase;
        }

        // $time=strtotime();
        // $date=date("d",$time);  
        // $month=date("m",$time);
        // $year=date("Y",$time);  

        $fieldset = array(
        'regNo' =>   $auditor_info[0]->certificate_no,
        'phone' => $auditor_info[0]->mobile,
        'email' => $auditor_info[0]->email,
        'address' => $auditor_info[0]->address1.' , '.$auditor_info[0]->address2.' , '.$auditor_info[0]->city,
        'curentDate' => date('d'),
        'curentMonth' => date('m'),
        'curentYear' => date('Y'),
        'date' => date('d').'/'.date('m').'/'.date('Y'),
        'auditor_name' => $auditor_info[0]->first_name.' '.$auditor_info[0]->last_name,
        'phrase' => $phrase,
        );

        $pdf = PDF::loadView('forms/scan30_aud_strikeoff',$fieldset);
        $pdf->stream('Auditors-Strike-Off.pdf');

    }

    function upload(Request $request){
            $file_name =  uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $auditor_id = $request->auditor_id;
            $auditorType = $request->auditorType;
            $requestNumber =  $request->requestNumber;

            $request_id = $this->valid_request_operation($auditor_id ,$auditorType);

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

            $path = 'company/auditorStrikeOff/'.$auditor_id;
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
            $token = md5(uniqid());

            $get_query = AuditorDocument::query();
            $get_query->where('auditor_id', $auditor_id );
            $get_query->where('document_id',$file_type_id);
            $old_doc_info = $get_query->first();
        
            $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
              
            $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
            $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
        
            if($auditorType == 'MODULE_AUDITOR'){
                $query =  AuditorDocument::where('auditor_id', $auditor_id);
            } else{
                $query =  AuditorDocument::where('firm_id', $auditor_id);
            } 
            $query->where('document_id',$file_type_id);
            $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
            $query->delete();
                
               $doc = new AuditorDocument;
               $doc->document_id = $file_type_id;
               $doc->path = $path;
               if($auditorType == 'MODULE_AUDITOR'){
                $doc->auditor_id = $auditor_id;
                } else {
                $doc->firm_id = $auditor_id;
                } 
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
        $auditor_id = $request->company_id;
        $auditorType = $request->auditorType;
        $file_description = $request->fileDescription;
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
    
        $path = 'company/auditorStrikeOff/'.$auditor_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $other_doc_count = AuditorDocument::where('auditor_id',$auditor_id)
                            ->where('document_id',$file_type_id )
                            ->count();
    

        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
          
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
    
 
           $doc = new AuditorDocument;
           $doc->document_id = $file_type_id;
           $doc->path = $path;
           if($auditorType == 'MODULE_AUDITOR'){
                $doc->auditor_id = $auditor_id;
            } else{
                $doc->firm_id = $auditor_id;
            }
           $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
           $doc->file_token = $token;
           $doc->name = $real_file_name;
           $doc->description = $file_description;
           $doc->save();
           $new_doc_id = $doc->id;

           return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'error'  => 'no',
        ], 200);
    

    }

    function submit(Request $request){

        $auditorId = $request->auditorId;
        $auditorType = $request->auditorType;
        $request_id = $this->valid_request_operation($auditorId ,$auditorType);
        $phrase = $request->phrase;
        

        // $auditorsOffshoreId = AuditorsStrikeOff::select('id')
        //                         ->where('request_id',$request_id)
        //                         ->where('auditors_id', $auditorId)
        //                         ->where('strike_off_type',$this->settings('VOLUNTARY_DELISTING','key')->id)
        //                         ->first();
        //  if($request_id && $phrase && $auditorId){
        //     if($auditorsOffshoreId){
        //         $req = AuditorsStrikeOff::find($auditorsOffshoreId->id);
        //    }
        //    else {
        //         $req = new AuditorsStrikeOff;
        //         $req->auditors_id = $auditorId;
        //         $req->request_id = $request_id;
        //         $req->strike_off_type = $this->settings('VOLUNTARY_DELISTING','key')->id;
        //         $req->auditor_type = $auditorType;
        //    }
        //    $req->phrase = $phrase;
        //    $req->save();
        // }

        $update = AuditorChangeRequestItem::find($request_id);
        $update->status = $this->settings( 'AUDITOR_DELISTING_PENDING','key')->id;
        $update->save();

           return response()->json([
                'message' => 'File uploaded successfully.',
                'status' =>true,
                'error'  => 'no',
            ], 200);
    }

    function dataUplode(Request $request){
        
        $auditorId = $request->auditorId;
        $auditorType = $request->auditorType;
        $request_id = $this->valid_request_operation($auditorId ,$auditorType);
        $phrase = $request->phrase;
        

        $auditorsOffshoreId = AuditorsStrikeOff::select('id')
                                ->where('request_id',$request_id)
                                ->where('auditors_id', $auditorId)
                                ->where('strike_off_type',$this->settings('VOLUNTARY_DELISTING','key')->id)
                                ->first();
         if($request_id && $phrase && $auditorId){
            if($auditorsOffshoreId){
                $req = AuditorsStrikeOff::find($auditorsOffshoreId->id);
           }
           else {
                $req = new AuditorsStrikeOff;
                $req->auditors_id = $auditorId;
                $req->request_id = $request_id;
                $req->strike_off_type = $this->settings('VOLUNTARY_DELISTING','key')->id;
                $req->auditor_type = $auditorType;
           }
           $req->phrase = $phrase;
           $req->save();
        }

          return response()->json([
                'message' => 'File uploaded successfully.',
                'status' =>true,
                'error'  => 'no',
            ], 200);
    }

    function removeDoc(Request $request){

            $auditorId = $request->auditorId;
            $auditorType = $request->auditorType;
            $request_id = $this->valid_request_operation($auditorId ,$auditorType);

            if(!$request_id ){
        
                return response()->json([
                    'message' => 'Invalid Request.',
                    'status' =>false,
                ], 200);
           }
            $file_type_id = $request->fileTypeId;

            if($auditorType == 'MODULE_AUDITOR'){
                $query =  AuditorDocument::where('auditor_id', $auditorId);
            } else{
                $query =  AuditorDocument::where('firm_id', $auditorId);
            }
                $query = $query->where('document_id', $file_type_id)
                            ->delete();

            return response()->json([
                            'message' => 'File removed successfully.',
                            'status' =>true,
            ], 200);
    }

    function removeOtherDoc(Request $request){

        $file_token = $request->file_token;
        $auditorType = $request->auditorType;

         if($auditorType == 'MODULE_AUDITOR'){
                $query =  AuditorDocument::where('auditor_id', $auditorId);
            } else{
                $query =  AuditorDocument::where('firm_id', $auditorId);
            }
           $query = $query->where('file_token', $file_token)
                        ->delete();

        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        

        ], 200);
    }


}