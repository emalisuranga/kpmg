<?php

namespace App\Http\Controllers\API\v1\SatisCharge;

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
use App\SatisfactionCharge;
use App\SatisDeed;
use App\Charges;
use App\DeclaredMembers;
use App\CompanyDocuments;
use App\Http\Helper\_helper;
use PDF;
use Storage;

class SatisChargeController extends Controller
{
    use _helper;

    public function loadCompanyScData(Request $request){

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

        $satischangedetails = CompanyChangeRequestItem::where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('SATISFACTION_CHARGE_CHANGE','key')->id)
                ->where('company_change_requests.status','=', $this->settings('COMPANY_CHANGE_PROCESSING','key')->id)
                ->first();

        if(!$satischangedetails){

        $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.address_id','company_certificate.registration_no as registration_no']);

        $charges = Charges::leftJoin('registration_of_charge_certificates','charges.request_id','=','registration_of_charge_certificates.request_id')
        ->leftJoin('charges_deed_items','charges.request_id','=','charges_deed_items.request_id')
        ->where('charges_deed_items.status',1)
        ->where('charges.company_id',$request->id)
        ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
        ->get(['charges.id as id','charges.short_perticular_description as short_perticular_description','charges.charge_date as charge_date','charges.date_of as date_of','charges.request_id as request_id','charges.amount_secured as amount_secured','registration_of_charge_certificates.issued_at as issued_at']);

        $chargesWithDeeds = Charges::leftJoin('charges_deed_items','charges.request_id','=','charges_deed_items.request_id')
                ->where('charges.company_id',$request->id)
                ->where('charges_deed_items.status',1)
                ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
                ->get(['charges_deed_items.id as id','charges.charge_type as charge_type','charges_deed_items.deed_date as deed_date','charges_deed_items.deed_no as deed_no','charges_deed_items.request_id as request_id','charges_deed_items.amount_secured as amount_secured','charges_deed_items.lawyers as lawyers','charges.id as charge_id']);
                
        $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->id)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

