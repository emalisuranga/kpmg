<?php

namespace App\Http\Controllers\API\v1\Search;

use App\ChangeName;
use App\ChangeAddress;
use App\SharesDetails;
use App\CompanyChangeRequests;
use App\Company;
use App\CompanyDocuments;
use App\CompanyDocumentStatus;
use App\CompanyPostfix;
use App\CompanyChangeRequestItem;
use App\CompanyStatus;
use App\Documents;
use App\DocumentsGroup;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use App\Http\Resources\RecervationCollection;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\CountValidator\Exception;

use App\RegisterOfCharges;
use App\ShareCalls;
use App\AnnualReturn;
use App\Charges;
use App\AppointmentOfAdmins;
use App\CompanyNotices;

use App\User;
use App\People;
use App\CompanyMember;
use App\CompanyFirms;
use App\OverseasAlteration;
use App\OffshoreAlteration;
use App\Form9;
use App\ProspectusRegistration;
use App\AnnualAccounts;
use App\ShareClasses;
use App\UserAttachedCompanies;

use Auth;

use App\InlandRevenueDetails;

class ReservationController extends Controller
{
    use _helper;

    public function setName(Request $request)
    {

        \DB::beginTransaction();
        try {
            $inArray = array(
                $this->settings('COMPANY_NAME_EXPIRED', 'key')->id,
                $this->settings('COMPANY_NAME_REJECTED', 'key')->id,
                $this->settings('COMPANY_NAME_CANCELED', 'key')->id,
                $this->settings('COMPANY_NAME_PROCESSING', 'key')->id,
                $this->settings('COMPANY_NAME_CHANGE_REJECTED', 'key')->id
            );

            $company = Company::where('name', strtoupper($request['englishName']))
                ->whereNotIn('status', $inArray)
                ->first();
            if (!$company) {
                $company = new Company();
                $company->id = (int)$this->genarateCompanyId();
            }

            $company->type_id = $request['typeId'];
            $company->name = strtoupper($request['englishName']);
            $company->name_si = $request['sinhalaName'];
            $company->name_ta = $request['tamilname'];
            $company->postfix = (is_null($request['postfix']) ? '' : $request['postfix']);
            $company->abbreviation_desc = $request['abreviations'];
            $company->is_name_change = ($request['oldnumber'] != 0 ? 1 : 0);
            $company->status = $this->settings('COMPANY_NAME_PROCESSING', 'key')->id;
            $company->created_by = $this->getAuthUser()->userid;
            $company->save();

            if ($request['oldnumber'] !== 0) {

                $oldData = Company::find($request['oldnumber']);
                if ($oldData) {
                    $changeTable = new ChangeName();
                    $changeTable->new_company_id = $company->id;
                    $changeTable->old_company_id = $oldData->id;
                    $changeTable->old_type_id = $oldData->type_id;
                    $changeTable->old_name = $oldData->name;
                    $changeTable->old_name_si = $oldData->name_si;
                    $changeTable->old_name_ta = $oldData->name_ta;
                    $changeTable->old_postfix = $oldData->postfix;
                    $changeTable->old_abbreviation_desc = $oldData->abbreviation_desc;
                    $changeTable->status = $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id;
                    $changeTable->change_type = $this->settings('NAME_CHANGE', 'key')->id;
                    $changeTable->save();
                }
            }
            \DB::commit();
            return response()->json(['company' => $company->id, 'is_from_name_change' => intval($request['oldnumber'])>0 ], 200);
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function uploadFile(Request $request)
    {
        try {
            if ($request->file->getClientMimeType() != "application/pdf") {
                return response()->json(['error' => 'Invalid File Format (\*.' . $request->file->getClientMimeType() . '). only upload to (\*.pdf) file. '], 400);
            }
            $storagePath = 'company/' . substr($request->companyId, 0, 2) . '/' . substr($request->companyId, 2, 2) . '/' . $request->companyId . '/NR';
            $path = $request->file('file')->storeAs($storagePath, uniqid() . '.pdf', 'sftp');
         //   $path = $storagePath;
            if ($path) {

                $name_change_letters = array(
                    'NAME_CHANGE_LETTER_OFFSHORE',
                    'NAME_CHANGE_LETTER_UNLIMITED',
                    'NAME_CHANGE_LETTER_PUBLIC',
                    'NAME_CHANGE_LETTER_PRIVATE',
                    'NAME_CHANGE_LETTER_GUARANTEE_34',
                    'NAME_CHANGE_LETTER_GUARANTEE_32'
                );
                $name_change_reason_letters_ids = array();

                foreach($name_change_letters as $letter ) {
                    $doc = Documents::where('key', $letter)->first();
                    $name_change_reason_letters_ids[] = $doc->id;

                }

                $isNameChange = isset($request->isNameChange) && $request->isNameChange == 'yes';
                if (isset($request->fileResubmit)) {
                    $comDoc = CompanyDocuments::find($request->id);
                   // $isComDocStatusRequested =  $this->settings($comDoc->status, 'id')->key == 'DOCUMENT_REQUESTED';
                  $namechange_record =   ChangeName::where('new_company_id', $request->companyId)
                    ->whereIn('status', array(
                        $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
                        $this->settings('COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id
                    ))->first();

                    $comDoc->no_of_pages = $this->getPagesCount($request->file);
                    $comDoc->status = $this->settings('DOCUMENT_PENDING', 'key')->id;
                    $comDoc->file_token = md5(uniqid());
                    $comDoc->path = $path;
                    $comDoc->name = $request->fileName;
                  //  $comDoc->change_id = isset($namechange_record->id) && $namechange_record->id && !in_array($comDoc->document_id, $name_change_reason_letters_ids) && !$isComDocStatusRequested ? $namechange_record->id : null;
                    $comDoc->change_id = isset($namechange_record->id) && $namechange_record->id && $isNameChange ? $namechange_record->id : null;
                    $comDoc->save();


                } else {
                    $comDoc = new CompanyDocuments();
                    $comDoc->company_id = $request->companyId;
                    $comDoc->document_id = $request->docId;

                    $namechange_record =  ChangeName::where('new_company_id', $request->companyId)
                                ->whereIn('status', array(
                                    $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
                                    $this->settings('COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id
                                ))->first();
                    $comDoc->no_of_pages = $this->getPagesCount($request->file);
                    $comDoc->status = $this->settings('DOCUMENT_PENDING', 'key')->id;
                    $comDoc->file_token = md5(uniqid());
                    $comDoc->path = $path;
                    $comDoc->name = $request->fileName;
                    $comDoc->change_id = isset($namechange_record->id) && $namechange_record->id && $isNameChange ? $namechange_record->id : null;
                    $comDoc->save();
                }


                
                
                
            }

            return response()->json(['key' => $comDoc->file_token], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unauthorised'], 400);
        }
    }

    function uploadOtherDocs(Request $request){
        


        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $company_id = $request->company_id;
        $file_description = $request->fileDescription;

  
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
    
        $path = 'name-change/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $other_doc_count = CompanyDocuments::where('company_id',$company_id)
                            ->where('document_id',$file_type_id )
                            ->count();
    

      //  $get_query = CompanyDocuments::query();
      //  $get_query->where('company_id', $company_id );
     //   $get_query->where('request_id', $request_id);
      //  $get_query->where('document_id',$file_type_id);
      //  $get_query->where('multiple_id',($other_doc_count+1));
      //  $old_doc_info = $get_query->first();
    
        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
          
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
    
    
       // $query = CompanyDocuments::query();
      //  $query->where('company_id', $company_id );
      //  $query->where('request_id', $request_id);
       // $query->where('document_id',$file_type_id);
      //  $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
      //  $query->delete();

        $namechange_record =  ChangeName::where('new_company_id', $company_id)
        ->whereIn('status', array(
            $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
            $this->settings('COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id
        ))->first();
            
    
           $doc = new CompanyDocuments;
           $doc->document_id = $file_type_id;
           $doc->path = $path;
           $doc->company_id = $company_id;
           $doc->change_id = isset($namechange_record->id) && $namechange_record->id ? $namechange_record->id : null ;
           $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
           $doc->file_token = $token;
           $doc->multiple_id = mt_rand(1,1555400976);
           $doc->name = $real_file_name;
           $doc->file_description = $real_file_name;
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
        $namechange_record =  ChangeName::where('new_company_id', $company_id)
        ->whereIn('status', array(
            $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
            $this->settings('COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id
        ))->first();
            

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
    
        $path = 'name-change/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
      //   $form_other_docs = Documents::where('key', 'CHARGES_OTHER_DOCUMENTS')->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
           );

           if(isset($namechange_record->id) ){
            CompanyDocuments::where('company_id', $company_id)
            ->where('multiple_id', $multiple_id)
           // ->where('document_id',$form_other_docs->id )
            ->where('change_id',$namechange_record->id)
            ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id)
             ->update($update_arr);
           }
          
    
 
           return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'error'  => 'no',
        ], 200);
    

    }


    function uploadOtherDocsForName(Request $request){
        


        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $company_id = $request->company_id;
        $file_description = $request->fileDescription;

  
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
    
        $path = 'name-reg/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $other_doc_count = CompanyDocuments::where('company_id',$company_id)
                            ->where('document_id',$file_type_id )
                            ->count();
    

      //  $get_query = CompanyDocuments::query();
      //  $get_query->where('company_id', $company_id );
     //   $get_query->where('request_id', $request_id);
      //  $get_query->where('document_id',$file_type_id);
      //  $get_query->where('multiple_id',($other_doc_count+1));
      //  $old_doc_info = $get_query->first();
    
        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
          
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
    
    
       // $query = CompanyDocuments::query();
      //  $query->where('company_id', $company_id );
      //  $query->where('request_id', $request_id);
       // $query->where('document_id',$file_type_id);
      //  $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
      //  $query->delete();
            
    
           $doc = new CompanyDocuments;
           $doc->document_id = $file_type_id;
           $doc->path = $path;
           $doc->company_id = $company_id;
          // $doc->request_id = $request_id;
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


    function files_for_other_docs_for_name_reservation(Request $request){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'dbid' => null,

        );
        $company_id = $request->company_id;
        $type = intval($request->type);

    
        

       if(!$company_id || !$type) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'dbid' => null,
        );
       }

        $request_id = null;

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

        $other_doc_key = $this->settings($type,'id')->key.'_OTHER_DOCS';

      
        // documents list
        $form_other_docs = Documents::where('key', $other_doc_key)->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        

        $has_all_uploaded_str = '';

        $other_docs = CompanyDocuments::where('company_id', $company_id)
                                        ->where('document_id', $form_other_docs->id )
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
            if($company_status == 'COMPANY_NAME_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    $generated_files['dbid'] = $form_other_docs->id;

    return $generated_files;
    
}


    function files_for_other_docs(Request $request){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'court_status' => null,
                'court_name' => null,
                'court_case_no' => null,
                'court_date' => null,
                'court_penalty' => null,
                'court_period' => null,
                'court_discharged' => null,
                

        );
        $company_id = $request->company_id;

        if(!$company_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'court_status' => null,
                'court_name' => null,
                'court_case_no' => null,
                'court_date' => null,
                'court_penalty' => null,
                'court_period' => null,
                'court_discharged' => null,
                'penalty_charge' => 0
        );
        }

        $request_id = null;

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'NAME_CHANGE_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        

        $has_all_uploaded_str = '';
        

        $other_docs = CompanyDocuments::where('company_id', $company_id)
                                        ->where('document_id', $form_other_docs->id )
                                        ->whereNotIn('status', array(
                                               $this->settings('DOCUMENT_APPROVED','key')->id, 
                                               $this->settings('DOCUMENT_DELETED','key')->id, 
                                             )
                                        )
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
            if($company_status == 'COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

    $changeNameData = ChangeName::where('new_company_id', $company_id)->first();

    $penalty_charge = 0 ;

    if(!isset($changeNameData->resolution_date) || !$changeNameData->resolution_date ) {
        $penalty_charge = 0;
    }else {
        $form_3 = Documents::where('key', 'FORM_3')->first();
        $form3_upload_data = CompanyDocuments::where('company_id', $company_id)
                                   ->where('document_id', $form_3->id )
                                   ->orderBy('id', 'DESC')
                                   ->first();
        if( !isset($form3_upload_data->updated_at)) {
            $penalty_charge = 0;
        } else{
            $updated_date = strtotime($form3_upload_data->updated_at);
            $resolution_date = strtotime($changeNameData->resolution_date);

            $date_gaps = ($updated_date - $resolution_date) / (60*60*24);
            $date_gaps = intval($date_gaps);
      
      
            $min_date_gap = 10;
            $increment_gap_dates = 30;
            $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_NAME_CHANGE_INITIAL','key')->value );
            $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_NAME_CHANGE_INCREMENT','key')->value );
            $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_NAME_CHANGE_MAX','key')->value );
      
            $increment_gaps = 0;
            $penalty_value = 0;
            if($date_gaps < $min_date_gap ) {
                $penalty_charge = 0;
            } else {

                $increment_gaps = ( $date_gaps % $increment_gap_dates == 0 ) ? $date_gaps / $increment_gap_dates : intval($date_gaps / $increment_gap_dates) + 1;
                $penalty_value  = $penalty_value + $init_panalty;

                if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                    $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
                }

                $penalty_charge =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;

            }
        }
    }

    

    $generated_files['penalty_charge'] = $penalty_charge;
   // $generated_files['court_status'] = ($penalty_charge) ? null : 'no';

   
   if($company_status == 'COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT') {
        $generated_files['court_status'] = $changeNameData->court_status;
        $generated_files['court_name'] =  $changeNameData->court_name ;
        $generated_files['court_case_no'] = $changeNameData->court_case_no;
        $generated_files['court_date'] = $changeNameData->court_date ;
        $generated_files['court_penalty'] = $changeNameData->court_penalty;
        $generated_files['court_period'] = $changeNameData->court_period;
        $generated_files['court_discharged'] =  $changeNameData->court_discharged;
        $generated_files['status'] = $this->settings($changeNameData->status,'id')->key;
   }else{
        $generated_files['court_status'] = ($penalty_charge) ? null : 'no'; 
        $generated_files['court_name'] = ($penalty_charge) ? $changeNameData->court_name : null;
        $generated_files['court_case_no'] = ($penalty_charge) ? $changeNameData->court_case_no : null;
        $generated_files['court_date'] = ($penalty_charge) ? $changeNameData->court_date : null;
        $generated_files['court_penalty'] = ($penalty_charge) ? $changeNameData->court_penalty : null;
        $generated_files['court_period'] = ($penalty_charge) ? $changeNameData->court_period : null;
        $generated_files['court_discharged'] = ($penalty_charge) ? $changeNameData->court_discharged : null;
        $generated_files['status'] = $this->settings($changeNameData->status,'id')->key;
    }

    
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

    public function uploadFileRequest(Request $request)
    {
        try {
            
            $companyId = str_replace('"','', $request->companyId);

            if ($request->file->getClientMimeType() != "application/pdf") {
                return response()->json(['error' => 'Invalid File Format (\*.' . $request->file->getClientMimeType() . '). only upload to (\*.pdf) file. '], 400);
            }
            $storagePath = 'company/' . substr($companyId, 0, 2) . '/' . substr($companyId, 2, 2) . '/' . $companyId . '/NR';
            $path = $request->file('file')->storeAs($storagePath, uniqid() . '.pdf', 'sftp');
           // $path = $storagePath;
            if ($path) {
                if (isset($request->fileResubmit)) {
                    $comDoc = CompanyDocuments::find($request->id);
                } else {
                    $comDoc = new CompanyDocuments();
                    $comDoc->company_id = $companyId;
                    $comDoc->document_id = $request->docId;
                    $comDoc->request_id = $request->requestId;
                }
                $comDoc->no_of_pages = $this->getPagesCount($request->file);
                $comDoc->status = $this->settings('DOCUMENT_PENDING', 'key')->id;
                $comDoc->file_token = md5(uniqid());
                $comDoc->path = $path;
                $comDoc->name = $request->fileName;
                $comDoc->save();
            }

            return response()->json(['key' => $comDoc->file_token], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unauthorised'], 400);
        }
    }

    public function hasNameReSubmit(Request $request)
    {
        $has = Company::leftJoin('users', 'companies.created_by', '=', 'users.id')
            ->leftjoin('people', 'users.people_id', '=', 'people.id')
            ->where('people.email', '=', $this->clearEmail($request->email))
            ->where('companies.id', '=', $request->Id)
            ->where('companies.status', [$this->settings('COMPANY_NAME_REQUEST_TO_RESUBMIT', 'key')->id])
            ->first();

        return response()->json(['has' => is_null($has)], 200);
    }

    public function getUserData(Request $request)
    {
        $notIn = array(
            $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
            $this->settings('COMPANY_NAME_PROCESSING', 'key')->id,
        );

        $userInfo = User::where('email', $this->clearEmail($request->email))->first();
        $userId = $userInfo->id;
        $clearEmail = $this->clearEmail($request->email);
        $directorKeyId = $this->settings('DERECTOR', 'key')->id;
        $secKeyId = $this->settings('SECRETARY', 'key')->id;
        $adminKeyId = $this->settings('ADMINISTRATOR', 'key')->id;
        $otherUserId = $this->settings('LAWYER_BANKER_OTHER_STAKEHOLDER', 'key')->id;

        if (!isset($userInfo->id)) {
            return [
                'data' => array(),
                'meta' => [
                    'last_page' => 0,
                    'per_page' => 0,
                    'total' => 0,
                ],
            ];
        }

        $stakeholderRole = $userInfo->stakeholder_role;
        // module code
         $is_director_or_sec = $stakeholderRole == $directorKeyId || $stakeholderRole == $secKeyId;
      //  $is_director_or_sec = false;
        $is_admin = ($stakeholderRole == $adminKeyId);
        $is_otheruser = ($stakeholderRole == $otherUserId);

        if($is_admin) {

            $companies = User::leftJoin('company_appointed_admins', 'users.email', '=', 'company_appointed_admins.email')
                                ->leftJoin('company_admin_appointments', 'company_admin_appointments.id', '=', 'company_appointed_admins.appointment_record_id')
                                ->leftjoin('people', 'users.people_id', '=', 'people.id')
                                ->leftjoin('companies', 'company_admin_appointments.company_id', '=', 'companies.id')
                                ->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                                ->leftJoin('addresses', 'companies.address_id', '=', 'addresses.id')
                                 ->leftJoin('settings', 'settings.id', '=', 'companies.status')

                ->where('users.email', '=', $clearEmail)
                ->where('companies.status', $this->settings('COMPANY_STATUS_APPROVED', 'key')->id)
                ->orderBy('companies.updated_at', 'desc')
                ->select(
                    'companies.id',
                    'companies.name',
                    'companies.name_si',
                    'companies.name_ta',
                    'companies.type_id',
                    'companies.postfix',
                    'companies.abbreviation_desc',
                    'company_certificate.registration_no',
                    'companies.email',
                    'companies.is_name_change',
                    'companies.created_at',
                    'companies.incorporation_at',
                    'companies.name_resavation_at',
                    'companies.updated_at',
                    'companies.name_renew_at',
                    'addresses.address1',
                    'addresses.address2',
                    'addresses.city',
                    'addresses.district',
                    'addresses.province',
                    'addresses.country',
                    'addresses.postcode',
                    DB::raw('(CASE WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_APPROVED', 'key')->id . ' THEN eroc_settings.value  ELSE \'In-Progress\' END) AS status'),
                    'settings.key'
                );
                $companies->groupBy('companies.id',
                'companies.name',
                'companies.name_si',
                'companies.name_ta',
                'companies.type_id',
                'companies.postfix',
                'companies.abbreviation_desc',
                'company_certificate.registration_no',
                'companies.email',
                'companies.is_name_change',
                'companies.created_at',
                'companies.incorporation_at',
                'companies.name_resavation_at',
                'companies.updated_at',
                'companies.name_renew_at',
                'addresses.address1',
                'addresses.address2',
                'addresses.city',
                'addresses.district',
                'addresses.province',
                'addresses.country',
                'addresses.postcode',
                'companies.status',
                'settings.value',
                'settings.key');
                
        }

        else if($is_otheruser) {

            $companies = User::leftjoin('people', 'users.people_id', '=', 'people.id')
                                ->leftjoin('login_user_attached_companies', 'login_user_attached_companies.user_id', '=', 'users.id')
                                ->leftjoin('companies', 'login_user_attached_companies.company_id', '=', 'companies.id')
                                ->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                                ->leftJoin('addresses', 'companies.address_id', '=', 'addresses.id')
                                 ->leftJoin('settings', 'settings.id', '=', 'companies.status')

                ->where('users.email', '=', $clearEmail)
                ->where('companies.status', $this->settings('COMPANY_STATUS_APPROVED', 'key')->id)
                ->orderBy('companies.updated_at', 'desc')
                ->select(
                    'companies.id',
                    'companies.name',
                    'companies.name_si',
                    'companies.name_ta',
                    'companies.type_id',
                    'companies.postfix',
                    'companies.abbreviation_desc',
                    'company_certificate.registration_no',
                    'companies.email',
                    'companies.is_name_change',
                    'companies.created_at',
                    'companies.incorporation_at',
                    'companies.name_resavation_at',
                    'companies.updated_at',
                    'companies.name_renew_at',
                    'addresses.address1',
                    'addresses.address2',
                    'addresses.city',
                    'addresses.district',
                    'addresses.province',
                    'addresses.country',
                    'addresses.postcode',
                    DB::raw('(CASE WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_APPROVED', 'key')->id . ' THEN eroc_settings.value  ELSE \'In-Progress\' END) AS status'),
                    'settings.key'
                );
                $companies->groupBy('companies.id',
                'companies.name',
                'companies.name_si',
                'companies.name_ta',
                'companies.type_id',
                'companies.postfix',
                'companies.abbreviation_desc',
                'company_certificate.registration_no',
                'companies.email',
                'companies.is_name_change',
                'companies.created_at',
                'companies.incorporation_at',
                'companies.name_resavation_at',
                'companies.updated_at',
                'companies.name_renew_at',
                'addresses.address1',
                'addresses.address2',
                'addresses.city',
                'addresses.district',
                'addresses.province',
                'addresses.country',
                'addresses.postcode',
                'companies.status',
                'settings.value',
                'settings.key');
                
        }
        else if ($is_director_or_sec) {

          // $result1= CompanyMember::where('email',$this->clearEmail($request->email) )
          //  ->whereIn('designation_type', array($directorKeyId, $secKeyId))
         //   ->pluck('company_id')->toArray();
          
         $result1 = CompanyMember::leftJoin('companies', 'companies.id', '=', 'company_members.company_id')
         ->whereIn('company_members.designation_type', array($directorKeyId, $secKeyId))
         ->whereIn('companies.status', array( $this->settings('COMPANY_STATUS_APPROVED', 'key')->id,  $this->settings('COMPANY_FOREIGN_STATUS_APPROVED', 'key')->id ) )
         ->where('company_members.email', $this->clearEmail($request->email) )
         ->pluck('company_members.company_id')->toArray();


         // $result2 = CompanyFirms::where('email', $this->clearEmail($request->email))
        //  ->where('type_id', $secKeyId)
        //  ->pluck('company_id')->toArray();

        $result2 = CompanyFirms::leftJoin('companies', 'companies.id', '=', 'company_member_firms.company_id')
        ->where('company_member_firms.type_id', $secKeyId)
        ->whereIn('companies.status', array( $this->settings('COMPANY_STATUS_APPROVED', 'key')->id,  $this->settings('COMPANY_FOREIGN_STATUS_APPROVED', 'key')->id ))
        ->where('company_member_firms.email', $this->clearEmail($request->email) )
        ->pluck('company_member_firms.company_id')->toArray();

          $result3 = Company::where('created_by', $userId)
          ->whereNotIn('companies.status',array(
            $this->settings('COMPANY_NAME_PROCESSING', 'key')->id,
            $this->settings('COMPANY_NAME_CHANGE_PENDING', 'key')->id,
            $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
            $this->settings('COMPANY_NAME_CHANGE_REJECTED', 'key')->id,
            $this->settings('COMPANY_NAME_CHANGE_APPROVED', 'key')->id,
            $this->settings('COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id
          ))
          ->pluck('id')->toArray();

         

          $result3filter = array();
          if(count($result3)){
              foreach($result3 as $cid) {
                  $namechange_count = ChangeName::where('new_company_id', $cid)->count();
                  if($namechange_count) {
                      continue;
                  }
                  $result3filter[] = $cid;

              }
          }

          $arr = array_merge($result1,$result2,$result3filter);
          $arr = array_unique($arr);
          //   print_r($arr);
          //die();


           $firm_sec_count = 0;
            
           $status_approved = $this->settings('COMPANY_STATUS_APPROVED', 'key')->id;


           $companies = Company::leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                                ->leftJoin('addresses', 'companies.address_id', '=', 'addresses.id')
                                ->leftJoin('settings', 'settings.id', '=', 'companies.status')
                                ->whereIn('companies.id', $arr)

                ->orderBy('companies.updated_at', 'desc')
                ->select(
                    'companies.id',
                    'companies.name',
                    'companies.name_si',
                    'companies.name_ta',
                    'companies.type_id',
                    'companies.postfix',
                    'companies.abbreviation_desc',
                    'company_certificate.registration_no',
                    'companies.email',
                    'companies.is_name_change',
                    'companies.created_at',
                    'companies.incorporation_at',
                    'companies.name_resavation_at',
                    'companies.updated_at',
                    'companies.name_renew_at',
                    'addresses.address1',
                    'addresses.address2',
                    'addresses.city',
                    'addresses.district',
                    'addresses.province',
                    'addresses.country',
                    'addresses.postcode',
                    DB::raw('(CASE WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_NOT_RECOMMEND_FOR_APPROVAL', 'key')->id . ' THEN \'Approval Pending\'
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_REJECTED', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_APPROVED', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_REQUEST_TO_RESUBMIT', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_REQUEST_TO_RESUBMIT', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_REJECTED', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_CANCELED', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_EXPIRED', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('NAME_CHANGE', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_FOREIGN_STATUS_APPROVED', 'key')->id . ' THEN eroc_settings.value
                    WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_APPROVED', 'key')->id . ' THEN eroc_settings.value  ELSE \'In-Progress\' END) AS status'),
                    'settings.key'
                );
                $companies->groupBy('companies.id',
                'companies.name',
                'companies.name_si',
                'companies.name_ta',
                'companies.type_id',
                'companies.postfix',
                'companies.abbreviation_desc',
                'company_certificate.registration_no',
                'companies.email',
                'companies.is_name_change',
                'companies.created_at',
                'companies.incorporation_at',
                'companies.name_resavation_at',
                'companies.updated_at',
                'companies.name_renew_at',
                'addresses.address1',
                'addresses.address2',
                'addresses.city',
                'addresses.district',
                'addresses.province',
                'addresses.country',
                'addresses.postcode',
                'companies.status',
                'settings.value',
                'settings.key');

        } else {

            $companies = Company::leftJoin('users', 'companies.created_by', '=', 'users.id')
                ->leftjoin('people', 'users.people_id', '=', 'people.id')
                ->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                ->leftJoin('addresses', 'companies.address_id', '=', 'addresses.id')
                ->leftJoin('settings', 'settings.id', '=', 'companies.status')
                ->where('users.email', '=', $clearEmail)
                ->where('companies.is_name_change', '=', 0)
                ->whereNotIn('companies.status', $notIn)
                ->orderBy('companies.updated_at', 'desc')
                ->select(
                    'companies.id',
                    'companies.name',
                    'companies.name_si',
                    'companies.name_ta',
                    'companies.type_id',
                    'companies.postfix',
                    'companies.abbreviation_desc',
                    'company_certificate.registration_no',
                    'companies.email',
                    'companies.is_name_change',
                    'companies.created_at',
                    'companies.incorporation_at',
                    'companies.name_resavation_at',
                    'companies.updated_at',
                    'companies.name_renew_at',
                    'addresses.address1',
                    'addresses.address2',
                    'addresses.city',
                    'addresses.district',
                    'addresses.province',
                    'addresses.country',
                    'addresses.postcode',
                    DB::raw('(CASE WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_NOT_RECOMMEND_FOR_APPROVAL', 'key')->id . ' THEN \'Approval Pending\'
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_REJECTED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_APPROVED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_REQUEST_TO_RESUBMIT', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_REQUEST_TO_RESUBMIT', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_REJECTED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_CANCELED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_EXPIRED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('NAME_CHANGE', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_FOREIGN_STATUS_APPROVED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_APPROVED', 'key')->id . ' THEN eroc_settings.value  ELSE \'In-Progress\' END) AS status'),
                    'settings.key'
                );
        }



