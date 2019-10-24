<?php

namespace App\Http\Controllers\API\v1\AccountingAddressChange;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OtherAddress;
use App\Address;
use App\Company;
use App\CompanyItemChange;
use App\CompanyChangeRequestItem;
use App\CourtCase;
use App\ChangeAddress;
use App\CompanyMember;
use App\CompanyStatus;
use App\CompanyFirms;
use App\CompanyDocumentStatus;
use App\Setting;
use App\User;
use App\Country;
use App\People;
use App\Documents;
use App\CompanyCertificate;
use App\CompanyDocuments;
use App\Http\Helper\_helper;
use PDF;
use Storage;

class AccountingAddressChangeController extends Controller
{
    use _helper;
    // loadCompanyAddress using company id number...
    public function loadCompanyAddress(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a company id.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }
        $type = $request->type;
        if($type == 'submit'){

            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);

            $address = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
                                   ->where('company_other_addresses.company_id',$request->id)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.country','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);
            $mindate = NULL;
            if(count($address) > 0){
                $dates = array();

            foreach ($address as $key => $value) {
                $dates[] = $value->date;
            }
            
            $min = min(array_map('strtotime', $dates));
            // usort($dates, function($a, $b) {
            //     $dateTimestamp1 = strtotime($a);
            //     $dateTimestamp2 = strtotime($b);
            
            //     return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
            // });
            $mindate = date('Y-m-d', $min);
                
            }


            $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
            ->where('company_members.company_id',$request->id)
            ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
            ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

            $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
            ->where('company_member_firms.company_id',$request->id)
            ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
            ->get(['company_member_firms.id','company_member_firms.name']);

            $countries = Country::where('status',1)
            ->orderBy('name', 'asc')
            ->get();

            $date = array();
            foreach ($members as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 0,
                    'type' => 0,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "name" => $value->first_name .' '. $value->last_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
              ];
            }

            foreach ($memberfirms as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 1,
                    'type' => 1,
                    "title" => '',
                    "first_name" => $value->name,
                    "name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
              ];
            }
        

        if($address){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'address'     => $address,
                                'company'     => $company,
                                'countries'     => $countries,
                                'mindate'     => $mindate,
                                'members'     => $date
                                             
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
            ], 200);
        }

        }
        elseif($type == 'processing'){

            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.incorporation_at','companies.postfix','companies.address_id','company_certificate.registration_no as registration_no']);
                                   
            $address = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
                                   ->where('company_other_addresses.company_id',$request->id)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.country','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);                       
            $mindate = NULL;
            if(count($address) > 0){
                $dates = array();

            foreach ($address as $key => $value) {
                $dates[] = $value->date;
            }
            
            $min = min(array_map('strtotime', $dates));
            // usort($dates, function($a, $b) {
            //     $dateTimestamp1 = strtotime($a);
            //     $dateTimestamp2 = strtotime($b);
            
            //     return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
            // });
            $mindate = date('Y-m-d', $min);
                
            }
            $addressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            ->leftJoin('company_item_changes', function ($join) {
                $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
                ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
                                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                                    ->where('company_other_addresses.company_id',$request->id)
                                    ->where('company_item_changes.request_id',$request->requestID)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.country','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','settings.key as type','company_other_addresses.id as oid']);

                                   $addresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                                   ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                                  ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                                   ->where('company_other_addresses.company_id',$request->id)
                                   ->where('company_item_changes.request_id',$request->requestID)
                                  ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                                  ->where('company_other_addresses.address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
                                  ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.country','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','settings.key as type','company_other_addresses.id as oid']);

                                  $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
            ->where('company_members.company_id',$request->id)
            ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
            ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

            $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
            ->where('company_member_firms.company_id',$request->id)
            ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
            ->get(['company_member_firms.id','company_member_firms.name']);

            $case = CourtCase::where('company_court_cases.company_id',$request->id)
            ->where('company_court_cases.request_id',$request->requestID)
            ->first();

            $countries = Country::where('status',1)
            ->orderBy('name', 'asc')
            ->get();

            $date = array();
            foreach ($members as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 0,
                    "name" => $value->first_name .' '. $value->last_name,
                    'type' => 0,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
              ];
            }

            foreach ($memberfirms as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 1,
                    "name" => $value->name,
                    'type' => 1,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
              ];
            }

            $changeRequest = CompanyChangeRequestItem::leftJoin('settings','company_change_requests.signed_by_table_type','=','settings.id')->where('company_change_requests.id',$request->requestID)->get(['company_change_requests.signed_by','settings.key as tableType']);
       $signedby = $changeRequest[0]['signed_by'];
       $signedbytype = $changeRequest[0]['tableType'];

        if(($addressactive || $addresspending) && $company){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'addressactive'     => $addressactive,
                                'addresspending'     => $addresspending,
                                'company'     => $company,
                                'members'     => $date,
                                'mindate'     => $mindate,
                                'signedby' => $signedby,
                                'signedbytype' => $signedbytype,
                                'case'     => $case,
                                'address'     => $address,
                                'countries'     => $countries,
                                          
                            )
            ], 200);            
        }
        else{
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
            ], 200);
            }

        }
        elseif($type == 'resubmit'){

            $docId;
            $docIdArray = Documents::where('key','FORM_16_ADD')->select('id')->first();
            $docId = $docIdArray->id;

            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.incorporation_at','companies.postfix','companies.address_id','company_certificate.registration_no as registration_no']);
        
            $address = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
                                   ->where('company_other_addresses.company_id',$request->id)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.country','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);                       
            $mindate = NULL;
            if(count($address) > 0){
                $dates = array();

            foreach ($address as $key => $value) {
                $dates[] = $value->date;
            }
            
            $min = min(array_map('strtotime', $dates));
            // usort($dates, function($a, $b) {
            //     $dateTimestamp1 = strtotime($a);
            //     $dateTimestamp2 = strtotime($b);
            
            //     return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
            // });
            $mindate = date('Y-m-d', $min);
                
            }
            $addressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            ->leftJoin('company_item_changes', function ($join) {
                $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
                ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
                                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                                    ->where('company_other_addresses.company_id',$request->id)
                                    ->where('company_item_changes.request_id',$request->requestID)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.country','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','settings.key as type','company_other_addresses.id as oid']);

                                   
                                   $addresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                                   ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')
                                  // ->leftJoin('company_documents','company_documents.change_id','=','company_item_changes.id')
                                //   ->leftJoin('company_documents', function ($join) {
                                //     $join->on('company_documents.change_id', '=', 'company_item_changes.id')
                                //         ->where('company_documents.document_id','=' ,$docIdArray->id)
                                //         ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id);
                                //     })
                                   // ->leftJoin('settings','company_documents.status','=','settings.id')
                                   // ->where('company_documents.document_id','=' ,$docIdArray->id)
                                  //  ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->where('company_other_addresses.company_id',$request->id)
                                   ->where('company_item_changes.request_id',$request->requestID)
                                  ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                                  ->where('company_other_addresses.address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
                                  ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.country','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);
                                  //'settings.key as docStatus'

                                  $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
            ->where('company_members.company_id',$request->id)
            ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
            ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

            $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
            ->where('company_member_firms.company_id',$request->id)
            ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
            ->get(['company_member_firms.id','company_member_firms.name']);

            $case = CourtCase::where('company_court_cases.company_id',$request->id)
            ->where('company_court_cases.request_id',$request->requestID)
            ->first();

            $countries = Country::where('status',1)
            ->orderBy('name', 'asc')
            ->get();

            $date = array();
            foreach ($members as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 0,
                    "name" => $value->first_name .' '. $value->last_name,
                    'type' => 0,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
              ];
            }

            foreach ($memberfirms as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    "_id" => $value->id .'-'. 1,
                    "name" => $value->name,
                    'type' => 1,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
              ];
            }

            $external_comment_query = CompanyStatus::where('company_id',$request->id)
                                                    ->where('comment_type','=', $this->settings('COMMENT_EXTERNAL', 'key')->id )
                                                    ->where('request_id',$request->requestID)
                                                    ->where('status','=', $this->settings('ACCOUNTING_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id )
                                                    ->orderBy('id', 'desc')
                                                    ->limit(1)
                                                    ->first();
           $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                      ?  $external_comment_query->comments
                                      : '';



            $changeRequest = CompanyChangeRequestItem::leftJoin('settings','company_change_requests.signed_by_table_type','=','settings.id')->where('company_change_requests.id',$request->requestID)->get(['company_change_requests.signed_by','settings.key as tableType']);
            $signedby = $changeRequest[0]['signed_by'];
            $signedbytype = $changeRequest[0]['tableType'];

                                  

        if(($addressactive || $addresspending) && $company){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'addressactive'     => $addressactive,
                                'addresspending'     => $addresspending,
                                'company'     => $company,
                                'members'     => $date,
                                'mindate'     => $mindate,
                                'signedby' => $signedby,
                                'signedbytype' => $signedbytype,
                                'external_global_comment' => $external_global_comment,
                                'case'     => $case,
                                'countries'     => $countries,
                                          
                            )
            ], 200);            
        }
        else{
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
            ], 200);
            }

        }
        

    }

    // update accounting address change data using company newaddressid id number...
