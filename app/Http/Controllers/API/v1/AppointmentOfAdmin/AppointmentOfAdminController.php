<?php
namespace App\Http\Controllers\API\v1\AppointmentOfAdmin;
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
use App\CompanyStatus;
use Storage;
use Cache;
use App;
use URL;
use App\Http\Helper\_helper;
use PDF;
use App\CompanyChangeRequestItem;
use App\CompanyItemChange;

use App\AppointmentOfAdmins;
use App\AppointedAdmins;

use App\Province;
use App\District;
use App\City;
use App\CourtCase;
use App\UserAttachedCompanies;

class AppointmentOfAdminController extends Controller
{
    use _helper;
    

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

        return array(
            'provinces' => $provinces_cache,
            'districts' => $districts_cache,
            'cities' => $cities_cache,
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

        $request_id = $this->valid_request_operation($request->companyId);

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);

       
        $RegisterOfChargesRecord =  AppointmentOfAdmins::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();

            
        $moduleStatus = $this->settings($RegisterOfChargesRecord->status,'id')->key;

        if( !( $moduleStatus === 'APPOINTMENT_OF_ADMIN_PROCESSING' ||  $moduleStatus === 'APPOINTMENT_OF_ADMIN_RESUBMIT' ) ) {

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

        /*if($loginUserInfo->id  != $company_info->created_by ) {
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

        

        /****** record list *****/
        $record_count =0;

        $record_list_count = AppointedAdmins::where('status',$this->settings('APPOINTMENT_OF_ADMIN','key')->id)
                                            ->where('appointment_record_id',$RegisterOfChargesRecord->id)
                                            ->count();
        $record_list = AppointedAdmins::where('status',$this->settings('APPOINTMENT_OF_ADMIN','key')->id)
                                            ->where('appointment_record_id',$RegisterOfChargesRecord->id)
                                            ->get();

                                           

                                           
        

        $records = array();
        if($record_list_count) {
            foreach($record_list as $sr){

                $record_count++;      
                
                $address ='';
                $forAddress = '';
                $officeAddress = '';
                if( $sr->address_id) {
                   $address = Address::where('id',$sr->address_id)->first();
                }
                if( $sr->foreign_address_id) {
                   $forAddress = Address::where('id', $sr->foreign_address_id)->first();
                }
                if($sr->office_address_id) {
                    $officeAddress = Address::where('id', $sr->office_address_id)->first();
                }
        
                $rec = array(
                'id' => $sr->id,
                'full_name' =>  $sr->full_name,
                'id' => $sr['id'],
                'type' => ($sr->is_srilankan  =='yes' ) ? 'local' : 'foreign',
                'firstname' => $sr->first_name,
                'lastname' => $sr->last_name,
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


                'officeProvince' =>  ( isset($officeAddress->province) &&  $officeAddress->province) ? $officeAddress->province : '',
                'officeDistrict' =>  ( isset($officeAddress->district) && $officeAddress->district) ? $officeAddress->district : '',
                'officeCity' =>  ( isset($officeAddress->city) && $officeAddress->city) ? $officeAddress->city : '',
                'officeAddress1' => ( isset($officeAddress->address1) && $officeAddress->address1) ? $officeAddress->address1 : '',
                'officeAddress2' => ( isset($officeAddress->address2) && $officeAddress->address2) ? $officeAddress->address2 : '',
                'officePostcode' => (isset($officeAddress->postcode) && $officeAddress->postcode) ? $officeAddress->postcode : '',

                'nic'       => $sr->nic,
                'passport'  => $sr->passport_no,
                'country'  => @( $sr->foreign_address_id)  ? @$forAddress->country : @$address->country,
                'passport_issued_country'   => $sr->passport_issued_country,
                'date'      => '1970-01-01' == $sr->date_of_appointment ? null : $sr->date_of_appointment,
                'phone' => $sr->tel,
                'mobile' => $sr->mobile,
                'email' => $sr->email,
                'appointed_by' => $sr->appointed_by,
                'resolution_date' => $sr->resolution_date,
                'court_name' => $sr->court_name,
                'court_case_no' => $sr->court_case_no,
                'court_date' => $sr->court_date,
                'court_penalty' => $sr->court_penalty,
                'court_period' => $sr->court_period,
                'court_discharged' => $sr->court_discharged,
               
                );
            

                $records[] = $rec;
            }

        }

        //print_r($request_id);
       // die();


        $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

        $postfix_values = $this->getPostfixValues($company_info->postfix);

        $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
         $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

         $external_global_comment = '';


         $form_34 = Documents::where('key', 'FORM_34')->first();
         $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
     
               
         $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                         ->where('comment_type', $external_comment_type_id )
                                                         ->where('request_id', $request_id)
                                                         ->orderBy('id', 'DESC')
                                                         ->first();
        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                           ?  $external_comment_query->comments
                                           : '';

        $director_list = CompanyMember::where('company_id',$request->companyId)
                                           ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                           ->where('status',1)
                                           ->orderBy('id','ASC')
                                           ->get();
        $directors = [];
        $savedDirectorsNames =[];
        $saved_list = $RegisterOfChargesRecord->directors;
        $saved_list_arr = explode(',', $saved_list);
        foreach($director_list as $director) {

            $row = array();
            $row['first_name'] = $director->first_name;
            $row['last_name'] = $director->last_name;
            $row['id'] = $director->id;
            $row['saved'] = in_array($director->id, $saved_list_arr) ? true : false;

            $directors[] = $row;

            if(in_array($director->id, $saved_list_arr)){
                $savedDirectorsNames[] = $director->first_name . ' ' . $director->last_name;
            }
        }

        $company_address = Address::where('id',$company_info->address_id)->first();

        if( isset( $company_info->foreign_address_id) && $company_info->foreign_address_id ) {
            $company_for_address = Address::where('id',$company_info->foreign_address_id)->first();
        }else {
            $company_for_address = '';
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
                        'records' => $records,
                        'court_data' => $court_data_arr,
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'admin_office_address1' => $RegisterOfChargesRecord->admin_office_address1,
                        'admin_office_address2' => $RegisterOfChargesRecord->admin_office_address2,
                        'admin_office_address3' => $RegisterOfChargesRecord->admin_office_address3,
                        'appointed_by' => $RegisterOfChargesRecord->appointed_by,
                        
                        'court_date' => ($RegisterOfChargesRecord->appointed_by == 'court_order' ) ? $RegisterOfChargesRecord->court_date : '',
                        'resolution_date' => ($RegisterOfChargesRecord->appointed_by == 'resolution' ) ? $RegisterOfChargesRecord->resolution_date : '',
                        'court_name' => ($RegisterOfChargesRecord->appointed_by == 'court_order' ) ? $RegisterOfChargesRecord->court_name : '',
                        'court_case_no' => ($RegisterOfChargesRecord->appointed_by == 'court_order' ) ? $RegisterOfChargesRecord->court_case_no : '',
                        'court_penalty' => ($RegisterOfChargesRecord->appointed_by == 'court_order' ) ? $RegisterOfChargesRecord->court_penalty : '',
                        'court_period' => ($RegisterOfChargesRecord->appointed_by == 'court_order' ) ? $RegisterOfChargesRecord->court_period : '',
                        'court_discharged' => ($RegisterOfChargesRecord->appointed_by == 'court_order' ) ? $RegisterOfChargesRecord->court_discharged : '',

                        'external_global_comment' => $external_global_comment,
                        'directorList' => $directors,
                        'pdc' => $this->getProvincesDisctrictsCities(),
                        'countries'     => Cache::rememberForever('countries_cache', function () {
                            return Country::all();
                        }),
                        'penalty_charge' =>  $this->getPanaltyCharge($RegisterOfChargesRecord->id),

                        'downloadDocs' => $this->generate_report($request->companyId,array(

                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'certificate_no' => $certificate_no,
                            'companyType' => $this->settings($company_info->type_id,'id'),
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'records' => $records,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                            'admin_office_address1' => $RegisterOfChargesRecord->admin_office_address1,
                            'admin_office_address2' => $RegisterOfChargesRecord->admin_office_address2,
                            'admin_office_address3' => $RegisterOfChargesRecord->admin_office_address3,
                            'date_of' => $RegisterOfChargesRecord->date_of,
                            'court_date' => $RegisterOfChargesRecord->court_date,
                            'appointed_by' => $RegisterOfChargesRecord->appointed_by,
                            'savedDirectorsNames' => $savedDirectorsNames
                           
                        )),
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                        'form34_payment' => $this->settings('PAYMENT_APPOINTMENT_OF_ADMIN_FORM34','key')->value,
                        'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                        'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                        'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                        )
                ], 200);
          
    }

    private function getPanaltyCharge($record_id) {


        $adminsWhoHasResolutionCount = AppointedAdmins::where('appointment_record_id', $record_id)
                                    //->where('appointed_by', 'resolution')
                                    ->count();
        


        if(!$adminsWhoHasResolutionCount) {
            return 0;
        }

        $min_date_gap = 10;
        $increment_gap_dates = 30;
        $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_34_INITIAL','key')->value );
        $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_34_INCREMENT','key')->value );
        $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_34_MAX','key')->value );

        $increment_gaps = 0;

        $penalty_value = 0;

        $today = time();

        $adminsWhoHasResolution = AppointedAdmins::where('appointment_record_id', $record_id)
       // ->where('appointed_by', 'resolution')
        ->get();
        $resoultion_dates_arr = array();

        
        /************changed penalty code */
        $date_arr = array();

        foreach($adminsWhoHasResolution as $rec ) {

            if($rec->appointed_by === 'court_order') { 
                $res_date = strtotime($rec->court_date);
            }else {
                $res_date = strtotime($rec->resolution_date);
            }

            $date_arr[] = $res_date;
        }
        $res_date = min($date_arr);


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

       /************end changed penalty code */


      /*  foreach($adminsWhoHasResolution as $rec ) {

           if($rec->appointed_by === 'court_order') { 
            $res_date = strtotime($rec->court_date);
           }else {
            $res_date = strtotime($rec->resolution_date);
           }

            if( $res_date )  {

                $date_gap =  intval( ($today - $res_date) / (24*60*60) );
    
                if($date_gap < $min_date_gap ) {
                    continue;
                }
    
                $increment_gaps = ( $date_gap % $increment_gap_dates == 0 ) ? $date_gap / $increment_gap_dates : intval($date_gap / $increment_gap_dates) + 1;
                $penalty_value  = $penalty_value + $init_panalty;
    
                if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                    $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
                }
    
            }


        }
        
       return ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value; */
    

    }

    function updateCourtRecords(Request $request ) {
        $company_id = $request->companyId;

        $request_id = $this->valid_request_operation($company_id);

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


    function uploadedDocs($companyId){
          
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

  private function has_request_record($company_id) {

    $accepted_request_statuses = array(
        $this->settings('APPOINTMENT_OF_ADMIN_APPROVED','key')->id,
        $this->settings('APPOINTMENT_OF_ADMIN_REJECTED','key')->id
    );
   
    $record_count = AppointmentOfAdmins::where('company_id', $company_id)
                              ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count === 1 ) {
        $record = AppointmentOfAdmins::where('company_id', $company_id)
        ->whereNotIn('status', $accepted_request_statuses )
         ->first();

        return $record->request_id;
    } else {
        return false;
    }
}

  private function valid_request_operation($company_id){

  
    $accepted_request_statuses = array(
        $this->settings('APPOINTMENT_OF_ADMIN_APPROVED','key')->id,
        $this->settings('APPOINTMENT_OF_ADMIN_REJECTED','key')->id
    );
    $request_type =  $this->settings('APPOINTMENT_OF_ADMIN','key')->id;

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

            $user = $this->getAuthUser();
            $company_info = Company::where('id', $company_id)->first();
            $year = date('Y',time());

            $request = new CompanyChangeRequestItem;
            $request->company_id = $company_id;
            $request->request_type = $request_type;
            $request->status = $this->settings('APPOINTMENT_OF_ADMIN_PROCESSING','key')->id;
            $request->request_by = $user->userid;
            $request->save();
            $request_id =  $request->id;

            $record = new AppointmentOfAdmins;
            $record->request_id = $request_id;
            $record->company_id = $company_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('APPOINTMENT_OF_ADMIN_PROCESSING','key')->id;
            $record->save();
            $record_id =  $record->id;

            if($record_id && $request_id ) {
                return $request_id;
            }else{
                return false;
            }

    }
    
}

  function generate_report($company_id, $info_array=array()){

    $generated_files = array(
          'docs' => array(),
    );
    $request_id = $this->valid_request_operation($company_id);

    if(!$request_id) {
        return $generated_files;
    }
  
    $file_name_key = 'form34';
    $file_name = 'FORM 34';


    $data = $info_array;
                  
    $directory = "appointment-of-admins/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form34';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");
    $generated_files['docs'][] = $file_row;

    return $generated_files;
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
        
        $request_type =  $this->settings('APPOINTMENT_OF_ADMIN','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();
        $RegisterOfChargesRecord =  AppointmentOfAdmins::where('company_id', $company_id)
                               ->where('request_id', $request_id)
                                ->first();

      
        // documents list
        $form_34 = Documents::where('key', 'FORM_34')->first();
        $form_other_docs = Documents::where('key', 'APPOINTMENT_OF_ADMIN_OTHER_DOCUMENTS')->first();
        $form_34_resolution_letter = Documents::where('key', 'FORM_34_RESOLUTION_LETTER')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_34->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_34->id;
        $file_row['file_description'] = $form_34->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_34->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'APPOINTMENT_OF_ADMIN_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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



        /*if($RegisterOfChargesRecord->appointed_by == 'resolution' &&  $RegisterOfChargesRecord->resolution_date) {
            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $form_34_resolution_letter->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $form_34_resolution_letter->id;
            $file_row['file_description'] = $form_34_resolution_letter->description;
            $file_row['applicant_item_id'] = null;
            $file_row['member_id'] = null;
            $file_row['request_id'] = $request_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                            ->where('request_id',$request_id)
                                            ->where('document_id', $form_34_resolution_letter->id )
                                            ->orderBy('id', 'DESC')
                                            ->first();
            $uploadeDocStatus = @$uploadedDoc->status;
            if($request->status == 'APPOINTMENT_OF_ADMIN_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        }*/


        //other documents (those are ususally visible on requesting by the admin )
        $regChargeGroup = DocumentsGroup::where('request_type', 'APPOINTMENT_OF_ADMIN')->first();
        $regChargeDocuments = Documents::where('document_group_id', $regChargeGroup->id)
                                           // ->where('key', '!=' , 'FORM_34')
                                            ->get();
        $regChargeDocumentsCount = Documents::where('document_group_id', $regChargeGroup->id)
                                               // ->where('key', '!=' , 'FORM_34')
                                                ->count();

        if($regChargeDocumentsCount){
            foreach($regChargeDocuments as $other_doc ) {

                if($form_34->id === $other_doc->id || $form_34_resolution_letter->id === $other_doc->id || $form_other_docs->id === $other_doc->id ) {
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
                if($request->status == 'APPOINTMENT_OF_ADMIN_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    

    function submitRecords(Request $request){

     
        
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

        $loginUserEmail = $this->clearEmail($request->loginUser);
        $direcotrList = array();

        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');

        $record = AppointmentOfAdmins::where('company_id', $company_id)
        ->where('request_id', $request_id)
         ->first();

         if(!isset($record->id)) { 

            return response()->json([
                'message' => 'Invalid Request having empty record.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }


        $call_count = AppointedAdmins::where('status',$this->settings('APPOINTMENT_OF_ADMIN','key')->id)
                                                ->where('appointment_record_id', $record->id)
                                                ->count();
        if($call_count){
            $calls = AppointedAdmins::where('status',$this->settings('APPOINTMENT_OF_ADMIN','key')->id)
                                                ->where('appointment_record_id', $record->id)
                                                ->get();
            foreach($calls as $d ) {
                
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('APPOINTMENT_OF_ADMIN_TABLE','key')->id)
                 ->delete();
                 AppointedAdmins::where('id', $d->id)
                             ->where('status', $this->settings('APPOINTMENT_OF_ADMIN','key')->id)
                             ->delete();
            }

        }
       // end remore part

      

        //loop through add director list
        foreach($request->records['record'] as $sr ){

            $addressId= null;
            $forAddressId = null;
            $officeAddressId = null;
            
            if($sr['province'] && $sr['district'] &&  $sr['city'] && $sr['localAddress1']  && $sr['postcode'] ) {
             $address = new Address;
           //  $address->id = 9999;
             $address->province = $sr['province'];
             $address->district =  $sr['district'];
             $address->city =  $sr['city'];
             $address->address1 =  $sr['localAddress1'];
             $address->address2 =  $sr['localAddress2'];
             $address->postcode = $sr['postcode'];
             $address->country =   'Sri Lanka';
           
             $address->save();
             $addressId = $address->id;
            }

            if($sr['forProvince'] &&  $sr['forCity'] && $sr['forAddress1'] && $sr['forPostcode'] ) {
             $forAddress = new Address;
           //  $address->id = 9999;
             $forAddress->province = $sr['forProvince'];
             $forAddress->district = null;
             $forAddress->city =  $sr['forCity'];
             $forAddress->address1 =  $sr['forAddress1'];
             $forAddress->address2 =  $sr['forAddress2'];
             $forAddress->postcode = $sr['forPostcode'];
             $forAddress->country =  $sr['country'];
           
             $forAddress->save();
             $forAddressId = $forAddress->id;
            }

            if($sr['officeProvince'] && $sr['officeDistrict'] &&  $sr['officeCity'] && $sr['officeAddress1']  && $sr['officePostcode'] ) {
                $address = new Address;
              //  $address->id = 9999;
                $address->province = $sr['officeProvince'];
                $address->district =  $sr['officeDistrict'];
                $address->city =  $sr['officeCity'];
                $address->address1 =  $sr['officeAddress1'];
                $address->address2 =  $sr['officeAddress2'];
                $address->postcode = $sr['officePostcode'];
                $address->country =   'Sri Lanka';
              
                $address->save();
                $officeAddressId = $address->id;
               }

            $newSr = new AppointedAdmins;
            $newSr->first_name = $sr['firstname'];
            $newSr->last_name = $sr['lastname'];
            $newSr->address_id = $addressId;
            $newSr->foreign_address_id =  $forAddressId;
            $newSr->office_address_id =  $officeAddressId;
            $newSr->is_srilankan =  $sr['type'] != 'local' ?  'no' : 'yes';
            $newSr->nic = strtoupper($sr['nic']);
            $newSr->passport_no = $sr['passport'];
            $newSr->passport_issued_country = isset( $sr['passport_issued_country']) ? $sr['passport_issued_country'] : $sr['country'];
            $newSr->tel = $sr['phone'];
            $newSr->mobile =$sr['mobile'];
            $newSr->email = $sr['email'];
           // $newSr->date_of_appointment = date('Y-m-d',strtotime($sr['date']) );
            $newSr->status =  $this->settings('APPOINTMENT_OF_ADMIN','key')->id;
            $newSr->appointment_record_id =  $record->id;
            $newSr->appointed_by = $sr['appointed_by'];
            $newSr->resolution_date = ( 'resolution' == $sr['appointed_by'] ) ? date('Y-m-d',strtotime($sr['resolution_date']) ) : null;
            $newSr->court_name = ( 'court_order' == $sr['appointed_by'] ) ? $sr['court_name']  : null;
            $newSr->court_case_no = ( 'court_order' == $sr['appointed_by'] ) ? $sr['court_case_no']  : null;
            $newSr->court_date = ( 'court_order' == $sr['appointed_by'] ) ? date('Y-m-d',strtotime($sr['court_date']) )  : null;
            $newSr->court_penalty = ( 'court_order' == $sr['appointed_by'] ) ? floatval($sr['court_penalty'])  : null;
            $newSr->court_period = ( 'court_order' == $sr['appointed_by'] ) ? $sr['court_period']  : null;
            $newSr->court_discharged = ( 'court_order' == $sr['appointed_by'] ) ? $sr['court_discharged']  : null;
            $newSr->save();
            $new_sr_id = $newSr->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $new_sr_id;
            $change->item_table_type = $this->settings('APPOINTMENT_OF_ADMIN_TABLE','key')->id;
            $change->save();
            $change_id = $change->id;

      }

      $director_arr = array();
      foreach($request->directors['director'] as $director ){

         if($director['saved']) {
             $director_arr[] = $director['id'];
         }

      }

      $share_summery = array(
      //  'admin_office_address1' => $request->admin_office_address1 ? $request->admin_office_address1 : null,
    //    'admin_office_address2' => $request->admin_office_address2 ? $request->admin_office_address2 : null,
      //  'admin_office_address3' => $request->admin_office_address3 ? $request->admin_office_address3 : null,
      //  'court_date' => $request->appointed_by == 'court_order' ? $request->court_date : null,
      //  'resolution_date' => $request->appointed_by == 'resolution' ? $request->resolution_date : null,
     //   'appointed_by' => $request->appointed_by,
   //  'court_name' => $request->appointed_by == 'court_order' ? $request->court_name : null,
      //  'court_case_no' => $request->appointed_by == 'court_order' ? $request->court_case_no : null,
      //  'court_penalty' => $request->appointed_by == 'court_order' ? floatval($request->court_penalty) : null,
      //  'court_period' => $request->appointed_by == 'court_order' ? $request->court_period : null,
       // 'court_discharged' => $request->appointed_by == 'court_order' ? $request->court_discharged : null,
        'directors' => implode(',', $director_arr)
    );
    AppointmentOfAdmins::where('company_id', $company_id)
    ->where('request_id', $request_id)
     ->update($share_summery);
     

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
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
        $form_other_docs = Documents::where('key', 'APPOINTMENT_OF_ADMIN_OTHER_DOCUMENTS')->first();
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
            if($company_status == 'APPOINTMENT_OF_ADMIN_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
    
        if( $size >= 1024 * 1024 * 4) {
    
             return response()->json([
                 'message' => 'You can upload document only up to 4 MB.',
                 'status' =>false,
                 'error'  => 'yes'
                 
                 
             ], 200);
        }
    
        $path = 'appointment-of-admins/other-docs/'.substr($company_id,0,2);
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
    
        $path = 'appointment-of-admins/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'APPOINTMENT_OF_ADMIN_OTHER_DOCUMENTS')->first();


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

        $registerOfChargesRecord =  AppointmentOfAdmins::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($registerOfChargesRecord->status) && $registerOfChargesRecord->status === $this->settings('APPOINTMENT_OF_ADMIN_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Call on Shares Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = AppointmentOfAdmins::where('request_id', $request_id)->update(['status' => $this->settings('APPOINTMENT_OF_ADMIN_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('APPOINTMENT_OF_ADMIN_RESUBMITTED', 'key')->id]);

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
        
            if( $size > 1024 * 1024 * 4) {
        
                 return response()->json([
                     'message' => 'File size should be less than 4 MB.',
                     'status' =>false,
                     'error'  => 'yes'
                     
                     
                 ], 200);
            }
        
            $path = 'appointment-of-admins/'.substr($company_id,0,2).'/'.$request_id;
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



    function checkCompanyByRegNumber(Request $request){
        $regNumber = $request->registration_no;

        $user = $this->getAuthUser();

        if($user->stakeholder_role  != $this->settings('ADMINISTRATOR', 'key')->id) {
            return response()->json(['status'=> false, 'message'=> 'Invalid user role!!','company_name' => null, 'company_id' =>  null], 200);
         }
       
        if(!$regNumber) {
            return response()->json(['status'=> false, 'message'=> 'Inavlid Company Registration Number', 'company_name' => '','company_id' =>  null], 200);
        }

        $regInfo = CompanyCertificate::where('registration_no', strtoupper(trim($regNumber)) )->first();

        if(isset($regInfo->company_id)) {

            $registered_companies = $this->get_admin_regietered_companies();

            $companyInfo = Company::where('id', $regInfo->company_id)->first();

            if( in_array($regInfo->company_id, $registered_companies)) {
                return response()->json(['status'=> false, 'message'=> 'You have already an administrator of this company', 'company_name' => null, 'company_id' =>  null], 200);
            }

            return response()->json(['status'=> true, 'message'=> 'Company successfully added', 'company_name' => $companyInfo->name,'company_id' =>  $companyInfo->id], 200);
        }else{
            return response()->json(['status'=> false, 'message'=> 'No companies found under this registration number', 'company_name' => null, 'company_id' =>  null], 200);
        }

    }

    private function get_admin_regietered_companies() {
        $user = $this->getAuthUser();
        $companies = UserAttachedCompanies::where('user_id', $user->userid)->pluck('company_id')->toArray();

        return $companies;
    }

    function addCompaniesToAdminProfile(Request $request ) {

         $assignedCompanies = $request->assignedCompanies;
        
         $user = $this->getAuthUser();

         if($user->stakeholder_role  != $this->settings('ADMINISTRATOR', 'key')->id) {
            return response()->json(['status'=> false, 'message'=> 'Invalid user role!!'], 200);
         }

        if( isset($assignedCompanies) && is_array($assignedCompanies ) && count($assignedCompanies)) {
            foreach($assignedCompanies as $company_id) {

                $attach  = new UserAttachedCompanies;
                $attach->user_id = $user->userid;
                $attach->company_id = $company_id;
                $attach->save();
            }
         }

         return response()->json(['status'=> true, 'message'=> 'Successfully attach companies to your profile'], 200);
    }


} // end class