        $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->id)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_member_firms.id','company_member_firms.name','settings.value as designation']);

        $date = array();
            foreach ($members as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    'type' => 0,
                    'show' => true,
                    'disable' => false,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
                    "comdesignation" => $value->designation,
              ];
            }

            foreach ($memberfirms as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    'type' => 1,
                    'show' => true,
                    'disable' => false,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
                    "comdesignation" => $value->designation,
              ];
            }


        

        if($company){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'company'     => $company,
                                'members'     => $date,
                                'charges'     => $charges,
                                'chargesWithDeeds'     => $chargesWithDeeds,
                                             
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
            ], 200);
        }

    }
    else{

        $scchange = CompanyItemChange::where('request_id', $satischangedetails->id)->first();

        $sc = SatisfactionCharge::where('id', $scchange->item_id)->first();

        $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                ->where('companies.id',$request->id)
                ->get(['companies.id','companies.name','companies.postfix','companies.address_id','company_certificate.registration_no as registration_no']);
        
        $charges = Charges::leftJoin('registration_of_charge_certificates','charges.request_id','=','registration_of_charge_certificates.request_id')
        ->leftJoin('charges_deed_items','charges.request_id','=','charges_deed_items.request_id')
        ->where('charges_deed_items.status',1)
        ->where('charges.company_id',$request->id)
        ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
        ->get(['charges.id as id','charges.short_perticular_description as short_perticular_description','charges.charge_date as charge_date','charges.date_of as date_of','charges.request_id as request_id','charges.amount_secured as amount_secured','registration_of_charge_certificates.issued_at as issued_at']);

        $chargesWithDeeds = Charges::leftJoin('charges_deed_items','charges.request_id','=','charges_deed_items.request_id')
                ->where('charges.company_id',$request->id)
                ->where('charges_deed_items.status',1)
                ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
                ->get(['charges_deed_items.id as id','charges.charge_type as charge_type','charges_deed_items.deed_date as deed_date','charges_deed_items.deed_no as deed_no','charges_deed_items.request_id as request_id','charges_deed_items.amount_secured as amount_secured','charges_deed_items.lawyers as lawyers','charges.id as charge_id']);
        // selected members array creating // company_change_declare_members

        $membersSelected = DeclaredMembers::leftJoin('company_members','company_members.id','=','company_change_declare_members.member_id')
                ->where('company_change_declare_members.request_id',$satischangedetails->id)
                ->leftJoin('settings','company_members.designation_type','=','settings.id')
                ->where('company_members.company_id',$request->id)
                ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
                ->get(['company_members.id as id','company_members.first_name as first_name','company_members.last_name as last_name','settings.value as designation']);
        
        $memberfirmsSelected = DeclaredMembers::leftJoin('company_member_firms','company_member_firms.id','=','company_change_declare_members.member_firm_id')
                ->where('company_change_declare_members.request_id',$satischangedetails->id)
                ->leftJoin('settings','company_member_firms.type_id','=','settings.id')
                ->where('company_member_firms.company_id',$request->id)
                ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
                ->get(['company_member_firms.id as id','company_member_firms.name as name','settings.value as designation']);
        
        $dateselected = array();
            foreach ($membersSelected as $key => $value) {
                      $dateselected[] = [
                            "id" => $value->id,
                            'type' => 0,
                            'show' => false,
                            'disable' => false,
                            "title" => $value->title,
                            "first_name" => $value->first_name,
                            "last_name" => $value->last_name,
                            "designation" => $value->designation,
                            "comdesignation" => $value->designation,
                      ];
                    }
        
            foreach ($memberfirmsSelected as $key => $value) {
                      $dateselected[] = [
                            "id" => $value->id,
                            'type' => 1,
                            'show' => false,
                            'disable' => false,
                            "title" => '',
                            "first_name" => $value->name,
                            "last_name" => '',
                            "designation" => 'Firm',
                            "comdesignation" => $value->designation,
                      ];
                    }
        // selected members array creating //

        $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->id)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

        $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->id)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_member_firms.id','company_member_firms.name','settings.value as designation']);

        $date = array();
            foreach ($members as $key => $value) {
                foreach ($membersSelected as $key => $Selectedvalue) {
                    if($value->id == $Selectedvalue->id){
                        $date[] = [
                            "id" => $value->id,
                            'type' => 0,
                            'show' => false,
                            'disable' => false,
                            "title" => $value->title,
                            "first_name" => $value->first_name,
                            "last_name" => $value->last_name,
                            "designation" => $value->designation,
                            "comdesignation" => $value->designation,
                      ];
                      goto end;

                    }

                }
                $date[] = [
                    "id" => $value->id,
                    'type' => 0,
                    'show' => true,
                    'disable' => false,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
                    "comdesignation" => $value->designation,
              ];
              end:
              continue;
            }

            foreach ($memberfirms as $key => $value) {
                foreach ($memberfirmsSelected as $key => $Selectedvalue) {
                    if($value->id == $Selectedvalue->id){
                        $date[] = [
                            "id" => $value->id,
                            'type' => 1,
                            'show' => false,
                            'disable' => false,
                            "title" => '',
                            "first_name" => $value->name,
                            "last_name" => '',
                            "designation" => 'Firm',
                            "comdesignation" => $value->designation,
                      ];
                      goto end1;

                    }
                }
                $date[] = [
                    "id" => $value->id,
                    'type' => 1,
                    'show' => true,
                    'disable' => false,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
                    "comdesignation" => $value->designation,
              ];
              end1:
              continue;
            }

            if($company && $scchange){            
                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' =>true, // to load data from eroc company...
                    'dataPro'   => array(
                                    'company'     => $company,
                                    'scchange'     => $scchange,
                                    'sc'     => $sc,
                                    'members'     => $date,
                                    'selectedMembers'     => $dateselected,
                                    'charges'     => $charges,
                                    'chargesWithDeeds'     => $chargesWithDeeds,
                                                 
                                )
                ], 200);            
            }else{
                return response()->json([
                    'message' => 'We can \'t find a company.',
                    'status' =>false,
                ], 200);
            }

                }

        }
        elseif($type == 'resubmit'){

        $satischangedetails = CompanyChangeRequestItem::where('company_change_requests.company_id',$request->id)
                ->where('company_change_requests.request_type', $this->settings('SATISFACTION_CHARGE_CHANGE','key')->id)
                ->where('company_change_requests.status','=', $this->settings('COMPANY_CHANGE_REQUEST_TO_RESUBMIT','key')->id)
                ->first();

        $scchange = CompanyItemChange::where('request_id', $satischangedetails->id)->first();

        $sc = SatisfactionCharge::where('id', $scchange->item_id)->first();

        $charges = Charges::leftJoin('registration_of_charge_certificates','charges.request_id','=','registration_of_charge_certificates.request_id')
        ->leftJoin('charges_deed_items','charges.request_id','=','charges_deed_items.request_id')
        ->where('charges_deed_items.status',1)
        ->where('charges.company_id',$request->id)
        ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
        ->get(['charges.id as id','charges.short_perticular_description as short_perticular_description','charges.charge_date as charge_date','charges.date_of as date_of','charges.request_id as request_id','charges.amount_secured as amount_secured','registration_of_charge_certificates.issued_at as issued_at']);


        $chargesWithDeeds = Charges::leftJoin('charges_deed_items','charges.request_id','=','charges_deed_items.request_id')
                ->where('charges.company_id',$request->id)
                ->where('charges_deed_items.status',1)
                ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
                ->get(['charges_deed_items.id as id','charges.charge_type as charge_type','charges_deed_items.deed_date as deed_date','charges_deed_items.deed_no as deed_no','charges_deed_items.request_id as request_id','charges_deed_items.amount_secured as amount_secured','charges_deed_items.lawyers as lawyers','charges.id as charge_id']);

        $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                ->where('companies.id',$request->id)
                ->get(['companies.id','companies.name','companies.postfix','companies.address_id','company_certificate.registration_no as registration_no']);
        
        // selected members array creating // company_change_declare_members

        $membersSelected = DeclaredMembers::leftJoin('company_members','company_members.id','=','company_change_declare_members.member_id')
                ->where('company_change_declare_members.request_id',$satischangedetails->id)
                ->leftJoin('settings','company_members.designation_type','=','settings.id')
                ->where('company_members.company_id',$request->id)
                ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
                ->get(['company_members.id as id','company_members.first_name as first_name','company_members.last_name as last_name','settings.value as designation']);
        
        $memberfirmsSelected = DeclaredMembers::leftJoin('company_member_firms','company_member_firms.id','=','company_change_declare_members.member_firm_id')
                ->where('company_change_declare_members.request_id',$satischangedetails->id)
                ->leftJoin('settings','company_member_firms.type_id','=','settings.id')
                ->where('company_member_firms.company_id',$request->id)
                ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
                ->get(['company_member_firms.id as id','company_member_firms.name as name','settings.value as designation']);
        
        $dateselected = array();
            foreach ($membersSelected as $key => $value) {
                      $dateselected[] = [
                            "id" => $value->id,
                            'type' => 0,
                            'show' => false,
                            'disable' => false,
                            "title" => $value->title,
                            "first_name" => $value->first_name,
                            "last_name" => $value->last_name,
                            "designation" => $value->designation,
                            "comdesignation" => $value->designation,
                      ];
                    }
        
            foreach ($memberfirmsSelected as $key => $value) {
                      $dateselected[] = [
                            "id" => $value->id,
                            'type' => 1,
                            'show' => false,
                            'disable' => false,
                            "title" => '',
                            "first_name" => $value->name,
                            "last_name" => '',
                            "designation" => 'Firm',
                            "comdesignation" => $value->designation,
                      ];
                    }
        // selected members array creating //

        $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->id)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

        $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->id)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_member_firms.id','company_member_firms.name','settings.value as designation']);

        $date = array();
            foreach ($members as $key => $value) {
                foreach ($membersSelected as $key => $Selectedvalue) {
                    if($value->id == $Selectedvalue->id){
                        $date[] = [
                            "id" => $value->id,
                            'type' => 0,
                            'show' => false,
                            'disable' => false,
                            "title" => $value->title,
                            "first_name" => $value->first_name,
                            "last_name" => $value->last_name,
                            "designation" => $value->designation,
                            "comdesignation" => $value->designation,
                      ];
                      goto end2;

                    }

                }
                $date[] = [
                    "id" => $value->id,
                    'type' => 0,
                    'show' => true,
                    'disable' => false,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
                    "comdesignation" => $value->designation,
              ];
              end2:
              continue;
            }

            foreach ($memberfirms as $key => $value) {
                foreach ($memberfirmsSelected as $key => $Selectedvalue) {
                    if($value->id == $Selectedvalue->id){
                        $date[] = [
                            "id" => $value->id,
                            'type' => 1,
                            'show' => false,
                            'disable' => false,
                            "title" => '',
                            "first_name" => $value->name,
                            "last_name" => '',
                            "designation" => 'Firm',
                            "comdesignation" => $value->designation,
                      ];
                      goto end3;

                    }
                }
                $date[] = [
                    "id" => $value->id,
                    'type' => 1,
                    'show' => true,
                    'disable' => false,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
                    "comdesignation" => $value->designation,
              ];
              end3:
              continue;
            }

            $external_comment_query = CompanyStatus::where('company_id',$request->id)
                                                    ->where('comment_type','=', $this->settings('COMMENT_EXTERNAL', 'key')->id )
                                                    ->where('request_id',$satischangedetails->id)
                                                    ->where('status','=', $this->settings('COMPANY_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id )
                                                    ->orderBy('id', 'desc')
                                                    ->limit(1)
                                                    ->first();
           $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                      ?  $external_comment_query->comments
                                      : '';

            if($company && $scchange){            
                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' =>true, // to load data from eroc company...
                    'dataPro'   => array(
                                    'company'     => $company,
                                    'scchange'     => $scchange,
                                    'sc'     => $sc,
                                    'members'     => $date,
                                    'selectedMembers'     => $dateselected,
                                    'external_global_comment' => $external_global_comment,
                                    'charges'     => $charges,
                                    'chargesWithDeeds'     => $chargesWithDeeds,
                                                 
                                )
                ], 200);            
            }else{
                return response()->json([
                    'message' => 'We can \'t find a company.',
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

        $type = $request->type;
        if($type == 'submit'){

        $userId = User::where('email', $request->input('email'))->value('id');
        $comid = $request->id;
        $reqid = $request->reqid;
        $scid = $request->scid;
        $scchangeid = $request->scchangeid;

        if( intval($reqid) ){
            $req = CompanyChangeRequestItem::find($reqid);   
        }       
        else{
            $req = new CompanyChangeRequestItem;        
        }

        $req->company_id = $comid;
        $req->request_by = $userId;
        $req->request_type = $this->settings('SATISFACTION_CHARGE_CHANGE', 'key')->id;
        $req->status = $this->settings('COMPANY_CHANGE_PROCESSING', 'key')->id;
        $req->save();

        // delete members in declared members table if there is any

        $removemembers = DeclaredMembers::where('request_id', $req->id)->get();
        if($removemembers){
            $removemembers = DeclaredMembers::where('request_id', $req->id)->delete();

        }

        // delete members in declared members table if there is any //

        // adding selected members

        $newMembs = $request->input('decMembArray');

        foreach($newMembs as $newMemb){
            if(!empty($newMemb)){
                if($newMemb['type'] == 0){

                $memb = new DeclaredMembers;
                $memb->request_id = $req->id;
                $memb->member_id = $newMemb['id'];
                $memb->save();

                }
                elseif($newMemb['type'] == 1){

                $memb = new DeclaredMembers;
                $memb->request_id = $req->id;
                $memb->member_firm_id = $newMemb['id'];
                $memb->save();

                }

                
                                            
            }
            
        }
        // adding selected members //

        if( intval($scid) ){
            $sc = SatisfactionCharge::find($scid);   
        }       
        else{
            $sc = new SatisfactionCharge;       
        }       
        // $arr = explode("/",$request->input('Intype'));
        // $chargeid = (int)$arr[0];
        $charge = Charges::where('charges.request_id',$request->Intype)
        ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
        ->first();
        

                $sc->company_id = $comid;
                $sc->satisfaction_date = $request->input('SatDate');
                $sc->declaration_date = $request->input('DecDate');
                $sc->end_date = $request->input('EndDate');
                $sc->instrument_type = $request->input('Intype');
                $sc->prop_ids = json_encode($request->input('IntypeProp'));
                $sc->instrument_details = $request->input('Indetails');
                $sc->satisfaction_amount = $request->input('SatAmount');
                $sc->charges_id = $charge->id;
                $sc->instrument_details_other_date = $request->input('IndetailsOther');
                $sc->property_details = $request->input('ProDetails');
                $sc->full_ex = $request->input('Fullex');
                $sc->save();

        // delete deeds in satis deed table if there is any

        $removedeeds = SatisDeed::where('satisfaction_of_charge_id', $sc->id)->get();
        if($removedeeds){
            $removedeeds = SatisDeed::where('satisfaction_of_charge_id', $sc->id)->delete();

        }

        // delete deeds in satis deed table if there is any

        // adding deeds in satis deed table 

        $deedIDS = $request->input('IntypeProp');

        foreach($deedIDS as $deedID){
            if(!empty($deedID)){

                $deed = new SatisDeed;
                $deed->satisfaction_of_charge_id = $sc->id;
                $deed->charges_deed_item_id = $deedID;
                $deed->save();

                
                                            
            }
            
        }
        // adding deeds in satis deed table 

                if( intval($scchangeid) ){
                    $scchange = CompanyItemChange::find($scchangeid);   
                }       
                else{
                    $scchange = new CompanyItemChange;        
                }

                $scchange->request_id = $req->id;
                $scchange->changes_type = $this->settings('ADD', 'key')->id;
                $scchange->item_table_type = $this->settings('EROC_SATISFACTION_OF_CHARGES', 'key')->id;
                $scchange->item_id = $sc->id;
                $scchange->save();

                $update_compnay_updated_at = array(
                    'updated_at' => date('Y-m-d H:i:s', time())
                );
                Company::where('id', $request->id)
                ->update($update_compnay_updated_at);
        
    

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'reqID' => $req->id,
            'data'   => array(
                'reqid'     => $req->id,
                'scid'     => $sc->id,
                'scchangeid'     => $scchange->id,

                          
            )
        ], 200);

        }
        elseif($type == 'resubmit'){

        $userId = User::where('email', $request->input('email'))->value('id');
        $comid = $request->id;
        $reqid = $request->reqid;
        $scid = $request->scid;
        $scchangeid = $request->scchangeid;

        if( intval($reqid) ){
            $req = CompanyChangeRequestItem::find($reqid);   
        }       
        
        // delete members in declared members table if there is any

        $removemembers = DeclaredMembers::where('request_id', $req->id)->get();
        if($removemembers){
            $removemembers = DeclaredMembers::where('request_id', $req->id)->delete();

        }

        // delete members in declared members table if there is any //

        // adding selected members

        $newMembs = $request->input('decMembArray');

        foreach($newMembs as $newMemb){
            if(!empty($newMemb)){
                if($newMemb['type'] == 0){

                $memb = new DeclaredMembers;
                $memb->request_id = $req->id;
                $memb->member_id = $newMemb['id'];
                $memb->save();

                }
                elseif($newMemb['type'] == 1){

                $memb = new DeclaredMembers;
                $memb->request_id = $req->id;
                $memb->member_firm_id = $newMemb['id'];
                $memb->save();

                }

                
                                            
            }
            
        }
        // adding selected members //

        if( intval($scid) ){
            $sc = SatisfactionCharge::find($scid);   
        }
        // $arr = explode("/",$request->input('Indetails'));
        // $chargeid = (int)$arr[0]; 
        
        $charge = Charges::where('charges.request_id',$request->Intype)
        ->where('charges.status', '=', $this->settings('CHARGES_REGISTRATION_APPROVED','key')->id)
        ->first();

                $sc->company_id = $comid;
                $sc->satisfaction_date = $request->input('SatDate');
                $sc->declaration_date = $request->input('DecDate');
                $sc->end_date = $request->input('EndDate');
                $sc->instrument_type = $request->input('Intype');
                $sc->prop_ids = json_encode($request->input('IntypeProp'));
                $sc->instrument_details = $request->input('Indetails');
                $sc->satisfaction_amount = $request->input('SatAmount');
                $sc->charges_id = $charge->id;
                $sc->instrument_details_other_date = $request->input('IndetailsOther');
                $sc->property_details = $request->input('ProDetails');
                $sc->full_ex = $request->input('Fullex');
                $sc->save();

        // delete deeds in satis deed table if there is any

        $removedeeds = SatisDeed::where('satisfaction_of_charge_id', $sc->id)->get();
        if($removedeeds){
            $removedeeds = SatisDeed::where('satisfaction_of_charge_id', $sc->id)->delete();

        }

        // delete deeds in satis deed table if there is any

        // adding deeds in satis deed table 

        $deedIDS = $request->input('IntypeProp');

        foreach($deedIDS as $deedID){
            if(!empty($deedID)){

                $deed = new SatisDeed;
                $deed->satisfaction_of_charge_id = $sc->id;
                $deed->charges_deed_item_id = $deedID;
                $deed->save();

                
                                            
            }
            
        }
        // adding deeds in satis deed table

        if( intval($scchangeid) ){
            $scchange = CompanyItemChange::find($scchangeid);   
        }

                $scchange->request_id = $req->id;
                $scchange->changes_type = $this->settings('ADD', 'key')->id;
                $scchange->item_table_type = $this->settings('EROC_SATISFACTION_OF_CHARGES', 'key')->id;
                $scchange->item_id = $sc->id;
                $scchange->save();
        
        $update_compnay_updated_at = array(
                    'updated_at' => date('Y-m-d H:i:s', time())
                );
        Company::where('id', $request->id)
                ->update($update_compnay_updated_at);

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
            'reqID' => $req->id,
            'data'   => array(
                'reqid'     => $req->id,
                'scid'     => $sc->id,
                'scchangeid'     => $scchange->id,

                          
            )
        ], 200);

        }



    }

    public function set_month($date){
        if(!empty($date)) {

        $arr = explode("-",$date);
        if ($arr[1] == '01') {
            return "January";

        }elseif ($arr[1] == '02') {
            return "February";

        }elseif ($arr[1] == '03') {
            return "March";

        }elseif ($arr[1] == '04') {
            return "April";

        }elseif ($arr[1] == '05') {
            return "May";

        }elseif ($arr[1] == '06') {
            return "June";

        }elseif ($arr[1] == '07') {
            return "July";

        }elseif ($arr[1] == '08') {
            return "August";

        }elseif ($arr[1] == '09') {
            return "September";

        }elseif ($arr[1] == '10') {
            return "October";

        }elseif ($arr[1] == '11') {
            return "November";

        }elseif ($arr[1] == '12') {
            return "December";

        }

        }
        else{
            return false;
        }
        

    }

    //for view form 17 pdf...
