<?php
namespace App\Http\Controllers\API\v1\IssueofShares;
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
use App\Province;
use App\District;
use App\City;
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
use App\AnnualReturn;
use App\ShareRegister;
use App\AnnualRecords;
use App\AnnualAuditors;
use App\AnnualCharges;

use App\ShareClasses;
use App\ShareIssueRecords;
use App\CourtCase;
use App\ShareIssueHistory;

class IssueOfSharesController extends Controller
{
    use _helper;



    private function getPanaltyCharge( $company_id , $request_id ) {



        $record = ShareClasses::where('request_id', $request_id)->first();

      //  print_r($record);

        $call_records = ShareIssueRecords::where('record_id', $record->id )->get();

      //  print_r($call_records);
     //   die();

        $obligation_date_arr = array();

        $res_date = '';

        if(isset($call_records[0]->id)) {
            foreach($call_records as $c ) {

                if($c->date_of_issue) {
                    $obligation_date_arr[] = strtotime($c->date_of_issue);
                }

            }
            $res_date = min($obligation_date_arr);
        }
       
    
        $penalty_value = 0;
       

        $min_date_gap = 20;
        $increment_gap_dates = 30;
        $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_6_INITIAL','key')->value );
        $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_6_INCREMENT','key')->value );
        $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_6_MAX','key')->value );
    
    
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

    private function shareRecordsList($companyId, $request_id) {
        $callonSharesRecord =  ShareClasses::where('company_id', $companyId)
        ->where('request_id', $request_id)
         ->first();

         $share_call_records = ShareIssueRecords::where('status',$this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                                            ->where('record_id', $callonSharesRecord->id)
                                            ->get();
        $shareRecorList = array();

        if(isset($share_call_records[0]->id)) {
            foreach($share_call_records as $s ) {
                $row = array(
                    'id' => $s->id,
                    'class' => $s->share_class == 'OTHER_SHARE' ? $s->share_class_other : $this->settings($s->share_class,'key')->value,
                    'issued' => floatval($s->no_of_shares_as_cash) + floatval($s->no_of_shares_as_non_cash)
                );
                $shareRecorList[] = $row;
            }
        }

        return $shareRecorList;
    }

    private function shareClasses($companyId, $request_id ) {

        $callonSharesRecord =  ShareClasses::where('company_id', $companyId)
            ->where('request_id', $request_id)
             ->first();

        $share_types = $this->settings('SHARE_TYPES');
       
        foreach($share_types as $type ) {

            if($type->key == 'OTHER_SHARE'){
                continue;
            }
            $share_call_record_count = ShareIssueRecords::where('status',$this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                                            ->where('record_id', $callonSharesRecord->id)
                                            ->where('share_class', $type->key)
                                            ->count();
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

        $request_id = $this->valid_calls_on_shares_request_operation($request->companyId);

      
        $update_compnay_updated_at = array(
           'updated_at' => date('Y-m-d H:i:s', time())
       );
       Company::where('id', $request->companyId)
       ->update($update_compnay_updated_at);


        $callonSharesRecord =  ShareClasses::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        $moduleStatus = $this->settings($callonSharesRecord->status,'id')->key;

        if( !( $moduleStatus === 'COMPANY_ISSUE_OF_SHARES_PROCESSING' ||  $moduleStatus === 'COMPANY_ISSUE_OF_SHARES_REQUEST_TO_RESUBMIT' ) ) {

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

        
        $shareholders = array();
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
            foreach($shareholder_list as $sh ) {
 
                $shareholders[] = array(
                    'id' => $sh->id,
                    'name' => $sh->first_name. ' '. $sh->last_name
                );
            }

        }

        $shareholder_firms = array();
        $shareholder_firm_list_count = CompanyFirms::where('company_id',$request->companyId)
          ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
          ->where('status',1)
          ->count();
 
        if($shareholder_firm_list_count) {
             $shareholder_firm_list = CompanyFirms::where('company_id',$request->companyId)
                 ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
                 ->where('status',1)
                 ->get();
                 foreach($shareholder_firm_list as $sh ) {
                
                    $shareholder_firms[] = array(
                        'id' => $sh->id,
                        'name' => $sh->name. '( '. $sh->registration_no. ')'
                    );
                
                }
          }


        /******share record list *****/
        $record_count =0;
        $share_call_record_list = ShareIssueRecords::where('status',$this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                                            ->where('record_id', $callonSharesRecord->id)
                                            ->get();
        
        

        $shareCalls = array();
        foreach($share_call_record_list as $sr){

            $record_count++;                     
    
            $rec = array(
            'id' => $sr['id'],
            'share_class' =>  $sr->share_class,
            'share_class_other' => $sr->share_class_other,
            'is_issue_type_as_cash' =>$sr->is_issue_type_as_cash,
            'no_of_shares_as_cash' => $sr->no_of_shares_as_cash,
            'consideration_of_shares_as_cash' =>  $sr->consideration_of_shares_as_cash,
            'is_issue_type_as_non_cash' =>  $sr->is_issue_type_as_non_cash,
            'no_of_shares_as_non_cash' =>  $sr->no_of_shares_as_non_cash,
            'consideration_of_shares_as_non_cash' =>  $sr->consideration_of_shares_as_non_cash,
            'date_of_issue' => $sr->date_of_issue,
            'selected_share_class_name' => ($sr->share_class == 'OTHER_SHARE') ? $sr->share_class_other : $this->settings($sr->share_class,'key')->value
            );
         
            $shareCalls[] = $rec;
        }

        
     

        $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

        $postfix_values = $this->getPostfixValues($company_info->postfix);

        $companyCertificate = CompanyCertificate::where('company_id', $request->companyId)
                                              ->where('is_sealed', 'yes')
                                              ->first();
        $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

       
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

       $stakeholder_info = array(
           'type' => '',
           'name' => ''
       );
      if($callonSharesRecord->signing_party_designation == 'Director') {
             $stakeholder_info = CompanyMember::where('id', $callonSharesRecord->signed_party_id)->first();
             $stakeholder_info = array(
                   'type' => 'Director',
                   'name' => isset($stakeholder_info->id) ? $stakeholder_info->first_name. ' ' . $stakeholder_info->last_name : ''
             );
       } 
       if($callonSharesRecord->signing_party_designation == 'Secretary') {
           $stakeholder_info = CompanyMember::where('id', $callonSharesRecord->signed_party_id)->first();
           $stakeholder_info = array(
                 'type' => 'Secretary',
                 'name' => isset($stakeholder_info->id) ? $stakeholder_info->first_name. ' ' . $stakeholder_info->last_name : ''
           );
      } 
      if($callonSharesRecord->signing_party_designation == 'Secretary Firm') {
           $stakeholder_info = CompanyFirms::where('id', $callonSharesRecord->signed_party_id)->first();
           $stakeholder_info = array(
               'type' => 'Secretary Firm',
               'name' => isset($stakeholder_info->id) ? $stakeholder_info->name : ''
           );
      }

       $penalty_charges = $this->getPanaltyCharge($request->companyId, $request_id);
    //   $penalty_charges = 0;

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

        $shareholder_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                ->whereIn('status', array(1,  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id))
                ->whereNull('company_member_firm_id' )
            //    ->where('status',1)
            ->orderBy('status','ASC')
                ->get();
        $shareholdersList = array();
        if(isset($shareholder_list[0]->id )) {

            foreach($shareholder_list as $shareholder){
    
               
    
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
             $groupAddedValue = 0;
    
            if(isset($shareRow->company_member_id ) && $shareRow->company_member_id ){
    
                $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();
    
                $shareRecord['type'] = $shareGroupInfo['type'];
                $shareRecord['name'] = $shareGroupInfo['name'];
                $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
                $shareRecord['sharegroupName'] = $shareGroupInfo['name'];
                $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];
                $shareRecord['current_shares'] = null;
                $shareRecord['new_shares'] = null;
               // $shareRecord['rec'] =  $shareGroupInfo;

            //    if($shareholder->status == 1 ) {
                    $shareRecord['current_shares'] = $shareGroupInfo['current_shares'];
                    $shareRecord['new_shares'] = $shareGroupInfo['new_shares'];
                    $groupAddedValue = intval($shareGroupInfo['new_shares']) - intval($shareGroupInfo['current_shares']);
                    $groupAddedValue = ($groupAddedValue > 0 ) ? $groupAddedValue : 0;
           //     }
               
    
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
            'groupAddedValue' => $groupAddedValue,
            'shareholderType' => 'natural',
            'status' => $shareholder->status,
            'shareIssueClass' => ($shareholder->issued_share_class) ? $shareholder->issued_share_class : ''
            );
            $shareholdersList[] = $rec;
            }

        }

        $shareholder_list = CompanyFirms::where('company_id',$request->companyId)
        ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
        ->whereIn('status', array(1,  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id))
        // ->where('status',1)
        ->orderBy('status','ASC')
        ->get();
        $shareholderFirmList = [];
        if(isset($shareholder_list[0]->id)) {
            $sh_firm_count = 0;

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
              $groupAddedValue = 0;
    
             if(isset($shareRow->company_firm_id ) && $shareRow->company_firm_id ){
    
                 $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();
                 $shareRecord['type'] = $shareGroupInfo['type'];
                 $shareRecord['name'] = $shareGroupInfo['name'];
                 $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
                 $shareRecord['sharegroupName'] = $shareGroupInfo['name'];
                 $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];
                 $shareRecord['current_shares'] = null;
                 $shareRecord['new_shares'] = null;

               //  if($shareholder->status == 1 ) {
                    $shareRecord['current_shares'] = $shareGroupInfo['current_shares'];
                    $shareRecord['new_shares'] = $shareGroupInfo['new_shares'];
                    $groupAddedValue = intval($shareGroupInfo['new_shares']) - intval($shareGroupInfo['current_shares']);
                    $groupAddedValue = ($groupAddedValue > 0 ) ? $groupAddedValue : 0;
              //  }
     
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
             'country' =>  isset( $address->country) && $address->country ? $address->country : '',
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
             'groupAddedValue' => $groupAddedValue,
             'benifiList' => array('ben' => $benifListArr ),
             'shareholderType' => 'firm',
             'status' => $shareholder->status,
             'shareIssueClass' => ($shareholder->issued_share_class) ? $shareholder->issued_share_class : ''
             );
             $shareholderFirmList[] = $rec;
             }

        }
        $countries_cache = Cache::rememberForever('countries_cache', function () {
            return Country::all();
        });

        $core_groups_list = array();
      $core_groups = ShareGroup::where('type','core_share')
                                  ->where('company_id', $request->companyId )
                                ->get();
      if(isset($core_groups[0]->id) && $core_groups[0]->id){
          foreach($core_groups as $g ){
        
          $grec = array(
              'group_id' => $g->id,
              'group_name' => "$g->name ($g->no_of_shares)"
          );
          $core_groups_list[] = $grec;
        }
      }

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
                        'share_calls' => $shareCalls,
                        'court_data' => $court_data_arr,
                        'public_path' =>  storage_path(),
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'shareholders' => $shareholders,
                        'shareholdersList' => $shareholdersList,
                        'shareholderFirmList' => $shareholderFirmList,
                        'shareholder_list_count' => $shareholder_list_count,
                        'shareholder_firms' => $shareholder_firms,
                        'shareholder_firm_list_count' => $shareholder_firm_list_count,
                        'callonSharesRecord' => $callonSharesRecord,
                        'external_global_comment' => $external_global_comment,
                        'directors' =>$directors,
                        'secs' => $secs,
                        'sec_firms' =>$sec_firms,
                        'penalty_value' => $penalty_charges,
                        'shareTypes' => $this->shareClasses($request->companyId,$request_id),
                        'shareRecordsList' => $this->shareRecordsList($request->companyId,$request_id),
                        'cpd' => $this->getProvincesDisctrictsCities(),
                        'countries' => $countries_cache,
                        'coreShareGroups' => $core_groups_list,
                        'shareholder_bulk_format' => asset('other/shareholder-details-sample-format.xlsx'),
                        'example_member_bulk_data' => asset('other/shareholder-details-dummy-data.csv'),

                        'downloadDocs' => $this->generate_calls_on_shares_report($request->companyId,array(

                            'company_info' => $company_info,
                            'certificate_no' => $certificate_no,
                            'companyType' => $this->settings($company_info->type_id,'id'),
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'share_calls' => $shareCalls,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                            'callonSharesRecord' => $callonSharesRecord,
                            'stakeholder_info' => $stakeholder_info,
                            'shareholdersList' => $shareholdersList,
                        'shareholderFirmList' => $shareholderFirmList,
                           
                        )),
                        'uploadDocs'   => $this->files_for_upload_docs($request->companyId),
                        'uploadOtherDocs' => $this->files_for_other_docs($request->companyId),
                        'form7_payment' => $this->settings('PAYMENT_COMPANY_ISSUE_OF_SHARES','key')->value,
                        'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                        'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                        'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                       // 'ordShares' => $this->checkNoOfSharesForClass($request->companyId,'54'),
                        'isShareIssuedProperly' => $this->isShareIssuedProperly($request->companyId,$callonSharesRecord->id)
                        )
                ], 200);
          
    }

   
  private function has_calls_on_shares_record($company_id) {
   
    $accepted_request_statuses = array(
        $this->settings('COMPANY_ISSUE_OF_SHARES_APPROVED','key')->id,
        $this->settings('COMPANY_ISSUE_OF_SHARES_REJECTED','key')->id
    );
    $request_type =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
    //$record_count = ShareClasses::where('company_id', $company_id)
                            //  ->whereNotIn('status', $accepted_request_statuses )
                            //   ->count();
    $record_count = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
    if( $record_count === 1 ) {
       // $record = ShareClasses::where('company_id', $company_id)
       // ->whereNotIn('status', $accepted_request_statuses )
       //  ->first();

         $record = CompanyChangeRequestItem::where('request_type',$request_type)
         ->where('company_id', $company_id)
         ->whereNotIn('status', $accepted_request_statuses )
         ->first();

        //return $record->request_id;
        return $record->id;
    } else {
        return false;
    }
}

  private function valid_calls_on_shares_request_operation($company_id){


    $accepted_request_statuses = array(
        $this->settings('COMPANY_ISSUE_OF_SHARES_APPROVED','key')->id,
        $this->settings('COMPANY_ISSUE_OF_SHARES_REJECTED','key')->id
    );
    $request_type =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;

    $exist_request_id = $this->has_calls_on_shares_record($company_id);

    if($exist_request_id) {

        
      //  $request_count = CompanyChangeRequestItem::where('request_type',$request_type)
       //                    ->where('company_id', $company_id)
      //                     ->where('id', $exist_request_id)
      //                     ->whereNotIn('status', $accepted_request_statuses )
      //                     ->count();
     //   if($request_count !== 1) { // request not in processing or  resubmit stage
     //       return false;
     //   } else {
      //      return $exist_request_id;
      //  }

           return $exist_request_id;
         
    } else {
           $user = $this->getAuthUser();
            $company_info = Company::where('id', $company_id)->first();
            $year = date('Y',time());

            $request = new CompanyChangeRequestItem;
            $request->company_id = $company_id;
            $request->request_type = $request_type;
            $request->status = $this->settings('COMPANY_ISSUE_OF_SHARES_PROCESSING','key')->id;
            $request->request_by =  $user->userid;
            $request->save();
            $request_id =  $request->id;

            $record = new ShareClasses;
            $record->company_id = $company_id;
            $record->request_id = $request_id;
            $record->date_of = date('Y-m-d', time());
            $record->status = $this->settings('COMPANY_ISSUE_OF_SHARES_PROCESSING','key')->id;
            $record->save();
            $record_id =  $record->id;

            if($record_id && $request_id ) {
                return $request_id;
            }else{
                return false;
            }

    }
    
}



  function generate_calls_on_shares_report($company_id, $info_array=array()){

    $generated_files = array(
          'docs' => array(),
    );
    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

    if(!$request_id) {
        return $generated_files;
    }
  
    $file_name_key = 'form6';
    $file_name = 'FORM 06';


    $data = $info_array;
                  
    $directory = "issue-of-shares/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form6';
    $pdf = PDF::loadView($view, $data);
    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$request_id.'.pdf');

    $file_row = array();
                      
    $file_row['name'] = $file_name;
    $file_row['file_name_key'] = $file_name_key;
    $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$request_id.pdf");


    $generated_files['docs'][] = $file_row;

    /**************************** */

    $file_name_key = 'form6-shareholder-details';
    $file_name = 'Shareholder Details';


    $data = $info_array;
                  
    $directory = "issue-of-shares/$request_id";
    Storage::makeDirectory($directory);

    $view = 'forms.'.'form6-shareholders-details';
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

    $request_id = $this->valid_calls_on_shares_request_operation($company_id);

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

        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

        if(!$request_id) {
            return $generated_files;
        }
        
        $request_type =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;

        $request = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $request_id)
                               ->first();

      
        // documents list
        $form_6 = Documents::where('key', 'ISSUE_OF_SHARES_FORM6')->first();
        $form_other_docs = Documents::where('key', 'ISSUE_OF_SHARES_ADDITIONAL_DOCUMENT')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';

        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $form_6->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $form_6->id;
        $file_row['file_description'] = $form_6->description;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['request_id'] = $request_id;
        $file_row['uploaded_path'] = '';
        $file_row['is_admin_requested'] = false;
                
        $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('request_id',$request_id)
                                        ->where('document_id', $form_6->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
        $uploadeDocStatus = @$uploadedDoc->status;
        $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
        if($request->status == 'COMPANY_ISSUE_OF_SHARES_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
         $callShareGroup = DocumentsGroup::where('request_type', 'ISSUE_OF_SHARES')->first();
         $callShareDocuments = Documents::where('document_group_id', $callShareGroup->id)
                                            // ->where('key', '!=' , 'FORM_7')
                                             ->get();
         $callShareDocumentsCount = Documents::where('document_group_id', $callShareGroup->id)
                                                // ->where('key', '!=' , 'FORM_7')
                                                 ->count();
 
         if($callShareDocumentsCount){
             foreach($callShareDocuments as $other_doc ) {


                if($form_6->id === $other_doc->id ||  $form_other_docs->id === $other_doc->id) {
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
                 if($request->status == 'COMPANY_ISSUE_OF_SHARES_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                 $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
                 if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
 
                             $commentRow = CompanyDocumentStatus::where('company_document_id', $uploadedDoc->id )
                                                                     ->whereIn('status', array($this->settings('DOCUMENT_REQUESTED','key')->id, $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) ) 
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

    function submitShareCallRecords(Request $request){
        
        $company_id = $request->companyId;

        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

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

        $sharecallRow = ShareClasses::where('company_id', $company_id)
            ->where('request_id', $request_id)
            ->first();
        if(!isset($sharecallRow->id)) { 

                return response()->json([
                    'message' => 'Invalid Request having no issue of share row.',
                    'status' =>false,
                    'request_id'   => null,
                    'change_id'    => null,
                ], 200);
    
                 exit();
    
        }


       /* $call_count = ShareIssueRecords::where('status',$this->settings('ISSUE_OF_SHARES','key')->id)
                                                ->where('record_id', $sharecallRow->id)
                                                ->count();
        if($call_count){
            $calls = ShareIssueRecords::where('status',$this->settings('ISSUE_OF_SHARES','key')->id)
                                                ->where('record_id', $sharecallRow->id)
                                                ->get();
            foreach($calls as $d ) {
                
                 CompanyItemChange::where('request_id',$request_id)
                 ->where('changes_type', $this->settings('ADD','key')->id)
                 ->where('item_id', $d->id)
                 ->where('item_table_type', $this->settings('ISSUE_SHARE_TABLE','key')->id)
                 ->delete();
                 ShareIssueRecords::where('id', $d->id)
                             ->where('status', $this->settings('ISSUE_OF_SHARES','key')->id)
                            // ->where('company_id', $company_id)
                             ->delete();
            }

        }*/

 
        //loop through add director list
        foreach($request->call_records['share'] as $sr ){

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
            $newSr->status =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
            $newSr->record_id = $sharecallRow->id;
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
        'stated_capital' => floatval($request->stated_capital) ? floatval($request->stated_capital) : null,
        'signing_party_designation' => $request->signing_party_designation ? $request->signing_party_designation : null,
        'signed_party_id' => $request->singning_party_name ? $request->singning_party_name : null,
        );
     ShareClasses::where('company_id', $company_id)
        ->where('request_id', $request_id)
        ->update($share_summery);
        

      return response()->json([
        'message' => 'data.',
        'status' =>true,
        'request_id'   => $request_id,
        'change_id'    => null,
      ], 200);
    }



    function resubmit(Request $request ) {

        $company_id = $request->companyId;

        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $callonSharesRecord =  ShareClasses::where('company_id', $request->companyId)
            ->where('request_id', $request_id)
             ->first();
        if( !( isset($callonSharesRecord->status) && $callonSharesRecord->status === $this->settings('COMPANY_ISSUE_OF_SHARES_REQUEST_TO_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Issue of shares Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update1 = ShareClasses::where('request_id', $request_id)->update(['status' => $this->settings('COMPANY_ISSUE_OF_SHARES_RESUBMITTED', 'key')->id]);
        $update2 =  CompanyChangeRequestItem::where('id', $request_id)->update(['status' => $this->settings('COMPANY_ISSUE_OF_SHARES_RESUBMITTED', 'key')->id]);

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

            $request_id = $this->valid_calls_on_shares_request_operation($company_id);

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
        
            $path = 'issue-of-shares/'.substr($company_id,0,2).'/'.$request_id;
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
            $request_id = $this->valid_calls_on_shares_request_operation($company_id);

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
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);


        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        // documents list
        $form_other_docs = Documents::where('key', 'ISSUE_OF_SHARES_ADDITIONAL_DOCUMENT')->first();
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
            if($company_status == 'COMPANY_ISSUE_OF_SHARES_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
        
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

  
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
    
        $path = 'issue-of-shares/other-docs/'.substr($company_id,0,2);
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
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

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
    
        $path = 'issue-of-shares/other-docs/'.substr($company_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'ISSUE_OF_SHARES_ADDITIONAL_DOCUMENT')->first();


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

     function removeShareClassRecord(Request $request){

        $company_id = $request->companyId;
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
            ], 200);

             exit();

        }
        $record_id = isset($request->record_id) && intval($request->record_id) ? $request->record_id : null;
       
        if(!$record_id) { 

            return response()->json([
                'message' => 'Invalid Record ID.',
                'status' =>false,
            ], 200);

             exit();

        }

        $shareholders = CompanyMember::where('company_id', $company_id)->where('issued_share_class', $record_id)->get();

        if(isset($shareholders[0]->id)) {
            foreach($shareholders as $sh ) {

                $shareRow = Share::where('company_member_id', $sh->id)->orderBy('id', 'DESC')->first();

                if($sh->status == 1 ) {
                    $shareGroup_update = ShareGroup::find( $shareRow->group_id);
                    $shareGroup_update->new_shares = null;
                    $shareGroup_update->current_shares = null;
                    $shareGroup_update->save();

                    $shareholder_update = CompanyMember::find( $sh->id);
                    $shareholder_update->issued_share_class = '';
                    $shareholder_update->update();
                }
                if($sh->status == $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id){

                    Share::where('company_member_id', $sh->id)->delete();
                    ShareGroup::where('id', $shareRow->group_id)->delete();

                    //remove addresses
                    if($sh->address_id){
                        Address::where('id', $sh->address_id)->delete();
                    }
                    if($sh->foreign_address_id){
                        Address::where('id', $sh->foreign_address_id)->delete();
                    }
                    //remove Shareholder
                    CompanyMember::where('id', $sh->id)->delete();



                }
                
            }
        }

        $shareholderFirms = CompanyFirms::where('company_id', $company_id)->where('issued_share_class', $record_id)->get();

        if(isset($shareholderFirms[0]->id)) {
            foreach($shareholderFirms as $sh ) {

                $shareRow = Share::where('company_firm_id', $sh->id)->orderBy('id', 'DESC')->first();

                if($sh->status == 1 ) {
                    $shareGroup_update = ShareGroup::find( $shareRow->group_id);
                    $shareGroup_update->new_shares = null;
                    $shareGroup_update->current_shares = null;
                    $shareGroup_update->save();

                    $shareholder_update = CompanFirms::find( $sh->id);
                    $shareholder_update->issued_share_class = '';
                    $shareholder_update->update();
                }
                if($sh->status == $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id){

                    Share::where('company_firm_id',$sh->id)->delete();
                    ShareGroup::where('id', $shareRow->group_id)->delete();

                    //remove addresses
                    if($sh->address_id){
                        Address::where('id', $sh->address_id)->delete();
                    }

                    //remove Shareholder
                    CompanyFirms::where('id', $sh->id)->delete();



                }
                
            }
        }

        
        ShareIssueRecords::where('id', $record_id )->delete();

        return response()->json([
            'message' => 'Successfully removed the record.',
            'status' =>true,
        ], 200);

         exit();

        

     }

     function removeShareHolder(Request $request) {
        $company_id = $request->companyId;
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $shoarehoderId = isset($request->shareholder_id) && intval($request->shareholder_id) ? $request->shareholder_id : null;
       
        if(!$shoarehoderId) { 

            return response()->json([
                'message' => 'Invalid Shareholder ID.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $shareholderInfo = null;
        $shareRow = null;

        if( $request->shareholder_type === 'firm' ){
            $shareholderInfo = CompanyFirms::where('id', $shoarehoderId)->where('status', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)->first();
        }else{
            $shareholderInfo = CompanyMember::where('id', $shoarehoderId)->where('status', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)->first();
        }

        if(!( isset($shareholderInfo->id) && intval($shareholderInfo->id)) ) {
            return response()->json([
                'message' => 'Invalid Shareholder.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }


        if( $request->shareholder_type === 'firm' ){
           
            //delete shares
            $shareRow = Share::where('company_firm_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
            Share::where('company_firm_id', $shoarehoderId)->delete();
            ShareGroup::where('id', $shareRow->group_id)->delete();

            //remove addresses
            Address::where('id', $shareholderInfo->address_id)->delete();

            //remove Shareholder
            CompanyFirms::where('id', $shareholderInfo->id)->delete();


            
        } else {
         
            $shareRow = Share::where('company_member_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
            Share::where('company_member_id', $shoarehoderId)->delete();
            ShareGroup::where('id', $shareRow->group_id)->delete();

            //remove addresses
            if($shareholderInfo->address_id){
                Address::where('id', $shareholderInfo->address_id)->delete();
            }
            if($shareholderInfo->foreign_address_id){
                Address::where('id', $shareholderInfo->foreign_address_id)->delete();
            }

            //remove Shareholder
            CompanyMember::where('id', $shareholderInfo->id)->delete();
           
        }

        return response()->json([
            'message' => 'Successfully Removed the Shareholder.',
            'status' =>true,
        ], 200);

         exit();

     }


     function submitExisitingShareholder( Request $request ) {
        $company_id = $request->companyId;
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }
        $shareholder = $request->shareholder;

        $shoarehoderId = isset($shareholder['id']) && intval($shareholder['id']) ? $shareholder['id'] : null;
       
        if(!$shoarehoderId) { 

            return response()->json([
                'message' => 'Invalid Shareholder.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $shareholderInfo = null;
        if( $shareholder['shareholderType'] === 'firm' ){
            $shareholderInfo = CompanyFirms::where('id', $shoarehoderId)->first();
            
        } else {
            $shareholderInfo = CompanyMember::where('id', $shoarehoderId)->first();
            $shareRow = Share::where('company_member_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
        }

      

        $company_info = Company::where('id',$company_id)->first();


        if( $shareholder['shareholderType'] === 'natural' ){

            if( $shareholderInfo->address_id ) {

                $address = Address::find($shareholderInfo->address_id);
                $address->province = $shareholder['province'];
                $address->district =  $shareholder['district'];
                $address->city     =  $shareholder['city'];
                $address->address1 =  $shareholder['localAddress1'];
                $address->address2 =  $shareholder['localAddress2'];
                $address->postcode =  $shareholder['postcode'];
                $address->country  =  'Sri Lanka';
                $address->save();

            }

            if( $shareholderInfo->foreign_address_id ) {
                $forAddress = Address::find($shareholderInfo->foreign_address_id);
                $forAddress->province = $shareholder['forProvince'];
                $forAddress->city =  $shareholder['forCity'];
                $forAddress->district =  null;
                $forAddress->address1 =  $shareholder['forAddress1'];
                $forAddress->address2 =  $shareholder['forAddress2'];
                $forAddress->postcode =  $shareholder['forPostcode'];
                $forAddress->country =   $shareholder['country'];
                $forAddress->save();

            }

            $newSh = CompanyMember::find($shoarehoderId);
            $newSh->company_id = $company_id;
            $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
            $newSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes'; 
            $newSh->title = $shareholder['title'];
            $newSh->first_name = $shareholder['firstname'];
            $newSh->last_name = $shareholder['lastname'];
            $newSh->nic = strtoupper($shareholder['nic']);
            $newSh->passport_no = $shareholder['passport'];
            $newSh->address_id = $shareholderInfo->address_id;
            $newSh->foreign_address_id = $shareholderInfo->foreign_address_id;
            $newSh->passport_issued_country = isset($shareholder['passport_issued_country']) ?  $shareholder['passport_issued_country'] : $shareholder['country'];
            $newSh->telephone = $shareholder['phone'];
            $newSh->mobile =$shareholder['mobile'];
            $newSh->email = $shareholder['email'];
            $newSh->occupation = $shareholder['occupation'];
            $newSh->date_of_appointment = date('Y-m-d',strtotime($shareholder['date']) );
            $newSh->status =  $shareholderInfo->status == 1 ? 1 : $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
            $newSh->issued_share_class =  $shareholder['shareIssueClass'];
            $newSh->save();
           


        }

        if( $shareholder['shareholderType'] === 'firm' ){
                $address = Address::find($shareholderInfo->address_id);
                $address->province = $shareholder['firm_province'];
                $address->district =  ( $shareholder['type'] == 'local') ? $shareholder['firm_district'] : null ;
                $address->city =  $shareholder['firm_city'];
                $address->address1 =  $shareholder['firm_localAddress1'];
                $address->address2 =  $shareholder['firm_localAddress2'];
                $address->postcode = $shareholder['firm_postcode'];
                $address->country = $shareholder['country'];
                $address->save();
               
                $newSh = CompanyFirms::find($shoarehoderId);
                $newSh->registration_no = isset($shareholder['pvNumber']) ? $shareholder['pvNumber'] :  null;
                $newSh->name = $shareholder['firm_name'];
                $newSh->email = $shareholder['firm_email'];
                $newSh->mobile = $shareholder['firm_mobile'];
                $newSh->date_of_appointment = $shareholder['firm_date'];
                $newSh->company_id = $company_id;
                $newSh->address_id = $address->id;
                $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                $newSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                $newSh->status =  $shareholderInfo->status == 1 ? 1 : $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
                $newSh->issued_share_class =  $shareholder['shareIssueClass'];
                $newSh->save();

        }

        $new_share_for_history = 0;
        $old_share_for_history = 0;
        $share_type_for_history = '';
        $shareholder_type_for_history = '';
        $shareholder_id_for_history = '';
        $shareholder_new_or_exist_for_history = '';
        $request_for_history = $request_id;
        $assigned_share_class_for_history =  $shareholder['shareIssueClass'];

        
        if(  $shareholder['shareType'] == 'single' && intval($shareholder['noOfShares'])  ) {
            $shareRow = null;
            if ( $shareholder['shareholderType']  == 'natural' ) {
                $shareRow = Share::where('company_member_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
            }else {
                $shareRow = Share::where('company_firm_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
            }
           
            $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();
            // $share_group_id = $shareRow->group_id;
            //remove  share records first
            if ( $shareholder['shareholderType']  == 'natural' ) {
                Share::where('company_member_id', $shoarehoderId )->delete();
            }else{
                Share::where('company_firm_id', $shoarehoderId )->delete();
            }

           
         //   $shareholder_sharegroup = new ShareGroup;
            $shareholder_sharegroup = isset($shareGroupInfo->id ) && $shareGroupInfo->type != 'core_share' ? ShareGroup::find($shareGroupInfo->id) : new ShareGroup;
            $shareholder_sharegroup->type ='single_share';
            $shareholder_sharegroup->name ='single_share_no_name';

            $share_type_for_history = 'single_share';

            if($shareholder['shareIssueClass']) {
                $shareholder_sharegroup->no_of_shares =( $shareholderInfo->status != 1) ? intval($shareholder['noOfShares'])   :  intval($shareGroupInfo->no_of_shares);
                $shareholder_sharegroup->new_shares = ( $shareholderInfo->status != 1) ? intval($shareholder['noOfShares']) : intval( $shareholder['groupAddedValue'] ) + intval($shareGroupInfo->no_of_shares);
                $shareholder_sharegroup->current_shares = ( $shareholderInfo->status != 1) ? intval($shareholder['noOfShares']) : intval($shareGroupInfo->no_of_shares);
                
                $new_share_for_history = ( $shareholderInfo->status != 1) ? intval($shareholder['noOfShares']) : intval( $shareholder['groupAddedValue'] ) + intval($shareGroupInfo->no_of_shares);
                $old_share_for_history = ( $shareholderInfo->status != 1) ? intval($shareholder['noOfShares'])   :  intval($shareGroupInfo->no_of_shares);
            }else {
                $shareholder_sharegroup->no_of_shares =( $shareholderInfo->status != 1) ? intval($shareholder['noOfShares'])   :  intval($shareGroupInfo->no_of_shares);
                $shareholder_sharegroup->new_shares =  null;
                $shareholder_sharegroup->current_shares = null;

                $new_share_for_history = 0;
                $old_share_for_history = ( $shareholderInfo->status != 1) ? intval($shareholder['noOfShares'])   :  intval($shareGroupInfo->no_of_shares);
            }
            $shareholder_sharegroup->company_id = $company_id;
            $shareholder_sharegroup->status = 1;
            $shareholder_sharegroup->save();

            if(isset($shareGroupInfo->id ) && $shareGroupInfo->type != 'core_share') {
                $shareholder_sharegroupID = $shareGroupInfo->id;
            } else {
                $shareholder_sharegroupID = $shareholder_sharegroup->id;
              
            }
           

            $shareholder_share = new Share; 
            if ( $shareholder['shareholderType']  == 'natural' ) {
                $shareholder_share->company_member_id = $shoarehoderId;
                $shareholder_type_for_history = 'natural';
            }else{ 
                $shareholder_share->company_firm_id = $shoarehoderId;
                $shareholder_type_for_history = 'firm';
            }
            $shareholder_share->group_id = $shareholder_sharegroupID;
            $shareholder_share->save();


            $shareholder_new_or_exist_for_history = $shareholderInfo->status != 1 ? 'new' : 'exist';
            $shareholder_id_for_history = $shoarehoderId;
        }

        if($shareholder['shareType'] == 'core'  &&  isset($shareholder['coreGroupSelected']) &&  intval( $shareholder['coreGroupSelected']) ){
            
            $share_type_for_history = 'core_share';

            //remove share records
           
            if ( $shareholder['shareholderType']  == 'natural' ) {
                Share::where('company_member_id', $shoarehoderId )->delete();
            }else{
                Share::where('company_firm_id', $shoarehoderId )->delete();
            }

            $shareGroupInfo = ShareGroup::where('id', $shareholder['coreGroupSelected'])->first();

            $shareholder_sharegroup = ShareGroup::find($shareholder['coreGroupSelected']);


            if($shareholder['shareIssueClass']) {

                if(isset($shareholder['groupAddedValue'] ) && intval($shareholder['groupAddedValue'] )) {

                    $shareholder_sharegroup->new_shares = intval( $shareholder['groupAddedValue'] ) + intval($shareGroupInfo->no_of_shares);
                    $shareholder_sharegroup->current_shares = intval($shareGroupInfo->no_of_shares);
                    $shareholder_sharegroup->save();

                    $new_share_for_history = intval( $shareholder['groupAddedValue'] ) + intval($shareGroupInfo->no_of_shares);
                    $old_share_for_history = ( $shareholderInfo->status != 1) ? 0 : $shareGroupInfo->no_of_shares;
                }
               // $shareholder_sharegroup->new_shares = intval( $shareholder['groupAddedValue'] ) + intval($shareGroupInfo->no_of_shares);
              //  $shareholder_sharegroup->current_shares = intval($shareGroupInfo->no_of_shares);
            }else {
                $shareholder_sharegroup->new_shares = null;
                $shareholder_sharegroup->current_shares = null;

                $new_share_for_history = 0;
                $old_share_for_history = $shareGroupInfo->no_of_shares;
            }

            $shareholder_sharegroup->save();

            $shareholder_share = new Share;
            if ( $shareholder['shareholderType']  == 'natural' ) {
                $shareholder_share->company_member_id = $shoarehoderId;
                $shareholder_type_for_history = 'natural';
            }else{
                 $shareholder_share->company_firm_id = $shoarehoderId;
                 $shareholder_type_for_history = 'firm';
            }
            $shareholder_share->group_id =intval( $shareholder['coreGroupSelected']);
            $shareholder_share->save();

            $shareholder_new_or_exist_for_history = $shareholderInfo->status != 1 ? 'new' : 'exist';
            $shareholder_id_for_history = $shoarehoderId;
        }
        if(
            $shareholder['shareType'] == 'core' &&
             ( empty( $shareholder['coreGroupSelected'])  ||  !intval( $shareholder['coreGroupSelected']) )  &&
              isset( $shareholder['coreShareGroupName']) && 
              $shareholder['coreShareGroupName'] && 
            intval($shareholder['noOfSharesGroup']) ) {

                $share_type_for_history = 'core_share';

                if ( $shareholder['shareholderType']  == 'natural' ) {
                    $shareRow = Share::where('company_member_id', $shoarehoderId)->orderBy('id', 'DESC')->first();

                   if(isset($shareRow->group_id)){
                    ShareGroup::where('id', $shareRow->group_id)->delete();
                   }
                    
                }else {
                    $shareRow = Share::where('company_firm_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
                  //  ShareGroup::where('id', $shareRow->group_id)->delete();

                    if(isset($shareRow->group_id)){
                        ShareGroup::where('id', $shareRow->group_id)->delete();
                    }
                }
               
                if ( $shareholder['shareholderType']  == 'natural' ) {
                    Share::where('company_member_id', $shoarehoderId )->delete();
                }else{
                    Share::where('company_firm_id', $shoarehoderId )->delete();
                }
               


              //add to single share group
              $shareholder_sharegroup = new ShareGroup;
              $shareholder_sharegroup->type ='core_share';
              $shareholder_sharegroup->name = $shareholder['coreShareGroupName'];
              $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfSharesGroup'] );
              $shareholder_sharegroup->new_shares =  intval( $shareholder['noOfSharesGroup'] );
              $shareholder_sharegroup->current_shares = intval( $shareholder['noOfSharesGroup'] );
              $shareholder_sharegroup->company_id = $company_id;
              $shareholder_sharegroup->status = 1;

              $shareholder_sharegroup->save();
              $shareholder_sharegroupID = $shareholder_sharegroup->id;

              $new_share_for_history = intval( $shareholder['noOfSharesGroup'] );
              $old_share_for_history = 0;

              //add to share table
              $shareholder_share = new Share;
              if ( $shareholder['shareholderType']  == 'natural' ) {
                  $shareholder_share->company_member_id = $shoarehoderId;
                  $shareholder_type_for_history = 'natural';
              }else{
                  $shareholder_share->company_firm_id = $shoarehoderId;
                  $shareholder_type_for_history = 'firm';
              }
              $shareholder_share->group_id = $shareholder_sharegroupID;
              $shareholder_share->save();

              $shareholder_new_or_exist_for_history = $shareholderInfo->status != 1 ? 'new' : 'exist';
              $shareholder_id_for_history = $shoarehoderId;
          }

           

          //save share history
           ShareIssueHistory::where('request_id', $request_id )
           ->where('company_id' ,$company_id)
           ->where('shareholder_type', $shareholder_type_for_history)
           ->where('shareholder_id', $shareholder_id_for_history)
           ->where('shareholder_exist_status', $shareholder_new_or_exist_for_history)
           ->delete();

            $share_history = new ShareIssueHistory;
            $share_history->company_id = $company_id;
            $share_history->request_id = $request_id;
            $share_history->new_share = $new_share_for_history;
            $share_history->old_share = $old_share_for_history;
            $share_history->share_type = $share_type_for_history;
            $share_history->shareholder_type = $shareholder_type_for_history;
            $share_history->shareholder_id = $shareholder_id_for_history;
            $share_history->shareholder_exist_status = $shareholder_new_or_exist_for_history;
            $share_history->assigned_share_class = $assigned_share_class_for_history;
            $share_history->save();


     }

     function submitNewShareHolder( Request $request ) {

        $company_id = $request->companyId;
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }
        $shareholder = $request->shareholder;
        $company_info = Company::where('id',$company_id)->first();

        $address = new Address;
        $forAddress = new Address;
        $new_address_id= null;
        $new_forAddressId = null;
        $shareHolderId = null;

        if( $shareholder['shareholderType'] === 'natural' ){
            if( $shareholder['province'] && $shareholder['district'] && $shareholder['city'] && $shareholder['localAddress1'] && $shareholder['postcode'] ) {
                $address->province = $shareholder['province'];
                $address->district =  $shareholder['district'];
                $address->city     =  $shareholder['city'];
                $address->address1 =  $shareholder['localAddress1'];
                $address->address2 =  $shareholder['localAddress2'];
                $address->postcode =  $shareholder['postcode'];
                $address->country  =  'Sri Lanka';
                $address->save();
                $new_address_id = $address->id;

            }

            if( $shareholder['forProvince'] && $shareholder['forCity'] &&  $shareholder['forAddress1'] && $shareholder['forPostcode']) {
                $forAddress->province = $shareholder['forProvince'];
                $forAddress->city =  $shareholder['forCity'];
                $address->district =  null;
                $forAddress->address1 =  $shareholder['forAddress1'];
                $forAddress->address2 =  $shareholder['forAddress2'];
                $forAddress->postcode =  $shareholder['forPostcode'];
                $forAddress->country =   $shareholder['country'];
                $forAddress->save();
                $new_forAddressId = $forAddress->id;

            }

            $newSh = new CompanyMember;
            $newSh->company_id = $company_id;
            $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
            $newSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes'; 
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
            $newSh->status =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
            $newSh->issued_share_class =  $shareholder['shareIssueClass'];
            $newSh->save();
            $shareHolderId =  $newSh->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $shareHolderId;
            $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
            $change->save();
            $change_id = $change->id;


        }

        if( $shareholder['shareholderType'] === 'firm' ){

                $address->province = $shareholder['firm_province'];
                $address->district =  ( $shareholder['type'] == 'local') ? $shareholder['firm_district'] : null ;
                $address->city =  $shareholder['firm_city'];
                $address->address1 =  $shareholder['firm_localAddress1'];
                $address->address2 =  $shareholder['firm_localAddress2'];
                $address->postcode = $shareholder['firm_postcode'];
                $address->country = $shareholder['country'];
                $address->save();
                $new_address_id = $address->id;


                $newSh = new CompanyFirms;
                $process_status = $this->settings($company_info->status,'id')->key;
                $newSh->registration_no = isset($shareholder['pvNumber']) ? $shareholder['pvNumber'] :  null;
                $newSh->name = $shareholder['firm_name'];
                $newSh->email = $shareholder['firm_email'];
                $newSh->mobile = $shareholder['firm_mobile'];
                $newSh->date_of_appointment = $shareholder['firm_date'];
                $newSh->company_id = $company_id;
                $newSh->address_id = $new_address_id;
                $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                $newSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                $newSh->status = $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
                $newSh->issued_share_class =  $shareholder['shareIssueClass'];
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

        $new_share_for_history = 0;
        $old_share_for_history = 0;
        $share_type_for_history = '';
        $shareholder_type_for_history = '';
        $shareholder_id_for_history = '';
        $shareholder_new_or_exist_for_history = 'new';
        $request_for_history = $request_id;
        $assigned_share_class_for_history = $shareholder['shareIssueClass'];

        if(  $shareholder['shareType'] == 'single' && intval($shareholder['noOfShares']) ) {

            $shareholder_sharegroup = new ShareGroup;
            $shareholder_sharegroup->type ='single_share';
            $shareholder_sharegroup->name ='single_share_no_name';
            $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfShares'] );
            $shareholder_sharegroup->new_shares =intval( $shareholder['noOfShares'] );
            $shareholder_sharegroup->current_shares =intval( $shareholder['noOfShares'] );
            $shareholder_sharegroup->company_id = $company_id;
            $shareholder_sharegroup->status = 1;
            $shareholder_sharegroup->save();
            $shareholder_sharegroupID = $shareholder_sharegroup->id;

            $shareholder_share = new Share; 
            if ( $shareholder['shareholderType']  == 'natural' ) {
                $shareholder_share->company_member_id = $shareHolderId;
                $shareholder_type_for_history = 'natural';
                
            }else{ 
                $shareholder_share->company_firm_id = $shareHolderId;
                $shareholder_type_for_history = 'firm';
            }
            $shareholder_share->group_id = $shareholder_sharegroupID;
            $shareholder_share->save();


            $new_share_for_history = intval( $shareholder['noOfShares'] );
            $old_share_for_history = 0;
            $share_type_for_history = 'single_share';
            $shareholder_id_for_history = $shareholder_sharegroupID;



        }

        if($shareholder['shareType'] == 'core' && isset($shareholder['coreGroupSelected']) &&  intval( $shareholder['coreGroupSelected']) ){
            
            if(isset($shareholder['groupAddedValue'] ) && intval($shareholder['groupAddedValue'] )) {

                $shareholder_sharegroup_info = ShareGroup::where('id', intval( $shareholder['coreGroupSelected']) )->first();
                
                $shareholder_sharegroup = ShareGroup::find(intval( $shareholder['coreGroupSelected']));
                $shareholder_sharegroup->new_shares = intval( $shareholder['groupAddedValue'] ) + intval($shareholder_sharegroup_info->no_of_shares);
                $shareholder_sharegroup->current_shares = intval($shareholder_sharegroup_info->no_of_shares);
                $shareholder_sharegroup->save();

                $new_share_for_history = intval( $shareholder['groupAddedValue'] ) + intval($shareholder_sharegroup_info->no_of_shares);
                $old_share_for_history = intval($shareholder_sharegroup_info->no_of_shares);
                $share_type_for_history = 'core_share';
            }
            
            $shareholder_share = new Share;
            if ( $shareholder['shareholderType']  == 'natural' ) {
                $shareholder_share->company_member_id = $shareHolderId;
                $shareholder_type_for_history = 'natural';
            }else{
                 $shareholder_share->company_firm_id = $shareHolderId;
                 $shareholder_type_for_history = 'firm';
            }
            $shareholder_share->group_id =intval( $shareholder['coreGroupSelected']);
            $shareholder_share->save();

            $shareholder_id_for_history = $shareHolderId;


           


        }
        if(
            $shareholder['shareType'] == 'core' &&
             ( empty( $shareholder['coreGroupSelected'])  ||  !intval( $shareholder['coreGroupSelected']) )  &&
              isset( $shareholder['coreShareGroupName']) && 
              $shareholder['coreShareGroupName'] && 
            intval($shareholder['noOfSharesGroup']) ) {

                //add to single share group
                $shareholder_sharegroup = new ShareGroup;
                $shareholder_sharegroup->type ='core_share';
                $shareholder_sharegroup->name = $shareholder['coreShareGroupName'];
                $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfSharesGroup'] );
                $shareholder_sharegroup->new_shares =intval( $shareholder['noOfSharesGroup'] );
                $shareholder_sharegroup->current_shares =intval( $shareholder['noOfSharesGroup'] );
                $shareholder_sharegroup->company_id = $company_id;
                $shareholder_sharegroup->status = 1;

                $shareholder_sharegroup->save();
                $shareholder_sharegroupID = $shareholder_sharegroup->id;

                //add to share table
                $shareholder_share = new Share;
                if ( $shareholder['shareholderType']  == 'natural' ) {
                    $shareholder_share->company_member_id = $shareHolderId;
                    $shareholder_type_for_history = 'natural';
                  }else{
                    $shareholder_share->company_firm_id = $shareHolderId;
                    $shareholder_type_for_history = 'firm';
                  }
                $shareholder_share->group_id = $shareholder_sharegroupID;
                $shareholder_share->save();

                $new_share_for_history = intval( $shareholder['noOfSharesGroup'] );
                $old_share_for_history = 0;
                $share_type_for_history = 'core_share';
                $shareholder_id_for_history = $shareHolderId;

                


        }

        $share_history = new ShareIssueHistory;
        $share_history->company_id = $company_id;
        $share_history->request_id = $request_id;
        $share_history->new_share = $new_share_for_history;
        $share_history->old_share = $old_share_for_history;
        $share_history->share_type = $share_type_for_history;
        $share_history->shareholder_type = $shareholder_type_for_history;
        $share_history->shareholder_id = $shareholder_id_for_history;
        $share_history->shareholder_exist_status = 'new';
        $share_history->assigned_share_class = $assigned_share_class_for_history;
        $share_history->save();

         
     }

     private function isShareIssuedProperly($company_id, $share_record_id) {
        $share_call_record_list = ShareIssueRecords::where('status',$this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
        ->where('record_id', $share_record_id)
        ->get();

        $share_arr = array();
        $result_str = '';
        if(isset($share_call_record_list[0]->id)){
            foreach($share_call_record_list as $s ) {

                $class_name =  $s->share_class == 'OTHER_SHARE' ? $s->share_class_other : $this->settings($s->share_class,'key')->value;
                $class_issued_shares = floatval($s->no_of_shares_as_cash) + floatval($s->no_of_shares_as_non_cash);
                $total_issued_from_class = $this->checkNoOfSharesForClass( $company_id, $s->id );

                $row = array(
                    'class_name' => $class_name,
                    'class_issued_shares' => $class_issued_shares,
                    'total_issued_from_class' => $total_issued_from_class,
                    'share_difference' => ($class_issued_shares -  $total_issued_from_class)
                );
                $share_arr[] = $row;
                $row_str = ($class_issued_shares -  $total_issued_from_class) != 0 ? '0' : '1';
                $result_str = $result_str . $row_str;

            }
        }

        return array(
            'status' => ($result_str != '') && ( strpos($result_str, '0') === false ),
            'share_arr' => $share_arr
        );
     }
     
     

     private function checkNoOfSharesForClass($companyId, $shareclassId) {
        
        $shareclassId = intval( $shareclassId ) .''; //explicitly convert to string;
       
        $sharesCalc = 0;

        $core_groups = ShareGroup::where('type','core_share')
                                  ->where('company_id', $companyId )
                                ->get();

      if(isset($core_groups[0]->id) && $core_groups[0]->id){
          foreach($core_groups as $g ){

           $shareholdersAssigned = Share::where('group_id', $g->id)->get();

           $shNatural = [];
           $shFirm = [];

           if(isset($shareholdersAssigned[0]->id) && $shareholdersAssigned[0]->id ) {

                foreach( $shareholdersAssigned as $s ) {

                    if($s->company_member_id) {
                        $shNatural[] = $s->company_member_id;
                    }
                    if($s->company_firm_id) {
                        $shFirm[] = $s->company_firm_id;
                    }
                    
                }

                $NewshareHolderRecords =  count($shNatural) ? CompanyMember::whereIn('id',$shNatural)->where('issued_share_class', $shareclassId)->where('status', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)->count()  : 0;
                $NewshareHolderFirmRecords =  count($shFirm) ? CompanyFirms::whereIn('id',$shFirm)->where('issued_share_class', $shareclassId)->where('status', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)->count()   : 0;

                $shareHolderRecords =  count($shNatural) ? CompanyMember::whereIn('id',$shNatural)->where('issued_share_class', $shareclassId)->where('status', 1)->count()  : 0;
                $shareHolderFirmRecords =  count($shFirm) ? CompanyFirms::whereIn('id',$shFirm)->where('issued_share_class', $shareclassId)->where('status', 1)->count()   : 0;

                if($NewshareHolderRecords || $NewshareHolderFirmRecords ) {
                    $sharesCalc = $sharesCalc + $g->new_shares ;
                } else {
                    $sharesCalc = $sharesCalc + (   ($shareHolderRecords || $shareHolderFirmRecords ) ?   ( $g->new_shares ?   ($g->new_shares - $g->no_of_shares ) : $g->no_of_shares   )   : 0  );
                }
                
                

           } 
                                  
      
        }
      }


      //single shares
      $core_groups = ShareGroup::where('type','single_share')
      ->where('company_id', $companyId )
    ->get();

    if(isset($core_groups[0]->id) && $core_groups[0]->id){
        foreach($core_groups as $g ){

            $shareholdersAssigned = Share::where('group_id', $g->id)->first();

            $shareHolderRecords = 0;
            $shareHolderFirmRecords =0;

            if(isset($shareholdersAssigned->id) && $shareholdersAssigned->id ) {

                if($shareholdersAssigned->company_member_id) {
                    $shareHolderRecord =   CompanyMember::where('id',$shareholdersAssigned->company_member_id)->where('issued_share_class', $shareclassId)->first();

                    if(isset($shareHolderRecord->status) && $shareHolderRecord->status == $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id) {
                        $sharesCalc = $sharesCalc +  $g->new_shares;
                    } 
                    if(isset($shareHolderRecord->status) && $shareHolderRecord->status == 1) {
                        $sharesCalc = $sharesCalc +  ( $g->new_shares ?   ($g->new_shares - $g->no_of_shares ) : $g->no_of_shares );
                    } 
                   
                }
                if($shareholdersAssigned->company_firm_id) {
                    $shareHolderFirmRecord =  CompanyFirms::where('id',$shareholdersAssigned->company_firm_id)->where('issued_share_class', $shareclassId)->first();

                    if(isset($shareHolderFirmRecord->status) && $shareHolderFirmRecord->status == $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id) {
                        $sharesCalc = $sharesCalc +  $g->new_shares;
                    } 
                    if(isset($shareHolderFirmRecord->status) && $shareHolderFirmRecord->status == 1) {
                        $sharesCalc = $sharesCalc +  ( $g->new_shares ?   ($g->new_shares - $g->no_of_shares ) : $g->no_of_shares );
                    } 
                }

            } 
                

        }
    }

      return  $sharesCalc;
         
     }

     function submitShareolders( Request $request ){


        die();

        $company_id = $request->companyId;
        $request_id = $this->valid_calls_on_shares_request_operation($company_id);
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
                
                $newSh->registration_no = isset($shareholder['pvNumber']) ? $shareholder['pvNumber'] :  null;
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



    public function uploadShareholderByCSV(Request $request){
        $real_file_name = $request->fileRealName;

        $company_id = $request->companyId;

        $request_id = $this->valid_calls_on_shares_request_operation($company_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'uploadedExt' => $ext
   
            ], 200);
  

        }
 
     
        $size = $request->file('uploadFile')->getClientSize() ;
        $ext = $request->file('uploadFile')->getClientMimeType();

        if('application/vnd.ms-excel' !== $ext  &&  'application/octet-stream' !== $ext){

         return response()->json([
             'message' => 'Please upload your files with csv format.',
             'status' =>false,
             'uploadedExt' => $ext

         ], 200);
        }

        if( $size > 1024 * 1024 * 4) {

         return response()->json([
             'message' => 'File size should be less than 4 MB.',
             'status' =>false,
         ], 200);
        }

        $directory = "issue-of-shares-shareholders/$company_id";
    //    @ Storage::deleteDirectory($directory);
        $path = Storage::putFile($directory, $request->file('uploadFile'));
 
        $file_path = str_replace('public','',url('/')).Storage::url("app/$path");

        $added_arr = array();


     
        if (($handle = fopen($file_path, "r")) !== FALSE) {


            $row = 1;


            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if( !isset($data[0])) {
                   break;
                }

                if( $data[0] == 'yes'  ){ //is firm

                    $memberId= $this->checkComRegNumberShareholderExist($company_id,$data[8]);

                    if( $data[8] && $memberId ) {
                       $added =  $this->submitExisitingShareholderForCSVupload($company_id,$request_id, $memberId,$data);

                       if($added['status']) {
                            $added_arr[] = $data[7].' shareholder firm succsssfully updated';
                       } else {
                           continue;
                       }
                    }else {
                        $added =  $this->submitNewShareHolderForCSVupload($company_id,$request_id, $data);
                        if($added['status']) {
                            $added_arr[] = $data[7].' shareholder firm succsessfully added';
                        } else {
                            continue;
                        }
                    }

                } else {

                  

                    if( $data[1] == 'yes' ) {
                   
                        $memberId= $this->checkNICShareholderExist($company_id,$data[2]);
                        
                        if( $data[2] && $memberId) {
    
                            $added = $this->submitExisitingShareholderForCSVupload($company_id,$request_id, $memberId,$data);


                            if($added['status']) {
                                $added_arr[] = $data[5].' '.$data[6].' shareholder  succsssfully updated';
                           } else {
                               continue;
                           }
                        } else {
                            $added = $this->submitNewShareHolderForCSVupload($company_id, $request_id, $data);
                            if($added['status']) {
                                $added_arr[] = $data[5].' '.$data[6].' shareholder  succsssfully added';
                           } else {
                               continue;
                           }
                        }
                    } 
                    else if($data[1] == 'no') {
                        $memberId= $this->checkPASSPORTShareholderExist($company_id,$data[2]);
                        if( $data[2] && $memberId) {
                            $added['status'] = $this->submitExisitingShareholderForCSVupload($company_id,$request_id, $memberId,$data);
                            if($added) {
                                $added_arr[] = $data[5].' '.$data[6].' shareholder  succsssfully updated';
                           } else {
                               continue;
                           }
                        } else {
                            $added = $this->submitNewShareHolderForCSVupload($company_id,$request_id, $data);
                            if($added['status']) {
                                $added_arr[] = $data[5].' '.$data[6].' shareholder  succsssfully added';
                           } else {
                               continue;
                           }
                        }
                    } else {
                        return response()->json([
                            'message' => 'Invalid data for shareholder member.',
                            'status' =>false,
                       
                        ], 200);
                    }

                }


            }

            return response()->json([
                'message' => 'Shareholders updated/added.',
                'status' =>true,
                'added_arr' => $added_arr
           
            ], 200);

        } else {

            return response()->json([
                'message' => 'Invalid or No records found on CSV.',
                'status' =>false,
                'added_arr' => array()
           
            ], 200);

        }

    }



    function submitExisitingShareholderForCSVupload( $company_id, $request_id,  $shoarehoderId, $data= array()) {


        if(!$shoarehoderId) { 

            return array(
                'message' => 'Invalid shareholder',
                'status' => false
            );
  

        }


        $shareholderInfo = null;
        if( $data[0] == 'yes' ){
            $shareholderInfo = CompanyFirms::where('id', $shoarehoderId)->first();
            
        } else {
            $shareholderInfo = CompanyMember::where('id', $shoarehoderId)->first();
            $shareRow = Share::where('company_member_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
        }

        $company_info = Company::where('id',$company_id)->first();


        if( $data[0] == 'no'  ){

            if( $shareholderInfo->address_id ) {

                $address = Address::find($shareholderInfo->address_id);
                $address->province =  $data[9];
                $address->district =  $data[10];
                $address->city     =  $data[11];
                $address->address1 =  $data[12];
                $address->address2 =  $data[13];
                $address->postcode =  $data[14];
                $address->country  =  'Sri Lanka';
                $address->save();

            }

            if( $shareholderInfo->foreign_address_id ) {
                $forAddress = Address::find($shareholderInfo->foreign_address_id);
                $forAddress->province =  $data[9];
                $forAddress->city =   $data[11];
                $forAddress->district =  null;
                $forAddress->address1 =   $data[12];
                $forAddress->address2 =   $data[13];
                $forAddress->postcode =   $data[14];
                $forAddress->country =   $data[15];
                $forAddress->save();

            }

            $newSh = CompanyMember::find($shoarehoderId);
            $newSh->company_id = $company_id;
            $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
            $newSh->is_srilankan =  $data[1]; 
            $newSh->title = $data[4];
            $newSh->first_name = $data[5];
            $newSh->last_name = $data[6];
            $newSh->nic = ($data[1] == 'yes') ? $data[2] : null;
            $newSh->passport_no =($data[1] == 'no') ? $data[2] : null;;
            $newSh->address_id = $shareholderInfo->address_id;
            $newSh->foreign_address_id =$shareholderInfo->foreign_address_id;
            $newSh->passport_issued_country = $data[1] == 'no' ?  $data[3] : $data[15];
            $newSh->telephone = $data[17];
            $newSh->mobile = $data[16];
            $newSh->email = $data[18];
            $newSh->occupation = $data[20];
            $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
            $newSh->status =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
            $newSh->issued_share_class =  intval($data[21] ) ? intval($data[21] ) : null;
            $newSh->save();
           


        }

        if( $data[0] == 'yes'  ){
                $address = Address::find($shareholderInfo->address_id);
                $address->province = $data[9];
                $address->district =  $data[10];
                $address->city =  $data[11];
                $address->address1 =  $data[12];
                $address->address2 =  $data[13];
                $address->postcode = $data[14];
                $address->country = $data[15] ? $data[15] : 'Sri Lanka';
                $address->save();
               
                $newSh = CompanyFirms::find($shoarehoderId);
                $newSh->registration_no = $data[8];
                $newSh->name = $data[7];
                $newSh->email = $data[18];
                $newSh->mobile =  $data[16];
                $newSh->date_of_appointment =  $data[19];
                $newSh->company_id = $company_id;
                $newSh->address_id = $shareholderInfo->address_id;
                $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                $newSh->is_srilankan =  $data[1];
                $newSh->status = $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
                $newSh->issued_share_class =  intval($data[21]) ? intval($data[21]) : null;
                $newSh->save();

        }
        
        if(  $data[22] == 'single' && intval($data[23])  ) {
            $shareRow = null;
            if ( $data[0]  == 'no' ) {
                $shareRow = Share::where('company_member_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
            }else {
                $shareRow = Share::where('company_firm_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
            }
           
            $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();
            // $share_group_id = $shareRow->group_id;
            //remove  share records first
            if ( $data[0]  == 'no' ) {
                Share::where('company_member_id', $shoarehoderId )->delete();
            }else{
                Share::where('company_firm_id', $shoarehoderId )->delete();
            }

           
         //   $shareholder_sharegroup = new ShareGroup;
            $shareholder_sharegroup = isset($shareGroupInfo->id ) && $shareGroupInfo->type != 'core_share' ? ShareGroup::find($shareGroupInfo->id) : new ShareGroup;
            $shareholder_sharegroup->type ='single_share';
            $shareholder_sharegroup->name ='single_share_no_name';

            if($data[21]) {
                $shareholder_sharegroup->no_of_shares =( $shareholderInfo->status != 1) ? intval($data[23])   :  intval($shareGroupInfo->no_of_shares);
                $shareholder_sharegroup->new_shares = ( $shareholderInfo->status != 1) ? intval($data[23]) : intval( $data[23] ) + intval($shareGroupInfo->no_of_shares);
                $shareholder_sharegroup->current_shares = ( $shareholderInfo->status != 1) ? intval($data[23]) : intval($shareGroupInfo->no_of_shares);
                
            }else {
                $shareholder_sharegroup->no_of_shares =( $shareholderInfo->status != 1) ? intval($data[23])   :  intval($shareGroupInfo->no_of_shares);
                $shareholder_sharegroup->new_shares =  null;
                $shareholder_sharegroup->current_shares = null;
            }
            $shareholder_sharegroup->company_id = $company_id;
            $shareholder_sharegroup->status = 1;
            $shareholder_sharegroup->save();

            if(isset($shareGroupInfo->id ) && $shareGroupInfo->type != 'core_share') {
                $shareholder_sharegroupID = $shareGroupInfo->id;
            } else {
                $shareholder_sharegroupID = $shareholder_sharegroup->id;
              
            }
           

            $shareholder_share = new Share; 
            if ( $data[0]  == 'no' ) {
                $shareholder_share->company_member_id = $shoarehoderId;
            }else{ 
                $shareholder_share->company_firm_id = $shoarehoderId;
            }
            $shareholder_share->group_id = $shareholder_sharegroupID;
            $shareholder_share->save();

        }

        if( $data[22] == 'core'  &&  isset( $data[24]) &&  intval(  $data[24]) ){
            
            //remove share records
           
            if ( $data[0]  == 'no' ) {
                Share::where('company_member_id', $shoarehoderId )->delete();
            }else{
                Share::where('company_firm_id', $shoarehoderId )->delete();
            }

            $shareGroupInfo = ShareGroup::where('id', intval(  $data[24]))->first();

            $shareholder_sharegroup = ShareGroup::find(intval(  $data[24]));


            if(intval(  $data[21])) {

                if(isset($data[25] ) && intval($data[25] )) {

                  

                    $shareholder_sharegroup->new_shares = intval($data[25] ) + intval($shareGroupInfo->no_of_shares);
                    $shareholder_sharegroup->current_shares = intval($shareGroupInfo->no_of_shares);
                    $shareholder_sharegroup->save();
                }
               // $shareholder_sharegroup->new_shares = intval( $shareholder['groupAddedValue'] ) + intval($shareGroupInfo->no_of_shares);
              //  $shareholder_sharegroup->current_shares = intval($shareGroupInfo->no_of_shares);
            }else {
                $shareholder_sharegroup->new_shares = null;
                $shareholder_sharegroup->current_shares = null;
            }

            $shareholder_sharegroup->save();

            $shareholder_share = new Share;
            if ( $data[0]  == 'no' ) {
                $shareholder_share->company_member_id = $shoarehoderId;
            }else{
                 $shareholder_share->company_firm_id = $shoarehoderId;
            }
            $shareholder_share->group_id =intval( $data[24]);
            $shareholder_share->save();
        }
        if(
            $data[22] == 'core' &&
             ( empty( $data[24])  ||  !intval( $data[24]) )  &&
              isset( $data[26]) && 
              $data[26] && 
            intval( $data[27]) ) {


                if ( $data[0]  == 'no' ) {
                    $shareRow = Share::where('company_member_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
                    ShareGroup::where('id', $shareRow->group_id)->delete();
                }else {
                    $shareRow = Share::where('company_firm_id', $shoarehoderId)->orderBy('id', 'DESC')->first();
                    ShareGroup::where('id', $shareRow->group_id)->delete();
                }
               
                if ( $data[0]  == 'no' ) {
                    Share::where('company_member_id', $shoarehoderId )->delete();
                }else{
                    Share::where('company_firm_id', $shoarehoderId )->delete();
                }
               


              //add to single share group
              $shareholder_sharegroup = new ShareGroup;
              $shareholder_sharegroup->type ='core_share';
              $shareholder_sharegroup->name = $data[26];
              $shareholder_sharegroup->no_of_shares = intval( $data[27]);
              $shareholder_sharegroup->new_shares = intval( $data[27]);
              $shareholder_sharegroup->current_shares = intval( $data[27]);
              $shareholder_sharegroup->company_id = $company_id;
              $shareholder_sharegroup->status = 1;

              $shareholder_sharegroup->save();
              $shareholder_sharegroupID = $shareholder_sharegroup->id;

              //add to share table
              $shareholder_share = new Share;
              if ( $data[0]  == 'no' ) {
                  $shareholder_share->company_member_id = $shareHolderId;
              }else{
                  $shareholder_share->company_firm_id = $shareHolderId;
              }
              $shareholder_share->group_id = $shareholder_sharegroupID;
              $shareholder_share->save();
          }
           
          return array(
              'message' => 'Successfully added the shareholder',
              'status' => true
          );

        


     }


    private function submitNewShareHolderForCSVupload($company_id,$request_id, $data = array()) {

        $company_info = Company::where('id',$company_id)->first();

        $address = new Address;
        $forAddress = new Address;
        $new_address_id= null;
        $new_forAddressId = null;
        $shareHolderId = null;

        if(  $data[0] == 'no' ){
            if( $data[1] == 'yes' && $data[9] && $data[10] && $data[11] && $data[12] && $data[14] ) {
                $address->province =  $data[9];
                $address->district =  $data[10];
                $address->city     =  $data[11];
                $address->address1 =  $data[12];
                $address->address2 =  $data[13];
                $address->postcode =  $data[14];
                $address->country  =  'Sri Lanka';
                $address->save();
                $new_address_id = $address->id;

            }

            if( $data[1] == 'no' && $data[9]  && $data[11] && $data[12] && $data[14] && $data[15]) {
                $forAddress->province =  $data[9];
                $forAddress->city =   $data[11];
                $address->district =  null;
                $forAddress->address1 =   $data[12];
                $forAddress->address2 =   $data[13];
                $forAddress->postcode =   $data[14];
                $forAddress->country =   $data[15];
                $forAddress->save();
                $new_forAddressId = $forAddress->id;

            }

            $newSh = new CompanyMember;
            $newSh->company_id = $company_id;
            $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
            $newSh->is_srilankan =  $data[1]; 
            $newSh->title = $data[4];
            $newSh->first_name = $data[5];
            $newSh->last_name = $data[6];
            $newSh->nic = ($data[1] == 'yes') ? $data[2] : null;
            $newSh->passport_no =($data[1] == 'no') ? $data[2] : null;;
            $newSh->address_id = $new_address_id;
            $newSh->foreign_address_id = $new_forAddressId;
            $newSh->passport_issued_country = $data[1] == 'no' ?  $data[3] : $data[15];
            $newSh->telephone = $data[17];
            $newSh->mobile = $data[16];
            $newSh->email = $data[18];
            $newSh->occupation = $data[20];
            $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
            $newSh->status =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
            $newSh->issued_share_class =  intval($data[21] ) ? intval($data[21] ) : null;
            $newSh->save();
            $shareHolderId =  $newSh->id;

            $change = new CompanyItemChange;
            $change->request_id = $request_id;
            $change->changes_type = $this->settings('ADD','key')->id;
            $change->item_id = $shareHolderId;
            $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
            $change->save();
            $change_id = $change->id;


        }

        if(  $data[0] == 'yes' ){

                $address->province = $data[9];
                $address->district =  $data[10];
                $address->city =  $data[11];
                $address->address1 =  $data[12];
                $address->address2 =  $data[13];
                $address->postcode = $data[14];
                $address->country = $data[15] ? $data[15] : 'Sri Lanka';
                $address->save();
                $new_address_id = $address->id;


                $newSh = new CompanyFirms;
                $newSh->registration_no = $data[8];
                $newSh->name = $data[7];
                $newSh->email = $data[18];
                $newSh->mobile =  $data[16];
                $newSh->date_of_appointment =  $data[19];
                $newSh->company_id = $company_id;
                $newSh->address_id = $new_address_id;
                $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                $newSh->is_srilankan =  $data[1];
                $newSh->status = $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
                $newSh->issued_share_class =  intval($data[21]) ? intval($data[21]) : null;
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

        if(  $data[22] == 'single' && intval( $data[23]) ) {

            $shareholder_sharegroup = new ShareGroup;
            $shareholder_sharegroup->type ='single_share';
            $shareholder_sharegroup->name ='single_share_no_name';
            $shareholder_sharegroup->no_of_shares = intval( $data[23] );
            $shareholder_sharegroup->new_shares = intval( $data[23] );
            $shareholder_sharegroup->current_shares = intval( $data[23] );
            $shareholder_sharegroup->company_id = $company_id;
            $shareholder_sharegroup->status = 1;
            $shareholder_sharegroup->save();
            $shareholder_sharegroupID = $shareholder_sharegroup->id;

            $shareholder_share = new Share; 
            if ( $data[0]  == 'no' ) {
                $shareholder_share->company_member_id = $shareHolderId;
            }else{ 
                $shareholder_share->company_firm_id = $shareHolderId;
            }
            $shareholder_share->group_id = $shareholder_sharegroupID;
            $shareholder_share->save();

        }

        if($data[22] == 'core' && isset($data[24]) &&  intval( $data[24]) ){
            
            if(isset($data[25] ) && intval($data[25] )) {

                $shareholder_sharegroup_info = ShareGroup::where('id', intval( $data[24]) )->first();
                
                $shareholder_sharegroup = ShareGroup::find(intval( $data[24]));
                $shareholder_sharegroup->new_shares = intval( $data[25] ) + intval($shareholder_sharegroup_info->no_of_shares);
                $shareholder_sharegroup->current_shares = intval($shareholder_sharegroup_info->no_of_shares);
                $shareholder_sharegroup->save();
            }
            
            $shareholder_share = new Share;
            if (  $data[0]  == 'no' ) {
                $shareholder_share->company_member_id = $shareHolderId;
            }else{
                 $shareholder_share->company_firm_id = $shareHolderId;
            }
            $shareholder_share->group_id =intval( $data[24]);
            $shareholder_share->save();
        }
        if(
            $data[22] == 'core' &&
             ( empty( $data[24])  ||  !intval( $data[24]) )  &&
              isset( $data[26]) && 
              $data[26] && 
            intval( $data[27]) ) {

                //add to single share group
                $shareholder_sharegroup = new ShareGroup;
                $shareholder_sharegroup->type ='core_share';
                $shareholder_sharegroup->name = $data[26];
                $shareholder_sharegroup->no_of_shares = intval( $data[27]);
                $shareholder_sharegroup->new_shares = intval( $data[27]);
                $shareholder_sharegroup->current_shares = intval( $data[27]);
                $shareholder_sharegroup->company_id = $company_id;
                $shareholder_sharegroup->status = 1;

                $shareholder_sharegroup->save();
                $shareholder_sharegroupID = $shareholder_sharegroup->id;

                //add to share table
                $shareholder_share = new Share;
                if (  $data[0]  == 'no' ) {
                    $shareholder_share->company_member_id = $shareHolderId;
                  }else{
                    $shareholder_share->company_firm_id = $shareHolderId;
                  }
                $shareholder_share->group_id = $shareholder_sharegroupID;
                $shareholder_share->save();


        }

        return array(
            'message' => 'Successfully added the shareholder',
            'status' => true
        );

         
     }
     

    private function checkNICShareholderExist($company_id, $nic ) {
    
            $members_nic_lower_member = CompanyMember::where('company_id',$company_id)
            ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
            ->where('nic', strtolower($nic))
            ->where('is_srilankan','yes')
            ->first();
            if (isset($members_nic_lower_member->id) && intval($members_nic_lower_member->id)){
               return intval($members_nic_lower_member->id);
            } else {
                $members_nic_upper_member = CompanyMember::where('company_id',$company_id)
                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                ->where('nic', strtoupper($nic))
                ->where('is_srilankan','yes')
                ->first();

                if (isset($members_nic_upper_member->id) && intval($members_nic_upper_member->id)){
                    return intval($members_nic_upper_member->id);
                } else {
                    return null;
                }
            }

    }

    private function checkPASSPORTShareholderExist($company_id, $passport_no ) {

        $members_passport_no_lower_member = CompanyMember::where('company_id',$company_id)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->where('passport_no', strtolower($passport_no))
        ->where('is_srilankan','no')
        ->first();
        if (isset($members_passport_no_lower_member->id) && intval($members_passport_no_lower_member->id)){
           return intval($members_passport_no_lower_member->id);
        } else {
            $members_passport_upper_member = CompanyMember::where('company_id',$company_id)
            ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
            ->where('passport_no', strtoupper($passport_no))
            ->where('is_srilankan','no')
            ->first();

            if (isset($members_passport_upper_member->id) && intval($members_passport_upper_member->id)){
                return intval($members_passport_upper_member->id);
            } else {
                return null;
            }
        }

    }


    private function checkComRegNumberShareholderExist($company_id, $reg_no ) {
        
        $firm_reg_no_lower_member = CompanyFirms::where('company_id',$company_id)
        ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
        ->where('registration_no', strtolower($reg_no))
        ->first();
        if (isset($firm_reg_no_lower_member->id) && intval($firm_reg_no_lower_member->id)){
           return intval($firm_reg_no_lower_member->id);
        } else {
            $firm_reg_no_upper_member =CompanyFirms::where('company_id',$company_id)
            ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
            ->where('registration_no', strtoupper($reg_no))
            ->first();

            if (isset($firm_reg_no_upper_member->id) && intval($firm_reg_no_upper_member->id)){
                return intval($firm_reg_no_upper_member->id);
            } else {
                return null;
            }
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



} // end class