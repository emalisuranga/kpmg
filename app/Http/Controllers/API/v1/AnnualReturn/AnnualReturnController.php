<?php
namespace App\Http\Controllers\API\v1\AnnualReturn;
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
use App\CompanyMemberFirmBenif;
// use App\CompanyObjective;
use App\CompanyObjective1;
use App\CompanyObjective2;
use App\CompanyObjective3;
use App\CompanyObjective4;
use App\CompanyObjective5;
use App\CompanyObjective;
use App\CompanyStatus;
use App\ChangeName;
use App\Order;
use App\Secretary;
use App\SecretaryCertificate;
use App\Province;
use App\District;
use App\City;
use App\GNDivision;
use App\CompanyDocumentCopies;
use App\InlandRevenueDetails;
use App\IRDregPurposes;
use App\SecDivision;
use Storage;
use Cache;
use App;
use URL;
use App\Http\Helper\_helper;
use PDF;
use App\CompanyChangeRequestItem;
use App\CompanyItemChange;
use App\AnnualReturn;
use App\ShareRegister;
use App\AnnualRecords;
use App\AnnualAuditors;
use App\AnnualCharges;
use App\ShareClasses;
use App\CourtCase;
use App\ShareIssueRecords;
use App\OtherAddress;
use App\Form9;
use App\ShareholderTransfer;
use App\Charges;
use App\DeedItems;
use App\ChargesEntitledPersons;

class AnnualReturnController extends Controller
{
    use _helper;
    function generate_gns(){

        $cities = City::all();
        $n = 0;
        foreach($cities as $c ) {
            $rand_no_of_gns = rand(5,15);

            for($i=0;$i< $rand_no_of_gns; $i++ ) {
                $n++;
                $gn = new GNDivision;
                $gn->id = $c->code.$n;
                $gn->description_en = $c->name. '-GN' .($i+1);
                $gn->description_si = $c->name. '-GN' .($i+1);
                $gn->description_ta = $c->name. '-GN' .($i+1);
                $gn->city_id = $c->code;
                $gn->district_id = $c->district_id;
                $gn->province_id = $c->province_code;
                $gn->save();
            }
        }
    }


    function removeSecForDirector(Request $request){
        
        $company_id = $request->companyId;
        $user_id = $request->userId;
        $shaareUser = CompanyMember::where('id', $user_id)->first();
        $sec_nic_or_pass = ($shaareUser->is_srilankan  =='yes') ? $shaareUser->nic : $shaareUser->passport_no;
        $sec_nic_or_pass_field = ($shaareUser->is_srilankan  =='yes') ? 'nic' : 'passport_no';

        $removeDoc = CompanyDocuments::where('company_id', $company_id)
          ->where('company_member_id', $user_id)
          ->delete();
        
        $delete = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                ->where($sec_nic_or_pass_field,$sec_nic_or_pass )
                                ->delete();

        if($delete){
            return response()->json([
                'message' => 'Successfully remove seretary position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing seretary position',
                'status' =>false
            ], 200);
        }
        
    }