public function updateData (Request $request){

    $arr = explode("-",$request->input('signby'));
            $membid = (int)$arr[0];
            $type = (int)$arr[1];

        if ($type == 0 ){
                $signbyid = $membid;
                $signbytype = $this->settings('COMPANY_MEMBERS','key')->id;
            }
        else{
                $signbyid = $membid;
                $signbytype = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
            }

    CompanyChangeRequestItem::where('id', $request->reqid)->update(['signed_by' => $signbyid,'signed_by_table_type' => $signbytype]);

        $newadds = $request->input('addArr');
       
       
        foreach($newadds as $newadd){
            if(!empty($newadd)){
                
                OtherAddress::where('id', $newadd['id'])
                ->update(['records_kept_from' => $newadd['date']]);

                $otheraddress = OtherAddress::where('id', $newadd['id'])->first();

                if($newadd['type'] == 1){
                    $country = 'Sri Lanka';
                }
                else{
                    $country = $newadd['country'];
                }

                Address::where('id', $otheraddress->address_id)
                ->update(['address1' => $newadd['localAddress1'],
                'address2' => $newadd['localAddress2'],
                'province' => $newadd['province'],
                'district' => $newadd['district'],
                'city' => $newadd['city'],
                'gn_division' => $newadd['gnDivision'],
                'postcode' => $newadd['postcode'],
                'country' => $country]);

                

                
                                            
            }
            
        }

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->id)
        ->update($update_compnay_updated_at);

        $penalty_value = $this->penaltyCal($request->id);


    


    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
        'penalty_value' => $penalty_value,
    ], 200);



}