        if (!is_null($request->key)) {
            $companies = $companies->where(function ($query) use ($request) {
                $query->whereRaw('concat(trim(eroc_companies.name),\' \',trim(eroc_companies.postfix)) ilike (?)', '%' . $request->key . '%')
                    ->orWhere('company_certificate.registration_no', 'ilike',  $request->key . '%');
            });
        }
        $companies = $companies->orderBy('companies.updated_at', 'desc');
        $companies = $companies->orderBy('companies.id', 'desc');
       
        $companies = $companies->paginate(5);

        $in = ['COMPANY_NAME_CHANGE_APPROVED', 'COMPANY_NAME_CHANGE_REJECTED', 'COMPANY_NAME_CANCELED', 'COMPANY_NAME_REJECTED', 'COMPANY_NAME_CHANGE_PROCESSING'];
        $data = array();
        foreach ($companies as $key => $value) {

            $has_member_in_user_email = CompanyMember::where('company_id', $value->id)
                ->whereIn('designation_type', array($this->settings('SECRETARY', 'key')->id, $this->settings('DERECTOR', 'key')->id))
                ->where('status', 1)
                ->where('email', $this->clearEmail($request->email))
                ->count();
            $has_firm_in_user_email = CompanyFirms::where('company_id', $value->id)
                ->where('type_id', $this->settings('SECRETARY', 'key')->id)
                ->where('status', 1)
                ->where('email', $this->clearEmail($request->email))
                ->count();

            $offshoreStrikeOff = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id',$value->id)
                ->where('company_change_requests.request_type', $this->settings('OFFSHORE_STRIKE_OFF','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('OFFSHORE_STRIKE_OFF_APPROVED','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('OFFSHORE_STRIKE_OFF_REJECTED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if( count($offshoreStrikeOff) != 1 ) {
                    $i = 0;
                    $pages_arraysat[] = (object) array('changeReqID' => null, 'setKey' => '' ,'setValue' => '');

                    $offshoreStrikeOff = $pages_arraysat;
            }

            $overseasStrikeOff = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id',$value->id)
                ->where('company_change_requests.request_type', $this->settings('OVERSEAS_STRIKE_OFF','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('OVERSEAS_STRIKE_OFF_REJECTED','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('OVERSEAS_STRIKE_OFF_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if( count($overseasStrikeOff) != 1 ) {
                    $i = 0;
                    $pages_arraysat[] = (object) array('changeReqID' => null, 'setKey' => '' ,'setValue' => '');

                    $overseasStrikeOff = $pages_arraysat;
            }




            $namechangeData = $this->getchangeNameDetails($value->id);
            $subdata = array();
            $has = false;
            foreach ($namechangeData as $ky => $val) {
                if (!in_array($val['key'], $in) && $has == false) {
                    $has = true;
                }
                $subdata[$ky] = $val;
            }



           

            if ($has_member_in_user_email || $has_firm_in_user_email) {
                $value['this_user_company'] = true;
            } else {
                // module code
                $value['this_user_company'] = false;
               // $value['this_user_company'] = true;
            }
            $value['company_type'] =   $this->settings($value->type_id, 'id')->key;
            $data[$key] = [
                'companies' => $value,
                'namechangedata' => $subdata,
                'has' => $has,
                // module code
                 'this_user_company'=> ($is_director_or_sec || $is_admin || $is_otheruser ) ? $has_member_in_user_email || $has_firm_in_user_email || $is_admin || $is_otheruser : false,
               // 'this_user_company' => true
                'offshoreStrikeOff' => $offshoreStrikeOff,
                'overseasStrikeOff' => $overseasStrikeOff,

            ];
        }

        return [
            'data' => $data,
            'meta' => [
                'last_page' => $companies->lastPage(),
                'per_page' => $companies->perPage(),
                'total' => $companies->total(),

            ],
            //module code
             'is_director_or_sec' => ($is_director_or_sec) ? 'yes' : 'no',
            // 'is_director_or_sec' =>  'no',
             'is_admin' =>  ($is_admin) ? 'yes' : 'no',
             'is_other_user' =>    ($is_otheruser) ? 'yes' : 'no',

        ];
    }

    public function getchangeNameDetails($id)
    {
        $notIn = array(
            $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
            $this->settings('COMPANY_NAME_PROCESSING', 'key')->id,
        );

        $companies = Company::leftJoin('company_changes', 'companies.id', '=', 'company_changes.new_company_id')
            ->leftJoin('settings', 'settings.id', '=', 'companies.status')
            ->where('company_changes.old_company_id', '=', $id)
            ->whereNotIn('companies.status', $notIn)
            ->orderBy('companies.updated_at', 'desc')
            ->select(
                'companies.id',
                'company_changes.id as changeid',
                'companies.name',
                'companies.name_si',
                'companies.name_ta',
                'companies.type_id',
                'companies.postfix',
                'companies.abbreviation_desc',
                'companies.email',
                'companies.is_name_change',
                'companies.name_resavation_at',
                'companies.number_of_resolution_dates as resolution_dates',
                'company_changes.resolution_date',
                'companies.created_at',
                'companies.updated_at',
                'companies.name_renew_at',
                DB::raw('(CASE WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_NOT_RECOMMEND_FOR_APPROVAL', 'key')->id . ' THEN \'Approval Pending\'
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_REJECTED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_APPROVED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_REQUEST_TO_RESUBMIT', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_REQUEST_TO_RESUBMIT', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_REJECTED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_CANCELED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_EXPIRED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_CHANGE_APPROVED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_NAME_CHANGE_REJECTED', 'key')->id . ' THEN eroc_settings.value
                WHEN "eroc_companies"."status" = ' . $this->settings('COMPANY_STATUS_APPROVED', 'key')->id . ' THEN eroc_settings.value  ELSE \'In-Progress\' END) AS status'),
                'settings.key'
            );

        return $companies->get()->toArray();
    }

    public function getNameReservationData(Request $request)
    {
        try {


            $company = Company::leftJoin('addresses', 'companies.address_id', '=', 'addresses.id')
                ->leftJoin('settings', 'settings.id', '=', 'companies.status')
                ->leftJoin('settings as s1', 's1.id', '=', 'companies.type_id')
                ->where('companies.id', '=', $request->id)
                ->select(
                    'companies.id',
                    'companies.name',
                    'companies.name_si',
                    'companies.name_ta',
                    'companies.type_id',
                    'companies.postfix',
                    'companies.abbreviation_desc',
                    'companies.email',
                    'companies.created_at',
                    'companies.updated_at',
                    'addresses.address1',
                    'addresses.address2',
                    'addresses.city',
                    'addresses.district',
                    'addresses.province',
                    'addresses.country',
                    'addresses.postcode',
                    'settings.value as status',
                    's1.key as typeKey',
                    'settings.key'
                )->firstOrFail();

            $notIn = array(
                $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                $this->settings('DOCUMENT_REQUESTED', 'key')->id,
                $this->settings('DOCUMENT_DELETED', 'key')->id,
            );

            $req = array(
                $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                $this->settings('DOCUMENT_REQUESTED', 'key')->id,
                $this->settings('DOCUMENT_PENDING', 'key')->id,
            );

            $companyDocument = CompanyDocuments::leftJoin('documents', 'documents.id', '=', 'company_documents.document_id')
                ->where('company_id', $request->id)
                ->where('documents.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                ->whereNotIn('company_documents.status', $notIn)
                ->get();

            $companyResubmitedDoc = CompanyDocuments::leftJoin('company_document_status', 'company_documents.id', '=', 'company_document_status.company_document_id')
                ->leftjoin('documents', 'documents.id', '=', 'company_documents.document_id')
                ->where('company_id', $request->id)
                ->where(function ($query) {
                     $query->whereNotIn('documents.key', array('NAME_CHANGE_OTHER_DOCUMENTS'));
                     $query->orWhereNull('documents.key'); })
                ->whereIn('company_documents.status', $req)
                ->select(
                    'company_documents.id',
                    'documents.name',
                    'company_document_status.comments',
                    'company_documents.file_token'
                )->get();
            $otherDocsForNameChangeResubmit  = Documents::where('key', 'NAME_CHANGE_OTHER_DOCUMENTS')->first();

            

            $user = $this->getAuthUser();
            $user_id = $user->userid;
            $userInfo = User::where('id' ,$user_id)->first();
           

            if($userInfo->stakeholder_role ==  $this->settings('LAWYER_BANKER_OTHER_STAKEHOLDER', 'key')->id ) {

              //  $attachedCompanies = UserAttachedCompanies::where('user_id', $user_id)->pluck('company_id')->toArray();
                $attachedRequestIds = CompanyChangeRequestItem::where('request_by', $user_id )
                ->where('request_type', $this->settings('CHARGES_REGISTRATION','key')->id )->pluck('id')->toArray();
                $accepted_request_statuses = array(
                    $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id,
                    $this->settings('CHARGES_REGISTRATION_REJECTED','key')->id,
                    $this->settings('CHARGES_REGISTRATION_RESUBMIT','key')->id
                );


                $comment = CompanyStatus::join('settings', 'settings.id', '=', 'company_statuses.status')
                ->where('company_statuses.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                ->whereIn('company_statuses.status', $accepted_request_statuses)
                ->whereIn('company_statuses.request_id', $attachedRequestIds)
                ->where('company_statuses.company_id', $request->id)
                ->select(
                    'settings.value as name',
                    'company_statuses.comments',
                    'company_statuses.updated_at'
                )
                ->orderBy('created_at', 'desc')
                ->get();

            } 
            else if($userInfo->stakeholder_role ==  $this->settings('ADMINISTRATOR', 'key')->id ) {

                // $attachedCompanies = UserAttachedCompanies::where('user_id', $user_id)->pluck('company_id')->toArray();
                $attachedRequestIds = CompanyChangeRequestItem::where('request_by', $user_id )
                ->where('request_type', $this->settings('APPOINTMENT_OF_ADMIN','key')->id )->pluck('id')->toArray();
                $accepted_request_statuses = array(
                    $this->settings('APPOINTMENT_OF_ADMIN_APPROVED','key')->id,
                    $this->settings('APPOINTMENT_OF_ADMIN_REJECTED','key')->id,
                    $this->settings('APPOINTMENT_OF_ADMIN_RESUBMIT','key')->id
                );


                $comment = CompanyStatus::join('settings', 'settings.id', '=', 'company_statuses.status')
                ->where('company_statuses.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                ->whereIn('company_statuses.status', $accepted_request_statuses)
                ->whereIn('company_statuses.request_id', $attachedRequestIds)
                ->where('company_statuses.company_id', $request->id)
                ->select(
                    'settings.value as name',
                    'company_statuses.comments',
                    'company_statuses.updated_at'
                )
                ->orderBy('created_at', 'desc')
                ->get();

            } else {

                $comment = CompanyStatus::join('settings', 'settings.id', '=', 'company_statuses.status')
                ->where('company_statuses.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                ->where('company_statuses.company_id', $request->id)
                ->select(
                    'settings.value as name',
                    'company_statuses.comments',
                    'company_statuses.updated_at'
                )
                ->orderBy('created_at', 'desc')
                ->get();


            }
           
            
            

            

            $changedetails = ChangeAddress::leftJoin('settings', 'settings.id', '=', 'address_changes.status')
                ->leftJoin('company_statuses', function ($join) {
                    $join->on('address_changes.id', '=', 'company_statuses.change_id')
                        ->where('company_statuses.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);
                })
                ->where('type_id', $request->id)
                ->where('address_changes.change_type', $this->settings('COMPANY_ADDRESS_CHANGE', 'key')->id)
                ->where('address_changes.status', '!=', $this->settings('COMPANY_ADDRESS_CHANGE_APPROVED', 'key')->id)
                ->where('address_changes.status', '!=', $this->settings('COMPANY_ADDRESS_CHANGE_REJECTED', 'key')->id)
                ->orderBy('address_changes.id', 'DESC')
                ->orderBy('company_statuses.id', 'DESC')
                ->limit(1)
                ->get(['address_changes.id', 'company_statuses.updated_at as updated_at', 'address_changes.new_address_id', 'address_changes.address_effect_on_date', 'settings.value as value', 'company_statuses.comments as comment', 'settings.key as setKey']);

            if (count($changedetails) != 1) {
                $i = 0;
                $pages_array[] = (object)array('changeReqID' => null, 'setKey' => '', 'setValue' => '');

                $changedetails = $pages_array;
            }


            $acadchangedetails = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id', $request->id)
                ->where('company_change_requests.request_type', $this->settings('ACCOUNTING_ADDRESS_CHANGE', 'key')->id)
                ->where('company_change_requests.status', '!=', $this->settings('ACCOUNTING_ADDRESS_CHANGE_APPROVED', 'key')->id)
                ->where('company_change_requests.status', '!=', $this->settings('ACCOUNTING_ADDRESS_CHANGE_REJECTED', 'key')->id)
                ->orderBy('company_change_requests.created_at', 'DESC')
                ->limit(1)
                ->get(['company_change_requests.id', 'company_change_requests.request_type', 'settings.value as value', 'settings.key as setKey']);

            if (count($acadchangedetails) != 1) {
                $i = 0;
                $pages_arrayac[] = (object)array('changeReqID' => null, 'setKey' => '', 'setValue' => '');

                $acadchangedetails = $pages_arrayac;
            }

            $bsdchangedetails = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('company_item_changes', 'company_item_changes.request_id', '=', 'company_change_requests.id')
                ->leftJoin('company_balance_sheet_dates', 'company_item_changes.item_id', '=', 'company_balance_sheet_dates.id')
                ->where('company_change_requests.company_id', $request->id)
                ->where('company_change_requests.request_type', $this->settings('BALANCE_SHEET_DATE_CHANGE', 'key')->id)
                ->where('company_change_requests.status', '!=', $this->settings('BALANCE_SHEET_DATE_CHANGE_APPROVED', 'key')->id)
                ->where('company_change_requests.status', '!=', $this->settings('BALANCE_SHEET_DATE_CHANGE_REJECTED', 'key')->id)
                ->orderBy('company_change_requests.created_at', 'DESC')
                ->limit(1)
                ->get(['company_change_requests.id', 'company_change_requests.request_type', 'settings.value as value', 'settings.key as setKey', 'company_balance_sheet_dates.id as bsdid', 'company_item_changes.id as bsdchangeid']);

            if (count($bsdchangedetails) != 1) {
                $i = 0;
                $pages_arraybsd[] = (object)array('changeReqID' => null, 'setKey' => '', 'setValue' => '');

                $bsdchangedetails = $pages_arraybsd;
            }

            $rradchangedetails = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id', $request->id)
                ->where('company_change_requests.request_type', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE', 'key')->id)
                ->where('company_change_requests.status', '!=', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_APPROVED', 'key')->id)
                ->where('company_change_requests.status', '!=', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_REJECTED', 'key')->id)
                ->orderBy('company_change_requests.created_at', 'DESC')
                ->limit(1)
                ->get(['company_change_requests.id', 'company_change_requests.request_type', 'settings.value as value', 'settings.key as setKey']);

            if (count($rradchangedetails) != 1) {
                $i = 0;
                $pages_arrayrr[] = (object)array('changeReqID' => null, 'setKey' => '', 'setValue' => '');

                $rradchangedetails = $pages_arrayrr;
            }

            $satischangedetails = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('SATISFACTION_CHARGE_CHANGE','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('COMPANY_CHANGE_APPROVED','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('COMPANY_CHANGE_REJECTED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if( count($satischangedetails) != 1 ) {
                    $i = 0;
                    $pages_arraysat[] = (object) array('changeReqID' => null, 'setKey' => '' ,'setValue' => '');

                    $satischangedetails = $pages_arraysat;
            }

                  $otherscourtorder = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('OTHERS_COURT_ORDER','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('OTHERS_COURT_ORDER_REJECTED','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('OTHERS_COURT_ORDER_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if( count($otherscourtorder) != 1 ) {
                    $i = 0;
                    $pages_arraysat[] = (object) array('changeReqID' => null, 'setKey' => '' ,'setValue' => '');

                    $otherscourtorder = $pages_arraysat;
            }

            $priorApproval = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
            ->where('company_change_requests.company_id',$request->id)
            ->where('company_change_requests.request_type', $this->settings('PRIOR_APPROVAL','key')->id)
            ->where('company_change_requests.status','!=', $this->settings('PRIOR_APPROVAL_REJECTED','key')->id)
            ->where('company_change_requests.status','!=', $this->settings('PRIOR_APPROVAL_APPROVED','key')->id)
            ->orderBy('company_change_requests.created_at','DESC')
            ->limit(1)
            ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

            if( count($priorApproval) != 1 ) {
                $i = 0;
                $pages_arraysat[] = (object) array('changeReqID' => null, 'setKey' => '' ,'setValue' => '');

                $priorApproval = $pages_arraysat;
        }

              $affairs = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('STATEMENT_OF_AFFAIRS','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('STATEMENT_OF_AFFAIRS_APPROVED','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('STATEMENT_OF_AFFAIRS_REJECTED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if( count($affairs) != 1 ) {
                    $i = 0;
                    $pages_arraysat[] = (object) array('changeReqID' => null, 'setKey' => '' ,'setValue' => '');

                    $affairs = $pages_arraysat;
            }

           /* $pages_arraysat[] = (object) array('changeReqID' => null, 'setKey' => '' ,'setValue' => '');

            $affairs = $pages_arraysat;*/



            $debentureChangedetails = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('companies', function ($join) {
                    $join->on('company_change_requests.company_id', '=', 'companies.id')
                        ->where('companies.status', '=', $this->settings('COMPANY_STATUS_APPROVED', 'key')->id);
                })
                ->where('company_change_requests.company_id', $request->id)
                ->where('company_change_requests.request_type', $this->settings('COMPANY_DEBENTURES', 'key')->id)
                ->where('company_change_requests.status','!=', $this->settings('COMPANY_CHANGE_APPROVED','key')->id)
                ->where('company_change_requests.status','!=', $this->settings('COMPANY_CHANGE_REJECTED','key')->id)
                ->orderBy('company_change_requests.id', 'DESC')
                ->select(
                    'company_change_requests.id as changeReqID',
                    'settings.key as setKey',
                    'settings.value as setValue'
                )
                ->first();

            if (!isset($debentureChangedetails->changeReqID)) {
                $debentureChangedetails = array('changeReqID' => null, 'setKey' => '', 'setValue' => '');
            }

          /*  $sharesChangedetails = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('companies', function ($join) {
                    $join->on('company_change_requests.company_id', '=', 'companies.id')
                        ->where('companies.status', '=', $this->settings('COMPANY_STATUS_APPROVED', 'key')->id);
                })
                ->where('company_change_requests.company_id', $request->id)
                ->where('company_change_requests.request_type', $this->settings('COMPANY_ISSUE_OF_SHARES', 'key')->id)
                ->orderBy('company_change_requests.id', 'DESC')
                ->select(
                    'company_change_requests.id as changeReqID',
                    'settings.key as setKey',
                    'settings.value as setValue'
                )
                ->first();
            if (!isset($sharesChangedetails->changeReqID)) {
                $sharesChangedetails = array('changeReqID' => null, 'setKey' => '', 'setValue' => '');
            }*/


            $issue_shares_accepted_request_statuses = array(
                $this->settings('COMPANY_ISSUE_OF_SHARES_APPROVED', 'key')->id,
                $this->settings('COMPANY_ISSUE_OF_SHARES_REJECTED', 'key')->id
            );
            $request_type =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
            $issue_shares_record_count = CompanyChangeRequestItem::where('request_type',$request_type)
                                        ->where('company_id', $request->id)
                                        ->whereNotIn('status', $issue_shares_accepted_request_statuses )
                                        ->count();
            $sharesChangedetails = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '' );
            if( $issue_shares_record_count === 1 ) {
                   // $record = ShareClasses::where('company_id', $request->id)
                  //  ->whereNotIn('status', $issue_shares_accepted_request_statuses )
                   //  ->first();
                    $record = CompanyChangeRequestItem::where('request_type',$request_type)
                    ->where('company_id', $request->id)
                    ->whereNotIn('status', $issue_shares_accepted_request_statuses )
                    ->first();
                $sharesChangedetails['changeReqID'] = $record['request_id'];
                $sharesChangedetails['setKey'] = $this->settings($record['status'],'id')->key;
                $sharesChangedetails['setValue'] = $this->settings($record['status'],'id')->value;
            }




            $call_shares_accepted_request_statuses = array(
                $this->settings('CALLS_ON_SHARES_APPROVED', 'key')->id,
                $this->settings('CALLS_ON_SHARES_REJECTED', 'key')->id
            );

            $call_shares_record_count = ShareCalls::where('company_id',  $request->id)
                                          ->whereNotIn('status', $call_shares_accepted_request_statuses )
                                           ->count();
            $call_shares_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '' );
            if( $call_shares_record_count === 1 ) {
                    $record = ShareCalls::where('company_id', $request->id)
                    ->whereNotIn('status', $call_shares_accepted_request_statuses )
                     ->first();
                $call_shares_record['changeReqID'] = $record['request_id'];
                $call_shares_record['setKey'] = $this->settings($record['status'],'id')->key;
                $call_shares_record['setValue'] = $this->settings($record['status'],'id')->value;
            }



            $annual_return_request_statuses = array(
                $this->settings('COMPANY_ANNUAL_RETURN_APPROVED', 'key')->id,
                $this->settings('COMPANY_ANNUAL_RETURN_REJECTED', 'key')->id
            );

            $annual_return_record_count = AnnualReturn::where('company_id',  $request->id)
                ->whereNotIn('status', $annual_return_request_statuses)
                ->count();
         //   $annual_return_record = array('changeReqID' => null, 'setKey' => '', 'setValue' => '');

            $company_info = Company::where('id', $request->id)->first();           
            $annual_return_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '', 'companyType' => '' );
            $annual_return_record['companyType'] = $this->settings($company_info->type_id,'id')->key;


            if ($annual_return_record_count === 1) {
                $record = AnnualReturn::where('company_id', $request->id)
                    ->whereNotIn('status', $annual_return_request_statuses)
                    ->first();
                $annual_return_record['changeReqID'] = $record['request_id'];
                $annual_return_record['setKey'] = $this->settings($record['status'], 'id')->key;
                $annual_return_record['setValue'] = $this->settings($record['status'], 'id')->value;
            }




            $director_sec_change_record = CompanyChangeRequestItem::where('company_id', $request->id)
                ->where('request_type', $this->settings('DIRECTOR_SECRETORY_CHANGE', 'key')->id)
                ->whereNotIn(
                    'status',
                    array(
                        $this->settings('COMPANY_CHANGE_APPROVED', 'key')->id,
                        $this->settings('COMPANY_CHANGE_REJECTED', 'key')->id,


                    )
                )->first();
            $director_sec_record = array('changeReqID' => null, 'setKey' => '', 'setValue' => '');
            if (isset($director_sec_change_record['id'])) {

                $director_sec_record['changeReqID'] = $director_sec_change_record['id'];
                $director_sec_record['setKey'] = $this->settings($director_sec_change_record['status'], 'id')->key;
                $director_sec_record['setValue'] = $this->settings($director_sec_change_record['status'], 'id')->value;
            }





            $reg_charges_accepted_request_statuses = array(
                $this->settings('REGISTER_OF_CHARGES_APPROVED', 'key')->id,
                $this->settings('REGISTER_OF_CHARGES_REJECTED', 'key')->id
            );

            $reg_charges_record_count = RegisterOfCharges::where('company_id',  $request->id)
                                          ->whereNotIn('status', $reg_charges_accepted_request_statuses )
                                           ->count();
            $reg_charges_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '' );
            if( $reg_charges_record_count === 1 ) {
                    $record = RegisterOfCharges::where('company_id', $request->id)
                    ->whereNotIn('status', $reg_charges_accepted_request_statuses )
                     ->first();
                    $reg_charges_record['changeReqID'] = $record['request_id'];
                    $reg_charges_record['setKey'] = $this->settings($record['status'],'id')->key;
                    $reg_charges_record['setValue'] = $this->settings($record['status'],'id')->value;
            }



            $charges_registration_accepted_request_statuses = array(
                $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id,
                $this->settings('CHARGES_REGISTRATION_REJECTED','key')->id
            );
            
            $charges_registration_record_count = Charges::where('company_id',  $request->id)
                                        ->whereNotIn('status', $charges_registration_accepted_request_statuses )
                                        ->count();
            $charges_registration_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '' );
            if( $charges_registration_record_count === 1 ) {
                    $record = Charges::where('company_id', $request->id)
                    ->whereNotIn('status', $charges_registration_accepted_request_statuses )
                    ->first();
                    $charges_registration_record['changeReqID'] = $record['request_id'];
                    $charges_registration_record['setKey'] = $this->settings($record['status'],'id')->key;
                    $charges_registration_record['setValue'] = $this->settings($record['status'],'id')->value;

            }
           
           
            
            $stated_capital_record_item = CompanyChangeRequestItem::where('company_id', $request->id)
                ->where('request_type', $this->settings('REDUCTION_OF_CAPITAL', 'key')->id)
                ->whereNotIn(
                    'status',
                    array(
                        $this->settings('COMPANY_CHANGE_APPROVED', 'key')->id,
                        $this->settings('COMPANY_CHANGE_REJECTED', 'key')->id,


                    )
                )->first();
            $stated_capital = array('changeReqID' => null, 'setKey' => '', 'setValue' => '');
            if (isset($stated_capital_record_item['id'])) {

                $stated_capital['changeReqID'] = $stated_capital_record_item['id'];
                $stated_capital['setKey'] = $this->settings($stated_capital_record_item['status'], 'id')->key;
                $stated_capital['setValue'] = $this->settings($stated_capital_record_item['status'], 'id')->value;
            }



            $appoint_admin_accepted_request_statuses = array(
                $this->settings('APPOINTMENT_OF_ADMIN_APPROVED','key')->id,
                $this->settings('APPOINTMENT_OF_ADMIN_REJECTED','key')->id
            );
           
            $appoint_admin_record_count = AppointmentOfAdmins::where('company_id',  $request->id)
                                        ->whereNotIn('status', $appoint_admin_accepted_request_statuses )
                                        ->count();
            $appoint_admin_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '' );
            if( $appoint_admin_record_count === 1 ) {
                    $record = AppointmentOfAdmins::where('company_id', $request->id)
                    ->whereNotIn('status', $appoint_admin_accepted_request_statuses )
                    ->first();
                    $appoint_admin_record['changeReqID'] = $record['request_id'];
                    $appoint_admin_record['setKey'] = $this->settings($record['status'],'id')->key;
                    $appoint_admin_record['setValue'] = $this->settings($record['status'],'id')->value;
            }
            

            $notice_record_item = CompanyChangeRequestItem::where('company_id', $request->id)
            ->where('request_type', $this->settings('COMPANY_NOTICES','key')->id)
            ->whereNotIn('status', array(
                        $this->settings('COMPANY_NOTICE_APPROVED','key')->id,
                        $this->settings('COMPANY_NOTICE_REJECTED','key')->id,
                                            
                                                        
                                    ) 
                        )->first();
            $notice = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '' );
            if(isset($notice_record_item['id'])) {

            $notice['changeReqID'] = $notice_record_item['id'];
            $notice['setKey'] = $this->settings($notice_record_item['status'],'id')->key;
            $notice['setValue'] = $this->settings($notice_record_item['status'],'id')->value;

            }

            $name_change_of_overseas_item = CompanyChangeRequestItem::where('company_id', $request->id)
            ->where('request_type', $this->settings('OVERSEAS_NAME_CHANGE','key')->id)
            ->whereNotIn('status', array(
                        $this->settings('OVERSEAS_NAME_CHANGE_APPROVED','key')->id,
                        $this->settings('OVERSEAS_NAME_CHANGE_REJECTED','key')->id,
                        ) 
            )->first();
            $company_info = Company::where('id', $request->id)->first();           
            $oversease_namechange = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '', 'companyType' => '' );
            $oversease_namechange['companyType'] = $this->settings($company_info->type_id,'id')->key;
            if(isset($name_change_of_overseas_item['id'])) {

                $oversease_namechange['changeReqID'] = $name_change_of_overseas_item['id'];
                $oversease_namechange['setKey'] = $this->settings($name_change_of_overseas_item['status'],'id')->key;
                $oversease_namechange['setValue'] = $this->settings($name_change_of_overseas_item['status'],'id')->value;

            }

            

            $overseas_alt_statuses = array(
                $this->settings('OVERSEAS_ALTERATIONS_APPROVED','key')->id,
                $this->settings('OVERSEAS_ALTERATIONS_REJECTED','key')->id
            );
            
            $overseas_alt_record_count = OverseasAlteration::where('company_id',  $request->id)
                                        ->whereNotIn('status', $overseas_alt_statuses )
                                        ->count();


            $company_info = Company::where('id', $request->id)->first();           
            $overseas_alt_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '', 'companyType' => '' );
            $overseas_alt_record['companyType'] = $this->settings($company_info->type_id,'id')->key;


            if( $overseas_alt_record_count === 1 ) {
                    $record = OverseasAlteration::where('company_id', $request->id)
                    ->whereNotIn('status', $overseas_alt_statuses )
                    ->first();
                    $overseas_alt_record['changeReqID'] = $record['request_id'];
                    $overseas_alt_record['setKey'] = $this->settings($record['status'],'id')->key;
                    $overseas_alt_record['setValue'] = $this->settings($record['status'],'id')->value;
            }


            $offshore_alt_statuses = array(
                $this->settings('OFFSHORE_ALTERATIONS_APPROVED','key')->id,
                $this->settings('OFFSHORE_ALTERATIONS_REJECTED','key')->id
            );
            
            $offshore_alt_record_count = OffshoreAlteration::where('company_id',  $request->id)
                                        ->whereNotIn('status', $offshore_alt_statuses )
                                        ->count();


            $company_info = Company::where('id', $request->id)->first();           
            $offshore_alt_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '', 'companyType' => '' );
            $offshore_alt_record['companyType'] = $this->settings($company_info->type_id,'id')->key;


            if( $offshore_alt_record_count === 1 ) {
                    $record = OffshoreAlteration::where('company_id', $request->id)
                    ->whereNotIn('status', $offshore_alt_statuses )
                    ->first();
                    $offshore_alt_record['changeReqID'] = $record['request_id'];
                    $offshore_alt_record['setKey'] = $this->settings($record['status'],'id')->key;
                    $offshore_alt_record['setValue'] = $this->settings($record['status'],'id')->value;
            }




            $form9_statuses = array(
                $this->settings('COMPANY_SHARE_FORM9_APPROVED','key')->id,
                $this->settings('COMPANY_SHARE_FORM9_REJECTED','key')->id
            );
            
            $form9_record_count = Form9::where('company_id',  $request->id)
                                        ->whereNotIn('status', $form9_statuses )
                                        ->count();


            $company_info = Company::where('id', $request->id)->first();           
            $form9_record = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '', 'companyType' => '' );
            $form9_record['companyType'] = $this->settings($company_info->type_id,'id')->key;


            if( $form9_record_count === 1 ) {
                    $record = Form9::where('company_id', $request->id)
                    ->whereNotIn('status', $form9_statuses )
                    ->first();
                    $form9_record['changeReqID'] = $record['request_id'];
                    $form9_record['setKey'] = $this->settings($record['status'],'id')->key;
                    $form9_record['setValue'] = $this->settings($record['status'],'id')->value;
            }




            $prospectus_statuses = array(
                $this->settings('PROSPECTUS_OF_REG_APPROVED','key')->id,
                $this->settings('PROSPECTUS_OF_REG_REJECTED','key')->id
            );
            
            $prospectus_record_count = ProspectusRegistration::where('company_id',  $request->id)
                                        ->whereNotIn('status', $prospectus_statuses )
                                        ->count();


            $company_info = Company::where('id', $request->id)->first();           
            $prospectus = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '', 'companyType' => '' );
            $prospectus['companyType'] = $this->settings($company_info->type_id,'id')->key;


            if( $prospectus_record_count === 1 ) {
                    $record = ProspectusRegistration::where('company_id', $request->id)
                    ->whereNotIn('status', $prospectus_statuses )
                    ->first();
                    $prospectus['changeReqID'] = $record['request_id'];
                    $prospectus['setKey'] = $this->settings($record['status'],'id')->key;
                    $prospectus['setValue'] = $this->settings($record['status'],'id')->value;
            }



            $annual_accounts_statuses = array(
                $this->settings('ANNUAL_ACCOUNTS_APPROVED','key')->id,
                $this->settings('ANNUAL_ACCOUNTS_REJECTED','key')->id
            );
            
            $annual_accounts_record_count = AnnualAccounts::where('company_id',  $request->id)
                                        ->whereNotIn('status', $annual_accounts_statuses )
                                        ->count();


            $company_info = Company::where('id', $request->id)->first();           
            $annual_accounts = array('changeReqID'=>null, 'setKey'=>'', 'setValue'=> '', 'companyType' => '' );
            $annual_accounts['companyType'] = $this->settings($company_info->type_id,'id')->key;


            if( $annual_accounts_record_count === 1 ) {
                    $record = AnnualAccounts::where('company_id', $request->id)
                    ->whereNotIn('status', $annual_accounts_statuses )
                    ->first();
                    $annual_accounts['changeReqID'] = $record['request_id'];
                    $annual_accounts['setKey'] = $this->settings($record['status'],'id')->key;
                    $annual_accounts['setValue'] = $this->settings($record['status'],'id')->value;
            }

 
            $special_resolution_item = CompanyChangeRequestItem::where('company_id', $request->id)
                ->where('request_type', $this->settings('SPECIAL_RESOLUTION', 'key')->id)
                ->whereNotIn(
                    'status',
                    array(
                        $this->settings('COMPANY_CHANGE_APPROVED', 'key')->id,
                        $this->settings('COMPANY_CHANGE_REJECTED', 'key')->id,


                    )
                )->first();
            $special_res = array('changeReqID' => null, 'setKey' => '', 'setValue' => '');
            if (isset($special_resolution_item['id'])) {

                $special_res['changeReqID'] = $special_resolution_item['id'];
                $special_res['setKey'] = $this->settings($special_resolution_item['status'], 'id')->key;
                $special_res['setValue'] = $this->settings($special_resolution_item['status'], 'id')->value;
            }


            /***********ird info ************/
            $ird_info = null;
            $ird_count = InlandRevenueDetails::where('company_id', $request->id)->count();
            if($ird_count){
                $ird_info = InlandRevenueDetails::where('company_id', $request->id)->first();

            }
 
              
            $userInfo = User::where('email', $this->clearEmail( Auth::guard('api')->user()->email) )->first();
            $stakeholder_key = isset($userInfo->stakeholder_role) && intval($userInfo->stakeholder_role) ?  $this->settings($userInfo->stakeholder_role,'id')->key  : '';

            $response = [
                'companyInfor' => $company,
                'companyResubmitedDoc' => $companyResubmitedDoc,
                'otherDocsForNameChangeResubmit' => $otherDocsForNameChangeResubmit,
                'companyDocument' => $companyDocument,
                'comments' => $comment,
                'changedetails' => $changedetails,
                'acadchangedetails' => $acadchangedetails,
                'rradchangedetails' => $rradchangedetails,
                'satischangedetails' => $satischangedetails,
                'otherscourtorder' => $otherscourtorder,
                 'priorApproval' => $priorApproval,
                'affairs' => $affairs,
                'bsdchangedetails' => $bsdchangedetails,
                'debentureChangedetails' => $debentureChangedetails,
                'sharesChangedetails' => $sharesChangedetails,
                'regChargesDetails' => $reg_charges_record,
                'callShareDetails' =>  $call_shares_record,
                'annualReturnDetails' => $annual_return_record,
                'directorSecChangeDetails' => $director_sec_record,
                'statedCapitalDetails' => $stated_capital,
                'overseasNameChangeNotice' => $oversease_namechange,
                'chargesRegistratioRecord' => $charges_registration_record,
                'statedCapitalDetails' => $stated_capital,
                'noticeDetails' => $notice,
                'overseaseAltDetails' => $overseas_alt_record,
                'offshoreAltDetails' => $offshore_alt_record,
                'appintAdminDetails' => $appoint_admin_record,
                'noticeDetails' => $notice,
                'form9Details' => $form9_record,
                'prospectusDetails' => $prospectus,
                'annualAccountsDetails' => $annual_accounts,
                'specialResolutionDetails' => $special_res,

                'irdTIN' => isset($ird_info->taxpayer_identification_number) ? $ird_info->taxpayer_identification_number : '',
                'irdStatus' => isset($ird_info->status) ? $this->settings($ird_info->status,'id')->key : '',
                'irdRejectMessage'=>isset($ird_info->rejected_resion) ? nl2br($ird_info->rejected_resion) : '',

                'stakeholder_key' => $stakeholder_key,
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function setNameReSubmit(Request $request)
    {
        try {
            $company = Company::find($request->data['refId']);
            $company->name = $request->data['companyName'];
            $company->name_si = $request->data['sinhalaName'];
            $company->name_ta = $request->data['tamileName'];
            $company->abbreviation_desc = $request->data['abbreviation_desc'];
            $company->status = $this->settings('COMPANY_NAME_RESUBMITTED', 'key')->id;
            $company->save();

            return response()->json(['success' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getSearchResult(Request $request)
    {
        return new RecervationCollection(
            Company::where(\DB::raw("concat(eroc_companies.name,' ',eroc_companies.postfix)"), 'ilike', '%' . $request->key . '%')
                ->where('email', $this->clearEmail($request->email))
                ->paginate(10)
        );
    }

    public function isGetfix(Request $request)
    {
        try {
            $fix = CompanyPostfix::where('company_type_id', $request->hasfix)->get();
            return response()->json($fix->isEmpty(), 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function nameCancel(Request $request)
    {

        \DB::beginTransaction();
        try {
            $this->validateId($request->all())->validate();
            $company = Company::find($request->id);
            $company->status = $this->settings('COMPANY_NAME_CANCELED', 'key')->id;
            $company->user_comment = $request->text;
            if ($company->save()) {

                $changeTable = ChangeName::where('new_company_id', $request->id)->first();

                if ($changeTable != null) {
                    $changeTable->status =  $this->settings('COMPANY_NAME_CANCELED', 'key')->id;
                    $changeTable->save();

                    $oldCom = Company::find($changeTable->old_company_id);
                    $oldCom->status = $this->settings('COMPANY_STATUS_APPROVED', 'key')->id;
                    $oldCom->save();
                }

                \DB::commit();
                return response()->json(['success' => 'success'], 200);
            }
        } catch (\ErrorException $e) {
            \DB::rollBack();
            return response()->json(['excepation' => $e->getMessage()]);
        }
    }

    protected function validateId(array $request)
    {
        return Validator::make($request, [
            'id' => 'required',
            'text' => 'required',
        ]);
    }

    public function getResubmitDoc(Request $request)
    {
      //  $dataUp = CompanyDocuments::where('company_id', $request->id)
      //      ->where('status', $this->settings('DOCUMENT_PENDING', 'key')->id)
      //      ->update(['status' => $this->settings('DOCUMENT_DELETED', 'key')->id]);
     //   if ($dataUp) {
      //      return response()->json(['status' => true], 200);
      //  }
      //  return response()->json(['status' => false], 200);
      return response()->json(['status' => true], 200);
    }
}
