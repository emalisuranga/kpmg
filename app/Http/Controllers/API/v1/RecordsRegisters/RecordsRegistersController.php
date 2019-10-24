<?php

namespace App\Http\Controllers\API\v1\RecordsRegisters;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OtherAddress;
use App\Address;
use App\Company;
use App\CompanyItemChange;
use App\CompanyChangeRequestItem;
use App\ChangeAddress;
use App\CompanyMember;
use App\CompanyFirms;
use App\CompanyStatus;
use App\CourtCase;
use App\CompanyDocumentStatus;
use App\Setting;
use App\User;
use App\People;
use App\Documents;
use App\CompanyCertificate;
use App\CompanyDocuments;
use App\Http\Helper\_helper;
use PDF;
use Storage;

class RecordsRegistersController extends Controller
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

        $loggedUserEmail = $request->email;
        $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
        $createdUserId = Company::where('id', $request->id)->value('created_by');
        // checking if user has access

        $inArray = array(
            $this->settings('DERECTOR', 'key')->id,
            $this->settings('SECRETARY', 'key')->id
        );


        $membs = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->id)
        ->where('company_members.email',$loggedUserEmail)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->whereIn('company_members.designation_type', $inArray)
        ->get();

        $membfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->id)
        ->where('company_member_firms.email',$loggedUserEmail)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', $this->settings('SECRETARY','key')->id)
        ->get();

        // checking if user has access

        // if($loggedUserId === $createdUserId){
        if((count($membs) > 0) || (count($membfirms) > 0)){

        $type = $request->type;
        $comtype = $request->comType;
        if($type == 'submit'){

            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);                                   
            
            $company1 = Company::where('id',$request->id)->first();
            $address = Address::where('id',$company1->address_id)->first();

        $recordaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
                                   ->where('company_other_addresses.company_id',$request->id)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);
        
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

            if($comtype == false){

                $shareaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
               ->where('company_other_addresses.company_id',$request->id)
               ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
               ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
               ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);


               if(count($company) > 0){            
                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' =>true, // to load data from eroc company...
                    'data'   => array(
                                    'raddress'     => $recordaddress,
                                    'saddress'     => $shareaddress,
                                    'address'     => $address,
                                    'company'     => $company,
                                    'members'     => $date
                                                 
                                )
                ], 200);            
            }else{
                return response()->json([
                    'message' => 'We can \'t find any Address.',
                    'status' =>false,
                ], 200);
            }

            }
            elseif ($comtype == true) {

                $memberaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
               ->where('company_other_addresses.company_id',$request->id)
               ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
               ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
               ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.description as description','company_other_addresses.status as status','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);

               if(count($company) > 0){            
                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' =>true, // to load data from eroc company...
                    'data'   => array(
                                    'raddress'     => $recordaddress,
                                    'maddress'     => $memberaddress,
                                    'address'     => $address,
                                    'company'     => $company,
                                    'members'     => $date
                                                 
                                )
                ], 200);            
            }else{
                return response()->json([
                    'message' => 'We can \'t find any Address.',
                    'status' =>false,
                ], 200);
            }
            }

        }
        elseif($type == 'processing'){

            $company1 = Company::where('id',$request->id)->first();
            $address = Address::where('id',$company1->address_id)->first();

            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);

            // $recordaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
            //                        ->where('company_other_addresses.company_id',$request->id)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);                       

            /////////////////////////                       ///////////////// ////////////////////////////////
            // $addressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            // ->leftJoin('company_item_changes', function ($join) {
            //     $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
            //         ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
            //                         ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
            //                         ->where('company_other_addresses.company_id',$request->id)
            //                         ->where('company_item_changes.request_id',$request->requestID)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

                    $oldrecordaddressList = OtherAddress::where('company_id',$request->id)
                    ->where('address_type',$this->settings('RECORD_ADDRESS','key')->id)                                               
                    ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                    ->get();
     
                                   $oldrecaddresses = array();

                                   foreach($oldrecordaddressList as $rec){
                   
                                       $isRecEdited = CompanyItemChange::where('request_id',$request->requestID)
                                                                         ->where('changes_type',$this->settings('EDIT','key')->id)
                                                                         ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                         ->where('old_record_id',$rec['id'])
                                                                         ->first();
                   
                                       $isRecDeleted = CompanyItemChange::where('request_id',$request->requestID)
                                                                         ->where('changes_type',$this->settings('DELETE','key')->id)
                                                                         ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                         ->where('item_id',$rec['id'])
                                                                         ->first();                                  
                                       $recID =   $rec['id'];
                                       $newrecID =   null;
                                       $isdeleted =   null;
                                       $type =   null;
                                       if($isRecEdited){
                   
                                           $newEditedRec = OtherAddress::where('id',$isRecEdited->item_id)
                                                                   ->where('company_id',$request->id)
                                                                   ->where('address_type',$this->settings('RECORD_ADDRESS','key')->id)                                               
                                                                   ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                                   ->first();

                                           
                   
                                           $rec =   $newEditedRec;
                                           $recID =   $isRecEdited->old_record_id;
                                           $newrecID =   $newEditedRec->id;
                                           $type =   $isRecEdited->move_type;                    
                   
                                       }
                                       if($isRecDeleted){
                   
                                           $isdeleted =   true; 
                                           $type =   $isRecDeleted->move_type;                    
                   
                                       }               
                                        
                                       $address ='';
                                       if( $rec->address_id) {
                                          $address = Address::where('id',$rec->address_id)->first();
                                       }   
                          
                                                     
                                       $record = array(
                                          'id' => $recID,
                                          'newid' => $newrecID,
                                          'isdeleted' => $isdeleted,             
                                          'type' => $type,             
                                          'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                                          'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                                          'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                                          'gnDivision' =>  ( isset($address->gn_division) && $address->gn_division) ? $address->gn_division : '',
                                          'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                                          'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                                          'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '', 
                                          'date'      => '1970-01-01' == $rec->records_kept_from ? null : $rec->records_kept_from,
                                          'remdate'      => '1970-01-01' == $rec->removal_date ? null : $rec->removal_date,
                                          'discription'      => $rec->description,                            
                                       );
                                       $oldrecaddresses[] = $record;
                           }                       
                      ////////////////////////             //////////////////   
                                   
                                   
            $addresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->id)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

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

        if($comtype == false){

            // $shareaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
            //    ->where('company_other_addresses.company_id',$request->id)
            //    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //    ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            //    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);

            // $shareaddressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            // ->leftJoin('company_item_changes', function ($join) {
            //     $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
            //         ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
            //                         ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
            //                         ->where('company_other_addresses.company_id',$request->id)
            //                         ->where('company_item_changes.request_id',$request->requestID)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

            $oldshareaddressList = OtherAddress::where('company_id',$request->id)
                    ->where('address_type',$this->settings('SHARE_REGISTER_ADDRESS','key')->id)                                               
                    ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                    ->get();
     
                                   $oldshareaddresses = array();

                                   foreach($oldshareaddressList as $rec){
                   
                                       $isRecEdited = CompanyItemChange::where('request_id',$request->requestID)
                                                                         ->where('changes_type',$this->settings('EDIT','key')->id)
                                                                         ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                         ->where('old_record_id',$rec['id'])
                                                                         ->first();
                   
                                       $isRecDeleted = CompanyItemChange::where('request_id',$request->requestID)
                                                                         ->where('changes_type',$this->settings('DELETE','key')->id)
                                                                         ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                         ->where('item_id',$rec['id'])
                                                                         ->first();                                  
                                       $recID =   $rec['id'];
                                       $newrecID =   null;
                                       $isdeleted =   null;
                                       $type =   null;
                                       if($isRecEdited){
                   
                                           $newEditedRec = OtherAddress::where('id',$isRecEdited->item_id)
                                                                   ->where('company_id',$request->id)
                                                                   ->where('address_type',$this->settings('SHARE_REGISTER_ADDRESS','key')->id)                                               
                                                                   ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                                   ->first();

                                           
                   
                                           $rec =   $newEditedRec;
                                           $recID =   $isRecEdited->old_record_id;
                                           $newrecID =   $newEditedRec->id;
                                           $type =   $isRecEdited->move_type;                    
                   
                                       }
                                       if($isRecDeleted){
                   
                                           $isdeleted =   true; 
                                           $type =   $isRecDeleted->move_type;                    
                   
                                       }               
                                        
                                       $address ='';
                                       if( $rec->address_id) {
                                          $address = Address::where('id',$rec->address_id)->first();
                                       }   
                          
                                                     
                                       $record = array(
                                          'id' => $recID,
                                          'newid' => $newrecID,
                                          'isdeleted' => $isdeleted,             
                                          'type' => $type,             
                                          'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                                          'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                                          'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                                          'gnDivision' =>  ( isset($address->gn_division) && $address->gn_division) ? $address->gn_division : '',
                                          'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                                          'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                                          'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '', 
                                          'date'      => '1970-01-01' == $rec->records_kept_from ? null : $rec->records_kept_from,
                                          'remdate'      => '1970-01-01' == $rec->removal_date ? null : $rec->removal_date,
                                          'discription'      => $rec->description,                            
                                       );
                                       $oldshareaddresses[] = $record;
                           }                       
                      ////////////////////////             //////////////////                       


            $shareaddresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->id)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);


           if(count($company) > 0){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                    // 'raddress'     => $recordaddress,
                    'oldrecaddresses'     => $oldrecaddresses,
                    'addresspending'     => $addresspending,
                    // 'saddress'     => $shareaddress,
                    'oldshareaddresses'     => $oldshareaddresses,
                    'shareaddresspending'     => $shareaddresspending,
                    'address'     => $address,
                    'company'     => $company,
                    'members'     => $date,
                    'signedby' => $signedby,
                    'signedbytype' => $signedbytype,
                    'case'     => $case,
                                             
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find any Address.',
                'status' =>false,
            ], 200);
        }

        }
        elseif ($comtype == true) {

            // $memberaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
            //    ->where('company_other_addresses.company_id',$request->id)
            //    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //    ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
            //    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.description as description','company_other_addresses.status as status','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);

            // $memberaddressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            // ->leftJoin('company_item_changes', function ($join) {
            //     $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
            //         ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
            //                         ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
            //                         ->where('company_other_addresses.company_id',$request->id)
            //                         ->where('company_item_changes.request_id',$request->requestID)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

        $oldmemberaddressList = OtherAddress::where('company_id',$request->id)
                    ->where('address_type',$this->settings('MEMBER_REGISTER_ADDRESS','key')->id)                                               
                    ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                    ->get();
     
                                   $oldmemberaddresses = array();

                                   foreach($oldmemberaddressList as $rec){
                   
                                       $isRecEdited = CompanyItemChange::where('request_id',$request->requestID)
                                                                         ->where('changes_type',$this->settings('EDIT','key')->id)
                                                                         ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                         ->where('old_record_id',$rec['id'])
                                                                         ->first();
                   
                                       $isRecDeleted = CompanyItemChange::where('request_id',$request->requestID)
                                                                         ->where('changes_type',$this->settings('DELETE','key')->id)
                                                                         ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                         ->where('item_id',$rec['id'])
                                                                         ->first();                                  
                                       $recID =   $rec['id'];
                                       $newrecID =   null;
                                       $isdeleted =   null;
                                       $type =   null;
                                       if($isRecEdited){
                   
                                           $newEditedRec = OtherAddress::where('id',$isRecEdited->item_id)
                                                                   ->where('company_id',$request->id)
                                                                   ->where('address_type',$this->settings('MEMBER_REGISTER_ADDRESS','key')->id)                                               
                                                                   ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                                   ->first();

                                           
                   
                                           $rec =   $newEditedRec;
                                           $recID =   $isRecEdited->old_record_id;
                                           $newrecID =   $newEditedRec->id;
                                           $type =   $isRecEdited->move_type;                    
                   
                                       }
                                       if($isRecDeleted){
                   
                                           $isdeleted =   true; 
                                           $type =   $isRecDeleted->move_type;                    
                   
                                       }               
                                        
                                       $address ='';
                                       if( $rec->address_id) {
                                          $address = Address::where('id',$rec->address_id)->first();
                                       }   
                          
                                                     
                                       $record = array(
                                          'id' => $recID,
                                          'newid' => $newrecID,
                                          'isdeleted' => $isdeleted,             
                                          'type' => $type,             
                                          'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                                          'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                                          'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                                          'gnDivision' =>  ( isset($address->gn_division) && $address->gn_division) ? $address->gn_division : '',
                                          'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                                          'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                                          'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '', 
                                          'date'      => '1970-01-01' == $rec->records_kept_from ? null : $rec->records_kept_from,
                                          'remdate'      => '1970-01-01' == $rec->removal_date ? null : $rec->removal_date,
                                          'discription'      => $rec->description,                            
                                       );
                                       $oldmemberaddresses[] = $record;
                           }                       
                      ////////////////////////             //////////////////    



            $memberaddresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->id)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

           if(count($company) > 0){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                    // 'raddress'     => $recordaddress,
                    'oldrecaddresses'     => $oldrecaddresses,
                    'addresspending'     => $addresspending,
                    // 'maddress'     => $memberaddress,
                    'oldmemberaddresses'     => $oldmemberaddresses,
                    'memberaddresspending'     => $memberaddresspending,
                    'address'     => $address,
                    'company'     => $company,
                    'members'     => $date,
                    'signedby' => $signedby,
                    'signedbytype' => $signedbytype,
                    'case'     => $case,
                                             
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find any Address.',
                'status' =>false,
            ], 200);
        }
        }
    }
        elseif($type == 'resubmit'){

            $company1 = Company::where('id',$request->id)->first();
            $address = Address::where('id',$company1->address_id)->first();

            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);
            
            // $recordaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
            //                        ->where('company_other_addresses.company_id',$request->id)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);                       

            // $addressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            // ->leftJoin('company_item_changes', function ($join) {
            //     $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
            //         ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
            //                         ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
            //                         ->where('company_other_addresses.company_id',$request->id)
            //                         ->where('company_item_changes.request_id',$request->requestID)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);


            $oldrecordaddressList = OtherAddress::where('company_id',$request->id)
            ->where('address_type',$this->settings('RECORD_ADDRESS','key')->id)                                               
            ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->get();

                           $oldrecaddresses = array();

                           foreach($oldrecordaddressList as $rec){
           
                               $isRecEdited = CompanyItemChange::where('request_id',$request->requestID)
                                                                 ->where('changes_type',$this->settings('EDIT','key')->id)
                                                                 ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                 ->where('old_record_id',$rec['id'])
                                                                 ->first();
           
                               $isRecDeleted = CompanyItemChange::where('request_id',$request->requestID)
                                                                 ->where('changes_type',$this->settings('DELETE','key')->id)
                                                                 ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                 ->where('item_id',$rec['id'])
                                                                 ->first();                                  
                               $recID =   $rec['id'];
                               $newrecID =   null;
                               $isdeleted =   null;
                               $type =   null;
                               if($isRecEdited){
           
                                   $newEditedRec = OtherAddress::where('id',$isRecEdited->item_id)
                                                           ->where('company_id',$request->id)
                                                           ->where('address_type',$this->settings('RECORD_ADDRESS','key')->id)                                               
                                                           ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                           ->first();

                                   
           
                                   $rec =   $newEditedRec;
                                   $recID =   $isRecEdited->old_record_id;
                                   $newrecID =   $newEditedRec->id;
                                   $type =   $isRecEdited->move_type;                    
           
                               }
                               if($isRecDeleted){
           
                                   $isdeleted =   true; 
                                   $type =   $isRecDeleted->move_type;                    
           
                               }               
                                
                               $address ='';
                               if( $rec->address_id) {
                                  $address = Address::where('id',$rec->address_id)->first();
                               }   
                  
                                             
                               $record = array(
                                  'id' => $recID,
                                  'newid' => $newrecID,
                                  'isdeleted' => $isdeleted,             
                                  'type' => $type,             
                                  'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                                  'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                                  'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                                  'gnDivision' =>  ( isset($address->gn_division) && $address->gn_division) ? $address->gn_division : '',
                                  'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                                  'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                                  'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '', 
                                  'date'      => '1970-01-01' == $rec->records_kept_from ? null : $rec->records_kept_from,
                                  'remdate'      => '1970-01-01' == $rec->removal_date ? null : $rec->removal_date,
                                  'discription'      => $rec->description,                            
                               );
                               $oldrecaddresses[] = $record;
                   }                       
              ////////////////////////             ////////////////// 

            $addresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->id)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

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

       $external_comment_query = CompanyStatus::where('company_id',$request->id)
                                                    ->where('comment_type','=', $this->settings('COMMENT_EXTERNAL', 'key')->id )
                                                    ->where('request_id',$request->requestID)
                                                    ->where('status','=', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id )
                                                    ->orderBy('id', 'desc')
                                                    ->limit(1)
                                                    ->first();
           $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                      ?  $external_comment_query->comments
                                      : '';

        if($comtype == false){
            // $shareaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
            //    ->where('company_other_addresses.company_id',$request->id)
            //    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //    ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            //    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);


            // $shareaddressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            // ->leftJoin('company_item_changes', function ($join) {
            //     $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
            //         ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
            //                         ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
            //                         ->where('company_other_addresses.company_id',$request->id)
            //                         ->where('company_item_changes.request_id',$request->requestID)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

            $oldshareaddressList = OtherAddress::where('company_id',$request->id)
            ->where('address_type',$this->settings('SHARE_REGISTER_ADDRESS','key')->id)                                               
            ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->get();

                           $oldshareaddresses = array();

                           foreach($oldshareaddressList as $rec){
           
                               $isRecEdited = CompanyItemChange::where('request_id',$request->requestID)
                                                                 ->where('changes_type',$this->settings('EDIT','key')->id)
                                                                 ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                 ->where('old_record_id',$rec['id'])
                                                                 ->first();
           
                               $isRecDeleted = CompanyItemChange::where('request_id',$request->requestID)
                                                                 ->where('changes_type',$this->settings('DELETE','key')->id)
                                                                 ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                 ->where('item_id',$rec['id'])
                                                                 ->first();                                  
                               $recID =   $rec['id'];
                               $newrecID =   null;
                               $isdeleted =   null;
                               $type =   null;
                               if($isRecEdited){
           
                                   $newEditedRec = OtherAddress::where('id',$isRecEdited->item_id)
                                                           ->where('company_id',$request->id)
                                                           ->where('address_type',$this->settings('SHARE_REGISTER_ADDRESS','key')->id)                                               
                                                           ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                           ->first();

                                   
           
                                   $rec =   $newEditedRec;
                                   $recID =   $isRecEdited->old_record_id;
                                   $newrecID =   $newEditedRec->id;
                                   $type =   $isRecEdited->move_type;                    
           
                               }
                               if($isRecDeleted){
           
                                   $isdeleted =   true; 
                                   $type =   $isRecDeleted->move_type;                    
           
                               }               
                                
                               $address ='';
                               if( $rec->address_id) {
                                  $address = Address::where('id',$rec->address_id)->first();
                               }   
                  
                                             
                               $record = array(
                                  'id' => $recID,
                                  'newid' => $newrecID,
                                  'isdeleted' => $isdeleted,             
                                  'type' => $type,             
                                  'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                                  'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                                  'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                                  'gnDivision' =>  ( isset($address->gn_division) && $address->gn_division) ? $address->gn_division : '',
                                  'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                                  'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                                  'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '', 
                                  'date'      => '1970-01-01' == $rec->records_kept_from ? null : $rec->records_kept_from,
                                  'remdate'      => '1970-01-01' == $rec->removal_date ? null : $rec->removal_date,
                                  'discription'      => $rec->description,                            
                               );
                               $oldshareaddresses[] = $record;
                   }                       
              ////////////////////////             //////////////////

            $shareaddresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->id)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);


           if(count($company) > 0){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                    //'raddress'     => $recordaddress,
                    //'addressactive'     => $addressactive,
                    'addresspending'     => $addresspending,
                    'oldrecaddresses'     => $oldrecaddresses,
                    //'saddress'     => $shareaddress,
                    //'shareaddressactive'     => $shareaddressactive,
                    'shareaddresspending'     => $shareaddresspending,
                    'oldshareaddresses'     => $oldshareaddresses,
                    'address'     => $address,
                    'company'     => $company,
                    'members'     => $date,
                    'signedby' => $signedby,
                    'signedbytype' => $signedbytype,
                    'external_global_comment' => $external_global_comment,
                    'case'     => $case,
                                             
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find any Address.',
                'status' =>false,
            ], 200);
        }

        }
        elseif ($comtype == true) {
            // $memberaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
            //    ->where('company_other_addresses.company_id',$request->id)
            //    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //    ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
            //    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.description as description','company_other_addresses.status as status','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);


            // $memberaddressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            // ->leftJoin('company_item_changes', function ($join) {
            //     $join->on('company_other_addresses.id', '=', 'company_item_changes.item_id')
            //         ->where('company_item_changes.item_table_type', '=', $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id);})
            //                         ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
            //                         ->where('company_other_addresses.company_id',$request->id)
            //                         ->where('company_item_changes.request_id',$request->requestID)
            //                        ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
            //                        ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
            //                        ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

            $oldmemberaddressList = OtherAddress::where('company_id',$request->id)
            ->where('address_type',$this->settings('MEMBER_REGISTER_ADDRESS','key')->id)                                               
            ->where('status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
            ->get();

                           $oldmemberaddresses = array();

                           foreach($oldmemberaddressList as $rec){
           
                               $isRecEdited = CompanyItemChange::where('request_id',$request->requestID)
                                                                 ->where('changes_type',$this->settings('EDIT','key')->id)
                                                                 ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                 ->where('old_record_id',$rec['id'])
                                                                 ->first();
           
                               $isRecDeleted = CompanyItemChange::where('request_id',$request->requestID)
                                                                 ->where('changes_type',$this->settings('DELETE','key')->id)
                                                                 ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                                 ->where('item_id',$rec['id'])
                                                                 ->first();                                  
                               $recID =   $rec['id'];
                               $newrecID =   null;
                               $isdeleted =   null;
                               $type =   null;
                               if($isRecEdited){
           
                                   $newEditedRec = OtherAddress::where('id',$isRecEdited->item_id)
                                                           ->where('company_id',$request->id)
                                                           ->where('address_type',$this->settings('MEMBER_REGISTER_ADDRESS','key')->id)                                               
                                                           ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                                                           ->first();

                                   
           
                                   $rec =   $newEditedRec;
                                   $recID =   $isRecEdited->old_record_id;
                                   $newrecID =   $newEditedRec->id;
                                   $type =   $isRecEdited->move_type;                    
           
                               }
                               if($isRecDeleted){
           
                                   $isdeleted =   true; 
                                   $type =   $isRecDeleted->move_type;                    
           
                               }               
                                
                               $address ='';
                               if( $rec->address_id) {
                                  $address = Address::where('id',$rec->address_id)->first();
                               }   
                  
                                             
                               $record = array(
                                  'id' => $recID,
                                  'newid' => $newrecID,
                                  'isdeleted' => $isdeleted,             
                                  'type' => $type,             
                                  'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                                  'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                                  'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                                  'gnDivision' =>  ( isset($address->gn_division) && $address->gn_division) ? $address->gn_division : '',
                                  'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                                  'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                                  'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '', 
                                  'date'      => '1970-01-01' == $rec->records_kept_from ? null : $rec->records_kept_from,
                                  'remdate'      => '1970-01-01' == $rec->removal_date ? null : $rec->removal_date,
                                  'discription'      => $rec->description,                            
                               );
                               $oldmemberaddresses[] = $record;
                   }                       
              ////////////////////////             //////////////////  

            $memberaddresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->id)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

           if(count($company) > 0){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                    //'raddress'     => $recordaddress,
                    //'addressactive'     => $addressactive,
                    'oldrecaddresses'     => $oldrecaddresses,
                    'addresspending'     => $addresspending,
                    //'maddress'     => $memberaddress,
                    //'memberaddressactive'     => $memberaddressactive,
                    'memberaddresspending'     => $memberaddresspending,
                    'oldmemberaddresses'     => $oldmemberaddresses,
                    'address'     => $address,
                    'company'     => $company,
                    'members'     => $date,
                    'signedby' => $signedby,
                    'signedbytype' => $signedbytype,
                    'external_global_comment' => $external_global_comment,
                    'case'     => $case,
                                             
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find any Address.',
                'status' =>false,
            ], 200);
        }
        }

        }

        }
        else{
            return response()->json([
                'message' => 'Unauthorized user is trying a company change',
                'status' =>false,
            ], 200);
        }
        
        

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
    
            // $approvedRequests = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
            //         ->where('company_change_requests.company_id', $request->id)
            //         ->where('company_change_requests.request_type', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE', 'key')->id)
            //         ->where('company_change_requests.status', '=', $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_APPROVED', 'key')->id)
            //         ->get();
    
            // penalty charges calculation //
    
            //mindate function
            $pendingaddresses = OtherAddress::where('company_id',$request->id)
            ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
            ->where(function($q) {
                $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
                  ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
                  ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);})
            ->get(['company_other_addresses.id','company_other_addresses.records_kept_from']);

            $pendingeditaddresses = OtherAddress::where('company_id',$request->id)
        ->where('status','=',$this->settings('COMMON_STATUS_EDIT', 'key')->id)
        ->where(function($q) {
            $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);})
        ->get(['company_other_addresses.id','company_other_addresses.records_kept_from']);

        $pendingdeleteaddresses = OtherAddress::where('company_id',$request->id)
        ->where('status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
        ->where('removal_date','!=',NULL)
        ->where(function($q) {
            $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);})
        ->get(['company_other_addresses.id','company_other_addresses.removal_date']);

        if(count($pendingaddresses) > 0 || count($pendingeditaddresses) > 0 || count($pendingdeleteaddresses) > 0){
    
                $dates = array();
                foreach ($pendingaddresses as $key => $value) {
                    $dates[] = $value->records_kept_from;
                }
                foreach ($pendingeditaddresses as $key => $value) {
                    $dates[] = $value->records_kept_from;
                }
                foreach ($pendingdeleteaddresses as $key => $value) {
                    $dates[] = $value->removal_date;
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
    
    
               $min_date_gap = $this->settings('RECORDS_REGISTER_ADDRESS_DELAY_PERIOD','key')->value;
               $increment_gap_dates = 30;
               $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_RECORDS_REGISTER_ADDRESS_INITIAL','key')->value );
               $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_RECORDS_REGISTER_ADDRESS_INCREMENT','key')->value );
               $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_RECORDS_REGISTER_ADDRESS_MAX','key')->value );
       
               $increment_gaps = 0;
       
               $penalty_value = 0;
            //    if(count($approvedRequests) > 0){
               if(true){
    
                if($date_gaps < $min_date_gap ) {
                    return response()->json(['status' => true,'msg' => 'less10gap', 'gap'=>$date_gaps, 'penaly_charge'=>0 ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);
                }
       
                $increment_gaps = ( $date_gaps % $increment_gap_dates == 0 ) ? $date_gaps / $increment_gap_dates : intval($date_gaps / $increment_gap_dates) + 1;
                $penalty_value  = $penalty_value + $init_panalty;
       
                   if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                       $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
                   }
       
                   $penalty_value =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;
    
                   return response()->json(['status' => true,'msg' => 'alwaysPenalty', 'gap'=>$date_gaps, 'penaly_charge'=>$penalty_value ,'caseid'=>$caseid ,'mindate'=>$mindate ], 200);
    
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

    public function changeData (Request $request){

        

        $userId = User::where('email', $request->input('email'))->value('id');
        $comid = $request->companyId;
        $reqid = $request->reqid;
        $rectype = $request->type;
        $action = $request->action;
        $oldid = $request->id;
        $signbyid = null;
        $signbytype = null;
        if(!empty($request->input('signby'))){
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

        }

        

        if( intval($reqid) ){
            $req = CompanyChangeRequestItem::find($reqid);
            if(!empty($req)){
                if($req->status == $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id){
                    $status = $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id;

                }
                elseif($req->status == $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_PROCESSING', 'key')->id){
                    $status = $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_PROCESSING', 'key')->id;

                }
            }   
        }       
        else{
            $req = new CompanyChangeRequestItem;
            $status = $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_PROCESSING', 'key')->id;        
        }

        $req->company_id = $comid;
        $req->request_by = $userId;
        $req->signed_by = $signbyid;
        $req->signed_by_table_type = $signbytype;
        $req->request_type = $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE', 'key')->id;
        $req->status = $status;
        $req->save();
             

        $records = $request->input('records');
        $shares = $request->input('shares');
        $members = $request->input('members');
        
        if($rectype == 'record'){
       // for record addresses
        foreach($records as $record){
            if(!empty($record)){
                if($record['id'] == $oldid){
                    if($action == 'remove'){

                        $otheraddresschange = new CompanyItemChange;
                        $otheraddresschange->request_id = $req->id;
                        $otheraddresschange->changes_type = $this->settings('DELETE', 'key')->id;
                        $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                        $otheraddresschange->item_id = $record['id'];
                        $otheraddresschange->move_type =  $record['type'];
                        $otheraddresschange->save();
                        
                        OtherAddress::where('id',$record['id'])
                       ->update(['removal_date' => $record['remdate'],  
                                ]);

                    }
                    else{
                        
                $description = $record['discription']; 

                $address = new Address;
                $address->address1 = $record['localAddress1'];
                $address->address2 = $record['localAddress2'];
                $address->province = is_array($record['province']) ? $record['province']['description_en'] : $record['province'];
                $address->district = is_array($record['district']) ? $record['district']['description_en'] : $record['district'];
                $address->city = is_array($record['city']) ? $record['city']['description_en'] : $record['city'];
                $address->gn_division = is_array($record['gnDivision']) ? $record['gnDivision']['description_en'] : $record['gnDivision'];
                $address->postcode = $record['postcode'];
                $address->country = 'Sri Lanka';
                $address->save();
                
                $isNewRecord;
                if(isset($record['newid']) && $record['newid'] ){
                    $otheradd = OtherAddress::find($record['newid']);
                    $isNewRecord = false;
                }else{
                    $otheradd = new OtherAddress;
                    $isNewRecord = true;
                }               
                
                
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $record['date'];
                $otheradd->description = $description;
                $otheradd->address_type = $this->settings('RECORD_ADDRESS', 'key')->id;
                $otheradd->status = $this->settings('COMMON_STATUS_EDIT', 'key')->id;
                $otheradd->save();

                if($isNewRecord){
                $otheraddresschange = new CompanyItemChange;
                $otheraddresschange->request_id = $req->id;
                $otheraddresschange->changes_type = $this->settings('EDIT', 'key')->id;
                $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                $otheraddresschange->item_id = $otheradd->id;
                $otheraddresschange->old_record_id =  $record['id'];
                $otheraddresschange->move_type =  $record['type'];
                $otheraddresschange->save();
                }
                else{  
                    
                    CompanyItemChange::where('request_id', $req->id)
                    ->where('changes_type',$this->settings('EDIT','key')->id)
                    ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                    ->where('old_record_id',$record['id'])
                    ->where('item_id',$otheradd->id)
                    ->update(['move_type' => $record['type']]);                   

                }

                    }

                
                                            
            }
        }
            
        }
    }
    elseif($rectype == 'share'){
        // for record addresses
         foreach($shares as $srecord){
             if(!empty($srecord)){
                 if($srecord['id'] == $oldid){

                    if($action == 'remove'){

                        $otheraddresschange = new CompanyItemChange;
                        $otheraddresschange->request_id = $req->id;
                        $otheraddresschange->changes_type = $this->settings('DELETE', 'key')->id;
                        $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                        $otheraddresschange->item_id = $srecord['id'];
                        $otheraddresschange->move_type =  $srecord['type'];
                        $otheraddresschange->save();
                        
                        OtherAddress::where('id',$srecord['id'])
                       ->update(['removal_date' => $srecord['remdate'],  
                                ]);

                    }
                    else{
                        $description = $srecord['discription'];
 
                 $address = new Address;
                 $address->address1 = $srecord['localAddress1'];
                 $address->address2 = $srecord['localAddress2'];
                 $address->province = is_array($srecord['province']) ? $srecord['province']['description_en'] : $srecord['province'];
                $address->district = is_array($srecord['district']) ? $srecord['district']['description_en'] : $srecord['district'];
                $address->city = is_array($srecord['city']) ? $srecord['city']['description_en'] : $srecord['city'];
                $address->gn_division = is_array($srecord['gnDivision']) ? $srecord['gnDivision']['description_en'] : $srecord['gnDivision'];
                 $address->postcode = $srecord['postcode'];
                 $address->country = 'Sri Lanka';
                 $address->save();
                 
                 $isNewSRecord;
                 if(isset($srecord['newid']) && $srecord['newid'] ){
                     $otheradd = OtherAddress::find($srecord['newid']);
                     $isNewSRecord = false;
                 }else{
                     $otheradd = new OtherAddress;
                     $isNewSRecord = true;
                 }   
                 
                 
                 $otheradd->address_id = $address->id;
                 $otheradd->company_id = $comid;
                 $otheradd->records_kept_from = $srecord['date'];
                 $otheradd->description = $description;
                 $otheradd->address_type = $this->settings('SHARE_REGISTER_ADDRESS', 'key')->id;
                 $otheradd->status = $this->settings('COMMON_STATUS_EDIT', 'key')->id;
                 $otheradd->save();
 
                 if($isNewSRecord){
                 $otheraddresschange = new CompanyItemChange;
                 $otheraddresschange->request_id = $req->id;
                 $otheraddresschange->changes_type = $this->settings('EDIT', 'key')->id;
                 $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                 $otheraddresschange->item_id = $otheradd->id;
                 $otheraddresschange->old_record_id =  $srecord['id'];
                 $otheraddresschange->move_type =  $srecord['type'];
                 $otheraddresschange->save();
                 }
                 else{  
                     
                     CompanyItemChange::where('request_id', $req->id)
                     ->where('changes_type',$this->settings('EDIT','key')->id)
                     ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                     ->where('old_record_id',$srecord['id'])
                     ->where('item_id',$otheradd->id)
                     ->update(['move_type' => $srecord['type']]);                   
 
                 }

                    }   
                 
 
                 
                                             
             }
         }
             
         }
     }
     elseif($rectype == 'member'){
        // for member addresses
         foreach($members as $mrecord){
             if(!empty($mrecord)){
                 if($mrecord['id'] == $oldid){

                    if($action == 'remove'){

                        $otheraddresschange = new CompanyItemChange;
                        $otheraddresschange->request_id = $req->id;
                        $otheraddresschange->changes_type = $this->settings('DELETE', 'key')->id;
                        $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                        $otheraddresschange->item_id = $mrecord['id'];
                        $otheraddresschange->move_type =  $mrecord['type'];
                        $otheraddresschange->save();
                        
                        OtherAddress::where('id',$mrecord['id'])
                       ->update(['removal_date' => $mrecord['remdate'],  
                                ]);

                    }
                    else{

                        $description = $mrecord['discription'];
 
                 $address = new Address;
                 $address->address1 = $mrecord['localAddress1'];
                 $address->address2 = $mrecord['localAddress2'];
                 $address->province = is_array($mrecord['province']) ? $mrecord['province']['description_en'] : $mrecord['province'];
                $address->district = is_array($mrecord['district']) ? $mrecord['district']['description_en'] : $mrecord['district'];
                $address->city = is_array($mrecord['city']) ? $mrecord['city']['description_en'] : $mrecord['city'];
                $address->gn_division = is_array($mrecord['gnDivision']) ? $mrecord['gnDivision']['description_en'] : $mrecord['gnDivision'];
                 $address->postcode = $mrecord['postcode'];
                 $address->country = 'Sri Lanka';
                 $address->save();
                 
                 $isNewMRecord;
                 if(isset($mrecord['newid']) && $mrecord['newid'] ){
                     $otheradd = OtherAddress::find($mrecord['newid']);
                     $isNewMRecord = false;
                 }else{
                     $otheradd = new OtherAddress;
                     $isNewMRecord = true;
                 }               
                 
                 
                 $otheradd->address_id = $address->id;
                 $otheradd->company_id = $comid;
                 $otheradd->records_kept_from = $mrecord['date'];
                 $otheradd->description = $description;
                 $otheradd->address_type = $this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id;
                 $otheradd->status = $this->settings('COMMON_STATUS_EDIT', 'key')->id;
                 $otheradd->save();
 
                 if($isNewMRecord){
                 $otheraddresschange = new CompanyItemChange;
                 $otheraddresschange->request_id = $req->id;
                 $otheraddresschange->changes_type = $this->settings('EDIT', 'key')->id;
                 $otheraddresschange->item_table_type = $this->settings('EROC_COMPANY_OTHER_ADDRESSES', 'key')->id;
                 $otheraddresschange->item_id = $otheradd->id;
                 $otheraddresschange->old_record_id =  $mrecord['id'];
                 $otheraddresschange->move_type =  $mrecord['type'];
                 $otheraddresschange->save();
                 }
                 else{  
                     
                     CompanyItemChange::where('request_id', $req->id)
                     ->where('changes_type',$this->settings('EDIT','key')->id)
                     ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                     ->where('old_record_id',$mrecord['id'])
                     ->where('item_id',$otheradd->id)
                     ->update(['move_type' => $mrecord['type']]);                   
 
                 }

                    }
                 
 
                 
                                             
             }
         }
             
         }
     }

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->companyId)
        ->update($update_compnay_updated_at);

        // $penalty_value = $this->penaltyCal($comid);
        // if(!$penalty_value){
        //     $case = CourtCase::where('company_court_cases.company_id',$request->id)
        //     ->where('company_court_cases.request_id',$req->id)
        //     ->first();
        //     if($case){
        //         $case->delete();

        //     }

        // }

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'reqID' => $req->id,
            //'penalty_value' => $penalty_value,
            'data'   => array(
                'reqid'     => $req->id,

                          
            )
        ], 200);



    }

    public function revertData (Request $request){

        $comid = $request->companyId;
        $reqid = $request->reqid;
        $oid = $request->id;
        $type = $request->type;
        if(true){

            $isRecEdited = CompanyItemChange::where('request_id',$request->reqid)
            ->where('changes_type',$this->settings('EDIT','key')->id)
            ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
            ->where('old_record_id',$oid)
            ->first();

            $isRecDeleted = CompanyItemChange::where('request_id',$request->reqid)
                        ->where('changes_type',$this->settings('DELETE','key')->id)
                        ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                        ->where('item_id',$oid)
                        ->first();

            if($isRecEdited){

            $newEditedRec = OtherAddress::where('id',$isRecEdited->item_id)
                ->where('company_id',$comid)                                              
                ->where('status',$this->settings('COMMON_STATUS_EDIT','key')->id)
                ->first();



                if($newEditedRec){   
                                               
                    $newEditedRecDelete = OtherAddress::where('id',$isRecEdited->item_id)
                                        ->delete();
                }
                $isRecEditedItem = CompanyItemChange::where('id',$isRecEdited->id)
                                              ->delete();                    

            }
            if($isRecDeleted){

                OtherAddress::where('id',$isRecDeleted->item_id)
                ->update(['removal_date' => NULL]);
                         
                $isRecDeletedItem = CompanyItemChange::where('id',$isRecDeleted->id)
                         ->delete();                   

            }

            if($isRecEdited || $isRecDeleted){
                return response()->json([
                    'message' => 'Successfully reverted',
                    'status' =>true             
                ], 200);
                }
                else{
                return response()->json([
                    'message' => 'Please try again',
                    'status' =>false            
                ], 200);
                }

        }

    }

    public function deleteData (Request $request){

        $comid = $request->companyId;
        $reqid = $request->reqid;
        $oid = $request->id;
        $type = $request->type;
        if(true){
        $remove = OtherAddress::where('id', $oid)->delete();
        $removeItem = CompanyItemChange::where('item_id', $oid)
        ->where('request_id', $reqid)
        ->where('changes_type', $this->settings('ADD', 'key')->id)
        ->delete();
        if($remove && $removeItem){
            return response()->json([
                'message' => 'Successfully deleted',
                'status' =>true             
            ], 200);
            }else{
            return response()->json([
                'message' => 'Please try again',
                'status' =>false            
            ], 200);
            }

        }

    }

    public function saveData (Request $request){

        $type = $request->type;
        if($type == 'submit'){

        $userId = User::where('email', $request->input('email'))->value('id');
        $comid = $request->id;
        $reqid = $request->reqid;
        $signbyid = null;
        $signbytype = null;
        if(!empty($request->input('signby'))){
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
        $req->request_type = $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE', 'key')->id;
        $req->status = $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_PROCESSING', 'key')->id;
        $req->save();

        
        // deleting all previous data if there is any 

        $removechanges = CompanyItemChange::where('request_id', $req->id)
        ->where('changes_type', $this->settings('ADD', 'key')->id)
        ->get();
        if($removechanges){
            $removechanges = CompanyItemChange::where('request_id', $req->id)
            ->where('changes_type', $this->settings('ADD', 'key')->id)
            ->delete();
        }

        $docIdArray = Documents::where('key','FORM_14')->select('id')->first();
        $document = CompanyDocuments::where('request_id', $req->id)->where('document_id', $docIdArray->id)->first();
        if($document){
            $delete = Storage::disk('sftp')->delete($document->path);
            $remove = CompanyDocuments::where('request_id', $req->id)
            ->where('document_id', $docIdArray->id)
            ->delete();
        }

        $oldaddsremoves = OtherAddress::where('company_id',$comid)
        ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
        ->where(function ($q) {
            $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);
        })->get();

        if($oldaddsremoves){
            foreach($oldaddsremoves as $oldaddsremove){
                $remove = Address::where('id', $oldaddsremove->address_id)->delete();
    
            }
            $oldaddsremoves = OtherAddress::where('company_id',$comid)
            ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
            ->where(function ($q) {
                $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);
            })->delete();
           
        }

        // deleting all previous data if there is any               

        $newadds = $request->input('addArr');

        $newSadds = $request->input('addSArr');

        $newMadds = $request->input('addMArr');
       
       // for record addresses
        foreach($newadds as $newadd){
            if(!empty($newadd)){
                $description = $newadd['discription'];

                $address = new Address;
                $address->address1 = $newadd['localAddress1'];
                $address->address2 = $newadd['localAddress2'];
                $address->province = $newadd['province'];
                $address->district = $newadd['district'];
                $address->city = $newadd['city'];
                $address->gn_division = $newadd['gnDivision'];
                $address->postcode = $newadd['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $otheradd = new OtherAddress;
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $newadd['date'];
                $otheradd->description = $description;
                $otheradd->address_type = $this->settings('RECORD_ADDRESS', 'key')->id;
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

        // for share register addresses
        foreach($newSadds as $newadd){
            if(!empty($newadd)){
                $description = $newadd['discription'];

                $address = new Address;
                $address->address1 = $newadd['localAddress1'];
                $address->address2 = $newadd['localAddress2'];
                $address->province = $newadd['province'];
                $address->district = $newadd['district'];
                $address->city = $newadd['city'];
                $address->gn_division = $newadd['gnDivision'];
                $address->postcode = $newadd['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $otheradd = new OtherAddress;
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $newadd['date'];
                $otheradd->description = $description;
                $otheradd->address_type = $this->settings('SHARE_REGISTER_ADDRESS', 'key')->id;
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

        // for member register addresses
        foreach($newMadds as $newadd){
            if(!empty($newadd)){
                $description = $newadd['discription'];

                $address = new Address;
                $address->address1 = $newadd['localAddress1'];
                $address->address2 = $newadd['localAddress2'];
                $address->province = $newadd['province'];
                $address->district = $newadd['district'];
                $address->city = $newadd['city'];
                $address->gn_division = $newadd['gnDivision'];
                $address->postcode = $newadd['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $otheradd = new OtherAddress;
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $newadd['date'];
                $otheradd->description = $description;
                $otheradd->address_type = $this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id;
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

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->id)
        ->update($update_compnay_updated_at);

        $penalty_value = $this->penaltyCal($comid);
        if(!$penalty_value){
            $case = CourtCase::where('company_court_cases.company_id',$request->id)
            ->where('company_court_cases.request_id',$req->id)
            ->first();
            if($case){
                $case->delete();

            }

        }

        // $newchanges = CompanyItemChange::leftJoin('settings','company_item_changes.changes_type','=','settings.id')
        // ->leftJoin('company_other_addresses','company_item_changes.item_id','=','company_other_addresses.id')
        // ->leftJoin('addresses','company_other_addresses.address_id','=','addresses.id')
        // ->where('company_item_changes.request_id', $req->id)->get(['addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_item_changes.id','company_item_changes.item_id','settings.key as type']);
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'reqID' => $req->id,
            'penalty_value' => $penalty_value,
            'data'   => array(
                'reqid'     => $req->id,

                          
            )
        ], 200);

        }
        elseif($type == 'resubmit') {
            
        $userId = User::where('email', $request->input('email'))->value('id');
        $comid = $request->id;
        $reqid = $request->reqid;
        $signbyid = null;
        $signbytype = null;

        if(!empty($request->input('signby'))){
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

        }

        if( intval($reqid) ){
            $req = CompanyChangeRequestItem::find($reqid);   
        }       
        else{
            $req = new CompanyChangeRequestItem;        
        }

        $req->signed_by = $signbyid;
        $req->signed_by_table_type = $signbytype;
        $req->save();

        
        // deleting all previous data if there is any

        $removechanges = CompanyItemChange::where('request_id', $req->id)
        ->where('changes_type', $this->settings('ADD', 'key')->id)
        ->get();
        if($removechanges){
            $removechanges = CompanyItemChange::where('request_id', $req->id)
            ->where('changes_type', $this->settings('ADD', 'key')->id)
            ->delete();
        }

        // $document = CompanyDocuments::where('request_id', $req->id)->first();
        // if($document){
        //     $delete = Storage::disk('sftp')->delete($document->path);
        //     $remove = CompanyDocuments::where('request_id', $req->id)->delete();
        // }

        $oldaddsremoves = OtherAddress::where('company_id',$comid)
        ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
        ->where(function ($q) {
            $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);
        })->get();

        if($oldaddsremoves){
            foreach($oldaddsremoves as $oldaddsremove){
                $remove = Address::where('id', $oldaddsremove->address_id)->delete();
    
            }
            $oldaddsremoves = OtherAddress::where('company_id',$comid)
            ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
            ->where(function ($q) {
                $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
            ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);
            })->delete();
           
        }

        // deleting all previous data if there is any               

        $newadds = $request->input('addArr');
        

        $newSadds = $request->input('addSArr');
        

        $newMadds = $request->input('addMArr');
        
       
       // for record addresses
        foreach($newadds as $newadd){
            if(!empty($newadd)){
                $description = $newadd['discription'];

                $address = new Address;
                $address->address1 = $newadd['localAddress1'];
                $address->address2 = $newadd['localAddress2'];
                $address->province = $newadd['province'];
                $address->district = $newadd['district'];
                $address->city = $newadd['city'];
                $address->gn_division = $newadd['gnDivision'];
                $address->postcode = $newadd['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $otheradd = new OtherAddress;
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $newadd['date'];
                $otheradd->description = $description;
                $otheradd->address_type = $this->settings('RECORD_ADDRESS', 'key')->id;
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

        // for share register addresses
        foreach($newSadds as $newadd){
            if(!empty($newadd)){
                $description = $newadd['discription'];

                $address = new Address;
                $address->address1 = $newadd['localAddress1'];
                $address->address2 = $newadd['localAddress2'];
                $address->province = $newadd['province'];
                $address->district = $newadd['district'];
                $address->city = $newadd['city'];
                $address->gn_division = $newadd['gnDivision'];
                $address->postcode = $newadd['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $otheradd = new OtherAddress;
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $newadd['date'];
                $otheradd->description = $description;
                $otheradd->address_type = $this->settings('SHARE_REGISTER_ADDRESS', 'key')->id;
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

        // for member register addresses
        foreach($newMadds as $newadd){
            if(!empty($newadd)){
                $description = $newadd['discription'];

                $address = new Address;
                $address->address1 = $newadd['localAddress1'];
                $address->address2 = $newadd['localAddress2'];
                $address->province = $newadd['province'];
                $address->district = $newadd['district'];
                $address->city = $newadd['city'];
                $address->gn_division = $newadd['gnDivision'];
                $address->postcode = $newadd['postcode'];
                $address->country = 'Sri Lanka';
                $address->save(); 
                
                $otheradd = new OtherAddress;
                $otheradd->address_id = $address->id;
                $otheradd->company_id = $comid;
                $otheradd->records_kept_from = $newadd['date'];
                $otheradd->description = $description;
                $otheradd->address_type = $this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id;
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

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->id)
        ->update($update_compnay_updated_at);

        $penalty_value = $this->penaltyCal($comid);

        // $newchanges = CompanyItemChange::leftJoin('settings','company_item_changes.changes_type','=','settings.id')
        // ->leftJoin('company_other_addresses','company_item_changes.item_id','=','company_other_addresses.id')
        // ->leftJoin('addresses','company_other_addresses.address_id','=','addresses.id')
        // ->where('company_item_changes.request_id', $req->id)->get(['addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_item_changes.id','company_item_changes.item_id','settings.key as type']);
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'penalty_value' => $penalty_value,
            'reqID' => $req->id,
            'data'   => array(
                'reqid'     => $req->id,

                          
            )
        ], 200);
        }



    }

    //for view form 14 pdf...
public function generate_pdf(Request $request) {

    if(isset($request->requestID)){
        
        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();
        $comtype = $request->comType;

        $comId = $request->input('comId');

        $company = Company::where('id',$comId)->first();
        $companyaddress = Address::where('id',$company->address_id)->first();

        $company1 = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['companies.id','companies.name','companies.address_id','company_certificate.registration_no as registration_no']);
        
        $regNo =   $company1[0]['registration_no'];

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

        // changed detail array bulding
                
                $editedCMitems = CompanyItemChange::where('request_id',$request->requestID)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                      ->get(); 

                $editedrecs = array();
                foreach ($editedCMitems as $key => $value) {
                    $old = OtherAddress::where('id',$value->old_record_id)
                                ->first();
                    $oldaddress = Address::where('id',$old->address_id)
                                ->first();            
                    $new = OtherAddress::where('id',$value->item_id)
                                ->first();
                    $newaddress = Address::where('id',$new->address_id)
                                ->first();            

                    if($old->address_type == $this->settings('RECORD_ADDRESS','key')->id){
                        $editedrecs[] = [
                            "oldid" => $old->id,
                            "newid" => $new->id,
                            'type' => 0,
                            "description" => $new->description,
                            "old_address" => $oldaddress->address1 . ',' . $oldaddress->address2 . ',' . $oldaddress->city,
                            "new_address" => $newaddress->address1 . ',' . $newaddress->address2 . ',' . $newaddress->city,
                            "date" => $new->records_kept_from,
                      ];

                    }

                }

        // changed detail array bulding        

        $addressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')
                                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                                    ->where('company_other_addresses.company_id',$request->comId)
                                    ->where('company_item_changes.request_id',$request->requestID)
                                    ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.removal_date as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

            $addresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->comId)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);


        $todayDate = date("Y-m-d");

        $day1 = date('d', strtotime($todayDate));
        $month1 = date('m', strtotime($todayDate));
        $year1 = date('Y', strtotime($todayDate));

        $recordaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
                                   ->where('company_other_addresses.company_id',$request->comId)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);

        $shareaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
                                   ->where('company_other_addresses.company_id',$request->comId)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.status as status','company_other_addresses.description as description','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);
        
        $memberaddress = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')                            
                                   ->where('company_other_addresses.company_id',$request->comId)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.description as description','company_other_addresses.status as status','company_other_addresses.records_kept_from as date','company_other_addresses.id as oid']);
        if(count($recordaddress) > 0){
            $noRecActive = false;
        }
        else{
            $noRecActive = true;
        }                                                     
                                   

        if($comtype == false){

            if(count($shareaddress) > 0){
                $noShareActive = false;
            }
            else{
                $noShareActive = true;
            }

            // changed detail array bulding
                
                $editedCMitems = CompanyItemChange::where('request_id',$request->requestID)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                      ->get(); 

                $editedshares = array();
                foreach ($editedCMitems as $key => $value) {
                    $old = OtherAddress::where('id',$value->old_record_id)
                                ->first();
                    $oldaddress = Address::where('id',$old->address_id)
                                ->first();            
                    $new = OtherAddress::where('id',$value->item_id)
                                ->first();
                    $newaddress = Address::where('id',$new->address_id)
                                ->first();            

                    if($old->address_type == $this->settings('SHARE_REGISTER_ADDRESS','key')->id){
                        $editedshares[] = [
                            "oldid" => $old->id,
                            "newid" => $new->id,
                            'type' => 0,
                            "description" => $new->description,
                            "old_address" => $oldaddress->address1 . ',' . $oldaddress->address2 . ',' . $oldaddress->city,
                            "new_address" => $newaddress->address1 . ',' . $newaddress->address2 . ',' . $newaddress->city,
                            "date" => $new->records_kept_from,
                      ];

                    }

                }

        // changed detail array bulding 

            $shareaddressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')
                                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                                    ->where('company_other_addresses.company_id',$request->comId)
                                    ->where('company_item_changes.request_id',$request->requestID)
                                    ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.removal_date as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

            $shareaddresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->comId)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

            $fieldset = array(
                'comName' => $company->name,
                'comAd' => $companyaddress,
                'noRecActive' => $noRecActive,
                'noShareActive' => $noShareActive,
                'comPostfix' => $company->postfix, 
                'comReg' => $company->registration_no,
                'member' => $date,
                'addressactive' => $addressactive, 
                'editedrecs' => $editedrecs, 
                'addresspending' => $addresspending,
                'shareaddressactive' => $shareaddressactive, 
                'shareaddresspending' => $shareaddresspending,
                'editedshares' => $editedshares,
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
            $pdf = PDF::loadView('rraddresschange/form-14',$fieldset);
            $pdf->stream('form-14.pdf');
        }
        elseif($comtype == true){

            if(count($memberaddress) > 0){
                $noMembActive = false;
            }
            else{
                $noMembActive = true;
            }

            // changed detail array bulding
                
                $editedCMitems = CompanyItemChange::where('request_id',$request->requestID)
                                                      ->where('changes_type',$this->settings('EDIT','key')->id)
                                                      ->where('item_table_type',$this->settings('EROC_COMPANY_OTHER_ADDRESSES','key')->id)
                                                      ->get(); 

                $editedmembs = array();
                foreach ($editedCMitems as $key => $value) {
                    $old = OtherAddress::where('id',$value->old_record_id)
                                ->first();
                    $oldaddress = Address::where('id',$old->address_id)
                                ->first();            
                    $new = OtherAddress::where('id',$value->item_id)
                                ->first();
                    $newaddress = Address::where('id',$new->address_id)
                                ->first();            

                    if($old->address_type == $this->settings('MEMBER_REGISTER_ADDRESS','key')->id){
                        $editedmembs[] = [
                            "oldid" => $old->id,
                            "newid" => $new->id,
                            'type' => 0,
                            "description" => $new->description,
                            "old_address" => $oldaddress->address1 . ',' . $oldaddress->address2 . ',' . $oldaddress->city,
                            "new_address" => $newaddress->address1 . ',' . $newaddress->address2 . ',' . $newaddress->city,
                            "date" => $new->records_kept_from,
                      ];

                    }

                }

        // changed detail array bulding

            $memberaddressactive = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
            ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')
                                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                                    ->where('company_other_addresses.company_id',$request->comId)
                                    ->where('company_item_changes.request_id',$request->requestID)
                                    ->where('company_item_changes.changes_type',$this->settings('DELETE','key')->id)
                                   ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                                   ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
                                   ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.removal_date as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);

            $memberaddresspending = Address::leftJoin('company_other_addresses','addresses.id','=','company_other_addresses.address_id')
                    ->leftJoin('company_item_changes','company_item_changes.item_id','=','company_other_addresses.id')                            
                    ->leftJoin('settings','company_item_changes.changes_type','=','settings.id')
                    ->where('company_other_addresses.company_id',$request->comId)
                    ->where('company_item_changes.request_id',$request->requestID)
                    ->where('company_other_addresses.status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
                    ->where('company_other_addresses.address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id)
                    ->get(['addresses.id','addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_other_addresses.description as description','settings.key as type','company_other_addresses.id as oid']);


            $fieldset = array(
                'comName' => $company->name,
                'comAd' => $companyaddress,
                'noRecActive' => $noRecActive,
                'noMembActive' => $noMembActive,
                'comPostfix' => $company->postfix, 
                'comReg' => $company->registration_no,
                'member' => $date,
                'addressactive' => $addressactive, 
                'editedrecs' => $editedrecs,
                'addresspending' => $addresspending, 
                'memberaddresspending' => $memberaddresspending, 
                'memberaddressactive' => $memberaddressactive,
                'editedmembs' => $editedmembs,
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
            $pdf = PDF::loadView('rraddresschange/form-14memb',$fieldset);
            $pdf->stream('form-14.pdf');
        }
        
           
    }
    else{            
        return response()->json([
            'message' => 'We can \'t find a bsd.',
            'status' =>false,
        ], 200);
    }    
    
 }

 function penaltyCal ($comId){

    // $type = $request->type;
    // $court_status = $request->court_status;
    // $caseId = $request->caseId;


    // penalty charges calculation //

        //mindate function
        $pendingaddresses = OtherAddress::where('company_id',$comId)
        ->where('status','=',$this->settings('COMMON_STATUS_PENDING', 'key')->id)
        ->where(function($q) {
            $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);})
        ->get(['company_other_addresses.id','company_other_addresses.records_kept_from']);

        $pendingeditaddresses = OtherAddress::where('company_id',$comId)
        ->where('status','=',$this->settings('COMMON_STATUS_EDIT', 'key')->id)
        ->where(function($q) {
            $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);})
        ->get(['company_other_addresses.id','company_other_addresses.records_kept_from']);

        $pendingdeleteaddresses = OtherAddress::where('company_id',$comId)
        ->where('status','=',$this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
        ->where('removal_date','!=',NULL)
        ->where(function($q) {
            $q->where('address_type','=',$this->settings('RECORD_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('SHARE_REGISTER_ADDRESS', 'key')->id)
              ->orWhere('address_type','=',$this->settings('MEMBER_REGISTER_ADDRESS', 'key')->id);})
        ->get(['company_other_addresses.id','company_other_addresses.removal_date']);

        if(count($pendingaddresses) > 0 || count($pendingeditaddresses) > 0 || count($pendingdeleteaddresses) > 0){

            $dates = array();
            foreach ($pendingaddresses as $key => $value) {
                $dates[] = $value->records_kept_from;
            }
            foreach ($pendingeditaddresses as $key => $value) {
                $dates[] = $value->records_kept_from;
            }
            foreach ($pendingdeleteaddresses as $key => $value) {
                $dates[] = $value->removal_date;
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


           $min_date_gap = $this->settings('RECORDS_REGISTER_ADDRESS_DELAY_PERIOD','key')->value;
           $increment_gap_dates = 30;
           $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_RECORDS_REGISTER_ADDRESS_INITIAL','key')->value );
           $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_RECORDS_REGISTER_ADDRESS_INCREMENT','key')->value );
           $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_RECORDS_REGISTER_ADDRESS_MAX','key')->value );
   
           $increment_gaps = 0;
   
           $penalty_value = 0;
        //    if(count($approvedRequests) > 0){
           if(true){

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

 //for upload rr addresschange pdf...
public function rrUploadPdf(Request $request){

    if(isset($request)){

    $fileName =  uniqid().'.pdf';
    $token = md5(uniqid());

    $comId = $request->comId;
    $docType = $request->docType;
    $pdfName = $request->filename;

    $description = $request->description;
    if($description=='undefined'){
            $description=NULL;
        }

    $path = 'company/'.$comId;
    $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');
    
    $docId;
    if($docType=='applicationUpload'){
        $docIdArray = Documents::where('key','FORM_14')->select('id')->first();
    $docId = $docIdArray->id;
    }
    elseif($docType=='extraUpload'){
        $docIdArray = Documents::where('key','EXTRA_DOCUMENT')->select('id')->first();
    $docId = $docIdArray->id;
    }

    $socDoc = new CompanyDocuments;
    $socDoc->document_id = $docId;
    $socDoc->company_id = $comId;
    $socDoc->name = $pdfName;
    $socDoc->file_token = $token;
    $socDoc->path = $path;
    $socDoc->change_id = null;
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

// for load bsd uploaded files...
public function rrFile(Request $request){
    if(isset($request)){
        $type = $request->type;
        if($type == 'submit'){

            $comId = $request->comId;
            $reqid = $request->requestId;
            //$docIdArray = Documents::where('key','FORM_14')->select('id')->first();

            // $changedetails = ChangeAddress::where('type_id',$comId)
            //                 ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
            //                 ->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id)
            //                 ->first();
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)

        $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                    ->where('company_documents.company_id',$comId)
                                   //->where('company_documents.document_id',$docIdArray->id)
                                   ->where('company_documents.request_id',$reqid)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->get(['company_documents.id','company_documents.name','company_documents.file_description','company_documents.file_token','documents.key as docKey','documents.name as docname']);

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
            $reqid = $request->requestId;
            // $docIdArray = Documents::where('key','FORM_14')->select('id')->first();

            // $changedetails = ChangeAddress::where('type_id',$comId)
            //                 ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
            //                 ->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT','key')->id)
            //                 ->first();
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
                                   ->where('company_documents.request_id',$reqid)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->get(['company_documents.id','company_documents.name','company_documents.file_description','company_documents.file_token','documents.key as docKey','documents.name as docname','company_document_status.company_document_id as company_document_id','company_document_status.comments as comments','settings.value as value','settings.key as setKey']);

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
        
        
        
        

    }else{
        return response()->json([
            'status' =>false,
        ], 200);
    }

}

// to delete pdfs
function deleterrPdf(Request $request){
    if(isset($request)){
    $docId = $request->documentId;
    if($docId){
        $document = CompanyDocuments::where('id', $docId)->first();
        $delete = Storage::disk('sftp')->delete($document->path);
        
        if(!Storage::disk('sftp')->exists($document->path)){
            $remove = CompanyDocuments::where('id', $docId)->delete();
            
           
        }
       
    }
    return response()->json([
        'message' => 'File removed successfully.',
        'status' =>true,
    ], 200);
    }
}

public function rrUpdateUploadPdf(Request $request){

    if(isset($request->docId)){

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

function deleterrPdfUpdate(Request $request){
    if(isset($request)){
    $docId = $request->documentId;
    $type = $request->type;
    $docstatusid = CompanyDocumentStatus::where('company_document_id', $docId)->first();
    if($docstatusid){
        if($type =='applicationUpload'){

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

public function resubmitrr (Request $request){


    if(isset($request->reqid)){

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
    ->update(['status' => $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_RESUBMITTED','key')->id]);
    


    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
    ], 200);

    }
    
}

}