public function saveCourtData (Request $request){

    $type = $request->type;
    $court_status = $request->court_status;
    $caseId = $request->caseId;
    if($type == 'submit'){

        $caseid = NULL;

        if( intval($caseId) && $court_status == 'yes' ){
            $case = CourtCase::find($caseId);
            $case->company_id = $request->id;
            $case->request_id = $request->reqid;
            $case->court_status = $request->court_status;
            $case->court_name = $request->court_name;
            $case->court_date = $request->court_date;
            $case->court_case_no = $request->court_case_no;
            $case->court_penalty = $request->court_penalty;
            $case->court_period = $request->court_period;
            $case->court_discharged = $request->court_discharged;
            $case->save();
            
            $caseid = $caseId;
        }       
        elseif( intval($caseId) && $court_status == 'no'){
            $case = CourtCase::find($caseId);
            $case->delete();       
        }
        elseif( (!intval($caseId)) && $court_status == 'yes'){
            $case = new CourtCase;
            $case->company_id = $request->id;
            $case->request_id = $request->reqid;
            $case->court_status = $request->court_status;
            $case->court_name = $request->court_name;
            $case->court_date = $request->court_date;
            $case->court_case_no = $request->court_case_no;
            $case->court_penalty = $request->court_penalty;
            $case->court_period = $request->court_period;
            $case->court_discharged = $request->court_discharged;
            $case->save();    
            
            $caseid = $case->id;
        }

        $approvedRequests = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id', $request->id)
                ->where('company_change_requests.request_type', $this->settings('ACCOUNTING_ADDRESS_CHANGE', 'key')->id)
                ->where('company_change_requests.status', '=', $this->settings('ACCOUNTING_ADDRESS_CHANGE_APPROVED', 'key')->id)
                ->get();

        // penalty charges calculation 

        //mindate function
        $pendingaddresses = OtherAddress::where('company_id',$request->id)
        ->where('address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
        ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
        ->get(['company_other_addresses.id','company_other_addresses.records_kept_from']);

        if(count($pendingaddresses) > 0){

            $dates = array();
            foreach ($pendingaddresses as $key => $value) {
                $dates[] = $value->records_kept_from;
            }
            
            $min = min(array_map('strtotime', $dates));
            // usort($dates, function($a, $b) {
            //     $dateTimestamp1 = strtotime($a);
            //     $dateTimestamp2 = strtotime($b);
            
            //     return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
            // });
            $mindate = date('Y-m-d', $min);
            $todayDate = date("Y-m-d");



            $updated_date = strtotime($todayDate);
            $resolution_date = strtotime($mindate);


           $date_gaps = ($updated_date - $resolution_date) / (60*60*24);
           $date_gaps = intval($date_gaps);


           $min_date_gap = $this->settings('ACCOUNTING_ADDRESS_DELAY_PERIOD','key')->value;
           $increment_gap_dates = 30;
           $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_ACCOUNTING_ADDRESS_INITIAL','key')->value );
           $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_ACCOUNTING_ADDRESS_INCREMENT','key')->value );
           $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_ACCOUNTING_ADDRESS_MAX','key')->value );
   
           $increment_gaps = 0;
   
           $penalty_value = 0;
           if(count($approvedRequests) > 0){

            if($date_gaps < $min_date_gap ) {
                return response()->json(['status' => true,'msg' => 'less10gap', 'gap'=>$date_gaps, 'penaly_charge'=>0 ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);
            }
   
            $increment_gaps = ( $date_gaps % $increment_gap_dates == 0 ) ? $date_gaps / $increment_gap_dates : intval($date_gaps / $increment_gap_dates) + 1;
            $penalty_value  = $penalty_value + $init_panalty;
   
               if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                   $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
               }
   
               $penalty_value =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;

               return response()->json(['status' => true,'msg' => '2ndtime', 'gap'=>$date_gaps, 'penaly_charge'=>$penalty_value ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);

           }
           else {
            return response()->json(['status' => true,'msg' => '1sttime', 'gap'=>$date_gaps, 'penaly_charge'=>0 ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);
           }

        }
        else{
            return response()->json(['status' => true,'msg' => 'onlyRemove','penaly_charge'=>0 ,'caseid'=>$caseid ], 200);
        }
   
            

    }



}

function penaltyCal ($comId){

    // $type = $request->type;
    // $court_status = $request->court_status;
    // $caseId = $request->caseId;


    $approvedRequests = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->where('company_change_requests.company_id', $comId)
                ->where('company_change_requests.request_type', $this->settings('ACCOUNTING_ADDRESS_CHANGE', 'key')->id)
                ->where('company_change_requests.status', '=', $this->settings('ACCOUNTING_ADDRESS_CHANGE_APPROVED', 'key')->id)
                ->get();

        // penalty charges calculation 

        //mindate function
        $pendingaddresses = OtherAddress::where('company_id',$comId)
        ->where('address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
        ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
        ->get(['company_other_addresses.id','company_other_addresses.records_kept_from']);

        if(count($pendingaddresses) > 0){

            $dates = array();
            foreach ($pendingaddresses as $key => $value) {
                $dates[] = $value->records_kept_from;
            }
            
            $min = min(array_map('strtotime', $dates));
            // usort($dates, function($a, $b) {
            //     $dateTimestamp1 = strtotime($a);
            //     $dateTimestamp2 = strtotime($b);
            
            //     return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
            // });
            $mindate = date('Y-m-d', $min);
            $todayDate = date("Y-m-d");



            $updated_date = strtotime($todayDate);
            $resolution_date = strtotime($mindate);


           $date_gaps = ($updated_date - $resolution_date) / (60*60*24);
           $date_gaps = intval($date_gaps);


           $min_date_gap = $this->settings('ACCOUNTING_ADDRESS_DELAY_PERIOD','key')->value;
           $increment_gap_dates = 30;
           $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_ACCOUNTING_ADDRESS_INITIAL','key')->value );
           $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_ACCOUNTING_ADDRESS_INCREMENT','key')->value );
           $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_ACCOUNTING_ADDRESS_MAX','key')->value );
   
           $increment_gaps = 0;
   
           $penalty_value = 0;
           if(count($approvedRequests) > 0){

            if($date_gaps < $min_date_gap ) {
                return $penalty_value;
            }
   
            $increment_gaps = ( $date_gaps % $increment_gap_dates == 0 ) ? $date_gaps / $increment_gap_dates : intval($date_gaps / $increment_gap_dates) + 1;
            $penalty_value  = $penalty_value + $init_panalty;
   
               if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                   $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
               }
   
               $penalty_value =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;

               return $penalty_value;

           }
           else {
            return $penalty_value;
           }

        }
        else{
            return false;
        }



}


    public function saveData (Request $request){

        $userId = User::where('email', $request->input('email'))->value('id');
        $comid = $request->id;
        $reqid = $request->reqid;
        
        $arr = explode("-",$request->input('signby'));
            $membid = (int)$arr[0];
            $type = (int)$arr[1];

        if ($type == 0 ){
                $signbyid = $membid;
                $signbytype = $this->settings('COMPANY_MEMBERS','key')->id;
            }
        else{
                $signbyid = $membid;
                $signbytype = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
            }

        if( intval($reqid) ){
            $req = CompanyChangeRequestItem::find($reqid);   
        }       
        else{
            $req = new CompanyChangeRequestItem;        
        }

        $req->company_id = $comid;
        $req->request_by = $userId;
        $req->signed_by = $signbyid;
        $req->signed_by_table_type = $signbytype;
        $req->request_type = $this->settings('ACCOUNTING_ADDRESS_CHANGE', 'key')->id;
        $req->status = $this->settings('ACCOUNTING_ADDRESS_CHANGE_PROCESSING', 'key')->id;
        $req->save();

        
        // deleting all previous data if there is any

        $oldaddsremoves = OtherAddress::where('company_id',$comid)
        ->where('address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
        ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
        ->get();

        $removechanges = CompanyItemChange::where('request_id', $req->id)->get();

        if($oldaddsremoves){
            foreach($oldaddsremoves as $oldaddsremove){
                $remove = Address::where('id', $oldaddsremove->address_id)->delete();
    
            }
            $oldaddsremoves = OtherAddress::where('company_id',$comid)
            ->where('address_type','=',$this->settings('ACCOUNTING_ADDRESS', 'key')->id)
            ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
            ->delete();           
        }
        if($removechanges){
            foreach($removechanges as $removechange){
                $document = CompanyDocuments::where('change_id', $removechange->id)->first();
                if($document){
                    $delete = Storage::disk('sftp')->delete($document->path);
                    $remove = CompanyDocuments::where('change_id', $removechange->id)->delete();
                }
    
            }
            $removechanges = CompanyItemChange::where('request_id', $req->id)->delete();
        }

        // deleting all previous data if there is any               

        $newadds = $request->input('addArr');
        $remoadds = $request->input('delArr');
       
       
        foreach($newadds as $newadd){
            if(!empty($newadd)){
                if($newadd['type'] == 1){
                    $country = 'Sri Lanka';
                }
                else{
                    $country = $newadd['country'];
                }

                $address = new Address;
                $address->address1 = $newadd['localAddress1'];
                $address->address2 = $newadd['localAddress2'];
                $address->province = $newadd['province'];
                $address->district = $newadd['district'];
                $address->city = $newadd['city'];
                $address->gn_division = $newadd['gnDivision'];
                $address->postcode = $newadd['postcode'];
                $address->country = $country;
                $address->save(); 
                
                $otheradd = new OtherAddress;
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $newadd['date'];
                $otheradd->address_type = $this->settings('ACCOUNTING_ADDRESS', 'key')->id;
                $otheradd->status = $this->settings('COMMON_STATUS_PENDING', 'key')->id;
                $otheradd->save();

                $otheraddresschange = new CompanyItemChange;
                $otheraddresschange->request_id = $req->id;
                $otheraddresschange->changes_type = $this->settings('ADD', 'key')->id;
                $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                $otheraddresschange->item_id = $otheradd->id;
                $otheraddresschange->save();
                                            
            }
            
        }

        foreach($remoadds as $id){
            if(!empty($id)){

                $otheraddresschange = new CompanyItemChange;
                $otheraddresschange->request_id = $req->id;
                $otheraddresschange->changes_type = $this->settings('DELETE', 'key')->id;
                $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                $otheraddresschange->item_id = $id;
                $otheraddresschange->save(); 


            }
            
        }
        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->id)
        ->update($update_compnay_updated_at);

        $newchanges = CompanyItemChange::leftJoin('settings','company_item_changes.changes_type','=','settings.id')
        ->leftJoin('company_other_addresses','company_item_changes.item_id','=','company_other_addresses.id')
        ->leftJoin('addresses','company_other_addresses.address_id','=','addresses.id')
        ->where('company_item_changes.request_id', $req->id)->get(['addresses.city','addresses.district','addresses.province','addresses.country','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_item_changes.id','company_item_changes.item_id','settings.key as type']);

        $penalty_value = $this->penaltyCal($comid);
        if(!$penalty_value){
            $case = CourtCase::where('company_court_cases.company_id',$request->id)
            ->where('company_court_cases.request_id',$req->id)
            ->first();
            if($case){
                $case->delete();

            }

        }
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'reqID' => $req->id,
            'penalty_value' => $penalty_value,
            'data'   => array(
                'changes'     => $newchanges,
                'reqid'     => $req->id,

                          
            )
        ], 200);



    }

    //for view form 16 pdf...
public function generate_pdf(Request $request) {

    if(isset($request->oid) && isset($request->changeid)){
        
        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();

        $comId = $request->input('comId');

        $company = Company::where('id',$comId)->first();

        $company1 = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['companies.id','companies.name','companies.address_id','company_certificate.registration_no as registration_no']);
        
        $regNo =   $company1[0]['registration_no'];
        $otheraddid = $request->oid;

        $otheradd = OtherAddress::where('id',$otheraddid)->first();
        $address = Address::where('id',$otheradd->address_id)->first();

        $changeRequest = CompanyChangeRequestItem::where('id',$request->requestID)->first();

        if($changeRequest->signed_by_table_type == $this->settings('COMPANY_MEMBERS','key')->id){

            $member = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.id',$changeRequest->signed_by)
       ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

       $date = array();
                      
        $date[] = [
                    "id" => $member[0]['id'],
                    "type" => 0,
                    "title" => '',
                    "first_name" => $member[0]['first_name'],
                    "last_name" => $member[0]['last_name'],
                    "designation" => $member[0]['designation'],
                    ];
                        

        }
        else{
            $member = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.id',$changeRequest->signed_by)
       ->get(['company_member_firms.id','company_member_firms.name','settings.value as designation']);

       $date = array();
                      
        $date[] = [
                    "id" => $member[0]['id'],
                    "type" => 1,
                    "title" => '',
                    "first_name" => $member[0]['name'],
                    "last_name" => '',
                    "designation" => $member[0]['designation'],
                    ];

        }

        $o_date = $otheradd->records_kept_from;

        $day = date('d', strtotime($o_date));
        $month = date('m', strtotime($o_date));
        $year = date('Y', strtotime($o_date));

        $todayDate = date("Y-m-d");

        $day1 = date('d', strtotime($todayDate));
        $month1 = date('m', strtotime($todayDate));
        $year1 = date('Y', strtotime($todayDate));
                       
        
        $fieldset = array(
             'comName' => $company->name,
             'comPostfix' => $company->postfix, 
             'comReg' => $company->registration_no,
             'member' => $date,
             'address1' => $address->address1,
             'address2' => $address->address2, 
             'province' => $address->province, 
             'district' => $address->district,
             'city' => $address->city,
             'country' => $address->country,
             'gn_division' => $address->gn_division, 
             'postcode' => $address->postcode,
             'day' => $day, 
             'month' => $month, 
             'year' => $year,
             'day1' => $day1, 
             'month1' => $month1, 
             'year1' => $year1, 
             'first_name' => $people->first_name,
             'last_name' => $people->last_name,
             'telephone' => $people->telephone,
             'mobile' => $people->mobile,
             'email' => $people->email,
             'regNo' => $regNo,
             
 
         );

         $otheraddchange = CompanyItemChange::leftJoin('settings','company_item_changes.changes_type','=','settings.id')
        ->where('company_item_changes.item_id', $otheraddid)->get(['company_item_changes.id','company_item_changes.item_id','settings.key as type']);
        
         if(($otheraddchange[0]['type'])=='ADD'){
            $pdf = PDF::loadView('accountingaddresschange-forms/form16',$fieldset);
            $pdf->stream('form-16.pdf');
         }
         else{
            $pdf = PDF::loadView('accountingaddresschange-forms/formRem16',$fieldset);
            $pdf->stream('form-16.pdf');
         }
     
     
     
     
      

        
           
    }
    else{            
        return response()->json([
            'message' => 'We can \'t find a otheraddress.',
            'status' =>false,
        ], 200);
    }    
    
 }

 //for upload accounting addresschange pdf...