public function generate_pdf(Request $request) {

    if(isset($request->scid) && isset($request->requestID)){
        
        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();

        $comId = $request->input('comId');

        $company = Company::where('id',$comId)->first();

        $company1 = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['companies.id','companies.name','companies.address_id','company_certificate.registration_no as registration_no']);
        
        $regNo =   $company1[0]['registration_no'];
        $scid = $request->scid;

        $sc = SatisfactionCharge::where('id',$scid)->first();

        $satisfaction_date = $sc->satisfaction_date;
        $end_date = $sc->end_date;
        $declaration_date = $sc->declaration_date;
        $instrument_type = $sc->instrument_type;
        $instrument_details = $sc->instrument_details;
        $instrument_details_other_date = $sc->instrument_details_other_date;
        $property_details = $sc->property_details;
        $full_ex = $sc->full_ex;
        $chargeid = $sc->charges_id;

        $satMonth = $this->set_month($satisfaction_date);
        $decMonth = $this->set_month($declaration_date);
        $endMonth = $this->set_month($end_date);
        $end_date = date('dS Y F', strtotime($end_date));
        $satisfaction_date = date('dS Y F', strtotime($satisfaction_date));

        if($instrument_type == "other"){
            $inDate = $instrument_details;
            $inMonth = $this->set_month($inDate);
            $inDetails = "Form10-". $inDate;

        }
        else{
            if($instrument_details == "other"){
                $inDate = $instrument_details_other_date;
                $inMonth = $this->set_month($inDate);
                $inDetails = "Form10-". $inDate;
            }
            else{
                // $arr = explode("/",$instrument_details);
                // $chargeid = (int)$arr[0];

                $charges = Charges::where('id',$chargeid)->first();
                

                $inDate = $charges->charge_date;
                $inMonth = $this->set_month($inDate);
                $inDate = date('dS Y F', strtotime($inDate));
                //$inDetails = "Form10-". $inDate;
                $inDetails = $instrument_details;

            }
        }

        $changeRequest = CompanyChangeRequestItem::where('id',$request->requestID)->first();

        $membersSelected = DeclaredMembers::leftJoin('company_members','company_members.id','=','company_change_declare_members.member_id')
                ->leftJoin('settings','company_members.designation_type','=','settings.id')
                ->leftJoin('addresses','company_members.address_id','=','addresses.id')
                ->where('company_change_declare_members.request_id',$changeRequest->id)
                ->where('company_members.company_id',$request->comId)
                ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
                ->get(['company_members.id as id','company_members.first_name as first_name','company_members.last_name as last_name','settings.value as designation','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city']);
        
        $memberfirmsSelected = DeclaredMembers::leftJoin('company_member_firms','company_member_firms.id','=','company_change_declare_members.member_firm_id')
                ->leftJoin('settings','company_member_firms.type_id','=','settings.id')
                ->leftJoin('addresses','company_member_firms.address_id','=','addresses.id')
                ->where('company_change_declare_members.request_id',$changeRequest->id)
                ->where('company_member_firms.company_id',$request->comId)
                ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
                ->get(['company_member_firms.id as id','company_member_firms.name as name','settings.value as designation','addresses.address1 as address1','addresses.address2 as address2','addresses.city as city']);
        
        $dateselected = array();
            foreach ($membersSelected as $key => $value) {
                      $dateselected[] = [
                            "id" => $value->id,
                            'type' => 0,
                            'show' => false,
                            'disable' => false,
                            "title" => $value->title,
                            "address" => $value->address1 .' '. $value->address2 .' '. $value->city,
                            "first_name" => $value->first_name,
                            "last_name" => $value->last_name,
                            "designation" => $value->designation,
                            "comdesignation" => $value->designation,
                      ];
                    }
        
            foreach ($memberfirmsSelected as $key => $value) {
                      $dateselected[] = [
                            "id" => $value->id,
                            'type' => 1,
                            'show' => false,
                            'disable' => false,
                            "title" => '',
                            "address" => $value->address1 .' '. $value->address2 .' '. $value->city,
                            "first_name" => $value->name,
                            "last_name" => '',
                            "designation" => 'Firm',
                            "comdesignation" => $value->designation,
                      ];
                    }
            foreach ($dateselected as $item){
                if($item['comdesignation'] == "Secretary"){
                    $sec = $item;
                    
                }
                
            }        

        $todayDate = date("Y-m-d");

        $day1 = date('d', strtotime($todayDate));
        $month1 = date('m', strtotime($todayDate));
        $year1 = date('Y', strtotime($todayDate));

        if(!empty($sec)){
            $fieldset = array(
                'comName' => $company->name,
                'comPostfix' => $company->postfix, 
                'comReg' => $company->registration_no,
                'member' => $dateselected,
                'satisfaction_date' => $satisfaction_date, 
                'declaration_date' => $declaration_date,
                'instrument_type' => $instrument_type, 
                'instrument_details' => $instrument_details, 
                'property_details' => $property_details,
                'full_ex' => $full_ex,
                'satMonth' => $satMonth,
                'decMonth' => $decMonth,
                'day1' => $day1, 
                'month1' => $month1, 
                'year1' => $year1, 
                'first_name' => $people->first_name,
                'last_name' => $people->last_name,
                'telephone' => $people->telephone,
                'mobile' => $people->mobile,
                'email' => $people->email,
                'regNo' => $regNo,
                'sec' => $sec,
                'inDate' => $inDate,
                'inMonth' => $inMonth,
                'endDate' => $end_date,
                'endMonth' => $endMonth,
                'inDetails' => $inDetails,
                
    
            );
            
    
            $pdf = PDF::loadView('scchange/form12A2',$fieldset);
            $pdf->stream('form-12A.pdf');

        }
        else{
            $fieldset = array(
                'comName' => $company->name,
                'comPostfix' => $company->postfix, 
                'comReg' => $company->registration_no,
                'member' => $dateselected,
                'satisfaction_date' => $satisfaction_date, 
                'declaration_date' => $declaration_date,
                'instrument_type' => $instrument_type, 
                'instrument_details' => $instrument_details, 
                'property_details' => $property_details,
                'full_ex' => $full_ex,
                'satMonth' => $satMonth,
                'decMonth' => $decMonth,
                'day1' => $day1, 
                'month1' => $month1, 
                'year1' => $year1, 
                'first_name' => $people->first_name,
                'last_name' => $people->last_name,
                'telephone' => $people->telephone,
                'mobile' => $people->mobile,
                'email' => $people->email,
                'regNo' => $regNo,
                'inDate' => $inDate,
                'inMonth' => $inMonth,
                'endDate' => $end_date,
                'endMonth' => $endMonth,
                'inDetails' => $inDetails,
                
    
            );
    
            $pdf = PDF::loadView('scchange/form12A',$fieldset);
            $pdf->stream('form-12A.pdf');
        }
                       
        
        
           
    }
    else{            
        return response()->json([
            'message' => 'We can \'t find a bsd.',
            'status' =>false,
        ], 200);
    }    
    
 }

 //for upload accounting addresschange pdf...
