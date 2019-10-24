<?php

namespace App\Http\Controllers\API\v1\tender;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Address;
use App\ForeignAddress;
use App\SettingType;
use App\Setting;
use App\User;
use App\People;
use App\TenderPublication;
use App\Tender;
use App\TenderItem;
use App\TenderUser;
use App\TenderApplication;
use App\TenderApplicantItem;
use App\TenderApplyMember;
use App\TenderDocument;
use App\TenderDocumentStatus;
use App\Documents;
use App\Country;
use App\TenderCertificate;
use App\TenderRenewalReRegistration;
use Storage;
use App;
use URL;
use App\Http\Helper\_helper;

use PDF;

class TenderRenewalRegistrationController extends Controller
{
    use _helper;

    private $items_per_page;

    function __construct() {
        
        $this->items_per_page = 3;
    }


    function submitPCA7details(Request $request ) {

        $token = $request->token;
        $total_contract_cost = $request->total_contract_cost;
        $value_of_work_completed = $request->value_of_work_completed;
        $total_payment_received_for_work_completed = $request->total_payment_received_for_work_completed;
        $nature_of_sub_contract = $request->nature_of_sub_contract;
        $name_of_sub_contract = $request->name_of_sub_contract;
        $address_of_sub_contract = $request->address_of_sub_contract;
        $nationality_of_sub_contract = $request->nationality_of_sub_contract;
        $total_cost_of_sub_contract = $request->total_cost_of_sub_contract;
        $duration_of_sub_contract = $request->duration_of_sub_contract;
        $amount_of_commission_paid = $request->amount_of_commission_paid;


        $update_arr = array(
                'total_contract_cost' => $total_contract_cost,
                'value_of_work_completed' => $value_of_work_completed,
                'total_payment_received_for_work_completed' => $total_payment_received_for_work_completed,
                'nature_of_sub_contract' => $nature_of_sub_contract,
                'name_of_sub_contract' => $name_of_sub_contract,
                'address_of_sub_contract' => $address_of_sub_contract,
                'nationality_of_sub_contract' => $nationality_of_sub_contract,
                'total_cost_of_sub_contract' => $total_cost_of_sub_contract,
                'duration_of_sub_contract' => $duration_of_sub_contract,
                'amount_of_commission_paid' => $amount_of_commission_paid

        );

        TenderRenewalReRegistration::where('token', $token )->update($update_arr);

        return response()->json([
                'message' => 'Successfully updated pca 7 details.',
                'status' =>true,
                'error'  => 'no',
            ], 200);



    }


    /**************RENEWAL *****************/