public function acaddresschangeUploadPdf(Request $request){

    if(isset($request)){

    $fileName =  uniqid().'.pdf';
    $token = md5(uniqid());

    $comId = $request->comId;
    $docType = $request->docType;
    $pdfName = $request->filename;

    $path = 'company/'.$comId;
    $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');
    
    $docId;
    $change_id;
    if($docType=='addUpload'){
        $docIdArray = Documents::where('key','FORM_16_ADD')->select('id')->first();
    $docId = $docIdArray->id;
    $change_id = $request->description;
    $description=NULL;
    }
    elseif($docType=='removeUpload'){
        $docIdArray = Documents::where('key','FORM_16_REMOVE')->select('id')->first();
    $docId = $docIdArray->id; 
    $change_id = $request->description;
    $description=NULL;
    }
    elseif($docType=='extraUpload'){
        $docIdArray = Documents::where('key','EXTRA_DOCUMENT')->select('id')->first();
    $docId = $docIdArray->id;
    $change_id=NULL;
    $description = $request->description;
    if($description=='undefined'){
            $description=NULL;
        }
    }

    $socDoc = new CompanyDocuments;
    $socDoc->document_id = $docId;
    $socDoc->company_id = $comId;
    $socDoc->name = $pdfName;
    $socDoc->file_token = $token;
    $socDoc->path = $path;
    $socDoc->change_id = $change_id;
    $socDoc->file_description = $description;
    $socDoc->request_id = $request->requestId;
    $socDoc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
    $socDoc->save();
    
    $socdocId = $socDoc->id;

      return response()->json([
        'message' => 'File uploaded now successfully.',
        'status' =>true,
        'name' =>basename($path),
        'doctype' =>$docType,
        'docid' =>$socdocId, // for delete pdf...
        'token' =>$token,
        'pdfname' =>$pdfName,
        'file_description' =>$socDoc->file_description,
        'docArray' => $docId
        ], 200);

    }

}

