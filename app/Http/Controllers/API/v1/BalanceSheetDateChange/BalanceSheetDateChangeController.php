<?php

namespace App\Http\Controllers\API\v1\BalanceSheetDateChange;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Address;
use App\Company;
use App\ChangeAddress;
use App\Setting;
use App\User;
use App\People;
use App\Documents;
use App\CompanyStatus;
use App\CompanyDocumentStatus;
use App\CompanyBalanceSheetDate;
use App\CompanyChangeRequestItem;
use App\CompanyItemChange;
use App\CompanyMember;
use App\CompanyFirms;
use App\CompanyCertificate;
use App\CompanyDocuments;
use App\Http\Helper\_helper;
use PDF;
use Storage;
use DateTime;

class BalanceSheetDateChangeController extends Controller
{
    use _helper;
    // load Company bsd data using company id number...
    public function loadCompanybsdData(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Company Id.',
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
            if($type == 'submit'){

            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);

            $bsdchangedetailsRequest = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                                   ->leftJoin('company_item_changes', 'company_item_changes.request_id', '=', 'company_change_requests.id')
                                   ->leftJoin('company_balance_sheet_dates', 'company_item_changes.item_id', '=', 'company_balance_sheet_dates.id')
                                   ->where('company_change_requests.company_id', $request->id)
                                   ->where('company_change_requests.request_type', $this->settings('BALANCE_SHEET_DATE_CHANGE', 'key')->id)
                                   ->where('company_change_requests.status', '=', $this->settings('BALANCE_SHEET_DATE_CHANGE_APPROVED', 'key')->id)
                                   ->orderBy('company_change_requests.created_at', 'DESC')
                                   ->limit(1)
                                   ->get(['company_change_requests.id', 'company_change_requests.request_type', 'settings.value as value', 'settings.key as setKey', 'company_balance_sheet_dates.id as bsdid', 'company_item_changes.id as bsdchangeid']);
            $proposedate = null;
            $valdatestring = null;
            
            if(count($bsdchangedetailsRequest) > 0){
                $needapproval = false;
                $notfirstTime = true;
                $priorApprovalRequest = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('prior_approval', 'prior_approval.request_id', '=', 'company_change_requests.id')
                ->leftJoin('prior_approval_category', 'prior_approval.category_id', '=', 'prior_approval_category.id')
                ->where('prior_approval_category.key','=', 'CHANGE_OF_BALANCE_SHEET_DATE')
                ->where('prior_approval_category.enabled','=', 1)
                ->where('prior_approval.status','=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('PRIOR_APPROVAL','key')->id)
                ->where('company_change_requests.status','=', $this->settings('PRIOR_APPROVAL_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if(count($priorApprovalRequest) > 0){
                    $priorApproval = true;
                    $proposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
                                ->where('request_id',$priorApprovalRequest[0]['id'])
                                ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }
                else{
                    $priorApproval = false;

                }
            }
            else{
                $notfirstTime = false;
                $company3 = Company::where('companies.id',$request->id)
                                   ->first();
                                   
                $incorporation_date = $company3->incorporation_at;
                if($incorporation_date){

                $oldDateUnix = strtotime($incorporation_date);
                $Y = (int)(date("Y", $oldDateUnix));
                $newY = $Y + 1;


                $date = new DateTime();
                $def_date = $date->setDate($Y , 3, 31);
                
                $def_date_string = $def_date->format('Y-m-d');

                if($oldDateUnix < strtotime($def_date_string)){
                    $val_date = $date->setDate($Y , 3, 31);
                }
                else{
                    $val_date = $date->setDate($newY , 3, 31);
                }

                $today = strtotime(date("Y-m-d"));
                $val_date_string = $val_date->format('Y-m-d');

                if($today >= strtotime($val_date_string)){

                    $needapproval = true;

                    $priorApprovalRequest2 = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('prior_approval', 'prior_approval.request_id', '=', 'company_change_requests.id')
                ->leftJoin('prior_approval_category', 'prior_approval.category_id', '=', 'prior_approval_category.id')
                ->where('prior_approval_category.key','=', 'CHANGE_OF_BALANCE_SHEET_DATE')
                ->where('prior_approval_category.enabled','=', 1)
                ->where('prior_approval.status','=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('PRIOR_APPROVAL','key')->id)
                ->where('company_change_requests.status','=', $this->settings('PRIOR_APPROVAL_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);
                    
                if(count($priorApprovalRequest2) > 0){
                    $priorApproval = true;
                    $proposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
                                ->where('request_id',$priorApprovalRequest2[0]['id'])
                                ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }
                else{
                    $priorApproval = false;

                }
                    
                }
                else{
                    $needapproval = false;
                    $valdatestring = $val_date_string;
                    $priorApproval = false;

                }

                }
                else{
                    $needapproval = false;
                    $priorApproval = false;
                }

            }                       

        // $proposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
        // ->where('status','=',$this->settings('COMMON_STATUS_ACTIVE','key')->id)->first();

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

        

        if($company){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'company'     => $company,
                                'members'     => $date,
                                'predate'     => $proposedate,
                                'notfirstTime'     => $notfirstTime,
                                'needapproval'     => $needapproval,
                                'valdatestring'     => $valdatestring,
                                'priorApproval'     => $priorApproval
                                             
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
            ], 200);
        }

        }
        elseif($type == 'processing'){
            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);
            
            // $preproposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
            //                        ->where('status','=',$this->settings('COMMON_STATUS_ACTIVE','key')->id)->first();

            $bsdchangedetailsRequest = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                                   ->leftJoin('company_item_changes', 'company_item_changes.request_id', '=', 'company_change_requests.id')
                                   ->leftJoin('company_balance_sheet_dates', 'company_item_changes.item_id', '=', 'company_balance_sheet_dates.id')
                                   ->where('company_change_requests.company_id', $request->id)
                                   ->where('company_change_requests.request_type', $this->settings('BALANCE_SHEET_DATE_CHANGE', 'key')->id)
                                   ->where('company_change_requests.status', '=', $this->settings('BALANCE_SHEET_DATE_CHANGE_APPROVED', 'key')->id)
                                   ->orderBy('company_change_requests.created_at', 'DESC')
                                   ->limit(1)
                                   ->get(['company_change_requests.id', 'company_change_requests.request_type', 'settings.value as value', 'settings.key as setKey', 'company_balance_sheet_dates.id as bsdid', 'company_item_changes.id as bsdchangeid']);
            
            $proposedate = null;
            
            if(count($bsdchangedetailsRequest) > 0){
                $needapproval = false;
                $notfirstTime = true;
                $priorApprovalRequest = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('prior_approval', 'prior_approval.request_id', '=', 'company_change_requests.id')
                ->leftJoin('prior_approval_category', 'prior_approval.category_id', '=', 'prior_approval_category.id')
                ->where('prior_approval_category.key','=', 'CHANGE_OF_BALANCE_SHEET_DATE')
                ->where('prior_approval_category.enabled','=', 1)
                ->where('prior_approval.status','=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('PRIOR_APPROVAL','key')->id)
                ->where('company_change_requests.status','=', $this->settings('PRIOR_APPROVAL_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if(count($priorApprovalRequest) > 0){
                    $priorApproval = true;
                    $proposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
                                ->where('request_id',$priorApprovalRequest[0]['id'])
                                ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }
                else{
                    $priorApproval = false;

                }
            }
            else{

                $notfirstTime = false;
                $company4 = Company::where('companies.id',$request->id)
                                   ->first();
                                   
                $incorporation_date = $company4->incorporation_at;
                if($incorporation_date){

                $oldDateUnix = strtotime($incorporation_date);
                $Y = (int)(date("Y", $oldDateUnix));
                $newY = $Y + 1;


                $date = new DateTime();
                $def_date = $date->setDate($Y , 3, 31);
                
                $def_date_string = $def_date->format('Y-m-d');

                if($oldDateUnix < strtotime($def_date_string)){
                    $val_date = $date->setDate($Y , 3, 31);
                }
                else{
                    $val_date = $date->setDate($newY , 3, 31);
                }

                $today = strtotime(date("Y-m-d"));
                $val_date_string = $val_date->format('Y-m-d');

                if($today >= strtotime($val_date_string)){

                    $needapproval = true;

                    $priorApprovalRequest2 = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('prior_approval', 'prior_approval.request_id', '=', 'company_change_requests.id')
                ->leftJoin('prior_approval_category', 'prior_approval.category_id', '=', 'prior_approval_category.id')
                ->where('prior_approval_category.key','=', 'CHANGE_OF_BALANCE_SHEET_DATE')
                ->where('prior_approval_category.enabled','=', 1)
                ->where('prior_approval.status','=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('PRIOR_APPROVAL','key')->id)
                ->where('company_change_requests.status','=', $this->settings('PRIOR_APPROVAL_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);
                    
                if(count($priorApprovalRequest2) > 0){
                    $priorApproval = true;
                    $proposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
                                ->where('request_id',$priorApprovalRequest2[0]['id'])
                                ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }
                else{
                    $priorApproval = false;

                }
                    
                }
                else{
                    $needapproval = false;
                    $priorApproval = false;
                    $proposedate = CompanyBalanceSheetDate::where('id',$request->bsdid)
                                ->where('company_id',$request->id)
                                   ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }

                }
                else{
                    $needapproval = false;
                    $priorApproval = false;
                    $proposedate = CompanyBalanceSheetDate::where('id',$request->bsdid)
                                ->where('company_id',$request->id)
                                   ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();
                }


                // $notfirstTime = false;
                // $priorApproval = false;
                // $proposedate = CompanyBalanceSheetDate::where('id',$request->bsdid)
                //                 ->where('company_id',$request->id)
                //                    ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

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

       
       $changeRequest = CompanyChangeRequestItem::leftJoin('settings','company_change_requests.signed_by_table_type','=','settings.id')
       ->where('company_change_requests.id',$request->requestID)
       ->get(['company_change_requests.signed_by','settings.key as tableType']);
       
       $signedby = $changeRequest[0]['signed_by'];
       $signedbytype = $changeRequest[0]['tableType']; 

    //    $changerequest = CompanyChangeRequestItem::where('id',$request->requestID)->select('signed_by')->first();
    //    $signedby = $changerequest->signed_by;
        

        if($proposedate){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'company'     => $company,
                                'members'     => $date,
                                'signedby' => $signedby,
                                'signedbytype' => $signedbytype,
                                'predate'     => $proposedate,
                                'notfirstTime'     => $notfirstTime,
                                'needapproval'     => $needapproval,
                                'priorApproval'     => $priorApproval
                                          
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'Prior approval scene while processing',
                'status' =>false,
            ], 200);
        }
        }
        elseif($type == 'resubmit'){
            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);
            
            // $preproposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
            //                        ->where('status','=',$this->settings('COMMON_STATUS_ACTIVE','key')->id)->first();

            // $postproposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
            //                        ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();
            $bsdchangedetailsRequest = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                                   ->leftJoin('company_item_changes', 'company_item_changes.request_id', '=', 'company_change_requests.id')
                                   ->leftJoin('company_balance_sheet_dates', 'company_item_changes.item_id', '=', 'company_balance_sheet_dates.id')
                                   ->where('company_change_requests.company_id', $request->id)
                                   ->where('company_change_requests.request_type', $this->settings('BALANCE_SHEET_DATE_CHANGE', 'key')->id)
                                   ->where('company_change_requests.status', '=', $this->settings('BALANCE_SHEET_DATE_CHANGE_APPROVED', 'key')->id)
                                   ->orderBy('company_change_requests.created_at', 'DESC')
                                   ->limit(1)
                                   ->get(['company_change_requests.id', 'company_change_requests.request_type', 'settings.value as value', 'settings.key as setKey', 'company_balance_sheet_dates.id as bsdid', 'company_item_changes.id as bsdchangeid']);
                                   
            $proposedate = null;
            
            if(count($bsdchangedetailsRequest) > 0){
                $needapproval = false;
                $notfirstTime = true;
                $priorApprovalRequest = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('prior_approval', 'prior_approval.request_id', '=', 'company_change_requests.id')
                ->leftJoin('prior_approval_category', 'prior_approval.category_id', '=', 'prior_approval_category.id')
                ->where('prior_approval_category.key','=', 'CHANGE_OF_BALANCE_SHEET_DATE')
                ->where('prior_approval_category.enabled','=', 1)
                ->where('prior_approval.status','=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('PRIOR_APPROVAL','key')->id)
                ->where('company_change_requests.status','=', $this->settings('PRIOR_APPROVAL_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);

                if(count($priorApprovalRequest) > 0){
                    $priorApproval = true;
                    $proposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
                                ->where('id',$request->bsdid)
                                ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }
                else{
                    $priorApproval = false;

                }
            }
            else{


                $notfirstTime = false;
                $company5 = Company::where('companies.id',$request->id)
                                   ->first();
                                   
                $incorporation_date = $company5->incorporation_at;
                if($incorporation_date){

                $oldDateUnix = strtotime($incorporation_date);
                $Y = (int)(date("Y", $oldDateUnix));
                $newY = $Y + 1;


                $date = new DateTime();
                $def_date = $date->setDate($Y , 3, 31);
                
                $def_date_string = $def_date->format('Y-m-d');

                if($oldDateUnix < strtotime($def_date_string)){
                    $val_date = $date->setDate($Y , 3, 31);
                }
                else{
                    $val_date = $date->setDate($newY , 3, 31);
                }

                $today = strtotime(date("Y-m-d"));
                $val_date_string = $val_date->format('Y-m-d');

                if($today >= strtotime($val_date_string)){

                    $needapproval = true;

                    $priorApprovalRequest2 = CompanyChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                ->leftJoin('prior_approval', 'prior_approval.request_id', '=', 'company_change_requests.id')
                ->leftJoin('prior_approval_category', 'prior_approval.category_id', '=', 'prior_approval_category.id')
                ->where('prior_approval_category.key','=', 'CHANGE_OF_BALANCE_SHEET_DATE')
                ->where('prior_approval_category.enabled','=', 1)
                ->where('prior_approval.status','=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('PRIOR_APPROVAL','key')->id)
                ->where('company_change_requests.status','=', $this->settings('PRIOR_APPROVAL_APPROVED','key')->id)
                ->orderBy('company_change_requests.created_at','DESC')
                ->limit(1)
                ->get(['company_change_requests.id','company_change_requests.request_type','settings.value as value','settings.key as setKey']);
                    
                if(count($priorApprovalRequest2) > 0){
                    $priorApproval = true;
                    $proposedate = CompanyBalanceSheetDate::where('company_id',$request->id)
                                ->where('request_id',$priorApprovalRequest2[0]['id'])
                                ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }
                else{
                    $priorApproval = false;

                }
                    
                }
                else{
                    $needapproval = false;
                    $priorApproval = false;
                    $proposedate = CompanyBalanceSheetDate::where('id',$request->bsdid)
                                ->where('company_id',$request->id)
                                   ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

                }

                }
                else{
                    $needapproval = false;
                    $priorApproval = false;
                    $proposedate = CompanyBalanceSheetDate::where('id',$request->bsdid)
                                ->where('company_id',$request->id)
                                   ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();
                }

                // $notfirstTime = false;
                // $priorApproval = false;
                // $proposedate = CompanyBalanceSheetDate::where('id',$request->bsdid)
                //                 ->where('company_id',$request->id)
                //                    ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();

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

    //    $changerequest = CompanyChangeRequestItem::where('id',$request->requestID)->select('signed_by')->first();
    //     $signedby = $changerequest->signed_by;

       $external_comment_query = CompanyStatus::where('company_id',$request->id)
                                                    ->where('comment_type','=', $this->settings('COMMENT_EXTERNAL', 'key')->id )
                                                    ->where('request_id',$request->requestID)
                                                    ->where('status','=', $this->settings('BALANCE_SHEET_DATE_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id )
                                                    ->orderBy('id', 'desc')
                                                    ->limit(1)
                                                    ->first();
           $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                      ?  $external_comment_query->comments
                                      : '';
        

        if($proposedate){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                    'company'     => $company,
                                'members'     => $date,
                                'signedby' => $signedby,
                                'signedbytype' => $signedbytype,
                                'predate'     => $proposedate,
                                'notfirstTime'     => $notfirstTime,
                                'needapproval'     => $needapproval,
                                'priorApproval'     => $priorApproval,
                                'external_global_comment' => $external_global_comment,
                                          
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'Prior approval scene while processing',
                'status' =>false,
            ], 200);
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

    public function saveData (Request $request){

        $userId = User::where('email', $request->input('email'))->value('id');
        $comid = $request->id;
        $reqid = $request->reqid;
        $bsdid = $request->bsdid;
        $bsdchangeid = $request->bsdchangeid;
        $notfirstTime = $request->notfirstTime;
        $needapproval = $request->needapproval;

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
        $req->request_type = $this->settings('BALANCE_SHEET_DATE_CHANGE', 'key')->id;
        $req->status = $this->settings('BALANCE_SHEET_DATE_CHANGE_PROCESSING', 'key')->id;
        $req->save();


        if( intval($bsdid) ){
            $bsd = CompanyBalanceSheetDate::find($bsdid);   
        }       
        else{
            $bsd = new CompanyBalanceSheetDate;        
        }
        
        if($notfirstTime){
            $proposedate = $request->input('proDate');
            $previousdate = $request->input('preDate');

        }
        else{
            if($needapproval){
                $proposedate = $request->input('proDate');
                $previousdate = $request->input('preDate');
    
            }
            else{
                $proposedate = $request->input('preDate');
                $previousdate = null;

            }

        }
                $bsd->company_id = $comid;
                $bsd->proposed_date = $proposedate;
                $bsd->previous_date = $previousdate;
                $bsd->effected_year = $request->input('effectiveYear');
                $bsd->status = $this->settings('COMMON_STATUS_PENDING', 'key')->id;
                $bsd->save();

                if( intval($bsdchangeid) ){
                    $bsdchange = CompanyItemChange::find($bsdchangeid);   
                }       
                else{
                    $bsdchange = new CompanyItemChange;        
                }

                $bsdchange->request_id = $req->id;
                $bsdchange->changes_type = $this->settings('ADD', 'key')->id;
                $bsdchange->item_table_type = $this->settings('EROC_COMPANY_BALANCE_SHEET_DATES', 'key')->id;
                $bsdchange->item_id = $bsd->id;
                $bsdchange->save();
         
                $update_compnay_updated_at = array(
                    'updated_at' => date('Y-m-d H:i:s', time())
                );
                Company::where('id', $request->id)
                ->update($update_compnay_updated_at);        


        // $newchanges = CompanyItemChange::leftJoin('settings','company_item_changes.changes_type','=','settings.id')
        // ->leftJoin('company_other_addresses','company_item_changes.item_id','=','company_other_addresses.id')
        // ->leftJoin('addresses','company_other_addresses.address_id','=','addresses.id')
        // ->where('company_item_changes.request_id', $req->id)->get(['addresses.city','addresses.district','addresses.province','addresses.postcode','addresses.gn_division as gnDivision','addresses.address1','addresses.address2','company_other_addresses.records_kept_from as date','company_item_changes.id','company_item_changes.item_id','settings.key as type']);
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'reqID' => $req->id,
            'data'   => array(
                'reqid'     => $req->id,
                'bsdid'     => $bsd->id,
                'bsdchangeid'     => $bsdchange->id,

                          
            )
        ], 200);



    }

    //for view form 17 pdf...
public function generate_pdf(Request $request) {

    if(isset($request->bsdid) && isset($request->requestID)){
        
        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();

        $comId = $request->input('comId');

        $company = Company::where('id',$comId)->first();

        $company1 = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['companies.id','companies.name','companies.address_id','company_certificate.registration_no as registration_no']);
        
        $regNo =   $company1[0]['registration_no'];
        $bsdid = $request->bsdid;
        $notfirstTime = $request->notfirstTime;
        $needapproval = $request->needapproval;

        $bsd = CompanyBalanceSheetDate::where('id',$bsdid)->first();

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

    //    $member = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
    //     ->where('company_members.id',$changeRequest->signed_by)
    //    ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

       $effectedyear = strval($bsd->effected_year);

        $o_date = $bsd->proposed_date;

        $day = date('d', strtotime($o_date));
        $month = date('m', strtotime($o_date));

        $todayDate = date("Y-m-d");

        $day1 = date('d', strtotime($todayDate));
        $month1 = date('m', strtotime($todayDate));
        $year1 = date('Y', strtotime($todayDate));

        $pre_date = $bsd->previous_date;
        if($notfirstTime){
            $preday = date('d', strtotime($pre_date));
            $premonth = date('m', strtotime($pre_date));

            $fieldset = array(
                'comName' => $company->name,
                'comPostfix' => $company->postfix, 
                'comReg' => $company->registration_no,
                'member' => $date,
                'day' => $day, 
                'month' => $month,
                'preday' => $preday, 
                'premonth' => $premonth, 
                'effectedyear' => $effectedyear,
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
        }
        else{
            if($needapproval){
                $preday = date('d', strtotime($pre_date));
                $premonth = date('m', strtotime($pre_date));
    
                $fieldset = array(
                    'comName' => $company->name,
                    'comPostfix' => $company->postfix, 
                    'comReg' => $company->registration_no,
                    'member' => $date,
                    'day' => $day, 
                    'month' => $month,
                    'preday' => $preday, 
                    'premonth' => $premonth, 
                    'effectedyear' => $effectedyear,
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
            }
            else{
            $preday = date('d', strtotime($o_date));
            $premonth = date('m', strtotime($o_date));

            $fieldset = array(
                'comName' => $company->name,
                'comPostfix' => $company->postfix, 
                'comReg' => $company->registration_no,
                'member' => $date, 
                'effectedyear' => $effectedyear,
                'preday' => $preday, 
                'premonth' => $premonth,
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

            }
        }
                       
        
        

         $pdf = PDF::loadView('bsdchange/Form17',$fieldset);
            $pdf->stream('form-17.pdf');
        
           
    }
    else{            
        return response()->json([
            'message' => 'We can \'t find a bsd.',
            'status' =>false,
        ], 200);
    }    
    
 }

 //for upload accounting addresschange pdf...
public function bsdUploadPdf(Request $request){

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
        $docIdArray = Documents::where('key','FORM_17')->select('id')->first();
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
        'file_description' =>$description,
        'docArray' => $docId
        ], 200);

    }

}

function deletebsdPdfUpdate(Request $request){
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

public function resubmitbsd (Request $request){


    if(isset($request->reqid)){
        CompanyChangeRequestItem::where('id', $request->reqid)
    ->update(['status' => $this->settings('BALANCE_SHEET_DATE_CHANGE_RESUBMITTED','key')->id]);
    


    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
    ], 200);

    }
    
}

// for load bsd uploaded files...
public function bsdFile(Request $request){
    if(isset($request)){
        $type = $request->type;
        if($type == 'submit'){

            $comId = $request->comId;
            $reqid = $request->requestId;
            $bsdchangeid = $request->bsdchangeid;
            $bsdid = $request->bsdid;
            $notfirstTime = $request->notfirstTime;
            $needapproval = $request->needapproval;
            //$docIdArray = Documents::where('key','FORM_17')->select('id')->first();

            // $changedetails = ChangeAddress::where('type_id',$comId)
            //                 ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
            //                 ->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id)
            //                 ->first();
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
        $approvalLet = null;
        if($notfirstTime){

            $proposedate = CompanyBalanceSheetDate::where('id',$bsdid)
                                ->where('company_id',$comId)
                                   ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();
            $approvalReqId = $proposedate->request_id;
            if($approvalReqId){
                $approvalLet = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                    ->where('company_documents.company_id',$comId)
                                   //->where('company_documents.document_id',$docIdArray->id)
                                   //->where('company_documents.change_id',$bsdchangeid)
                                   ->where('company_documents.request_id',$approvalReqId)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->where('documents.key','BALANCE_SHEET_DATE_PRIOR_APPROVAL_LETTER')
                                   ->get(['company_documents.id','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname']);
            }

        }
        else{
            if($needapproval){

                $proposedate = CompanyBalanceSheetDate::where('id',$bsdid)
                                    ->where('company_id',$comId)
                                       ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();
                $approvalReqId = $proposedate->request_id;
                if($approvalReqId){
                    $approvalLet = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                        ->where('company_documents.company_id',$comId)
                                       //->where('company_documents.document_id',$docIdArray->id)
                                       //->where('company_documents.change_id',$bsdchangeid)
                                       ->where('company_documents.request_id',$approvalReqId)
                                       ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                       ->where('documents.key','BALANCE_SHEET_DATE_PRIOR_APPROVAL_LETTER')
                                       ->get(['company_documents.id','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname']);
                }
    
            }

        }

        $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                    ->where('company_documents.company_id',$comId)
                                   //->where('company_documents.document_id',$docIdArray->id)
                                   //->where('company_documents.change_id',$bsdchangeid)
                                   ->where('company_documents.request_id',$reqid)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->get(['company_documents.id','company_documents.file_description','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname']);

        if(isset($uploadedPdf)){
            return response()->json([
                'file' => $uploadedPdf,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdf,
                    'approvalLet'     => $approvalLet,
                )
            ], 200);

            }

        }
        elseif($type == 'resubmit'){

            $comId = $request->comId;
            $reqid = $request->requestId;
            $bsdchangeid = $request->bsdchangeid;
            $bsdid = $request->bsdid;
            $notfirstTime = $request->notfirstTime;
            $needapproval = $request->needapproval;
            // $docIdArray = Documents::where('key','FORM_17')->select('id')->first();

            // $changedetails = ChangeAddress::where('type_id',$comId)
            //                 ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
            //                 ->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT','key')->id)
            //                 ->first();
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)

        $approvalLet = null;
        if($notfirstTime){

            $proposedate = CompanyBalanceSheetDate::where('id',$bsdid)
                                ->where('company_id',$comId)
                                   ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();
            $approvalReqId = $proposedate->request_id;
            if($approvalReqId){
                $approvalLet = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                    ->where('company_documents.company_id',$comId)
                                   //->where('company_documents.document_id',$docIdArray->id)
                                   //->where('company_documents.change_id',$bsdchangeid)
                                   ->where('company_documents.request_id',$approvalReqId)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->where('documents.key','BALANCE_SHEET_DATE_PRIOR_APPROVAL_LETTER')
                                   ->get(['company_documents.id','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname']);
            }

        }
        else{
            if($needapproval){

                $proposedate = CompanyBalanceSheetDate::where('id',$bsdid)
                                    ->where('company_id',$comId)
                                       ->where('status','=',$this->settings('COMMON_STATUS_PENDING','key')->id)->first();
                $approvalReqId = $proposedate->request_id;
                if($approvalReqId){
                    $approvalLet = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                        ->where('company_documents.company_id',$comId)
                                       //->where('company_documents.document_id',$docIdArray->id)
                                       //->where('company_documents.change_id',$bsdchangeid)
                                       ->where('company_documents.request_id',$approvalReqId)
                                       ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                       ->where('documents.key','BALANCE_SHEET_DATE_PRIOR_APPROVAL_LETTER')
                                       ->get(['company_documents.id','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname']);
                }
    
            }

        }

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
                                   //->where('company_documents.change_id',$bsdchangeid)
                                   ->where('company_documents.request_id',$reqid)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->get(['company_documents.id','company_documents.file_description','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname','company_document_status.company_document_id as company_document_id','company_document_status.comments as comments','settings.value as value','settings.key as setKey']);

        if(isset($uploadedPdf)){
            return response()->json([
                'file' => $uploadedPdf,
                'status' =>true,
                'data'   => array(
                    'file'     => $uploadedPdf,
                    'approvalLet'     => $approvalLet,
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
function deletebsdPdf(Request $request){
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

// update bsd data using company requestid id number...resubmitBsd
public function saveDataResubmit (Request $request){

    if(isset($request->reqid)){
        $notfirstTime = $request->notfirstTime;
        $needapproval = $request->needapproval;

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

        CompanyChangeRequestItem::where('id', $request->reqid)
    ->update(['signed_by' => $signbyid,'signed_by_table_type' => $signbytype]);

    if($notfirstTime){
        $proposedate = $request->input('proDate');
        $previousdate = $request->input('preDate');

    }
    else{
        if($needapproval){
            $proposedate = $request->input('proDate');
            $previousdate = $request->input('preDate');
    
        }
        else{
            $proposedate = $request->input('preDate');
            $previousdate = null;

        }
        
    }

    if(isset($request->bsdid)){
        CompanyBalanceSheetDate::where('id', $request->bsdid)
    ->update(['proposed_date' => $proposedate,
    'previous_date' => $previousdate,
    'effected_year' => $request->input('effectiveYear')]);
    }

    $update_compnay_updated_at = array(
        'updated_at' => date('Y-m-d H:i:s', time())
    );
    Company::where('id', $request->id)
    ->update($update_compnay_updated_at);

    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
    ], 200);

    }

    return response()->json([
        'message' => 'No request id detetcted!!!',
        'status' =>false,
    ], 200);



}

public function bsdUpdateUploadPdf(Request $request){

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


}
