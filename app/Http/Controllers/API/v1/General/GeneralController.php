<?php

namespace App\Http\Controllers\API\v1\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use DB;
use App\Http\Resources\DocCollection;
use App\Documents;
use App\Company;
use App\CompanyCertificate;
use App\CompanyPostfix;
use App\DocumentsGroup;
use App\TempFile;
use App\CompanyDocuments;
use App\SecretaryDocument;
use App\TenderDocument;
use App\PublisherDocument;
use Storage;
use App\Province;
use App\District;
use App\City;
use App\Payment;
use App\GNDivision;
use App\SocietyDocument;
use App\AuditorDocument;
use App\Country;
use App\TenderCertificate;
use App\CompanyMember;
use App\CompanyFirms;
use App\User;
use App\ChangeName;

class GeneralController extends Controller
{

    use _helper;

    public function getCompanyType()
    {
        return response()->json($this->settings('COMPANY_TYPES'), 200);
    }

    public function getSubCompanyType(Request $request)
    {
        return CompanyPostfix::where('company_type_id', $request->id)->get();
    }

    public function getdocDynamic(Request $request)
    {
        $company_id = $request->companyId;

        //if(!$company_id) {
        //    return response()->json(['error' => 'Error decoding authentication request.'], 200);
       // }
       
        $company_status = '';
        if($company_id) {
            $company_info = Company::where('id', $company_id)->first();
            $company_status = $this->settings($company_info->status,'id')->key;
        }
        $other_doc_fields = array();
       

        $docs = DocumentsGroup::where('request_type', $request->req)
        ->where('status', 1)
        ->get();
       

        if($request->type != null){
            $docs = DocumentsGroup::where('company_type', $request->type)
                ->where('request_type', $request->req)
                ->where('status', 1)
                ->get();

               
        }
        $reqcount = 0;
        if (count($docs) > 0) {
            foreach ($docs as $key => $value) {

               

                $group = Documents::where('document_group_id', '=', $value->id)->where('status', 1)->get();
                
                $i =0;
                foreach ($group as $ky => $val) {

                    if($company_status != 'COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT') {

                        CompanyDocuments::where('company_id', $company_id)
                        ->where('document_id', $val->id)
                        ->whereNotIn('status', array( $this->settings('DOCUMENT_EMAILED', 'key')->id) )
                        ->delete();
                        
                    }

                    if($val->key != 'NAME_CHANGE_OTHER_DOCUMENTS') {

                        if( $val->key == 'NAME_CHANGE_LETTER'){ // skip old name change document
                          //  continue;
                        }
    
                        if(
                            !$company_id && 
                            in_array($val->key, array('NAME_CHANGE_LETTER_PRIVATE', 'NAME_CHANGE_LETTER_PUBLIC', 'NAME_CHANGE_LETTER_UNLIMITED', 'NAME_CHANGE_LETTER_GUARANTEE_32' , 'NAME_CHANGE_LETTER_GUARANTEE_34' , 'NAME_CHANGE_LETTER_OFFSHORE'))
                        
                        ) { // if name reservation via name change
                            continue;
                        }

                       
                       
                    

                    if ($val->is_required == 'yes') $reqcount += 100;


                   



                    $fields[$i] = [
                        'id' => $val->id,
                        'name' => $val->description,
                        'key' => $val->key,
                        'is_required' => $val->is_required
                    ];
                    $i++;

                  } else {
                    $other_doc_fields[0] = [
                        'id' => $val->id,
                        'name' => $val->description,
                        'key' => $val->key,
                        'is_required' => 'no'
                    ];
                  }
                }

                $collection[$key] = [
                    'id' => $value->id,
                    'description' => $value->description,
                    'fields' => $fields,
                    'other_doc_fields'=> $other_doc_fields
                ];
            }
            

            $prior_approval_letter_doc = Documents::where('key', 'NAME_CHANGE_LETTER')->first();
            $pa_letter = CompanyDocuments::where('company_id', $company_id)
            ->where('document_id', $prior_approval_letter_doc->id)
            ->first();


            /*************company members */
            $date = array();
            $old_company_info = array();
            $namechangeRec = ChangeName::where('new_company_id', $company_id)->first();
            if(isset($namechangeRec->id)) {
               

                $member = CompanyMember::leftJoin('settings', 'settings.id', '=', 'company_members.designation_type')
                ->where('company_id', str_replace('"', '', $namechangeRec->old_company_id))
                ->where('company_members.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                ->whereIn('designation_type', [$this->settings('DERECTOR', 'key')->id, $this->settings('SECRETARY', 'key')->id])
                ->select('company_members.id as id', 'company_members.title', 'company_members.first_name', 'company_members.last_name', 'settings.value as designation')
                ->get();
    
                $memberfirm = CompanyFirms::where('company_id', str_replace('"', '', $namechangeRec->old_company_id))
                    ->where('company_member_firms.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                    ->select('company_member_firms.id as id', 'company_member_firms.name')
                    ->get();
        
            
                foreach ($member as $key => $value) {
                $date[] = [
                        "id" => $value->id,
                        'type' => 0,
                        "title" => $value->title,
                        "first_name" => $value->first_name,
                        "last_name" => $value->last_name,
                        "designation" => $value->designation,
                ];
                }
        
                foreach ($memberfirm as $key => $value) {
                $date[] = [
                        "id" => $value->id,
                        'type' => 1,
                        "title" => '',
                        "first_name" => $value->name,
                        "last_name" => '',
                        "designation" => 'Firm',
                ];
                }

               // $old_company_info = Company::where('id',$namechangeRec->old_company_id)->first();

            }
           

        /***************end company members */
            $company_exp_at = null;
            $name_renew_at = isset($company_info->name_renew_at) ? $company_info->name_renew_at : null;
            if($name_renew_at) {
                
                $name_renew_at_timestap = strtotime($company_info->name_renew_at);
                $days_90 = 60*60*24*90;
                $name_exp_at_timestamp = $name_renew_at_timestap + $days_90;
                //$company_exp_at = date('Y-m-d' ,$name_exp_at_timestamp);
                
                $today = time();
                $company_exp_at = $name_exp_at_timestamp > $today ? date('Y-m-d' ,$today) : date('Y-m-d' ,$name_exp_at_timestamp);

            }

            


            $datarec = [
                'count' => $reqcount,
                'collection' => array_values($collection),
                'status' => $company_status,
                'prior_approval_letter_doc_name' => isset($pa_letter->id) ? $pa_letter->name : '',
                'prior_approval_letter_doc_token' => isset($pa_letter->id) ? $pa_letter->file_token : '',
                'pa_info' => $pa_letter,
                'company_id' => $company_id,
                'doc_id' => $prior_approval_letter_doc->id,
                'memberdata'=>$date,
                'company_reservation_at' => isset($company_info->name_resavation_at) ? $company_info->name_resavation_at : null,
                'company_exp_at' => $company_exp_at,
                'resolution_date' => isset($namechangeRec->resolution_date) ? $namechangeRec->resolution_date : null,
                'old_company_id' => isset($namechangeRec->old_company_id) ? $namechangeRec->old_company_id : null

                
            ];

            return response()->json($datarec, 200);

        } else {
            return response()->json(['error' => 'Error decoding authentication request.'], 400);
        }
    }

    public function getMemberTitle()
    {
        return $this->settings('NAME_TITLE');
    }

    public function getDocument(Request $request)
    {
		if($this->getDocModel($request->type)){
	        $document = $this->getDocModel($request->type)->where('file_token', $request->token)->first();
	        if (!empty($document)) {
	            return Storage::disk('sftp')->get($document->path);
	        }
        }
		return response()->json(['error' => 'Unavailable data request.'], 400);
    }

    public function getDocName(Request $request)
    {
		if($this->getDocModel($request->type)){
	        $document = $this->getDocModel($request->type)->where('file_token', $request->token)->first();
	        if (!empty($document)) {
	            return response()->json($document->name);
	        }
        }
		return response()->json(['error' => 'Unavailable data request.'], 400);
    }

    public function getDocModel($type){
        if($type == 'CAT_COMPANY_DOCUMENT'){
            return new CompanyDocuments();
        } else if($type == 'CAT_SECRETARY_DOCUMENT'){
            return new SecretaryDocument();
        } else if($type == 'CAT_TENDER_DOCUMENT'){
            return new TenderDocument();
        }else if($type == 'CAT_INVOICE'){
            return new Payment();
        }else if($type == 'CAT_AUDITOR_DOCUMENT'){
            return new AuditorDocument();
        }else if($type == 'CAT_SOCIETY_DOCUMENT'){
            return new SocietyDocument();
        }else if($type == 'CAT_TENDER_CERTIFICATE_DOCUMENT'){
            return new TenderCertificate();
        }
        else if($type == 'CAT_TENDER_PUBLISHER_DOCUMENT') {
            return new PublisherDocument();
        }else{
            return false;
        }
    }

    public function isFileDestroy(Request $request)
    {

        $document = CompanyDocuments::where('file_token', $request->token)->first();
        if (!empty($document)) {
            $document->delete();
            $delete = Storage::disk('sftp')->delete($document->path);
            return response()->json($delete, 200);
        } else {
            return response()->json(false, 400);
        }
    }

    public function isResubmitFileDestroy(Request $request)
    {
        $document = CompanyDocuments::where('file_token', $request->token)->first();

        if (!empty($document)) {
            $path = $document->path;
            $document->status = $this->settings('DOCUMENT_REQUESTED', 'key')->id;
            $document->file_token = null;
            $document->path = null;
            $document->save();
            $delete = Storage::disk('sftp')->delete($path);
            return response()->json($delete, 200);
        } else {
            return response()->json(false, 400);
        }
    }


    public function getStatusCount(Request $request)
    {

        $data = [
            'all' => $this->status($request->email, 'all'),
            'inProgress' => $this->status($request->email, 'inProgress'),
            'resubmit' => $this->status($request->email, 'resubmit'),
            'pending' => $this->status($request->email, 'COMPANY_NAME_PENDING'),
            'approval' => $this->status($request->email, 'approval'),
            'rejected' => $this->status($request->email, 'COMPANY_NAME_REJECTED'),
            'canceled' => $this->status($request->email, 'COMPANY_NAME_CANCELED'),
            'inpending' => $this->status($request->email, 'inpending'),
            'inapproval' => $this->status($request->email, 'inapproval'),
            'inrejected' => $this->status($request->email, 'COMPANY_STATUS_REJECTED'),
        ];
        return $data;

    }

    public function status($email, $status)
    {
        $notIn = array(
            $this->settings('COMPANY_NAME_PROCESSING', 'key')->id,
            $this->settings('COMPANY_NAME_EXPIRED', 'key')->id
        );

        $userInfo = User::where('email', $this->clearEmail($email))->first();
        $clearEmail = $this->clearEmail($email);
        $directorKeyId = $this->settings('DERECTOR', 'key')->id;
        $secKeyId = $this->settings('SECRETARY', 'key')->id;

        $stakeholderRole = $userInfo->stakeholder_role;
        $is_director_or_sec = $stakeholderRole == $directorKeyId || $stakeholderRole == $secKeyId;

        if ($is_director_or_sec) {
            $count =   User::leftJoin('company_members', 'users.email', '=', 'company_members.email')
                                ->leftJoin('company_member_firms', 'users.email', '=', 'company_member_firms.email')
                                ->leftjoin('companies', 'company_members.company_id', '=', 'companies.id')
                                ->where('users.email', '=', $this->clearEmail($email))
                                ->whereNotIn('companies.status', $notIn);
        }else {
            $count = Company::leftJoin('users', 'companies.created_by', '=', 'users.id')
            ->where('users.email', '=', $this->clearEmail($email))
            ->whereNotIn('companies.status', $notIn);
        }
        
        
        if ($status != 'all') {
            if($status == 'inProgress'){
                $notIn = array(
                    $this->settings('COMPANY_NAME_PENDING', 'key')->id,
                    $this->settings('COMPANY_NAME_RULES_VERIFICATION', 'key')->id,
                    $this->settings('COMPANY_NAME_RECOMMEND_FOR_APPROVAL', 'key')->id,
                    $this->settings('COMPANY_NAME_NOT_RECOMMEND_FOR_APPROVAL', 'key')->id,
                    $this->settings('COMPANY_NAME_RESUBMITTED', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_RESUBMITTED', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT_INTERNAL1', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_NOT_RECOMMEND_FOR_APPROVAL1', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT_INTERNAL2', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_NOT_RECOMMEND_FOR_APPROVAL2', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_RECOMMEND_FOR_APPROVAL1', 'key')->id,
                    $this->settings('COMPANY_STATUS_RECOMMEND_FOR_APPROVAL', 'key')->id,
                    $this->settings('COMPANY_NAME_RESERVED', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_PENDING', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_RECOMMEND_FOR_APPROVAL', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_NOT_RECOMMEND_FOR_APPROVAL', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_RESUBMITTED', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_REJECTED', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id,
                    $this->settings('COMPANY_NAME_REQUEST_TO_RESUBMIT_INTERNAL', 'key')->id,
                    $this->settings('COMPANY_STATUS_REQUEST_TO_RESUBMIT_INTERNAL', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT_INTERNAL', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_PENDING', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_RECOMMEND_FOR_APPROVAL1', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_NOT_RECOMMEND_FOR_APPROVAL1', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_RESUBMITTED', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT_INTERNAL1', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_RECOMMEND_FOR_APPROVAL2', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_NOT_RECOMMEND_FOR_APPROVAL2', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT_INTERNAL2', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_PAYMENT_PENDING', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_PAYMENT_DONE', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_PENDING', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_RECOMMEND_FOR_APPROVAL', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_NOT_RECOMMEND_FOR_APPROVAL', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_RESUBMITTED', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT_INTERNAL', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_REJECTED', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_WAITING_FOR_EFFECT_ON_DATE', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_DOCUMENT_SEALED', 'key')->id
                );
                $count = $count->whereIn('companies.status',$notIn);
            }else if($status == 'resubmit'){
                $notIn = array(
                    $this->settings('COMPANY_STATUS_REQUEST_TO_RESUBMIT', 'key')->id,
                    $this->settings('COMPANY_NAME_REQUEST_TO_RESUBMIT', 'key')->id
                );
                $count = $count->whereIn('companies.status',$notIn);
            }else if($status == 'approval'){
                $notIn = array(
                    $this->settings('COMPANY_NAME_APPROVED', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_APPROVED', 'key')->id,
                    $this->settings('COMPANY_NAME_CHANGE_APPROVED', 'key')->id,
                    $this->settings('COMPANY_STATUS_FOREIGN_APPROVED', 'key')->id,
                    $this->settings('COMPANY_ADDRESS_CHANGE_APPROVED', 'key')->id
                );
                $count = $count->whereIn('companies.status',$notIn);
            }else if($status == 'inpending'){
                $notIn = array(
                    $this->settings('COMPANY_STATUS_PENDING', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_APPROVED', 'key')->id
                );
                $count = $count->whereIn('companies.status',$notIn);
            }else if($status == 'inapproval'){
                $notIn = array(
                    $this->settings('COMPANY_STATUS_APPROVED', 'key')->id,
                    $this->settings('COMPANY_FOREIGN_STATUS_PENDING', 'key')->id
                );
                $count = $count->whereIn('companies.status',$notIn);
            }else{
                $count = $count->where('companies.status', $this->settings($status, 'key')->id);
            }
        }
        return $count->count();
    }

    public function getCountryDetails(){


        $data = [
            'province' => Province::all()->toArray(),
            'district' => District::all()->toArray(),
            'payment' =>  $this->getPayment()
        ];

        return response()->json($data);
    }

    public function getGnandCity(){
        $data = [
            'city' => City::all()->toArray(),
            'gndivision' => GNDivision::all()->toArray()
        ];
        return response()->json($data);
    }


    public function getPayment(){
        $payment = array();
        $pay = $this->settings('PAYMENTS', 'type');
        foreach ($pay as $key => $value) {
            $payment[$value->key] = $value;
        }
        return $payment;
    }

    public function validSecToken(Request $request){
        $pass = $this->getSecToken($request->email, $this->settings($request->type, 'key')->id , $request->token);
        return response()->json(['status' => $pass], 200);
    }


    public function getCountry(Request $request){
        $country = Country::where('status', 1)->select('id','name')->get()->toArray();
        return response()->json(['countries'=> $country, 'companies' => $this->getAdminCompanies()], 200);
    }


    function checkCompanyByRegNumber(Request $request){
        $regNumber = $request->registration_no;
       
        if(!$regNumber) {
            return response()->json(['status'=> false, 'message'=> 'Inavlid Company Registration Number', 'company_name' => '','company_id' =>  null], 200);
        }

        $regInfo = CompanyCertificate::where('registration_no', strtoupper(trim($regNumber)) )->first();

        if(isset($regInfo->company_id)) {
            $companyInfo = Company::where('id', $regInfo->company_id)->first();
            return response()->json(['status'=> true, 'message'=> 'Company Found', 'company_name' => $companyInfo->name,'company_id' =>  $companyInfo->id], 200);
        }else{
            return response()->json(['status'=> false, 'message'=> 'No companies found under this registration number', 'company_name' => null, 'company_id' =>  null], 200);
        }

    }


    private function getAdminCompanies() {

        $company_types = array(
                                $this->settings('COMPANY_TYPE_OFFSHORE', 'key')->id,
                                $this->settings('COMPANY_TYPE_OVERSEAS', 'key')->id
                        );
        
        $companies = Company::where('status', $this->settings('COMPANY_STATUS_APPROVED', 'key')->id)
                            ->whereNotIn('type_id',$company_types )
                            ->limit(5)
                            ->get();
  
        $companyList = array();
        if(isset($companies[0]->id)) {
            foreach($companies  as $c ) {
                $companyCertificate = CompanyCertificate::where('company_id', $c->id)
                                    ->where('is_sealed', 'yes')
                                    ->first();
                $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';

                $row = array();
                $row['id'] = $c->id;
                $row['name'] = ($certificate_no) ? $c->name .' - '.$certificate_no :  $c->name;
                $companyList[] = $row;
            }
        }

        return $companyList;
    }

    public function getMember(Request $request)
    {
        if ($request->id != null) {
            $member = CompanyMember::leftJoin('settings', 'settings.id', '=', 'company_members.designation_type')
                ->where('company_id', str_replace('"', '', $request->id))
                ->where('company_members.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                ->whereIn('designation_type', [$this->settings('DERECTOR', 'key')->id, $this->settings('SECRETARY', 'key')->id])
                ->select('company_members.id as id', 'company_members.title', 'company_members.first_name', 'company_members.last_name', 'settings.value as designation')
                ->get();

            $memberfirm = CompanyFirms::where('company_id', str_replace('"', '', $request->id))
                ->where('company_member_firms.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                ->select('company_member_firms.id as id', 'company_member_firms.name')
                ->get();

            $date = array();
            foreach ($member as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    'type' => 0,
                    "title" => $value->title,
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "designation" => $value->designation,
              ];
            }

            foreach ($memberfirm as $key => $value) {
              $date[] = [
                    "id" => $value->id,
                    'type' => 1,
                    "title" => '',
                    "first_name" => $value->name,
                    "last_name" => '',
                    "designation" => 'Firm',
              ];
            }

            return response()->json(['status' => true, 'data' => $date], 200);
        } else {
            return response()->json(['status' => false, 'data' => []], 200);
        }
    }

    function getSetting(Request $request){
        return response()->json( @$this->settings($request->key, $request->type)->value, 200);
    }

}
