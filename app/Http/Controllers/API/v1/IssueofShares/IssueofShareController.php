<?php

namespace App\Http\Controllers\API\v1\IssueofShares;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Address;
use App\Company;
use App\CompanyChangeRequestItem;
use App\CompanyItemChange;
use App\SharesDetails;
use App\ChangeAddress;
use App\ShareholdersDetails;
use App\ShareClasses;
use App\Setting;
use App\User;
use App\People;
use App\Documents;
use App\CompanyDocuments;
use App\Share;
use App\ShareGroup;
use App\CompanyMember;
use App\CompanyFirms;
use App\CompanyStatus;
use App\Http\Helper\_helper;
use PDF;
use Storage;

// CSV upload
use App;
use URL;
// csv upload

class IssueofShareController extends Controller
{
    use _helper;

    // LoadCompanyName using company id number...
    public function loadCompanyName(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $update_compnay_updated_at = array(
            'updated_at' => date('Y-m-d H:i:s', time())
        );
        Company::where('id', $request->id)
        ->update($update_compnay_updated_at);

        $type = $request->type;
        $external_global_comment = '';

        if($type == 'submit'){
            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                    ->get(['companies.id','companies.name','company_certificate.registration_no as registration_no']);
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'company'     => $company                
                            )
            ], 200); 


        }
        elseif($type == 'resubmit'){

            $changeReqID = $request->changeReqID;

            
            $company = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$request->id)
                                   ->get(['companies.id','companies.name','company_certificate.registration_no as registration_no']);


            $processingshares = ShareClasses::leftJoin('company_item_changes','share_classes.id','=','company_item_changes.item_id')
                                    ->where('company_item_changes.request_id',$changeReqID)
                                    ->where('share_classes.status', $this->settings('COMMON_STATUS_PENDING','key')->id)
                                    ->get(['share_classes.id','share_classes.share_class as typeofshare','share_classes.no_of_shares as issuedshares','share_classes.issue_type_as_cash as cashapplicability',
                                            'share_classes.issue_type_as_non_cash as noncashapplicability','share_classes.share_value as consideration','share_classes.shares_issued_for_cash as noofsharesascash',
                                            'share_classes.shares_issued_for_non_cash as noofsharesasnoncash','share_classes.share_consideration as considerationotherthancashtext','share_classes.share_consideration_value_paid as considerationotherthancash','share_classes.date_of_issue as dateofissue' ]);

            
            $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
      
            $external_comment_query = CompanyStatus::where('company_id',$request->id)
                                                    ->where('comment_type', $external_comment_type_id )
                                                    ->where('request_id', $changeReqID)
                                                    ->orderBy('id', 'DESC')
                                                    ->first();
            $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                                                       ?  $external_comment_query->comments
                                                                       : '';
            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'data'   => array(
                                'company'     => $company,
                                'processingshares'     => $processingshares,
                                'external_global_comment'=> $external_global_comment
                                         
                            )
            ], 200);
        }
    }

    // LoadCompanyName using company id number...
    public function loadProcessingList(Request $request){

        if(!$request->id){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $type = $request->type;
        $changeReqID = $request->changeReqID;

        if($type == 'submit'){
            $processingshares = ShareClasses::leftJoin('company_item_changes','share_classes.id','=','company_item_changes.item_id')
                                    ->where('company_item_changes.request_id',$request->changeReqID)
                                    ->where('share_classes.status', $this->settings('COMMON_STATUS_PENDING','key')->id)
                                    ->get(['share_classes.id','share_classes.share_class as typeofshare','share_classes.no_of_shares as issuedshares','share_classes.issue_type_as_cash as cashapplicability',
                                            'share_classes.issue_type_as_non_cash as noncashapplicability','share_classes.share_value as consideration','share_classes.shares_issued_for_cash as noofsharesascash',
                                            'share_classes.shares_issued_for_non_cash as noofsharesasnoncash','share_classes.share_consideration_value_paid as considerationotherthancash','share_classes.share_consideration as considerationotherthancashtext','share_classes.date_of_issue as dateofissue' ]);
                                            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'processingRecord'     => $processingshares
                
            ], 200); 


        }
    }
    
    // Submit company shares details...
    public function submitCompanyShares(Request $request){

        if(!$request->comId){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
            ], 200);
        }


        if(($request->type)=="COMPANY_ISSUE_OF_SHARES"){

            if(!$request->reqId){
                $user = User::where('email', $request->input('email'))->first();

                $CR = new CompanyChangeRequestItem();
                $CR->company_id = $request->input('comId');
                $CR->request_by = $user->people_id;
                $CR->request_type = $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
                $CR->status = $this->settings('COMPANY_CHANGE_PROCESSING','key')->id;
                $CR->save();

                $companychangerequestId = $CR->id;

                $shares = $request->newSharesRecords;

                //then add company shares record and company member change record
                if(isset($shares) && is_array($shares) && count($shares)) {
                    foreach($shares as $obj ) {
                        $newshare = new ShareClasses();
                        $newshare->company_id = $request->input('comId');
                        $newshare->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                        $newshare->share_class = $obj['typeofshare'];
                        $newshare->no_of_shares = $obj['issuedshares'];
                        $newshare->issue_type_as_cash = $obj['cashapplicability'];
                        $newshare->issue_type_as_non_cash = $obj['noncashapplicability'];
                        $newshare->share_value = $obj['consideration'];
                        $newshare->share_consideration_value_paid= null;
                        $newshare->shares_issued_for_cash = $obj['noofsharesascash'];
                        $newshare->shares_issued_for_non_cash = $obj['noofsharesasnoncash'];
                        $newshare->shares_called_on = null;
                        $newshare->share_consideration = $obj['considerationotherthancashtext'];
                        $newshare->share_consideration_value_paid = $obj['considerationotherthancash'];
                        $newshare->date_of_issue = $obj['dateofissue'];
                        $newshare->save();
                        
                        $CDC = new CompanyItemChange();
                        $CDC->request_id = $companychangerequestId;
                        $CDC->changes_type = $this->settings('ADD','key')->id;
                        $CDC->item_id = $newshare->id;
                        $CDC->item_table_type = $this->settings('COMPANY_DEBENTURES','key')->id;
                        $CDC->save();
                    }
                }

                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' =>true,
                    'companychangerequestId' => $companychangerequestId,
                    'type' => 'new'
                    
                ], 200);
            }else{

            $company_id = $request->comId;
            $user = User::where('email', $request->input('email'))->first();
            $shares = $request->newSharesRecords;
            $reqId = $request->reqId;
            
            //remove all company shares record first
            ShareClasses::leftJoin('company_item_changes','share_classes.id', '=', 'company_item_changes.item_id')
                    ->where('company_item_changes.request_id', $reqId)->delete();

            //remove company member change records second
            CompanyItemChange::where('request_id', $reqId)->delete();

            //then add company shares record and company member change record
            if(isset($shares) && is_array($shares) && count($shares)) {
                foreach($shares as $obj ) {
                    $newshare = new ShareClasses();
                    $newshare->company_id = $company_id;
                    $newshare->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                    $newshare->share_class = $obj['typeofshare'];
                    $newshare->no_of_shares = $obj['issuedshares'];
                    $newshare->issue_type_as_cash = $obj['cashapplicability'];
                    $newshare->issue_type_as_non_cash = $obj['noncashapplicability'];
                    $newshare->share_value = $obj['consideration'];
                    $newshare->share_consideration_value_paid= null;
                    $newshare->shares_issued_for_cash = $obj['noofsharesascash'];
                    $newshare->shares_issued_for_non_cash = $obj['noofsharesasnoncash'];
                    $newshare->shares_called_on = null;
                    $newshare->share_consideration = $obj['considerationotherthancashtext'];
                    $newshare->share_consideration_value_paid = $obj['considerationotherthancash'];
                    $newshare->date_of_issue = $obj['dateofissue'];
                    $newshare->save();
                    
                    $CDC = new CompanyItemChange();
                    $CDC->request_id = $reqId;
                    $CDC->changes_type = $this->settings('ADD','key')->id;
                    $CDC->item_id = $newshare->id;
                    $CDC->item_table_type = $this->settings('COMPANY_DEBENTURES','key')->id;
                    $CDC->save();
                }
            }

            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true,
                'companychangerequestId' => $reqId,
                'type' => 'old'
                
            ], 200);
            }         
        }
    }

    // Resubmit Company Shares data...
    public function resubmitCompanyShares(Request $request){

        if(!$request->comId){
            return response()->json([
                'message' => 'We can \'t find a Company.',
                'status' =>false,
            ], 200);
        }

            $company_id = $request->comId;
            $user = User::where('email', $request->input('email'))->first();
            $shares = $request->newSharesRecords;
            $reqId = $request->changeReqID;

            if(isset($shares) && is_array($shares) && count($shares)) {
                foreach($shares as $obj ) {

                    ShareClasses::where('id', $obj['id'])
                        ->update(['share_class' => $obj['typeofshare'],
                        'no_of_shares' => $obj['issuedshares'],
                        'issue_type_as_cash' => $obj['cashapplicability'],
                        'issue_type_as_non_cash' => $obj['noncashapplicability'],
                        'share_value' => $obj['consideration'],
                        'share_consideration_value_paid' => $obj['considerationotherthancash'],
                        'shares_issued_for_cash' => $obj['noofsharesascash'],
                        'shares_issued_for_non_cash' => $obj['noofsharesasnoncash'],
                        'share_consideration' => $obj['considerationotherthancashtext'],
                        'date_of_issue' => $obj['dateofissue']
                        ]);
                }
            }


        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
        ], 200);
    }

    // For load availble company shares types... 
    public function getSharesTypes()
    {
        return $this->settings('SHARE_TYPES');
    }

    // For load dummy data and sample excell files...
    public function getTwoCSVs()
    {   
        $csvs = array();
        $csvs['dummy_data'] = asset('other/shareholder-details-dummy-data.csv');
        $csvs['sample_format'] = asset('other/shareholder-details-sample-format.xlsx');
        
        if($csvs){            
            return response()->json([
                'message' => 'Two CSVs load successfully!!!',
                'status' =>true,
                'data' => $csvs
                
            ], 200);            
        }else{
            return response()->json([
                'message' => 'We can \'t find a CSVs.',
                'status' =>false,
            ], 200);
        }
    
    }

    public function uploadShareholderByCSV(Request $request){
        $real_file_name = $request->fileRealName;

        $loginUserEmail = $this->clearEmail($request->loginUserEmail);
        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->people_id;
        //$company_id = $request->comId;
        $changeReqID = $request->changeReqID;

        $company_id = $request->companyId;
        //$request_id = $this->valid_annual_return_request_operation($company_id);
        //$company_info = Company::where('id',$company_id)->first();
        //$companyType = $this->settings($company_info->type_id,'id');
      
        if(!$changeReqID) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

     
        $size = $request->file('uploadFile')->getClientSize() ;
        $ext = $request->file('uploadFile')->getClientMimeType();

        if('application/vnd.ms-excel' !== $ext  &&  'application/octet-stream' !== $ext){

         return response()->json([
             'message' => 'Please upload your files with csv format.',
             'status' =>false,
             'error'  => 'yes',
             'uploadedExt' => $ext

         ], 200);
        }

        if( $size > 1024 * 1024 * 4) {

         return response()->json([
             'message' => 'File size should be less than 4 MB.',
             'status' =>false,
             'error'  => 'yes'
  
         ], 200);
        }

        $directory = "annual-return-shareholders/$company_id";
    //    @ Storage::deleteDirectory($directory);
        $path = Storage::putFile($directory, $request->file('uploadFile'));
 
        $file_path = str_replace('public','',url('/')).Storage::url("app/$path");


     
        if (($handle = fopen($file_path, "r")) !== FALSE) {


            $row = 1;


            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if( !isset($data[0])) {
                   break;
                }
                $num = count($data);
               
                $address = new Address;
                $forAddress = new Address;
        
                $new_address_id= null;
                $new_forAddressId = null;

                if( isset($data[0]) &&  $data[0] !== 'yes' ){ // natural person

                  //  if($data[1] !== 'no') { // is sri lankan

                        $address->province = $data[9];
                        $address->district =  ($data[1] !== 'no') ? $data[10] : null;
                        $address->city =  $data[11];
                        $address->address1 = $data[12];
                        $address->address2 =  $data[13];
                        $address->postcode =  $data[14];
                        $address->country =  ($data[1] !== 'no') ? 'Sri Lanka' :  $data[15] ;
                        $address->save();
                        $new_address_id = $address->id;

                } else {

                        $address->province =  $data[9];
                        $address->district =   $data[10];
                        $address->city =  $data[11];
                        $address->address1 =  $data[12];
                        $address->address2 =  $data[13];
                        $address->postcode = $data[14];
                        $address->country = ($data[1] !== 'no') ? 'Sri Lanka' :  $data[15] ;
                        $address->save();
                        $new_address_id = $address->id;
    

                }
            
    
                if ( $data[0] !== 'yes' ) {
        
                        $newSh = new CompanyMember;
                        $newSh->company_id = $company_id;
                        $newSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $newSh->is_srilankan =  ($data[1] !== 'no') ?  'yes' : 'no';
                        $newSh->title = $data[4];
                        $newSh->first_name = $data[5];
                        $newSh->last_name = $data[6];
                        $newSh->nic = ($data[1] !== 'no') ? strtoupper($data[2]) : NULL;
                        $newSh->passport_no = ($data[1] === 'no') ? strtoupper($data[2]) : NULL;
                        $newSh->address_id = $new_address_id;
                        $newSh->foreign_address_id = $new_forAddressId;
                        $newSh->passport_issued_country = isset($data[3]) ?  $data[3] :$data[14];
                        $newSh->telephone = isset($data[17]) && strlen($data[17]) < 10 ? '0'.$data[17] : $data[17];
                        $newSh->mobile = isset($data[16]) && strlen($data[16]) < 10 ? '0'.$data[16] : $data[16];
                        $newSh->email = $data[18];
                        $newSh->occupation = $data[20];
                        $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
                        $newSh->status =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
                        $newSh->save();
                        $shareHolderId =  $newSh->id;
        
                        $change = new CompanyItemChange;
                        $change->request_id = $changeReqID;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $shareHolderId;
                        $change->item_table_type = $this->settings('COMPANY_MEMBERS','key')->id;
                        $change->save();
                        $change_id = $change->id;
        
                        
        
                    
                    } else {
        
                        $newSh = new CompanyFirms;
                        $newSh->registration_no = $data[8];
                        $newSh->name =  $data[7];
                        $newSh->email = $data[18];
                        $newSh->mobile = strlen($data[16]) < 10 ? '0'.$data[16] : $data[16];
                        $newSh->date_of_appointment = date('Y-m-d',strtotime($data[19]) );
                        $newSh->company_id = $company_id;
                        $newSh->address_id = $new_address_id;
                        $newSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                        $newSh->is_srilankan =  ($data[1] !== 'no') ?  'yes' : 'no';
                        $newSh->status =  $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id;
                        $newSh->save();
                        $shareHolderId = $newSh->id;
        
                        $change = new CompanyItemChange;
                        $change->request_id = $changeReqID;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $shareHolderId;
                        $change->item_table_type = $this->settings('COMPANY_MEMBER_FIRMS','key')->id;
                        $change->save();
                        $change_id = $change->id;
        
        

                    }

                    $shareholder_share = new Share;

                    $shareholder_sharegroup = new ShareGroup;
                    $shareholder_sharegroup->type ='single_share';
                    $shareholder_sharegroup->name ='single_share_no_name';
                    $shareholder_sharegroup->current_shares = isset( $data[21]) ? floatval( $data[21] ) : 0 ;
                    $shareholder_sharegroup->new_shares = isset( $data[22]) ? floatval( $data[22] ) : 0 ;
                    $shareholder_sharegroup->no_of_shares = ((isset( $data[21]) ? floatval( $data[21] ) : 0) + (isset( $data[22]) ? floatval( $data[22] ) : 0));
                    $shareholder_sharegroup->company_id = $company_id;
                    $shareholder_sharegroup->status = $this->settings('COMMON_STATUS_PENDING','key')->id;
                    $shareholder_sharegroup->save();
                    $shareholder_sharegroupID = $shareholder_sharegroup->id;

                    if ( isset($data[0]) &&  $data[0] !== 'yes' ) {
                        $shareholder_share->company_member_id = $shareHolderId;
                    }else{
                          
                        $shareholder_share->company_firm_id = $shareHolderId;
                    }
                    $shareholder_share->group_id = $shareholder_sharegroupID;
                    $shareholder_share->save();

            }
        fclose($handle);
        }

       

        return response()->json([
            'message' => 'Bulk Shareholders added.',
            'status' =>true,
        ], 200);
    }

    // Load upload excell shareholders record count...
    function excellDataLoad(Request $request){
        if(isset($request)){

            $comId = $request->input('comId');
            $changeReqID = $request->input('changeReqID');

            // count company_members records
            $indCount = Share::leftJoin('company_members','company_shares.company_member_id','=','company_members.id')
            ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
            ->where('company_members.company_id',$comId)
            ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
            ->where('company_members.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
            ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                ->count();
            
            // count company_member_firms records
            $firmCount = Share::leftJoin('company_member_firms','company_shares.company_firm_id','=','company_member_firms.id')
            ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
            ->where('company_member_firms.company_id',$comId)
            ->where('company_member_firms.type_id', '=', $this->settings('SHAREHOLDER','key')->id)
            ->where('company_member_firms.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
            ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                ->count();
            
            $totalexitshareholdersrecords = $indCount + $firmCount;

            $companyMembersarray = array();
            $companyFirmsarray = array();

           

                if($indCount){
                    $companyMembersarray = Share::leftJoin('company_members','company_shares.company_member_id','=','company_members.id')
                        ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
                        ->where('company_members.company_id',$comId)
                        ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
                        ->where('company_members.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                        ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                        ->get(['company_members.first_name as fname','company_members.nic as nic','company_members.passport_no as passport','company_members.last_name as lname', 'company_share_groups.current_shares as currentshares', 
                                'company_share_groups.new_shares as newshares', 'company_share_groups.no_of_shares as totalshares']);
                }
                if($firmCount){
                    $companyFirmsarray = Share::leftJoin('company_member_firms','company_shares.company_firm_id','=','company_member_firms.id')
                        ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
                        ->where('company_member_firms.company_id',$comId)
                        ->where('company_member_firms.type_id', '=', $this->settings('SHAREHOLDER','key')->id)
                        ->where('company_member_firms.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                        ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                        ->get(['company_member_firms.name as name','company_member_firms.registration_no as regno', 'company_share_groups.current_shares as currentshares', 
                                'company_share_groups.new_shares as newshares', 'company_share_groups.no_of_shares as totalshares']);
                }

            
            
            if(isset($totalexitshareholdersrecords)){
                return response()->json([
                    'data' => $totalexitshareholdersrecords,
                    'induvidual' => $companyMembersarray,
                    'indCount' => $indCount,
                    'firm' => $companyFirmsarray,
                    'firmCount' => $firmCount,
                    'status' =>true,
                    'message' => 'totalexist shareholders count succesfully',
                ], 200);
            }
        
        }else{
            return response()->json([
                'status' =>false,
            ], 200);
        }
    }

    // Reset excel data ftom database...
    function resetShareholdersExcellData(Request $request){
        if(isset($request)){

            $comId = $request->input('comId');
            $changeReqID = $request->input('changeReqID');

            // Delete Address records from company_members tabele
            $companyMembersIds = Share::leftJoin('company_members','company_shares.company_member_id','=','company_members.id')
                        ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
                        ->where('company_members.company_id',$comId)
                        ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
                        ->where('company_members.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                        ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                        ->get(['company_members.address_id as addressID','company_members.id as memberID','company_share_groups.id as sharegroupID','company_shares.id as sharesID']);
            
            if($companyMembersIds){
                // Delete address records
                foreach($companyMembersIds as $obj){
                    Address::where('id', $obj['addressID'])
                        ->delete();
                }

                // Delete Company Member records
                foreach($companyMembersIds as $obj){
                    CompanyMember::where('id', $obj['memberID'])
                        ->delete();
                }

                // Delete company_shares records
                foreach($companyMembersIds as $obj){
                    Share::where('id', $obj['sharesID'])
                        ->delete();
                }

                // Delete company_share_groups records
                foreach($companyMembersIds as $obj){
                    ShareGroup::where('id', $obj['sharegroupID'])
                        ->delete();
                }
            }


            // Delete Address records from company_member_firms table
            $companyFirmIds = Share::leftJoin('company_member_firms','company_shares.company_firm_id','=','company_member_firms.id')
                        ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
                        ->where('company_member_firms.company_id',$comId)
                        ->where('company_member_firms.type_id', '=', $this->settings('SHAREHOLDER','key')->id)
                        ->where('company_member_firms.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                        ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                        ->get(['company_member_firms.address_id as addressID','company_member_firms.id as firmID','company_share_groups.id as sharegroupID','company_shares.id as sharesID']);
            
            if($companyFirmIds){
                // Delete address records
                foreach($companyFirmIds as $obj){
                    Address::where('id', $obj['addressID'])
                        ->delete();
                }

                // Delete Company Member records
                foreach($companyFirmIds as $obj){
                    CompanyMember::where('id', $obj['firmID'])
                        ->delete();
                }

                // Delete company_shares records
                foreach($companyFirmIds as $obj){
                    Share::where('id', $obj['sharesID'])
                        ->delete();
                }

                // Delete company_share_groups records
                foreach($companyFirmIds as $obj){
                    ShareGroup::where('id', $obj['sharegroupID'])
                        ->delete();
                }
            }

                return response()->json([
                    'status' =>true,
                    'message' => 'shareholders record removed successfully...' 
                ], 200);
            

        }else{
            return response()->json([
                'status' =>false,
            ], 200);
        }
    }

    // Form 6 download...
    public function generate_App_pdf(Request $request) {

        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();

        $comId = $request->input('comId');

        $changeReqID = $request->input('changeReqID');

        $company = Company::where('id',$comId)->first();

        $companyRegNo = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['company_certificate.registration_no as registration_no']);

        $regNo =   $companyRegNo[0]['registration_no'];  

        $shareclass = ShareClasses::leftJoin('company_item_changes','share_classes.id','=','company_item_changes.item_id')
                                        ->where('company_item_changes.request_id',$changeReqID)
                                        ->where('share_classes.status',$this->settings('COMMON_STATUS_PENDING','key')->id)
                                        ->get(['share_classes.date_of_issue as doi','share_classes.no_of_shares as nos',
                                        'share_classes.share_value as sharevalueforcash','share_classes.share_consideration_value_paid as sharevalueforNoncash']);

        $sharesvalueForcash = 0;                                
        foreach($shareclass as $obj ) {
            $sharesvalueForcash = $sharesvalueForcash + $obj['sharevalueforcash'];
        }

        $sharesvalueForNoncash = 0;                                
        foreach($shareclass as $obj ) {
            $sharesvalueForNoncash = $sharesvalueForNoncash + $obj['sharevalueforNoncash'];
        }

        $sharevalue = $sharesvalueForcash + $sharesvalueForNoncash;

        $noOfshares = 0;                                
        foreach($shareclass as $obj ) {
            $noOfshares = $noOfshares + $obj['nos'];
        }

        $date_of_issue = $shareclass[0]['doi'];



        // Shares consideration prior to this issue
        $shareclassActiveCount = ShareClasses::where('share_classes.company_id',$comId)
                                        ->where('share_classes.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                        ->count();
        
        if($shareclassActiveCount){
            $shareclassActive = ShareClasses::where('share_classes.company_id',$comId)
                                        ->where('share_classes.status',$this->settings('COMMON_STATUS_ACTIVE','key')->id)
                                        ->get(['share_classes.share_value as sharevalueforcash','share_classes.share_consideration_value_paid as sharevalueforNoncash']);
            
            if($shareclassActive){
                $sharesvalueForcashActive = 0;                                
                foreach($shareclassActive as $obj ) {
                    $sharesvalueForcashActive = $sharesvalueForcashActive + $obj['sharevalueforcash'];
                }

                $sharesvalueForNoncashActive = 0;                                
                foreach($shareclassActive as $obj ) {
                    $sharesvalueForNoncashActive = $sharesvalueForNoncashActive + $obj['sharevalueforNoncash'];
                }

                $sharevalueActiveValue = $sharesvalueForcashActive + $sharesvalueForNoncashActive;
            }
        }else{
            $sharevalueActiveValue = 0;
        }

        
        



        // $itemChange = CompanyItemChange::where('request_id',$changeReqID)->first();
        // $itemChangeID = $itemChange->item_id;
        // $processingRecord = SharesDetails::where('id',$itemChangeID)->first();

        // $stated_capital_after = $processingRecord->consideration + $processingRecord->stated_capital;

        $todayDate = date("Y-m-d");

        $day = date('d', strtotime($todayDate));
        $month = date('m', strtotime($todayDate));
        $year = date('Y', strtotime($todayDate));
                    
        
        $fieldset = array(
                
            'comName' => $company->name, 
            'comReg' => $regNo, 

            'date_of_issue' => $date_of_issue, 
            'number_of_shares' => $noOfshares ,
            'consideration' => $sharevalue,
            'stated_capital_prior' => $sharevalueActiveValue,
            'stated_capital_after' => $sharevalue + $sharevalueActiveValue,
            'day' => $day, 
            'month' => $month, 
            'year' => $year,
            'first_name' => $people->first_name,
            'last_name' => $people->last_name,
            'telephone' => $people->telephone,
            'mobile' => $people->mobile,
            'email' => $people->email,
        );

        $pdf = PDF::loadView('issue-of-shares-forms/form-6',$fieldset);

        $pdf->stream('form-6.pdf');

    }
    
    // Current shareholders details pdf download
    public function generateCurrentShareholdersDetailspdf(Request $request) {

        $user = User::where('email', $request->input('email'))->first();
        $people = People::where('id', $user->people_id)->first();
    
        $comId = $request->input('comId');

        $changeReqID = $request->input('changeReqID');
    
        $company = Company::where('id',$comId)->first();

        $companyRegNo = Company::leftJoin('company_certificate','company_certificate.company_id','=','companies.id')
                                    ->where('companies.id',$comId)
                                   ->get(['company_certificate.registration_no as registration_no']);

        $regNo =   $companyRegNo[0]['registration_no'];

        $companyMembersarray = Share::leftJoin('company_members','company_shares.company_member_id','=','company_members.id')
                        ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
                        ->where('company_members.company_id',$comId)
                        ->where('company_members.designation_type', '=', $this->settings('SHAREHOLDER','key')->id)
                        ->where('company_members.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                        ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                        ->get(['company_members.first_name as fname','company_members.nic as nic','company_members.passport_no as passport','company_members.last_name as lname', 'company_share_groups.current_shares as currentshares', 
                        'company_share_groups.new_shares as newshares', 'company_share_groups.no_of_shares as totalshares']);

        
        $companyFirmsarray = Share::leftJoin('company_member_firms','company_shares.company_firm_id','=','company_member_firms.id')
                        ->leftJoin('company_share_groups','company_shares.group_id','=','company_share_groups.id')
                        ->where('company_member_firms.company_id',$comId)
                        ->where('company_member_firms.type_id', '=', $this->settings('SHAREHOLDER','key')->id)
                        ->where('company_member_firms.status', '=', $this->settings('COMPANY_ISSUE_OF_SHARES','key')->id)
                        ->where('company_share_groups.status', '=', $this->settings('COMMON_STATUS_PENDING','key')->id)
                        ->get(['company_member_firms.name as name','company_member_firms.registration_no as regno', 'company_share_groups.current_shares as currentshares', 
                        'company_share_groups.new_shares as newshares', 'company_share_groups.no_of_shares as totalshares']);
        


        $todayDate = date("Y-m-d");

        $day = date('d', strtotime($todayDate));
        $month = date('m', strtotime($todayDate));
        $year = date('Y', strtotime($todayDate));
                       
        $fieldset = array(
                
               'comName' => $company->name, 
               'comReg' => $regNo, 
               'companyMembersarray' => $companyMembersarray,
               'companyFirmsarray' => $companyFirmsarray,
               'day' => $day, 
               'month' => $month, 
               'year' => $year,
         );
     
        $pdf = PDF::loadView('issue-of-shares-forms/shareholders-details',$fieldset);
    
        $pdf->stream('shareholders-details.pdf');
    
     
    }

    
    //for upload issue of shares pdf...
    public function issueofsharesUploadPdf(Request $request){

        if(isset($request)){

        $fileName =  uniqid().'.pdf'; 
        $token = md5(uniqid());   

        $comId = $request->comId;  
        $docType = $request->docType; 
        $pdfName = $request->filename; 
        $changeReqID = $request->changeReqID;

        $path = 'company/'.$comId;  
        $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp'); 

        $docId;
        if($docType=='applicationUpload'){
            $docIdArray = Documents::where('key','ISSUE_OF_SHARES_FORM6')->select('id')->first();
            $docId = $docIdArray->id;
        }else if($docType=='aditionalDocumentsUpload'){
            $docIdArray = Documents::where('key','ISSUE_OF_SHARES_ADDITIONAL_DOCUMENT')->select('id')->first();
            $docId = $docIdArray->id;
        }

        

        $sharesDoc = new CompanyDocuments;
        $sharesDoc->document_id = $docId;
        $sharesDoc->company_id = $comId;
        $sharesDoc->name = $pdfName;
        $sharesDoc->file_token = $token;
        $sharesDoc->path = $path;
        $sharesDoc->change_id = $changeReqID;
        $sharesDoc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $sharesDoc->save();
        
        $sharesDocId = $sharesDoc->id;

        return response()->json([
            'message' => 'File uploaded now successfully.',
            'status' =>true,
            'name' =>basename($path),
            'doctype' =>$docType,
            'docid' =>$sharesDocId, // for delete pdf...
            'token' =>$token,
            'pdfname' =>$pdfName,
            'docArray' => $docId
            ], 200);

        }

    }

    
    // Updated file upload ...
    public function issueofsharesUploadUpdatedPdf(Request $request){

        if(isset($request)){
    
        $fileName =  uniqid().'.pdf';
        $token = md5(uniqid());
    
        $comId = $request->comId;
        $sharesDocId = $request->docId;
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
            'docid' =>$sharesDocId, // for delete pdf...
            'token' =>$token,
            'pdfname' =>$pdfName
            ], 200);
    
        }
    
    }

    // to delete pdfs
    function deleteIssueofSharesPdf(Request $request){
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

    // to delete updated uploaded issue-of-shares pdf files...
   function deleteUpdatedIssueofSharesPdf(Request $request){
    if(isset($request)){
        $docId = $request->documentId;
        $type = $request->type;
        if($docId){
            if($type =='applicationUpload'){
    
                $document = CompanyDocuments::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);          
            }
            else{
    
                $document = CompanyDocuments::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                CompanyDocuments::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);
            }
    
            
    
        }
        return response()->json([
            'message' => 'File emptied successfully.',
            'status' =>true,
        ], 200);
        }
   }

    // for load issue of shares uploaded files...
    public function issueofsharesFile(Request $request){
        if(isset($request)){
            $type = $request->type;
            if($type == 'submit'){

                $comId = $request->comId;
                $changeReqID = $request->changeReqID;

                $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                        ->where('company_documents.company_id',$comId)
                                        ->where('company_documents.change_id',$changeReqID) 
                                        ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)  
                                        ->get(['company_documents.id','company_documents.name','company_documents.file_token','documents.key as docKey','documents.name as docname']);
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
                $changeReqID = $request->changeReqID;

                $uploadedPdf = CompanyDocuments::leftJoin('documents','company_documents.document_id','=','documents.id')
                                        ->leftJoin('company_document_status', function ($join) {
                                                $join->on('company_documents.id', '=', 'company_document_status.company_document_id')
                                                    ->where('company_document_status.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);})
                                                    ->leftJoin('settings','company_documents.status','=','settings.id')
                                        ->where('company_documents.company_id',$comId)
                                        ->where('company_documents.change_id',$changeReqID)
                                        ->where('company_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                        ->get(['company_documents.id','company_documents.name','company_documents.file_token',
                                                'documents.key as docKey','documents.name as docname',
                                                'company_document_status.company_document_id as company_document_id','company_document_status.comments as comments',
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

    public function issueofsharesReSubmit (Request $request){

        CompanyChangeRequestItem::where('id', $request->changeReqID)
        ->update(['status' => $this->settings('COMPANY_CHANGE_RESUBMITTED','key')->id]);

        return response()->json([
            'message' => 'Sucess!!!',
            'status' =>true,
        ], 200);
    
    
    
    }

}
