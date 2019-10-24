<?php
namespace App\Http\Controllers\API\v1\foreignCompanyAlterations;
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

use App\OffshoreAlteration;
use App\CourtCase;
use App\SettingType;

class OffshoreCompanyAlterationsController extends Controller
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
  
    $file_name_key = 'form23';
    $file_name = 'FORM 23';

 
    $data = $info_array;
                  
    $directory = "overseas-of-alterations/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form23';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;

    /************** */ 
    
    
    $changes_director = CompanyItemChange::leftJoin('company_members', 'company_item_changes.item_id', '=', 'company_members.id')
    ->where('company_item_changes.request_id',$request_id)
   ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
   ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
   ->whereNull('company_item_changes.old_record_id')
   ->where('company_members.designation_type', $this->settings('DERECTOR','key')->id)
   ->select('*')
   ->count();

   $changes_sec = CompanyItemChange::leftJoin('company_members', 'company_item_changes.item_id', '=', 'company_members.id')
    ->where('company_item_changes.request_id',$request_id)
   ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
   ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
   ->whereNull('company_item_changes.old_record_id')
   ->where('company_members.designation_type', $this->settings('SECRETARY','key')->id)
   ->select('*')
   ->count();
   $changes_sec_firms = CompanyItemChange::leftJoin('company_member_firms', 'company_item_changes.item_id', '=', 'company_member_firms.id')
    ->where('company_item_changes.request_id',$request_id)
   ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
   ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
    ->whereNull('company_item_changes.old_record_id')
   ->where('company_member_firms.type_id', $this->settings('SECRETARY','key')->id)
   ->select('*')
   ->count();

   //if($changes_director) {
   if(false) {

    $view = 'diretor-secretary-change.'.'form45';
        $file_name_key = 'form45';
        $pdf = PDF::loadView($view, $data);
        $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');
                        
        $file_row['name'] = 'FORM 45';
        $file_row['file_name_key'] = 'form45';
        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
        $generated_files['docs'][] = $file_row;

   }

   //if($changes_sec || $changes_sec_firms) {
   if(false) {
    $view = 'diretor-secretary-change.'.'form46';
    $pdf = PDF::loadView($view, $data);
    $file_name_key = 'form46';
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');
                            
    $file_row['name'] = 'FORM 46';
    $file_row['file_name_key'] = 'form46';
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;
   }

    
   /* $changes = CompanyItemChange::where('request_id',$request_id)
        ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
        ->where('changes_type', $this->settings('ADD','key')->id)
        ->get();

        if(isset($changes[0]->id)){

            //check new directors
            foreach($changes as $member ) {
                $memberInfo = CompanyMember::where('id', $member->item_id)->first();
                $is_director = $this->settings('DERECTOR','key')->id == $memberInfo->designation_type;

                if(!$is_director) {
                    continue;
                }

                $view = 'diretor-secretary-change.'.'form45';
                $file_name_key = 'form45';
                $pdf = PDF::loadView($view, $data);
                $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'-member-'.$memberInfo->id.'.pdf');
                                
                $file_row['name'] = 'FORM 45 for '. $memberInfo->first_name.' '.$memberInfo->last_name;
                $file_row['file_name_key'] = 'form45';
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id-member-$memberInfo->id.pdf");
                $generated_files['docs'][] = $file_row;
            } //end check new directors 

           //check new power of attorny member
            foreach($changes as $member ) {
                $memberInfo = CompanyMember::where('id', $member->item_id)->first();
                $is_sec = $this->settings('SECRETARY','key')->id == $memberInfo->designation_type;

                if(!$is_sec) {
                    continue;
                }

                $view = 'diretor-secretary-change.'.'form46';
                $pdf = PDF::loadView($view, $data);
                $file_name_key = 'form46';
                $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'-member-'.$memberInfo->id.'.pdf');
                                
                $file_row['name'] = 'FORM 46 for '. $memberInfo->first_name.' '.$memberInfo->last_name;
                $file_row['file_name_key'] = 'form46';
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id-member-$memberInfo->id.pdf");
                $generated_files['docs'][] = $file_row;
               

            }
        }

        //check new power of attorny firms
        $changes = CompanyItemChange::where('request_id',$request_id)
        ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
        ->where('changes_type', $this->settings('ADD','key')->id)
        ->get();
        if(isset($changes[0]->id)){

            foreach($changes as $firm ) {

                $memberInfo = CompanyFirms::where('id', $firm->item_id)->first();
                $is_sec = $this->settings('SECRETARY','key')->id == $memberInfo->type_id;

                if(!$is_sec) {
                    continue;
                }

                $view = 'diretor-secretary-change.'.'form46';
                $pdf = PDF::loadView($view, $data);
                $file_name_key = 'form46';
                $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'-firm-'.$memberInfo->id.'.pdf');
                                
                $file_row['name'] = 'FORM 46 for '. $memberInfo->name;
                $file_row['file_name_key'] = 'form46';
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id-firm-$memberInfo->id.pdf");
                $generated_files['docs'][] = $file_row;


            }

        } */

    /*********** */


    return $generated_files;
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
        
        $annual_return_request_type =  $this->settings('OFFSHORE_ALTERATIONS','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$annual_return_request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_23 = Documents::where('key', 'FORM_23')->first();
        $form_other_docs = Documents::where('key', 'ALTERATIONS_OF_OFFSHORE_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_23->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_23->id;
        $file_row['file_description'] = $form_23->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_23->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'OFFSHORE_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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


         /******** */
        
         $form_45 = Documents::where('key', 'OFFSHORE_FORM45')->first();
         $form_46 = Documents::where('key', 'OFFSHORE_FORM46')->first();

         $changes_director = CompanyItemChange::leftJoin('company_members', 'company_item_changes.item_id', '=', 'company_members.id')
                ->where('company_item_changes.request_id',$request_id)
            ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
            ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
            ->whereNull('company_item_changes.old_record_id')
            ->where('company_members.designation_type', $this->settings('DERECTOR','key')->id)
            ->select('*')
            ->count();
        
            $changes_sec = CompanyItemChange::leftJoin('company_members', 'company_item_changes.item_id', '=', 'company_members.id')
                ->where('company_item_changes.request_id',$request_id)
            ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
            ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
            ->whereNull('company_item_changes.old_record_id')
            ->where('company_members.designation_type', $this->settings('SECRETARY','key')->id)
            ->select('*')
            ->count();
            $changes_sec_firms = CompanyItemChange::leftJoin('company_member_firms', 'company_item_changes.item_id', '=', 'company_member_firms.id')
                ->where('company_item_changes.request_id',$request_id)
            ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
            ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
            ->whereNull('company_item_changes.old_record_id')
            ->where('company_member_firms.type_id', $this->settings('SECRETARY','key')->id)
            ->select('*')
            ->count();

       // if($changes_director) {
        if(false) {

            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_45->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_45->id;
            $file_row['file_description'] = $form_45->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['firm_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_45->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'OVERSEAS_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

       // if($changes_sec || $changes_sec_firms ) {
        if(false ) {

            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_46->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_46->id;
            $file_row['file_description'] = $form_46->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['firm_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_46->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($request->status == 'OVERSEAS_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
         
         /******** */


        //other documents (those are ususally visible on requesting by the admin )
        $annualReturnGroup = DocumentsGroup::where('request_type', 'OFFSHORE_ALTERATIONS')->first();
        $annualReturnDocuments = Documents::where('document_group_id', $annualReturnGroup->id)
                                            ->get();
        $annualReturnDocumentsCount = Documents::where('document_group_id', $annualReturnGroup->id)
                                                ->count();

        $form_23_charter = Documents::where('key', 'FORM_23_CHARTER')->first();
        $form_23_statute = Documents::where('key', 'FORM_23_STATUTE')->first();
        $form_23_memo = Documents::where('key', 'FORM_23_MEMORANDUM')->first();
        $form_23_articls = Documents::where('key', 'FORM_23_ARTICLES')->first();


        if($annualReturnDocumentsCount){
            foreach($annualReturnDocuments as $other_doc ) {

                if(
                   $form_23->id === $other_doc->id ||
                   $form_23_charter->id === $other_doc->id ||
                   $form_23_memo->id === $other_doc->id ||
                   $form_23_articls->id === $other_doc->id ||
                   $form_23_statute->id === $other_doc->id || 
                   $form_other_docs->id === $other_doc->id
                   
                ) {
                    continue;
                }


                $is_document_requested =  CompanyDocuments::where('company_id', $company_id)
                ->where('request_id',$request_id)
                ->where('document_id', $other_doc->id )
                ->whereIn('status', array(
                            $this->settings('DOCUMENT_REQUESTED','key')->id,
                            $this->settings('DOCUMENT_APPROVED','key')->id,
                            $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                            $this->settings('DOCUMENT_PENDING','key')->id
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
                if($request->status == 'OFFSHORE_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id || $uploadeDocStatus == $this->settings('DOCUMENT_REQUESTED','key')->id ) { //if doc is resubmitted

                    $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                            ->whereIn('status', array($this->settings('DOCUMENT_REQUESTED','key')->id, $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )  )
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
        $generated_files['enable_45'] =  ( $changes_director );
        $generated_files['enable_46'] = ($changes_sec || $changes_sec_firms);
        return $generated_files;
    
    }



    function files_for_upload_other_docs($company_id){


        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'charter_file_upload' => '',
                'statute_file_upload' => '',
                'memorandum_file_upload' => '',
                'article_file_upload' => '',
            
        );

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id) {
            return $generated_files;
        }

        $has_all_uploaded_str = '';
        
        $annual_return_request_type =  $this->settings('OFFSHORE_ALTERATIONS','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$annual_return_request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();
        
        $record = OffshoreAlteration::where('request_id', $request_id)->first();

      
        // documents list
        $form_35_charter = Documents::where('key', 'FORM_23_CHARTER')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_35_charter->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_35_charter->id;
        $file_row['file_description'] = $form_35_charter->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
        $file_row['date_field'] = 'charter_date';
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_35_charter->id )
                                        ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'OFFSHORE_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

        $v =  ($file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' && $record->charter_change_date ) || 
              ($file_row['uploaded_path'] == '' &&  $file_row['uploaded_token'] == '' && !$record->charter_change_date );
        
        $has_all_uploaded_str = $has_all_uploaded_str.( intval ($v) );
        $generated_files['charter_file_upload']= $file_row['uploaded_path'];       
        $generated_files['docs'][] = $file_row;



        $form_35_statute = Documents::where('key', 'FORM_23_STATUTE')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_35_statute->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_35_statute->id;
        $file_row['file_description'] = $form_35_statute->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
        $file_row['date_field'] = 'statute_date';
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_35_statute->id )
                                        ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'OFFSHORE_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        
        $v =  ($file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' && $record->statute_change_date ) || 
              ($file_row['uploaded_path'] == '' &&  $file_row['uploaded_token'] == '' && !$record->statute_change_date );
        $has_all_uploaded_str = $has_all_uploaded_str.( intval ($v) );
        $generated_files['statute_file_upload']= $file_row['uploaded_path'];   
        $generated_files['docs'][] = $file_row;



        $form_35_memo = Documents::where('key', 'FORM_23_MEMORANDUM')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_35_memo->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_35_memo->id;
        $file_row['file_description'] = $form_35_memo->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
        $file_row['date_field'] = 'memorandum_date';
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_35_memo->id )
                                        ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'OFFSHORE_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

        $v =  ($file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' && $record->memorandum_change_date ) || 
              ($file_row['uploaded_path'] == '' &&  $file_row['uploaded_token'] == '' && !$record->memorandum_change_date );
        $has_all_uploaded_str = $has_all_uploaded_str.( intval ($v) );
        $generated_files['memorandum_file_upload']= $file_row['uploaded_path'];                       
        $generated_files['docs'][] = $file_row;


        $form_35_articls = Documents::where('key', 'FORM_23_ARTICLES')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_35_articls->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_35_articls->id;
        $file_row['file_description'] = $form_35_articls->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
       // $file_row['article_date'] = $record->article_change_date;
        $file_row['date_field'] = 'article_date';
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_35_articls->id )
                                        ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'OFFSHORE_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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

        $v =  ($file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' && $record->article_change_date ) || 
            ($file_row['uploaded_path'] == '' &&  $file_row['uploaded_token'] == '' && !$record->article_change_date );
        $has_all_uploaded_str = $has_all_uploaded_str.( intval ($v) );
        $generated_files['article_file_upload']= $file_row['uploaded_path'];  
        $generated_files['docs'][] = $file_row;


       // $generated_files['uploadedAll'] =   strpos($has_all_uploaded_str, '1') !== false ;
        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
        return $generated_files;
    
    }

    private function get_director_changes($request_id,$company_id){

        $secretory_records = array();
        $director_records = array();

        $changes_count = CompanyItemChange::where('request_id',$request_id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->count();
        

        if($changes_count){

            $changes = CompanyItemChange::where('request_id',$request_id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->get();
            foreach($changes  as $change ) {
                $member_id = $change->item_id;
                $member_info_count = CompanyMember::where('id', $member_id)->count();

                if(!$member_info_count) {
                    continue;
                }

              
                $effective_date = $change->change_or_effective_date;
                $change_type = $this->settings($change->changes_type,'id')->key;

                if($change_type == 'EDIT') {
                    $change_type = ( $change->old_record_id ) ? 'Modified' : 'Appointed';
                }
                if($change_type == 'ADD') {
                    $change_type = 'Appointed';
                }
                if($change_type == 'DELETE') {
                    $change_type = 'Resigned';
                }

                $member_info = CompanyMember::where('id', $member_id)->first();
                $member_address_id = $member_info->address_id;
                $member_address = '';
                if($member_info->address_id){
                    $member_address = Address::where('id', $member_address_id)->first();
                    $nationality =  $member_info->is_srilankan == 'yes' ? $member_address->country : '';
                }
                
                $member_for_address = '';
                if($member_info->foreign_address_id) {
                    $member_for_address = Address::where('id', $member_info->foreign_address_id)->first();
                    $nationality =  $member_info->is_srilankan == 'no' ? $member_for_address->country : '';
                }

                $row = array();
                $row['full_name'] = $member_info->first_name.' '. $member_info->last_name;
                $row['address'] = $member_address;
                $row['for_address'] = $member_for_address;
                $row['change'] = $change_type;
                $row['effective_date'] = $effective_date;
                $row['occupation'] = $member_info->occupation;
                $row['nationality'] = $nationality;
                $row['is_srilankan'] = $member_info->is_srilankan;

                if($this->settings($member_info->designation_type,'id')->key == 'DERECTOR')  {
                    $row['stakeholder_type'] = 'Director';

                    $director_records[] = $row;
                }
                if($this->settings($member_info->designation_type,'id')->key == 'SECRETARY')  {
                    $row['stakeholder_type'] = 'Power of Attorney Individual';
                    $secretory_records[] = $row;
                }

            }

        }

        $mems = CompanyMember::where('company_id', $company_id)
        ->where('status', 1)
        ->whereIn('designation_type',  array( $this->settings('DERECTOR','key')->id,$this->settings('SECRETARY','key')->id ) )
        ->get();
        if(isset($mems[0]->id)) {
            foreach($mems as $m  ) {
                $has_edit_changes = CompanyItemChange::where('request_id',$request_id)
                        ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                        ->where('changes_type', $this->settings('EDIT','key')->id)
                        ->where('old_record_id', $m->id )
                        ->count();
               $has_delete_changes = CompanyItemChange::where('request_id',$request_id)
                        ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                        ->where('changes_type', $this->settings('DELETE','key')->id)
                        ->where('item_id', $m->id )
                        ->count();
                if($has_edit_changes || $has_delete_changes ) {
                    continue;
                }
                
                $nationality = '';

                $member_address_id = $m->address_id;
                $member_address = '';
                if($m->address_id){
                    $member_address = Address::where('id', $member_address_id)->first();
                    $nationality =  $m->is_srilankan == 'yes' ? $member_address->country : '';

                }
                
                $member_for_address = '';
                if($m->foreign_address_id) {
                    $member_for_address = Address::where('id', $m->foreign_address_id)->first();
                    $nationality =  $m->is_srilankan == 'no' ? $member_for_address->country : '';
                }
    
                $row = array();
                $row['full_name'] = $m->first_name.' '. $m->last_name;
                $row['address'] = $member_address;
                $row['for_address'] = $member_for_address;
                $row['change'] = 'No Change';
                $row['effective_date'] = $m->date_of_appointmenta;
                $row['occupation'] = $m->occupation;
                $row['nationality'] = $nationality;
                $row['is_srilankan'] = $m->is_srilankan;
    
                if($this->settings($m->designation_type,'id')->key == 'DERECTOR')  {
                    $row['stakeholder_type'] = 'Director';
    
                    $director_records[] = $row;
                }
                if($this->settings($m->designation_type,'id')->key == 'SECRETARY')  {
                    $row['stakeholder_type'] = 'Power of Attorney Individual';
                    $secretory_records[] = $row;
                }
            }

        }
       
                    

        $changes_count = CompanyItemChange::where('request_id',$request_id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->count();
        

        if($changes_count){

            $changes = CompanyItemChange::where('request_id',$request_id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->get();
            foreach($changes  as $change ) {

                $member_id = $change->item_id;
                $effective_date = $change->change_or_effective_date;
                $change_type = $this->settings($change->changes_type,'id')->key;

                if($change_type == 'EDIT') {
                  //  $change_type = 'Modified';
                    $change_type = ( $change->old_record_id ) ? 'Modified' : 'Appointed';
                }
                if($change_type == 'ADD') {
                    $change_type = 'Appointed';
                }
                if($change_type == 'DELETE') {
                    $change_type = 'Resigned';
                }

                $member_info = CompanyFirms::where('id', $member_id)->first();
                $member_address_id = $member_info->address_id;
                $member_address = Address::where('id', $member_address_id)->first();
                $member_for_address = '';
              
                $row = array();
                $row['full_name'] = $member_info->name;
                $row['address'] = $member_address;
                $row['for_address'] = $member_for_address;
                $row['change'] = $change_type;
                $row['effective_date'] = $effective_date;
                $row['stakeholder_type'] = 'Power of Attorney firm';
                $row['occupation'] = '';
                $row['nationality'] = isset($member_address->country) ? $member_address->country : '';
                $row['is_srilankan'] = $member_info->is_srilankan;

                if($this->settings($member_info->type_id,'id')->key == 'SECRETARY')  {
                    $secretory_records[] = $row;
                }

            }

           

        }


        $mem_firms = CompanyFirms::where('company_id', $company_id)
        ->where('status', 1)
        ->where('type_id', $this->settings('SECRETARY','key')->id)
        ->get();
        if(isset($mem_firms[0]->id)) {
            foreach($mem_firms as $m  ) {
             

                $has_edit_changes = CompanyItemChange::where('request_id',$request_id)
                        ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                        ->where('changes_type', $this->settings('EDIT','key')->id)
                        ->where('old_record_id', $m->id )
                        ->count();
               $has_delete_changes = CompanyItemChange::where('request_id',$request_id)
                        ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                        ->where('changes_type', $this->settings('DELETE','key')->id)
                        ->where('item_id', $m->id )
                        ->count();
                if($has_edit_changes || $has_delete_changes ) {
                    continue;
                }


                if($has_changes) {
                    continue;
                }
                $member_address_id = $m->address_id;
                $member_address = Address::where('id', $member_address_id)->first();
                $member_for_address = '';
    
                $row = array();
                $row['full_name'] = $m->name;
                $row['address'] = $member_address;
                $row['for_address'] = $member_for_address;
                $row['change'] = 'No Change';
                $row['effective_date'] = $m->date_of_appointment;
                $row['occupation'] = 'Secretory';
                $row['stakeholder_type'] = 'Power of Attorney Individual';
                $row['nationality'] = isset($member_address->country) ? $member_address->country : '';
                $row['is_srilankan'] = $m->is_srilankan;
                $secretory_records[] = $row;

            }

        }


        return array(
            $director_records,
            $secretory_records
        );
       

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
            'COMPANY_STATUS_APPROVED',
            'COMPANY_FOREIGN_STATUS_APPROVED'
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
        $this->cleanUpforAlterOption($request->companyId);

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

        $alterRecord = OffshoreAlteration::where('company_id', $request->companyId)
                                            ->where('request_id', $request_id)
                                            ->first();
       

        $alterTypes = explode(',', $alterRecord->alteration_type);

        $companyType = $this->settings($company_info->type_id,'id');

        $open_company_address = false;
        $open_company_for_address = false;
        $company_for_address = null;

        if($company_info->address_id ){
            $company_address = Address::where('id',$company_info->address_id)->first();
            $open_company_address = $this->localAddressOpenStatus($company_address);
        }

        $request_address_item = CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
            ->first();
        $request_address = null;
        $request_address_change_date = '';
        if(isset($request_address_item->item_id) && $request_address_item->item_id ) {
            $request_address = Address::where('id', $request_address_item->item_id)->first();
            $request_address_change_date = $request_address_item->change_or_effective_date;
        }
        $has_request_address = CompanyItemChange::where('request_id',$request_id)
        ->where('changes_type', $this->settings('ADD','key')->id)
        ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
        ->count();
        

        if($company_info->foreign_address_id) {
            $company_for_address = Address::where('id',$company_info->foreign_address_id)->first();
            $open_company_for_address = $this->foriegnAddressOpenStatus($company_for_address);
        } else {
            $open_company_for_address = true;
        }

        $request_for_address_item = CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id)
            ->first();
        $request_for_address = null;
        $request_for_address_change_date = '';
        if(isset($request_for_address_item->item_id) && $request_for_address_item->item_id) {
                $request_for_address = Address::where('id', $request_for_address_item->item_id)->first();
                $request_for_address_change_date = $request_for_address_item->change_or_effective_date;
        }
        $has_request_for_address = CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id)
            ->count();

        
       $director_as_sec_count = 0;
       $director_as_sh_count =0;
       $dir_count = 0;
       $sec_count =0;
       $sh_count = 0;
       $sh_firm_count = 0;
       $sec_firm_count =0;
       $sec_sh_comes_from_director = false;
        /******director list *****/
        
        $director_list_count = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->count();
        $directors_already_exists = true;
        
        
        $directors = array();
        if($director_list_count){
            $director_list = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->get();
            $directors_already_exists = true;
        
            foreach($director_list as $director){

                $has_remove_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('DELETE','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $director->id)
                    ->count();


                if($has_remove_record){
                    continue;
                }

                $has_edit_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('old_record_id', $director->id)
                    ->count();
                if($has_edit_record){
                        continue;
                }
                
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
                $directors_as_sh = 0;                      

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
                    'passport'  => $director->passport_no,
                    'country'  => @( $director->foreign_address_id)  ? @$forAddress->country : @$address->country,
                    'passport_issued_country'   => $director->passport_issued_country,
                    'date'      => '1970-01-01' == $director->date_of_appointment ? null : $director->date_of_appointment,
                    'phone' => $director->telephone,
                    'mobile' => $director->mobile,
                    'email' => $director->email,
                    'occupation' => $director->occupation,
                    'other_relevent' => $director->other_relevent,
                    'directors_as_sec' =>$directors_as_sec,
                    'directors_as_sh' => $directors_as_sh,
                    'can_director_as_sec' => $can_director_as_sec,
                    'secRegDate' => $sec_reg_no
                
                );
                $directors[] = $rec;
            }

       }


       $changed_directors = array();
       $changed_director_list_count = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',$this->settings('OFFSHORE_ALTERATIONS','key')->id)
                                       ->count();
        if($changed_director_list_count){
            $director_list = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',$this->settings('OFFSHORE_ALTERATIONS','key')->id)
                                       ->get();
        
            foreach($director_list as $director){


                $existing_record= CompanyItemChange::where('request_id',$request_id)
                    ->whereIn('changes_type', array($this->settings('ADD','key')->id, $this->settings('EDIT','key')->id) )
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $director->id)
                    ->first();

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
                $directors_as_sh = 0;                      

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
                    'passport'  => $director->passport_no,
                    'country'  => @( $director->foreign_address_id)  ? @$forAddress->country : @$address->country,
                    'passport_issued_country'   => $director->passport_issued_country,
                    'date'      => '1970-01-01' == $director->date_of_appointment ? null : $director->date_of_appointment,
                    'changedate' => isset($existing_record->change_or_effective_date) ? $existing_record->change_or_effective_date : null,
                    'phone' => $director->telephone,
                    'mobile' => $director->mobile,
                    'email' => $director->email,
                    'occupation' => $director->occupation,
                    'other_relevent' => $director->other_relevent,
                    'directors_as_sec' =>$directors_as_sec,
                    'directors_as_sh' => $directors_as_sh,
                    'can_director_as_sec' => $can_director_as_sec,
                    'secRegDate' => $sec_reg_no,
                    'existing_record_id' => isset($existing_record->old_record_id) && $existing_record->old_record_id ?  $existing_record->old_record_id : null
                
                );
                $changed_directors[] = $rec;
            }

       }
        

        $secs_already_exists = true;
        /******secretory firms list *****/
        $sec_firm_list_count = CompanyFirms::where('company_id',$request->companyId)
        ->where('type_id',$this->settings('SECRETARY','key')->id)
        ->where('status',1)
        ->count();
        $secs_firms = array();
 
        if($sec_firm_list_count){
            $sec_list = CompanyFirms::where('company_id',$request->companyId)
                                    ->where('type_id',$this->settings('SECRETARY','key')->id)
                                    ->where('status',1)
                                    ->get();


            foreach($sec_list as $sec){

                $has_remove_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('DELETE','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->where('item_id', $sec->id)
                    ->count();


                if($has_remove_record){
                    continue;
                }

                $has_edit_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->where('old_record_id', $sec->id)
                    ->count();
                if($has_edit_record){
                        continue;
                }
        
                $sec_as_sh_count =  ( intval( $sec->sh_firm_of ) > 0 )  ? 1 : 0 ;
                $sec_firm_count++;

                $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;

                if(!$sec->foreign_address_id){
                    $address = Address::where('id',$address_id)->first();
                }else{
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
                'other_relevent' => $sec->other_relevent,
                'sec_as_sh' => $sec_as_sh_count,
                'secType' => 'firm',
                'secBenifList' => array(
                    'ben' => array()
                )
                );
                $secs_firms[] = $rec;
            }

        }

         /******change secretory firms list *****/
         $sec_firm_list_count = CompanyFirms::where('company_id',$request->companyId)
         ->where('type_id',$this->settings('SECRETARY','key')->id)
         ->where('status',$this->settings('OFFSHORE_ALTERATIONS','key')->id)
         ->count();
         $change_secs_firms = array();
  
         if($sec_firm_list_count){
             $sec_list = CompanyFirms::where('company_id',$request->companyId)
                                     ->where('type_id',$this->settings('SECRETARY','key')->id)
                                     ->where('status',$this->settings('OFFSHORE_ALTERATIONS','key')->id)
                                     ->get();
 
 
            foreach($sec_list as $sec){
    
                    $existing_record = CompanyItemChange::where('request_id',$request_id)
                    ->whereIn('changes_type', array($this->settings('ADD','key')->id, $this->settings('EDIT','key')->id) )
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->where('item_id', $sec->id)
                    ->first();
            
                    $sec_as_sh_count =  ( intval( $sec->sh_firm_of ) > 0 )  ? 1 : 0 ;
                    $sec_firm_count++;
    
                    $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;
    
                    if(!$sec->foreign_address_id){
                        $address = Address::where('id',$address_id)->first();
                    }else{
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
                    'changedate' => isset($existing_record->change_or_effective_date) ? $existing_record->change_or_effective_date : null,
                    'other_relevent' => $sec->other_relevent,
                    'sec_as_sh' => $sec_as_sh_count,
                    'secType' => 'firm',
                    'existing_record_id' => isset($existing_record->old_record_id) && $existing_record->old_record_id ?  $existing_record->old_record_id : null,
                    'secBenifList' => array(
                        'ben' => array()
                    )
                    );
                    $change_secs_firms[] = $rec;
            }
 
         }

        /******secretory list *****/
        $secs = array();
        $sec_list_count = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                ->where('status',1)
                ->count();
        if($sec_list_count){
            $sec_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                ->where('status',1)
                ->get();

            foreach($sec_list as $sec){

                $has_remove_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('DELETE','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $sec->id)
                    ->count();


                if($has_remove_record){
                    continue;
                }

                $has_edit_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('old_record_id', $sec->id)
                    ->count();
                if($has_edit_record){
                        continue;
                }
        
                $sec_nic_or_pass = ($sec->is_srilankan  =='yes') ? $sec->nic : $sec->passport_no;
                $sec_nic_or_pass_field = ($sec->is_srilankan  =='yes') ? 'nic' : 'passport_no';
            
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
                    'passport'  => $sec->passport_no,
                    'country'  => @( $sec->foreign_address_id && isset( $forAddress->country) )  ? @$forAddress->country : @$address->country,
                    'passport_issued_country'   => $sec->passport_issued_country,
                    'date'      => '1970-01-01' == $sec->date_of_appointment ? null : $sec->date_of_appointment,
                    'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
                    'regDate'      => ($sec->is_registered_secretary =='yes' || $companyType->key =='COMPANY_TYPE_PUBLIC' ) ? $sec->secretary_registration_no :'',
                    'phone' => $sec->telephone,
                    'mobile' => $sec->mobile,
                    'email' => $sec->email,
                    'occupation' => $sec->occupation,
                    'other_relevent' => $sec->other_relevent,
                    'secType' => 'natural',
                    'secCompanyFirmId' => $sec->company_member_firm_id,
                    'sec_as_sh' => 0,
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
              
        }

        /*******change secs */
        $change_secs = array();
        $sec_list_count = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                ->where('status',$this->settings('OFFSHORE_ALTERATIONS','key')->id)
                ->count();
        if($sec_list_count){
            $sec_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                ->where('status',$this->settings('OFFSHORE_ALTERATIONS','key')->id)
                ->get();

            foreach($sec_list as $sec){

                $existing_record = CompanyItemChange::where('request_id',$request_id)
                ->whereIn('changes_type', array($this->settings('ADD','key')->id, $this->settings('EDIT','key')->id) )
                ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                ->where('item_id', $sec->id)
                ->first();
        
                $sec_nic_or_pass = ($sec->is_srilankan  =='yes') ? $sec->nic : $sec->passport_no;
                $sec_nic_or_pass_field = ($sec->is_srilankan  =='yes') ? 'nic' : 'passport_no';
            
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
                    'passport'  => $sec->passport_no,
                    'country'  => @( $sec->foreign_address_id && isset( $forAddress->country) )  ? @$forAddress->country : @$address->country,
                    'passport_issued_country'   => $sec->passport_issued_country,
                    'date'      => '1970-01-01' == $sec->date_of_appointment ? null : $sec->date_of_appointment,
                    'changedate' => isset($existing_record->change_or_effective_date) ? $existing_record->change_or_effective_date : null,
                    'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
                    'regDate'      => ($sec->is_registered_secretary =='yes' || $companyType->key =='COMPANY_TYPE_PUBLIC' ) ? $sec->secretary_registration_no :'',
                    'phone' => $sec->telephone,
                    'mobile' => $sec->mobile,
                    'email' => $sec->email,
                    'occupation' => $sec->occupation,
                    'other_relevent' => $sec->other_relevent,
                    'secType' => 'natural',
                    'secCompanyFirmId' => $sec->company_member_firm_id,
                    'sec_as_sh' => 0,
                    'sec_sh_comes_from_director' => $sec_sh_comes_from_director,
                    'firm_info' =>$firm_info,
                    'pvNumber' => ($sec->company_member_firm_id) ? $firm_info['registration_no'] : '',
                    'firm_name' => ($sec->company_member_firm_id) ? $firm_info['name'] : '',
                    'firm_province' => ($sec->company_member_firm_id) ? $firm_address['province'] : '',
                    'firm_district' => ($sec->company_member_firm_id) ? $firm_address['district'] : '',
                    'firm_city' => ($sec->company_member_firm_id) ? $firm_address['city'] : '',
                    'firm_localAddress1' => ($sec->company_member_firm_id) ? $firm_address['address1'] : '',
                    'firm_localAddress2' => ($sec->company_member_firm_id) ? $firm_address['address2'] : '',
                    'firm_postcode' => ($sec->company_member_firm_id) ? $firm_address['postcode'] : '',
                    'existing_record_id' => isset($existing_record->old_record_id) && $existing_record->old_record_id ?  $existing_record->old_record_id : null
            
                );
                $change_secs[] = $rec;
            }
              
        }


        /*****end change secs */



        $secs_already_exists = ( $sec_firm_list_count || $sec_list_count );

        $shareholders = array();
        $shareholderFirms = array();
        $shareholders_inactive = array();
        $shareholderFirms_inactive = array();
       

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

        $shareRegisters = array();
        $annualRecords = array();
        $annualAuditors = array();
        $annualCharges = array();
        $shareRecords = array();

        

        /*****security checkpoint - check company status */
    

        $external_global_comment = '';


        $form_23 = Documents::where('key', 'FORM_23')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

                    
        $resubmit_doc = CompanyDocumentStatus::where('company_document_id', $form_23->id )
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

        $latest_name_change = ChangeName::where('old_company_id',$request->companyId )
                                        ->where('change_type', $this->settings('NAME_CHANGE','key')->id)
                                        ->where('status', $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id)
                                        ->first();

        if(isset($latest_name_change->id)) {
            $latest_name_change_record = array(
                    'old_type_id' => isset($latest_name_change->old_type_id) && $latest_name_change->old_type_id ?  $latest_name_change->old_type_id : null,
                    'old_postfix' => isset($latest_name_change->old_postfix) && $latest_name_change->old_postfix ? $latest_name_change->old_postfix : '',
                    'oldName' => isset($latest_name_change->old_name) && $latest_name_change->old_name ? $latest_name_change->old_name : '',
            );

            $annual_return_update_data = array(
                'former_name_of_company' =>  $latest_name_change_record['oldName'].' '.$latest_name_change_record['old_postfix']
            );
            AnnualReturn::where('company_id', $request->companyId)
            ->where('request_id',$request_id)
            ->update($annual_return_update_data);
        } else {
            $latest_name_change_record = array(
                'old_type_id' =>  null,
                'old_postfix' =>  '',
                'oldName' => '',
        );
        }


        $alter_options_group = SettingType::where('key', 'FOREIGN_ALTERATIONS_TYPES')->first();
        $alter_options = Setting::where('setting_type_id', $alter_options_group->id )->get();
        $alter_option_array = array();


        foreach($alter_options as $option ) {

                 $row = array(
                     'key' =>$option->key,
                     'value' => $option->value,
                     'isSelected' =>  in_array($option->key, $alterTypes)
                 );
                $alter_option_array[] = $row; 
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


        $doc_earliest_date = '';
        $date_arr = array();
        if( $alterRecord ->charter_change_date) {
            $date_arr[] = strtotime($alterRecord->charter_change_date);
        }
        if( $alterRecord ->memorandum_change_date) {
            $date_arr[] = strtotime($alterRecord->memorandum_change_date);
        }
        if( $alterRecord ->article_change_date) {
            $date_arr[] = strtotime($alterRecord->article_change_date);
        }
        if( $alterRecord ->statute_change_date) {
            $date_arr[] = strtotime($alterRecord->statute_change_date);
        }

        if(count($date_arr)){
            $doc_earliest_date  = date('Y-m-d', min($date_arr));
        }


        $stakeholder_store =  $this->stakeholder_store($request->companyId);

        $files_for_upload = $this->files_for_upload_docs($request->companyId);


    return response()->json([
            'message' => 'Data is successfully loaded.',
            'status' =>true,
            'data'   => array(
                'createrValid' => true,  
                'companyInfo'  => $company_info,
                'certificate_no' => $certificate_no,
                'latest_name_change' => $latest_name_change_record,
                'request_id'     => ($request_id) ? $request_id : null,
                'processStatus' => $this->settings($company_info->status,'id')->key,
                'companyAddress' => $company_address,
                'requestAddress' => $request_address,
                'request_address_change_date' => $request_address_change_date,
                'has_request_address' => $has_request_address,
                'open_company_cdp_dropdowns' => $open_company_address,
                'companyForAddress' => $company_for_address,
                'requestForAddress' => $request_for_address,
                'request_for_address_change_date' => $request_for_address_change_date,
                'has_request_for_address' => $has_request_for_address,
                'open_company_for_address'=> $open_company_for_address,
                'companyType'    =>$companyType,
                'countries'     => $countries_cache,
                'loginUser'     => $userPeople,
                'loginUserAddress'=> $userAddress,
                'directors' => $directors,
                'directors_already_exists' => $directors_already_exists,
                'changed_directors' => $changed_directors,
                'secs' => $secs,
                'change_secs' =>$change_secs,
                'secs_firms' => $secs_firms,
                'change_secs_firms' => $change_secs_firms,
                'share_register' => $shareRegisters,
                'share_register_already_exists' => false,
                'share_records' => $shareRecords,
                'share_records_already_exists' => false,
                'annual_records' => $annualRecords,
                'annual_records_already_exists' => false,
                'annual_charges' => $annualCharges,
                'annual_charges_already_exists' => false,
                'annual_auditors' => $annualAuditors,
                'annual_auditors_already_exists' => false,
                'secs_already_exists' => $secs_already_exists,
                'shareholders' => $shareholders,
                'shareholderFirms' => $shareholderFirms,
                'sh_already_exists' => false,
                'shareholders_inactive' => $shareholders_inactive,
                'shareholderFirms_inactive' => $shareholderFirms_inactive,
                'sh_inactive_already_exists' => false,
                'public_path' =>  storage_path(),
                'postfix' => $company_info->postfix,
                'postfix_si' => $postfix_values['postfix_si'],
                'postfix_ta' => $postfix_values['postfix_ta'],
                
                'amount_calls_recieved' => '',
                'amount_calls_unpaid' => '',
                'amount_calls_forfeited' => '',
                'amount_calls_purchased' => '',
                'amount_calls_redeemed' => '',
                'resolution_date' => '',
                'annual_return_status' => $this->settings($alterRecord->status,'id')->key, 
                'other_doc_change_date' => $alterRecord->other_doc_change_date,
                'charter_change_date' => $alterRecord->charter_change_date,
                'memorandum_change_date' => $alterRecord->memorandum_change_date,
                'article_change_date' => $alterRecord->article_change_date,
                'statute_change_date' => $alterRecord->statute_change_date,


                'example_shareholder_bulk_data' => asset('other/annual-return-shareholder-upload.csv'),
                'shareholder_bulk_format' => asset('other/annual-return-shareholder-upload.xlsx'),
                'example_member_bulk_data' => asset('other/annual-return-member-upload.csv'),
                'member_bulk_format' => asset('other/annual-return-member-upload.xlsx'),
                'example_ceased_shareholder_bulk_data' => asset('other/annual-return-ceased-shareholder-upload-example-data.csv'),
                'ceased_shareholder_bulk_format' => asset('other/annual-return-ceased-shareholder-upload-format.xlsx'),
                'example_ceased_member_bulk_data' => asset('other/annual-return-ceased-member-upload-example-data.csv'),
                'ceased_member_bulk_format' => asset('other/annual-return-ceased-member-upload-format.xlsx'),
                'dates'  =>  $this->get_annual_return_dates($request->companyId),
                'alter_options' => $alter_option_array,
                'court_data' => $court_data_arr,
                'alterType' => $alterTypes,
                'penalty_value' => $this->getPanaltyCharge($request->companyId, $request_id),

                'downloadDocs' => $this->generate_annual_return_report($request->companyId,array(

                    'company_info' => $company_info,
                    'certificate_no' => $certificate_no,
                    'latest_name_change' => $latest_name_change_record,
                    'companyType' => $this->settings($company_info->type_id,'id'),
                    'loginUser'     => $userPeople,
                    'loginUserAddress'=> $userAddress,
                    'company_address' => $company_address,
                    'request_address' => $request_address,
                    'request_address_change_date' => $request_address_change_date,
                    'has_request_address' => $has_request_address,
                    'company_for_address' => $company_for_address,
                    'request_for_address' => $request_for_address,
                    'request_for_address_change_date' => $request_for_address_change_date,
                    'has_request_for_address' => $has_request_for_address,

                    'stakeholder_changes' => $this->get_director_changes($request_id,$request->companyId),
                    'postfix' => $company_info->postfix,
                    'postfix_si' => $postfix_values['postfix_si'],
                    'postfix_ta' => $postfix_values['postfix_ta'],
                    'request_address_change_date' => $request_address_change_date,
                    'request_for_address_change_date' => $request_for_address_change_date,
                    'other_doc_change_date' => $doc_earliest_date,

                    'charter_change_date' => $alterRecord->charter_change_date,
                    'memorandum_change_date' => $alterRecord->memorandum_change_date,
                    'article_change_date' => $alterRecord->article_change_date,
                    'statute_change_date' => $alterRecord->statute_change_date,


                    'date_of_record' => $alterRecord->date_of,
                    'directors' =>  $stakeholder_store['directors'],
                    'secs' => $stakeholder_store['secs'],
                    'secFirms' => $stakeholder_store['secFirms']
                    
                )),
                'coreShareGroups' => $core_groups_list,
                'uploadDocs'   => $files_for_upload,
                'otherUploadDocs' => $this->files_for_upload_other_docs($request->companyId),
                'additionalDocs' => $this->files_for_additional_docs($request->companyId),
                'external_global_comment' => $external_global_comment,
                'form15_payment' => $this->settings('PAYMENT_FORM23','key')->value,
                'form45_payment' => $this->settings('PAYMENT_OFFSHORE_FORM45','key')->value,
                'form46_payment' => $this->settings('PAYMENT_OFFSHORE_FORM46','key')->value,
               // 'form45_payment_enable' => $files_for_upload['enable_45'],
               // 'form46_payment_enable' => $files_for_upload['enable_46'],
                'form45_payment_enable' => false,
                'form46_payment_enable' => false,
                'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                )
        ], 200);
          
    }

    private function cleanUpforAlterOption($company_id) {

        $request_id = $this->valid_annual_return_request_operation($company_id);
        $record = OffshoreAlteration::where('request_id', $request_id)->first();

        $alterTypes = explode(',', $record->alteration_type);


        if( !in_array('FOREIGN_ARTICLE_CHANGES', $alterTypes) ) {
            $form_35_charter = Documents::where('key', 'FORM_23_CHARTER')->first();
            $form_35_statute = Documents::where('key', 'FORM_23_STATUTE')->first();
            $form_35_memo = Documents::where('key', 'FORM_23_MEMORANDUM')->first();
            $form_35_articls = Documents::where('key', 'FORM_23_ARTICLES')->first();

            $query = CompanyDocuments::query();
            $query->where('company_id', $company_id );
            $query->where('request_id', $request_id);
            $query->whereIn('document_id',array($form_35_charter->id, $form_35_statute->id, $form_35_memo->id, $form_35_articls->id ));
            $query->delete();

            $update_rec =  array(
                'charter_change_date'    => null,
                'memorandum_change_date'    => null,
                'article_change_date'    => null,
                'statute_change_date'    => null
            );
            $update = OffshoreAlteration::where('request_id', $request_id)->update($update_rec);
        }
        
        if(!in_array('FOREIGN_BUSINESS_ADDRESS_CHANGES', $alterTypes)) {
            $address_request =  CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
            ->first();

            if(isset($address_request->item_id) && $address_request->item_id ) {

                Address::where('id',$address_request->item_id)->delete();

                CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('ADD','key')->id)
                ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                ->delete();

                

            }
        }
        if(  !in_array('FOREIGN_REGISTER_ADDRESS_CHANGES', $alterTypes)) {
            $address_request =  CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id)
            ->first();

            if(isset($address_request->item_id) && $address_request->item_id ) {

                Address::where('id',$address_request->item_id)->delete();

                CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('ADD','key')->id)
                ->where('item_table_type', $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id)
                ->delete();
            }

        }

        if(  !in_array('FOREIGN_DIRECTOR_CHANGES', $alterTypes)) {


            $changeDirectors = CompanyMember::where('company_id', $company_id)
            ->where('designation_type',$this->settings('DERECTOR','key')->id)
            ->where('status', $this->settings('OFFSHORE_ALTERATIONS','key')->id)
            ->get();
            if( isset($changeDirectors[0]->id) && $changeDirectors[0]->id ){
                foreach($changeDirectors as $d ) {

                    if($d->address_id ) {
                        Address::where('id', $d->address_id )->delete();
                    }
                    if($d->foreign_address_id ) {
                        Address::where('id', $d->foreign_address_id )->delete();
                    }
                   

                   CompanyItemChange::where('request_id',$request_id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $d->id)
                    ->delete();

                    CompanyMember::where('id',  $d->id)->delete();
                }
            }


            $exisiting_directos = CompanyMember::where('company_id', $company_id)
            ->where('designation_type',$this->settings('DERECTOR','key')->id)
            ->where('status', 1)
            ->get();
            foreach($exisiting_directos as $d ) {
                CompanyItemChange::where('request_id',$request_id)
                ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                ->where('item_id', $d->id)
                ->where('changes_type', $this->settings('DELETE','key')->id)
                ->delete();
            }



        }


        if(  !in_array('FOREIGN_POWER_OF_ATTORNEY_CHANGES', $alterTypes)) {

            $changeSecs = CompanyMember::where('company_id', $company_id)
            ->where('designation_type',$this->settings('SECRETARY','key')->id)
            ->where('status', $this->settings('OFFSHORE_ALTERATIONS','key')->id)
            ->get();
            if( isset($changeSecs[0]->id) && $changeSecs[0]->id ){
                foreach($changeSecs as $d ) {

                    if($d->address_id ) {
                        Address::where('id', $d->address_id )->delete();
                    }
                    if($d->foreign_address_id ) {
                        Address::where('id', $d->foreign_address_id )->delete();
                    }
                   

                   CompanyItemChange::where('request_id',$request_id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $d->id)
                    ->delete();

                    CompanyMember::where('id',  $d->id)->delete();
                }
            }

            $existingSecs = CompanyMember::where('company_id', $company_id)
            ->where('designation_type',$this->settings('SECRETARY','key')->id)
            ->where('status', 1)
            ->get();
            foreach($existingSecs as $d ) {

                CompanyItemChange::where('request_id',$request_id)
                ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                ->where('item_id', $d->id)
                ->where('changes_type', $this->settings('DELETE','key')->id)
                ->delete();
            }



            $changeSecFirms = CompanyFirms::where('company_id', $company_id)
            ->where('type_id',$this->settings('SECRETARY','key')->id)
            ->where('status', $this->settings('OFFSHORE_ALTERATIONS','key')->id)
            ->get();
            if( isset($changeSecFirms[0]->id) && $changeSecFirms[0]->id ){
                foreach($changeSecFirms as $d ) {

                    if($d->address_id ) {
                        Address::where('id', $d->address_id )->delete();
                    }
                   

                   CompanyItemChange::where('request_id',$request_id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->where('item_id', $d->id)
                    ->delete();

                    CompanyFirms::where('id',  $d->id)->delete();
                }
            }

            $existingSecFirms = CompanyFirms::where('company_id', $company_id)
            ->where('type_id',$this->settings('SECRETARY','key')->id)
            ->where('status', 1)
            ->get();

            foreach($existingSecFirms as $d ) {
                CompanyItemChange::where('request_id',$request_id)
                ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                ->where('item_id', $d->id)
                ->where('changes_type', $this->settings('DELETE','key')->id)
                ->delete();
            }


        }

    }


    public function updateAlterationType(Request $request){

        $company_id = $request->company_id;
        $request_id = $this->valid_annual_return_request_operation($company_id);
        $alter_type = is_array($request->alter_type) ? $request->alter_type : array();

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
              
            ], 200);

             exit();

        }

        $alteration = OffshoreAlteration::where('request_id', $request_id)->first();
        $prev_alterations = $alteration->alteration_type;

        if($prev_alterations != implode(',', $alter_type)) {
            //remove all the documents uploaded
            $form_23 = Documents::where('key', 'FORM_23')->first();
            $form_45 = Documents::where('key', 'OFFSHORE_FORM45')->first();
            $form_46 = Documents::where('key', 'OFFSHORE_FORM46')->first();
            $form_other_docs = Documents::where('key', 'ALTERATIONS_OF_OFFSHORE_OTHER_DOCUMENTS')->first();
          
           CompanyDocuments::where('company_id', $company_id)
            ->where('request_id',$request_id)
            ->where('document_id', $form_23->id )
            ->delete();
            CompanyDocuments::where('company_id', $company_id)
            ->where('request_id',$request_id)
            ->where('document_id', $form_45->id )
            ->delete();
            CompanyDocuments::where('company_id', $company_id)
            ->where('request_id',$request_id)
            ->where('document_id', $form_46->id )
            ->delete();
            CompanyDocuments::where('company_id', $company_id)
            ->where('document_id', $form_other_docs->id )
            ->where('request_id', $request_id)
             ->delete();
        }


        $update_rec =  array(
            'alteration_type'    => implode(',', $alter_type)
        );
        $update = OffshoreAlteration::where('request_id', $request_id)->update($update_rec);
       
        if($update) {
            return response()->json([
    
                'status' =>true,
                'request_id'   => $request_id,
            ], 200);
    
        }else {
            return response()->json([  
                'status' =>false,
                'request_id'   => $request_id,
            ], 200);

        }
        
    }


    function updateExistingDirector( Request $request) {

        $director_id = $request->director_id;
        $company_id = $request->company_id;
        $director = $request->director;
        $type = $request->type;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $has_edit_director_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('old_record_id', $director_id)
                    ->count();
        
        //remove existing records first
        if($has_edit_director_record){

            $edit_director_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('old_record_id', $director_id)
                    ->first();


            CompanyMember::where('id',$edit_director_record->item_id)->delete();

            CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('EDIT','key')->id)
                ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                ->where('item_id', $edit_director_record->item_id)
                ->delete();


        } else {

            if( $type !='existing' ) {
                CompanyMember::where('id',$director_id)->delete();
            }


           

            CompanyItemChange::where('request_id',$request_id)
                ->whereIn('changes_type', array( $this->settings('EDIT','key')->id, $this->settings('ADD','key')->id) )
                ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                ->where('item_id', $director_id)
                ->delete();

        }


            $new_director_local_address_id= null;
            $new_director_foreign_address_id = null;
            
            if($director['province'] && $director['province'] &&  $director['district'] && $director['localAddress1'] && $director['postcode'] ) {
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
            $newDirector->other_relevent = isset($director['other_relevent']) ? $director['other_relevent'] : null;
            $newDirector->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
            $newDirector->status =  $this->settings('OFFSHORE_ALTERATIONS','key')->id;
            $newDirector->save();
            $new_director_id = $newDirector->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('EDIT','key')->id;
            $change->item_id = $new_director_id;
            $change->change_or_effective_date = (isset($director['changedate'])) ? date('Y-m-d',strtotime($director['changedate']) ) : null;
            $change->old_record_id = ($has_edit_director_record  || $type ==='existing' ) ? $director_id : null;
            $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;

            $change->save();
            $change_id = $change->id;


        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }

    }

    function removeExistingDirector(Request $request ){

        $director_id = $request->director_id;
        $company_id = $request->company_id;
        $reason = isset($request->reason_info) ? $request->reason_info : null;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $seased_reason = null;
        if( isset($reason['reason']) ) {

            if($reason['reason'] == 'Other') {
                $seased_reason = isset($reason['other_option_reason']) ? $reason['other_option_reason']   : null;
            } else {
                $seased_reason = $reason['reason'];
            }

        }

        $change = new CompanyItemChange;
        $change->request_id = $request_id;
        $change->changes_type = $this->settings('DELETE','key')->id;
        $change->item_id = $director_id;
        $change->change_or_effective_date = isset($reason['effective_date']) ? $reason['effective_date']   : date('Y-m-d', time());
        $change->ceased_reason = $seased_reason;
        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;

        $change->save();
        $change_id = $change->id;

        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }


    }

    function removeChangeDirector(Request $request ){

        $director_id = $request->director_id;
        $company_id = $request->company_id;
        $reason = isset($request->reason_info) ? $request->reason_info : null;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $record = CompanyItemChange::where('request_id',$request_id)
                    ->whereIn('changes_type', array( $this->settings('EDIT','key')->id , $this->settings('ADD','key')->id) )
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $director_id)
                    ->first();
        
        if( isset($record->id) && $record->id ) {

            CompanyMember::where('id', $record->item_id)->delete();

            CompanyItemChange::where('request_id',$request_id)
                    ->whereIn('changes_type', array( $this->settings('EDIT','key')->id , $this->settings('ADD','key')->id) )
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $record->item_id)
                    ->delete();


            if($record->old_record_id) {

                $seased_reason = null;
                if( isset($reason['reason']) ) {

                    if($reason['reason'] == 'Other') {
                        $seased_reason = isset($reason['other_option_reason']) ? $reason['other_option_reason']   : null;
                    } else {
                        $seased_reason = $reason['reason'];
                    }

                }

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('DELETE','key')->id;
                $change->item_id = $record->old_record_id;
                $change->change_or_effective_date = isset($reason['effective_date']) ? $reason['effective_date']   : date('Y-m-d', time());
                $change->ceased_reason = $seased_reason;
                $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;

                $change->save();
                $change_id = $change->id;

            }


        } 

        

        return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
        ], 200);

    

    }

    function addNewDirector( Request $request ){

        $company_id = $request->company_id;
        $director = $request->director;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        
        $new_director_local_address_id= null;
        $new_director_foreign_address_id = null;
            
        if($director['province'] && $director['province'] &&  $director['district'] && $director['localAddress1'] && $director['postcode'] ) {
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

          
        }

        $change_id = null;

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
        $newDirector->other_relevent = isset($director['other_relevent']) ? $director['other_relevent'] : null;
        $newDirector->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
        $newDirector->status =  $this->settings('OFFSHORE_ALTERATIONS','key')->id;
        $newDirector->save();
        $new_director_id = $newDirector->id;

        $change = new CompanyItemChange;
        $change->request_id = $request_id;
        $change->changes_type = $this->settings('ADD','key')->id;
        $change->item_id = $new_director_id;
        $change->change_or_effective_date = date('Y-m-d',strtotime($director['date']) );
        $change->old_record_id = null;
        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;

        $change->save();
        $change_id = $change->id;


        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }

    }

    function removeExistingSec(Request $request ){

        $director_id = $request->sec_id;
        $company_id = $request->company_id;
        $type = $request->type;
        $reason = isset($request->reason_info) ? $request->reason_info : null;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $seased_reason = null;
        if( isset($reason['reason']) ) {

            if($reason['reason'] == 'Other') {
                $seased_reason = isset($reason['other_option_reason']) ? $reason['other_option_reason']   : null;
            } else {
                $seased_reason = $reason['reason'];
            }

        }

        $change = new CompanyItemChange;
        $change->request_id = $request_id;
        $change->changes_type = $this->settings('DELETE','key')->id;
        $change->item_id = $director_id;
        $change->change_or_effective_date = isset($reason['effective_date']) ? $reason['effective_date']   : date('Y-m-d', time());
        $change->ceased_reason = $seased_reason;
        $change->item_table_type = ($type == 'secFirm') ?  $this->settings('COMPANY_MEMBER_FIRMS','key')->id :  $this->settings('COMPANY_MEMBERS','key')->id;

        $change->save();
        $change_id = $change->id;
        

        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }


    }

    function updateExistingSec( Request $request) {

        $sec_id = $request->sec_id;
        $company_id = $request->company_id;
        $sec = $request->sec;
        $type = $request->type;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $has_edit_sec_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('old_record_id', $sec_id)
                    ->count();
        
        //remove existing records first
        if($has_edit_sec_record){

            $edit_sec_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('old_record_id', $sec_id)
                    ->first();


            CompanyMember::where('id',$edit_sec_record->item_id)->delete();

            CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('EDIT','key')->id)
                ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                ->where('item_id', $edit_sec_record->item_id)
                ->delete();


        } else {

            if( $type !='existing' ) {
                CompanyMember::where('id',$sec_id)->delete();
            }

          

            CompanyItemChange::where('request_id',$request_id)
                ->whereIn('changes_type', array( $this->settings('EDIT','key')->id, $this->settings('ADD','key')->id) )
                ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                ->where('item_id', $sec_id)
                ->delete();

        }


            $new_director_local_address_id= null;
            $new_director_foreign_address_id = null;
            
            if($sec['province'] && $sec['province'] &&  $sec['district'] && $sec['localAddress1'] && $sec['postcode'] ) {
                $address = new Address;
                $address->province = $sec['province'];
                $address->district =  $sec['district'];
                $address->city =  $sec['city'];
                $address->address1 =  $sec['localAddress1'];
                $address->address2 =  $sec['localAddress2'];
                $address->postcode = $sec['postcode'];
                $address->country =   'Sri Lanka';
            
                $address->save();
                $new_director_local_address_id = $address->id;

               
            }

            if($sec['forProvince'] ||  $sec['forCity'] || $sec['forAddress1'] || $sec['forAddress2'] || $sec['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $sec['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $sec['forCity'];
             $forAddress->address1 =  $sec['forAddress1'];
             $forAddress->address2 =  $sec['forAddress2'];
             $forAddress->postcode = $sec['forPostcode'];
             $forAddress->country =  $sec['country'];
           
             $forAddress->save();
             $new_director_foreign_address_id = $forAddress->id;

          
            }

            $newSec = new CompanyMember;
            $newSec->company_id = $company_id;
            $newSec->designation_type = $this->settings('SECRETARY','key')->id;
            $newSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
            $newSec->first_name = $sec['firstname'];
            $newSec->last_name = $sec['lastname']; 
            $newSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
            $newSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
            $newSec->address_id = $new_director_local_address_id;
            $newSec->foreign_address_id = $new_director_foreign_address_id;
            $newSec->passport_issued_country = isset( $sec['passport_issued_country'] ) ? $sec['passport_issued_country']  : null ;
            $newSec->telephone = $sec['phone'];
            $newSec->mobile =$sec['mobile'];
            $newSec->email = $sec['email'];
            $newSec->occupation = $sec['occupation'];
            $newSec->other_relevent = isset($sec['other_relevent']) ? $sec['other_relevent'] : null;
            $newSec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
            $newSec->is_registered_secretary = ($sec['isReg'] == true ) ? 'yes' : 'no';
            $newSec->secretary_registration_no =  (isset($sec['regDate']) && $sec['regDate'] ) ? $sec['regDate'] : NULL;
            $newSec->status = $this->settings('OFFSHORE_ALTERATIONS','key')->id;
            $newSec->is_natural_person = $sec['secType'] =='natural' ? 'yes' : 'no';
            $newSec->save();
            $new_sec_id = $newSec->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('EDIT','key')->id;
            $change->item_id = $new_sec_id;
            $change->change_or_effective_date = (isset($sec['changedate'])) ? date('Y-m-d',strtotime($sec['changedate']) ) : null;
            $change->old_record_id = ($has_edit_sec_record || $type ==='existing') ? $sec_id : null;
            $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;

            $change->save();
            $change_id = $change->id;


        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }

    }

    function removeChangeSec(Request $request ){

        $sec_id = $request->sec_id;
        $company_id = $request->company_id;
        $type = $request->type;
        $reason = isset($request->reason_info) ? $request->reason_info : null;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $record = null;

        if($type == 'secFirm') {

            $record = CompanyItemChange::where('request_id',$request_id)
            ->whereIn('changes_type', array( $this->settings('EDIT','key')->id , $this->settings('ADD','key')->id) )
            ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
            ->where('item_id', $sec_id)
            ->first();

        } else {

            $record = CompanyItemChange::where('request_id',$request_id)
            ->whereIn('changes_type', array( $this->settings('EDIT','key')->id , $this->settings('ADD','key')->id) )
            ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
            ->where('item_id', $sec_id)
            ->first();

        }

       
        
        if( isset($record->id) && $record->id ) {


            if($type == 'secFirm') {

                CompanyFirms::where('id', $sec_id)->delete();

                CompanyItemChange::where('request_id',$request_id)
                    ->whereIn('changes_type', array( $this->settings('EDIT','key')->id , $this->settings('ADD','key')->id) )
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->where('item_id', $record->item_id)
                    ->delete();


            }else {

              
                CompanyMember::where('id', $sec_id)->delete();

                CompanyItemChange::where('request_id',$request_id)
                    ->whereIn('changes_type', array( $this->settings('EDIT','key')->id , $this->settings('ADD','key')->id) )
                    ->where('item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
                    ->where('item_id', $sec_id)
                    ->delete();

            }


            if($record->old_record_id) {

                $seased_reason = null;
                if( isset($reason['reason']) ) {

                    if($reason['reason'] == 'Other') {
                        $seased_reason = isset($reason['other_option_reason']) ? $reason['other_option_reason']   : null;
                    } else {
                        $seased_reason = $reason['reason'];
                    }

                }

                $change = new CompanyItemChange;
                $change->request_id = $request_id;
                $change->changes_type = $this->settings('DELETE','key')->id;
                $change->item_id = $record->old_record_id;
                $change->change_or_effective_date = isset($reason['effective_date']) ? $reason['effective_date']   : date('Y-m-d', time());
                $change->ceased_reason = $seased_reason;
                $change->item_table_type = ($type == 'secFirm') ?  $this->settings('COMPANY_MEMBER_FIRMS','key')->id : $this->settings('COMPANY_MEMBERS','key')->id;

                $change->save();
                $change_id = $change->id;

            }


        } 

        return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
        ], 200);

    

    }

    function updateExistingSecFirm( Request $request) {

        $sec_id = $request->sec_id;
        $company_id = $request->company_id;
        $sec = $request->sec;
        $type = $request->type;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        $has_edit_sec_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->where('old_record_id', $sec_id)
                    ->count();
        
        //remove existing records first
        if($has_edit_sec_record){

            $edit_sec_record = CompanyItemChange::where('request_id',$request_id)
                    ->where('changes_type', $this->settings('EDIT','key')->id)
                    ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                    ->where('old_record_id', $sec_id)
                    ->first();


            CompanyFirms::where('id',$edit_sec_record->item_id)->delete();

            CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('EDIT','key')->id)
                ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                ->where('item_id', $edit_sec_record->item_id)
                ->delete();


        } else {

            if( $type !='existing' ) {
                CompanyFirms::where('id',$sec_id)->delete();
            }

          

            CompanyItemChange::where('request_id',$request_id)
                ->whereIn('changes_type', array( $this->settings('EDIT','key')->id, $this->settings('ADD','key')->id) )
                ->where('item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
                ->where('item_id', $sec_id)
                ->delete();

        }


        $new_companyFirmAddressId= null;
 
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


        $newSec = new CompanyFirms;
        $newSec->email  = $sec['firm_email'];
        $newSec->mobile = $sec['firm_mobile'];
        $newSec->phone  = $sec['firm_phone'];
        $newSec->date_of_appointment = $sec['firm_date'];
        $newSec->other_relevent = isset($sec['other_relevent']) ? $sec['other_relevent'] : null;
        $newSec->company_id = $company_id;
        $newSec->address_id = $new_companyFirmAddressId;
        $newSec->type_id = $this->settings('SECRETARY','key')->id;
        $newSec->status =  $this->settings('OFFSHORE_ALTERATIONS','key')->id;
        $newSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
        $newSec->registration_no = $sec['pvNumber'];
        $newSec->name = $sec['firm_name'];
        $newSec->save();
        $new_sec_id = $newSec->id;


        $change = new CompanyItemChange;
        $change->request_id = $request_id;
        $change->changes_type = $this->settings('EDIT','key')->id;
        $change->item_id = $new_sec_id;
        $change->change_or_effective_date = (isset($sec['changedate'])) ? date('Y-m-d',strtotime($sec['changedate']) ) : null;
        $change->old_record_id = ($has_edit_sec_record || $type ==='existing') ? $sec_id : null;
        $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;

        $change->save();
        $change_id = $change->id;


        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }

    }


    function addNewSecretory( Request $request ){

        $company_id = $request->company_id;
        $sec = $request->sec;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        
        $new_sec_local_address_id= null;
        $new_sec_foreign_address_id = null;
            
        if($sec['province'] && $sec['province'] &&  $sec['district'] && $sec['localAddress1'] && $sec['postcode'] ) {
                $address = new Address;
                $address->province = $sec['province'];
                $address->district =  $sec['district'];
                $address->city =  $sec['city'];
                $address->address1 =  $sec['localAddress1'];
                $address->address2 =  $sec['localAddress2'];
                $address->postcode = $sec['postcode'];
                $address->country =   'Sri Lanka';
            
                $address->save();
                $new_sec_local_address_id = $address->id;

               
        }

        if( isset($sec['forProvince'] ) && $sec['forProvince'] &&
            isset($sec['forCity']) &&  $sec['forCity'] &&
            isset($sec['forAddress1']) && $sec['forAddress1'] &&
            isset($sec['forPostcode']) && $sec['forPostcode'] ) {
             $forAddress = new Address;
             $forAddress->province = $sec['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $sec['forCity'];
             $forAddress->address1 =  $sec['forAddress1'];
             $forAddress->address2 =  $sec['forAddress2'];
             $forAddress->postcode = $sec['forPostcode'];
             $forAddress->country =  $sec['country'];
           
             $forAddress->save();
             $new_sec_foreign_address_id = $forAddress->id;

        }

        $change_id = null;

        $newSec = new CompanyMember;
        $newSec->company_id = $company_id;
        $newSec->designation_type = $this->settings('SECRETARY','key')->id;
        $newSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
        $newSec->first_name = $sec['firstname'];
        $newSec->last_name = $sec['lastname']; 
        $newSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
        $newSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
        $newSec->address_id = $new_sec_local_address_id;
        $newSec->foreign_address_id = $new_sec_foreign_address_id;
        $newSec->passport_issued_country = isset( $sec['passport_issued_country'] ) ? $sec['passport_issued_country']  : null ;
        $newSec->telephone = $sec['phone'];
        $newSec->mobile =$sec['mobile'];
        $newSec->email = $sec['email'];
        $newSec->occupation = $sec['occupation'];
        $newSec->other_relevent = isset($sec['other_relevent']) ? $sec['other_relevent'] : null;
        $newSec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
        $newSec->is_registered_secretary = ($sec['isReg'] == true ) ? 'yes' : 'no';
        $newSec->secretary_registration_no =  (isset($sec['regDate']) && $sec['regDate'] ) ? $sec['regDate'] : NULL;
        $newSec->status = $this->settings('OFFSHORE_ALTERATIONS','key')->id;
        $newSec->is_natural_person = $sec['secType'] =='natural' ? 'yes' : 'no';
        $newSec->save();
        $new_sec_id = $newSec->id;

        $change = new CompanyItemChange;
        $change->request_id = $request_id;
        $change->changes_type = $this->settings('ADD','key')->id;
        $change->item_id = $new_sec_id;
        $change->change_or_effective_date = date('Y-m-d',strtotime($sec['date']) );
        $change->old_record_id = null;
        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;

        $change->save();
        $change_id = $change->id;

        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }

    }

    function addNewSecretoryFirm( Request $request ){

        $company_id = $request->company_id;
        $sec = $request->sec;

        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);
        }

        
        $new_companyFirmAddressId= null;
 
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


        $newSec = new CompanyFirms;
        $newSec->email  = $sec['firm_email'];
        $newSec->mobile = $sec['firm_mobile'];
        $newSec->phone  = $sec['firm_phone'];
        $newSec->date_of_appointment = $sec['firm_date'];
        $newSec->other_relevent = isset($sec['other_relevent']) ? $sec['other_relevent'] : '';
        $newSec->company_id = $company_id;
        $newSec->address_id = $new_companyFirmAddressId;
        $newSec->type_id = $this->settings('SECRETARY','key')->id;
        $newSec->status =  $this->settings('OFFSHORE_ALTERATIONS','key')->id;
        $newSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
        $newSec->registration_no = $sec['pvNumber'];
        $newSec->name = $sec['firm_name'];
        $newSec->save();
        $new_sec_id = $newSec->id;


        $change = new CompanyItemChange;
        $change->request_id = $request_id;
        $change->changes_type = $this->settings('ADD','key')->id;
        $change->item_id = $new_sec_id;
        $change->change_or_effective_date = date('Y-m-d',strtotime($sec['firm_date']) );
        $change->old_record_id = null;
        $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;

        $change->save();
        $change_id = $change->id;

        if($change_id) {

            return response()->json([
                'message' => 'data.',
                'status' =>true,
                'request_id'   => $request_id,
                'change_id'    => $change_id,
            ], 200);

        } else {

            return response()->json([
                'message' => 'data.',
                'status' =>false,
                'request_id'   => $request_id,
                'change_id'    => null,
            ], 200);

        }

    }

    public function updateOtherDocsChangeDate(Request $request){

        

        $company_id = $request->company_id;

      
        $request_id = $this->valid_annual_return_request_operation($company_id);


        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
              
            ], 200);

             exit();

        }

  

        $update_rec =  array(
            'charter_change_date'    => $request->charter_date,
            'memorandum_change_date'    => $request->memorandum_date,
            'article_change_date'    => $request->article_date,
            'statute_change_date'    => $request->statute_date
        );
        $update = OffshoreAlteration::where('request_id', $request_id)->update($update_rec);
       
        if($update) {

            return response()->json([
    
                'status' =>true,
                'request_id'   => $request_id,
            ], 200);
    

        }else {

            return response()->json([
              
                'status' =>false,
                'request_id'   => $request_id,
            ], 200);
    

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
            $this->settings('OFFSHORE_ALTERATIONS_APPROVED','key')->id,
            $this->settings('OFFSHORE_ALTERATIONS_REJECTED','key')->id
        );
        $annual_return_request_type =  $this->settings('OFFSHORE_ALTERATIONS','key')->id;

        $exist_request_id = $this->has_annual_record_for_this_year($company_id);

        if($exist_request_id) {

            

            $request_count = CompanyChangeRequestItem::where('request_type',$annual_return_request_type)
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
                $request->request_type = $annual_return_request_type;
                $request->status = $this->settings('OFFSHORE_ALTERATIONS_PROCESSING','key')->id;
                $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
                $request->save();
                $request_id =  $request->id;

                $record = new OffshoreAlteration;
                $record->company_id = $company_id;
                $record->date_of = date('Y-m-d',time());
                $record->request_id = $request_id;
                $record->status = $this->settings('OFFSHORE_ALTERATIONS_PROCESSING','key')->id;
                $record->save();
                $record_id =  $record->id;

                if($record_id && $request_id ) {
                    return $request_id;
                }else{
                    return false;
                }

        }
        
    }

    private function has_annual_record_for_this_year($company_id) {
        $accepted_request_statuses = array(
            $this->settings('OFFSHORE_ALTERATIONS_APPROVED','key')->id,
            $this->settings('OFFSHORE_ALTERATIONS_REJECTED','key')->id
        );
       
        $record_count = OffshoreAlteration::where('company_id', $company_id)
                                  ->whereNotIn('status', $accepted_request_statuses )
                                   ->count();
        if( $record_count === 1 ) {
            $record = OffshoreAlteration::where('company_id', $company_id)
            ->whereNotIn('status', $accepted_request_statuses )
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

        if(
            $request->address1 &&
            $request->city &&
            $request->district &&
            $request->province &&
            $request->postcode &&
            $request->gn_division
        ) {

           $address_request =  CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
            ->first();

            if(isset($address_request->item_id) && $address_request->item_id ) {

                Address::where('id',$address_request->item_id)->delete();

                CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('ADD','key')->id)
                ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
                ->delete();

                

            }

            $new_company_address = new Address;
            $new_company_address->address1 = $request->address1;
            $new_company_address->address2 = $request->address2;
            $new_company_address->city = $request->city;
            $new_company_address->district = $request->district;
            $new_company_address->province = $request->province;
            $new_company_address->gn_division = $request->gn_division;
            $new_company_address->postcode = $request->postcode;
            $new_company_address->country = 'Sri Lanka';

            $new_company_address->save();
            $new_company_address_id = $new_company_address->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_company_address_id;
            $change->change_or_effective_date = $request->oversease_alteration_address_change_date;
            $change->old_record_id = $addressId;
            $change->item_table_type = $this->settings('ADDRESSES_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

           
        }


        if(
            $request->forAddress1 &&
            $request->forAddress2 &&
            $request->forCity &&
            $request->forProvince &&
            $request->forCountry &&
            $request->forPostcode
        ) {

           $address_request =  CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id)
            ->first();

            if(isset($address_request->item_id) && $address_request->item_id ) {

                Address::where('id',$address_request->item_id)->delete();

                CompanyItemChange::where('request_id',$request_id)
                ->where('changes_type', $this->settings('ADD','key')->id)
                ->where('item_table_type', $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id)
                ->delete();
            }

            $new_company_address = new Address;
            $new_company_address->address1 = $request->forAddress1;
            $new_company_address->address2 = $request->forAddress2;
            $new_company_address->city = $request->forCity;
            $new_company_address->district = NULL;
            $new_company_address->province = $request->forProvince;
            $new_company_address->postcode = $request->forPostcode;
            $new_company_address->country = $request->forCountry;

            $new_company_address->save();
            $new_company_address_id = $new_company_address->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_company_address_id;
            $change->old_record_id = $forAddressId;
            $change->change_or_effective_date = $request->oversease_alteration_for_address_change_date;
            $change->item_table_type = $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id;

            $change->save();
            $change_id = $change->id;

           
        }

       

        
        return response()->json([
            'message' => 'data.',
            'status' =>true,
            'request_id'   => $request_id,
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

        $director_count = CompanyMember::where('company_id',$company_id)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->count();
        if($director_count) {
            foreach($request->directors['directors'] as $director ){

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
            $newDirector->other_relevent = isset($director['other_relevent']) ? $director['other_relevent'] : null;
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

      }

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
                $newSec->other_relevent = isset($sec['other_relevent']) ? $sec['other_relevent'] : null;
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
                $newSec->other_relevent = isset($sec['other_relevent']) ?  $sec['other_relevent'] : null;
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

        $share_register_count = ShareRegister::where('company_id',$company_id)
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

        $share_register_count = ShareRegister::where('company_id', $company_id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($share_register_count){
            $share_registers = ShareRegister::where('company_id', $company_id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->get();
            foreach($share_registers as $d ) {
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
                 ->where('item_table_type', $this->settings('SHARE_REGISTER_TABLE','key')->id)
                 ->delete();
                 ShareRegister::where('id', $d->id)
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


            $newSr = new ShareRegister;
            $newSr->company_id = $company_id;
            $newSr->description = $sr['description'];
            $newSr->address_id = $new_sr_local_address_id;
            $newSr->foreign_address_id =  $new_sr_foreign_address_id;
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

        $record_count = AnnualRecords::where('company_id',$company_id)
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

        $record_count = AnnualRecords::where('company_id', $company_id)
                                                ->where('status',$this->settings('ANNUAL_RETURN','key')->id)
                                                ->count();
        if($record_count){
            $records = AnnualRecords::where('company_id', $company_id)
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
                 AnnualRecords::where('id', $d->id)
                             ->where('status', $this->settings('ANNUAL_RETURN','key')->id)
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


            $newRec = new AnnualRecords;
            $newRec->company_id = $company_id;
            $newRec->description = $rec['description'];
            $newRec->address_id = $new_rec_local_address_id;
            $newRec->foreign_address_id =  $new_rec_foreign_address_id;
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
    
    function submitShareRecords(Request $request){
        
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

        $annualReturnRecord =  OffshoreAlteration::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($annualReturnRecord->status) && $annualReturnRecord->status === $this->settings('OFFSHORE_ALTERATIONS_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Annual Return Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = OffshoreAlteration::where('request_id', $request_id)->update(['status' => $this->settings('OFFSHORE_ALTERATIONS_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('OFFSHORE_ALTERATIONS_RESUBMITTED', 'key')->id]);

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
    
    private function foriegnAddressOpenStatus($address){

        return !(
            ( isset($address->province) && $address->province )  &&
            ( isset($address->city) && $address->city )  &&
            ( isset($address->address1) && $address->address1 )  &&
            ( isset($address->address2) && $address->address2 )  &&
            ( isset($address->postcode) && $address->postcode )  &&
            ( isset($address->country) && $address->country )
        );


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

            $request_id = $this->valid_annual_return_request_operation($company_id);
    
            $changes_director = CompanyItemChange::leftJoin('company_members', 'company_item_changes.item_id', '=', 'company_members.id')
            ->where('company_item_changes.request_id',$request_id)
        ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
        ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
        ->whereNull('company_item_changes.old_record_id')
        ->where('company_members.designation_type', $this->settings('DERECTOR','key')->id)
        ->select('*')
        ->get();
    
        $changes_sec = CompanyItemChange::leftJoin('company_members', 'company_item_changes.item_id', '=', 'company_members.id')
            ->where('company_item_changes.request_id',$request_id)
        ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBERS','key')->id)
        ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
        ->whereNull('company_item_changes.old_record_id')
        ->where('company_members.designation_type', $this->settings('SECRETARY','key')->id)
        ->select('*')
        ->get();
        $changes_sec_firms = CompanyItemChange::leftJoin('company_member_firms', 'company_item_changes.item_id', '=', 'company_member_firms.id')
            ->where('company_item_changes.request_id',$request_id)
        ->where('company_item_changes.item_table_type', $this->settings('COMPANY_MEMBER_FIRMS','key')->id)
        ->whereIn('company_item_changes.changes_type', array( $this->settings('ADD','key')->id, $this->settings('EDIT','key')->id))
        ->whereNull('company_item_changes.old_record_id')
        ->where('company_member_firms.type_id', $this->settings('SECRETARY','key')->id)
        ->select('*')
        ->get();
    
        $directors = [];
        $secs = [];
        $secFirms = [];
    
        if(isset($changes_director[0]->id)) {
    
            foreach($changes_director as $member ) {
                $director = CompanyMember::where('id', $member->item_id)->first();
    
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
                    'country'  => ( $director->is_srilankan == 'no')  ? @$forAddress->country : @$address->country,
                    'passport_issued_country' => ($director->is_srilankan == 'no' )  ? $director->passport_issued_country : 'Sri Lanka',
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
    
        if(isset($changes_sec[0]->id)) {
            foreach($changes_sec as $member ) {
                $sec = CompanyMember::where('id', $member->item_id)->first();
    
                $address_id =  ($sec->foreign_address_id ) ? $sec->foreign_address_id : $sec->address_id;
                    
                if(!$sec->foreign_address_id){
                    $address = Address::where('id',$address_id)->first();
                }else{
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
                    'country'  => ( $sec->is_srilankan == 'no')  ? @$forAddress->country : 'Sri Lanka',
                    'passport_issued_country' => ( $sec->passport_issued_country )  ? $sec->passport_issued_country : 'Sri Lanka',
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
    
        if(isset($changes_sec_firms[0]->id)) {
            foreach($changes_sec_firms as $member ) {
                $sec = CompanyFirms::where('id', $member->item_id)->first();
    
                $address_id =  $sec->address_id;  
                $address = Address::where('id',$address_id)->first();
    
                $rec = array(
                    'id' => $sec->id,
                    'type' => ($sec->is_srilankan == 'yes' ) ? 'local' : 'foreign',
                    'title' =>  $sec->name,
                    'registration_no' => $sec->registration_no,
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
    
    
            return array(
    
                'directors' => $directors,
                'secs'      => $secs,
                'secFirms'  => $secFirms,
            );
    
    
    }



    function upload(Request $request){


            $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
            $real_file_name = $request->fileRealName;
            $file_type_id = $request->fileTypeId;
            $company_id = $request->company_id;
            $member_type = $request->member_type;
            $member_id = $request->member_id;

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
        
            $path = 'offshore-of-alterations/'.substr($company_id,0,2).'/'.$request_id;
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
        
            $token = md5(uniqid());
        
              
            $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
            $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
        
        
            $query = CompanyDocuments::query();
            $query->where('company_id', $company_id );
            $query->where('request_id', $request_id);
            $query->where('document_id',$file_type_id);
            if($member_type == 'member') {
                $query->where('company_member_id',$member_id);
            }
            if($member_type == 'firm') {
                $query->where('company_firm_id',$member_id);
             }
            $query->whereIn('status', array($doc_pending,$doc_req_resumbit,$doc_requeted));
            $query->delete();
                
        
               $doc = new CompanyDocuments;
               $doc->document_id = $file_type_id;
               $doc->path = $path;
               $doc->company_id = $company_id;
               $doc->request_id = $request_id;
               if($member_type == 'member') {
                $doc->company_member_id = $member_id;
               }
               if($member_type == 'firm') {
                $doc->company_firm_id = $member_id;
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



    function files_for_additional_docs($company_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => true,
                'doc_id' => 0,
        );

        if(!$company_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $request_id = null;
        $request_id = $this->valid_annual_return_request_operation($company_id);

        if(!$request_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'ALTERATIONS_OF_OFFSHORE_OTHER_DOCUMENTS')->first();
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
            if($company_status == 'OFFSHORE_ALTERATIONS_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    
        $path = 'offshore-of-alterations/other-docs/'.substr($company_id,0,2);
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
    
        $path = 'offshore-of-alterations/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'ALTERATIONS_OF_OFFSHORE_OTHER_DOCUMENTS')->first();


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

    private function getPanaltyCharge( $company_id , $request_id ) {

        $members_date_of_appointments = CompanyMember::leftJoin('company_item_changes', 'company_members.id', '=', 'company_item_changes.item_id')
                ->where('company_members.company_id', $company_id)
                ->where('company_members.status', $this->settings('OFFSHORE_ALTERATIONS', 'key')->id)
                ->whereIn('company_members.designation_type', [$this->settings('DERECTOR', 'key')->id, $this->settings('SECRETARY', 'key')->id])
                ->where('company_item_changes.request_id', $request_id)
                ->pluck('company_item_changes.change_or_effective_date')->toArray();
        
        $firms_date_of_appointments = CompanyFirms::leftJoin('company_item_changes', 'company_member_firms.id', '=', 'company_item_changes.item_id')
                ->where('company_member_firms.company_id', $company_id)
                ->where('company_member_firms.status', $this->settings('OFFSHORE_ALTERATIONS', 'key')->id)
                ->where('company_member_firms.type_id', $this->settings('SECRETARY', 'key')->id)
                ->where('company_item_changes.request_id', $request_id)
                ->pluck('company_item_changes.change_or_effective_date')->toArray();

        $appointments = array_merge($members_date_of_appointments, $firms_date_of_appointments);

        

        $request_address_item = CompanyItemChange::where('request_id',$request_id)
        ->where('changes_type', $this->settings('ADD','key')->id)
        ->where('item_table_type', $this->settings('ADDRESSES_TABLE','key')->id)
        ->first();
        $request_address = null;
        $request_address_change_date = '';
        if(isset($request_address_item->item_id) && $request_address_item->item_id ) {
            $request_address = Address::where('id', $request_address_item->item_id)->first();
            $request_address_change_date = $request_address_item->change_or_effective_date;

            $appointments[] = $request_address_change_date;
        }


        $request_for_address_item = CompanyItemChange::where('request_id',$request_id)
            ->where('changes_type', $this->settings('ADD','key')->id)
            ->where('item_table_type', $this->settings('FOREIGN_ADDRESSES_TABLE','key')->id)
            ->first();
        $request_for_address = null;
        $request_for_address_change_date = '';
        if(isset($request_for_address_item->item_id) && $request_for_address_item->item_id) {
                $request_for_address = Address::where('id', $request_for_address_item->item_id)->first();
                $request_for_address_change_date = $request_for_address_item->change_or_effective_date;

                $appointments[] = $request_for_address_change_date;
        }

        $alterRecord = OffshoreAlteration::where('company_id', $company_id)
        ->where('request_id', $request_id)
        ->first();

        if( $alterRecord ->charter_change_date) {
            $appointments[] = $alterRecord ->charter_change_date;
        }
        if( $alterRecord ->memorandum_change_date) {
            $appointments[] = $alterRecord ->memorandum_change_date;
        }
        if( $alterRecord ->article_change_date) {
            $appointments[] = $alterRecord ->article_change_date;
        }
        if( $alterRecord ->statute_change_date) {
            $appointments[] = $alterRecord ->statute_change_date;
        }


        $date_arr = array();

        $res_date = '';

        if(count($appointments)) {
            foreach($appointments as $c ) {
                $date_arr[] = strtotime($c);
            }
            $res_date = min($date_arr);
        }
       
        $penalty_value = 0;
       

        $min_date_gap = 30;
        $increment_gap_dates = 30;
        $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_23_INITIAL','key')->value );
        $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_23_INCREMENT','key')->value );
        $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_23_MAX','key')->value );
    
    
        $today =  time();
    
        if( $res_date )  {
    
            $date_gap =  intval( ($today - $res_date) / (24*60*60) );
    
            if($date_gap < $min_date_gap ) {
                return 0;
            }
    
            $increment_gaps = ( $date_gap % $increment_gap_dates == 0 ) ? $date_gap / $increment_gap_dates : intval($date_gap / $increment_gap_dates) + 1;
            $penalty_value  = $penalty_value + $init_panalty;
    
            if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
            }
    
        }
    
        return ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;
    
    }


} // end class