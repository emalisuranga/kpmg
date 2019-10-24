<?php

namespace App\Http\Controllers\API\v1\StrikeOff;

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
use App\OffshoreStrike;
use Storage;
use Cache;
use App;
use URL;
use PDF;
use View;
use App\CompanyItemChange;

use App\ProspectusRegistration;
use App\Currency;

class StrikeOffController extends Controller
{
     use _helper;

    //  public function __construct()
    // {
    //     $country = '';
    //     $lastDate = '';
    // }

     public function loadData(Request $request){

        $companyId = $request->companyId;
        $requestId = $request->requestId;
        $country = $request->country;
        $ceasedDate = $request->ceasedDate;
        $signing_party_designation = $request->signing_party_designation;
        $signed_party_id = $request->singning_party_name;
        $offshoreStrikeId  = '';


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

        

        $request_id = $this->valid_request_operation($companyId);
         $RegisterOfChargesRecord = CompanyChangeRequestItem::where('id',$request_id)
                           ->where('company_id', $request->companyId)
                           ->first();
        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

        if( !( $moduleStatus === 'OFFSHORE_STRIKE_OFF_PROCESSING' ||  $moduleStatus === 'OFFSHORE_STRIKE_OFF_RESUBMIT' ) ) {

            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }

        $offshoreStrikeId = OffshoreStrike::select('id')
                                ->where('request_id',$request_id)
                                ->where('company_id', $companyId)
                                ->where('strike_type',$this->settings('OFFSHORE_STRIKE_OFF','key')->id)
                                ->first();

        if($request_id && $country && $ceasedDate){
            if($offshoreStrikeId){
                $req = OffshoreStrike::find($offshoreStrikeId->id);
           }
           else {
                $req = new OffshoreStrike;
                $req->company_id = $companyId;
                $req->request_id = $request_id;
                $req->strike_type = $this->settings('OFFSHORE_STRIKE_OFF','key')->id;
           }
           $req->country = $request->country;
           $req->last_date = $request->ceasedDate;
           $req->signing_party_designation = $request->signing_party_designation;
           $req->signed_party_id = $request->singning_party_name;
           $req->save();
        }

        
        $offshoreStrike = OffshoreStrike::where('request_id',$request_id)
                                ->where('company_id', $companyId)
                                ->where('strike_type',$this->settings('OFFSHORE_STRIKE_OFF','key')->id)
                                ->first();

         if($offshoreStrike){
            $country = $offshoreStrike->country;
            $ceasedDate = $offshoreStrike->last_date;
            $signing_party_designation = $offshoreStrike->signing_party_designation;
            $signed_party_id = $offshoreStrike->signed_party_id;
        }

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
        $director_list = CompanyMember::where('company_id',$companyId)
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
                         'moduleStatus' => $moduleStatus,
                         'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                         'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                         'country' => $country,
                         'ceasedDate' => $ceasedDate,
                         'signing_party_designation' => $signing_party_designation,
                         'singning_party_name' => $signed_party_id,
                        //  'additional' => $this->aditional_docs($request->companyId),
                         'offshorePayment' => $this->settings('PAYMENT_OFFSHORE_STRIKE_OFF','key')->value,
                         'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                         'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                         'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                         'external_global_comment' => $external_global_comment,
                         'directors' =>$directors,
                         'secs' => $secs,
                         'sec_firms' =>$sec_firms,
                        )
                ], 200); 

    }

     private function valid_request_operation($company_id){

            $accepted_request_statuses = array(
                $this->settings('OFFSHORE_STRIKE_OFF_APPROVED','key')->id,
                $this->settings('OFFSHORE_STRIKE_OFF_REJECTED','key')->id
            );
            $request_type =  $this->settings('OFFSHORE_STRIKE_OFF','key')->id;

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
                    $request->status = $this->settings('OFFSHORE_STRIKE_OFF_PROCESSING','key')->id;
                    $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
                    $request->save();

                    return $request->id;
        }
    }

    private function has_request_record($company_id) {
   
        $accepted_request_statuses = array(
            $this->settings('OFFSHORE_STRIKE_OFF_APPROVED','key')->id,
            $this->settings('OFFSHORE_STRIKE_OFF_REJECTED','key')->id
        );
        $request_type =  $this->settings('OFFSHORE_STRIKE_OFF','key')->id;
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


    function files_for_upload_docs($company_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
        );

        $request_id = $this->valid_request_operation($company_id);

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('OFFSHORE_STRIKE_OFF','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

        // documents list
        $form_prospectus = Documents::where('key', 'FORM_24')->first();
        $form_other_docs = Documents::where('key', 'OFFSHORE_STRIKE_OTHER_DOCUMENTS')->first();
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
        if($request->status == 'OFFSHORE_STRIKE_OFF_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


        //other documents (those are ususally visible on requesting by the admin )
        $regChargeGroup = DocumentsGroup::where('request_type', 'OFFSHORE_STRIKE_OFF_DOCUMENT_GROUP')->first();
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
                
                if($request->status == 'OFFSHORE_STRIKE_OFF_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        $form_other_docs = Documents::where('key', 'OFFSHORE_STRIKE_OTHER_DOCUMENTS')->first();
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
            if($company_status == 'OFFSHORE_STRIKE_OFF_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

            $path = 'company/strikeOff/'.$company_id;
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

        $party_name = CompanyMember::where('company_id',$request->companyId)
                                          ->where('id',$request->singning_party_name)
                                          ->first();

        $regNo =   $company1[0]['registration_no'];  
        $time=strtotime($request->ceasedDate);
        $date=date("d",$time);  
        $month=date("m",$time);
        $year=date("Y",$time);  

        $fieldset = array(
        'comName' => $company->name,
        'comPostfix' => $company->postfix,
        'comReg' => $company->registration_no,
        'regNo' => $regNo,
        'country' => $request->country,
        'date' => $date,
        'month' => $month,
        'year' => $year,
        'curentDate' => date('d'),
        'curentMonth' => date('m'),
        'curentYear' => date('Y'),
        'singning_party_name' => $party_name->first_name.' '.$party_name->last_name,
        
        );

        $pdf = PDF::loadView('forms/form24',$fieldset);
        $pdf->stream('form-24.pdf');

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

    function resubmit(Request $request ) {

        $company_id = $request->companyId;
        $request_id = $request->request_id;

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
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('OFFSHORE_STRIKE_OFF_RESUBMITTED', 'key')->id]);

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

    function removeOtherDoc(Request $request){

        $file_token = $request->file_token;
        
        CompanyDocuments::where('file_token', $file_token)
                        ->delete();

        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        

        ], 200);
    }

}