public function scUploadPdf(Request $request){

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
        $docIdArray = Documents::where('key','FORM_12A')->select('id')->first();
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

// for load sc uploaded files...
public function scFile(Request $request){
    if(isset($request)){
        $type = $request->type;
        if($type == 'submit'){

            $comId = $request->comId;
            $reqid = $request->requestId;
            $scchangeid = $request->scchangeid;
            //$docIdArray = Documents::where('key','FORM_12A')->select('id')->first();

            // $changedetails = ChangeAddress::where('type_id',$comId)
            //                 ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
            //                 ->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id)
            //                 ->first();
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)

        $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                    ->where('company_documents.company_id',$comId)
                                   //->where('company_documents.document_id',$docIdArray->id)
                                   //->where('company_documents.change_id',$scchangeid)
                                   ->where('company_documents.request_id',$reqid)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->get(['company_documents.id','company_documents.file_description','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname']);

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
            $scchangeid = $request->scchangeid;
            // $docIdArray = Documents::where('key','FORM_17')->select('id')->first();

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
                                   //->where('company_documents.change_id',$scchangeid)
                                   ->where('company_documents.request_id',$reqid)
                                   ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                   ->get(['company_documents.id','company_documents.file_description','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname','company_document_status.company_document_id as company_document_id','company_document_status.comments as comments','settings.value as value','settings.key as setKey']);

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
function deletescPdf(Request $request){
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

public function scUpdateUploadPdf(Request $request){

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

function deletescPdfUpdate(Request $request){
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

public function resubmitsc (Request $request){


    if(isset($request->reqid)){
        CompanyChangeRequestItem::where('id', $request->reqid)
    ->update(['status' => $this->settings('COMPANY_CHANGE_RESUBMITTED','key')->id]);
    


    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
    ], 200);

    }
    
}
}
