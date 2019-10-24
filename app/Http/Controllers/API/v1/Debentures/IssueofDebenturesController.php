<?php

namespace App\Http\Controllers\API\v1\Debentures;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\CompanyDebentures;
use App\CompanyChangeRequestItem;
use App\CompanyDocumentStatus;
use App\CompanyItemChange;
use App\Setting;
use App\User;
use App\People;
use App\Documents;
use App\CompanyDocuments;
use App\CompanyStatus;
use App\Http\Helper\_helper;
use PDF;
use Storage;
use App\CompanyFirms;
use App\CompanyMember;

class IssueofDebenturesController extends Controller
{
    use _helper;

    //Load previous approved debentures record  using company id number...
    public function loadPreApproved(Request $request){

      /*  if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $type = $request->type;

        $requestRecord = CompanyChangeRequestItem::where('company_id',$request->id)
                                ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                                ->where('status', $this->settings('COMPANY_CHANGE_APPROVED','key')->id)
                                ->orderBy('company_change_requests.id','DESC')
                                ->first();
        if($requestRecord){
            $debentures = CompanyDebentures::leftJoin('company_item_changes','company_debentures.id','=','company_item_changes.item_id')
                                ->where('company_item_changes.request_id',$requestRecord->id)
                                ->where('company_debentures.status', $this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                ->get(['company_debentures.total_amount_secured as totalAmountSecured','company_debentures.series','company_debentures.amount',
                                        'company_debentures.description','company_debentures.name_of_trustees as nameOfTrustees','company_debentures.date_of_covering_dead as dateOfCoveringDead',
                                        'company_debentures.date_of_resolution as dateOfResolution','company_debentures.date_of_issue as dateOfIssue']);
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'debentures'    => $debentures,                           
            ], 200);
        }else{
            return response()->json([
                'message' => 'no previous record',
                'status' =>false,                           
            ], 200);
        } */  

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $type = $request->type;
        $status = $request->status;

        if($type == 'submit'){
            $requestRecord = CompanyChangeRequestItem::where('company_id',$request->id)
                                    ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                                    ->where('status', $this->settings('COMPANY_DEBENTURES_PROCESSING','key')->id)
                                    ->orderBy('company_change_requests.id','DESC')
                                    ->first();
            if(isset($requestRecord->id)) {
                $processingdeb = CompanyDebentures::leftJoin('company_item_changes','company_debentures.id','=','company_item_changes.item_id')
                ->where('company_item_changes.request_id',$requestRecord->id)
                ->where('company_debentures.status', $this->settings('COMMON_STATUS_PENDING','key')->id)
                ->get(['company_debentures.total_amount_secured as totalAmountSecured','company_debentures.series','company_debentures.amount',
                        'company_debentures.description','company_debentures.name_of_trustees as nameOfTrustees','company_debentures.date_of_covering_dead as dateOfCoveringDead',
                        'company_debentures.date_of_resolution as dateOfResolution','company_debentures.date_of_issue as dateOfIssue']);
            }else {

                $processingdeb = array(
                    'id' => null,
                    'amount' => null,
                    'dateOfCoveringDead' => '',
                    'dateOfIssue' => '',
                    'dateOfResolution' => '',
                    'description' => '',
                    'nameOfTrustees' => '',
                    'series' => '',
                    'totalAmountSecured' => null
                );

            }

            $director_list = CompanyMember::where('company_id',$request->id)
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

            $sec_list = CompanyMember::where('company_id',$request->id)
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

            $sec_firm_list = CompanyFirms::where('company_id',$request->id)
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
           
            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'processingdeb' => $processingdeb,
                'directors' =>$directors,
                'secs' => $secs,
                'sec_firms' =>$sec_firms,    
                            
            ], 200); 
        }
        elseif($type == 'resubmit'){

            $requestRecord = CompanyChangeRequestItem::where('company_id',$request->id)
                                    ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                                    ->where('status', $this->settings('COMPANY_DEBENTURES_REQUEST_TO_RESUBMIT','key')->id)
                                    ->orderBy('company_change_requests.id','DESC')
                                    ->first();
            $processingdeb = CompanyDebentures::leftJoin('company_item_changes','company_debentures.id','=','company_item_changes.item_id')
                                    ->where('company_item_changes.request_id',$requestRecord->id)
                                    ->where('company_debentures.status', $this->settings('COMMON_STATUS_PENDING','key')->id)
                                    ->get(['company_debentures.id','company_debentures.total_amount_secured as totalAmountSecured','company_debentures.series','company_debentures.amount',
                                            'company_debentures.description','company_debentures.name_of_trustees as nameOfTrustees','company_debentures.date_of_covering_dead as dateOfCoveringDead',
                                            'company_debentures.date_of_resolution as dateOfResolution','company_debentures.date_of_issue as dateOfIssue']);

            $director_list = CompanyMember::where('company_id',$request->id)
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
                                
            $sec_list = CompanyMember::where('company_id',$request->id)
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
                                
            $sec_firm_list = CompanyFirms::where('company_id',$request->id)
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

            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'processingdeb' => $processingdeb,
                'directors' =>$directors,
                'secs' => $secs,
                'sec_firms' =>$sec_firms, 
            ], 200);
        }
    }

    
    // Load procesing debentures list
    public function loadProcesingList(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $type = $request->type;
        $status = $request->status;
        $external_global_comment = '';

        $director_list = CompanyMember::where('company_id',$request->id)
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
                                
            $sec_list = CompanyMember::where('company_id',$request->id)
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
                                
            $sec_firm_list = CompanyFirms::where('company_id',$request->id)
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

        if($type == 'submit'){
            $requestRecord = CompanyChangeRequestItem::where('company_id',$request->id)
                                    ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                                    ->where('status', $this->settings('COMPANY_DEBENTURES_PROCESSING','key')->id)
                                    ->orderBy('company_change_requests.id','DESC')
                                    ->first();

            if(isset($requestRecord->id)){
            $processingdeb = CompanyDebentures::leftJoin('company_item_changes','company_debentures.id','=','company_item_changes.item_id')
                                    ->where('company_item_changes.request_id',$requestRecord->id)
                                    ->where('company_debentures.status', $this->settings('COMMON_STATUS_PENDING','key')->id)
                                    ->get(['company_debentures.total_amount_secured as totalAmountSecured','company_debentures.series','company_debentures.amount',
                                            'company_debentures.description','company_debentures.name_of_trustees as nameOfTrustees','company_debentures.date_of_covering_dead as dateOfCoveringDead',
                                            'company_debentures.date_of_resolution as dateOfResolution','company_debentures.date_of_issue as dateOfIssue']);
            } else {
                $processingdeb = array();
            }
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'processingdeb' => $processingdeb,
                'directors' =>$directors,
                'secs' => $secs,
                'sec_firms' =>$sec_firms, 
                'signing_party_designation' => @$requestRecord->signing_party_designation,
                'signed_party_id' =>  @$requestRecord->signed_party_id
                            
            ], 200); 
        }
        elseif($type == 'resubmit'){

            $requestRecord = CompanyChangeRequestItem::where('company_id',$request->id)
                                    ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                                    ->where('status', $this->settings('COMPANY_DEBENTURES_REQUEST_TO_RESUBMIT','key')->id)
                                    ->orderBy('company_change_requests.id','DESC')
                                    ->first();
            $processingdeb = CompanyDebentures::leftJoin('company_item_changes','company_debentures.id','=','company_item_changes.item_id')
                                    ->where('company_item_changes.request_id',$requestRecord->id)
                                    ->where('company_debentures.status', $this->settings('COMMON_STATUS_PENDING','key')->id)
                                    ->get(['company_debentures.id','company_debentures.total_amount_secured as totalAmountSecured','company_debentures.series','company_debentures.amount',
                                            'company_debentures.description','company_debentures.name_of_trustees as nameOfTrustees','company_debentures.date_of_covering_dead as dateOfCoveringDead',
                                            'company_debentures.date_of_resolution as dateOfResolution','company_debentures.date_of_issue as dateOfIssue']);

            $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
      
            $external_comment_query = CompanyStatus::where('company_id',$request->id)
                                                    ->where('comment_type', $external_comment_type_id )
                                                    ->where('request_id', $requestRecord->id)
                                                    ->orderBy('id', 'DESC')
                                                    ->first();
            $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                       ?  $external_comment_query->comments
                                       : '';
            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'processingdeb' => $processingdeb,
                'external_global_comment' => $external_global_comment,
                'directors' =>$directors,
                'secs' => $secs,
                'sec_firms' =>$sec_firms, 
                'signing_party_designation' => $requestRecord->signing_party_designation,
                'signed_party_id' =>  $requestRecord->signed_party_id
            ], 200);
        }
    }
    
    // Submit company debentures details...
    public function submitCompanyDebentures(Request $request){

        if(!$request->comId){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
            ], 200);
        }

        if((($request->type)=="new") || (($request->type)=="newbuthaspreviousrecord")){

                $company_id = $request->comId;
                $user = User::where('email', $request->input('email'))->first();
                $debentures = $request->debenture_array;

                $CR = new CompanyChangeRequestItem();
                $CR->company_id = $company_id;
                $CR->request_by = $user->id;
                $CR->request_type = $this->settings('COMPANY_DEBENTURES','key')->id;
                $CR->status = $this->settings('COMPANY_DEBENTURES_PROCESSING','key')->id;
                $CR->signing_party_designation =  $request->input('signing_party_designation');
                $CR->signed_party_id =  $request->input('signed_party_id');
                $CR->save();

                $companychangerequestId = $CR->id;

                if(isset($debentures) && is_array($debentures) && count($debentures)) {
                    foreach($debentures as $obj ) {
                        $newdebenture = new CompanyDebentures();
                        $newdebenture->company_id = $company_id;
                        $newdebenture->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                        $newdebenture->total_amount_secured = $obj['totalamountsecured'];
                        $newdebenture->series = $obj['series'];
                        $newdebenture->amount = $obj['amount'];
                        $newdebenture->description = $obj['description'];
                        $newdebenture->name_of_trustees = $obj['nameoftrustees'];
                        $newdebenture->date_of_covering_dead= $obj['dateofcoveringdead'];
                        $newdebenture->date_of_resolution = $obj['dateofresolution'];
                        $newdebenture->date_of_issue = $obj['dateofissue'];
                        $newdebenture->save();

                        $CDC = new CompanyItemChange();
                        $CDC->request_id = $companychangerequestId;
                        $CDC->changes_type = $this->settings('ADD','key')->id;
                        $CDC->item_id = $newdebenture->id;
                        $CDC->item_table_type = $this->settings('COMPANY_DEBENTURES','key')->id;
                        $CDC->save();
                    }
                }

                $update_compnay_updated_at = array(
                    'updated_at' => date('Y-m-d H:i:s', time())
                );
                Company::where('id', $request->comId)
                ->update($update_compnay_updated_at);

                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' =>true,
                    'companychangerequestId' => $companychangerequestId,
                    
                ], 200);

        } else if(($request->type)=="processing"){

            $company_id = $request->comId;
            $user = User::where('email', $request->input('email'))->first();
            $debentures = $request->debenture_array;
            $reqId = $request->reqId;
            
            //remove all company debentures record first
            CompanyDebentures::leftJoin('company_item_changes','company_debentures.id', '=', 'company_item_changes.item_id')
                    ->where('company_item_changes.request_id', $reqId)->delete();

            //remove company member change records second
            CompanyItemChange::where('request_id', $reqId)->delete();

            //then add company debentures record and company member change record
            if(isset($debentures) && is_array($debentures) && count($debentures)) {
                foreach($debentures as $obj ) {
                    $newdebenture = new CompanyDebentures();
                    $newdebenture->company_id = $company_id;
                    $newdebenture->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                    $newdebenture->total_amount_secured = $obj['totalamountsecured'];
                    $newdebenture->series = $obj['series'];
                    $newdebenture->amount = $obj['amount'];
                    $newdebenture->description = $obj['description'];
                    $newdebenture->name_of_trustees = $obj['nameoftrustees'];
                    $newdebenture->date_of_covering_dead= $obj['dateofcoveringdead'];
                    $newdebenture->date_of_resolution = $obj['dateofresolution'];
                    $newdebenture->date_of_issue = $obj['dateofissue'];
                    $newdebenture->save();
                    
                    $CDC = new CompanyItemChange();
                    $CDC->request_id = $reqId;
                    $CDC->changes_type = $this->settings('ADD','key')->id;
                    $CDC->item_id = $newdebenture->id;
                    $CDC->item_table_type = $this->settings('COMPANY_DEBENTURES','key')->id;
                    $CDC->save();
                }
            }

            $update_request_arr = array(
                'signing_party_designation' => $request->input('signing_party_designation'),
                'signed_party_id' =>  $request->input('signed_party_id')
    
            );
            $update = CompanyChangeRequestItem::where('id', $reqId)
             ->update($update_request_arr);

            $update_compnay_updated_at = array(
                'updated_at' => date('Y-m-d H:i:s', time())
            );
            Company::where('id', $request->comId)
            ->update($update_compnay_updated_at);

            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true,
                
            ], 200);

        }  else if(($request->type)=="resubmit"){
            $company_id = $request->comId;
            $user = User::where('email', $request->input('email'))->first();
            $debentures = $request->debenture_array;
            $reqId = $request->reqId;


            if(isset($debentures) && is_array($debentures) && count($debentures)) {
                foreach($debentures as $obj ) {

                    CompanyDebentures::where('id', $obj['id'])
                        ->update(['total_amount_secured' => $obj['totalamountsecured'],
                        'series' => $obj['series'],
                        'amount' => $obj['amount'],
                        'description' => $obj['description'],
                        'name_of_trustees' => $obj['nameoftrustees'],
                        'date_of_covering_dead' => $obj['dateofcoveringdead'],
                        'date_of_resolution' => $obj['dateofresolution'],
                        'date_of_issue' => $obj['dateofissue']
                        ]);
                }
            }

            $update_request_arr = array(
                'signing_party_designation' => $request->input('signing_party_designation'),
                'signed_party_id' =>  $request->input('signed_party_id')
    
            );
            $update = CompanyChangeRequestItem::where('id', $reqId)
             ->update($update_request_arr);

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
    }

    // Form 10A download...
    public function generate_App_pdf(Request $request) {

        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();

        $comId = $request->input('comId');

        $company = Company::where('id',$comId)->first();

        $companyRegNo = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['company_certificate.registration_no as registration_no']);

        $regNo =   $companyRegNo[0]['registration_no'];
        
        $debenturelist=array();

        $requestRecord = CompanyChangeRequestItem::where('company_id',$comId)
                                    ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                                    ->where('request_by', $user->id)
                                    ->where(function ($query) {
                                        $query->where('status', '=', $this->settings('COMPANY_DEBENTURES_PROCESSING','key')->id)
                                            ->orWhere('status', '=', $this->settings('COMPANY_DEBENTURES_REQUEST_TO_RESUBMIT','key')->id);
                                    })
                                    ->orderBy('company_change_requests.id','DESC')
                                    ->first();
        $debentures = CompanyDebentures::leftJoin('company_item_changes','company_debentures.id','=','company_item_changes.item_id')
                                    ->where('company_item_changes.request_id',$requestRecord->id)
                                    ->where('company_item_changes.changes_type',$this->settings('ADD','key')->id)
                                    ->where('company_debentures.status', $this->settings('COMMON_STATUS_PENDING','key')->id)
                                    ->get();

        foreach($debentures as $debenture)
        {   
            $idebenture = array();
            $idebenture['total_amount_secured'] = $debenture->total_amount_secured;
            $idebenture['date_of_issue'] = $debenture->date_of_issue;
            $idebenture['amount'] = $debenture->amount;
            $idebenture['date_of_resolution'] = $debenture->date_of_resolution;
            $idebenture['date_of_covering_dead'] = $debenture->date_of_covering_dead;
            $idebenture['description'] = $debenture->description;
            $idebenture['name_of_trustees'] = $debenture->name_of_trustees;
            array_push($debenturelist,$idebenture);
        }

        $todayDate = date("Y-m-d");

        $day = date('d', strtotime($todayDate));
        $month = date('m', strtotime($todayDate));
        $year = date('Y', strtotime($todayDate));


      
      $signing_party_name = '';
      if($requestRecord->signing_party_designation != '') {
          if($requestRecord->signing_party_designation == 'Secretary Firm') {
              $signing_party_info = CompanyFirms::where('id' , $requestRecord->signed_party_id)->first();
              $signing_party_name = $signing_party_info->name;
          } else {
            $signing_party_info = CompanyMember::where('id' , $requestRecord->signed_party_id)->first();
            $signing_party_name = $signing_party_info->first_name.' '. $signing_party_info->last_name;
          }
      }

                    
        
        $fieldset = array(
                
            'comName' => ($company->postfix) ? $company->name .' '.$company->postfix : $company->name, 
            'comReg' => $regNo,
            
            'debenture'=>$debenturelist,

            'day' => $day, 
            'month' => $month, 
            'year' => $year,
            'first_name' => $people->first_name,
            'last_name' => $people->last_name,
            'telephone' => $people->telephone,
            'mobile' => $people->mobile,
            'email' => $people->email,

            'signing_party_designation' => ucwords($requestRecord->signing_party_designation),
            'signing_party_name' => $signing_party_name
            
        );

        $pdf = PDF::loadView('debenture-forms/form-10A',$fieldset);
        $pdf->stream('form-10A.pdf', array("Attachment" => true));
  
    }

    //for upload issue of debentures pdf...
    public function issueofDebentureUploadPdf(Request $request){

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

        $requestRecord = CompanyChangeRequestItem::where('company_id',$comId)
                            ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                            ->where('status', $this->settings('COMPANY_DEBENTURES_PROCESSING','key')->id)
                            ->orWhere('status', $this->settings('COMPANY_DEBENTURES_REQUEST_TO_RESUBMIT','key')->id)
                            ->orderBy('company_change_requests.id','DESC')
                            ->first();
        $docId;
        if($docType=='applicationUpload'){
            $docIdArray = Documents::where('key','ISSUE_OF_DEBENTURES_FORM10A')->select('id')->first();
            $docId = $docIdArray->id;
        }else if($docType=='aditionalDocumentsUpload'){
            $docIdArray = Documents::where('key','ISSUE_OF_DEBENTURES_ADDITIONAL_DOCUMENT')->select('id')->first();
            $docId = $docIdArray->id;
        }

        

        $debentureDoc = new CompanyDocuments;
        $debentureDoc->document_id = $docId;
        $debentureDoc->company_id = $comId;
        $debentureDoc->name = $pdfName;
        $debentureDoc->file_token = $token;
        $debentureDoc->path = $path;
        $debentureDoc->change_id = $requestRecord->id;
        $debentureDoc->file_description = $description;
        $debentureDoc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $debentureDoc->save();
        
        $debentureDocId = $debentureDoc->id;

        return response()->json([
            'message' => 'File uploaded now successfully.',
            'status' =>true,
            'name' =>basename($path),
            'doctype' =>$docType,
            'docid' =>$debentureDocId, // for delete pdf...
            'token' =>$token,
            'pdfname' =>$pdfName,
            'file_description' =>$description,
            'docArray' => $docId
            ], 200);

        }

    }

    
    // Updated file upload ...
    public function issueofdebenturesUploadUpdatedPdf(Request $request){

        if(isset($request)){
    
        $fileName =  uniqid().'.pdf';
        $token = md5(uniqid());
    
        $comId = $request->comId;
        $debenturesDocId = $request->docId;
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
            'docid' =>$debenturesDocId, // for delete pdf...
            'token' =>$token,
            'pdfname' =>$pdfName
            ], 200);
    
        }
    }

    // to delete pdfs
    function deleteIssueofDebenturesPdf(Request $request){
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

    // to delete updated uploaded issue-of-debentures pdf files...
   function deleteUpdatedIssueofDebenturesPdf(Request $request){
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
            elseif($type =='additionalUpload'){
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
                // ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
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

    // for load issue of debentures uploaded files...
    public function issueofdebenturesFile(Request $request){
        if(isset($request)){
            $type = $request->type;
            if($type == 'submit'){

                $comId = $request->comId;

                $requestRecord = CompanyChangeRequestItem::where('company_id',$comId)
                            ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                            ->where('status', $this->settings('COMPANY_DEBENTURES_PROCESSING','key')->id)
                            ->orderBy('company_change_requests.id','DESC')
                            ->first();

                $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                        ->where('company_documents.company_id',$comId)
                                        ->where('company_documents.change_id',$requestRecord->id) 
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

            }elseif($type == 'resubmit'){

                $comId = $request->comId;
                $requestRecord = CompanyChangeRequestItem::where('company_id',$comId)
                            ->where('request_type', $this->settings('COMPANY_DEBENTURES','key')->id)
                            ->where('status', $this->settings('COMPANY_DEBENTURES_REQUEST_TO_RESUBMIT','key')->id)
                            ->orderBy('company_change_requests.id','DESC')
                            ->first();

                $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                        ->leftJoin('company_document_status', function ($join) {
                                                $join->on('company_documents.id', '=', 'company_document_status.company_document_id')
                                                    ->where('company_document_status.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);})
                                                    ->leftJoin('settings','company_documents.status','=','settings.id')
                                        ->where('company_documents.company_id',$comId)
                                        ->where('company_documents.change_id',$requestRecord->id)
                                        ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                        ->get(['company_documents.id','company_documents.name','company_documents.file_token',
                                                'documents.key as docKey','documents.name as docname',
                                                'company_document_status.company_document_id as company_document_id','company_documents.file_description','company_document_status.comments as comments',
                                                'settings.value as value','settings.key as setKey']);
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

    public function issueofdebenturesReSubmit (Request $request){

        CompanyChangeRequestItem::where('id', $request->reqId)
        ->update(['status' => $this->settings('COMPANY_CHANGE_RESUBMITTED','key')->id]);

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
        ], 200);
    }

}