public function accountingaddresschangeUpdateUploadPdf(Request $request){

    if(isset($request)){

    $fileName =  uniqid().'.pdf';
    $token = md5(uniqid());

    $comId = $request->comId;
    $socDocId = $request->docId;
    $docType = $request->docType;
    $pdfName = $request->filename;

    $path = 'company/'.$comId;
    $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');


    CompanyDocuments::where('id', $request->docId)
        ->update(['status' => $this->settings('DOCUMENT_PENDING','key')->id,
        'name' => $pdfName,
        'file_token' => $token,
        'path' => $path]);
    

      return response()->json([
        'message' => 'File uploaded now successfully.',
        'status' =>true,
        'name' =>basename($path),
        'doctype' =>$docType,
        'docid' =>$socDocId, // for delete pdf...
        'token' =>$token,
        'pdfname' =>$pdfName
        ], 200);

    }

}

function deleteAcAddresschangePdfUpdate(Request $request){
    if(isset($request)){
    $docId = $request->documentId;
    $type = $request->type;
    $docstatusid = CompanyDocumentStatus::where('company_document_id', $docId)->first();
    if($docstatusid){
        if($type =='addUpload'){

            $document = CompanyDocuments::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);
            CompanyDocuments::where('id', $docId)
            ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);          
        }
        elseif($type =='removeUpload'){
            $document = CompanyDocuments::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);
            CompanyDocuments::where('id', $docId)
            ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);
        }
        elseif($type =='extraUpload'){
            $document = CompanyDocuments::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);
            CompanyDocuments::where('id', $docId)
            ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);
        }
        else{
            $docstatusid = CompanyDocumentStatus::where('company_document_id', $docId)->first();

            $document = CompanyDocuments::where('id', $docId)->first();
                if($docstatusid->status == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id){

                    $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);

                }
                else{

                    $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);

                }

            // $document = CompanyDocuments::where('id', $docId)->first();
            // $delete = Storage::disk('sftp')->delete($document->path);
            // CompanyDocuments::where('id', $docId)
            // ->update(['status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
            // 'name' => NULL,
            // 'file_token' => NULL,
            // 'path' => NULL]);
        }

        

    }
    else{
        $document = CompanyDocuments::where('id', $docId)->first();
        $delete = Storage::disk('sftp')->delete($document->path);
        $remove = CompanyDocuments::where('id', $docId)->delete();
    }
    return response()->json([
        'message' => 'File emptied successfully.',
        'status' =>true,
    ], 200);
    }
}