    function getRenewalApplication( Request $request ) {
      
       try{
        $tenderRenewalToken =  $request->token;
        $tenderId = $request->tenderId;

        if( !$tenderRenewalToken ) {
                return response()->json([
                        'message'         => "Invalid Renewal Request.",
                        'status'          => false,
                        ], 200);
        }


        $rrItemCount = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->count();
 
        if( $rrItemCount  !== 1) {
                return response()->json([
                        'message'         => "Invalid Renewal Request.",
                        'status'          => false,
                        ], 200);
        }
        
        $renwal_pending_statuses = array($this->settings('TENDER_RENEWAL_PCA3_PENDING','key')->id, $this->settings('TENDER_RENEWAL_PCA4_PENDING','key')->id);
        
        $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
        
        $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                  //->where('status', $renwal_pending_status)
                                                  ->first();
        
        if( !in_array($rrItemInfo->status, $renwal_pending_statuses)){
                return response()->json([
                        'message'         => "Invalid Renewal Statuses.",
                        'status'          => false,
                        ], 200);

        }
        
        $tenderApplicantId = $applicantItemInfo->tender_application_id;
        $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();

        $update_updated_at = array(
                'updated_at' => date('Y-m-d H:i:s', time())
        );
        TenderApplication::where('id', $tenderApplicantId)
        ->update($update_updated_at);

        $tenderItemId = $applicantItemInfo->tender_item_id;
        $itemInfo = TenderItem::where('id', $tenderItemId)->first();

        //original tender info
        $tenderId =  $applicationInfo->tender_id;
        $tenderInfo = Tender::where('id', $tenderId)->first();

        $certificateInfo = null;
        $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
        //certificate info 

        $payment_value = 0;
        $pay_form_name = '';
        
        if( $renewal_status == 'TENDER_RENEWAL_PCA3_PENDING' ) {
                $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
               $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                   ->where('type', $cert_type)
                                                   ->orderBy('id', 'DESC')
                                                   ->first();
                $payment_value = $this->settings('PAYMENT_TENDER_PCA3_RENEWAL','key')->value;
                $pay_form_name = 'PCA 05';
        }
        if( $renewal_status == 'TENDER_RENEWAL_PCA4_PENDING' ) {
               $cert_type = $this->settings('CERT_TENDER_PCA4','key')->id;
              $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                  ->where('type', $cert_type)
                                                  ->orderBy('id', 'DESC')
                                                  ->first();
                $payment_value = $this->settings('PAYMENT_TENDER_PCA4_RENEWAL','key')->value;
                $pay_form_name = 'PCA 06';
       }

       // $applicationStatus = $this->settings($applicationInfo->status,'id')->key;

        
        $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
        $applicant_type_id = $applicant_type->id;

        $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
      
        $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
        $applicant_sub_type_id = $applicant_sub_type->id;
        
       
 

        return response()->json([
                'message'         => "Successfully populated application and related tender details.",
                'tenderInfo'      => $tenderInfo,
                'applicationInfo' => $applicationInfo,
                'status'          => true,
                'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                'applicantSubType' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->key : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->key ,
                'applicantTypeValue' => $this->settings( $applicationInfo->applicant_type ,'id')->value,
                'applicantSubTypeValue' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->value : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->value,
                'itemInfo'  => $itemInfo,
                'certificateNo' => $certificateInfo->certificate_no,
                'certificate_issued_at' => $certificateInfo->issued_at,
                'certificate_expires_at' => $certificateInfo->expires_at,

                'countries'  => Country::all(),

                'downloadDocs' => $this->generate_renewal_files(  $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                'uploadDocs' =>   $this->files_for_renewal_upload( $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                'renewal_status' => $this->settings($rrItemInfo->status,'id')->key,
                
                'total_contract_cost' => $rrItemInfo->total_contract_cost,
                'value_of_work_completed' => $rrItemInfo->value_of_work_completed,
                'total_payment_received_for_work_completed' => $rrItemInfo->total_payment_received_for_work_completed,
                'nature_of_sub_contract' => $rrItemInfo->nature_of_sub_contract,
                'name_of_sub_contract' => $rrItemInfo->name_of_sub_contract,
                'address_of_sub_contract' => $rrItemInfo->address_of_sub_contract,
                'nationality_of_sub_contract' => $rrItemInfo->nationality_of_sub_contract,
                'total_cost_of_sub_contract' => $rrItemInfo->total_cost_of_sub_contract,
                'duration_of_sub_contract' => $rrItemInfo->duration_of_sub_contract,
                'amount_of_commission_paid' => $rrItemInfo->amount_of_commission_paid,
                'rowId' => $rrItemInfo->id,
                'pca1_payment' => $payment_value,
                'pay_form_name' => $pay_form_name,
                'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
               

                ], 200);
       }catch(Exception $e){
        return response()->json($e->getMessage());
       }
        

    }

    function getRenewalResubmission( Request $request ) {
      
        try{
                $tenderRenewalToken =  $request->token;
                $tenderId = $request->tenderId;
        
                if( !$tenderRenewalToken ) {
                        return response()->json([
                                'message'         => "Invalid Renewal resubmission Request.",
                                'status'          => false,
                                ], 200);
                }
        
        
                $rrItemCount = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->count();
         
                if( $rrItemCount  !== 1) {
                        return response()->json([
                                'message'         => "Invalid Renewal Request.",
                                'status'          => false,
                                ], 200);
                }
                
                $renwal_pending_statuses = array($this->settings('TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT','key')->id, $this->settings('TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT','key')->id);
                
                $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
                
                $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                          //->where('status', $renwal_pending_status)
                                                          ->first();
                
                if( !in_array($rrItemInfo->status, $renwal_pending_statuses)){
                        return response()->json([
                                'message'         => "Invalid Renewal resubmission Statuses.",
                                'status'          => false,
                                ], 200);
        
                }
                
                $tenderApplicantId = $applicantItemInfo->tender_application_id;
                $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();
        
                $tenderItemId = $applicantItemInfo->tender_item_id;
                $itemInfo = TenderItem::where('id', $tenderItemId)->first();
        
                //original tender info
                $tenderId =  $applicationInfo->tender_id;
                $tenderInfo = Tender::where('id', $tenderId)->first();
        
                $certificateInfo = null;
                $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
                //certificate info 
                if( $renewal_status == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT' ) {
                        $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
                       $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                           ->where('type', $cert_type)
                                                           ->orderBy('id', 'DESC')
                                                           ->first();
                }
                if( $renewal_status == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT' ) {
                       $cert_type = $this->settings('CERT_TENDER_PCA4','key')->id;
                      $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                          ->where('type', $cert_type)
                                                          ->orderBy('id', 'DESC')
                                                          ->first();
               }
        
               // $applicationStatus = $this->settings($applicationInfo->status,'id')->key;
        
                
                $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
                $applicant_type_id = $applicant_type->id;
        
                $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
              
                $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
                $applicant_sub_type_id = $applicant_sub_type->id;
                
               
         
        
                return response()->json([
                        'message'         => "Successfully populated application and related tender details.",
                        'tenderInfo'      => $tenderInfo,
                        'applicationInfo' => $applicationInfo,
                        'status'          => true,
                        'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                        'applicantSubType' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->key : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->key,
                        'applicantTypeValue' => $this->settings( $applicationInfo->applicant_type ,'id')->value,
                        'applicantSubTypeValue' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->value : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->value,
                        'itemInfo'  => $itemInfo,
                        'certificateNo' => $certificateInfo->certificate_no,
                        'certificate_issued_at' => $certificateInfo->issued_at,
                        'certificate_expires_at' => $certificateInfo->expires_at,
        
                        'countries'  => Country::all(),
        
                        'downloadDocs' => $this->generate_renewal_files(  $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                        'uploadDocs' =>   $this->files_for_renewal_upload( $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                        'renewal_status' => $this->settings($rrItemInfo->status,'id')->key,
                        
                        'total_contract_cost' => $rrItemInfo->total_contract_cost,
                        'value_of_work_completed' => $rrItemInfo->value_of_work_completed,
                        'total_payment_received_for_work_completed' => $rrItemInfo->total_payment_received_for_work_completed,
                        'nature_of_sub_contract' => $rrItemInfo->nature_of_sub_contract,
                        'name_of_sub_contract' => $rrItemInfo->name_of_sub_contract,
                        'address_of_sub_contract' => $rrItemInfo->address_of_sub_contract,
                        'nationality_of_sub_contract' => $rrItemInfo->nationality_of_sub_contract,
                        'total_cost_of_sub_contract' => $rrItemInfo->total_cost_of_sub_contract,
                        'duration_of_sub_contract' => $rrItemInfo->duration_of_sub_contract,
                        'amount_of_commission_paid' => $rrItemInfo->amount_of_commission_paid,
                        'rowId' => $rrItemInfo->id
                       
        
                        ], 200);
               }catch(Exception $e){
                return response()->json($e->getMessage());
               }
                
         
 
     }



     function renewalResubmitted(Request $request){

             $token = $request->token;

             $rrItemCount = TenderRenewalReRegistration::where('token',$token)->count();
         
             if( $rrItemCount  !== 1) {
                     return response()->json([
                             'message'         => "Invalid Resubmission Request",
                             'status'          => false,
                             ], 200);
             }
             
             $renwal_pending_statuses = array($this->settings('TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT','key')->id, $this->settings('TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT','key')->id);
             
             $rrItemInfo = TenderRenewalReRegistration::where('token',$token)->first();
             $current_status = $this->settings($rrItemInfo->status,'id')->key;
             $new_status = '';
             if($current_status === 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT') {
               $new_status = 'TENDER_RENEWAL_PCA3_RESUBMITED';
             }
             if($current_status === 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT') {
                $new_status = 'TENDER_RENEWAL_PCA4_RESUBMITED';
             }

             if(!$new_status) {

                return response()->json([
                        'message'         => "Invalid Resubmission Request",
                        'status'          => false,
                        ], 200);

             }
             

             $update_arr = array(
                        'status' => $this->settings( $new_status,'key')->id,
                        );

             TenderRenewalReRegistration::where('token', $token )->update($update_arr);

            
             return response()->json([
                'message'         => "Resubmitted.",
                'status'          => true,
                ], 200);
     }

    function generate_renewal_files($renewal_type,$tenderRenewalToken){

        $generated_files = array(

                'docs' => array(),
        );

         if( $renewal_type == 'TENDER_RENEWAL_PCA3_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT') {
                $file_name_key = 'TENDER_PCA_5';
                $file_name = 'PCA 05';
        }

        if( $renewal_type == 'TENDER_RENEWAL_PCA4_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT') {
                $file_name_key = 'TENDER_PCA_6';
                $file_name = 'PCA 06';
        }
         

       /***** */
       $renwal_pending_statuses = array($this->settings('TENDER_RENEWAL_PCA3_PENDING','key')->id, $this->settings('TENDER_RENEWAL_PCA4_PENDING','key')->id);
        
       $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
       
       $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                 //->where('status', $renwal_pending_status)
                                                 ->first();
       
   
       $tenderApplicantId = $applicantItemInfo->tender_application_id;
       $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();

       $tenderItemId = $applicantItemInfo->tender_item_id;
       $itemInfo = TenderItem::where('id', $tenderItemId)->first();

       //original tender info
       $tenderId =  $applicationInfo->tender_id;
       $tenderInfo = Tender::where('id', $tenderId)->first();

       $certificateInfo = null;
       $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
      
       //certificate info 
       if($renewal_type == 'TENDER_RENEWAL_PCA3_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT' ) {
               $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
              $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                  ->where('type', $cert_type)
                                                  ->orderBy('id', 'DESC')
                                                  ->first();
        
       }
       if( $renewal_type == 'TENDER_RENEWAL_PCA4_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT' ) {
              $cert_type = $this->settings('CERT_TENDER_PCA4','key')->id;
             $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                 ->where('type', $cert_type)
                                                 ->orderBy('id', 'DESC')
                                                 ->first();
      }

      // $applicationStatus = $this->settings($applicationInfo->status,'id')->key;

       
       $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
       $applicant_type_id = $applicant_type->id;

       $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
     
       $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
       $applicant_sub_type_id = $applicant_sub_type->id;

       /******* */



        $data = array(
                'public_path' => public_path(),
                'eroc_logo' => url('/').'/images/forms/eroc.png',
                'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                'css_file' => url('/').'/images/forms/form1/form1.css',
                'rrItemInfo' => $rrItemInfo,
                'tenderInfo'      => $tenderInfo,
                'applicationInfo' => $applicationInfo,
                'applicantItemInfo' => $applicantItemInfo,
                'status'          => true,
                'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                'applicantSubType' => ($applicationInfo->applicant_sub_type) ?  $this->settings( $applicationInfo->applicant_sub_type ,'id')->key :  $this->settings( $applicationInfo->tenderer_sub_type ,'id')->key,
                'applicantTypeValue' => $this->settings( $applicationInfo->applicant_type ,'id')->value,
                'applicantSubTypeValue' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->value : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->value,
                'itemInfo'  => $itemInfo,
                'certificateNo' => $certificateInfo->certificate_no,
                'certificate_issued_at' => $certificateInfo->issued_at,
                'certificate_expires_at' => $certificateInfo->expires_at,
            );

               $applicant_item_row_id =$applicantItemInfo->id;


                 /********pca 5/6 ********/
            
                $directory = "tender-renewal/$applicant_item_row_id";
                Storage::makeDirectory($directory);

                
                if( $renewal_type == 'TENDER_RENEWAL_PCA3_PENDING' ||  $renewal_type == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT') {
                        $view = 'forms.'.'pca5';
                }
        
                if( $renewal_type == 'TENDER_RENEWAL_PCA4_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT') {
                        $view = 'forms.'.'pca6';
                }


                $pdf = PDF::loadView($view, $data);
                $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$applicant_item_row_id.'.pdf');

                $file_row = array();
                
                $file_row['name'] = $file_name;
                $file_row['file_name_key'] = $file_name_key;
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$applicant_item_row_id.pdf");
                $generated_files['docs'][] = $file_row;



                /********pca 7 ********/
                $file_name_key = 'TENDER_PCA_7';
                $file_name = 'PCA 07';

                $directory = "tender-renewal/$applicant_item_row_id";
                Storage::makeDirectory($directory);
                $view = 'forms.'.'pca7';
               
                $pdf = PDF::loadView($view, $data);
                $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$applicant_item_row_id.'.pdf');

                $file_row = array();
                
                $file_row['name'] = $file_name;
                $file_row['file_name_key'] = $file_name_key;
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$applicant_item_row_id.pdf");
                $generated_files['docs'][] = $file_row;
                 
  
       
          
          return $generated_files;
      }


      function files_for_renewal_upload($renewal_type, $tenderRenewalToken){

        // $docs = $this->getDocs($doc_type );
 
       //  $uploaded = $docs['upload'];
 
         $generated_files = array(
                 'docs' => array(),
                 'uploadedAll' => false
               
         );

          /***** */
       $renwal_pending_statuses = array($this->settings('TENDER_RENEWAL_PCA3_PENDING','key')->id, $this->settings('TENDER_RENEWAL_PCA4_PENDING','key')->id);
        
       $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
       
       $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                 //->where('status', $renwal_pending_status)
                                                 ->first();
       
   
       $tenderApplicantId = $applicantItemInfo->tender_application_id;
       $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();

       $tenderItemId = $applicantItemInfo->tender_item_id;
       $itemInfo = TenderItem::where('id', $tenderItemId)->first();

       //original tender info
       $tenderId =  $applicationInfo->tender_id;
       $tenderInfo = Tender::where('id', $tenderId)->first();

       $certificateInfo = null;
       $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
      
       //certificate info 
       if($renewal_type == 'TENDER_RENEWAL_PCA3_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT' ) {
               $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
              $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                  ->where('type', $cert_type)
                                                  ->orderBy('id', 'DESC')
                                                  ->first();
        
       }
       if( $renewal_type == 'TENDER_RENEWAL_PCA4_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT' ) {
              $cert_type = $this->settings('CERT_TENDER_PCA4','key')->id;
             $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                 ->where('type', $cert_type)
                                                 ->orderBy('id', 'DESC')
                                                 ->first();
      }

 
       $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
       $applicant_type_id = $applicant_type->id;

       $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
     
       $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
       $applicant_sub_type_id = $applicant_sub_type->id;

       /******* */
 
  
        // documents list
        $pca5_form_info = Documents::where('key', 'TENDER_PCA_5')->first();
        $pca6_form_info = Documents::where('key', 'TENDER_PCA_6')->first();
        $pca7_form_info = Documents::where('key', 'TENDER_PCA_7')->first();
      
        $applicant_type = $this->settings($applicationInfo->applicant_type,'id')->key;
        $applicant_sub_type = ($applicationInfo->applicant_sub_type) ? $this->settings($applicationInfo->applicant_sub_type,'id')->key : $this->settings($applicationInfo->tenderer_sub_type,'id')->key ;
 
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
 
        $has_all_uploaded_str = '';

        if( $renewal_type == 'TENDER_RENEWAL_PCA3_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT' ) {
        
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $pca5_form_info->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $pca5_form_info->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] =  $applicantItemInfo->id;
        
        
                $uploadedDoc =  TenderDocument::where('tender_id', $tenderId)
                                                ->where('appication_id',$tenderApplicantId)
                                                ->where('document_id', $pca5_form_info->id )
                                                ->where('application_item_id', $applicantItemInfo->id)
                                                ->orderBy('id', 'DESC')
                                                ->first();

        
                $uploadeDocStatus = @$uploadedDoc->status;

                if($renewal_type == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
        
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
        
                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;

        }

        if( $renewal_type == 'TENDER_RENEWAL_PCA4_PENDING' || $renewal_type == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT') {
        
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $pca6_form_info->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $pca6_form_info->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] =  $applicantItemInfo->id;
        
        
                $uploadedDoc =  TenderDocument::where('tender_id', $tenderId)
                                                ->where('appication_id',$tenderApplicantId)
                                                ->where('document_id', $pca6_form_info->id )
                                                ->where('application_item_id', $applicantItemInfo->id)
                                                ->orderBy('id', 'DESC')
                                                ->first();
                $uploadeDocStatus = @$uploadedDoc->status;

                if($renewal_type == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
        
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
        
                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;

        }

        /******pca 7 *********/

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $pca7_form_info->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $pca7_form_info->id;
        $file_row['file_description'] = '';
        $file_row['applicant_item_id'] =  $applicantItemInfo->id;


        $uploadedDoc =  TenderDocument::where('tender_id', $tenderId)
                                        ->where('appication_id',$tenderApplicantId)
                                        ->where('document_id', $pca7_form_info->id )
                                        ->where('application_item_id', $applicantItemInfo->id)
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;

        if(($renewal_type == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT' || $renewal_type == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT') && isset($uploadeDocStatus) && $uploadeDocStatus ){
                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
         }

        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;



       
        
        return $generated_files;
     
     }


     function upload_renwal(Request $request){

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $tender_id = $request->tenderId; 
        $applicant_id = (int) $request->applicantId;
        $item_id = (int) $request->itemId;
        $renewal_token =  $request->token;

       // $applicantItemInfo = TenderApplicantItem::where('token',$renewal_token)
                                                  //->where('status', $renwal_pending_status)
                                                  //->first();
        $rrItemInfo = TenderRenewalReRegistration::where('token',$renewal_token)->first();

        
       

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

       $path = 'tender/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$renewal_token;
       $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');

      
       $token = md5(uniqid());

        $get_query = TenderDocument::query();
        $get_query->where('tender_id', $tender_id );
        $get_query->where('appication_id', $applicant_id);
        $get_query->where('document_id',$file_type_id);
        $get_query->where('application_item_id', $item_id );
       
        $old_doc_info = $get_query->first();

        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
      

        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
         $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;

         $query = TenderDocument::query();
         $query->where('tender_id', $tender_id );
         $query->where('appication_id', $applicant_id);
         $query->where('document_id',$file_type_id);
         $query->where('application_item_id', $item_id );
         $query->whereIn('status', array($doc_pending,$doc_req_resumbit));
         $query->delete();
        

       $doc = new TenderDocument;
       $doc->document_id = $file_type_id;
       $doc->path = $path;
       $doc->tender_id = $tender_id;
       $doc->appication_id = $applicant_id;
       $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
       $doc->file_token = $token;
       $doc->name = $real_file_name;
       $doc->application_item_id = $item_id;
       

       $doc->save();
       $new_doc_id = $doc->id;

       if( $old_doc_id ) { //update new doc id to old doc id in tenderdocument status row
         
         $update_new_id_info = array(
                 'tender_document_id' => $new_doc_id
         );
         $updated = TenderDocumentStatus::where('tender_document_id', $old_doc_id)->update($update_new_id_info);
       }


       return response()->json([
         'message' => 'File uploaded successfully.',
         'status' =>true,
         'name' =>basename($path),
         'error'  => 'no',
         'uploadDocs' =>   $this->files_for_renewal_upload( $this->settings($rrItemInfo->status,'id')->key ,$renewal_token)
         
     ], 200);
     }


     function removeTenderRenewalFile( Request $request ){

        $tender_id = $request->tenderId;
        $applicant_id = $request->applicantId;
        $file_type_id = $request->fileTypeId;
        $item_id = $request->itemId;
        $renewal_token =  $request->token;

        $rrItemInfo = TenderRenewalReRegistration::where('token',$renewal_token)->first();

        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;

        $query = TenderDocument::query();
        $query->where('tender_id', $tender_id );
        $query->where('appication_id', $applicant_id);
        $query->where('document_id',$file_type_id);
        $query->where('application_item_id', $item_id );
        $query->where('status', $doc_pending);
       
        $query->delete();


       /* TenderDocument::where('tender_id', $tender_id)
                     ->where('appication_id',$applicant_id)
                     ->where('document_id', $file_type_id)
                     ->delete();*/


 
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        'uploadDocs' =>   $this->files_for_renewal_upload( $this->settings($rrItemInfo->status,'id')->key ,$renewal_token)

        ], 200);

        
     }



