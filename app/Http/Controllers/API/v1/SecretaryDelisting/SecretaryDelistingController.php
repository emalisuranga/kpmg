<?php

namespace App\Http\Controllers\API\v1\SecretaryDelisting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
// use App\Auditor;
use App\Documents;
use App\CompanyPostfix;
use App\Address;
use App\Setting;
USE App\SettingType;
use App\DocumentsGroup;
use App\Country;
use App\Share;
use App\ShareGroup;
use App\User;
use App\People;
use App\Secretary;
use App\SecretaryFirm;
use App\SecretaryComment;
use App\SecretaryDelisting;
use App\SecretaryChangeRequestItem;
use App\SecretaryDocument;
use Storage;
use Cache;
use App;
use URL;
use PDF;
use View;
use App\CompanyItemChange;

use App\ProspectusRegistration;
use App\Currency;

class SecretaryDelistingController extends Controller
{
    use _helper;

    public function loadData(Request $request){

        $secretaryId = $request->secretaryId;
        $secretaryType = $request->secretaryType;
        $phrase = "";

        if(!$secretaryId){

            return response()->json([
                'message' => 'We can \'t find a auditor.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $request_id = $this->valid_request_operation($secretaryId,$secretaryType);
        
        if($secretaryType == 'MODULE_SECRETARY'){
            $secretary_info = Secretary::leftJoin('addresses','secretaries.address_id','=','addresses.id')
                                  ->leftJoin('secretary_certificates','secretaries.id','=','secretary_certificates.secretary_id')
                                    ->where('secretaries.id',$secretaryId)
                                    ->get(['secretaries.first_name','secretaries.last_name','secretaries.nic','secretaries.email','secretaries.mobile','secretary_certificates.certificate_no as certificate_no','addresses.address1','addresses.address2','addresses.city','addresses.district']); 
        } else{
            $secretary_info = SecretaryFirm::leftJoin('addresses','secretary_firm.address_id','=','addresses.id')
                                  ->leftJoin('secretary_certificates','secretary_firm.id','=','secretary_certificates.secretary_id')
                                    ->where('secretary_firm.id',$secretaryId)
                                    ->get(['secretary_firm.first_name','secretary_firm.last_name','secretary_firm.email','secretary_firm.mobile','secretary_certificates.certificate_no as certificate_no','addresses.address1','addresses.address2','addresses.city','addresses.district']); 
        }
          

        $secretaryOffshore = SecretaryDelisting::where('request_id',$request_id)
                                ->where('secretary_id', $secretaryId)
                                ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
                                ->where('delisting_type',$this->settings('VOLUNTARY_DELISTING','key')->id)
                                ->first();

        if($secretaryOffshore){
            $phrase = $secretaryOffshore->phrase;
        }

        if( ! isset($secretary_info[0])) {

            return response()->json([
                'message' => 'We can \'t find a auditor.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $RegisterOfChargesRecord = SecretaryChangeRequestItem::where('id',$request_id)
                           ->where('secretary_id', $secretaryId)
                           ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
                           ->first();

        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;
       

        if( !( $moduleStatus === 'SECRETARY_DELISTING_PROCESSING' ||  $moduleStatus === 'SECRETARY_DELISTING_RESUBMIT') ) {

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
     
        if($secretaryType == 'MODULE_SECRETARY'){
            $external_comment_query = SecretaryComment::where('secretary_id',$secretaryId);
        } else{
            $external_comment_query = SecretaryComment::where('firm_id',$secretaryId);
        }
        $external_comment_query = $external_comment_query->where('comment_type', $external_comment_type_id );
        $external_comment_query = $external_comment_query->orderBy('id', 'DESC');
        $external_comment_query = $external_comment_query->first();

        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                           ?  $external_comment_query->comments
                                           : '';
            return response()->json([
                    'message' => 'Data is successfully loaded.',
                    'status' =>true,
                    'data'   => array(
                         'createrValid' => true,  
                         'secretaryInfo'  => $secretary_info[0],
                         'request_id' => $request_id,
                         'moduleStatus' => $moduleStatus,
                         'phrase' => $phrase,
                         'uploadDocs'   => $this->files_for_upload_docs($secretaryId, $secretaryType),
                         'uploadOtherDocs' => $this->files_for_other_docs($secretaryId, $secretaryType),
                         'external_global_comment' => $external_global_comment,
                        )
                ], 200); 

    }

    private function valid_request_operation($secretaryId , $secretaryType){

            $accepted_request_statuses = array(
                $this->settings('SECRETARY_DELISTING_APPROVED','key')->id,
                $this->settings('SECRETARY_DELISTING_REJECTED','key')->id
            );
            $request_type =  $this->settings('SECRETARY_DELISTING','key')->id;

            $exist_request_id = $this->has_request_record($secretaryId , $secretaryType);
            if($exist_request_id) {
               
                $request_count = SecretaryChangeRequestItem::where('request_type',$request_type)
                                ->where('secretary_id', $secretaryId)
                                ->where('id', $exist_request_id)
                                ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
                                ->whereNotIn('status', $accepted_request_statuses )
                                ->count();
                if($request_count !== 1) { // request not in processing or  resubmit stage
                    return false;
                } else {
                    return $exist_request_id;
                }
                
            } else {
                $user = auth()->user();

                    $request = new SecretaryChangeRequestItem;
                    $request->secretary_id = $secretaryId;
                    $request->request_type = $request_type;
                    $request->secretary_type = $this->settings( $secretaryType,'key')->id;
                    $request->status = $this->settings('SECRETARY_DELISTING_PROCESSING','key')->id;
                    $request->request_by = $user->id ;
                    $request->save();

                    return $request->id;
        }
    }

    private function has_request_record($secretaryId ,$secretaryType) {
   
        $accepted_request_statuses = array(
                $this->settings('SECRETARY_DELISTING_APPROVED','key')->id,
                $this->settings('SECRETARY_DELISTING_REJECTED','key')->id
            );
        $request_type =  $this->settings('SECRETARY_DELISTING','key')->id;
        $record_count = SecretaryChangeRequestItem::where('secretary_id', $secretaryId)
                                ->where('request_type',$request_type)
                                ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
                                ->whereNotIn('status', $accepted_request_statuses )
                                ->count();
       
        if( $record_count === 1 ) {
            $record = SecretaryChangeRequestItem::where('secretary_id', $secretaryId)
             ->where('request_type',$request_type)
             ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
            ->whereNotIn('status', $accepted_request_statuses )
            ->first();

            return $record->id;
        } else {
            return false;
        }
    }

    function files_for_upload_docs($secretaryId , $secretaryType){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
        );

        $request_id = $this->valid_request_operation($secretaryId ,$secretaryType);

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('SECRETARY_DELISTING','key')->id;

        $request = SecretaryChangeRequestItem::where('id',$request_id)
                            ->where('request_type',$request_type)
                           ->where('secretary_id', $secretaryId)
                           ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
                           ->first();

        // documents list
        $form_prospectus = Documents::where('key', 'FORM_SECRETARY_DELISTING')->first();
        $form_other_docs = Documents::where('key', 'SECRETARY_DELISTING_OTHER_DOCUMENTS')->first();
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
        
        if($secretaryType == 'MODULE_SECRETARY'){
            $uploadedDoc =  SecretaryDocument::where('secretary_id', $secretaryId);
        } else{
            $uploadedDoc =  SecretaryDocument::where('firm_id', $secretaryId);
        }
        $uploadedDoc = $uploadedDoc->where('document_id', $form_prospectus->id )->orderBy('id', 'DESC')->first();
                
        $uploadeDocStatus = @$uploadedDoc->status;
        if($request->status == 'SECRETARY_DELISTING_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }
        
        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                       $commentRow = SecretaryComment::where('auditor_document_id', $uploadedDoc->id )
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
        $regChargeGroup = DocumentsGroup::where('request_type', 'SECRETARY_DELISTING_DOCUMENT_GROUP')->first();
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

                 if($auditorType == 'MODULE_SECRETARY'){
                    $is_document_requested =  SecretaryDocument::where('auditor_id', $auditorId);
                } else{
                    $is_document_requested =  SecretaryDocument::where('firm_id', $auditorId);
                }
                $is_document_requested =  $is_document_requested->where('document_id', $other_doc->id )
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
                        
                if($secretaryType == 'MODULE_SECRETARY'){
                    $uploadedDoc =  SecretaryDocument::where('secretary_id', $secretaryId);
                } else{
                    $uploadedDoc =  SecretaryDocument::where('firm_id', $secretaryId);
                }
                $uploadedDoc =  $uploadedDoc->where('document_id', $other_doc->id )
                                                ->orderBy('id', 'DESC')
                                                ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                
                if($request->status == 'SECRETARY_DELISTING_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                 if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                            $commentRow = SecretaryComment::where('secretary_document_id', $uploadedDoc->id )
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

    function files_for_other_docs($secretaryId , $secretaryType){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
        );
      //  $company_id = $request->company_id;

        if(!$secretaryId) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $request_id = null;
        $request_id = $this->valid_request_operation($secretaryId , $secretaryType);

        $secretary_info = SecretaryChangeRequestItem::where('secretary_id', $secretaryId)
                            ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
                               ->where('id', $request_id)
                               ->first();
        $secretary_status = $this->settings($secretary_info->status,'id')->key;

        // documents list
        $form_other_docs = Documents::where('key', 'SECRETARY_DELISTING_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

        if($secretaryType == 'MODULE_SECRETARY'){
            $other_docs =  SecretaryDocument::where('secretary_id', $secretaryId);
        } else{
            $other_docs =  SecretaryDocument::where('firm_id', $secretaryId);
        }
        $other_docs =  $other_docs->where('document_id', $form_other_docs->id )
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
            if($uploadeDocStatus == 'SECRETARY_DELISTING_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                            $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
            }
            if(isset($uploadeDocStatus) && $uploadeDocStatus ){
                            $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
            }
            if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
    
                 $commentRow = SecretaryComment::where('secretary_document_id', $uploadedDoc->id )
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

        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
    
        return $generated_files;
    
    }

    public function generate_App_pdf(Request $request){
        $secretaryId = $request->secretaryId;
        $phrase = $request->phrase;
        $secretaryType = $request->secretaryType;
        $request_id = $this->valid_request_operation($secretaryId ,$secretaryType);
        
        if(!$request_id){

            return response()->json([
                'message' => 'We can \'t find a request_id.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

         if($secretaryType == 'MODULE_SECRETARY'){
            $secretary_info = Secretary::leftJoin('addresses','secretaries.address_id','=','addresses.id')
                                  ->leftJoin('secretary_certificates','secretaries.id','=','secretary_certificates.secretary_id')
                                    ->where('secretaries.id',$secretaryId)
                                    ->get(['secretaries.first_name','secretaries.last_name','secretaries.nic','secretaries.email','secretaries.mobile','secretary_certificates.certificate_no as certificate_no','addresses.address1','addresses.address2','addresses.city','addresses.district']); 
        } else{
            $secretary_info = SecretaryFirm::leftJoin('addresses','secretary_firm.address_id','=','addresses.id')
                                  ->leftJoin('secretary_certificates','secretary_firm.id','=','secretary_certificates.secretary_id')
                                    ->where('secretary_firm.id',$secretaryId)
                                    ->get(['secretary_firm.first_name','secretary_firm.last_name','secretary_firm.email','secretary_firm.mobile','secretary_certificates.certificate_no as certificate_no','addresses.address1','addresses.address2','addresses.city','addresses.district']); 
        }  
        $secretaryOffshore = SecretaryDelisting::where('request_id',$request_id)
                                ->where('secretary_id', $secretaryId)
                                ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
                                ->where('delisting_type',$this->settings('VOLUNTARY_DELISTING','key')->id)
                                ->first();

        if($secretaryOffshore){
           $phrase = $secretaryOffshore->phrase;
        }

        // $time=strtotime();
        // $date=date("d",$time);  
        // $month=date("m",$time);
        // $year=date("Y",$time);  

        $fieldset = array(
        'regNo' =>   $secretary_info[0]->certificate_no,
        'phone' => $secretary_info[0]->mobile,
        'email' => $secretary_info[0]->email,
        'address' => $secretary_info[0]->address1.' , '.$secretary_info[0]->address2.' , '.$secretary_info[0]->city,
        'curentDate' => date('d'),
        'curentMonth' => date('m'),
        'curentYear' => date('Y'),
        'date' => date('d').'/'.date('m').'/'.date('Y'),
        'auditor_name' => $secretary_info[0]->first_name.' '.$secretary_info[0]->last_name,
        'phrase' => $phrase,
        );

        $pdf = PDF::loadView('forms/scan30_aud_strikeoff',$fieldset);
        $pdf->stream('Auditors-Strike-Off.pdf');

    }

    function upload(Request $request){
            $file_name =  uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $secretaryId = $request->secretaryId;
            $secretaryType = $request->secretaryType;
            $requestNumber =  $request->requestNumber;

            $request_id = $this->valid_request_operation($secretaryId , $secretaryType);

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

            $path = 'secretary/secretaryDelisting/'.$secretaryId;
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
            $token = md5(uniqid());

            $get_query = SecretaryDocument::query();

            $get_query->where('secretary_id', $secretaryId );
            $get_query->where('document_id',$file_type_id);
            $old_doc_info = $get_query->first();
        
            $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
              
            $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
            $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
        
        
            $query = SecretaryDocument::query();
            if($secretaryType == 'MODULE_SECRETARY'){
                $query->where('secretary_id', $secretaryId );
            } else {
                $query->where('firm_id', $secretaryId );
            }
            $query->where('document_id',$file_type_id);
            $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
            $query->delete();
                
               $doc = new SecretaryDocument;
               $doc->document_id = $file_type_id;
               $doc->path = $path;
               $doc->secretary_id = $secretaryId;
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
        $secretaryId = $request->secretaryId;
        $secretaryType = $request->secretaryType;
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
    
        $path = 'secretary/secretaryDelisting/'.$secretaryId;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

         $other_doc_count = SecretaryDocument::query();
            if($secretaryType == 'MODULE_SECRETARY'){
                $other_doc_count->where('secretary_id', $secretaryId );
            } else {
                $other_doc_count->where('firm_id', $secretaryId );
            }
        $other_doc_count->where('document_id',$file_type_id )->count();
    

        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
          
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
    
 
           $doc = new SecretaryDocument;
           $doc->document_id = $file_type_id;
           $doc->path = $path;
             if($secretaryType == 'MODULE_SECRETARY'){
                $doc->secretary_id = $secretaryId;
            } else{
                $doc->firm_id = $secretaryId;
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

        $secretaryId = $request->secretaryId;
        $secretaryType = $request->secretaryType;
        $phrase = $request->phrase;
        $request_id = $this->valid_request_operation($secretaryId ,$secretaryType);

        $update = SecretaryChangeRequestItem::find($request_id);
        $update->status = $this->settings( 'SECRETARY_DELISTING_PENDING','key')->id;
        $update->save();

           return response()->json([
                'message' => 'File uploaded successfully.',
                'status' =>true,
                'error'  => 'no',
            ], 200);
    }

    function dataUplode(Request $request){
        
        $secretaryId = $request->secretaryId;
        $secretaryType = $request->secretaryType;
        $phrase = $request->phrase;
        $request_id = $this->valid_request_operation($secretaryId ,$secretaryType);
        
        $secretaryOffshoreId = SecretaryDelisting::where('request_id',$request_id)
        ->where('secretary_id', $secretaryId)
        ->where('secretary_type', $this->settings( $secretaryType,'key')->id)
        ->where('delisting_type',$this->settings('VOLUNTARY_DELISTING','key')->id)
        ->select('id')
        ->first();

            if($request_id && $phrase && $secretaryId){
                if($secretaryOffshoreId){
                    $req = SecretaryDelisting::find($secretaryOffshoreId->id);
                }
                else {
                    $req = new SecretaryDelisting;
                    $req->secretary_id = $secretaryId;
                    $req->request_id = $request_id;
                    $req->delisting_type = $this->settings('VOLUNTARY_DELISTING','key')->id;
                    $req->secretary_type =$this->settings( $secretaryType,'key')->id;
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

            $secretaryId = $request->secretaryId;
            $secretaryType = $request->secretaryType;
            $file_type_id = $request->fileTypeId;

            $request_id = $this->valid_request_operation($secretaryId ,$secretaryType);

            if(!$request_id ){
                return response()->json([
                    'message' => 'Invalid Request.',
                    'status' =>false,
                ], 200);
           }
            
            if($secretaryType == 'MODULE_SECRETARY'){
                $query =  SecretaryDocument::where('secretary_id', $secretaryId);
            } else{
                $query =  SecretaryDocument::where('firm_id', $secretaryId);
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

        SecretaryDocument ::where('file_token', $file_token)
                        ->delete();

        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        

        ], 200);
    }
}