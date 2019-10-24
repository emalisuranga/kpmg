<?php

namespace App\Http\Controllers\API\v1\AddressChange;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Address;
use App\Company;
use App\ChangeAddress;
use App\Setting;
use App\User;
use App\CompanyFirms;
use App\People;
use App\CompanyStatus;
use App\CompanyDocumentStatus;
use App\Documents;
use App\CompanyMember;
use App\CompanyCertificate;
use App\CompanyDocuments;
use App\Http\Helper\_helper;
use PDF;
use Storage;

class AddressChangeController extends Controller
{
    use _helper;
    // loadCompanyAddress using company id number...
    public function loadCompanyAddress(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }
        $type = $request->type;
        if($type == 'submit'){

            $company1 = Company::where('id',$request->id)->first();
            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.address_id','companies.incorporation_at','company_certificate.registration_no as registration_no']);

        $address = Address::where('id',$company1->address_id)->first();

        $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->id)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->orderBy('settings.value')
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
            $company1 = Company::where('id',$request->id)->first();
            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.postfix','companies.incorporation_at','companies.address_id','company_certificate.registration_no as registration_no']);

            $address = Address::where('id',$company1->address_id)->first();
            $newaddress = Address::where('id',$request->addressid)->first();

            $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->id)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->orderBy('settings.value')
       ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

       $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->id)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_member_firms.id','company_member_firms.name']);

       $changedetails = ChangeAddress::leftJoin('settings','address_changes.signed_by_table_type','=','settings.id')->where('address_changes.id',$request->changeid)->get(['address_changes.signed_by','settings.key as tableType']);

       $signedby = $changedetails[0]['signed_by'];
       $signedbytype = $changedetails[0]['tableType'];

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
        

        if($address && $newaddress){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'address'     => $address,
                                'company'     => $company,
                                'newaddress'     => $newaddress,
                                'members'     => $date,
                                'signedby' => $signedby,
                                'signedbytype' => $signedbytype,
                                          
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
            ], 200);
        }
        }
        elseif($type == 'resubmit'){
            $company1 = Company::where('id',$request->id)->first();
            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','companies.incorporation_at','companies.postfix','companies.address_id','company_certificate.registration_no as registration_no']);

            $address = Address::where('id',$company1->address_id)->first();
            $newaddress = Address::where('id',$request->addressid)->first();

            $members = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.company_id',$request->id)
        ->where('company_members.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_members.designation_type', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->orderBy('settings.value')
       ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

       $memberfirms = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.company_id',$request->id)
        ->where('company_member_firms.status', '=', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
        ->where('company_member_firms.type_id', '!=', $this->settings('SHAREHOLDER','key')->id)
        ->get(['company_member_firms.id','company_member_firms.name']);

       $changedetails = ChangeAddress::leftJoin('settings','address_changes.signed_by_table_type','=','settings.id')->where('address_changes.id',$request->changeid)->get(['address_changes.signed_by','settings.key as tableType']);

       $external_comment_query = CompanyStatus::where('company_id',$request->id)
                                                    ->where('comment_type','=', $this->settings('COMMENT_EXTERNAL', 'key')->id )
                                                    ->where('change_id',$request->changeid)
                                                    ->where('status','=', $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id )
                                                    ->orderBy('id', 'desc')
                                                    ->limit(1)
                                                    ->first();
           $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                      ?  $external_comment_query->comments
                                      : '';

       $signedby = $changedetails[0]['signed_by'];
       $signedbytype = $changedetails[0]['tableType'];

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
        

        if($address && $newaddress){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'address'     => $address,
                                'company'     => $company,
                                'newaddress'     => $newaddress,
                                'members'     => $date,
                                'signedby' => $signedby,
                                'signedbytype' => $signedbytype,
                                'external_global_comment' => $external_global_comment,
                                          
                            )
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
            ], 200);
        }
        }
        

    }
    
    
    public function submitNewCompanyAddress(Request $request){

        if(!$request->comId){
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
            ], 200);
        }
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
        if(isset($request->changeid) && isset($request->newaddressid)){
            
                $address = Address::find($request->newaddressid);
                $address->address1 = $request->input('localAddress1');
                $address->address2 = $request->input('localAddress2');
                $address->province = $request->input('province');
                $address->district = $request->input('district');
                $address->city = $request->input('city');
                $address->gn_division = $request->input('gnDivision');
                $address->postcode = $request->input('postcode');
                $address->country = $request->input('country');
                $address->save();

                $newaddressid = $request->newaddressid;

                $user = User::where('email', $request->input('email'))->first();

                $change = ChangeAddress::find($request->changeid);
                $change->type_id = $request->input('comId');
                $change->old_address_id = $request->input('oldaddressId');
                $change->change_type = $this->settings('COMPANY_ADDRESS_CHANGE','key')->id;
                $change->status = $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id;
                $change->new_address_id = $address->id;
                $change->requesed_by = $user->id;
                $change->signed_by = $signbyid;
                $change->signed_by_table_type = $signbytype;
                $change->address_effect_on_date = $request->input('date');
                $change->save();

                $changeid = $change->id;
        }
        else{
                $address = new Address();
                $address->address1 = $request->input('localAddress1');
                $address->address2 = $request->input('localAddress2');
                $address->province = $request->input('province');
                $address->district = $request->input('district');
                $address->city = $request->input('city');
                $address->gn_division = $request->input('gnDivision');
                $address->postcode = $request->input('postcode');
                $address->country = $request->input('country');
                $address->save();

                $newaddressid = $address->id;

                $user = User::where('email', $request->input('email'))->first();

                $change = new ChangeAddress();
                $change->type_id = $request->input('comId');
                $change->old_address_id = $request->input('oldaddressId');
                $change->change_type = $this->settings('COMPANY_ADDRESS_CHANGE','key')->id;
                $change->status = $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id;
                $change->new_address_id = $address->id;
                $change->requesed_by = $user->id;
                $change->signed_by = $signbyid;
                $change->signed_by_table_type = $signbytype;
                $change->address_effect_on_date = $request->input('date');
                $change->save();

                $changeid = $change->id;
        }
        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->comId)
        ->update($update_compnay_updated_at);
       

        if($change && $address){            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true,
                'changeid' => $changeid,
                'newaddressid' => $newaddressid
                
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a Address.',
                'status' =>false,
            ], 200);
        }

    }

    public function generate_App_pdf(Request $request) {

        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();

        $comId = $request->input('comId');

        $company = Company::where('id',$comId)->first();

        $company1 = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['companies.id','companies.name','companies.address_id','company_certificate.registration_no as registration_no']);

        $regNo =   $company1[0]['registration_no'];                        
        $changedetails = ChangeAddress::where('id',$request->changeid)
        ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
        ->where(function ($query) {
            $query->where('status', '=', $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id)
                  ->orWhere('status', '=', $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT','key')->id);
        })
        ->first();
   
        $address = Address::where('id',$changedetails->new_address_id)->first();

        $o_date = $changedetails->address_effect_on_date;

        $todayDate = date("Y-m-d");

        $day = date('d', strtotime($o_date));
        $month = date('m', strtotime($o_date));
        $year = date('Y', strtotime($o_date));

        $day1 = date('d', strtotime($todayDate));
        $month1 = date('m', strtotime($todayDate));
        $year1 = date('Y', strtotime($todayDate));

        if($changedetails->signed_by_table_type == $this->settings('COMPANY_MEMBERS','key')->id){
            $member = CompanyMember::leftJoin('settings','company_members.designation_type','=','settings.id')
        ->where('company_members.id',$changedetails->signed_by)
       ->get(['company_members.id','company_members.first_name','company_members.last_name','settings.value as designation']);

       $fieldset = array(
        'comName' => $company->name,
        'comPostfix' => $company->postfix, 
        'comReg' => $company->registration_no,
        'memFirstName' => $member[0]['first_name'],
        'memLastName' => $member[0]['last_name'],
        'memDesignation' => $member[0]['designation'], 
        'address1' => $address->address1,
        'address2' => $address->address2, 
        'province' => $address->province, 
        'district' => $address->district,
        'city' => $address->city, 
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




 $pdf = PDF::loadView('addresschange-forms/form-13',$fieldset);
 $pdf->stream('form-13.pdf');

        }
        else {
            $member = CompanyFirms::leftJoin('settings','company_member_firms.type_id','=','settings.id')
        ->where('company_member_firms.id',$changedetails->signed_by)
       ->get(['company_member_firms.id','company_member_firms.name','settings.value as designation']);

       $fieldset = array(
        'comName' => $company->name,
        'comPostfix' => $company->postfix, 
        'comReg' => $company->registration_no,
        'memFirstName' => $member[0]['name'],
        'memDesignation' => $member[0]['designation'], 
        'address1' => $address->address1,
        'address2' => $address->address2, 
        'province' => $address->province, 
        'district' => $address->district,
        'city' => $address->city, 
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




 $pdf = PDF::loadView('addresschange-forms/form-13firm',$fieldset);
 $pdf->stream('form-13.pdf');
        }
 
 
     
 }


 //for upload addresschange pdf...
public function addresschangeUploadPdf(Request $request){

    if(isset($request)){

    $fileName =  uniqid().'.pdf';
    $token = md5(uniqid());

    $comId = $request->comId;
    $docType = $request->docType;
    $pdfName = $request->filename;
    $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->comId)
                                   ->get(['companies.id','companies.name','companies.incorporation_at','companies.postfix','companies.address_id','company_certificate.registration_no as registration_no']);
    

    $description = $request->description;
    if($description=='undefined'){
            $description=NULL;
        }

    $path = 'company/'.$comId;
    $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');

    $changedetails = ChangeAddress::where('type_id',$comId)
        ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
        ->where(function ($query) {
            $query->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id)
                  ->orWhere('status', $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT','key')->id);
        })->first();

    
    $docId;
    if($docType=='applicationUpload'){
        $docIdArray = Documents::where('key','FORM_13')->select('id')->first();
        // FORM 13: ABC (PVT) LTD
        $pdfName = 'FORM 13: '. $company[0]['name'] .' '. $company[0]['postfix'];
    $docId = $docIdArray->id;
    }elseif($docType=='extraUpload'){
        $docIdArray = Documents::where('key','EXTRA_DOCUMENT')->select('id')->first();
    $docId = $docIdArray->id;   
    }

    

    $socDoc = new CompanyDocuments;
    $socDoc->document_id = $docId;
    $socDoc->company_id = $comId;
    $socDoc->name = $pdfName;
    $socDoc->file_token = $token;
    $socDoc->path = $path;
    $socDoc->file_description = $description;
    $socDoc->change_id = $changedetails->id;
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

// for load addresschange uploaded files...
public function addresschangeFile(Request $request){
    if(isset($request)){
        $type = $request->type;
        if($type == 'submit'){

            $comId = $request->comId;
            $docIdArray = Documents::where('key','FORM_13')->select('id')->first();
            $changedetails = ChangeAddress::where('type_id',$comId)
                            ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
                            ->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_PROCESSING','key')->id)
                            ->first();
        //$uploadedPdf =  SecretaryDocument::where('secretary_id', $secId)->get(['id','document_id','name','file_token']);->where('society_documents_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
        $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                    ->where('company_documents.company_id',$comId)
                                   //->where('company_documents.document_id',$docIdArray->id)
                                   ->where('company_documents.change_id',$changedetails->id)
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
            $docIdArray = Documents::where('key','FORM_13')->select('id')->first();
            $changedetails = ChangeAddress::where('type_id',$comId)
                            ->where('change_type', $this->settings('COMPANY_ADDRESS_CHANGE','key')->id)
                            ->where('status', $this->settings('COMPANY_ADDRESS_CHANGE_REQUEST_TO_RESUBMIT','key')->id)
                            ->first();
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
                                   //->where('company_documents.document_id',$docIdArray->id)
                                   ->where('company_documents.change_id',$changedetails->id)
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
function deleteAddresschangePdf(Request $request){
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

// update addresschange data using company newaddressid id number...resubmitSociety
public function resubmitNewCompanyAddress (Request $request){

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

    Address::where('id', $request->newaddressid)
    ->update(['address1' => $request->input('localAddress1'),
    'address2' => $request->input('localAddress2'),
    'province' => $request->input('province'),
    'district' => $request->input('district'),
    'city' => $request->input('city'),
    'gn_division' => $request->input('gnDivision'),
    'postcode' => $request->input('postcode'),
    'country' => $request->input('country')]);

    ChangeAddress::where('id', $request->changeid)
    ->update(['signed_by' => $signbyid,'address_effect_on_date' => $request->input('date'),'signed_by_table_type' => $signbytype]);

    $update_compnay_updated_at = array(
        'updated_at' => date('Y-m-d H:i:s', time())
    );
    Company::where('id', $request->comId)
    ->update($update_compnay_updated_at);
    


    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
    ], 200);



}

public function addresschangeUpdateUploadPdf(Request $request){

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

function deleteAddresschangePdfUpdate(Request $request){
    if(isset($request)){
    $docId = $request->documentId;
    $type = $request->type;
    $docstatusid = CompanyDocumentStatus::where('company_document_id', $docId)->first();
    if($docstatusid){
        if($type =='additionalUpload'){

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
        }
        else{

            $document = CompanyDocuments::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);
            CompanyDocuments::where('id', $docId)
            ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            'name' => NULL,
            'file_token' => NULL,
            'path' => NULL]);
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


public function resubmitAddresschange (Request $request){



    ChangeAddress::where('id', $request->changeId)
    ->update(['status' => $this->settings('COMPANY_ADDRESS_CHANGE_RESUBMITTED','key')->id]);
    


    return response()->json([
        'message' => 'Sucess!!!',
        'status' =>true,
    ], 200);



}


}