    function removeShForDirector(Request $request){

        $company_id = $request->companyId;
        $user_id = $request->userId;

        $shaareUser = CompanyMember::where('id', $user_id)->first();
        $sh_nic_or_pass = ($shaareUser->is_srilankan  =='yes') ? $shaareUser->nic : $shaareUser->passport_no;
        $sh_nic_or_pass_field = ($shaareUser->is_srilankan  =='yes') ? 'nic' : 'passport_no';

    
        $delete = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where($sh_nic_or_pass_field,$sh_nic_or_pass )
                                ->delete();
        if($delete){
            return response()->json([
                'message' => 'Successfully remove shareholder position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }

    function removeShForSec(Request $request){

        $company_id = $request->companyId;
        $user_id = $request->userId;

        $shaareUser = CompanyMember::where('id', $user_id)->first();
        $sh_nic_or_pass = ($shaareUser->is_srilankan  =='yes') ? $shaareUser->nic : $shaareUser->passport_no;
        $sh_nic_or_pass_field = ($shaareUser->is_srilankan  =='yes') ? 'nic' : 'passport_no';

        $delete = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where($sh_nic_or_pass_field,$sh_nic_or_pass )
                                ->whereNull('company_member_firm_id' )
                                ->delete();

        
        if($delete){
            return response()->json([
                'message' => 'Successfully remove shareholder position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }


    function removeShForSecFirm(Request $request){

        $company_id = $request->companyId;
        $firm_id = $request->userId;

        $shaareUser = CompanyFirms::where('id', $firm_id)->first();
        $shFirmRecordId = $shaareUser->sh_firm_of;

        //delete benif owner
        $delete_benif = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where('company_member_firm_id',$shFirmRecordId )
                                ->delete();
        //delete sh firm
        $delete_sh_firm = CompanyFirms::where('id',$shFirmRecordId )->delete();

        //update sec firm record
        $firm_update =  array(
            'sh_firm_of'    => null
        );
        $update_sec_firm = CompanyFirms::where('id', $firm_id)->update($firm_update);

        
        if( $delete_benif && $delete_sh_firm  && $update_sec_firm ){
            return response()->json([
                'message' => 'Successfully remove shareholder position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }


    function removeShFirm(Request $request){

        $company_id = $request->companyId;
        $firm_id = $request->userId;

        //delete benif owner
        $delete_benif = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where('company_member_firm_id',$firm_id )
                                ->where('is_beneficial_owner','yes')
                                ->delete();
        //delete sh firm
        $delete_sh_firm = CompanyFirms::where('id',$firm_id )->delete();

    

        
        if( $delete_benif && $delete_sh_firm   ){
            return response()->json([
                'message' => 'Successfully remove shareholder firm',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }


    function removeSecFirm(Request $request){

        $company_id = $request->companyId;
        $firm_id = $request->firmId;

     
        //delete sec firm
        $delete_sec_firm = CompanyFirms::where('id',$firm_id )->delete();

        $remove = CompanyDocuments::where('company_id', $company_id)
        ->where('company_firm_id', $firm_id)
        ->delete();


        if( $delete_sec_firm   ){
            return response()->json([
                'message' => 'Successfully remove secretory firm',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing secretory position',
                'status' =>false
            ], 200);
        }
        
    }


   

    function uploadedDocs($companyId){
          //uploaded docs array
      $uploaded_docs = array();
    
      $company_info = Company::where('id',$companyId)->first();


      $companyTypeKey = $this->settings($company_info->type_id,'id')->key;

      $docs = $this->documents();
      $docs_type_ids=array();

    // print_r($docs[$companyTypeKey]['upload']);
     //die();

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
    //  foreach($docs_type_ids as $id ){

       // $doc =CompanyDocuments::where('document_id', $id)
                     //   ->where('company_id', $companyId)
                     //   ->whereIn('status', $requested_doc_status)
                   //     ->get();
         $doc =CompanyDocuments::where('company_id', $companyId)
                        ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
                      // ->whereIn('status', $requested_doc_status)
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
   
   //   }

      return $uploaded_docs;
    }

    function uploadedDocsWithToken($companyId){
        //uploaded docs array
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
 
   // foreach($docs_type_ids as $id ){

     // $doc =CompanyDocuments::where('document_id', $id)
                    //  ->where('company_id', $companyId)
                   //   ->get();
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
 
   // }

    return $uploaded_docs;
  }


function uploadedDocsWithNoOfPages($companyId){
    //uploaded docs array
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

//foreach($docs_type_ids as $id ){

  //$doc =CompanyDocuments::where('document_id', $id)
                //  ->where('company_id', $companyId)
                //  ->get();
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

//}

return $uploaded_docs;
}

  public function loadHeavyData(Request $request){

    if(!$request->companyId){

        return response()->json([
            'message' => 'We can \'t find a company.',
            'status' =>false
        ], 200);
    }

   

    return response()->json([
        'message' => 'Data Loaded.',
        'status' =>true,
        'data'   => array(
                'pdc' => $this->getProvincesDisctrictsCities(),
        )
    ], 200);

    
  }

  private function getCompanyPostFix($type_id) {
     $company_types = CompanyPostfix::all();
   //  $company_types = $company_types->toArray();

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

  function generate_annual_return_report($company_id, $info_array=array()){

    $generated_files = array(
          'docs' => array(),
    );
    $request_id = $this->valid_annual_return_request_operation($company_id);

    if(!$request_id) {
        return $generated_files;
    }
  
    $file_name_key = 'form15';
    $file_name = 'FORM 15';

    /**
     * array(
        'company_info' => $company_info,
        'company_address' => $company_address_change,
        'directors' => $directors,
        'secs' => $secs,
        'secFirms' => $secs_firms,
        'shareholders' => $shareholders,
        'shareholderFirms' => $shareholderFirms,
        'share_register' => $shareRegisters,
        'annual_records' => $annualRecords,
        'annual_charges' => $annualCharges,
        'annual_auditors' => $annualAuditors
    );*
    */

    $data = $info_array;
                  
    $directory = "annual-return/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form15';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;

    return $generated_files;
  }

  function updateCourtRecords(Request $request ) {
    $company_id = $request->companyId;

    $request_id = $this->valid_annual_return_request_operation($company_id);

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


   function files_for_upload_docs($company_id){


        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
            
        );

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id) {
            return $generated_files;
        }
        
        $annual_return_request_type =  $this->settings('ANNUAL_RETURN','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$annual_return_request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_15 = Documents::where('key', 'FORM_15')->first();
        $form_other_docs = Documents::where('key', 'ANNUAL_RETURN_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_15->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_15->id;
        $file_row['file_description'] = $form_15->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_15->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
                if($request->status == 'ANNUAL_RETURN_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        $annualReturnGroup = DocumentsGroup::where('request_type', 'ANNUAL_RETURN')->first();
        $annualReturnDocuments = Documents::where('document_group_id', $annualReturnGroup->id)
                                           // ->where('key', '!=' , 'FORM_15')
                                            ->get();
        $annualReturnDocumentsCount = Documents::where('document_group_id', $annualReturnGroup->id)
                                             //   ->where('key', '!=' , 'FORM_15')
                                                ->count();

        if($annualReturnDocumentsCount){
            foreach($annualReturnDocuments as $other_doc ) {

                if($form_15->id === $other_doc->id) {
                    continue;
                }
                if($form_other_docs->id === $other_doc->id ) {
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
                if($request->status == 'ANNUAL_RETURN_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        $request_id = $this->valid_annual_return_request_operation($company_id);


        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'ANNUAL_RETURN_OTHER_DOCUMENTS')->first();
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
            if($company_status == 'ANNUAL_RETURN_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        $process_status = $this->settings($company_info->status,'id')->key;

        $approved_statuses = array(
            'COMPANY_NAME_CHANGE_APPROVED',
            'COMPANY_STATUS_APPROVED'
        );

        if(!in_array($process_status, $approved_statuses) ) {
          
                return response()->json([
                    'message' => 'Invalid Company Status.',
                    'status' =>false,
                    'process_status' => $process_status,
                    'data' => array(
                        'createrValid' => false
                    ),
                   
                ], 200);
    
            
        }

        $request_id = $this->valid_annual_return_request_operation($request->companyId);


        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);

        $loginUserEmail = $this->clearEmail($request->loginUser);
        

        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->people_id;

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
        $open_company_address = false;

        if($company_info->address_id ){
            $company_address = Address::where('id',$company_info->address_id)->first();
            $open_company_address = $this->localAddressOpenStatus($company_address);
            $company_for_address = Address::where('id',$company_info->foreign_address_id)->first();
        }else {
            $company_address = $company_for_address = array(

                'address1'=> "",
                'address2'=> "",
                'city'=> "",
                'country'=> "",
                'district'=> "",
                'id' => 0,
                'postcode'=> "",
                'province'=> "",
            );
            $open_company_address = $this->localAddressOpenStatus($company_address);
        }

        $annualReturnRecord =  AnnualReturn::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
            ->where('year', date('Y',time()))
             ->first();




        $directors_on_declaration = [];
        $saved_list = $annualReturnRecord->directors_on_declaration;
         $directors_on_declaration_arr = explode(',', $saved_list);

         if(  $saved_list && count($directors_on_declaration_arr)) {
            foreach($directors_on_declaration_arr as $director_id) {
 
                $directorInfo = CompanyMember::where('id', $director_id)->first();
    
                $row = array();
                $row['first_name'] = $directorInfo->first_name;
                $row['last_name'] = $directorInfo->last_name;
                $row['id'] = $directorInfo->id;
                $directors_on_declaration[] = $row;
    
            }
         }
         

       $director_as_sec_count = 0;
       $director_as_sh_count =0;
       $dir_count = 0;
       $sec_count =0;
       $sh_count = 0;
       $sh_firm_count = 0;
       $sec_firm_count =0;
        /******director list *****/
        
        $director_list_count = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->count();
        $directors_already_exists = true;
        if($director_list_count){
            $director_list = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->get();
            $directors_already_exists = true;
        }else{
            $director_list = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                       ->get();
            $directors_already_exists = false;
        }
        
        $directors = array();
        foreach($director_list as $director){

            $dir_count++;
             
             $director_nic_or_pass = ($director->is_srilankan  =='yes') ? $director->nic : $director->passport_no;
             $director_nic_or_pass_field = ($director->is_srilankan  =='yes') ? 'nic' : 'passport_no';

             //director as a secrotory list
             $directors_as_sec = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                        ->where('company_id', $request->companyId)
                                        ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                        ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                        ->get()
                                        ->count();
             
            if($directors_as_sec){
                $director_as_sec_count ++;
            }
            

            //director as a shareholder list
            $directors_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                       ->where('company_id', $request->companyId)
                                       ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                       ->where('is_beneficial_owner','no')
                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                       ->get()
                                       ->count();
             
            if($directors_as_sh){
                $director_as_sh_count ++;
            }                         

             $address ='';
             $forAddress = '';
             if( $director->address_id) {
                $address = Address::where('id',$director->address_id)->first();
             }
             if( $director->foreign_address_id) {
                $forAddress = Address::where('id', $director->foreign_address_id)->first();
             }

             $can_director_as_sec = true;
             $sec_reg_no = '';

            if( $director->nic  && ( $companyType->key =='COMPANY_TYPE_PUBLIC' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_32' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_34' ))  {
                
               
                $members_sec_nic_lower =Secretary::where('nic', strtolower($director->nic))->first();
                $members_sec_nic_lowercount = Secretary::where('nic', strtolower($director->nic))->count();
        
                $members_sec_nic_upper =Secretary::where('nic', strtoupper($director->nic))->first();
                $members_sec_nic_uppercount = Secretary::where('nic',strtoupper($director->nic))->count();
        
                $members_sec = ($members_sec_nic_lowercount ) ? $members_sec_nic_lower : $members_sec_nic_upper;
 
                $sec_reg_no = isset($members_sec->certificate_no) && $members_sec->certificate_no  ? $members_sec->certificate_no : '';
                $can_director_as_sec = ($sec_reg_no) ? true : false;
              

            }

             $rec = array(
                'id' => $director['id'],
                'type' => ($director->is_srilankan  =='yes' ) ? 'local' : 'foreign',

                'firstname' => $director->first_name,
                'lastname' => $director->last_name,
                'title' => $director->title,

                'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

                'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',

                'nic'       => $director->nic,
                'new_nic'       => $director->new_format_nic,
                'passport'  => $director->passport_no,
               // 'country'   =>($address->country) ? $address->country : '',
               'country'  => @( $director->foreign_address_id)  ? @$forAddress->country : @$address->country,
                'passport_issued_country'   => $director->passport_issued_country,
              //  'share'     => $director->no_of_shares,
                'date'      => '1970-01-01' == $director->date_of_appointment ? null : $director->date_of_appointment,
                'phone' => $director->telephone,
                'mobile' => $director->mobile,
                'email' => $director->email,
                'occupation' => $director->occupation,
                'directors_as_sec' =>$directors_as_sec,
                'directors_as_sh' => $directors_as_sh,
                'can_director_as_sec' => $can_director_as_sec,
                'secRegDate' => $sec_reg_no,
                'listed_on_declaration' => in_array($director['id'],$directors_on_declaration_arr) ? true : false
               
             );
             $directors[] = $rec;
        }

        $secs_already_exists = true;
        /******secretory firms list *****/
        $sec_firm_list_count = CompanyFirms::where('company_id',$request->companyId)
        ->where('type_id',$this->settings('SECRETARY','key')->id)
        ->where('status',1)
        ->count();
 
        if($sec_firm_list_count){
            $sec_firm_list = CompanyFirms::where('company_id',$request->companyId)
                                    ->where('type_id',$this->settings('SECRETARY','key')->id)
                                    ->where('status',1)
                                    ->get();
          
        }else {
            $sec_firm_list = CompanyFirms::where('company_id',$request->companyId)
                                    ->where('type_id',$this->settings('SECRETARY','key')->id)
                                    ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                    ->get();
            $secs_already_exists = false;
        }
        

        
        $secs_firms = array();

        foreach($sec_firm_list as $sec){

        //sec firm as a shareholder list
        
        $sec_as_sh_count =  ( intval( $sec->sh_firm_of ) > 0 )  ? 1 : 0 ;
        $sec_firm_count++;

        $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;

         if(!$sec->foreign_address_id){
            $address = Address::where('id',$address_id)->first();
         }else{
           // $address = ForeignAddress::where('id',$address_id)->first();
           $address = Address::where('id',$address_id)->first();
         }

        $rec = array(
        'id' => $sec['id'],
        'type' => ($address->country != 'Sri Lanka') ? 'foreign' : 'local',
        'pvNumber' => $sec->registration_no,
        'firm_name' => $sec->name,
        'firm_province' =>  ( $address->province) ? $address->province : '',
        'firm_district' =>  ($address->district) ? $address->district : '',
        'firm_city' =>  ( $address->city) ? $address->city : '',
        'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
        'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
        'firm_country'      => ($address->country) ? $address->country : '',
        'firm_postcode' => ($address->postcode) ? $address->postcode : '',
        'firm_email' => $sec->email,
        'firm_phone' => $sec->phone,
        'firm_mobile' => $sec->mobile,
        'firm_date'  => $sec->date_of_appointment,
        'sec_as_sh' => $sec_as_sh_count,
        'secType' => 'firm',
        'secBenifList' => array(
             'ben' => array()
        )
        );
        $secs_firms[] = $rec;
        }

        /******secretory list *****/
        $sec_list_count = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                ->where('status',1)
                ->count();
        if($sec_list_count){
            $sec_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                ->where('status',1)
                ->get();
              
        }else{
            $sec_list = CompanyMember::where('company_id',$request->companyId)
            ->where('designation_type',$this->settings('SECRETARY','key')->id)
            ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
            ->get();
           
        }
        
         $secs = array();
        foreach($sec_list as $sec){
        
                $sec_nic_or_pass = ($sec->is_srilankan  =='yes') ? $sec->nic : $sec->passport_no;
                $sec_nic_or_pass_field = ($sec->is_srilankan  =='yes') ? 'nic' : 'passport_no';
        
                //sec as a shareholder list
                $sec_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                    ->where('company_id', $request->companyId)
                     ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                     ->where('nic',$sec->nic)
                     ->whereNull('company_member_firm_id' )
                     ->get();
                $sec_as_sh_count = $sec_as_sh->count();
                $sec_sh_comes_from_director = false;

                $sec_count ++;
              
               
                 $address ='';
                 $forAddress = '';
                 if( $sec->address_id) {
                    $address = Address::where('id',$sec->address_id)->first();
                 }
                 if( $sec->foreign_address_id) {
                    $forAddress = Address::where('id', $sec->foreign_address_id)->first();
                 }
        
                 $firm_info = array();
                 if($sec->company_member_firm_id){
                     $firm_info = CompanyFirms::where('id',$sec->company_member_firm_id)->first();
        
                     $firm_address = Address::where('id', $firm_info->address_id)->first();
        
                     $firm_info['address']=$firm_address;
        
                 }
        
                $rec = array(
                'id' => $sec['id'],
                'type' => ($sec->is_srilankan =='yes' ) ? 'local' : 'foreign',
                'firstname' => $sec->first_name,
                'lastname' => $sec->last_name,

                'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

                'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',

                'nic'       => $sec->nic,
                'new_nic'       => $sec->new_format_nic,
                'passport'  => $sec->passport_no,
               // 'country'   =>($address->country) ? $address->country : '',
                'country'  => @( $sec->foreign_address_id && isset( $forAddress->country) )  ? @$forAddress->country : @$address->country,
              //  'country'  => ( $sec->foreign_address_id && isset( $forAddress->country) ) ? 'test' : 'fail',
                'passport_issued_country'   => $sec->passport_issued_country,
                //'share'     =>0,
                'date'      => '1970-01-01' == $sec->date_of_appointment ? null : $sec->date_of_appointment,
                'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
                'regDate'      => ($sec->is_registered_secretary =='yes' || $companyType->key =='COMPANY_TYPE_PUBLIC' ) ? $sec->secretary_registration_no :'',
                'phone' => $sec->telephone,
                'mobile' => $sec->mobile,
                'email' => $sec->email,
                'occupation' => $sec->occupation,
              //  'secType' => ( $sec->is_natural_person == 'yes') ? 'natural' : 'firm',
                'secType' => 'natural',
                'secCompanyFirmId' => $sec->company_member_firm_id,
                'sec_as_sh' => $sec_as_sh_count,
                'sec_sh_comes_from_director' => $sec_sh_comes_from_director,
                'firm_info' =>$firm_info,
                'pvNumber' => ($sec->company_member_firm_id) ? $firm_info['registration_no'] : '',
                'firm_name' => ($sec->company_member_firm_id) ? $firm_info['name'] : '',
                'firm_province' => ($sec->company_member_firm_id) ? $firm_address['province'] : '',
                'firm_district' => ($sec->company_member_firm_id) ? $firm_address['district'] : '',
                'firm_city' => ($sec->company_member_firm_id) ? $firm_address['city'] : '',
                'firm_localAddress1' => ($sec->company_member_firm_id) ? $firm_address['address1'] : '',
                'firm_localAddress2' => ($sec->company_member_firm_id) ? $firm_address['address2'] : '',
                'firm_postcode' => ($sec->company_member_firm_id) ? $firm_address['postcode'] : ''
        
                );
                $secs[] = $rec;
                }

                $secs_already_exists = ( $sec_firm_list_count || $sec_list_count );

        /******share holder list *****/

        $shareholder_list_count = CompanyMember::where('company_id',$request->companyId)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->whereNull('company_member_firm_id' )
        ->where('status',1)
        ->count();

        if($shareholder_list_count) {
            $shareholder_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                ->whereNull('company_member_firm_id' )
                ->where('status',1)
                ->get();
        }else {
            $shareholder_list = CompanyMember::where('company_id',$request->companyId)
            ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
            ->whereNull('company_member_firm_id' )
            ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
            ->get();
        }
        
        
        $shareholders = array();
        foreach($shareholder_list as $shareholder){

            $sh_count++;

            $address ='';
            $forAddress = '';
            if( $shareholder->address_id) {
               $address = Address::where('id',$shareholder->address_id)->first();
            }
            if( $shareholder->foreign_address_id) {
               $forAddress = Address::where('id', $shareholder->foreign_address_id)->first();
            }


        //check share row
        $shareRecord = array(
            'type' => '', 'name' => '' , 'no_of_shares' =>0
        );

        $shareRow = Share::where('company_member_id', $shareholder->id)->orderBy('id', 'DESC')->first();
   
         $shareType ='';
         $noOfShares ='';
         $groupName= '';
         $sharegroupId='';

        if(isset($shareRow->company_member_id ) && $shareRow->company_member_id ){

            $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();

            $shareRecord['type'] = $shareGroupInfo['type'];
            $shareRecord['name'] = $shareGroupInfo['name'];
            $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
            $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];

            $shareType = $shareGroupInfo['type'] == 'core_share' ? 'core' :'single';
            $noOfShares = $shareGroupInfo['no_of_shares'];

            if($shareType == 'core'){
                $groupName= $shareGroupInfo['name'];
                $sharegroupId = $shareGroupInfo['id'];
            }
        }

        $rec = array(
        'id' => $shareholder['id'],
        'type' => ($shareholder->is_srilankan =='yes' ) ? 'local' : 'foreign',
        'firstname' => $shareholder->first_name,
        'lastname' => $shareholder->last_name,
        'title'    => $shareholder->title,
        'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
        'nic'       => $shareholder->nic,
        'new_nic'       => $shareholder->new_format_nic,
        'passport'  => $shareholder->passport_no,
      //  'country'   =>($address->country) ? $address->country : '',
        'country'  => @( $shareholder->foreign_address_id)  ? @$forAddress->country : @$address->country,
        'passport_issued_country' => $shareholder->passport_issued_country,
       // 'share'     => $shareholder->no_of_shares,
        'date'      => '1970-01-01' == $shareholder->date_of_appointment ? null : $shareholder->date_of_appointment,
        'phone' => $shareholder->telephone,
        'mobile' => $shareholder->mobile,
        'email' => $shareholder->email,
        'occupation' => $shareholder->occupation,
        'shareRow' => $shareRecord,
        'shList'  =>$shareholder,
        'shareType' => $shareType,
        'noOfShares' => ($sharegroupId) ? '' : $noOfShares,
        'coreGroupSelected' => $sharegroupId,
        'shareholderType' => 'natural'
        );
        $shareholders[] = $rec;
        }

         /******share holder firms list *****/
         $shareholder_firm_list_count = CompanyFirms::where('company_id',$request->companyId)
         ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
         ->where('status',1)
         ->count();

         if($shareholder_firm_list_count) {
            $shareholder_list = CompanyFirms::where('company_id',$request->companyId)
                ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
                ->where('status',1)
                ->get();
         } else {
            $shareholder_list = CompanyFirms::where('company_id',$request->companyId)
            ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
            ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
            ->get();
         }
         
         $shareholderFirms = array();
         foreach($shareholder_list as $shareholder){

            $sh_firm_count ++;

           // benifi list 
           $benifListArr = array();
           $benifList = CompanyMember::where('company_id', $request->companyId)
                                    ->where('designation_type', $this->settings('SHAREHOLDER','key')->id)
                                    ->where('company_member_firm_id',  $shareholder->id )
                                    ->where('is_beneficial_owner', 'yes')
                                    ->get();
            if(count($benifList)){
                foreach($benifList as $ben ){
                    
                    $ben_address_id =  $ben->address_id;
                    $ben_address = Address::where('id',$ben_address_id)->first();

                     $row = array(
                        'type' => $ben->is_srilankan == 'yes' ? 'local' : 'foreign',
                        'title' => $ben->title,
                        'firstname' => $ben->first_name,
                        'lastname'  => $ben->last_name,
                        'province'  => $ben_address->province,
                        'district'  => $ben_address->district,
                        'city'      => $ben_address->city,
                        'localAddress1' => $ben_address->address1,
                        'localAddress2' => $ben_address->address2,
                        'postcode'     => $ben_address->postcode,
                        'nic'           => $ben->nic,
                        'passport'      => $ben->passport_no,
                        'country'      => $ben_address->country,
                        'date'          => ( $ben->date_of_oppointment == '1970-01-01' ) ? '' : $ben->date_of_oppointment,
                        'occupation'   => $ben->occupation,
                        'phone'        => $ben->telephone,
                        'mobile'       => $ben->mobile,
                        'email'        => $ben->email,
                        'id'           => $ben->id

                     );
                     $benifListArr[] = $row;
                }
            }

           
 
         $address_id =  $shareholder->address_id;
         $address = Address::where('id',$address_id)->first();

         $shareRecord = array(
             'type' => '', 'name' => '' , 'no_of_shares' =>0
         );
         $shareRow = Share::where('company_firm_id', $shareholder->id)->orderBy('id', 'DESC')->first();
 
          $shareType ='';
          $noOfShares ='';
          $groupName= '';
          $sharegroupId='';

         if(isset($shareRow->company_firm_id ) && $shareRow->company_firm_id ){

             $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();
             $shareRecord['type'] = $shareGroupInfo['type'];
             $shareRecord['name'] = $shareGroupInfo['name'];
             $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
             $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];
 
             $shareType = $shareGroupInfo['type'] == 'core_share' ? 'core' :'single';
             $noOfShares = $shareGroupInfo['no_of_shares'];
 
             if($shareType == 'core'){
                 $groupName= $shareGroupInfo['name'];
                 $sharegroupId = $shareGroupInfo['id'];
             }
 
         }

         $rec = array(
         'id' => $shareholder['id'],
         'type' => ($shareholder->is_srilankan =='yes' ) ? 'local' : 'foreign',
         'pvNumber' => $shareholder->registration_no,
         'firm_name' => $shareholder->name,
        'firm_province' =>  ( $address->province) ? $address->province : '',
        'firm_district' =>  ($address->district) ? $address->district : '',
        'firm_city' =>  ( $address->city) ? $address->city : '',
        'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
        'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
        'firm_postcode' => ($address->postcode) ? $address->postcode : '',
        'firm_email' => $shareholder->email,
        'firm_date'  => $shareholder->date_of_appointment,
        'firm_phone' => $shareholder->phone,
        'firm_mobile' => $shareholder->mobile,
         'shareRow' => $shareRecord,
         'shList'  =>$shareholder,
         'shareType' => $shareType,
         'noOfShares' => ($sharegroupId) ?  '' : $noOfShares,
         'coreGroupSelected' => $sharegroupId,
         'benifiList' => array('ben' => $benifListArr ),
         'shareholderType' => 'firm',
         );
         $shareholderFirms[] = $rec;
         }
         $sh_already_exists = ( $shareholder_firm_list_count || $shareholder_list_count );



         /***
          * Seased Shareholders
         */

          /******share holder list *****/

        $shareholder_inactive_list_count = CompanyMember::where('company_id',$request->companyId)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->whereNull('company_member_firm_id' )
        ->where('status',0)
        ->count();

        if($shareholder_inactive_list_count) {
            $shareholder_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                ->whereNull('company_member_firm_id' )
                ->where('status',0)
                ->get();
        }else {
            $shareholder_list = CompanyMember::where('company_id',$request->companyId)
            ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
            ->whereNull('company_member_firm_id' )
            ->where('status',$this->settings('ANNUAL_RETURN_FALSE','key')->id)
            ->get();
        }
        
        
        $shareholders_inactive = array();
        foreach($shareholder_list as $shareholder){

            $sh_count++;

            $address ='';
            $forAddress = '';
            if( $shareholder->address_id) {
               $address = Address::where('id',$shareholder->address_id)->first();
            }
            if( $shareholder->foreign_address_id) {
               $forAddress = Address::where('id', $shareholder->foreign_address_id)->first();
            }


        //check share row
        $shareRecord = array(
            'type' => '', 'name' => '' , 'no_of_shares' =>0
        );

        $shareRow = Share::where('company_member_id', $shareholder->id)->orderBy('id', 'DESC')->first();
   
         $shareType ='';
         $noOfShares ='';
         $groupName= '';
         $sharegroupId='';

        if(isset($shareRow->company_member_id ) && $shareRow->company_member_id ){

            $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();

            $shareRecord['type'] = $shareGroupInfo['type'];
            $shareRecord['name'] = $shareGroupInfo['name'];
            $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
            $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];

            $shareType = $shareGroupInfo['type'] == 'core_share' ? 'core' :'single';
            $noOfShares = $shareGroupInfo['no_of_shares'];

            if($shareType == 'core'){
                $groupName= $shareGroupInfo['name'];
                $sharegroupId = $shareGroupInfo['id'];
            }
        }

        $rec = array(
        'id' => $shareholder['id'],
        'type' => ($shareholder->is_srilankan =='yes' ) ? 'local' : 'foreign',
        'firstname' => $shareholder->first_name,
        'lastname' => $shareholder->last_name,
        'title'    => $shareholder->title,
        'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
        'nic'       => $shareholder->nic,
        'passport'  => $shareholder->passport_no,
      //  'country'   =>($address->country) ? $address->country : '',
        'country'  => @( $shareholder->foreign_address_id)  ? @$forAddress->country : @$address->country,
        'passport_issued_country' => $shareholder->passport_issued_country,
       // 'share'     => $shareholder->no_of_shares,
        'date'      => '1970-01-01' == $shareholder->date_of_appointment ? null : $shareholder->date_of_appointment,
        'phone' => $shareholder->telephone,
        'mobile' => $shareholder->mobile,
        'email' => $shareholder->email,
        'occupation' => $shareholder->occupation,
        'shareRow' => $shareRecord,
        'shList'  =>$shareholder,
        'shareType' => $shareType,
        'noOfShares' => ($sharegroupId) ? '' : $noOfShares,
        'coreGroupSelected' => $sharegroupId,
        'shareholderType' => 'natural'
        );
        $shareholders_inactive[] = $rec;
        }

         /******share holder firms list *****/
         $shareholder_firm_inactive_list_count = CompanyFirms::where('company_id',$request->companyId)
         ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
         ->where('status',0)
         ->count();

         if($shareholder_firm_inactive_list_count) {
            $shareholder_list = CompanyFirms::where('company_id',$request->companyId)
                ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
                ->where('status',0)
                ->get();
         } else {
            $shareholder_list = CompanyFirms::where('company_id',$request->companyId)
            ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
            ->where('status',$this->settings('ANNUAL_RETURN_FALSE','key')->id)
            ->get();
         }
         
         $shareholderFirms_inactive = array();
         foreach($shareholder_list as $shareholder){

            $sh_firm_count ++;

           // benifi list 
           $benifListArr = array();
           $benifList = CompanyMember::where('company_id', $request->companyId)
                                    ->where('designation_type', $this->settings('SHAREHOLDER','key')->id)
                                    ->where('company_member_firm_id',  $shareholder->id )
                                    ->where('is_beneficial_owner', 'yes')
                                    ->get();
            if(count($benifList)){
                foreach($benifList as $ben ){
                    
                    $ben_address_id =  $ben->address_id;
                    $ben_address = Address::where('id',$ben_address_id)->first();

                     $row = array(
                        'type' => $ben->is_srilankan == 'yes' ? 'local' : 'foreign',
                        'title' => $ben->title,
                        'firstname' => $ben->first_name,
                        'lastname'  => $ben->last_name,
                        'province'  => $ben_address->province,
                        'district'  => $ben_address->district,
                        'city'      => $ben_address->city,
                        'localAddress1' => $ben_address->address1,
                        'localAddress2' => $ben_address->address2,
                        'postcode'     => $ben_address->postcode,
                        'nic'           => $ben->nic,
                        'passport'      => $ben->passport_no,
                        'country'      => $ben_address->country,
                        'date'          => ( $ben->date_of_oppointment == '1970-01-01' ) ? '' : $ben->date_of_oppointment,
                        'occupation'   => $ben->occupation,
                        'phone'        => $ben->telephone,
                        'mobile'       => $ben->mobile,
                        'email'        => $ben->email,
                        'id'           => $ben->id

                     );
                     $benifListArr[] = $row;
                }
            }

           
 
         $address_id =  $shareholder->address_id;
         $address = Address::where('id',$address_id)->first();

         $shareRecord = array(
             'type' => '', 'name' => '' , 'no_of_shares' =>0
         );
         $shareRow = Share::where('company_firm_id', $shareholder->id)->orderBy('id', 'DESC')->first();
 
          $shareType ='';
          $noOfShares ='';
          $groupName= '';
          $sharegroupId='';

         if(isset($shareRow->company_firm_id ) && $shareRow->company_firm_id ){

             $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();
             $shareRecord['type'] = $shareGroupInfo['type'];
             $shareRecord['name'] = $shareGroupInfo['name'];
             $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
             $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];
 
             $shareType = $shareGroupInfo['type'] == 'core_share' ? 'core' :'single';
             $noOfShares = $shareGroupInfo['no_of_shares'];
 
             if($shareType == 'core'){
                 $groupName= $shareGroupInfo['name'];
                 $sharegroupId = $shareGroupInfo['id'];
             }
 
         }

         $rec = array(
         'id' => $shareholder['id'],
         'type' => ($shareholder->is_srilankan =='yes' ) ? 'local' : 'foreign',
         'pvNumber' => $shareholder->registration_no,
         'firm_name' => $shareholder->name,
        'firm_province' =>  ( $address->province) ? $address->province : '',
        'firm_district' =>  ($address->district) ? $address->district : '',
        'firm_city' =>  ( $address->city) ? $address->city : '',
        'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
        'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
        'firm_postcode' => ($address->postcode) ? $address->postcode : '',
        'firm_email' => $shareholder->email,
        'firm_date'  => $shareholder->date_of_appointment,
        'firm_phone' => $shareholder->phone,
        'firm_mobile' => $shareholder->mobile,
         'shareRow' => $shareRecord,
         'shList'  =>$shareholder,
         'shareType' => $shareType,
         'noOfShares' => ($sharegroupId) ?  '' : $noOfShares,
         'coreGroupSelected' => $sharegroupId,
         'benifiList' => array('ben' => $benifListArr ),
         'shareholderType' => 'firm',
         );
         $shareholderFirms_inactive[] = $rec;
         }
         $sh_inactive_already_exists = ( $shareholder_firm_inactive_list_count || $shareholder_inactive_list_count );


         /*******End Seased Shareholders */






          /******company documents *****/
        $documentsGroups = DocumentsGroup::where('company_type',$company_info->type_id )
                                                ->where('request_type','COM_REG')
                                                ->get();
        $documentList = array();

        foreach($documentsGroups as $group ){

            $group_id = @$group->id;

            $docs =  \DB::table('documents')->where('document_group_id', $group_id )->get();

            if(count($docs)){

                $data = array(

                    'group_name' => $group->description,
                    'documents'  =>  $docs,
                    'docs_count' => count($docs)
    
                );
                $documentList[] = $data;

            }


        }
      ////////share groups////////
      $core_groups_list = array();
      $core_groups = ShareGroup::where('type','core_share')
                                  ->where('company_id', $request->companyId )
                                ->get();
      if(count($core_groups)){
          foreach($core_groups as $g ){
        
          $grec = array(
              'group_id' => $g->id,
              'group_name' => "$g->name ($g->no_of_shares)"
          );
          $core_groups_list[] = $grec;
        }
      }

     $payment_row =  $this->document_map($companyType->key);

     $payment = $payment_row['form_map_fee'];

     $payment_new_row = $this->document_map_new($companyType->key,$directors,$secs,$secs_firms);

     $payment_new = $payment_new_row['form_map_fee'];

      /******share register list *****/
      $isGuarantyCompany =  ( $companyType->key === 'COMPANY_TYPE_GUARANTEE_32' || $companyType->key === 'COMPANY_TYPE_GUARANTEE_34' );
      $address_type = ($isGuarantyCompany) ? 'MEMBER_REGISTER_ADDRESS' : 'SHARE_REGISTER_ADDRESS';
      $sr_count =0;
      $share_register_list_count = OtherAddress::where('company_id',$request->companyId)
      ->where('address_type', $this->settings($address_type,'key')->id)
      ->where('status',1)
      ->count();
        $share_register_already_exists = true;
        if($share_register_list_count){
        $share_register_list = OtherAddress::where('company_id',$request->companyId)
                                ->where('status',1)
                                ->where('address_type', $this->settings($address_type,'key')->id)
                                ->get();
        $share_register_already_exists = true;
        }else{
        $share_register_list = OtherAddress::where('company_id',$request->companyId)
                                            ->where('address_type', $this->settings($address_type,'key')->id)
                                            ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                            ->get();
        $share_register_already_exists = false;
        }

        $shareRegisters = array();
        foreach($share_register_list as $sr){

        $sr_count++;                     

        $address ='';
        $forAddress = '';
        if( $sr->address_id) {
        $address = Address::where('id',$sr->address_id)->first();
        }
        if( $sr->foreign_address_id) {
        $forAddress = Address::where('id', $sr->foreign_address_id)->first();
        }

        $rec = array(
        'id' => $sr['id'],
        'address_type' => ( $sr->foreign_address_id ) ? 'foreign' : 'local',
        'description' => json_decode($sr->description),
        'records_kept_from' => $sr->records_kept_from,
        'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
        'country'  => @( $sr->foreign_address_id)  ? @$forAddress->country : 'Sri Lanka',
        );
        $shareRegisters[] = $rec;
        }

        /******annual records list *****/
      $record_count =0;
      $annual_record_list_count = OtherAddress::where('company_id',$request->companyId)
      ->where('address_type', $this->settings('RECORD_ADDRESS','key')->id)
      ->where('status',1)
      ->count();
        $annual_records_already_exists = true;
        if($annual_record_list_count){
        $annual_record_list = OtherAddress::where('company_id',$request->companyId)
                                ->where('address_type', $this->settings('RECORD_ADDRESS','key')->id)
                                ->where('status',1)
                                ->get();
        $annual_records_already_exists = true;
        }else{
        $annual_record_list = OtherAddress::where('company_id',$request->companyId)
                                            ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                            ->where('address_type', $this->settings('RECORD_ADDRESS','key')->id)
                                            ->get();
        $annual_records_already_exists = false;
        }

        $annualRecords = array();
        foreach($annual_record_list as $sr){

        $record_count++;                     

        $address ='';
        $forAddress = '';
        if( $sr->address_id) {
        $address = Address::where('id',$sr->address_id)->first();
        }
        if( $sr->foreign_address_id) {
        $forAddress = Address::where('id', $sr->foreign_address_id)->first();
        }

        $rec = array(
        'id' => $sr['id'],
        'address_type' => ( $sr->foreign_address_id ) ? 'foreign' : 'local',
        'description' => json_decode($sr->description),
        'records_kept_from' => $sr->records_kept_from,
        'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
        'country'  => @( $sr->foreign_address_id)  ? @$forAddress->country : 'Sri Lanka',
        );
        $annualRecords[] = $rec;
        }


           /******annual auditor list *****/
      $record_count =0;
      $annual_auditor_list_count = AnnualAuditors::where('company_id',$request->companyId)
      ->where('status',1)
      ->count();
        $annual_auditors_already_exists = true;
        if($annual_auditor_list_count){
        $annual_auditors_list = AnnualAuditors::where('company_id',$request->companyId)
                                ->where('status',1)
                                ->get();
        $annual_auditors_already_exists = true;
        }else{
        $annual_auditors_list = AnnualAuditors::where('company_id',$request->companyId)
                                            ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                            ->get();
        $annual_auditors_already_exists = false;
        }

        $annualAuditors = array();
        foreach($annual_auditors_list as $sr){

        $record_count++;                     

        $address ='';
        $forAddress = '';
        if( $sr->address_id) {
        $address = Address::where('id',$sr->address_id)->first();
        }
        if( $sr->foreign_address_id) {
        $forAddress = Address::where('id', $sr->foreign_address_id)->first();
        }

        $rec = array(
        'id' => $sr['id'],
        'address_type' => ( $sr->foreign_address_id ) ? 'foreign' : 'local',
        'first_name' => $sr->first_name,
        'last_name' => $sr->last_name,
        'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
        'country'  => @( $sr->foreign_address_id)  ? @$forAddress->country : 'Sri Lanka',
        'type' => $sr->type,
        'reg_no' => $sr->reg_no,
        'nic' => $sr->nic
        );
        $annualAuditors[] = $rec;
        }


         /******annual charges list *****/
        $record_count =0;
        $annual_charge_list_count = AnnualCharges::where('company_id',$request->companyId)
        ->where('status',1)
        ->count();
        $annual_charges_already_exists = true;
        if($annual_charge_list_count){
            $annual_charges_list = AnnualCharges::where('company_id',$request->companyId)
                                    ->where('status',1)
                                    ->get();
            $annual_charges_already_exists = true;
        }else{
            $annual_charges_list = AnnualCharges::where('company_id',$request->companyId)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            $annual_charges_already_exists = false;
        }

        $annualCharges = array();
        foreach($annual_charges_list as $sr){

        $record_count++;                     

        $address ='';
        $forAddress = '';
        if( $sr->address_id) {
        $address = Address::where('id',$sr->address_id)->first();
        }
        if( $sr->foreign_address_id) {
        $forAddress = Address::where('id', $sr->foreign_address_id)->first();
        }

        $rec = array(
        'id' => $sr['id'],
        'address_type' => ( $sr->foreign_address_id ) ? 'foreign' : 'local',
        'name' => $sr->name,
        'date' => $sr->date,
        'description' => $sr->description,
        'amount'  => floatval($sr->amount),
        'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
        'country'  => @( $sr->foreign_address_id)  ? @$forAddress->country : 'Sri Lanka',
        );
        $annualCharges[] = $rec;
        }


            /******share record list *****/
      $record_count =0;
      $share_record_list_count = ShareClasses::where('company_id', $request->companyId)
      ->where('status', $this->settings('ISSUE_OF_SHARES_APPROVED','key')->id)
      ->count();
      
        $share_records_already_exists = true;
        if($share_record_list_count){

            $share_records = ShareClasses::where('company_id', $request->companyId)
            ->where('status', $this->settings('ISSUE_OF_SHARES_APPROVED','key')->id)
            ->pluck('id')->toArray();
            $share_record_list = ShareIssueRecords::whereIn('record_id',$share_records)
            ->where('status',$this->settings('ISSUE_OF_SHARES','key')->id)
            ->get();
            $share_records_already_exists = true;

        }else{

            $share_records = ShareClasses::where('company_id', $request->companyId)
            ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
            ->where('request_id', $request_id)
            ->first();

         //   print_r($share_records);
       //     echo $request->companyId.'---'.$request_id;
        //    die();
            $share_record_list = isset($share_records->id) ? ShareIssueRecords::where('record_id',$share_records->id)
            ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
            ->get() : [];
            $share_records_already_exists = false;
        }

        $shareRecords = array();

        if(isset($share_record_list[0]->id)) {
            foreach($share_record_list as $sr){

                $record_count++;                     
    
                $rec = array(
                'id' => $sr->id,
                'share_class' =>  $sr->share_class,
                'share_class_other' => $sr->share_class_other,
                'is_issue_type_as_cash' =>$sr->is_issue_type_as_cash,
                'no_of_shares_as_cash' => $sr->no_of_shares_as_cash,
                'consideration_of_shares_as_cash' =>  $sr->consideration_of_shares_as_cash,
                'is_issue_type_as_non_cash' =>  $sr->is_issue_type_as_non_cash,
                'no_of_shares_as_non_cash' =>  $sr->no_of_shares_as_non_cash,
                'consideration_of_shares_as_non_cash' =>  $sr->consideration_of_shares_as_non_cash,
                'date_of_issue' => $sr->date_of_issue,
                'selected_share_class_name' => ($sr->share_class == 'OTHER_SHARE') ? $sr->share_class_other : $this->settings($sr->share_class,'key')->value,
                'called_on_shares' => $sr->called_on_shares,
                'consideration_paid_or_provided' => $sr->consideration_paid_or_provided
                );
                $shareRecords[] = $rec;
            }

        }


 
     /*****security checkpoint - check company status */
    

    $external_global_comment = '';


    $form_15 = Documents::where('key', 'FORM_15')->first();
    $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

                
    $resubmit_doc = CompanyDocumentStatus::where('company_document_id', $form_15->id )
                                       ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                       ->where('comment_type', $external_comment_type_id )
                                       ->first();

    // if(isset($resubmit_doc->id) && $resubmit_doc->id){
          
           $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                    ->where('comment_type', $external_comment_type_id )
                                                    ->where('request_id', $request_id)
                                                    ->orderBy('id', 'DESC')
                                                    ->first();
           $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                      ?  $external_comment_query->comments
                                      : '';

   // } 

    $countries_cache = Cache::rememberForever('countries_cache', function () {
        return Country::all();
    });
    $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

    $postfix_values = $this->getPostfixValues($company_info->postfix);

    $company_address_change = $company_address;
    if(!$open_company_address) {
        $company_address_change = $company_address;
    } else {
        if(!$request_id) {
            $company_address_change = $company_address;
        } else {
            
            $address_record = CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
            ->first();
            if(isset($address_record->item_id) && $address_record->item_id) {

                $req_address = Address::where('id', $address_record->item_id)->first();
                $company_address_change = $req_address;

            } else {

                $company_address_change = $company_address;
                
            }
            
        }
    }

    $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
    $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

    
    $last_annual_return = AnnualRecords::where('status', $this->settings('COMPANY_ANNUAL_RETURN_APPROVED','key')->id )
    ->where('company_id',  $request->companyId)
    ->orderBy('id','DESC')
    ->first();
    $name_change_get_from = isset($last_annual_return->date_of) ? $last_annual_return->date_of : $company_info->incorporation_at;
    
    $latest_name_change = ChangeName::where('old_company_id',$request->companyId )
                                    ->where('change_type', $this->settings('NAME_CHANGE','key')->id)
                                    ->where('status', $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id)
                                    ->whereDate('created_at', '>=', date('Y-m-d', strtotime($name_change_get_from)))
                                    ->orderBy('id','DESC')
                                    ->get();
    $latest_name_change_arr = array();

    if(isset($latest_name_change[0]->id)) {

        foreach($latest_name_change as $c) {

            $row = array(
                'old_type_id' => isset($c->old_type_id) && $c->old_type_id ?  $c->old_type_id : null,
                'old_postfix' => isset($c->old_postfix) && $c->old_postfix ? $c->old_postfix : '',
                'oldName' => isset($c->old_name) && $c->old_name ? $c->old_name : '',
                'date' => date("F j, Y, g:i a", strtotime($c->created_at))
          );
          $latest_name_change_arr[] = $row;
        }
       

        $annual_return_update_data = array(
            'former_name_of_company' =>  $latest_name_change[0]->old_name.' '.$latest_name_change[0]->old_postfix
        );
        AnnualReturn::where('company_id', $request->companyId)
        ->where('request_id',$request_id)
        ->update($annual_return_update_data);
    } else {
        $latest_name_change_arr[0] = array(
            'old_type_id' =>  null,
            'old_postfix' =>  '',
            'oldName' => '',
            'date'=> ''
    );
    }
    
    $penalty = 0;
    if($annualReturnRecord->meeting_type == 'Annual General Meeting') {
        $penalty = $this->getPanaltyCharge($annualReturnRecord->general_meeting_of_date);
    }
    if($annualReturnRecord->meeting_type == 'Resolution in Liue Thereof') {
        $penalty = $this->getPanaltyCharge($annualReturnRecord->resolution_inlieu_date);
    }

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


    $signed_directors = [];
    $saved_list = $annualReturnRecord->signed_directors;
        $saved_list_arr = explode(',', $saved_list);
        foreach($director_list as $director) {

            $row = array();
            $row['first_name'] = $director->first_name;
            $row['last_name'] = $director->last_name;
            $row['id'] = $director->id;
            $row['saved'] = in_array($director->id, $saved_list_arr) ? true : false;

            $signed_directors[] = $row;

        }


        $signed_secs = [];
        $saved_list = $annualReturnRecord->signed_secretories;
            $saved_list_arr = explode(',', $saved_list);
        foreach($sec_list as $sec) {

            $row = array();
            $row['first_name'] = $sec->first_name;
            $row['last_name'] = $sec->last_name;
            $row['id'] = $sec->id;
            $row['saved'] = in_array($sec->id, $saved_list_arr) ? true : false;

            $signed_secs[] = $row;
        }

        $signed_sec_firms = [];
        $saved_list = $annualReturnRecord->signed_sec_firms;
        $saved_list_arr = explode(',', $saved_list);
        foreach($sec_firm_list as $sec) {

            $row = array();
            $row['name'] = $sec->name;
            $row['id'] = $sec->id;
            $row['saved'] = in_array($sec->id, $saved_list_arr) ? true : false;

            $signed_sec_firms[] = $row;
        }




   $charges_recods = [];
   $charges_recods_list = [];

   $charges_recods = Charges::where('company_id', $request->companyId)->where('status',  $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id )->get()->toArray();

    if(isset($charges_recods[0])){
        foreach($charges_recods as $rec ) {

            $requested_id = $rec['request_id'];

            $amount = 0;

            $deed_items = DeedItems::where('request_id', $requested_id)->get();
            foreach($deed_items as $item ) {
              $amount += floatval($item->amount_secured);
            }
            $persons ='';
            $person_addresses ='';
            $entitled_persons = ChargesEntitledPersons::where('request_id', $requested_id)->get();
            foreach($entitled_persons as $p ) {
                $persons .= $p->name.'<br/>';
                $person_address = '';
                $person_address.= $p->address_1.',';
                $person_address.= $p->address_2;
                $person_address.= ($p->address_3) ? ',' . $p->address_3.'<br/>' : '<br/>';

                $person_addresses .= $person_address;
              }
            $row = array();
            $row['date'] = $rec['charge_date'];
            $row['description'] = $rec['short_perticular_description'];
            $row['amount'] = $amount;
            $row['persons'] = $persons;
            $row['person_addresses'] = $person_addresses;

            $charges_recods_list[] = $row;

        }
    }
   
    $form9_records = [];
  
       
      $form9_records = Form9::leftJoin('company_share_form9_records', 'company_share_form9.id', '=', 'company_share_form9_records.form9_record_id')
      ->where('company_share_form9.company_id', $request->companyId)
      ->where('company_share_form9.status', $this->settings('COMPANY_SHARE_FORM9_APPROVED','key')->id)
      ->select(
          'company_share_form9_records.shareholder_id as shareholder_id',
          'company_share_form9_records.norm_type as shareholder_type',
          'company_share_form9_records.aquire_or_redeemed as aquire_or_redeemed',
          'company_share_form9_records.aquire_or_redeemed_value as aquire_or_redeemed_value',
          'company_share_form9.date_of as date_of'

      )
      ->get()->toArray();
   

   $form9_records_list = [];

   if(isset($form_records[0]) ) {

    foreach($form9_records as $rec ) {

        if(isset($rec['shareholder_id']) && intval($rec['shareholder_id']) && $rec['shareholder_type']== 'Person') {
            $shareholder_id = $rec['shareholder_id'];

            $shareholder_info = CompanyMember::where('id', $shareholder_id)->first();

            $full_name = $shareholder_info['first_name']. ' ' . $shareholder_info['last_name'];

            $shareholder_address = '';
            if($shareholder_info['is_srilankan'] == 'yes') {
                $address = Address::where('id', $shareholder_info['address_id'])->first();
              
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }
            if($shareholder_info['is_srilankan'] == 'no') {
                $address = Address::where('id', $shareholder_info['foreign_address_id'])->first();
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= $address->province.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }

            $share_record = Share::where('company_member_id',$shareholder_id )->orderBy('id', 'desc')->first();
            $shares_held = 0;
            $share_transfer_date = $rec['date'];
            if(isset($share_record->id)) {
                $share_group = ShareGroup::where('id', $share_record->group_id)->first();
                $shares_held = isset($share_group->no_of_shares) && floatval($share_group->no_of_shares)  ? floatval($share_group->no_of_shares) : 0;
            }
            $row = array();
            $row['full_name'] = $full_name;
            $row['address'] = $shareholder_address;
            $row['shares_held'] = $shares_held;
            $row['share_transfer_date'] = $share_transfer_date;
            $row['aquire_or_redeemed'] = $rec['aquire_or_redeemed'];
            $form9_records_list[]  = $row;

            

        }

        if(isset($rec['shareholder_id']) && intval($rec['shareholder_id']) && $rec['shareholder_type']== 'Corporate-body') {
            $shareholder_id = $rec['shareholder_id'];

            $shareholder_info = CompanyFirms::where('id', $shareholder_id)->first();

            $full_name = $shareholder_info['name'];

            $shareholder_address = '';
            if($shareholder_info['is_srilankan'] == 'yes') {
                $address = Address::where('id', $shareholder_info['address_id'])->first();
              
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }
            if($shareholder_info['is_srilankan'] == 'no') {
                $address = Address::where('id', $shareholder_info['address_id'])->first();
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= $address->province.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }

            $share_record = Share::where('company_firm_id',$shareholder_id )->orderBy('id', 'desc')->first();
            $shares_held = 0;
            $share_transfer_date = $rec['date'];
            if(isset($share_record->id)) {
                $share_group = ShareGroup::where('id', $share_record->group_id)->first();
                $shares_held = isset($share_group->no_of_shares) && floatval($share_group->no_of_shares)  ? floatval($share_group->no_of_shares) : 0;
            }
            $row = array();
            $row['full_name'] = $full_name;
            $row['address'] = $shareholder_address;
            $row['shares_held'] = $shares_held;
            $row['share_transfer_date'] = $share_transfer_date;
            $row['aquire_or_redeemed'] = $rec['aquire_or_redeemed'];
            $form9_records_list[]  = $row;

            

        }

    }

   }

   $shareholder_transfer_records = [];
   $transfer_records = [];
  

  $transfer_records = ShareholderTransfer::where('request_id', $request_id)->get()->toArray();
              
   if(isset($transfer_records[0]) ) {

    foreach($transfer_records as $rec ) {

        if(isset($rec['shareholder_id']) && intval($rec['shareholder_id']) && $rec['shareholder_type']== 'natural') {
            $shareholder_id = $rec['shareholder_id'];

            $shareholder_info = CompanyMember::where('id', $shareholder_id)->first();

            $full_name = $shareholder_info['first_name']. ' ' . $shareholder_info['last_name'];

            $shareholder_address = '';
            if($shareholder_info['is_srilankan'] == 'yes') {
                $address = Address::where('id', $shareholder_info['address_id'])->first();
              
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }
            if($shareholder_info['is_srilankan'] == 'no') {
                $address = Address::where('id', $shareholder_info['foreign_address_id'])->first();
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= $address->province.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }

            $shares_held = floatval($rec['shares_held']) ? floatval($rec['shares_held']) : '';
            $share_transfer_date = $rec['transfer_date'] ? $rec['transfer_date'] : '';
            $row = array();
            $row['full_name'] = $full_name;
            $row['address'] = $shareholder_address;
            $row['shares_held'] = $shares_held;
            $row['share_transfer_date'] = $share_transfer_date;
            $row['shareholder_id'] = $shareholder_id;
            $row['shareholder_type'] = 'natural';
            $shareholder_transfer_records[]  = $row;

        }

        if(isset($rec['shareholder_id']) && intval($rec['shareholder_id']) && $rec['shareholder_type']== 'firm') {
            $shareholder_id = $rec['shareholder_id'];

            $shareholder_info = CompanyFirms::where('id', $shareholder_id)->first();

            $full_name = $shareholder_info['name'];

            $shareholder_address = '';
            if($shareholder_info['is_srilankan'] == 'yes') {
                $address = Address::where('id', $shareholder_info['address_id'])->first();
              
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }
            if($shareholder_info['is_srilankan'] == 'no') {
                $address = Address::where('id', $shareholder_info['address_id'])->first();
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= $address->province.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }

            $shares_held = floatval($rec['shares_held']) ? floatval($rec['shares_held']) : '';
            $share_transfer_date = $rec['transfer_date'] ? $rec['transfer_date'] : '';
            $row = array();
            $row['full_name'] = $full_name;
            $row['address'] = $shareholder_address;
            $row['shares_held'] = $shares_held;
            $row['share_transfer_date'] = $share_transfer_date;
            $row['shareholder_id'] = $shareholder_id;
            $row['shareholder_type'] = 'firm';
            $shareholder_transfer_records[]  = $row;

            

        }

    }

   } else {
      $transfer_records = CompanyMember::where('company_id', $request->companyId)
      ->where('designation_type' , $this->settings('SHAREHOLDER','key')->id)
      ->whereIn('status', array(1,$this->settings('ANNUAL_RETURN','key')->id ) )->get()->toArray();

      if(isset($transfer_records[0])) {
          foreach($transfer_records as $rec ) {

            $shareholder_id = $rec['id'];

            $full_name = $rec['first_name']. ' ' . $rec['last_name'];

            $shareholder_address = '';
            if($rec['is_srilankan'] == 'yes') {
                $address = Address::where('id', $rec['address_id'])->first();
              
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }
            if($rec['is_srilankan'] == 'no') {
                $address = Address::where('id', $rec['foreign_address_id'])->first();
                $shareholder_address .= $address->address1.'<br/>';
                $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
                $shareholder_address .= $address->city.'<br/>';
                $shareholder_address .= $address->province.'<br/>';
                $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

            }

            $shares_held =  '';
            $share_transfer_date =  '';
            $row = array();
            $row['full_name'] = $full_name;
            $row['address'] = $shareholder_address;
            $row['shares_held'] = $shares_held;
            $row['share_transfer_date'] = $share_transfer_date;
            $row['shareholder_id'] = $shareholder_id;
            $row['shareholder_type'] = 'natural';
            $shareholder_transfer_records[]  = $row;

          }
      }

      $transfer_records = CompanyFirms::where('company_id', $request->companyId)
      ->where('type_id' , $this->settings('SHAREHOLDER','key')->id)
      ->whereIn('status', array(1,$this->settings('ANNUAL_RETURN','key')->id ) )->get()->toArray();

      if(isset($transfer_records[0])) {
        foreach($transfer_records as $rec ) {

          $shareholder_id = $rec['id'];

          $full_name = $rec['name'];

          $shareholder_address = '';
          if($rec->is_srilankan == 'yes') {
              $address = Address::where('id', $rec['address_id'])->first();
            
              $shareholder_address .= $address->address1.'<br/>';
              $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
              $shareholder_address .= $address->city.'<br/>';
              $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

          }
          if($rec->is_srilankan == 'no') {
              $address = Address::where('id', $rec['address_id'])->first();
              $shareholder_address .= $address->address1.'<br/>';
              $shareholder_address .= ($address->address2) ? $address->address2.'<br/>' : '';
              $shareholder_address .= $address->city.'<br/>';
              $shareholder_address .= $address->province.'<br/>';
              $shareholder_address .= '<strong>'.$address->postcode.'</strong>';

          }

          $shares_held =  '';
          $share_transfer_date =  '';
          $row = array();
          $row['full_name'] = $full_name;
          $row['address'] = $shareholder_address;
          $row['shares_held'] = $shares_held;
          $row['share_transfer_date'] = $share_transfer_date;
          $row['shareholder_id'] = $shareholder_id;
          $row['shareholder_type'] = 'firm';
          $shareholder_transfer_records[]  = $row;

        }
    }
      

    


            
   }






    return response()->json([
            'message' => 'Data is successfully loaded.',
            'status' =>true,
            'data'   => array(
                //'sss' => $share_records,
                'createrValid' => true,
                'companyInfo'  => $company_info,
                'certificate_no' => $certificate_no,
                'latest_name_change' =>  $latest_name_change_arr[0],
                'all_name_changes' => $latest_name_change_arr,
                'request_id'     => ($request_id) ? $request_id : null,
                'processStatus' => $this->settings($company_info->status,'id')->key,
                'companyAddress' => $company_address_change,
                'open_company_cdp_dropdowns' => $open_company_address,
                'companyForAddress' => $company_for_address,
                'companyType'    =>$companyType,
                'countries'     => $countries_cache,
                'loginUser'     => $userPeople,
                'loginUserAddress'=> $userAddress,
                'directors' => $directors,
                'signed_directors' => $signed_directors,
                'directors_already_exists' => $directors_already_exists,
                'form9_records_list' => $form9_records_list,
                'shareholder_transfer_records' => $shareholder_transfer_records,
                'charges_recods_list' => $charges_recods_list,
                'secs' => $secs,
                'signed_secs' => $signed_secs,
                'secs_firms' => $secs_firms,
                'signed_sec_firms'=> $signed_sec_firms,
                'share_register' => $shareRegisters,
                'share_register_already_exists' => $share_register_already_exists,
                'share_records' => $shareRecords,
                'shareTypes' => $this->shareClasses($request->companyId,$request_id),
                'share_records_already_exists' => $share_records_already_exists,
                'annual_records' => $annualRecords,
                'annual_records_already_exists' => $annual_records_already_exists,
                'annual_charges' => $annualCharges,
                'annual_charges_already_exists' => $annual_charges_already_exists,
                'annual_auditors' => $annualAuditors,
                'annual_auditors_already_exists' => $annual_auditors_already_exists,
                'secs_already_exists' => $secs_already_exists,
                'shareholders' => $shareholders,
                'shareholderFirms' => $shareholderFirms,
                'sh_already_exists' => $sh_already_exists,
                'shareholders_inactive' => $shareholders_inactive,
                'shareholderFirms_inactive' => $shareholderFirms_inactive,
                'sh_inactive_already_exists' => $sh_inactive_already_exists,
                'public_path' =>  storage_path(),
                'postfix' => $company_info->postfix,
                'postfix_si' => $postfix_values['postfix_si'],
                'postfix_ta' => $postfix_values['postfix_ta'],
                
                'amount_calls_recieved' => isset($annualReturnRecord->amount_calls_recieved) ? intval($annualReturnRecord->amount_calls_recieved) : '',
                'amount_calls_unpaid' => isset($annualReturnRecord->amount_calls_unpaid) ? intval($annualReturnRecord->amount_calls_unpaid) : '',
                'amount_calls_forfeited' => isset($annualReturnRecord->amount_calls_forfeited) ? intval($annualReturnRecord->amount_calls_forfeited) : '',
                'amount_calls_purchased' => isset($annualReturnRecord->amount_calls_purchased) ? intval($annualReturnRecord->amount_calls_purchased) : '',
                'amount_calls_redeemed' => isset($annualReturnRecord->amount_calls_recieved) ? intval($annualReturnRecord->amount_calls_redeemed) : '',
                'meeting_type' => isset($annualReturnRecord->meeting_type) ? $annualReturnRecord->meeting_type : '',
                'resolution_date' => isset($annualReturnRecord->general_meeting_of_date) ? $annualReturnRecord->general_meeting_of_date : '',
                'resolution_inlieu_date' => isset($annualReturnRecord->resolution_inlieu_date) ? $annualReturnRecord->resolution_inlieu_date : '',
                'annual_return_status' =>  isset($annualReturnRecord->status) && $annualReturnRecord->status ? $this->settings($annualReturnRecord->status,'id')->key  : '', 
                'annual_return_status_id' =>  $annualReturnRecord->status, 
                'example_shareholder_bulk_data' => asset('other/annual-return-shareholder-upload.csv'),
                'shareholder_bulk_format' => asset('other/annual-return-shareholder-upload.xlsx'),
                'example_member_bulk_data' => asset('other/annual-return-member-upload.csv'),
                'member_bulk_format' => asset('other/annual-return-member-upload.xlsx'),
                'example_ceased_shareholder_bulk_data' => asset('other/annual-return-ceased-shareholder-upload-example-data.csv'),
                'ceased_shareholder_bulk_format' => asset('other/annual-return-ceased-shareholder-upload-format.xlsx'),
                'example_ceased_member_bulk_data' => asset('other/annual-return-ceased-member-upload-example-data.csv'),
                'ceased_member_bulk_format' => asset('other/annual-return-ceased-member-upload-format.xlsx'),
                'dates'  =>  $this->get_annual_return_dates($request->companyId),
                'penalty_charge' => $penalty,


                'downloadDocs' => $this->generate_annual_return_report($request->companyId,array(

                    'company_info' => $company_info,
                    'certificate_no' => $certificate_no,
                    'latest_name_change' => $latest_name_change_arr[0],
                    'companyType' => $this->settings($company_info->type_id,'id'),
                    'loginUser'     => $userPeople,
                    'loginUserAddress'=> $userAddress,
                    'company_address' => $company_address_change,
                    'directors' => $directors,
                    'signed_directors' => $signed_directors,
                    'directors_on_declaration' =>$directors_on_declaration,
                    'secs' => $secs,
                    'signed_secs' =>$signed_secs,
                    'secFirms' => $secs_firms,
                    'signed_sec_firms' => $signed_sec_firms,
                    'shareholders' => $shareholders,
                    'shareholderFirms' => $shareholderFirms,
                    'shareholders_inactive' => $shareholders_inactive,
                    'shareholderFirms_inactive' => $shareholderFirms_inactive,
                    'share_register' => $shareRegisters,
                    'annual_records' => $annualRecords,
                    'annual_charges' => $annualCharges,
                    'annual_auditors' => $annualAuditors,
                    'share_records' => $shareRecords,
                    'postfix' => $company_info->postfix,
                    'postfix_si' => $postfix_values['postfix_si'],
                    'postfix_ta' => $postfix_values['postfix_ta'],
                    'amount_calls_recieved' => isset($annualReturnRecord->amount_calls_recieved) ? intval($annualReturnRecord->amount_calls_recieved) : '',
                    'amount_calls_unpaid' => isset($annualReturnRecord->amount_calls_unpaid) ? intval($annualReturnRecord->amount_calls_unpaid) : '',
                    'amount_calls_forfeited' => isset($annualReturnRecord->amount_calls_forfeited) ? intval($annualReturnRecord->amount_calls_forfeited) : '',
                    'amount_calls_purchased' => isset($annualReturnRecord->amount_calls_purchased) ? intval($annualReturnRecord->amount_calls_purchased) : '',
                    'amount_calls_redeemed' => isset($annualReturnRecord->amount_calls_recieved) ? intval($annualReturnRecord->amount_calls_redeemed) : '',
                    'meeting_type' => isset($annualReturnRecord->meeting_type) ? $annualReturnRecord->meeting_type : '',
                    'resolution_date' => isset($annualReturnRecord->general_meeting_of_date) ? $annualReturnRecord->general_meeting_of_date : '',
                    'resolution_inlieu_date' => isset($annualReturnRecord->resolution_inlieu_date) ? $annualReturnRecord->resolution_inlieu_date : '',
                    'dates'  =>  $this->get_annual_return_dates($request->companyId),
                    'form9_records_list' => $form9_records_list,
                    'shareholder_transfer_records' => $shareholder_transfer_records,
                    'charges_recods_list' => $charges_recods_list
                )),
                'coreShareGroups' => $core_groups_list,
                'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                'court_data' => $court_data_arr,
                'external_global_comment' => $external_global_comment,
                'form15_payment' => $this->settings('PAYMENT_FORM15','key')->value,
                'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                )
        ], 200);
          
    }

    private function shareClasses($companyId, $request_id ) {

        $previousShareRecord_count = ShareClasses::where('company_id', $companyId)
             ->where('status', $this->settings('ISSUE_OF_SHARES_APPROVED','key')->id)
             ->count();
        $new_sharerecord = null;
        if(!$previousShareRecord_count) {

            $new_sharerecord = ShareClasses::where('company_id', $companyId)
             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
             ->where('request_id', $request_id)
             ->first();
             
        }

        $share_types = $this->settings('SHARE_TYPES');
       
        foreach($share_types as $type ) {

            if($type->key == 'OTHER_SHARE'){
                continue;
            }
            $share_call_record_count = ($previousShareRecord_count) ? 0 : 
            
            ( isset($new_sharerecord->id) ? ShareIssueRecords::where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                            ->where('record_id', $new_sharerecord->id)
                                            ->where('share_class', $type->key)
                                            ->count() : 0 );
            if($share_call_record_count){
                continue;
            }

            $row = array(
                    'id' => $type->id,
                    'key' => $type->key,
                    'value' => $type->value
            );
            $share_type_arr[] = $row;

        }

        $row = array(
            'id' => $this->settings('OTHER_SHARE','key')->id,
            'key' => 'OTHER_SHARE',
            'value' => 'Other'
        );
        $share_type_arr[] = $row;

        return $share_type_arr;
    }

    private function getPanaltyCharge($resoultion_date) {


        if(!$resoultion_date) {
            return 0;
        }

         $min_date_gap = 30;
        $increment_gap_dates = 30;
        $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_15_INITIAL','key')->value );
        $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_15_INCREMENT','key')->value );
        $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_15_MAX','key')->value );

        $increment_gaps = 0;

        $penalty_value = 0;


        $res_date = '';
        if( $res_date = strtotime($resoultion_date))  {

            $today = time();
            $date_gap =  intval( ($today - $res_date) / (24*60*60) );

            if( $min_date_gap >= $date_gap ) {
                return 0;
            }

            $increment_gaps = ( $date_gap % $increment_gap_dates == 0 ) ? $date_gap / $increment_gap_dates : intval($date_gap / $increment_gap_dates) + 1;
            $penalty_value  = $penalty_value + $init_panalty;

            if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
            }

            return ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;




        }

       
        

    }


    private function get_annual_return_dates($company_id){

        $this_year = date('Y',time());
        $previous_year = $this_year - 1 ;

        $dates = array(
            'this_year_annual_return_date' => '',
            'last_year_annual_return_date' => '',
            'is_incorporation_date_as_last_annual_return' => false,
            'incorporation_date' => '',
            
        );

        $company_info = Company::where('id', $company_id)->first();
        if(isset($company_info->incorporation_at) && $company_info->incorporation_at ) {
            $dates['incorporation_date'] = $company_info->incorporation_at;
        }

        $this_year_annual_return_record =  AnnualReturn::where('company_id', $company_id)
            ->where('year', $this_year)
            ->first();
        if(isset($this_year_annual_return_record->date_of_annual_return) && $this_year_annual_return_record->date_of_annual_return){
           $dates['this_year_annual_return_date'] = $this_year_annual_return_record->date_of_annual_return;
        }

        $last_year_annual_return_record =  AnnualReturn::where('company_id', $company_id)
             ->where('year', $previous_year)
             ->first();
        if(isset($last_year_annual_return_record->date_of_annual_return) && $last_year_annual_return_record->date_of_annual_return){
                $dates['last_year_annual_return_date'] = $last_year_annual_return_record->date_of_annual_return;

                $annual_return_update_data = array(
                    'date_of_last_annual_return' =>  $dates['last_year_annual_return_date']
                );
                AnnualReturn::where('company_id', $company_id)
                ->where('year',$this_year)
                ->update($annual_return_update_data);

               
        }

        if( $dates['incorporation_date']) {
            $incorporation_year = date('Y',strtotime($dates['incorporation_date']) );

            if(!$dates['last_year_annual_return_date']) {
                if($incorporation_year >= $previous_year ) {
                    $dates['is_incorporation_date_as_last_annual_return'] = true;
                    $dates['last_year_annual_return_date'] = $dates['incorporation_date'];

                    $annual_return_update_data = array(
                        'date_of_last_annual_return' =>  $dates['last_year_annual_return_date']
                    );
                    AnnualReturn::where('company_id', $company_id)
                    ->where('year',$this_year)
                    ->update($annual_return_update_data);


                } else {
                    $dates['last_year_annual_return_date'] = isset($this_year_annual_return_record->date_of_last_annual_return) && $this_year_annual_return_record->date_of_last_annual_return ? $this_year_annual_return_record->date_of_last_annual_return : '';
                }
            }
        }

        return $dates;
       

    }

    private function incorporationPaymentValue( $company_type ) {

        if($company_type == 'COMPANY_TYPE_PRIVATE') {
            return  $this->settings('PAYMENT_PRIVATE_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_PUBLIC') {
            return  $this->settings('PAYMENT_PUBLIC_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_UNLIMITED') {
            return  $this->settings('PAYMENT_UNLIMITED_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_GUARANTEE_32' || $company_type == 'COMPANY_TYPE_GUARANTEE_34' ) {
            return  $this->settings('PAYMENT_GURANTEE_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_OVERSEAS') {
            return  $this->settings('PAYMENT_OVERSEAS_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_OFFSHORE') {
            return  $this->settings('PAYMENT_OFFSHORE_COMPANY_REGISTRATION','key')->value;
        }
    }


    public function requestApprovalForForeign(Request $request ){

        $company_update =  array(

            'status'    => $this->settings('COMPANY_FOREIGN_STATUS_PENDING','key')->id 
        );
        Company::where('id', $request->company_id)->update($company_update);

        return response()->json([
            'message' => 'Successfully requested the registration approval',
            'status' =>true,
           
        ], 200);
    }


    public function submitPay(Request $request ){

        $company_update =  array(
            'status'    => $this->settings('COMPANY_STATUS_PENDING','key')->id 
        );
        Company::where('id', $request->company_id)->update($company_update);

        return response()->json([
            'message' => 'Payment Successful.',
            'status' =>true,
           
        ], 200);
    }

    private function valid_annual_return_request_operation($company_id){

        $accepted_request_statuses = array(
            $this->settings('ANNUAL_RETURN_PROCESSING','key')->id,
            $this->settings('ANNUAL_RETURN_RESUBMIT','key')->id
        );
        $annual_return_request_type =  $this->settings('ANNUAL_RETURN','key')->id;

        $exist_request_id = $this->has_annual_record_for_this_year($company_id);

       

        if($exist_request_id) {

            $request_count = CompanyChangeRequestItem::where('request_type',$annual_return_request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $exist_request_id)
                               ->whereIn('status', $accepted_request_statuses )
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
                $request->request_type = $annual_return_request_type;
                $request->status = $this->settings('ANNUAL_RETURN_PROCESSING','key')->id;
                $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
                $request->save();
                $request_id =  $request->id;

                $record = new AnnualReturn;
                $record->company_id = $company_id;
                $record->year = $year;
                $record->date_of = date('Y-m-d',time());
                $record->request_id = $request_id;
                $record->status = $this->settings('ANNUAL_RETURN_PROCESSING','key')->id;
                $record->save();
                $record_id =  $record->id;

                if($record_id && $request_id ) {
                    return $request_id;
                }else{
                    return false;
                }

        }
        
    }

    private function valid_annual_return_record_operation($company_id){
        $year = date('Y',time());
        $request_id = false;

        if($request_id = $this->valid_annual_return_request_operation) {

            $record_count = AnnualReturn::where('company_id',$company_id)
                               ->where('year', $year)
                               ->where('request_id', $request_id)
                               ->count();
            if($record_count !== 1 ) {
                
                $record = new AnnualReturn;
                $record->company_id = $company_id;
                $record->year = $year;
                $record->date_of = date('Y-m-d',time());
                $record->request_id = $request_id;
                $record->save();
                return $record->id;

            } else {
                $record = CompanyChangeRequestItem::where('company_id',$company_id)
                               ->where('year', $year)
                               ->where('request_id', $request_id)
                               ->first();
                return $record->id;
            }

        } else {
            return false;
        }
    }
    private function has_annual_record_for_this_year($company_id) {

        $accepted_request_statuses = array(
            $this->settings('ANNUAL_RETURN_PROCESSING','key')->id,
            $this->settings('ANNUAL_RETURN_RESUBMIT','key')->id
        );

        $year = date('Y',time());
        $record_count = AnnualReturn::where('company_id',$company_id)
                               ->where('year', $year)
                               ->whereIn('status', $accepted_request_statuses )
                               ->count();
        if( $record_count === 1 ) {
            $record = AnnualReturn::where('company_id',$company_id)
            ->where('year', $year)
            ->whereIn('status', $accepted_request_statuses )
            ->first();

            return $record->request_id;
        } else {
            return false;
        }
    }


    public function submitStep1(Request $request){

        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id')->key;

        $addressId = $company_info->address_id;
        $forAddressId = $company_info->foreign_address_id;

        $company_address = Address::where('id',$addressId)->first();


        $annual_return_update_data = array(
            'meeting_type' => $request->meeting_type,
            'general_meeting_of_date' =>  ( $request->meeting_type &&  $request->meeting_type == 'Annual General Meeting') ? date('Y-m-d',strtotime($request->resolution_date) ) : null,
            'resolution_inlieu_date' =>  ($request->resolution_inlieu_date &&  $request->meeting_type =='Resolution in Liue Thereof') ? date('Y-m-d',strtotime($request->resolution_inlieu_date) ) : null,
            'date_of_annual_return' => ($request->this_year_annual_return_date) ? date('Y-m-d',strtotime($request->this_year_annual_return_date) ) : '',
            'date_of_last_annual_return' => ($request->last_year_annual_return_date) ? date('Y-m-d',strtotime($request->last_year_annual_return_date) ) : '',
        );
        AnnualReturn::where('company_id', $company_id)
        ->where('request_id', $request_id)
        ->where('year', date('Y',time()))
         ->update($annual_return_update_data);

        $open_company_address = $this->localAddressOpenStatus($company_address);


        if(FALSE == $open_company_address) {

            CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('UNCHANGED','key')->id)
            ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
            ->delete();

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('UNCHANGED','key')->id;
            $change->item_id = $addressId;
            $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;


            return response()->json([
                'message' => 'Success.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

            exit();
        }

        $company_address = new Address;
        $company_address->address1 = $request->address1;
        $company_address->address2 = $request->address2;
        $company_address->city = $request->city;
        $company_address->district = $request->district;
        $company_address->province = $request->province;
        $company_address->gn_division = $request->gn_division;
        $company_address->postcode = $request->postcode;

        $company_address->save();
        $new_company_address_id = $company_address->id;

        //delete previous tasks when available
        $prev_address_record_count = CompanyItemChange::where('request_id',$request_id)
                                   ->where('changes_type', $this->settings('ADD','key')->id)
                                   ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                                   ->count();
        if($prev_address_record_count) {
            $prev_address_record = CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
            ->first();
            $prev_address_id = $prev_address_record->item_id;

            //remove prev address
            $delete = Address::where('id', $prev_address_id)->delete();
            //remove item change
            CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
            ->delete();

        }
        
        //save changes
        $change = new CompanyItemChange;
        $change->request_id = $request_id;
        $change->changes_type = $this->settings('ADD','key')->id;
        $change->item_id = $new_company_address_id;
        $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

        $change->save();
        $change_id = $change->id;

       
        
        
        return response()->json([
            'message' => 'data.',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => $change_id,
        ], 200);

    }
 
    function removeStakeHolder(Request  $request ){

          $stakeholder_id = $request->userId;
          $company_id = $request->companyId;

          $stakeholder_info = CompanyMember::where('id', $stakeholder_id)
                                  ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                  ->first();

          if(isset($stakeholder_info->address_id) && $stakeholder_info->address_id){
            Address::where('id', $stakeholder_info->address_id)->delete();
            CompanyItemChange::where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $stakeholder_info->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();
          }
          if(isset($stakeholder_info->foreign_address_id) && $stakeholder_info->foreign_address_id){
            Address::where('id', $stakeholder_info->foreign_address_id)->delete();
            CompanyItemChange::where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $stakeholder_info->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();
          }

          CompanyItemChange::where('changes_type', $this->settings('ADD','key')->id)
                           ->where('item_id', $stakeholder_id)
                           ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                           ->delete();

          $delete = CompanyMember::where('id', $stakeholder_id)
                                  ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                  ->delete();


          if($delete){

            return response()->json([
                'message' => 'Successfully deleted the stakeholder',
                'status' =>true
               
            ], 200);

          }else{
            return response()->json([
                'message' => 'Failed deleting the stakeholder. Please try again',
                'status' =>false
              
            ], 200);
          }

         
    }

    function submitDirectors(Request $request){

     //   print_r($request->directors['directors']);
     //   echo 'ha ha';
     //   die();
        
        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

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

        $directors_on_declaration = array();

        $director_count = CompanyMember::where('company_id',$company_id)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->count();
        
        if($director_count) {
            foreach($request->directors['directors'] as $director ){

                if($companyType->key == 'COMPANY_TYPE_PUBLIC' || $companyType->key == 'COMPANY_TYPE_UNLIMITED') {
                    if($director['listed_on_declaration']) {
                        $directors_on_declaration[] = $director['id'];
                     }
                } 
                else if($companyType->key == 'COMPANY_TYPE_PRIVATE') {
                    $directors_on_declaration[] = $director['id'];
                } else {
                    // do nothing
                }
                

                if($director['new_nic']) {

                    $update_arr = array(
                        'new_format_nic' => $director['new_nic']
                   );
                    CompanyMember::where('company_id', $company_id)
                    ->where('id', $director['id'])
                   // ->where('year', date('Y',time()))
                    ->update($update_arr);

                    CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_id', $director['id'])
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->delete();
   
                    $change = new CompanyItemChange;
                    $change->request_id = $request_id;
                    $change->changes_type = $this->settings('EDIT','key')->id;
                    $change->item_id = $director['id'];
                    $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
    
                    $change->save();
                    $change_id = $change->id;

                } else {
                    CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                    ->where('item_id', $director['id'])
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->delete();
   
                    $change = new CompanyItemChange;
                    $change->request_id = $request_id;
                    $change->changes_type = $this->settings('UNCHANGED','key')->id;
                    $change->item_id = $director['id'];
                    $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
    
                    $change->save();
                    $change_id = $change->id;
                }

                


            }


            $signed_director_arr = array();
            if(count($request->signed_directors['director'])) {
                
                foreach($request->signed_directors['director'] as $director ){
                    if($director['saved']) {
                        $signed_director_arr[] = $director['id'];
                    }
                }
               
            }



            $update_arr = array(
                'signed_directors' => implode(',', $signed_director_arr),
                'directors_on_declaration' => implode(',', $directors_on_declaration),
           );
            AnnualReturn::where('company_id', $company_id)
            ->where('request_id', $request_id)
           // ->where('year', date('Y',time()))
            ->update($update_arr);
            

            

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

             exit();
            
        }

        /***remove part done from here
         * 
         * remove directors
         * remove director addresses
         * 
        */
        $annual_return_directors_count = CompanyMember::where('company_id', $company_id)
                                                ->where('designation_type', $this->settings('DERECTOR','key')->id )
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($annual_return_directors_count){
            $annual_return_directors = CompanyMember::where('company_id', $company_id)
                                                ->where('designation_type', $this->settings('DERECTOR','key')->id )
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($annual_return_directors as $d ) {
                 if(isset($d->address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                    /*CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                 ->delete();
                 CompanyMember::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

        //loop through add director list
        foreach($request->directors['directors'] as $director ){

            $new_director_local_address_id= null;
            $new_director_foreign_address_id = null;
            
            if($director['province'] || $director['district'] ||  $director['city'] || $director['localAddress1'] || $director['localAddress2'] || $director['postcode'] ) {
                $address = new Address;
                $address->province = $director['province'];
                $address->district =  $director['district'];
                $address->city =  $director['city'];
                $address->address1 =  $director['localAddress1'];
                $address->address2 =  $director['localAddress2'];
                $address->postcode = $director['postcode'];
                $address->country =   'Sri Lanka';
            
                $address->save();
                $new_director_local_address_id = $address->id;

                /*$change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_director_local_address_id;
                $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

                $change->save();
                $change_id = $change->id;*/
            }

            if($director['forProvince'] ||  $director['forCity'] || $director['forAddress1'] || $director['forAddress2'] || $director['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $director['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $director['forCity'];
             $forAddress->address1 =  $director['forAddress1'];
             $forAddress->address2 =  $director['forAddress2'];
             $forAddress->postcode = $director['forPostcode'];
             $forAddress->country =  $director['country'];
           
             $forAddress->save();
             $new_director_foreign_address_id = $forAddress->id;

             /*$change = new CompanyItemChange;
             $change->request_id = $request_id;
             $change->changes_type = $this->settings('ADD','key')->id;
             $change->item_id = $new_director_foreign_address_id;
             $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

             $change->save();
             $change_id = $change->id;*/
            }


            $newDirector = new CompanyMember;
            $newDirector->company_id = $company_id;
            $newDirector->designation_type =  $this->settings('DERECTOR','key')->id;
            $newDirector->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
            $newDirector->title = $director['title'];
            $newDirector->first_name = $director['firstname'];
            $newDirector->last_name = $director['lastname'];
            $newDirector->nic = strtoupper($director['nic']);
            $newDirector->passport_no = $director['passport'];
            $newDirector->address_id = $new_director_local_address_id;
            $newDirector->foreign_address_id =  $new_director_foreign_address_id;
            $newDirector->passport_issued_country = isset( $director['passport_issued_country']) ? $director['passport_issued_country'] : $director['country'];
            $newDirector->telephone = $director['phone'];
            $newDirector->mobile =$director['mobile'];
            $newDirector->email = $director['email'];
            $newDirector->occupation = $director['occupation'];
            $newDirector->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
            $newDirector->status =  $this->settings('ANNUAL_RETURN','key')->id;
            $newDirector->save();
            $new_director_id = $newDirector->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_director_id;
            $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;

            $change->save();
            $change_id = $change->id;


            if($companyType->key == 'COMPANY_TYPE_PUBLIC' || $companyType->key == 'COMPANY_TYPE_UNLIMITED') {
                if($director['listed_on_declaration']) {
                    $directors_on_declaration[] = $new_director_id;
                 }
            } 
            else if($companyType->key == 'COMPANY_TYPE_PRIVATE') {
                $directors_on_declaration[] = $new_director_id;
            } else {
                // do nothing
            }

      }

      $signed_director_arr = array();
            if(count($request->signed_directors['director'])) {
                
                foreach($request->signed_directors['director'] as $director ){
                    if($director['saved']) {
                        $signed_director_arr[] = $director['id'];
                    }
                }
               
     }

       $update_arr = array(
        'signed_directors' => implode(',', $signed_director_arr),
        'directors_on_declaration' => implode(',', $directors_on_declaration),
        );
            AnnualReturn::where('company_id', $company_id)
            ->where('request_id', $request_id)
        // ->where('year', date('Y',time()))
            ->update($update_arr);

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }

    function submitSecretories(Request $request){

        $company_id = $request->companyId;
        $request_id = $this->valid_annual_return_request_operation($company_id);
        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }
        $sec_count = CompanyMember::where('company_id', $company_id)
                                                ->where('designation_type', $this->settings('SECRETARY','key')->id )
                                                ->where('status',1)
                                                ->count();
        $sec_firm_count = CompanyFirms::where('company_id', $company_id)
                                                ->where('type_id', $this->settings('SECRETARY','key')->id )
                                                ->where('status',1)
                                                ->count();
        if($sec_count || $sec_firm_count ) {

            foreach($request->secretories['secs'] as $sec ){

                if( $sec['secType'] == 'firm' ) {

                    CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                    ->where('item_id', $sec['id'])
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->delete();

                    $change = new CompanyItemChange;
                    $change->request_id = $request_id;
                    $change->changes_type = $this->settings('UNCHANGED','key')->id;
                    $change->item_id = $sec['id'];
                    $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                    $change->save();
                  


                }else {

                    if($sec['new_nic']) {
                        $update_arr = array(
                            'new_format_nic' => $sec['new_nic']
                       );
                        CompanyMember::where('company_id', $company_id)
                        ->where('id', $sec['id'])
                        ->update($update_arr);

                        CompanyItemChange::where('request_id',$request_id)
                        ->where('changes_type', $this->settings('EDIT','key')->id)
                        ->where('item_id',$sec['id'])
                        ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                        ->delete();

                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('EDIT','key')->id;
                        $change->item_id = $sec['id'];
                        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                        $change->save();

                    } else {

                        CompanyItemChange::where('request_id',$request_id)
                        ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                        ->where('item_id',$sec['id'])
                        ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                        ->delete();

                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('UNCHANGED','key')->id;
                        $change->item_id = $sec['id'];
                        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                        $change->save();

                    }

                    
                    
                }


            }


            $signed_sec_arr = array();
            if(count($request->signedsecs['secs'])) {
                
                foreach($request->signedsecs['secs'] as $sec ){
                    if($sec['saved']) {
                        $signed_sec_arr[] = $sec['id'];
                    }
                }
               
            }
            $update_arr = array(
                'signed_secretories' => implode(',', $signed_sec_arr)
           );
            AnnualReturn::where('company_id', $company_id)
            ->where('request_id', $request_id)
           // ->where('year', date('Y',time()))
            ->update($update_arr);


            $signed_sec_firm_arr = array();
            if(count($request->signedsecfirms['secs'])) {
                
                foreach($request->signedsecfirms['secs'] as $sec ){
                    if($sec['saved']) {
                        $signed_sec_firm_arr[] = $sec['id'];
                    }
                }
               
            }
            $update_arr = array(
                'signed_sec_firms' => implode(',', $signed_sec_firm_arr)
            );
            AnnualReturn::where('company_id', $company_id)
            ->where('request_id', $request_id)
           // ->where('year', date('Y',time()))
            ->update($update_arr);
            


            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        /***remove part done from here
         * 
         * remove sec/sec firms
         * remove sec/sec firms addresses
         * 
        */

        //sec induvidual
        $annual_return_sec_count = CompanyMember::where('company_id', $company_id)
                                                ->where('designation_type', $this->settings('SECRETARY','key')->id )
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($annual_return_sec_count){
            $annual_return_secs = CompanyMember::where('company_id', $company_id)
                                                ->where('designation_type', $this->settings('SECRETARY','key')->id )
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($annual_return_secs as $d ) {
                 if(isset($d->address_id)) {

                    /*CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                    /*CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                 ->delete();
                 CompanyMember::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

      
        //sec firm
        $annual_return_sec_firm_count = CompanyFirms::where('company_id', $company_id)
                                                ->where('type_id', $this->settings('SECRETARY','key')->id )
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($annual_return_sec_firm_count){
            $annual_return_sec_firms = CompanyFirms::where('company_id', $company_id)
                                                ->where('type_id', $this->settings('SECRETARY','key')->id )
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($annual_return_sec_firms as $d ) {
                 if(isset($d->address_id)) {

                    /*CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                 ->delete();
                 CompanyFirms::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('type_id', $this->settings('SECRETARY','key')->id )
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
      

       // end remore part

        foreach($request->secretories['secs'] as $sec ){

            $new_companyFirmAddressId = null;
            $new_addressId= null;
            $new_forAddressId = null;
     
            if( $sec['secType'] == 'firm' ) {

                $companyFirmAddress = new Address;
                $companyFirmAddress->province = $sec['firm_province'];
                $companyFirmAddress->district =  $sec['firm_district'];
                $companyFirmAddress->city =  $sec['firm_city'];
                $companyFirmAddress->address1 =  $sec['firm_localAddress1'];
                $companyFirmAddress->address2 =  $sec['firm_localAddress2'];
                $companyFirmAddress->postcode = $sec['firm_postcode'];
                $companyFirmAddress->country = isset($sec['firm_country'] ) ? $sec['firm_country'] : 'Sri Lanka';
              
                $companyFirmAddress->save();
                $new_companyFirmAddressId = $companyFirmAddress->id;

                /*$change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_companyFirmAddressId;
                $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;
                $change->save();
                $change_id = $change->id;*/

            } else {

                if($sec['province'] || $sec['district'] ||  $sec['city'] || $sec['localAddress1'] || $sec['localAddress2'] || $sec['postcode'] ) {
                 $address = new Address;
               //  $address->id = 9999;
                 $address->province = $sec['province'];
                 $address->district =  $sec['district'];
                 $address->city =  $sec['city'];
                 $address->address1 =  $sec['localAddress1'];
                 $address->address2 =  $sec['localAddress2'];
                 $address->postcode = $sec['postcode'];
                 $address->country =  'Sri Lanka';
               
                 $address->save();
                 $new_addressId = $address->id;

                 /*$change = new CompanyItemChange;
                 $change->request_id = $request_id;
                 $change->changes_type = $this->settings('ADD','key')->id;
                 $change->item_id = $new_addressId;
                 $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;
                 $change->save();
                 $change_id = $change->id;*/
                }
                
                $postcodecheck =  ($companyType->key === 'COMPANY_TYPE_OVERSEAS' || $companyType->key === 'COMPANY_TYPE_OFFSHORE') ? true : isset($sec['forPostcode']);
                if(
                   ( isset( $sec['forProvince'] ) && isset($sec['forCity']) && isset($sec['forAddress1']) && isset($sec['forAddress2']) && $postcodecheck ) && 
                   ( $sec['forProvince'] ||  $sec['forCity'] || $sec['forAddress1'] || $sec['forAddress2'] || $sec['forPostcode'] )
                    
                ) {
                 $forAddress = new Address;
               //  $address->id = 9999;
                 $forAddress->province = $sec['forProvince'];
                 $forAddress->district = null;
                 $forAddress->city =  $sec['forCity'];
                 $forAddress->address1 =  $sec['forAddress1'];
                 $forAddress->address2 =  $sec['forAddress2'];
                 $forAddress->postcode = $sec['forPostcode'];
                 $forAddress->country =  $sec['country'];
                 $forAddress->save();
                 $new_forAddressId = $forAddress->id;

                 /*$change = new CompanyItemChange;
                 $change->request_id = $request_id;
                 $change->changes_type = $this->settings('ADD','key')->id;
                 $change->item_id = $new_forAddressId;
                 $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;
                 $change->save();
                 $change_id = $change->id;*/
                }

            }


            if( $sec['secType'] == 'firm' ) {

                $newSec = new CompanyFirms;
                $newSec->email  = $sec['firm_email'];
                $newSec->mobile = $sec['firm_mobile'];
                $newSec->phone  = $sec['firm_phone'];
                $newSec->date_of_appointment = $sec['firm_date'];
                $newSec->company_id = $company_id;
                $newSec->address_id = $new_companyFirmAddressId;
                $newSec->type_id = $this->settings('SECRETARY','key')->id;
                $newSec->status =  $this->settings('ANNUAL_RETURN','key')->id;
                $newSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';

                /*$company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                    $updateSec->registration_no = $sec['pvNumber'];
                    $updateSec->name = $sec['firm_name'];
                }*/
                $newSec->registration_no = $sec['pvNumber'];
                $newSec->name = $sec['firm_name'];
                $newSec->save();
                $new_sec_id = $newSec->id;

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_sec_id;
                $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                $change->save();
                $change_id = $change->id;
              
            }else {
                $newSec = new CompanyMember;
                $newSec->company_id = $company_id;
                $newSec->designation_type = $this->settings('SECRETARY','key')->id;
                $newSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';

                /*$company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                 //   $updateSec->title = $sec['title'];
                    $updateSec->first_name = $sec['firstname'];
                    $updateSec->last_name = $sec['lastname']; 
                    $updateSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
                    $updateSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
                }*/
                $newSec->first_name = $sec['firstname'];
                $newSec->last_name = $sec['lastname']; 
                $newSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
                $newSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
                $newSec->address_id = $new_addressId;
                $newSec->foreign_address_id = $new_forAddressId;
                $newSec->passport_issued_country = isset( $sec['passport_issued_country'] ) ? $sec['passport_issued_country']  : null ;
                $newSec->telephone = $sec['phone'];
                $newSec->mobile =$sec['mobile'];
                $newSec->email = $sec['email'];
                $newSec->occupation = $sec['occupation'];
                $newSec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                $newSec->is_registered_secretary = ($sec['isReg'] == true ) ? 'yes' : 'no';
                $newSec->secretary_registration_no =  (isset($sec['regDate']) && $sec['regDate'] ) ? $sec['regDate'] : NULL;
                $newSec->status = $this->settings('ANNUAL_RETURN','key')->id;
                $newSec->is_natural_person = $sec['secType'] =='natural' ? 'yes' : 'no';
                $newSec->save();
                $new_sec_id = $newSec->id;

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_sec_id;
                $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                $change->save();
                $change_id = $change->id;
   
            }

            
        }
        

    }

    function submitShareholderTransfers( Request $request ) {

     //   print_r($request);
     //   die();
        $company_id = $request->companyId;
        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
            ], 200);

             exit();

        }
        if( !count($request->shareholder_transfers['records'])) {
            return response()->json([
                'message' => 'No records to be submitted.',
                'status' =>true,
            ], 200);

             exit();
        }else {

            //remove all 
            ShareholderTransfer::where('request_id', $request_id)->delete();

            foreach($request->shareholder_transfers['records'] as $sr ){

                if(!( $sr['share_transfer_date'] && $sr['shares_held']) ) {
                    continue;
                }

                $transfer = new ShareholderTransfer;
                $transfer->shareholder_id = $sr['shareholder_id'];
                $transfer->shareholder_type = $sr['shareholder_type'];
                $transfer->request_id = $request_id;
                $transfer->shares_held = $sr['shares_held'];
                $transfer->transfer_date = $sr['share_transfer_date'];
                $transfer->save();
    
            }

            return response()->json([
                'message' => 'Successfully submitted shareholder share transfer detailsl.',
                'status' =>true,
            ], 200);

             exit();

        }
        
        
    }

    function submitShareolders( Request $request ){

        $company_id = $request->companyId;
        $request_id = $this->valid_annual_return_request_operation($company_id);
        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');
        $set_operation =  isset($request->set_operation) && $request->set_operation ==='active' ? 'active' : 'inactive';

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }
        if($set_operation === 'active') {
               $sh_count = CompanyMember::where('company_id', $company_id)
                                            ->where('designation_type', $this->settings('SHAREHOLDER','key')->id )
                                            ->where('status',1)
                                            ->count();
               $sh_firm_count = CompanyFirms::where('company_id', $company_id)
                                            ->where('type_id', $this->settings('SHAREHOLDER','key')->id )
                                            ->where('status',1)
                                            ->count();

                if($sh_count || $sh_firm_count ) {

                    foreach($request->shareholders['shs'] as $shareholder ){

                        if( $shareholder['shareholderType'] === 'natural' ){

                            if($shareholder['new_nic']) {

                                $update_arr = array(
                                    'new_format_nic' => $shareholder['new_nic']
                               );
                                CompanyMember::where('company_id', $company_id)
                                ->where('id', $shareholder['id'])
                                ->update($update_arr);

                                CompanyItemChange::where('request_id',$request_id)
                                            ->where('changes_type', $this->settings('EDIT','key')->id)
                                            ->where('item_id', $shareholder['id'])
                                            ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                                            ->delete();

                                $change = new CompanyItemChange;
                                $change->request_id = $request_id;
                                $change->changes_type = $this->settings('EDIT','key')->id;
                                $change->item_id = $shareholder['id'];
                                $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                                $change->save();

                            } else {

                                CompanyItemChange::where('request_id',$request_id)
                                            ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                                            ->where('item_id', $shareholder['id'])
                                            ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                                            ->delete();

                                $change = new CompanyItemChange;
                                $change->request_id = $request_id;
                                $change->changes_type = $this->settings('UNCHANGED','key')->id;
                                $change->item_id = $shareholder['id'];
                                $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                                $change->save();

                            }


                        } else {

                            CompanyItemChange::where('request_id',$request_id)
                            ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                            ->where('item_id', $shareholder['id'])
                            ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                            ->delete();


                            $change = new CompanyItemChange;
                            $change->request_id = $request_id;
                            $change->changes_type = $this->settings('UNCHANGED','key')->id;
                            $change->item_id = $shareholder['id'];
                            $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                            $change->save();
                          

                        }


                    }


                    return response()->json([
                        'message' => 'Invalid Request.',
                        'status' =>false,
                        'request_id'   => null,
                        'change_id'    => null,
                    ], 200);
        
                     exit();

                }

                

        }

         /***remove part done from here
         * 
         * remove shareholder/sh firms
         * remove shareholder/sh firms addresses
         * 
        */
        //sh induvidual
        $set_operation_status = ($set_operation === 'active' ) ? 'ANNUAL_RETURN' : 'ANNUAL_RETURN_FALSE';
        $annual_return_sh_count = CompanyMember::where('company_id', $company_id)
                                                ->where('designation_type', $this->settings('SHAREHOLDER','key')->id )
                                                ->where('status',$this->settings($set_operation_status,'key')->id)
                                                ->count();
        if($annual_return_sh_count){
            $annual_return_shs = CompanyMember::where('company_id', $company_id)
                                                ->where('designation_type', $this->settings('SHAREHOLDER','key')->id )
                                                ->where('status',$this->settings($set_operation_status,'key')->id)
                                                ->get();
            foreach($annual_return_shs as $d ) {
                 if(isset($d->address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                 ->delete();
                 CompanyMember::where('id', $d->id)
                             ->where('status', $this->settings($set_operation_status,'key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

      
        //sec firm
        $annual_return_sh_firm_count = CompanyFirms::where('company_id', $company_id)
                                                ->where('type_id', $this->settings('SHAREHOLDER','key')->id )
                                                ->where('status',$this->settings($set_operation_status,'key')->id)
                                                ->count();
        if($annual_return_sh_firm_count){
            $annual_return_sh_firms = CompanyFirms::where('company_id', $company_id)
                                                ->where('type_id', $this->settings('SHAREHOLDER','key')->id )
                                                ->where('status',$this->settings($set_operation_status,'key')->id)
                                                ->get();
            foreach($annual_return_sh_firms as $d ) {
                 if(isset($d->address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                    /*CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                 ->delete();
                 CompanyFirms::where('id', $d->id)
                             ->where('status', $this->settings($set_operation_status,'key')->id)
                             ->where('type_id', $this->settings('SHAREHOLDER','key')->id )
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
      
       // end remore part

        foreach($request->shareholders['shs'] as $shareholder ){

            $address = new Address;
            $forAddress = new Address;

            $new_address_id= null;
            $new_forAddressId = null;
    

            if( $shareholder['shareholderType'] === 'natural' ){
                if( $shareholder['province'] || $shareholder['district'] || $shareholder['city'] || $shareholder['localAddress1'] || $shareholder['localAddress2'] || $shareholder['postcode'] ) {
                    $address->province = $shareholder['province'];
                    $address->district =  ($shareholder['type'] == 'local') ? $shareholder['district'] : null;
                    $address->city =  $shareholder['city'];
                    $address->address1 =  $shareholder['localAddress1'];
                    $address->address2 =  $shareholder['localAddress2'];
                    $address->postcode =  $shareholder['postcode'];
                    $address->country =  'Sri Lanka';
                    $address->save();
                    $new_address_id = $address->id;

                    /*$change = new CompanyItemChange;
                    $change->request_id = $request_id;
                    $change->changes_type = $this->settings('ADD','key')->id;
                    $change->item_id = $new_address_id;
                    $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;
                    $change->save();
                    $change_id = $change->id;*/
                }
                
            } else {

                $address->province = $shareholder['firm_province'];
                $address->district =  ( $shareholder['type'] == 'local') ? $shareholder['firm_district'] : '' ;
                $address->city =  $shareholder['firm_city'];
                $address->address1 =  $shareholder['firm_localAddress1'];
                $address->address2 =  $shareholder['firm_localAddress2'];
                $address->postcode = $shareholder['firm_postcode'];
                $address->country = $shareholder['country'];
                $address->save();
                $new_address_id = $address->id;

               /* $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_address_id;
                $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;
                $change->save();
                $change_id = $change->id;*/

            }
          
           

            if( $shareholder['shareholderType'] === 'natural' ){

                    if( @$shareholder['forProvince'] || @$shareholder['forCity'] || @$shareholder['forAddress1'] || @$shareholder['forAddress2'] || @$shareholder['forPostcode']) {
                        $forAddress->province = @$shareholder['forProvince'];
                        $forAddress->city =  @$shareholder['forCity'];
                        $forAddress->address1 =  @$shareholder['forAddress1'];
                        $forAddress->address2 =  @$shareholder['forAddress2'];
                        $forAddress->postcode =  @$shareholder['forPostcode'];
                        $forAddress->country =   $shareholder['country'];
                        $forAddress->save();
                        $new_forAddressId = $forAddress->id;

                       /* $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $new_forAddressId;
                        $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;
                        $change->save();
                        $change_id = $change->id;*/
                    }
            }

            if ( $shareholder['shareholderType'] === 'natural' ) {

                $newSh = new CompanyMember;
                $newSh->company_id = $company_id;
                $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
                $newSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  ( $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
               
                $newSh->title = $shareholder['title'];
                $newSh->first_name = $shareholder['firstname'];
                $newSh->last_name = $shareholder['lastname'];
                $newSh->nic = strtoupper($shareholder['nic']);
                $newSh->passport_no = $shareholder['passport'];
                $newSh->address_id = $new_address_id;
                $newSh->foreign_address_id = $new_forAddressId;
                $newSh->passport_issued_country = isset($shareholder['passport_issued_country']) ?  $shareholder['passport_issued_country'] : $shareholder['country'];
                $newSh->telephone = $shareholder['phone'];
                $newSh->mobile =$shareholder['mobile'];
                $newSh->email = $shareholder['email'];
                $newSh->occupation = $shareholder['occupation'];
                $newSh->date_of_appointment = date('Y-m-d',strtotime($shareholder['date']) );
                $newSh->status = ($set_operation === 'active') ? $this->settings('ANNUAL_RETURN','key')->id : $this->settings('ANNUAL_RETURN_FALSE','key')->id;
                $newSh->save();
                $shareHolderId =  $newSh->id;

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $shareHolderId;
                $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                $change->save();
                $change_id = $change->id;

                

            
            } else {

                $newSh = new CompanyFirms;
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  ( $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                
                $newSh->registration_no = $shareholder['pvNumber'];
                $newSh->name = $shareholder['firm_name'];
                $newSh->email = $shareholder['firm_email'];
                $newSh->mobile = $shareholder['firm_mobile'];
                $newSh->date_of_appointment = $shareholder['firm_date'];
                $newSh->company_id = $company_id;
                $newSh->address_id = $new_address_id;
                $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                $newSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                $newSh->status = ($set_operation === 'active') ? $this->settings('ANNUAL_RETURN','key')->id : $this->settings('ANNUAL_RETURN_FALSE','key')->id;
                $newSh->save();
                $shareHolderId = $newSh->id;

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $shareHolderId;
                $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                $change->save();
                $change_id = $change->id;



                if( isset($shareholder['benifiList']['ben'])  &&  is_array($shareholder['benifiList']['ben']) && count($shareholder['benifiList']['ben'])) {

                   

                    foreach(  $shareholder['benifiList']['ben'] as $ben ) {

                        $benAddress = new Address;
                        $benAddress->province = $ben['province'];
                        $benAddress->district =  ($ben['type'] == 'local' ) ? $ben['district'] : null;
                        $benAddress->city =  $ben['city'];
                        $benAddress->address1 =  $ben['localAddress1'];
                        $benAddress->address2 =  $ben['localAddress2'];
                        $benAddress->postcode = $ben['postcode'];
                        $benAddress->country =  ($ben['type'] == 'local' ) ? 'Sri Lanka' : $ben['country'];
        
                        $benAddress->save();
                        $benAddress_id = $benAddress->id;

                       /* $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $benAddress_id;
                        $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;
                        $change->save();
                        $change_id = $change->id;*/

                        $benuUser = new CompanyMember;
                        $benuUser->company_id = $company_id;
                        $benuUser->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $benuUser->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
                        $benuUser->title = $ben['title'];
                        $benuUser->first_name = $ben['firstname'];
                        $benuUser->last_name = $ben['lastname'];
                        $benuUser->address_id = $benAddress_id;
                        $benuUser->nic = ( $ben['type'] == 'local' ) ? strtoupper($ben['nic']) : null;
                        $benuUser->passport_no = ( $ben['type'] == 'local' ) ? null : $ben['passport'];
                        $benuUser->passport_issued_country = ( $ben['type'] == 'local' )  ? null : $ben['country'];
                        $benuUser->telephone = $ben['phone'];
                        $benuUser->mobile =$ben['mobile'];
                        $benuUser->email = $ben['email'];
                        $benuUser->is_beneficial_owner = 'yes';
                        $benuUser->company_member_firm_id = $shareHolderId;
                    
                        $benuUser->occupation = $ben['occupation'];
                        $benuUser->date_of_appointment = date('Y-m-d',strtotime($ben['date']) );
                        $benuUser->status = $this->settings('ANNUAL_RETURN','key')->id;
                        $benuUser->save();
                        $benUserId = $benuUser->id;

                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $benUserId;
                        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                        $change->save();
                        $change_id = $change->id;

                      }
 
                    }
             
            }

          //  $shareHolderId = ( isset($shareholder['id']) && $shareholder['id'] ) ? $shareholder['id'] : $updateSh->id;

            if(  $shareholder['shareType'] == 'single' && intval($shareholder['noOfShares']) ) {

                if(isset($shareholder['id']) && $shareholder['id'] ){


                    if($shareholder['shareholderType']  == 'natural'){
                       Share::where('company_member_id', $shareholder['id'] )->delete();
                    }else{
                        Share::where('company_firm_id', $shareholder['id'] )->delete();
                    }
                    $shareholder_share = new Share; 
                   
                }else{
                    $shareholder_share = new Share;
                }

                if(isset($shareholder['id']) && $shareholder['id']  ){
                    
                       $shareholder_sharegroup = new ShareGroup;
                       $shareholder_sharegroup->type ='single_share';
                       $shareholder_sharegroup->name ='single_share_no_name';
                       $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfShares'] );
                       $shareholder_sharegroup->company_id = $company_id;
                       $shareholder_sharegroup->status = 1;
    
                       $shareholder_sharegroup->save();
                       $shareholder_sharegroupID =  $shareholder_sharegroup->id;

   
                }else{

                    $shareholder_sharegroup = new ShareGroup;
                    $shareholder_sharegroup->type ='single_share';
                    $shareholder_sharegroup->name ='single_share_no_name';
                    $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfShares'] );
                    $shareholder_sharegroup->company_id = $company_id;
                    $shareholder_sharegroup->status = 1;
                    $shareholder_sharegroup->save();
                    $shareholder_sharegroupID = $shareholder_sharegroup->id;
                }
  
                //add to share table
                
                if ( $shareholder['shareholderType']  == 'natural' ) {
                  $shareholder_share->company_member_id = $shareHolderId;
                }else{
                    
                  $shareholder_share->company_firm_id = $shareHolderId;
                }
                $shareholder_share->group_id = $shareholder_sharegroupID;
                $shareholder_share->save();
            }

            if($shareholder['shareType'] == 'core' && isset($shareholder['coreGroupSelected']) &&  intval( $shareholder['coreGroupSelected']) ){

                if(isset($shareholder['id']) && $shareholder['id'] ){


                    if ( $shareholder['shareholderType']  == 'natural' ) {
                        $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                        $singleGroups = array();
                        if($companyGroupsCount) {
                            $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                            foreach($companyGroups as $g ){
                                $singleGroups[] = $g['id'];
                            }
        
                            Share::whereIn('group_id', $singleGroups )->where('company_member_id', $shareHolderId )->delete();
                        }
        
                      }else{
        
                        $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                        $singleGroups = array();
                        if($companyGroupsCount) {
                            $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                            foreach($companyGroups as $g ){
                                $singleGroups[] = $g['id'];
                            }
        
                            Share::whereIn('group_id', $singleGroups )->where('company_firm_id', $shareHolderId )->delete();
                        }
                      }




                    if ( $shareholder['shareholderType']  == 'natural' ) {

                        $shareRow = Share::where('company_member_id', $shareholder['id'] )->first();
                    }else {
                        $shareRow = Share::where('company_firm_id', $shareholder['id'] )->first();
                    }

                    $shareholder_share = Share::find($shareRow['id']);
                }else{
                    $shareholder_share = new Share;
                }

                if(isset($shareholder['id']) && $shareholder['id'] ){
                    $shareholder_sharegroup = ShareGroup::find($shareRow['group_id']);
                }else{
                    $shareholder_sharegroup = new ShareGroup;
                }
                //add to share table
               
                if ( $shareholder['shareholderType']  == 'natural' ) {
                   $shareholder_share->company_member_id = $shareHolderId;
                }else{
                    $shareholder_share->company_firm_id = $shareHolderId;
                }
                $shareholder_share->group_id =intval( $shareholder['coreGroupSelected']);
                $shareholder_share->save();
            }

            if(
              $shareholder['shareType'] == 'core' &&
               ( empty( $shareholder['coreGroupSelected'])  ||  !intval( $shareholder['coreGroupSelected']) )  &&
                isset( $shareholder['coreShareGroupName']) && 
                $shareholder['coreShareGroupName'] && 
              intval($shareholder['noOfSharesGroup']) ) {

              //  die('come here');

              if ( $shareholder['shareholderType']  == 'natural' ) {
            
                $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                $singleGroups = array();
                if($companyGroupsCount) {
                    $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                    foreach($companyGroups as $g ){
                        $singleGroups[] = $g['id'];
                    }

                    Share::whereIn('group_id', $singleGroups )->where('company_member_id', $shareHolderId )->delete();
                }

              }else{

                $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                $singleGroups = array();
                if($companyGroupsCount) {
                    $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                    foreach($companyGroups as $g ){
                        $singleGroups[] = $g['id'];
                    }

                    Share::whereIn('group_id', $singleGroups )->where('company_firm_id', $shareHolderId )->delete();
                }
              }
               


                //add to single share group
                $shareholder_sharegroup = new ShareGroup;
                $shareholder_sharegroup->type ='core_share';
                $shareholder_sharegroup->name = $shareholder['coreShareGroupName'];
                $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfSharesGroup'] );
                $shareholder_sharegroup->company_id = $company_id;
                $shareholder_sharegroup->status = 1;

                $shareholder_sharegroup->save();
                $shareholder_sharegroupID = $shareholder_sharegroup->id;

                //add to share table
                $shareholder_share = new Share;
                if ( $shareholder['shareholderType']  == 'natural' ) {
                    $shareholder_share->company_member_id = $shareHolderId;
                  }else{
                    $shareholder_share->company_firm_id = $shareHolderId;
                  }
                $shareholder_share->group_id = $shareholder_sharegroupID;
                $shareholder_share->save();
            }
            
        }
    }


    function submitShareReisterRecords(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

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

        $isGuarantyCompany =  ( $companyType->key === 'COMPANY_TYPE_GUARANTEE_32' || $companyType->key === 'COMPANY_TYPE_GUARANTEE_34' );
        $address_type = ($isGuarantyCompany) ? 'MEMBER_REGISTER_ADDRESS' : 'SHARE_REGISTER_ADDRESS';

        $share_register_count = OtherAddress::where('company_id',$company_id)
                                        ->where('address_type', $this->settings($address_type,'key')->id)
                                       ->where('status',1)
                                       ->count();
        if($share_register_count) {


            foreach($request->share_registers['sr'] as $sr ){

                CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                 ->where('item_id', $sr['id'])
                 ->where('item_table_type', $this->settings('SHARE_REGISTER_TABLE','key')->id)
                 ->delete();

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('UNCHANGED','key')->id;
                $change->item_id = $sr['id'];
                $change->item_table_type = $this->settings('SHARE_REGISTER_TABLE','key')->id;
                $change->save();
               
            }

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

             exit();
            
        }

        $share_register_count = OtherAddress::where('company_id', $company_id)
                                                ->where('address_type', $this->settings($address_type,'key')->id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($share_register_count){
            $share_registers = OtherAddress::where('company_id', $company_id)
                                                ->where('address_type', $this->settings($address_type,'key')->id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($share_registers as $d ) {
                 if(isset($d->address_id)) {

                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('SHARE_REGISTER_TABLE','key')->id)
                 ->delete();
                 OtherAddress::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

      

        //loop through add director list
        foreach($request->share_registers['sr'] as $sr ){

            $new_sr_local_address_id= null;
            $new_sr_foreign_address_id = null;
            
            if($sr['province'] && $sr['district'] &&  $sr['city'] && $sr['localAddress1'] && $sr['postcode'] ) {
                $address = new Address;
                $address->province = $sr['province'];
                $address->district =  $sr['district'];
                $address->city =  $sr['city'];
                $address->address1 =  $sr['localAddress1'];
                $address->address2 =  $sr['localAddress2'];
                $address->postcode = $sr['postcode'];
                $address->country =   'Sri Lanka';
            
                $address->save();
                $new_sr_local_address_id = $address->id;

               /* $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_sr_local_address_id;
                $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

                $change->save();
                $change_id = $change->id;*/
            }

            if($sr['forProvince'] &&  $sr['forCity'] && $sr['forAddress1'] && $sr['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $sr['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $sr['forCity'];
             $forAddress->address1 =  $sr['forAddress1'];
             $forAddress->address2 =  $sr['forAddress2'];
             $forAddress->postcode = $sr['forPostcode'];
             $forAddress->country =  $sr['country'];
           
             $forAddress->save();
             $new_sr_foreign_address_id = $forAddress->id;

             /*$change = new CompanyItemChange;
             $change->request_id = $request_id;
             $change->changes_type = $this->settings('ADD','key')->id;
             $change->item_id = $new_sr_foreign_address_id;
             $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

             $change->save();
             $change_id = $change->id;*/
            }


            $newSr = new OtherAddress;
            $newSr->company_id = $company_id;
            $newSr->description = json_encode($sr['description']);
            $newSr->records_kept_from = $sr['records_kept_from'];
            $newSr->address_id = $new_sr_local_address_id;
            $newSr->address_type =  $this->settings($address_type,'key')->id;
          //  $newSr->foreign_address_id =  $new_sr_foreign_address_id;
            $newSr->status =  $this->settings('ANNUAL_RETURN','key')->id;
            $newSr->save();
            $new_sr_id = $newSr->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_sr_id;
            $change->item_table_type = $this->settings('SHARE_REGISTER_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

      }

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }

    function submitAnnualRecords(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

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

        $record_count = OtherAddress::where('company_id',$company_id)
                                        ->where('address_type', $this->settings('RECORD_ADDRESS','key')->id)
                                       ->where('status',1)
                                       ->count();
        if($record_count) {

            foreach($request->annual_records['rec'] as $rec ){

                CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                 ->where('item_id', $rec['id'])
                 ->where('item_table_type', $this->settings('ANNUAL_RECORDS_TABLE','key')->id)
                 ->delete();

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('UNCHANGED','key')->id;
                $change->item_id = $rec['id'];
                $change->item_table_type = $this->settings('ANNUAL_RECORDS_TABLE','key')->id;
    
                $change->save();
                $change_id = $change->id;


            }

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

             exit();
            
        }

        $record_count = OtherAddress::where('company_id', $company_id)
                                                ->where('address_type', $this->settings('RECORD_ADDRESS','key')->id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($record_count){
            $records = OtherAddress::where('company_id', $company_id)
                                                ->where('address_type', $this->settings('RECORD_ADDRESS','key')->id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($records as $d ) {
                 if(isset($d->address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                    /*CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('ANNUAL_RECORDS_TABLE','key')->id)
                 ->delete();
                 OtherAddress::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('address_type', $this->settings('RECORD_ADDRESS','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

        //loop through add record list
        foreach($request->annual_records['rec'] as $rec ){

            $new_rec_local_address_id= null;
            $new_rec_foreign_address_id = null;
            
            if($rec['province'] && $rec['district'] &&  $rec['city'] && $rec['localAddress1'] && $rec['postcode'] ) {
                $address = new Address;
                $address->province = $rec['province'];
                $address->district =  $rec['district'];
                $address->city =  $rec['city'];
                $address->address1 =  $rec['localAddress1'];
                $address->address2 =  $rec['localAddress2'];
                $address->postcode = $rec['postcode'];
                $address->country =   'Sri Lanka';
            
                $address->save();
                $new_rec_local_address_id = $address->id;

               /* $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_rec_local_address_id;
                $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

                $change->save();
                $change_id = $change->id;*/
            }

            if($rec['forProvince'] &&  $rec['forCity'] && $rec['forAddress1'] && $rec['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $rec['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $rec['forCity'];
             $forAddress->address1 =  $rec['forAddress1'];
             $forAddress->address2 =  $rec['forAddress2'];
             $forAddress->postcode = $rec['forPostcode'];
             $forAddress->country =  $rec['country'];
           
             $forAddress->save();
             $new_sr_foreign_address_id = $forAddress->id;

            /* $change = new CompanyItemChange;
             $change->request_id = $request_id;
             $change->changes_type = $this->settings('ADD','key')->id;
             $change->item_id = $new_rec_foreign_address_id;
             $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

             $change->save();
             $change_id = $change->id;*/
            }


            $newRec = new OtherAddress;
            $newRec->company_id = $company_id;
            $newRec->description = json_encode($rec['description']);
            $newRec->address_id = $new_rec_local_address_id;
            $newRec->address_type =   $this->settings('RECORD_ADDRESS','key')->id;
          //  $newRec->foreign_address_id =  $new_rec_foreign_address_id;
          $newRec->records_kept_from = $rec['records_kept_from'];
            $newRec->status =  $this->settings('ANNUAL_RETURN','key')->id;
            $newRec->save();
            $new_rec_id = $newRec->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_rec_id;
            $change->item_table_type = $this->settings('ANNUAL_RECORDS_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

      }

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }

    function submitAnnualAuditorRecords(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

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

        $record_count = AnnualAuditors::where('company_id',$company_id)
                                       ->where('status',1)
                                       ->count();
       /* if($record_count) {  // this checking removed since auditors may varying

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

             exit();
            
        }*/

        $record_count = AnnualAuditors::where('company_id', $company_id)
                                              //  ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($record_count){
            $records = AnnualAuditors::where('company_id', $company_id)
                                               // ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($records as $d ) {
                 if(isset($d->address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('ANNUAL_AUDITORS_TABLE','key')->id)
                 ->delete();
                 AnnualAuditors::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

        //loop through add record list
        foreach($request->auditor_records['member'] as $rec ){

            $new_rec_local_address_id= null;
            $new_rec_foreign_address_id = null;
            
            if($rec['province'] && $rec['district'] &&  $rec['city'] && $rec['localAddress1'] && $rec['postcode'] ) {
                $address = new Address;
                $address->province = $rec['province'];
                $address->district =  $rec['district'];
                $address->city =  $rec['city'];
                $address->address1 =  $rec['localAddress1'];
                $address->address2 =  $rec['localAddress2'];
                $address->postcode = $rec['postcode'];
                $address->country =   'Sri Lanka';
            
                $address->save();
                $new_rec_local_address_id = $address->id;

                /*$change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_rec_local_address_id;
                $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

                $change->save();
                $change_id = $change->id;*/
            }

            if($rec['forProvince'] &&  $rec['forCity'] && $rec['forAddress1'] && $rec['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $rec['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $rec['forCity'];
             $forAddress->address1 =  $rec['forAddress1'];
             $forAddress->address2 =  $rec['forAddress2'];
             $forAddress->postcode = $rec['forPostcode'];
             $forAddress->country =  $rec['country'];
           
             $forAddress->save();
             $new_sr_foreign_address_id = $forAddress->id;

            /* $change = new CompanyItemChange;
             $change->request_id = $request_id;
             $change->changes_type = $this->settings('ADD','key')->id;
             $change->item_id = $new_rec_foreign_address_id;
             $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

             $change->save();
             $change_id = $change->id;*/
            }


            $newRec = new AnnualAuditors;
            $newRec->company_id = $company_id;
            $newRec->first_name = $rec['first_name'];
            $newRec->last_name = $rec['last_name'];
            $newRec->address_id = $new_rec_local_address_id;
            $newRec->foreign_address_id =  $new_rec_foreign_address_id;
            $newRec->type = $rec['type'];
            $newRec->reg_no = ( $rec['type'] == 'Registered' ) ? $rec['reg_no'] : null;
            $newRec->nic = $rec['nic'];
            $newRec->status =  $this->settings('ANNUAL_RETURN','key')->id;
            $newRec->save();
            $new_rec_id = $newRec->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_rec_id;
            $change->item_table_type = $this->settings('ANNUAL_AUDITORS_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

      }

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }
    function submitAnnualCharges(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

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

        $record_count = AnnualCharges::where('company_id',$company_id)
                                       ->where('status',1)
                                       ->count();
        if($record_count) {

            foreach($request->charges_records['ch'] as $rec ){

                CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                ->where('item_id', $rec['id'])
                ->where('item_table_type', $this->settings('ANNUAL_CHARGES_TABLE','key')->id)
                ->delete();

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('UNCHANGED','key')->id;
                $change->item_id = $rec['id'];
                $change->item_table_type = $this->settings('ANNUAL_CHARGES_TABLE','key')->id;
                $change->save();



            }

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

             exit();
            
        }

        $record_count = AnnualCharges::where('company_id', $company_id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($record_count){
            $records = AnnualCharges::where('company_id', $company_id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($records as $d ) {
                 if(isset($d->address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->address_id)->delete();

                 }
                 if(isset($d->foreign_address_id)) {

                   /* CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('ADD','key')->id)
                    ->where('item_id', $d->foreign_address_id)
                    ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                    ->delete();*/
                    Address::where('id', $d->foreign_address_id)->delete();

                 }
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('ANNUAL_CHARGES_TABLE','key')->id)
                 ->delete();
                 AnnualCharges::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

        //loop through add record list
        foreach($request->charges_records['ch'] as $rec ){

            $new_rec_local_address_id= null;
            $new_rec_foreign_address_id = null;
            
            if($rec['province'] && $rec['district'] &&  $rec['city'] && $rec['localAddress1'] && $rec['postcode'] ) {
                $address = new Address;
                $address->province = $rec['province'];
                $address->district =  $rec['district'];
                $address->city =  $rec['city'];
                $address->address1 =  $rec['localAddress1'];
                $address->address2 =  $rec['localAddress2'];
                $address->postcode = $rec['postcode'];
                $address->country =   'Sri Lanka';
            
                $address->save();
                $new_rec_local_address_id = $address->id;

                /*$change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_rec_local_address_id;
                $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

                $change->save();
                $change_id = $change->id;*/
            }

            if($rec['forProvince'] &&  $rec['forCity'] && $rec['forAddress1'] && $rec['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $rec['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $rec['forCity'];
             $forAddress->address1 =  $rec['forAddress1'];
             $forAddress->address2 =  $rec['forAddress2'];
             $forAddress->postcode = $rec['forPostcode'];
             $forAddress->country =  $rec['country'];
           
             $forAddress->save();
             $new_sr_foreign_address_id = $forAddress->id;

             /*$change = new CompanyItemChange;
             $change->request_id = $request_id;
             $change->changes_type = $this->settings('ADD','key')->id;
             $change->item_id = $new_rec_foreign_address_id;
             $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

             $change->save();
             $change_id = $change->id;*/
            }


            $newRec = new AnnualCharges;
            $newRec->company_id = $company_id;
            $newRec->name = $rec['name'];
            $newRec->date = $rec['date'];
            $newRec->description = $rec['description'];
            $newRec->amount = floatval($rec['amount']);
            $newRec->address_id = $new_rec_local_address_id;
            $newRec->foreign_address_id =  $new_rec_foreign_address_id;
            $newRec->status =  $this->settings('ANNUAL_RETURN','key')->id;
            $newRec->save();
            $new_rec_id = $newRec->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_rec_id;
            $change->item_table_type = $this->settings('ANNUAL_CHARGES_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

      }

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }

    function submitShareRecords(Request $request) {
        $company_id = $request->companyId;
        $request_id = $this->valid_annual_return_request_operation($company_id);
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

       $has_share_classes =  ShareClasses::where('company_id', $company_id)
        ->where('status', $this->settings('ISSUE_OF_SHARES_APPROVED','key')->id)
        ->count();

       if($has_share_classes) {
        return response()->json([
            'message' => 'Invalid Request.',
            'status' =>false,
            'request_id'   => $request_id,
            'change_id'    => null,
        ], 200);

         exit();
       }
       $record_id = null;
       $annual_share_class_request =  ShareClasses::where('company_id', $company_id)
        ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
        ->where('request_id', $request_id)
        ->first();
        if(!isset($annual_share_class_request->id)){

            $company_info = Company::where('id', $company_id)->first();
            $year = date('Y',time());

            $record = new ShareClasses;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('ANNUAL_RETURN','key')->id;
            $record->save();
            $record_id =  $record->id;
            
        } else {
            $record_id = $annual_share_class_request->id;
        }

     //   print_r($request->share_records['share']);
     //   die();

        foreach($request->share_records['share'] as $sr ){

            if(isset($sr['id']) && intval($sr['id']) ) {
                $newSr = ShareIssueRecords::find($sr['id']);
            }else {
                $newSr = new ShareIssueRecords;
            }

           
            $newSr->share_class =  $sr['share_class'];
            $newSr->share_class_other =  ($sr['share_class'] == 'OTHER_SHARE') ? $sr['share_class_other']  : null;
            $newSr->is_issue_type_as_cash =  $sr['is_issue_type_as_cash'];
            $newSr->no_of_shares_as_cash = $sr['is_issue_type_as_cash'] == 'yes' ? floatval( $sr['no_of_shares_as_cash']) : null;
            $newSr->consideration_of_shares_as_cash = $sr['is_issue_type_as_cash'] == 'yes' ? floatval( $sr['consideration_of_shares_as_cash']) : null;
            $newSr->is_issue_type_as_non_cash =  $sr['is_issue_type_as_non_cash'];
            $newSr->no_of_shares_as_non_cash = $sr['is_issue_type_as_non_cash'] == 'yes' ? floatval( $sr['no_of_shares_as_non_cash']) : null;
            $newSr->consideration_of_shares_as_non_cash = $sr['is_issue_type_as_non_cash'] == 'yes' ? floatval( $sr['consideration_of_shares_as_non_cash']) : null;
            $newSr->date_of_issue =  $sr['date_of_issue'];
            $newSr->called_on_shares = $sr['called_on_shares'] ? floatval($sr['called_on_shares']) : '';
            $newSr->consideration_paid_or_provided = $sr['consideration_paid_or_provided'] ? floatval($sr['consideration_paid_or_provided']) : '';
            $newSr->status =  $this->settings('ANNUAL_RETURN','key')->id;
            $newSr->record_id = $record_id;
            $newSr->save();
          

            if(isset($sr['id']) && intval($sr['id']) ) {
             
                $new_sr_id = intval($sr['id']);

            } else {
                $new_sr_id = $newSr->id;

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('ADD','key')->id;
                $change->item_id = $new_sr_id;
                $change->item_table_type = $this->settings('ISSUE_SHARE_TABLE','key')->id;
    
                $change->save();
                $change_id = $change->id;
            }

           

      }

        $share_summery = array(

        'amount_calls_recieved' => intval($request->amount_calls_recieved) ? intval($request->amount_calls_recieved) : null,
        'amount_calls_unpaid' => intval($request->amount_calls_unpaid) ? intval($request->amount_calls_unpaid) : null,
        'amount_calls_forfeited' => intval($request->amount_calls_forfeited) ? intval($request->amount_calls_forfeited) : null,
        'amount_calls_purchased' => intval($request->amount_calls_purchased) ? intval($request->amount_calls_purchased) : null,
        'amount_calls_redeemed' => intval($request->amount_calls_redeemed) ? intval($request->amount_calls_redeemed) : null,

        );
        AnnualReturn::where('company_id', $company_id)
        ->where('request_id', $request_id)
        ->where('year', date('Y',time()))
        ->update($share_summery);

        return response()->json([
            'message' => 'data.',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => null,
        ], 200);



       
    }
    
    function submitShareRecords_OLD(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

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

        $record_count = ShareClasses::where('company_id',$company_id)
                                       ->where('status',1)
                                       ->count();
        if($record_count) {  // this checking removed since auditors may varying

            foreach($request->share_records['share'] as $rec ){

                CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('UNCHANGED','key')->id)
                 ->where('item_id', $rec['id'])
                 ->where('item_table_type', $this->settings('SHARE_CLASS_TABLE','key')->id)
                 ->delete();

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('UNCHANGED','key')->id;
                $change->item_id = $rec['id'];
                $change->item_table_type = $this->settings('SHARE_CLASS_TABLE','key')->id;
                $change->save();

               

            }
            $share_summery = array(

                'amount_calls_recieved' => intval($request->amount_calls_recieved) ? intval($request->amount_calls_recieved) : null,
                'amount_calls_unpaid' => intval($request->amount_calls_unpaid) ? intval($request->amount_calls_unpaid) : null,
                'amount_calls_forfeited' => intval($request->amount_calls_forfeited) ? intval($request->amount_calls_forfeited) : null,
                'amount_calls_purchased' => intval($request->amount_calls_purchased) ? intval($request->amount_calls_purchased) : null,
                'amount_calls_redeemed' => intval($request->amount_calls_redeemed) ? intval($request->amount_calls_redeemed) : null,
        
            );
            AnnualReturn::where('company_id', $company_id)
            ->where('request_id', $request_id)
            ->where('year', date('Y',time()))
             ->update($share_summery);

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

             exit();
            
        }

        $record_count = ShareClasses::where('company_id', $company_id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($record_count){
            $records = ShareClasses::where('company_id', $company_id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($records as $d ) {
                 

                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('SHARE_CLASS_TABLE','key')->id)
                 ->delete();
                 ShareClasses::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
                             ->where('company_id', $company_id)
                             ->delete();
            }

        }
       // end remore part

        //loop through add record list
        foreach($request->share_records['share'] as $rec ){

            if( !isset($this->settings( $rec['share_class'],'key')->id )){
                continue;
            }

   

            $newRec = new ShareClasses;
            $newRec->company_id = $company_id;
            $newRec->share_class =  $this->settings( $rec['share_class'],'key')->id;
            $newRec->no_of_shares = floatval($rec['no_of_shares']);
            $newRec->issue_type_as_cash = ($rec['issue_type_as_cash']) ? 1 : 0;
            $newRec->issue_type_as_non_cash = ($rec['issue_type_as_non_cash']) ? 1 : 0;

            $newRec->share_value = ($rec['issue_type_as_cash'] != 1 ) ? null : floatval($rec['share_value']);
            $newRec->shares_issued_for_cash = ($rec['issue_type_as_cash'] != 1 ) ? null : floatval($rec['shares_issued_for_cash']);

           
            $newRec->share_consideration = ($rec['issue_type_as_non_cash'] != 1 ) ? null : $rec['share_consideration'];
            $newRec->share_consideration_value_paid = ($rec['issue_type_as_non_cash'] != 1 ) ? null : floatval($rec['share_consideration_value_paid']);
            $newRec->shares_issued_for_non_cash = ( $rec['issue_type_as_non_cash'] != 1 ) ? null : floatval($rec['shares_issued_for_non_cash']);

            $newRec->shares_called_on = floatval($rec['shares_called_on']);
            $newRec->status =  $this->settings('ANNUAL_RETURN','key')->id;
            $newRec->save();
            $new_rec_id = $newRec->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_rec_id;
            $change->item_table_type = $this->settings('SHARE_CLASS_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

            

      }

      $share_summery = array(

        'amount_calls_recieved' => intval($request->amount_calls_recieved) ? intval($request->amount_calls_recieved) : null,
        'amount_calls_unpaid' => intval($request->amount_calls_unpaid) ? intval($request->amount_calls_unpaid) : null,
        'amount_calls_forfeited' => intval($request->amount_calls_forfeited) ? intval($request->amount_calls_forfeited) : null,
        'amount_calls_purchased' => intval($request->amount_calls_purchased) ? intval($request->amount_calls_purchased) : null,
        'amount_calls_redeemed' => intval($request->amount_calls_redeemed) ? intval($request->amount_calls_redeemed) : null,

    );
        AnnualReturn::where('company_id', $company_id)
        ->where('request_id', $request_id)
        ->where('year', date('Y',time()))
        ->update($share_summery);

        return response()->json([
            'message' => 'data.',
            'status' =>true,
            'request_id'   => $request_id,
            'change_id'    => null,
        ], 200);
    }

    function uploadShareholderByCSV(Request $request){

        $company_id = $request->companyId;
        $request_id = $this->valid_annual_return_request_operation($company_id);
        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');
      
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

     
        $size = $request->file('uploadFile')->getClientSize() ;
        $ext = $request->file('uploadFile')->getClientMimeType();

        if('application/vnd.ms-excel' !== $ext  &&  'application/octet-stream' !== $ext){

         return response()->json([
             'message' => 'Please upload your files with csv format.',
             'status' =>false,
             'error'  => 'yes',
             'uploadedExt' => $ext

         ], 200);
        }

        if( $size > 1024 * 1024 * 4) {

         return response()->json([
             'message' => 'File size should be less than 4 MB.',
             'status' =>false,
             'error'  => 'yes'
  
         ], 200);
        }

        $directory = "annual-return-shareholders/$company_id";
    //    @ Storage::deleteDirectory($directory);
        $path = Storage::putFile($directory, $request->file('uploadFile'));
 
        $file_path = str_replace('public','',url('/')).Storage::url("app/$path");


     
        if (($handle = fopen($file_path, "r")) !== FALSE) {


            $row = 1;


            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if( !isset($data[0])) {
                   break;
                }
                $num = count($data);
               
                $address = new Address;
                $forAddress = new Address;
        
                $new_address_id= null;
                $new_forAddressId = null;

                if( isset($data[0]) &&  $data[0] !== 'yes' ){ // natural person

                  //  if($data[1] !== 'no') { // is sri lankan

                        $address->province = $data[9];
                        $address->district =  ($data[1] !== 'no') ? $data[10] : null;
                        $address->city =  $data[11];
                        $address->address1 = $data[12];
                        $address->address2 =  $data[13];
                        $address->postcode =  $data[14];
                        $address->country =  ($data[1] !== 'no') ? 'Sri Lanka' :  $data[15] ;
                        $address->save();
                        $new_address_id = $address->id;

                } else {

                        $address->province =  $data[9];
                        $address->district =   $data[10];
                        $address->city =  $data[11];
                        $address->address1 =  $data[12];
                        $address->address2 =  $data[13];
                        $address->postcode = $data[14];
                        $address->country = ($data[1] !== 'no') ? 'Sri Lanka' :  $data[15] ;
                        $address->save();
                        $new_address_id = $address->id;
    

                }
            
    
                if ( $data[0] !== 'yes' ) {
        
                        $newSh = new CompanyMember;
                        $newSh->company_id = $company_id;
                        $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $newSh->is_srilankan =  ($data[1] !== 'no') ?  'yes' : 'no';
                        $newSh->title = $data[4];
                        $newSh->first_name = $data[5];
                        $newSh->last_name = $data[6];
                        $newSh->nic = ($data[1] !== 'no') ? strtoupper($data[2]) : NULL;
                        $newSh->passport_no = ($data[1] === 'no') ? strtoupper($data[2]) : NULL;
                        $newSh->address_id = $new_address_id;
                        $newSh->foreign_address_id = $new_forAddressId;
                        $newSh->passport_issued_country = isset($data[3]) ?  $data[3] :$data[14];
                        $newSh->telephone = isset($data[17]) && strlen($data[17]) < 10 ? '0'.$data[17] : $data[17];
                        $newSh->mobile = isset($data[16]) && strlen($data[16]) < 10 ? '0'.$data[16] : $data[16];
                        $newSh->email = $data[18];
                        $newSh->occupation = $data[20];
                        $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
                        $newSh->status =  $this->settings('ANNUAL_RETURN','key')->id;
                        $newSh->save();
                        $shareHolderId =  $newSh->id;
        
                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $shareHolderId;
                        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                        $change->save();
                        $change_id = $change->id;
        
                        
        
                    
                    } else {
        
                        $newSh = new CompanyFirms;
                        $newSh->registration_no = $data[8];
                        $newSh->name =  $data[7];
                        $newSh->email = $data[18];
                        $newSh->mobile = strlen($data[16]) < 10 ? '0'.$data[16] : $data[16];
                        $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
                        $newSh->company_id = $company_id;
                        $newSh->address_id = $new_address_id;
                        $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                        $newSh->is_srilankan =  ($data[1] !== 'no') ?  'yes' : 'no';
                        $newSh->status =  $this->settings('ANNUAL_RETURN','key')->id;
                        $newSh->save();
                        $shareHolderId = $newSh->id;
        
                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $shareHolderId;
                        $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                        $change->save();
                        $change_id = $change->id;
        
        

                    }

                    $shareholder_share = new Share;

                    $shareholder_sharegroup = new ShareGroup;
                    $shareholder_sharegroup->type ='single_share';
                    $shareholder_sharegroup->name ='single_share_no_name';
                    $shareholder_sharegroup->no_of_shares = isset( $data[21]) ? floatval( $data[21] ) : 0 ;
                    $shareholder_sharegroup->company_id = $company_id;
                    $shareholder_sharegroup->status = 1;
                    $shareholder_sharegroup->save();
                    $shareholder_sharegroupID = $shareholder_sharegroup->id;

                    if ( isset($data[0]) &&  $data[0] !== 'yes' ) {
                        $shareholder_share->company_member_id = $shareHolderId;
                    }else{
                          
                        $shareholder_share->company_firm_id = $shareHolderId;
                    }
                    $shareholder_share->group_id = $shareholder_sharegroupID;
                    $shareholder_share->save();

            }
        fclose($handle);
        }

       

        return response()->json([
            'message' => 'Bulk Shareholders added.',
            'status' =>true,
        ], 200);



    }

    function uploadCeasedShareholderByCSV(Request $request){

        $company_id = $request->companyId;
        $request_id = $this->valid_annual_return_request_operation($company_id);
        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');
      
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

     
        $size = $request->file('uploadFile')->getClientSize() ;
        $ext = $request->file('uploadFile')->getClientMimeType();

        if('application/vnd.ms-excel' !== $ext  &&  'application/octet-stream' !== $ext){

         return response()->json([
             'message' => 'Please upload your files with csv format.',
             'status' =>false,
             'error'  => 'yes',
             'uploadedExt' => $ext

         ], 200);
        }

        if( $size > 1024 * 1024 * 4) {

         return response()->json([
             'message' => 'File size should be less than 4 MB.',
             'status' =>false,
             'error'  => 'yes'
  
         ], 200);
        }

        $directory = "annual-return-shareholders/$company_id";
    //    @ Storage::deleteDirectory($directory);
        $path = Storage::putFile($directory, $request->file('uploadFile'));
 
        $file_path = str_replace('public','',url('/')).Storage::url("app/$path");


     
        if (($handle = fopen($file_path, "r")) !== FALSE) {


            $row = 1;


            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if( !isset($data[0])) {
                    break;
                 }
                $num = count($data);
               
                $address = new Address;
                $forAddress = new Address;
        
                $new_address_id= null;
                $new_forAddressId = null;

                if( isset($data[0]) &&  $data[0] !== 'yes' ){ // natural person

                  //  if($data[1] !== 'no') { // is sri lankan

                        $address->province = $data[9];
                        $address->district =  ($data[1] !== 'no') ? $data[10] : null;
                        $address->city =  $data[11];
                        $address->address1 = $data[12];
                        $address->address2 =  $data[13];
                        $address->postcode =  $data[14];
                        $address->country =  ($data[1] !== 'no') ? 'Sri Lanka' :  $data[15] ;
                        $address->save();
                        $new_address_id = $address->id;

                } else {

                        $address->province =  $data[9];
                        $address->district =   $data[10];
                        $address->city =  $data[11];
                        $address->address1 =  $data[12];
                        $address->address2 =  $data[13];
                        $address->postcode = $data[14];
                        $address->country = ($data[1] !== 'no') ? 'Sri Lanka' :  $data[15] ;
                        $address->save();
                        $new_address_id = $address->id;
    

                }
            
    
                if ( $data[0] !== 'yes' ) {
        
                        $newSh = new CompanyMember;
                        $newSh->company_id = $company_id;
                        $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $newSh->is_srilankan =  ($data[1] !== 'no') ?  'yes' : 'no';
                        $newSh->title = $data[4];
                        $newSh->first_name = $data[5];
                        $newSh->last_name = $data[6];
                        $newSh->nic = ($data[1] !== 'no') ? strtoupper($data[2]) : NULL;
                        $newSh->passport_no = ($data[1] === 'no') ? strtoupper($data[2]) : NULL;
                        $newSh->address_id = $new_address_id;
                        $newSh->foreign_address_id = $new_forAddressId;
                        $newSh->passport_issued_country = isset($data[3]) ?  $data[3] :$data[14];
                        $newSh->telephone = isset($data[17]) && strlen($data[17]) < 10 ? '0'.$data[17] : $data[17];
                        $newSh->mobile = isset($data[16]) && strlen($data[16]) < 10 ? '0'.$data[16] : $data[16];
                        $newSh->email = $data[18];
                        $newSh->occupation = $data[20];
                        $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
                        $newSh->status =  $this->settings('ANNUAL_RETURN_FALSE','key')->id;
                        $newSh->save();
                        $shareHolderId =  $newSh->id;
        
                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $shareHolderId;
                        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                        $change->save();
                        $change_id = $change->id;
        
                        
        
                    
                    } else {
        
                        $newSh = new CompanyFirms;
                        $newSh->registration_no = $data[8];
                        $newSh->name =  $data[7];
                        $newSh->email = $data[18];
                        $newSh->mobile = isset($data[16]) && strlen($data[16]) < 10 ? '0'.$data[16] : $data[16];
                        $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
                        $newSh->company_id = $company_id;
                        $newSh->address_id = $new_address_id;
                        $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                        $newSh->is_srilankan =  ($data[1] !== 'no') ?  'yes' : 'no';
                        $newSh->status =  $this->settings('ANNUAL_RETURN_FALSE','key')->id;
                        $newSh->save();
                        $shareHolderId = $newSh->id;
        
                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $shareHolderId;
                        $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                        $change->save();
                        $change_id = $change->id;
        
        

                    }

                    $shareholder_share = new Share;

                    $shareholder_sharegroup = new ShareGroup;
                    $shareholder_sharegroup->type ='single_share';
                    $shareholder_sharegroup->name ='single_share_no_name';
                    $shareholder_sharegroup->no_of_shares = isset( $data[21]) ? floatval( $data[21] ) : 0 ;
                    $shareholder_sharegroup->company_id = $company_id;
                    $shareholder_sharegroup->status = 1;
                    $shareholder_sharegroup->save();
                    $shareholder_sharegroupID = $shareholder_sharegroup->id;

                    if ( isset($data[0]) &&  $data[0] !== 'yes' ) {
                        $shareholder_share->company_member_id = $shareHolderId;
                    }else{
                          
                        $shareholder_share->company_firm_id = $shareHolderId;
                    }
                    $shareholder_share->group_id = $shareholder_sharegroupID;
                    $shareholder_share->save();

            }
        fclose($handle);
        }

       

        return response()->json([
            'message' => 'Bulk Ceased Shareholders added.',
            'status' =>true,
        ], 200);



    }

    function resubmit(Request $request ) {

        $company_id = $request->companyId;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $annualReturnRecord =  AnnualReturn::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
            ->where('year', date('Y',time()))
             ->first();
        if( !( isset($annualReturnRecord->status) && $annualReturnRecord->status === $this->settings('ANNUAL_RETURN_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Annual Return Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = AnnualReturn::where('request_id', $request_id)->update(['status' => $this->settings('ANNUAL_RETURN_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('ANNUAL_RETURN_RESUBMITTED', 'key')->id]);

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



   

    function checkNic(Request $request ){

       //  $nic = strtoupper($request->nic);
         $company_id = $request->companyId;
         $company_info = Company::where('id',$request->companyId)->first();

         $companyType = $this->settings($company_info->type_id,'id')->key;

       
         $member_type = $request->memberType; // 1- director, 2- secrotory , 3 - shareholder

         if($member_type == 1 ) {
             $member_type = $this->settings('DERECTOR','key')->id;
         }
         if($member_type == 2 ){
              $member_type = $this->settings('SECRETARY','key')->id;
         }
         if($member_type == 3 ){
            $member_type = $this->settings('SHAREHOLDER','key')->id;
         }

        //$members =CompanyMember::where('company_id','!=', $company_id)
                              ///  ->where('nic', $nic)
                              //  ->where('designation_type', $member_type )
                              //  ->get();

                           

        $members_nic_lower =People::where('nic', strtolower($request->nic))->first();
        $members_nic_lowercount = People::where('nic', strtolower($request->nic))->count();

        $members_nic_upper =People::where('nic', strtoupper($request->nic))->first();
        $members_nic_uppercount = People::where('nic',strtoupper($request->nic))->count();


        $members = ($members_nic_lowercount ) ? $members_nic_lower : $members_nic_upper;
        $members_count = ($members_nic_lowercount) ? $members_nic_lowercount : $members_nic_uppercount;
     
         $sec_reg_no = '';

         if( $request->memberType == 1 || $request->memberType == 2) {

            $members_sec_nic_lower =Secretary::where('nic', strtolower($request->nic))->first();
            $members_sec_nic_lowercount = Secretary::where('nic', strtolower($request->nic))->count();
    
            $members_sec_nic_upper = Secretary::where('nic', strtoupper($request->nic))->first();
            $members_sec_nic_uppercount = Secretary::where('nic',strtoupper($request->nic))->count();
    
            $members_sec = ($members_sec_nic_lowercount ) ? $members_sec_nic_lower : $members_sec_nic_upper; 

            if(isset($members_sec->id)) {
                
                $sec_sertificate_count = SecretaryCertificate::where('secretary_id', $members_sec->id )->count();
                if($sec_sertificate_count) {
                    $sec_sertificate = SecretaryCertificate::where('secretary_id', $members_sec->id )->first();
                    $sec_reg_no = isset($sec_sertificate->certificate_no) && $sec_sertificate->certificate_no  ? $sec_sertificate->certificate_no : '';
                }else {
                    $sec_reg_no = '';
                }

            }

           // $sec_reg_no = isset($members_sec->certificate_no) && $members_sec->certificate_no  ? $members_sec->certificate_no : '';
         }

         if($members_count >= 1 ){

            $address = Address::where( 'id',$members->address_id )->get()->first();
            
            return response()->json([
                'message' => 'Director record exists under this NIC.',
                'status' =>true,
                'data'   => array(
                    // 'member_count' =>$members_count,
                     'member_count' =>1,
                      'member_record'      => array($members),
                      'address_record'     => $address,
                      'sec_reg_no'         => $sec_reg_no,
                      'openLocalAddress'    => $this->localAddressOpenStatus($address),
                      'title'              => isset($this->settings($members->title,'id')->value) && $this->settings($members->title,'id')->value ? $this->settings($members->title,'id')->value : NULL

                      
                )
            ], 200);
         }else{
            return response()->json([
                'message' => 'No record found under this NIC',
                'status' =>true,
                'data'   => array(
                     'member_count' =>0,
                     'sec_reg_no'         => $sec_reg_no,
                     'openLocalAddress' => true,
                     'title' => null
                )
            ], 200);
         }


    }
    private function localAddressOpenStatus($address){

        if(!isset($address->province) || !$address->province ){
            return true;
        }else{
            $province_count = Province::where('description_en', $address->province )->count();
            if(!$province_count){
                return true;
            }
        }

        if(!isset($address->district) || !$address->district ){
            return true;
        }else{
            $district_count = District::where('description_en', $address->district )->count();
            if(!$district_count){
                return true;
            }
        }

        if(!isset($address->city) || !$address->city ){
            return true;
        }else{
            $city_count = City::where('description_en', $address->city )->count();
            if(!$city_count){
                return true;
            }
        }

        if(!isset($address->gn_division) || !$address->gn_division ){
            return true;
        }else{
            $gn_count = GNDivision::where('description_en', $address->gn_division )->count();
            if(!$gn_count){
                return true;
            }
        }

        if(!isset($address->address1) || !$address->address1 ){
            return true;
        }
        if(!isset($address->postcode) || !$address->postcode ){
            return true;
        }

        return false;


    }

    function getDocs($doc_type, $companyId= null){
        $docs = $this->documents(false, $companyId);
        return isset(  $docs[$doc_type] ) ?   $docs[$doc_type]  : false;
    }

    function documents($is_resubmission=false, $companyId= null){

        $docs = array();
        $private_public_unlimited = array('COMPANY_TYPE_PRIVATE', 'COMPANY_TYPE_PUBLIC','COMPANY_TYPE_UNLIMITED');

        foreach($private_public_unlimited as $type ){

            $docs[$type] = array(
                'download' => array(
                    array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                ),
                'upload'   => array()
            );
    
            $type_id = $this->settings($type,'key')->id;
    
            $group = DocumentsGroup::where('company_type', $type_id)
                                            ->where('request_type', 'COM_REG')
                                            ->first();
            $group_id = @$group->id;

            if($is_resubmission){
                $uploads = Documents::where('document_group_id', $group_id)
                ->where('status', 1)
                ->get();
            }else{
                
                $uploads = Documents::where('document_group_id', $group_id)
                ->where('status', 1)
                ->get();
            }
    
            $upload_arr = array();
    
            foreach($uploads as $d ){

                $upload_arr[] = $d->id;
                  
                $rec = array(
                    'dbid' => $d->id,
                   'name' => $d->name,
                   'savedLocation' => '',
                   'required' => ($d->is_required == 'yes' ) ? true : false,
                   'specific' => $d->specific_company_member_type,
                   'type'   =>$this->slugify($d->name),
                   'fee'    => $d->fee,
                   'key' => $d->key,
                  // 'fee'    =>mt_rand(2000, 5000),
                   'uploaded_path' => '',
                   'comments' =>'',
                   'description' => $d->description,
                   'issue_certified_copy' => $d->issue_certified_copy,
                   'doc_requested' => 'no',
                   'admin_set'  => 'no',

                );
                $docs[$type]['upload'][] = $rec;
            }
            
            if($companyId) {

               
                //   echo ('echo::' .$companyId);
                   $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
                   ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                   ->whereNotIn('document_id', $upload_arr)
                   ->count();
                  // print_r( $additional_docs_count);
   
                   if( $additional_docs_count) {
                     
   
                       $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                       ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                       ->whereNotIn('document_id', $upload_arr)
                                                       ->get();
                                                     //  print_r( $additional_docs);   
                       foreach($additional_docs as $adoc) {
   
                           $doc_id_in_list = $adoc->document_id;
                           $docInfo =  Documents::where('id', $doc_id_in_list)->first();
   
                           $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';
   
                        //   print_r(  $docInfo);
   
                           $rec = array(
                               'dbid' => $doc_id_in_list,
                              'name' => $docInfo->name,
                              'savedLocation' => '',
                              'required' => true,
                              'specific' => $docInfo->specific_company_member_type,
                              'type'   =>$this->slugify($docInfo->name),
                              'fee'    => $docInfo->fee,
                              'key' => $docInfo->key,
                              'uploaded_path' => '',
                              'comments' =>'',
                              'description' => $docInfo->description,
                              'issue_certified_copy' => $docInfo->issue_certified_copy,
                              'doc_requested' => $doc_requested,
                              'admin_set'  => 'yes'
               
                           );
                           $docs[$type]['upload'][] = $rec;
   
   
   
                       }
   
                   }
                   
               }

            

        }

        $guarantee = array( 'COMPANY_TYPE_GUARANTEE_34', 'COMPANY_TYPE_GUARANTEE_32');

        foreach($guarantee as $type ){
            $docs[$type] = array(
                'download' => array(
                    array('name' =>'FORM 05', 'savedLocation' => "", 'view'=>'form5', 'specific' =>'','file_name_key' =>'form05' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                ),
                'upload'   => array()
            );
    
            $type_id = $this->settings($type,'key')->id;
            $group = DocumentsGroup::where('company_type', $type_id)
                                            ->where('request_type', 'COM_REG')
                                            ->first();
            $group_id = @$group->id;
    
            $uploads = Documents::where('document_group_id', $group_id)
                                ->where('status', 1)
                                ->get();

            $upload_arr = array();
    
            foreach($uploads as $d ){

                $upload_arr[] = $d->id;
                  
                $rec = array(
                   'dbid' => $d->id,
                   'name' => $d->name,
                   'savedLocation' => '',
                   'required' => ($d->is_required == 'yes' ) ? true : false,
                   'specific' => $d->specific_company_member_type,
                   'type'   =>$this->slugify($d->name),
                   'fee'    => $d->fee,
                   'key' => $d->key,
                   'uploaded_path' => '',
                   'description' => $d->description,
                   'issue_certified_copy' => $d->issue_certified_copy,
                   'doc_requested' => 'no',
                    'admin_set'  => 'no'
    
                );
                $docs[$type]['upload'][] = $rec;
    
            }

            if($companyId) {

               
             //   echo ('echo::' .$companyId);
                $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
                ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                ->whereNotIn('document_id', $upload_arr)
                ->count();
               // print_r( $additional_docs_count);

                if( $additional_docs_count) {
                  

                    $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                    ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                    ->whereNotIn('document_id', $upload_arr)
                                                    ->get();
                                                  //  print_r( $additional_docs);   
                    foreach($additional_docs as $adoc) {

                        $doc_id_in_list = $adoc->document_id;
                        $docInfo =  Documents::where('id', $doc_id_in_list)->first();

                        $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';

                     //   print_r(  $docInfo);

                        $rec = array(
                            'dbid' => $doc_id_in_list,
                           'name' => $docInfo->name,
                           'savedLocation' => '',
                           'required' => true,
                           'specific' => $docInfo->specific_company_member_type,
                           'type'   =>$this->slugify($docInfo->name),
                           'fee'    => $docInfo->fee,
                           'key' => $docInfo->key,
                           'uploaded_path' => '',
                           'comments' =>'',
                           'description' => $docInfo->description,
                           'issue_certified_copy' => $docInfo->issue_certified_copy,
                           'doc_requested' => $doc_requested,
                           'admin_set'  => 'yes'
            
                        );
                        $docs[$type]['upload'][] = $rec;



                    }

                }
                
            }

         //   print_r( $docs[$type]['upload']);
         // die('come here');

        }

        //overseas
        $type = 'COMPANY_TYPE_OVERSEAS';
        $docs[$type] = array(

            'download' => array(
                array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
            ),
            'upload'   => array()

        );

        $type_id = $this->settings($type,'key')->id;

        $group = DocumentsGroup::where('company_type', $type_id)
                                        ->where('request_type', 'COM_REG')
                                        ->first();
        $group_id = @$group->id;

        $uploads = Documents::where('document_group_id', $group_id)
                            ->where('status', 1)
                            ->orderBy('sort', 'asc')
                            ->get();

        $upload_arr = array();
    
        foreach($uploads as $d ){
                
            $upload_arr[] = $d->id;
              
            $rec = array(
               'dbid' => $d->id,
               'name' => $d->name,
               'savedLocation' => '',
               'required' => ($d->is_required == 'yes' ) ? true : false,
               'specific' => $d->specific_company_member_type,
               'type'   =>$this->slugify($d->name),
               'fee'    => $d->fee,
               'key' => $d->key,
               'uploaded_path' => '',
               'description' => $d->description,
               'issue_certified_copy' => $d->issue_certified_copy,
               'doc_requested' => 'no',
               'admin_set'  => 'no'

            );
        $docs[$type]['upload'][] = $rec;
        }

        if($companyId) {

               
            //   echo ('echo::' .$companyId);
               $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
               ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
               ->whereNotIn('document_id', $upload_arr)
               ->count();
              // print_r( $additional_docs_count);

               if( $additional_docs_count) {
                 

                   $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                   ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                   ->whereNotIn('document_id', $upload_arr)
                                                   ->get();
                                                 //  print_r( $additional_docs);   
                   foreach($additional_docs as $adoc) {

                       $doc_id_in_list = $adoc->document_id;
                       $docInfo =  Documents::where('id', $doc_id_in_list)->first();

                       $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';

                    //   print_r(  $docInfo);

                       $rec = array(
                           'dbid' => $doc_id_in_list,
                          'name' => $docInfo->name,
                          'savedLocation' => '',
                          'required' => true,
                          'specific' => $docInfo->specific_company_member_type,
                          'type'   =>$this->slugify($docInfo->name),
                          'fee'    => $docInfo->fee,
                          'key' => $docInfo->key,
                          'uploaded_path' => '',
                          'comments' =>'',
                          'description' => $docInfo->description,
                          'issue_certified_copy' => $docInfo->issue_certified_copy,
                          'doc_requested' => $doc_requested,
                          'admin_set'  => 'yes'
           
                       );
                       $docs[$type]['upload'][] = $rec;



                   }

               }
               
           }

        //offshore
        $type = 'COMPANY_TYPE_OFFSHORE';
        $docs[$type] = array(

            'download' => array(
                array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                       array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                       array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
            ),
            'upload'   => array()

        );

        $type_id = $this->settings($type,'key')->id;

        $group = DocumentsGroup::where('company_type', $type_id)
                                        ->where('request_type', 'COM_REG')
                                        ->first();
        $group_id = @$group->id;

        $uploads = Documents::where('document_group_id', $group_id)
                            ->where('status', 1)
                            ->orderBy('sort', 'asc')
                            ->get();

        $upload_arr = array();
    
        foreach($uploads as $d ){
                
            $upload_arr[] = $d->id;
              
            $rec = array(
               'dbid' => $d->id,
               'name' => $d->name,
               'savedLocation' => '',
               'required' => ($d->is_required == 'yes' ) ? true : false,
               'specific' => $d->specific_company_member_type,
               'type'   =>$this->slugify($d->name),
               'fee'    => $d->fee,
               'key' => $d->key,
               'uploaded_path' => '',
               'description' => $d->description,
               'issue_certified_copy' => $d->issue_certified_copy

            );
        $docs[$type]['upload'][] = $rec;
        
        }
        if($companyId) {

               
            //   echo ('echo::' .$companyId);
               $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
               ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
               ->whereNotIn('document_id', $upload_arr)
               ->count();
              // print_r( $additional_docs_count);

               if( $additional_docs_count) {
                 

                   $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                   ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                   ->whereNotIn('document_id', $upload_arr)
                                                   ->get();
                                                 //  print_r( $additional_docs);   
                   foreach($additional_docs as $adoc) {

                       $doc_id_in_list = $adoc->document_id;
                       $docInfo =  Documents::where('id', $doc_id_in_list)->first();

                       $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';

                    //   print_r(  $docInfo);

                       $rec = array(
                           'dbid' => $doc_id_in_list,
                          'name' => $docInfo->name,
                          'savedLocation' => '',
                          'required' => true,
                          'specific' => $docInfo->specific_company_member_type,
                          'type'   =>$this->slugify($docInfo->name),
                          'fee'    => $docInfo->fee,
                          'uploaded_path' => '',
                          'key' => $docInfo->key,
                          'comments' =>'',
                          'description' => $docInfo->description,
                          'issue_certified_copy' => $docInfo->issue_certified_copy,
                          'doc_requested' => $doc_requested,
                          'admin_set'  => 'yes'
           
                       );
                       $docs[$type]['upload'][] = $rec;



                   }

               }
               
           }
      
        
        return $docs;


}

function document_map_new($company_type, $dirList, $secList,$secFirms ){
      
    $form_map = array(
       'form_map_id' =>array(),
       'form_map_fee' => array(
       ),
    );

    $docs = $this->documents();
    foreach($docs as $doc_type=>$doc_val ){

       foreach($doc_val['upload'] as $doc ){

          if($doc_type == $company_type){

              $form_map['form_map_id'][$doc['type']] = $doc['dbid'];
              
              if($doc['specific'] == 'director' ){

                if(count($dirList)) {
                    $payment_key = 'PAYMENT_'.$doc['key'];
                    $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;
                  foreach($dirList as $dir ){
                    $form_map['form_map_fee']['director'][$dir['id']] = array(
                        'val' => floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                        'copies' => 0,
                        'original_val' => $payment_value,
                        'key'       => $payment_key,
                        'original_copies' => 1,
                        'for' => $doc['specific'],
                        'stakeholder_id' =>$dir['id'],
                        'issue_certified_copy' => $doc['issue_certified_copy'],
                        'doc_id' => $doc['dbid']

                   );
                      
                  }
                }

              }

              else if($doc['specific'] == 'sec' ){

                if(count($secList)) {
                    $payment_key = 'PAYMENT_'.$doc['key'];
                    $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;

                    foreach($secList as $sec ){

                        $form_map['form_map_fee']['sec'][$sec['id']] = array(
                             'val' => floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                            'copies' => 0,
                            'original_val' => $payment_value,
                            'key'       => $payment_key,
                            'original_copies' => 1,
                            'for' => $doc['specific'],
                            'stakeholder_id' => $sec['id'],
                            'issue_certified_copy' => $doc['issue_certified_copy'],
                            'doc_id' => $doc['dbid']
                       );
                          
      
                      }

                }

                if(count($secFirms)) {
                    $payment_key = 'PAYMENT_'.$doc['key'];
                    $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;

                    foreach($secFirms as $sec ){

                        $form_map['form_map_fee']['secFirm'][$sec['id']] = array(
                             'val' =>floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                            'copies' => 0,
                            'original_val' => $payment_value,
                            'key'       => $payment_key,
                            'original_copies' => 1,
                            'for' => $doc['specific'],
                            'stakeholder_id' => $sec['id'],
                            'issue_certified_copy' => $doc['issue_certified_copy'],
                            'doc_id' => $doc['dbid']
                       );    
      
                      }
                }
               
            }else {

                $payment_key = 'PAYMENT_'.$doc['key'];
                $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;

                $form_map['form_map_fee'][$doc['type']] = array(
                    //'val' => floatval($doc['fee']),
                     'val' => floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                   // 'val' => mt_rand(10,100),
                    'copies' => 0,
                    'original_val' =>$payment_value,
                    'key'       => $payment_key,
                     'original_copies' => 1,
                    'for' => $doc['specific'],
                    'issue_certified_copy' => $doc['issue_certified_copy'],
                    'doc_id' => $doc['dbid']
              );

            }

           
          }

       }

        
    }

    return $form_map;
}

function document_map($company_type){
      
      $form_map = array(
         'form_map_id' =>array(),
         'form_map_fee' => array(
         ),
      );

      $docs = $this->documents();
      foreach($docs as $doc_type=>$doc_val ){

         foreach($doc_val['upload'] as $doc ){

            if($doc_type == $company_type){

                $form_map['form_map_id'][$doc['type']] = $doc['dbid'];
                
                $form_map['form_map_fee'][$doc['type']] = array(
                    //'val' => floatval($doc['fee']),
                     'val' => floatval($doc['fee']),
                   // 'val' => mt_rand(10,100),
                    'copies' => 1,
                    'for' => $doc['specific']
              );

            }

         }

      }

      return $form_map;
}

    function documents_old(){
             $docs = array( 
                'COMPANY_TYPE_PRIVATE' => array(

                    'download' =>array(

                           array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                           array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                           array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                    ),
                        'upload' =>array( 
                            array('dbid' =>'18',  'name' =>'FORM 01','required' => true,'specific'=> '', 'type' => 'FORM01','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )

                    ),

                    'COMPANY_TYPE_PUBLIC' => array(

                        'download' =>array(

                            array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18 ', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                     ),
                        'upload' =>array( 
                            array('dbid' =>'18',  'name' =>'FORM 01','required' => true,'specific'=> '', 'type' => 'FORM01','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )
    
    
                    ),
                    'COMPANY_TYPE_UNLIMITED' => array(

                        'download' =>array(

                            array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                         ),
                         'upload' =>array( 
                             array('dbid' =>'18',  'name' =>'FORM 01','required' => true,'specific'=> '', 'type' => 'FORM01','uploaded_path' =>'' ),
                             array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                             array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                             array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                         )
        
        
                    ),
                    'COMPANY_TYPE_GUARANTEE_32' => array(

                       'download' =>array(

                            array('name' =>'FORM 05', 'savedLocation' => "", 'view'=>'form5', 'specific' =>'','file_name_key' =>'form05' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                        ),
                        'upload' =>array( 
                            array('dbid' =>'19',  'name' =>'FORM 05','required' => true,'specific'=> '', 'type' => 'FORM05','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )

            
                    ),  
                    'COMPANY_TYPE_GUARANTEE_34' => array(
   
                        'download' =>array(

                            array('name' =>'FORM 05', 'savedLocation' => "", 'view'=>'form5', 'specific' =>'','file_name_key' =>'form05' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                        ),
                        'upload' =>array( 
                            array('dbid' =>'19',  'name' =>'FORM 05','required' => true,'specific'=> '', 'type' => 'FORM05','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )
            
                    ),  
                    'COMPANY_TYPE_OVERSEAS' => array(

                        'download' =>array(
                            array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                            array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                            array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
                        ),
                        'upload' =>array( 
                            array('dbid' =>'100',  'name' =>'FORM 44','required' => true,'specific'=> '', 'type' => 'FORM44','uploaded_path' =>'' ),
                            array('dbid' =>'101','name' =>'FORM 45', 'required' => true, 'specific'=> '', 'type' => 'FORM45','uploaded_path' =>''),
                            array('dbid' =>'102','name' =>'FORM 46','required' => true, 'specific'=> '', 'type' => 'FORM46','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Recently certified articles of association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )

            
                    ),
                    'COMPANY_TYPE_OFFSHORE' => array(

                        'download' =>array(  
            
                            array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                            array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                            array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
                        ),
                        'upload' =>array(
            
                            array('dbid' =>'100',  'name' =>'FORM 44','required' => true,'specific'=> '', 'type' => 'FORM44','uploaded_path' =>'' ),
                            array('dbid' =>'101','name' =>'FORM 45', 'required' => true, 'specific'=> '', 'type' => 'FORM45','uploaded_path' =>''),
                            array('dbid' =>'102','name' =>'FORM 46','required' => true, 'specific'=> '', 'type' => 'FORM46','uploaded_path' =>'' ),
                            array('dbid' =>'103','name' =>'Recently certified copy of Company Incorporation certificate','required' => true, 'specific'=> '', 'type' => 'RCCCIC','uploaded_path' =>'' ),
                            array('dbid' =>'104','name' =>'Recently certified memorandum of association Copy','required' => true, 'specific'=> '', 'type' => 'RCMAC','uploaded_path' =>'' ),
                            array('dbid' =>'105','name' =>'Registered power of attorney Confirmation letter','required' => true, 'specific'=> '', 'type' => 'RPACL','uploaded_path' =>'' ),
 
                               
                                    
                        )
            
            
                    ), 

             );

             return $docs;
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


        private function stakeholder_store($company_id){

            $director_list = CompanyMember::where('company_id',$company_id)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->get();
        $directors = array();
        if(count($director_list )){
            foreach($director_list as $director){

                $address ='';
                $forAddress = '';
                if( $director->address_id) {
                    $address = Address::where('id',$director->address_id)->first();
                }
                if( $director->foreign_address_id) {
                    $forAddress = Address::where('id', $director->foreign_address_id)->first();
                }

                $rec = array(
                    'id' => $director->id,
                    'type' => ($director->is_srilankan == 'yes') ? 'local' : 'foreign',
                    'title' =>  $director->title,
                    'firstname' => $director->first_name,
                    'lastname' => $director->last_name,
                    'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                    'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                    'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                    'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                    'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                    'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
    
                    'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                    'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                    'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                    'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                    'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
                    'nic'       => $director->nic,
                    'passport'  => $director->passport_no,
                    'country'  => ( $director->foreign_address_id)  ? $forAddress->country : $address->country,
                    'passport_issued_country' => ( $director->foreign_address_id)  ? $director->passport_issued_country : 'Sri Lanka',
                    //'share'     => $director->no_of_shares,
                    'date'      => $director->date_of_appointment,
                    'phone' => $director->telephone,
                    'mobile' => $director->mobile,
                    'email' => $director->email,
                    'occupation' => $director->occupation

                );
                $directors[] = $rec;
            }
        }

        /******secretory list *****/
        $sec_list = CompanyMember::where('company_id',$company_id)
        ->where('designation_type',$this->settings('SECRETARY','key')->id)
        ->where('status',1)
        ->get();
        $secs = array();

        if(count($sec_list )){
            foreach($sec_list as $sec){

            $address_id =  ($sec->foreign_address_id ) ? $sec->foreign_address_id : $sec->address_id;
            
            if(!$sec->foreign_address_id){
                $address = Address::where('id',$address_id)->first();
            }else{
              //  $address = ForeignAddress::where('id',$address_id)->first();
              $address = Address::where('id',$address_id)->first();
            }

            $rec = array(
                'id' => $sec->id,
                'type' => ($sec->is_srilankan == 'yes' ) ? 'local' : 'foreign',
                'title' =>  $sec->title,
            'firstname' => $sec->first_name,
            'lastname' => $sec->last_name,

            'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
            'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
            'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
            'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
            'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
            'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
    
            'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
            'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
            'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
            'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
            'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',

            'nic'       => $sec->nic,
            'passport'  => $sec->passport_no,
            'country'   => $sec->passport_issued_country,
            //'share'     =>0,
            'date'      => $sec->date_of_appointment,
            'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
            'regDate'      => ($sec->is_registered_secretary =='yes') ? $sec->secretary_registration_no :'',
            'phone' => $sec->telephone,
            'mobile' => $sec->mobile,
            'email' => $sec->email,
            'occupation' => $sec->occupation

            );
            $secs[] = $rec;
            }

        }

         /******secretory firm list *****/
         $sec_list = CompanyFirms::where('company_id',$company_id)
         ->where('type_id',$this->settings('SECRETARY','key')->id)
         ->where('status',1)
         ->get();

        
         $secFirms = array();

         if(count($sec_list )){
            foreach($sec_list as $sec){
    
            $address_id =  $sec->address_id;
            
            $address = Address::where('id',$address_id)->first();

            $rec = array(
                'id' => $sec->id,
                'type' => ($sec->is_srilankan == 'yes' ) ? 'local' : 'foreign',
                'title' =>  $sec->name,
            'registration_no' => $sec->registration_no,
            //'lastname' => $sec->last_name,
    
            'province' =>  ( $address->province) ? $address->province : '',
            'district' =>  ($address->district) ? $address->district : '',
            'city' =>  ( $address->city) ? $address->city : '',
            'localAddress1' => ($address->address1) ? $address->address1 : '',
            'localAddress2' => ($address->address2) ? $address->address2 : '',
            'postcode' => ($address->postcode) ? $address->postcode : '',
            'phone' => $sec->phone,
            'mobile' => $sec->mobile,
            'email' => $sec->email,
            'date'      => $sec->date_of_appointment,
        
            );
            $secFirms[] = $rec;
            }

        }

        $sh_core_groups = array();
        $single_share_total = 0;

        /******share holder list *****/
        $shareholder_list = CompanyMember::where('company_id',$company_id)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->whereNull('company_member_firm_id' )
        ->where('status',1)
        ->get();
        $shareholders = array();

        if(count($shareholder_list )){

            foreach($shareholder_list as $shareholder){

                $address ='';
                $forAddress = '';
                if( $shareholder->address_id) {
                   $address = Address::where('id',$shareholder->address_id)->first();
                }
                if( $shareholder->foreign_address_id) {
                   $forAddress = Address::where('id', $shareholder->foreign_address_id)->first();
                }
        
            $shareRec = array(
                'value' => 0,
                'type' => ''
            );
            $shareRow = Share::where('company_member_id', $shareholder->id)->first();
            if(isset($shareRow->id) ){
                    $shareGroup = ShareGroup::where('id', $shareRow->group_id)->first();
                    
                    $shareRec['value'] = $shareGroup['no_of_shares'];
                    $shareRec['type'] = ($shareGroup['type'] == 'core_share') ? 'core share': 'single share';
                    $shareRec['id'] = $shareGroup['id'];

            }

            if(isset($shareRow->id) ){

                if($shareGroup['type'] == 'core_share') {
                    if(isset($sh_core_groups[$shareGroup['id']])) {
                      //  $sh_core_groups[$shareGroup['id']] = $sh_core_groups[$shareGroup['id']] +  floatval( $shareGroup['no_of_shares'] );
                        
                    } else {
                        $sh_core_groups[$shareGroup['id']] = floatval( $shareGroup['no_of_shares'] );
                       
                    }
                } else {
                    $single_share_total = $single_share_total + floatval($shareGroup['no_of_shares']);
                }

            }



            $rec = array(
                'id' => $shareholder->id,
                'type' => ($shareholder->is_srilankan == 'yes') ? 'local' : 'foreign',
                'title' =>  $shareholder->title,
            'firstname' => $shareholder->first_name,
            'lastname' => $shareholder->last_name,
        
            'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',


            'nic'       => $shareholder->nic,
            'passport'  => $shareholder->passport_no,
            'country'  => ( $shareholder->foreign_address_id)  ? $forAddress->country : $address->country,
            'passport_issued_country' => ( $shareholder->foreign_address_id)  ? $shareholder->passport_issued_country : 'Sri Lanka',
            // 'share'     => $shareholder->no_of_shares,
            'date'      => $shareholder->date_of_appointment,
            'phone' => $shareholder->telephone,
            'mobile' => $shareholder->mobile,
            'email' => $shareholder->email,
            'occupation' => $shareholder->occupation,
            'share' => $shareRec

            );
            $shareholders[] = $rec;
            }

        }
        

        

        /******sh firm list *****/
        $sh_list = CompanyFirms::where('company_id',$company_id)
        ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
        ->where('status',1)
        ->get();
        $shFirms = array();

        if(count($sh_list )){
           foreach($sh_list as $sec){
   
           $address_id =  $sec->address_id;
           
           $address = Address::where('id',$address_id)->first();

           $shareRec = array(
            'value' => 0,
            'type' => ''
            );
            $shareRow = Share::where('company_firm_id', $sec->id)->first();
            if(isset($shareRow->id) ){
                    $shareGroup = ShareGroup::where('id', $shareRow->group_id)->first();
                    
                    $shareRec['value'] = $shareGroup['no_of_shares'];
                    $shareRec['type'] = ($shareGroup['type'] == 'core_share') ? 'core share': 'single share';
                    $shareRec['id'] = $shareGroup['id'];
                     

                    if($shareGroup['type'] == 'core_share') {
                        if(isset($sh_core_groups[$shareGroup['id']])) {
                          //  $sh_core_groups[$shareGroup['id']] = $sh_core_groups[$shareGroup['id']] +  floatval( $shareGroup['no_of_shares'] );
                        } else {
                            $sh_core_groups[$shareGroup['id']] = floatval( $shareGroup['no_of_shares'] );
                        }
                    } else {
                        $single_share_total = $single_share_total + floatval($shareGroup['no_of_shares']);
                    }
                    


            }

           $rec = array(
               'id' => $sec->id,
               'type' => 'local',
               'title' =>  $sec->name,
           'registration_no' => $sec->registration_no,
           //'lastname' => $sec->last_name,
   
           'province' =>  ( $address->province) ? $address->province : '',
           'district' =>  ($address->district) ? $address->district : '',
           'city' =>  ( $address->city) ? $address->city : '',
           'localAddress1' => ($address->address1) ? $address->address1 : '',
           'localAddress2' => ($address->address2) ? $address->address2 : '',
           'postcode' => ($address->postcode) ? $address->postcode : '',
           'phone' => $sec->phone,
           'mobile' => $sec->mobile,
           'email' => $sec->email,
           'share' => $shareRec,
           'date'      => $sec->date_of_appointment,
          

       
           );
           $shFirms[] = $rec;
           }

       }

       $total_core_share_value = 0;
       if(count($sh_core_groups)){
           foreach($sh_core_groups as $key=> $shares ) {
               $total_core_share_value += $shares;
           }
       }



        return array(

            'directors' => $directors,
            'secs'      => $secs,
            'secFirms'  => $secFirms,
            'shs'       => $shareholders,
            'shFirms'   => $shFirms,
            'total_shares' => ( $total_core_share_value + $single_share_total )
        );


        }

        function generate_files($doc_type,$companyId,$loginUserEmail){

            $loginUserInfo = User::where('email', $loginUserEmail)->first();
            $loginUserId = $loginUserInfo->people_id;
    
            $userPeople = People::where('id',$loginUserId)->first();
            $userAddressId = $userPeople->address_id;  
            $userAddress = Address::where('id', $userAddressId)->first();

           
            //get payment date
            $payment_date= '';
            $payment_row = Order::where('module_id', $comapanyId)
                            ->where('module', $this->settings('MODULE_INCORPORATION','key')->id)
                            ->first();
            $payment_date = isset($payment_row->updated_at) ? strtotime($payment_row->updated_at) : '';

           // $payment_date = strtotime('2018-10-24 20:05:58');


            $docs = $this->getDocs($doc_type, $companyId );

            $downloaded = $docs['download'];

            $generated_files = array(

                'other' => array(),
                'director' => array(),
                'sec'   => array()


            );
            $company_info = Company::where('id',$companyId)->first();
            $postfix_values = $this->getPostfixValues($company_info->postfix);


            if(count($downloaded)){
                foreach($downloaded as $file ){
                      
                    $name = $file['name'];
                    $file_name_key = $file['file_name_key'];

                    $stakeholder_store = $this->stakeholder_store($companyId);

                    
                    
                    
                    $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

                    $company_address = Address::where('id',$company_info->address_id)->first();

                    if( isset( $company_info->foreign_address_id) && $company_info->foreign_address_id ) {
                        $company_for_address = Address::where('id',$company_info->foreign_address_id)->first();
                    }else {
                        $company_for_address = '';
                    }
                   


                if($file['specific']  == 'director'){

                    $companyType = $this->settings($company_info->type_id,'id');

                    if(count($stakeholder_store['directors'])) {
                        foreach( $stakeholder_store['directors'] as $director ){ 

                        $data = array(
                            'public_path' => public_path(),
                            'eroc_logo' => url('/').'/images/forms/eroc.png',
                            'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                            'css_file' => url('/').'/images/forms/form1/form1.css',
                            'director' => $director,
                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'company_type' => $companyType->value,
                            'loginUser' => $userPeople,
                            'loginUserAddress' => $userAddress,
                            'payment_date' => $payment_date,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],

                        );
                        
                            $directory = $companyId;
                            Storage::makeDirectory($directory);
            

                            $view = 'forms.'.$file['view'];
                            $director_id = $director['id'];
            
                            $pdf = PDF::loadView($view, $data);
                            $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$director_id.'.pdf');

                            $file_row = array();
                            $file_row['name'] = $file['name'];
                            $file_row['stakeholder_name'] = $director['firstname'].' '.$director['lastname'];
                            $file_row['stakeholder_id'] = $director['id'];
                            $file_row['file_name_key'] = $file_name_key;
    
                      
                          $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$director_id.pdf");
                      

                           $generated_files['director'][] = $file_row;

                        }
                    }
    
                }else if($file['specific']  == 'sec'){

                    $companyType = $this->settings($company_info->type_id,'id');

                    if( count($stakeholder_store['secs']) ) {
                        foreach( $stakeholder_store['secs'] as $sec ){

                        $data = array(
                            'public_path' => public_path(),
                            'eroc_logo' => url('/').'/images/forms/eroc.png',
                            'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                            'css_file' => url('/').'/images/forms/form1/form1.css',
                            'sec' => $sec,
                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'company_type' => $companyType->value,
                            'loginUser' => $userPeople,
                            'loginUserAddress' => $userAddress,
                            'sec_type' => 'natural',
                            'payment_date' => $payment_date,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],

                        );
                        
                            $directory = $companyId;
                            Storage::makeDirectory($directory);
                            $view = 'forms.'.$file['view'];
                         
                            $sec_id = $sec['id'];
            
                            $pdf = PDF::loadView($view, $data);
                            $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'. $sec['id'].'.pdf');

                            $file_row = array();
                            $file_row['name'] = $file['name'];
                            $file_row['stakeholder_name'] = $sec['firstname'].' '.$sec['lastname'];
                            $file_row['stakeholder_id'] = $sec['id'];
                            $file_row['file_name_key'] = $file_name_key;
                            $file_row['type'] = 'natural';
    
                         
                        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$sec_id.pdf");
                            $generated_files['sec'][] = $file_row;

                        }
                    }

                    if( count($stakeholder_store['secFirms']) ) {
                        foreach( $stakeholder_store['secFirms'] as $sec ){

                        $data = array(
                            'public_path' => public_path(),
                            'eroc_logo' => url('/').'/images/forms/eroc.png',
                            'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                            'css_file' => url('/').'/images/forms/form1/form1.css',
                            'sec' => $sec,
                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'company_type' => $companyType->value,
                            'loginUser' => $userPeople,
                            'loginUserAddress' => $userAddress,
                            'sec_type' => 'firm',
                            'payment_date' => $payment_date,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                        );
                        
                            $directory = $companyId;
                            Storage::makeDirectory($directory);
                            $view = 'forms.'.$file['view'];
                         
                            $sec_id = $sec['id'];
            
                            $pdf = PDF::loadView($view, $data);
                            $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'. $sec['id'].'.firm'.'.pdf');

                            $file_row = array();
                            $file_row['name'] = $file['name'];
                            $file_row['stakeholder_name'] = $sec['title'].' (Organization)';
                            $file_row['stakeholder_id'] = $sec['id'];
                            $file_row['file_name_key'] = $file_name_key;
                            $file_row['type'] = 'firm';
    
                         
                        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$sec_id.firm.pdf");
                            $generated_files['sec'][] = $file_row;

                        }
                    }


                }else{
                    $companyType = $this->settings($company_info->type_id,'id');
                    $data = array(
                        'public_path' => public_path(),
                        'eroc_logo' => url('/').'/images/forms/eroc.png',
                        'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                        'css_file' => url('/').'/images/forms/form1/form1.css',
                        'directors' => $stakeholder_store['directors'],
                        'secs' => $stakeholder_store['secs'],
                        'secFirms' => $stakeholder_store['secFirms'],
                        'shs' => $stakeholder_store['shs'],
                        'shFirms' => $stakeholder_store['shFirms'],
                        'company_info' => $company_info,
                        'company_address' => $company_address,
                        'company_for_address' => $company_for_address,
                        'company_type' => $companyType->value,
                        'loginUser' => $userPeople,
                        'loginUserAddress' => $userAddress,
                        'payment_date' => $payment_date,
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'total_shares' => $stakeholder_store['total_shares']
                       
                    );
            
                  //  dd($userPeople->first_name);
            
                    $directory = $companyId;
                    Storage::makeDirectory($directory);
  
                    $view = 'forms.'.$file['view'];
                    $pdf = PDF::loadView($view, $data);
                    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'.pdf');

                    $file_row = array();
                    $file_row['name'] = $file['name'];
                    $file_row['file_name_key'] = $file_name_key;
  
                 
                 $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key.pdf");
                   
                    $generated_files['other'][] =  $file_row;
                }


                }
            }
            
            return $generated_files;
        }


        function files_for_upload($doc_type,$companyId){

            $docs = $this->getDocs($doc_type, $companyId );

            $uploaded = $docs['upload'];

            $generated_files = array(
                'other' => array(),
                'multiple_other1'=> array(),
                'multiple_other2'=> array(),
                'director' => array(),
                'sec'   => array(),
                'secFirm' => array(),
               
            );

            $document_resubmit_status = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $document_request_status = $this->settings('DOCUMENT_REQUESTED','key')->id;
            $company_info = Company::where('id',$companyId)->first();
            $company_status = $this->settings($company_info->status,'id')->key;
            
         

            if(count($uploaded)){
                $stakeholder_store = $this->stakeholder_store($companyId);
                foreach($uploaded as $file ){
                      
                    $name = $file['name'];
                

                if($file['specific']  == 'director'){

                    foreach( $stakeholder_store['directors'] as $director ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){


                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('company_member_id',$director['id'])
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';
                            

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('company_member_id',$director['id'])
                            ->first();
                            
                            
                           

                            if( isset( $for_Resubmission_doc->id) ) {

                                

                               $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }
                           
                            $file_row['stakeholder_name'] = $director['firstname'].' '.$director['lastname'];
                            $file_row['stakeholder_id'] = $director['id'];
                            $file_row['is_required'] = $file['required'];
                            $file_row['file_name'] = $file['name'];
                            $file_row['file_type'] = $file['type'];
                            $file_row['dbid'] = $file['dbid'];
                            $file_row['description'] = $file['description'];
                            $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                          
                            $generated_files['director'][] = $file_row;

                    }

                }else if($file['specific']  == 'sec'){

                    foreach( $stakeholder_store['secs'] as $sec ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] ='DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('company_member_id',$sec['id'])
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                          //  $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('company_member_id',$sec['id'])
                            ->first();
                            
                            if( isset( $for_Resubmission_doc->id) ) {

                              $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }

                        $file_row['stakeholder_name'] = $sec['firstname'].' '.$sec['lastname'];
                        $file_row['stakeholder_id'] = $sec['id'];
                        $file_row['is_required'] = $file['required'];
                        $file_row['file_name'] = $file['name'];
                        $file_row['file_type'] = $file['type'];
                        $file_row['dbid'] = $file['dbid'];
                        $file_row['description'] = $file['description'];
                        $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                        $file_row['isFirm'] = false;
    
                        $generated_files['sec'][] = $file_row;
                    }

                    foreach( $stakeholder_store['secFirms'] as $sec ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] ='DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('company_firm_id',$sec['id'])
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                           // $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('company_firm_id',$sec['id'])
                            ->first();
                            
                            if( isset( $for_Resubmission_doc->id) ) {

                              $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }

                        $file_row['stakeholder_name'] = $sec['title'].' (Organization)';
                        $file_row['stakeholder_id'] = $sec['id'];
                        $file_row['stakeholder_prefix_id'] = 'firm-'.$sec['id'];
                        $file_row['is_required'] = $file['required'];
                        $file_row['file_name'] = $file['name'];
                        $file_row['file_type'] = $file['type'];
                        $file_row['dbid'] = $file['dbid'];
                        $file_row['isFirm'] = true;
                        $file_row['description'] = $file['description'];
                        $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
    
                        $generated_files['secFirm'][] = $file_row;
                    }

                }

                    else if($file['specific']  == 'multiple1'){

                        for( $i=0; $i < 5; $i++ ){
    
                            $file_row = array();
                            $file_row['doc_comment'] = '';
                            $file_row['doc_status'] = 'DOCUMENT_PENDING';
                            $file_row['company_status'] = $company_status;
                            if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                            $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                            $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                            $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                               // $file_row['doc_status'] = 'DOCUMENT_APPROVED';

                                $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                                ->where('company_id', $companyId)
                                ->where('multiple_id',$i)
                                ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                                ->first();
                               // $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                                $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';
    
                                $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                                ->where('company_id', $companyId)
                                ->whereIn('status', array($document_resubmit_status,$document_request_status))
                                ->where('multiple_id',$i)
                                ->first();
                                
                                
                               
    
                                if( isset( $for_Resubmission_doc->id) ) {
    
                                   $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;
    
                                   $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                        ->where('status', $for_Resubmission_doc->status)
                                                        ->orderBy('id', 'desc')
                                                        ->first();
                                   
                                   if( isset($doc_status_row->id) ){
    
                                    $file_row['doc_comment'] = $doc_status_row->comments;
                                   }else{
                                    $file_row['doc_comment'] = '';
                                   }
                                }
                            }
                               
                                $file_row['is_required'] = $file['required'];
                                $file_row['file_name'] = $file['name'];
                                $file_row['file_type'] = $file['type'];
                                $file_row['dbid'] = $file['dbid'];
                                $file_row['description'] = $file['description'];
                                $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                                $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                                $generated_files['multiple_other1'][] = $file_row;
    
                        }
    
                    

                }
                else if($file['specific']  == 'multiple2'){

                    for( $i=0; $i < 5; $i++ ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                          //  $file_row['doc_status'] = 'DOCUMENT_APPROVED';

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('multiple_id',$i)
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                           // $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('multiple_id',$i)
                            ->first();
                            
                            
                           

                            if( isset( $for_Resubmission_doc->id) ) {

                               $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }
                           
                            $file_row['is_required'] = $file['required'];
                            $file_row['file_name'] = $file['name'];
                            $file_row['file_type'] = $file['type'];
                            $file_row['dbid'] = $file['dbid'];
                            $file_row['description'] = $file['description'];
                            $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                            $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                            $generated_files['multiple_other2'][] = $file_row;

                    }

                

            }
                
                else{

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        

                        if( isset($file['doc_requested']) && $file['doc_requested'] == 'yes' ) {
                            $file_row['doc_status'] = 'DOCUMENT_REQUESTED';
                        }else {
                            $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        }

                       // $file_row['doc_status'] = 'DOCUMENT_PENDING';
                      
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();

                          //  $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->first();
                            
                            if( isset( $for_Resubmission_doc->id) ) {

                                $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                                $file_row['doc_statusssss'] = $doc_status_row;
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }

                    $file_row['is_required'] = $file['required'];
                    $file_row['file_name'] = $file['name'];
                    $file_row['file_type'] = $file['type'];
                    $file_row['dbid'] = $file['dbid'];
                    $file_row['description'] = $file['description'];
                    $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                    $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                    $generated_files['other'][] = $file_row;

                }
                }
            }



            return $generated_files;
        
        }

       /**********debugging forms */
        function checkform01(){

            $companyId = 1809136691;

            $stakeholder_store = $this->stakeholder_store($companyId);
            $company_info = Company::where('id',$companyId)->first(); 
            $company_address = Address::where('id',$company_info->address_id)->first();

           // print_r($stakeholder_store['secs'] );

            $data = array(
                'public_path' => public_path(),
                'eroc_logo' => url('/').'/images/forms/eroc.png',
                'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                'css_file' => url('/').'/images/forms/form1/form1.css',
                'directors' => $stakeholder_store['directors'],
                'secs' => $stakeholder_store['secs'],
                'shs' => $stakeholder_store['shs'],
                'company_info' => $company_info,
                'company_address' => $company_address
            );

            return view('forms/test-forms/form1', $data);
        }

        function upload_file(){
            return view('forms/upload');
        } 

        function upload(Request $request){


            $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $company_id = $request->company_id;

            $request_id = $this->valid_annual_return_request_operation($company_id);

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
        
            $path = 'annual-return/'.substr($company_id,0,2).'/'.$request_id;
          //  $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
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
            $request_id = $this->valid_annual_return_request_operation($company_id);

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
            
            $request_id = $this->valid_annual_return_request_operation($company_id);
    
      
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
        
            $path = 'annual-return/other-docs/'.substr($company_id,0,2);
          //  $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
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
            $request_id = $this->valid_annual_return_request_operation($company_id);
    
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
        
            $path = 'annual-return/other-docs/'.substr($company_id,0,2);
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
            $token = md5(uniqid());
    
              
             $form_other_docs = Documents::where('key', 'ANNUAL_RETURN_OTHER_DOCUMENTS')->first();
    
    
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
    
        function removeOtherDoc(Request $request){
    
            $file_token = $request->file_token;
            
        
            CompanyDocuments::where('file_token', $file_token)
                             ->delete();
        
            return response()->json([
                            'message' => 'File removed successfully.',
                            'status' =>true,
                            
        
            ], 200);
        }

        /*********** */


        function getProvincesDisctrictsCities() {


            $provinces_cache = Cache::rememberForever('provinces_cache', function () {
                $provinces_results = Province::all();

                $provinces = array();
    
                foreach($provinces_results as $p ) {
    
                     $rec = array(
                        'id' => $p->id,
                        'name' => $p->description_en
                     );
                     $provinces[] = $rec;
                     
                }

                 return $provinces;
            });

            $districts_cache = Cache::rememberForever('districts_cache', function () {
                $districts_results = District::all();
                $districts = array();

                foreach( $districts_results as $d ) {

                    $provinceName = Province::where('id', $d->province_code)->first();
                    $rec = array(
                        'id' => $d->id,
                        'name' => $d->description_en,
                        'provinceName' => $provinceName->description_en
                    );
                    $districts[] = $rec;
                }
                return $districts;
            });

            $cities_cache = Cache::rememberForever('cities_cache', function () {
                $city_results = City::all();
                $cities = array();

                foreach( $city_results as $c ) {

                    $districtName = District::where('id', $c->district_code )->first();
                    $rec = array(
                        'id' => $c->id,
                        'name' => $c->description_en,
                        'districtName' => $districtName->description_en
                    );
                    $cities[] = $rec;
                }
                return $cities;
            });

            $gns_cache = Cache::rememberForever('gns_cache', function () {
                $gn_results = GNDivision::orderBy('description_en', 'asc')->get();
                $gns = array();

                foreach( $gn_results as $g ) {

                    $cityName = City::where('id', $g->city_code )->first();

                    $rec = array(
                        'id' => $g->id,
                        'name' => $g->description_en,
                        'cityName' => @$cityName->description_en
                    );
                    $gns[] = $rec;
                }
                return $gns;
            });

            return array(
                'provinces' => $provinces_cache,
                'districts' => $districts_cache,
                'cities' => $cities_cache,
                'gns'   => $gns_cache
            );
            

             
        }


  
    function saveNoOfCopies( Request $request ) {

        $copyRequest = $request->copiesArr;
        $companyId = $request->companyId;

        $incorp_module = $this->settings('MODULE_INCORPORATION','key')->id;

        if(is_array($copyRequest) && count($copyRequest)){

        CompanyDocumentCopies::where('module_id', $companyId)
                                ->where('module', $incorp_module)
                                ->delete();
            foreach($copyRequest as $r ) {

                if(!intval($r['copies'])) {
                    continue;
                }

                $rec = new CompanyDocumentCopies;
                $rec->module = $incorp_module;
                $rec->module_id = $companyId;
                $rec->document_id = $r['doc_id'];
                $rec->member_id = ($r['member_id']) ? $r['member_id'] : null;
                $rec->firm_id = ($r['firm_id']) ? $r['firm_id'] : null;
                $rec->no_of_copies = (int) $r['copies'];
                $rec->save();

            }
        }
    }


} // end class