  /*************************RE REGISTRATION *******************************/


    function getReRegistrationApplication( Request $request ) {
      
        try{
         $tenderRenewalToken =  $request->token;
         $tenderId = $request->tenderId;
 
         if( !$tenderRenewalToken ) {
                 return response()->json([
                         'message'         => "Invalid Re Registration Request.",
                         'status'          => false,
                         ], 200);
         }
 
 
         $applicantItemCount = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->count();
  
         if( $applicantItemCount  !== 1) {
                 return response()->json([
                         'message'         => "Invalid Re Registration Request.",
                         'status'          => false,
                         ], 200);
         }
         
         $renwal_pending_statuses = array($this->settings('TENDER_REREGISTRATION_PCA3_PENDING','key')->id, $this->settings('TENDER_REREGISTRATION_PCA4_PENDING','key')->id);
          $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
        
        $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                  //->where('status', $renwal_pending_status)
                                                  ->first();

        
        if( !in_array($rrItemInfo->status, $renwal_pending_statuses)){
                return response()->json([
                        'message'         => "Invalid Re Registration Statuses.",
                        'status'          => false,
                        ], 200);

        }
        
        $tenderApplicantId = $applicantItemInfo->tender_application_id;
        $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();

        $update_updated_at = array(
                'updated_at' => date('Y-m-d H:i:s', time())
        );
        TenderApplication::where('id', $tenderApplicantId)
        ->update($update_updated_at);

        $tenderItemId = $applicantItemInfo->tender_item_id;
        $itemInfo = TenderItem::where('id', $tenderItemId)->first();

        //original tender info
        $tenderId =  $applicationInfo->tender_id;
        $tenderInfo = Tender::where('id', $tenderId)->first();

        $certificateInfo = null;
        $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
        //certificate info 

        $payment_value = 0;
        $pay_form_name = '';
        if( $renewal_status == 'TENDER_REREGISTRATION_PCA3_PENDING' ) {
                $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
               $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                   ->where('type', $cert_type)
                                                   ->orderBy('id', 'DESC')
                                                   ->first();
                $payment_value = $this->settings('PAYMENT_TENDER_PCA3_REREGISTRATION','key')->value;
                $pay_form_name = 'PCA 08';
        }
        if( $renewal_status == 'TENDER_REREGISTRATION_PCA4_PENDING' ) {
               $cert_type = $this->settings('CERT_TENDER_PCA4','key')->id;
              $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                  ->where('type', $cert_type)
                                                  ->orderBy('id', 'DESC')
                                                  ->first();
                $payment_value = $this->settings('PAYMENT_TENDER_PCA4_REREGISTRATION','key')->value;
                $pay_form_name = 'PCA 09';
       }

        
        $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
        $applicant_type_id = $applicant_type->id;

        $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
      
        $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
        $applicant_sub_type_id = $applicant_sub_type->id;



         return response()->json([
                 'message'         => "Successfully populated application and related tender details.",
                'tenderInfo'      => $tenderInfo,
                'applicationInfo' => $applicationInfo,
                'status'          => true,
                'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                'applicantSubType' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->key : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->key,
                'applicantTypeValue' => $this->settings( $applicationInfo->applicant_type ,'id')->value,
                'applicantSubTypeValue' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->value : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->value,
                'itemInfo'  => $itemInfo,
                'certificateNo' => $certificateInfo->certificate_no,
                'certificate_issued_at' => $certificateInfo->issued_at,
                'certificate_expires_at' => $certificateInfo->expires_at,

                'countries'  => Country::all(),

                'downloadDocs' => $this->generate_rereg_files(  $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                'uploadDocs' =>   $this->files_for_rereg_upload( $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                'renewal_status' => $this->settings($rrItemInfo->status,'id')->key,
                
                'total_contract_cost' => $rrItemInfo->total_contract_cost,
                'value_of_work_completed' => $rrItemInfo->value_of_work_completed,
                'total_payment_received_for_work_completed' => $rrItemInfo->total_payment_received_for_work_completed,
                'nature_of_sub_contract' => $rrItemInfo->nature_of_sub_contract,
                'name_of_sub_contract' => $rrItemInfo->name_of_sub_contract,
                'address_of_sub_contract' => $rrItemInfo->address_of_sub_contract,
                'nationality_of_sub_contract' => $rrItemInfo->nationality_of_sub_contract,
                'total_cost_of_sub_contract' => $rrItemInfo->total_cost_of_sub_contract,
                'duration_of_sub_contract' => $rrItemInfo->duration_of_sub_contract,
                'amount_of_commission_paid' => $rrItemInfo->amount_of_commission_paid,
                'rowId' => $rrItemInfo->id,
                'pca1_payment' => $payment_value,
                'pay_form_name' => $pay_form_name,
                'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                 ], 200);
        }catch(Exception $e){
         return response()->json($e->getMessage());
        }
         
 
     }
 
     function getReRegistrationResubmission( Request $request ) {
       
        try{
                $tenderRenewalToken =  $request->token;
                $tenderId = $request->tenderId;
        
                if( !$tenderRenewalToken ) {
                        return response()->json([
                                'message'         => "Invalid Re Registration Resubmission Request.",
                                'status'          => false,
                                ], 200);
                }
        
        
                $applicantItemCount = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->count();
         
                if( $applicantItemCount  !== 1) {
                        return response()->json([
                                'message'         => "Invalid Re Registration Resubmission Request.",
                                'status'          => false,
                                ], 200);
                }
                
                $renwal_pending_statuses = array($this->settings('TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT','key')->id, $this->settings('TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT','key')->id);
                 $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
               
               $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                         //->where('status', $renwal_pending_status)
                                                         ->first();
       
               
               if( !in_array($rrItemInfo->status, $renwal_pending_statuses)){
                       return response()->json([
                               'message'         => "Invalid Re Registration Statuses.",
                               'status'          => false,
                               ], 200);
       
               }
               
               $tenderApplicantId = $applicantItemInfo->tender_application_id;
               $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();
       
               $tenderItemId = $applicantItemInfo->tender_item_id;
               $itemInfo = TenderItem::where('id', $tenderItemId)->first();
       
               //original tender info
               $tenderId =  $applicationInfo->tender_id;
               $tenderInfo = Tender::where('id', $tenderId)->first();
       
               $certificateInfo = null;
               $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
               //certificate info 
               if( $renewal_status == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT' ) {
                       $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
                      $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                          ->where('type', $cert_type)
                                                          ->orderBy('id', 'DESC')
                                                          ->first();
               }
               if( $renewal_status == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT' ) {
                      $cert_type = $this->settings('CERT_TENDER_PCA4','key')->id;
                     $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                         ->where('type', $cert_type)
                                                         ->orderBy('id', 'DESC')
                                                         ->first();
              }
       
               
               $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
               $applicant_type_id = $applicant_type->id;
       
               $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
             
               $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
               $applicant_sub_type_id = $applicant_sub_type->id;
                
       
                return response()->json([
                        'message'         => "Successfully populated application and related tender details.",
                       'tenderInfo'      => $tenderInfo,
                       'applicationInfo' => $applicationInfo,
                       'status'          => true,
                       'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                       'applicantSubType' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->key : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->key,
                       'applicantTypeValue' => $this->settings( $applicationInfo->applicant_type ,'id')->value,
                       'applicantSubTypeValue' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->value :  $this->settings( $applicationInfo->tenderer_sub_type ,'id')->value,
                       'itemInfo'  => $itemInfo,
                       'certificateNo' => $certificateInfo->certificate_no,
                       'certificate_issued_at' => $certificateInfo->issued_at,
                       'certificate_expires_at' => $certificateInfo->expires_at,
       
                       'countries'  => Country::all(),
       
                       'downloadDocs' => $this->generate_rereg_files(  $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                       'uploadDocs' =>   $this->files_for_rereg_upload( $this->settings($rrItemInfo->status,'id')->key, $tenderRenewalToken),
                       'renewal_status' => $this->settings($rrItemInfo->status,'id')->key,
                       
                       'total_contract_cost' => $rrItemInfo->total_contract_cost,
                       'value_of_work_completed' => $rrItemInfo->value_of_work_completed,
                       'total_payment_received_for_work_completed' => $rrItemInfo->total_payment_received_for_work_completed,
                       'nature_of_sub_contract' => $rrItemInfo->nature_of_sub_contract,
                       'name_of_sub_contract' => $rrItemInfo->name_of_sub_contract,
                       'address_of_sub_contract' => $rrItemInfo->address_of_sub_contract,
                       'nationality_of_sub_contract' => $rrItemInfo->nationality_of_sub_contract,
                       'total_cost_of_sub_contract' => $rrItemInfo->total_cost_of_sub_contract,
                       'duration_of_sub_contract' => $rrItemInfo->duration_of_sub_contract,
                       'amount_of_commission_paid' => $rrItemInfo->amount_of_commission_paid,
                       'rowId' => $rrItemInfo->id
                        ], 200);
               }catch(Exception $e){
                return response()->json($e->getMessage());
               }
                
  
      }

      function reregResubmitted(Request $request){

                $token = $request->token;

                $rrItemCount = TenderRenewalReRegistration::where('token',$token)->count();
        
                if( $rrItemCount  !== 1) {
                        return response()->json([
                                'message'         => "Invalid Resubmission Request",
                                'status'          => false,
                                ], 200);
                }
                
                $renwal_pending_statuses = array($this->settings('TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT','key')->id, $this->settings('TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT','key')->id);
                
                $rrItemInfo = TenderRenewalReRegistration::where('token',$token)->first();
                $current_status = $this->settings($rrItemInfo->status,'id')->key;
                $new_status = '';
                if($current_status === 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT') {
                $new_status = 'TENDER_REREGISTRATION_PCA3_RESUBMITED';
                }
                if($current_status === 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT') {
                $new_status = 'TENDER_REREGISTRATION_PCA4_RESUBMITED';
                }

                if(!$new_status) {

                return response()->json([
                        'message'         => "Invalid Resubmission Request",
                        'status'          => false,
                        ], 200);

                }
                

                $update_arr = array(
                        'status' => $this->settings( $new_status,'key')->id,
                        );

                TenderRenewalReRegistration::where('token', $token )->update($update_arr);

        
                return response()->json([
                'message'         => "Resubmitted.",
                'status'          => true,
                ], 200);
     }

 
     function generate_rereg_files($renewal_type,$tenderRenewalToken){
 
         $generated_files = array(
 
                 'docs' => array(),
     
         );
 
 
          if( $renewal_type == 'TENDER_REREGISTRATION_PCA3_PENDING' || $renewal_type == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT') {
                 $file_name_key = 'TENDER_PCA_8';
                 $file_name = 'PCA 08';
         }
 
         if( $renewal_type == 'TENDER_REREGISTRATION_PCA4_PENDING' || $renewal_type == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT' ) {
                 $file_name_key = 'TENDER_PCA_9';
                 $file_name = 'PCA 09';
         }
 
   
           /***** */
       //$renwal_pending_statuses = array($this->settings('TENDER_RENEWAL_PCA3_PENDING','key')->id, $this->settings('TENDER_RENEWAL_PCA4_PENDING','key')->id);
        
       $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
       
       $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                 //->where('status', $renwal_pending_status)
                                                 ->first();
       
   
       $tenderApplicantId = $applicantItemInfo->tender_application_id;
       $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();

       $tenderItemId = $applicantItemInfo->tender_item_id;
       $itemInfo = TenderItem::where('id', $tenderItemId)->first();

       //original tender info
       $tenderId =  $applicationInfo->tender_id;
       $tenderInfo = Tender::where('id', $tenderId)->first();

       $certificateInfo = null;
       $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
      
       //certificate info 
       if($renewal_type == 'TENDER_REREGISTRATION_PCA3_PENDING' || $renewal_type == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT' ) {
               $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
              $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                  ->where('type', $cert_type)
                                                  ->orderBy('id', 'DESC')
                                                  ->first();
        
       }
       if( $renewal_type == 'TENDER_REREGISTRATION_PCA4_PENDING' || $renewal_type == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT' ) {
              $cert_type = $this->settings('CERT_TENDER_PCA4','key')->id;
             $certificateInfo = TenderCertificate::where('item_id', $rrItemInfo->tender_application_item_id)
                                                 ->where('type', $cert_type)
                                                 ->orderBy('id', 'DESC')
                                                 ->first();
      }

      // $applicationStatus = $this->settings($applicationInfo->status,'id')->key;

       
       $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
       $applicant_type_id = $applicant_type->id;

       $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
     
       $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
       $applicant_sub_type_id = $applicant_sub_type->id;

       /******* */



        $data = array(
                'public_path' => public_path(),
                'eroc_logo' => url('/').'/images/forms/eroc.png',
                'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                'css_file' => url('/').'/images/forms/form1/form1.css',
                'rrItemInfo' => $rrItemInfo,
                'tenderInfo'      => $tenderInfo,
                'applicationInfo' => $applicationInfo,
                'applicantItemInfo' => $applicantItemInfo,
                'status'          => true,
                'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                'applicantSubType' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->key : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->key,
                'applicantTypeValue' => $this->settings( $applicationInfo->applicant_type ,'id')->value,
                'applicantSubTypeValue' => ($applicationInfo->applicant_sub_type) ? $this->settings( $applicationInfo->applicant_sub_type ,'id')->value : $this->settings( $applicationInfo->tenderer_sub_type ,'id')->value,
                'itemInfo'  => $itemInfo,
                'certificateNo' => $certificateInfo->certificate_no,
                'certificate_issued_at' => $certificateInfo->issued_at,
                'certificate_expires_at' => $certificateInfo->expires_at,
            );

               $applicant_item_row_id =$applicantItemInfo->id;


                 /********pca 5/6 ********/
            
                $directory = "tender-rereg/$applicant_item_row_id";
                Storage::makeDirectory($directory);

                
                if( $renewal_type == 'TENDER_REREGISTRATION_PCA3_PENDING' ||  $renewal_type == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT') {
                        $view = 'forms.'.'pca8';
                }
        
                if( $renewal_type == 'TENDER_REREGISTRATION_PCA4_PENDING' || $renewal_type == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT') {
                        $view = 'forms.'.'pca9';
                }


                $pdf = PDF::loadView($view, $data);
                $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$applicant_item_row_id.'.pdf');

                $file_row = array();
                
                $file_row['name'] = $file_name;
                $file_row['file_name_key'] = $file_name_key;
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$applicant_item_row_id.pdf");
                $generated_files['docs'][] = $file_row;



                /********pca 7 ********/
                $file_name_key = 'TENDER_PCA_7';
                $file_name = 'PCA 07';

                $directory = "tender-rereg/$applicant_item_row_id";
                Storage::makeDirectory($directory);
                $view = 'forms.'.'pca7';
               
                $pdf = PDF::loadView($view, $data);
                $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$applicant_item_row_id.'.pdf');

                $file_row = array();
                
                $file_row['name'] = $file_name;
                $file_row['file_name_key'] = $file_name_key;
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$applicant_item_row_id.pdf");
                $generated_files['docs'][] = $file_row;
                 
  
       
          
          return $generated_files;
       }
 
 
       function files_for_rereg_upload($renewal_type, $tenderRenewalToken){
 
         // $docs = $this->getDocs($doc_type );
  
        //  $uploaded = $docs['upload'];
  
          $generated_files = array(
                  'docs' => array(),
                  'uploadedAll' => false
                
          );
 
 

         $rrItemInfo = TenderRenewalReRegistration::where('token',$tenderRenewalToken)->first();
       
         $applicantItemInfo = TenderApplicantItem::where('id',$rrItemInfo->tender_application_item_id)
                                                   //->where('status', $renwal_pending_status)
                                                   ->first();
         
     
         $tenderApplicantId = $applicantItemInfo->tender_application_id;
         $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();
  
         $tenderItemId = $applicantItemInfo->tender_item_id;
         $itemInfo = TenderItem::where('id', $tenderItemId)->first();
  
         //original tender info
         $tenderId =  $applicationInfo->tender_id;
         $tenderInfo = Tender::where('id', $tenderId)->first();
  
         $certificateInfo = null;
         $renewal_status = $this->settings($rrItemInfo->status,'id')->key;
        
         $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
         $applicant_type_id = $applicant_type->id;
  
         $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
       
         $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
         $applicant_sub_type_id = $applicant_sub_type->id;


         /*********** */
  
   
         // documents list
         $pca8_form_info = Documents::where('key', 'TENDER_PCA_8')->first();
         $pca9_form_info = Documents::where('key', 'TENDER_PCA_9')->first();
         $pca7_form_info = Documents::where('key', 'TENDER_PCA_7')->first();
       
         $applicant_type = $this->settings($applicationInfo->applicant_type,'id')->key;
         $applicant_sub_type = ($applicationInfo->applicant_sub_type) ? $this->settings($applicationInfo->applicant_sub_type,'id')->key : $this->settings($applicationInfo->tenderer_sub_type,'id')->key;
  
         $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
  
         $has_all_uploaded_str = '';
 
         if( $renewal_type == 'TENDER_REREGISTRATION_PCA3_PENDING' || $renewal_type == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT') {
         
                 $file_row = array();
                 $file_row['doc_comment'] = '';
                 $file_row['doc_status'] = 'DOCUMENT_PENDING';
                 $file_row['is_required'] = true;
                 $file_row['file_name'] = $pca8_form_info->name;
                 $file_row['file_type'] = '';
                 $file_row['dbid'] = $pca8_form_info->id;
                 $file_row['file_description'] = '';
                 $file_row['applicant_item_id'] =  $applicantItemInfo->id;
         
         
                 $uploadedDoc =  TenderDocument::where('tender_id', $tenderId)
                                                 ->where('appication_id',$tenderApplicantId)
                                                 ->where('document_id', $pca8_form_info->id )
                                                 ->where('application_item_id', $applicantItemInfo->id)
                                                 ->orderBy('id', 'DESC')
                                                 ->first();
 
               
                 $uploadeDocStatus = @$uploadedDoc->status;

                 if($renewal_type == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

         
                 if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
         
                       
                        
                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
 
                 $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
 
         }
 
         if( $renewal_type == 'TENDER_REREGISTRATION_PCA4_PENDING' || $renewal_type == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT') {
         
         
                 $file_row = array();
                 $file_row['doc_comment'] = '';
                 $file_row['doc_status'] = 'DOCUMENT_PENDING';
                 $file_row['is_required'] = true;
                 $file_row['file_name'] = $pca9_form_info->name;
                 $file_row['file_type'] = '';
                 $file_row['dbid'] = $pca9_form_info->id;
                 $file_row['file_description'] = '';
                 $file_row['applicant_item_id'] =  $applicantItemInfo->id;
         
         
                 $uploadedDoc =  TenderDocument::where('tender_id', $tenderId)
                                                 ->where('appication_id',$tenderApplicantId)
                                                 ->where('document_id', $pca9_form_info->id )
                                                 ->where('application_item_id', $applicantItemInfo->id)
                                                 ->orderBy('id', 'DESC')
                                                 ->first();
                 $uploadeDocStatus = @$uploadedDoc->status;

                 if($renewal_type == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }


         
                 if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
                        
                       

                         $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
 
                 $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
 
         }
 
         /******pca 7 *********/
 
         $file_row = array();
         $file_row['doc_comment'] = '';
         $file_row['doc_status'] = 'DOCUMENT_PENDING';
         $file_row['is_required'] = true;
         $file_row['file_name'] = $pca7_form_info->name;
         $file_row['file_type'] = '';
         $file_row['dbid'] = $pca7_form_info->id;
         $file_row['file_description'] = '';
         $file_row['applicant_item_id'] =  $applicantItemInfo->id;
 
 
         $uploadedDoc =  TenderDocument::where('tender_id', $tenderId)
                                         ->where('appication_id',$tenderApplicantId)
                                         ->where('document_id', $pca7_form_info->id )
                                         ->where('application_item_id', $applicantItemInfo->id)
                                         ->orderBy('id', 'DESC')
                                         ->first();
         $uploadeDocStatus = @$uploadedDoc->status;

         if( ($renewal_type == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT' || $renewal_type == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT' ) && isset($uploadeDocStatus) && $uploadeDocStatus ){
                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
         }

 
         if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
                
               

                 $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
 
         $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
 
 
 
        
         
         return $generated_files;
      
      }
 
 
      function upload_reregistration(Request $request){
 
         $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
         $real_file_name = $request->fileRealName;
         $file_type_id = $request->fileTypeId;
         $tender_id = $request->tenderId; 
         $applicant_id = (int) $request->applicantId;
         $item_id = (int) $request->itemId;
         $renewal_token =  $request->token;
 
         $rrItemInfo = TenderRenewalReRegistration::where('token',$renewal_token)->first();
 
         
        
 
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
 
        $path = 'tender/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$renewal_token;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
 
       
        $token = md5(uniqid());
 
         $get_query = TenderDocument::query();
         $get_query->where('tender_id', $tender_id );
         $get_query->where('appication_id', $applicant_id);
         $get_query->where('document_id',$file_type_id);
         $get_query->where('application_item_id', $item_id );
        
         $old_doc_info = $get_query->first();
 
         $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
       
         $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
         $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;

         $query = TenderDocument::query();
         $query->where('tender_id', $tender_id );
         $query->where('appication_id', $applicant_id);
         $query->where('document_id',$file_type_id);
         $query->where('application_item_id', $item_id );
         $query->whereIn('status', array($doc_pending,$doc_req_resumbit));
         $query->delete();
         
 
        $doc = new TenderDocument;
        $doc->document_id = $file_type_id;
        $doc->path = $path;
        $doc->tender_id = $tender_id;
        $doc->appication_id = $applicant_id;
        $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $doc->file_token = $token;
        $doc->name = $real_file_name;
        $doc->application_item_id = $item_id;
        
 
        $doc->save();
        $new_doc_id = $doc->id;
 
        if( $old_doc_id ) { //update new doc id to old doc id in tenderdocument status row
          
          $update_new_id_info = array(
                  'tender_document_id' => $new_doc_id
          );
          $updated = TenderDocumentStatus::where('tender_document_id', $old_doc_id)->update($update_new_id_info);
        }
 
 
        return response()->json([
          'message' => 'File uploaded successfully.',
          'status' =>true,
          'name' =>basename($path),
          'error'  => 'no',
          'uploadDocs' =>   $this->files_for_rereg_upload(  $this->settings($rrItemInfo->status,'id')->key ,$renewal_token)
          
      ], 200);
     

      }
 
 
      function removeTenderReregisterFile( Request $request ){
 
         $tender_id = $request->tenderId;
         $applicant_id = $request->applicantId;
         $file_type_id = $request->fileTypeId;
         $item_id = $request->itemId;
         $renewal_token =  $request->token;
 
         $rrItemInfo = TenderRenewalReRegistration::where('token',$renewal_token)->first();
         
         $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        
         $query = TenderDocument::query();
         $query->where('tender_id', $tender_id );
         $query->where('appication_id', $applicant_id);
         $query->where('document_id',$file_type_id);
         $query->where('application_item_id', $item_id );
         $query->where('status',$doc_pending);
         $query->delete();
 
 
        /* TenderDocument::where('tender_id', $tender_id)
                      ->where('appication_id',$applicant_id)
                      ->where('document_id', $file_type_id)
                      ->delete();*/
 
  
         return response()->json([
                         'message' => 'File removed successfully.',
                         'status' =>true,
                         'uploadDocs' =>   $this->files_for_rereg_upload( $this->settings($rrItemInfo->status,'id')->key ,$renewal_token)
 
         ], 200);
 
         
      }


      function renewalReregNewRequest(Request $request ) {

           $item_id = $request->item_id;
           $application_id = $request->application_id;
          // $tender_id = $request->tender_id;
           $type = $request->type;
           $renewal_or_rereg = $request->renewal_or_rereg;

           if( $type == 'pca3' ) {

                $renewal_rereg_pca3 =  TenderRenewalReRegistration::where('tender_application_item_id', $item_id )
                ->where('tender_application_id' ,$application_id)
                ->where('certificate_type', $this->settings( 'CERT_TENDER_PCA3', 'key')->id)
                ->first();

                if(isset($renewal_rereg_pca3->id)) {

                        return response()->json([
                                'message' => 'Record already found.',
                                'status' =>true,
                                'token' =>   $renewal_rereg_pca3->token,
                                'renewal_or_rereg' => $renewal_or_rereg
        
                ], 200);

                } else {

                        $token = md5(uniqid());
                        
                        $new_re_rereg = new TenderRenewalReRegistration;
                        $new_re_rereg->tender_application_id = $application_id;
                        $new_re_rereg->tender_application_item_id = $item_id;
                        $new_re_rereg->token = $token;
                        $new_re_rereg->certificate_type =  $this->settings( 'CERT_TENDER_PCA3', 'key')->id;
                        
                        if($renewal_or_rereg == 'renewal') {
                                $new_re_rereg->type =  $this->settings( 'TENDER_RENEWAL', 'key')->id;
                                $new_re_rereg->status =  $this->settings( 'TENDER_RENEWAL_PCA3_PENDING', 'key')->id;
                        }
                        if($renewal_or_rereg == 'reregistration') {
                                $new_re_rereg->type =  $this->settings( 'TENDER_REREGISTRATION', 'key')->id;
                                $new_re_rereg->status =  $this->settings( 'TENDER_REREGISTRATION_PCA3_PENDING', 'key')->id;
                        }
                        $new_re_rereg->save();

                        //send mail to publisher

                        return response()->json([
                                'message' => 'Record created.',
                                'status' =>true,
                                'token' =>   $token,
                                'renewal_or_rereg' => $renewal_or_rereg
                        ], 200);

                }

           }

           if( $type == 'pca4' ) {

                $renewal_rereg_pca4 =  TenderRenewalReRegistration::where('tender_application_item_id', $item_id )
                ->where('tender_application_id' ,$application_id)
                ->where('certificate_type', $this->settings( 'CERT_TENDER_PCA4', 'key')->id)
                ->first();

                if(isset($renewal_rereg_pca3->id)) {

                        return response()->json([
                                'message' => 'Record already found.',
                                'status' =>true,
                                'token' =>   $renewal_rereg_pca4->token,
                                'renewal_or_rereg' => $renewal_or_rereg
        
                ], 200);

                } else {

                        $token = md5(uniqid());
                        
                        $new_re_rereg = new TenderRenewalReRegistration;
                        $new_re_rereg->tender_application_id = $application_id;
                        $new_re_rereg->tender_application_item_id = $item_id;
                        $new_re_rereg->token = $token;
                        $new_re_rereg->certificate_type =  $this->settings( 'CERT_TENDER_PCA4', 'key')->id;
                        
                        if($renewal_or_rereg == 'renewal') {
                                $new_re_rereg->type =  $this->settings( 'TENDER_RENEWAL', 'key')->id;
                                $new_re_rereg->status =  $this->settings( 'TENDER_RENEWAL_PCA4_PENDING', 'key')->id;
                        }
                        if($renewal_or_rereg == 'reregistration') {
                                $new_re_rereg->type =  $this->settings( 'TENDER_REREGISTRATION', 'key')->id;
                                $new_re_rereg->status =  $this->settings( 'TENDER_REREGISTRATION_PCA4_PENDING', 'key')->id;
                        }
                        $new_re_rereg->save();

                         //send mail to publisher

                        return response()->json([
                                'message' => 'Record created.',
                                'status' =>true,
                                'token' =>   $token,
                                'renewal_or_rereg' => $renewal_or_rereg
                        ], 200);

                }

           }


           return response()->json([
                'message' => 'Failed.',
                'status' =>false,
                'token' =>   null,
                'renewal_or_rereg' => $renewal_or_rereg

                ], 200);

           
         

      }




}