// for load accounting addresschange uploaded files...
public function accountingaddresschangeFile(Request $request){
    if(isset($request)){
        $type = $request->type;
        if($type == 'submit'){
            $comId = $request->comId;

            // $newchanges = CompanyItemChange::where('company_item_changes.request_id', $request->reqid)->select('company_item_changes.id')->get();
            // $In = [];
            // foreach ($newchanges as $e){
            //     array_push($In,$e['id']);

            // }
            
            // $docIds = Documents::where('key','FORM_16_ADD')->orWhere('key','FORM_16_REMOVE')->select('id')->get();
            // $InDoc = [];
            // foreach ($docIds as $d){
            //     array_push($InDoc,$d['id']);

            // }
            
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
        $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                    ->where('company_documents.company_id',$comId)
                                //    ->whereIn('company_documents.document_id',$InDoc)
                                   ->where('company_documents.request_id',$request->reqid)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->get(['company_documents.id','company_documents.name','company_documents.file_description','company_documents.change_id as description','company_documents.file_token','documents.key as docKey','documents.name as docname']);
        if(isset($uploadedPdf)){
            return response()->json([
                'file' => $uploadedPdf,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdf,
                )
            ], 200);

            }

        }
        elseif($type == 'resubmit'){

            $comId = $request->comId;

            // $newchanges = CompanyItemChange::where('company_item_changes.request_id', $request->reqid)->select('company_item_changes.id')->get();
            // $In = [];
            // foreach ($newchanges as $e){
            //     array_push($In,$e['id']);

            // }
            
            // $docIds = Documents::where('key','FORM_16_ADD')->orWhere('key','FORM_16_REMOVE')->select('id')->get();
            // $InDoc = [];
            // foreach ($docIds as $d){
            //     array_push($InDoc,$d['id']);

            // }
            
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
        $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                        ->leftJoin('company_document_status', function ($join) {
                                                $join->on('company_documents.id', '=', 'company_document_status.company_document_id')
                                                ->where(function ($query) {
                                                    $query->where('company_document_status.status', '=', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id)
                                                      ->orWhere('company_document_status.status', '=', $this->settings('DOCUMENT_REQUESTED', 'key')->id);
                                                })
                                                ->where('company_document_status.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);})
                                        ->leftJoin('settings','company_documents.status','=','settings.id')
                                        ->where('company_documents.company_id',$comId)
                                        // ->whereIn('company_documents.document_id',$InDoc)
                                        ->where('company_documents.request_id',$request->reqid)
                                        ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                        ->get(['company_documents.id','company_documents.name','company_documents.file_description','company_documents.change_id as description','company_documents.file_token','documents.key as docKey','documents.name as docname','company_document_status.company_document_id as company_document_id','company_document_status.comments as comments','settings.value as value','settings.key as setKey']);
        
        $newchanges = CompanyItemChange::leftJoin('settings','company_item_changes.changes_type','=','settings.id')
        ->leftJoin('company_other_addresses','company_item_changes.item_id','=','company_other_addresses.id')
        ->leftJoin('addresses','company_other_addresses.address_id','=','addresses.id')
        ->where('company_item_changes.request_id', $request->reqid)->get(['addresses.city','addresses.district','addresses.country','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_item_changes.id','company_item_changes.item_id','settings.key as type']);
        
        if(isset($uploadedPdf)){
            return response()->json([
                'file' => $uploadedPdf,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdf,
                    'changes'     => $newchanges
                )
            ], 200);

            }

        }
        
        
        
        

    }else{
        return response()->json([
            'status' =>false,
        ], 200);
    }

}

// to delete pdfs
function deleteAcAddresschangePdf(Request $request){
    if(isset($request)){
    $docId = $request->documentId;
    if($docId){
        $document = CompanyDocuments::where('id', $docId)->first();
        $delete = Storage::disk('sftp')->delete($document->path);
       $remove = CompanyDocuments::where('id', $docId)->delete();
    }
    return response()->json([
        'message' => 'File removed successfully.',
        'status' =>true,
    ], 200);
    }
}

public function resubmitAcAddresschange (Request $request){

    $court_status = $request->court_status;
    $caseId = $request->caseId;

    if( intval($caseId) && $court_status == 'yes' ){
            $case = CourtCase::find($caseId);
            $case->court_name = $request->court_name;
            $case->court_date = $request->court_date;
            $case->court_case_no = $request->court_case_no;
            $case->court_penalty = $request->court_penalty;
            $case->court_period = $request->court_period;
            $case->court_discharged = $request->court_discharged;
            $case->save();
        }



    CompanyChangeRequestItem::where('id', $request->reqid)
    ->update(['status' => $this->settings('ACCOUNTING_ADDRESS_CHANGE_RESUBMITTED','key')->id]);
    


    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
    ], 200);



}

}
