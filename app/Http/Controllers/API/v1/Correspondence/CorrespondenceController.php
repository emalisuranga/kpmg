<?php
namespace App\Http\Controllers\API\v1\Correspondence;
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

use App\CompanyNotices;
use App\Correspondence;

class CorrespondenceController extends Controller
{
    use _helper;

   // private $moduleRequestId = null;
    
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

       
        $request_id = null;
        if(isset($request->request_id) &&  intval($request->request_id)) {
            
            $request_id =  $this->valid_request_operation($request->companyId,  intval($request->request_id) );
        }else {
            $request_id = $this->valid_request_operation($request->companyId);
        }

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);

        if($request_id === 'INVALID-REQUEST-ID' || !$request_id ) {
            return response()->json([
                'message' => 'Invalid Request id.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        

       
        $RegisterOfChargesRecord =  Correspondence::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();

            
            
        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

        if( !( $moduleStatus === 'CORRESPONDENCE_PROCESSING' ||  $moduleStatus === 'CORRESPONDENCE_RESUBMIT' ) ) {

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


        $userPeople = People::where('id',$loginUserId)->first();

        if($userPeople->is_srilankan == 'no') {
            $userAddressId = $userPeople->foreign_address_id;
            $userAddress = Address::where('id', $userAddressId)->first();
        }else {
            $userAddressId = $userPeople->address_id;
            $userAddress = Address::where('id', $userAddressId)->first();
        }
      

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


        // $form_11 = Documents::where('key', 'FORM_22')->first();
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
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'record' => $RegisterOfChargesRecord,
                        'external_global_comment' => $external_global_comment,

                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId, $request_id),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId, $request_id),
                        'form22_payment' => $this->settings('CORRESPONDENCE_PAYMENT','key')->value,
                        'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                        'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                        'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
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


  private function valid_request_operation($company_id,$request_id = null ){

    $user = $this->getAuthUser();


    if($request_id ) {

        $request_type =  $this->settings('CORRESPONDENCE','key')->id;
        $requestCount = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->where('request_by', $user->userid)
                               ->count();
        if($requestCount != 1 ) {
            return 'INVALID-REQUEST-ID';
        }

        

        return $request_id;
    }

    $company_info = Company::where('id', $company_id)->first();
    $year = date('Y',time());
   

    $request = new CompanyChangeRequestItem;
    $request->company_id = $company_id;
    $request->request_type = $this->settings('CORRESPONDENCE','key')->id;
    $request->status = $this->settings('CORRESPONDENCE_PROCESSING','key')->id;
    $request->request_by = $user->userid;
    $request->save();
    $request_id =  $request->id;

    $record = new Correspondence;
    $record->company_id = $company_id;
    $record->request_id = $request_id;
    $record->date_of = date('Y-m-d', time());
    $record->request_by = $user->userid;
    $record->status = $this->settings('CORRESPONDENCE_PROCESSING','key')->id;
    $record->save();
    $record_id =  $record->id;

    if($record_id && $request_id ) {
        return   $request_id;
    }else{
       return  null;
    }
   
    
}

 

   function files_for_upload_docs($company_id,$request_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
        );


        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('CORRESPONDENCE','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $form_mult = Documents::where('key', 'CORRESPONDENCE_OTHER_DOCUMENTS')->first();

        $has_all_uploaded_str = '';


        //other documents (those are ususally visible on requesting by the admin )
        $regChargeGroup = DocumentsGroup::where('request_type', 'CORRESPONDENCE_DOCUMENT_GROUP')->first();
        $regChargeDocuments = Documents::where('document_group_id', $regChargeGroup->id)
                                           // ->where('key', '!=' , 'FORM_22')
                                            ->get();
        $regChargeDocumentsCount = Documents::where('document_group_id', $regChargeGroup->id)
                                              //  ->where('key', '!=' , 'FORM_22')
                                                ->count();

        if($regChargeDocumentsCount){
            foreach($regChargeDocuments as $other_doc ) {

                if($form_mult->id === $other_doc->id) {
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
                if($request->status == 'CORRESPONDENCE_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


    
    function files_for_other_docs($company_id,$request_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
        );
      //  $company_id = $request->company_id;

        if(!$company_id || !$request_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

       

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'CORRESPONDENCE_OTHER_DOCUMENTS')->first();
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
            if($company_status == 'CORRESPONDENCE_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


    function submitRecords(Request $request){
        
 
        if( !( $request->companyId && $request->request_id ) ) {
            return response()->json([
                'message' => 'Invalid Parameters.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $company_id = $request->companyId;
        $requested_id = $request->request_id;

        $request_id = $this->valid_request_operation($company_id,$requested_id);

        if(!intval($request_id)) { 

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

        $charge_record = Correspondence::where('company_id', $company_id)
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
        'message' => isset($request->notice['message']) && $request->notice['message'] ? $request->notice['message'] : null,
        'subject' => isset($request->notice['subject']) && $request->notice['subject'] ? $request->notice['subject'] : null,
        'contact_mobile' => isset($request->notice['contact_mobile']) && $request->notice['contact_mobile'] ? $request->notice['contact_mobile'] : null,
        'contact_email' => isset($request->notice['contact_email']) && $request->notice['contact_email'] ? $request->notice['contact_email'] : null,
        
    );
    Correspondence::where('company_id', $company_id)
    ->where('request_id', $request_id)
     ->update($share_summery);
     

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }

    
    function submit( Request $request ) {
        $company_id = $request->companyId;
        $requested_id = $request->request_id;


        

        $request_id = $this->valid_request_operation($company_id,$requested_id);

        if(!intval($request_id)) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
               
            ], 200);

             exit();

        }
        $company_info = Company::where('id',$company_id)->first();

       
        $RegisterOfChargesRecord =  Correspondence::where('company_id', $company_id)
            ->where('request_id', $request_id)
             ->first();

       
      
        Correspondence::where('request_id', $request_id)->update(['status' => $this->settings('CORRESPONDENCE_PENDING', 'key')->id]);
        CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('CORRESPONDENCE_PENDING', 'key')->id]);

        return response()->json([
            'message' => 'You have successfully completed your request.',
            'status' =>true,
            
        ], 200);
       



    }

    function resubmit(Request $request ) {

        $company_id = $request->companyId;

        $requested_id = $request->request_id;

        $request_id = $this->valid_request_operation($company_id,$requested_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $registerOfChargesRecord =  Correspondence::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($registerOfChargesRecord->status) && $registerOfChargesRecord->status === $this->settings('CORRESPONDENCE_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Charges registration Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = Correspondence::where('request_id', $request_id)->update(['status' => $this->settings('CORRESPONDENCE_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('CORRESPONDENCE_RESUBMITTED', 'key')->id]);

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
            $requested_id = $request->request_id;

            $request_id = $this->valid_request_operation($company_id,$requested_id);

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
        
            $path = 'correspondence/'.substr($company_id,0,2).'/'.$request_id;
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
        
        $requested_id = $request->request_id;

        $request_id = $this->valid_request_operation($company_id,$requested_id);

  
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
    
        $path = 'correspondence/other-docs/'.substr($company_id,0,2);
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


    function removeDoc(Request $request){

            $company_id = $request->companyId;
            $requested_id = $request->request_id;

            $request_id = $this->valid_request_operation($company_id,$requested_id);

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

} // end class