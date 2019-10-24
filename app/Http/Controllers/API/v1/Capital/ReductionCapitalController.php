<?php

namespace App\Http\Controllers\API\v1\Capital;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ReductionStatedCapital;
use App\ShareGroup;
use App\Company;
use App\Http\Helper\_helper;
use PDF;
use App\ChangeName;
use App\CompanyMember;
use Auth;
use App\CompanyChangeRequests;
use App\CompanyChangeRequestItem;
use App\CompanyFirms;
use App\CompanyDocumentStatus;
use App\CompanyStatus;

class ReductionCapitalController extends Controller
{
    use _helper;

    public  function index(Request $request)
    {
        if ($request->id) {

          /*  $redData = ReductionStatedCapital::leftJoin('company_share_groups', 'reduction_stated_capital.share_class_id', '=', 'company_share_groups.id')
                ->leftJoin('company_change_requests', 'company_change_requests.id', '=', 'reduction_stated_capital.request_id')
                ->leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                // ->leftJoin('company_statuses', 'company_statuses.request_id', '=', 'reduction_stated_capital.request_id')
                ->where('company_share_groups.company_id', str_replace('"', '', $request->id) )
                // ->where('company_change_requests.status', $this->settings('COMPANY_CHANGE_PROCESSING', 'key')->id)
                ->where('reduction_stated_capital.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                ->select('reduction_stated_capital.id as id', 'name', 'type', 'share_capital_amount', 'reduction_amount', 'reduction_capital_amount', 'reduction_stated_capital.created_at', 'reduction_stated_capital.request_id as request_id', 'settings.key as key', 'settings.value as value', 'reduction_stated_capital.job_id as jobId')
                ->get();
                */

                $redData = ReductionStatedCapital::leftJoin('company_change_requests', 'company_change_requests.id', '=', 'reduction_stated_capital.request_id')
                ->leftJoin('settings', 'settings.id', '=', 'company_change_requests.status')
                // ->leftJoin('company_statuses', 'company_statuses.request_id', '=', 'reduction_stated_capital.request_id')
                // ->where('company_change_requests.status', $this->settings('COMPANY_CHANGE_PROCESSING', 'key')->id)
                ->where('company_change_requests.company_id', str_replace('"', '', $request->id) )
                ->where('reduction_stated_capital.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                ->select('reduction_stated_capital.id as id', 'share_capital_amount', 'reduction_amount', 'reduction_capital_amount', 'reduction_stated_capital.created_at', 'reduction_stated_capital.request_id as request_id', 'settings.key as key', 'settings.value as value', 'reduction_stated_capital.job_id as jobId')
                ->get();
            $data =  array();
            foreach ($redData as $key => $value) {
                $datarec = CompanyDocumentStatus::where('request_id', $value->request_id)->select('comments')->get()->toArray();
                $comments = CompanyStatus::where('request_id', $value->request_id)->select('comments')->get()->toArray();
      
                $red['id'] = $value->id;
                $red['comments'] = $comments;
                $red['doc_comments'] = $datarec;
                $red['name'] = $value->name;
                $red['share_capital_amount'] = $value->share_capital_amount;
                $red['reduction_amount'] =$value->reduction_amount;
                $red['reduction_capital_amount'] =$value->reduction_capital_amount;
                $red['created_at'] = $value->created_at;
                $red['request_id'] = $value->request_id;
                $red['key'] = $value->key;
                $red['value'] = $value->value;
                $red['jobId'] = $value->jobId;
                $data[] = $red;
            }

            return response()->json(['status' => true, 'data' => $data], 200);
        }
        return response()->json(['status' => false, 'data' => []], 200);
    }


    function getPanaltyCharge(Request $request) {

        $company_id = str_replace('"','',$request->company_id);
        $request_id = $request->request_id;

        $record = ReductionStatedCapital::where('request_id', $request_id)->first();

        $resoultion_date = $record->resalution_date;
        $publish_date = $record->publish_date;
        $publish_status = $record->publish_status;
        


        if(!$resoultion_date) {
            return response()->json(['status' => true, 'penalty' => 0, 'pub_penalty'=>0, 'not_pub_penalty' =>0 ], 200);
        }

       // $min_date_gap = 10;
        $min_date_gap = 30;
        $increment_gap_dates = 30;
        $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_8_INITIAL','key')->value );
        $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_8_INCREMENT','key')->value );
        $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_8_MAX','key')->value );
        $pubdate_penalty = floatval( $this->settings('PAYMENT_PENALTY_FORM_8_PENALTY_PUBLISH_DATE','key')->value );

        $min_pub_date_gap = 60;

        $increment_gaps = 0;

        $penalty_value = 0;
        $pub_value = 0;
        $not_pub_penalty_value = 0;

        $res_date = '';
        $pub_date='';
        if( $res_date = strtotime($resoultion_date))  {

            $today = time();
            $date_gap =  intval( ($today - $res_date) / (24*60*60) );

            if($date_gap < $min_date_gap ) {
                $penalty_value =  0;
            } else{

                $increment_gaps = ( $date_gap % $increment_gap_dates == 0 ) ? $date_gap / $increment_gap_dates : intval($date_gap / $increment_gap_dates) + 1;
               
               // $penalty_value  = $penalty_value + $init_panalty;

              //  if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
               //     $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
              //  }
                if($increment_gaps >= 1 ) { // more than or equal 30 days
                    $penalty_value = $penalty_value + $increment_penalty * $increment_gaps;
                }


                $penalty_value =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;

            }

        }

        if( $publish_status == 'Published' && $resoultion_date &&  strtotime($resoultion_date) && $publish_date &&  strtotime($publish_date))   {

           $res_date = strtotime($resoultion_date);
           $pub_date =  strtotime($publish_date);
           $date_gap =  intval( ($pub_date - $res_date) / (24*60*60) );

            if($date_gap>=0 && $date_gap < $min_pub_date_gap ) {
                $pub_value =  $pubdate_penalty;
            } else{
                $pub_value =  0;
            }


        } else {
            $pub_value =  0;
        }


        if( $publish_status == 'Not Published') {
            $not_pub_penalty_value = floatval( $this->settings('PAYMENT_PENALTY_FORM_8_PENALTY_NOT_PUBLISH','key')->value);
        }



        return response()->json(['status' => true, 'penalty' => $penalty_value, 'pub_penalty'=>$pub_value, 'not_pub_penalty' =>$not_pub_penalty_value ], 200);

        
    }


    public function getShareData(Request $request)
    {
        $collection = ShareGroup::where('company_id', str_replace('"', '', $request->id) )
            ->select('id', 'name', 'no_of_shares')
            ->get();
        $collection_count = ShareGroup::where('company_id', str_replace('"', '', $request->id) )
            ->select('id', 'name', 'no_of_shares')
            ->count();

     //  print_r($collection);
        if ($collection_count) {
            return response()->json(['status' => true, 'data' => $collection], 200);
        }
        return response()->json(['status' => false, 'data' => []], 200);
    }

    public function setReduRaw(Request $request)
    {
        $table = ReductionStatedCapital::where('share_class_id', $request->id)
            ->where('status', $this->settings('COMMON_STATUS_DEACTIVE', 'key')->id)
            ->where('build_id',  $request->secId)
            ->first();

        if (!$table) {
            $table = new ReductionStatedCapital();
        }

        $table->share_class_id = $request->id;
        $table->job_id = $request->jobId;
        $table->status = $this->settings('COMMON_STATUS_DEACTIVE', 'key')->id;
        $table->build_id = $request->secId;

        if ($table->save()) {
            return response()->json(['status' => true, 'data' => $table->id], 200);
        } else {
            return response()->json(['status' => false, 'data' => []], 200);
        }
    }


    public function updateReduRaw(Request $request)
    {
        if ($request['data']['selectCapitalid'] != null || $request['data']['selectCapitalid'] != '') {

            $tb = new CompanyChangeRequestItem();
           // $tb->company_id = $request['data']['companyId'];
            $tb->company_id = str_replace('"', '', $request['data']['companyId']);
            $tb->request_by = Auth::guard('api')->user()->id;
            $tb->request_type =  $this->settings('REDUCTION_OF_CAPITAL', 'key')->id;
            $tb->status =  $this->settings('COMPANY_CHANGE_PROCESSING', 'key')->id;
            if ($tb->save()) {
                $table =  ReductionStatedCapital::find($request['data']['selectCapitalid']);
                $table->share_capital_amount = $request['data']['shareCapitalAmount'];
                $table->reduction_amount = $request['data']['reductionAmount'];
                $table->reduction_capital_amount = $request['data']['reductionCapitalAmount'];
                $table->resalution_date =  date('Y-m-d', strtotime($request['data']['resolutionDate']));
                $table->publish_date =  ($request['data']['publishDate']) ? date('Y-m-d', strtotime($request['data']['publishDate'])) : null;
                $table->publish_status = $request['data']['publishState'];
                $table->status = $this->settings('COMMON_STATUS_ACTIVE', 'key')->id;
                $table->request_id = $tb->id;
                if ($table->save()) {
                    return response()->json(['status' => true], 200);
                } else {
                    return response()->json(['status' => false], 200);
                }
            }
        }
        return response()->json(['status' => false, 'data' => []], 200);
    }

    public function continue(Request $request)
    {
        $table =  ReductionStatedCapital::where('job_id', $request->id)->first();
        $table->status = $this->settings('DOCUMENT_REQUESTED', 'key')->id;
        if ($table->save()) {
            return response()->json(['status' => true], 200);
        } else {
            return response()->json(['status' => false], 200);
        }
        return response()->json(['status' => false, 'data' => []], 200);
    }

    public function getForm(Request $request)
    {

        if ($request->data['newRefid'] != null) {

            $arrId = explode("-", $request->data['dirId']);

            $user = $this->getAuthUser();

          /*  $redData = ReductionStatedCapital::leftJoin('company_share_groups', 'company_share_groups.id', '=', 'reduction_stated_capital.share_class_id')
              ->where('job_id', $request->data['newRefid'])->first(); */
            
           /*** */
             $redData = ReductionStatedCapital::where('job_id', $request->data['newRefid'])->first();
            $request_id = $redData->request_id;
            $requestInfo = CompanyChangeRequests::where('id', $request_id)->first();

            $newData = Company::leftJoin('people', 'companies.created_by', '=', 'people.id')
            ->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
            ->where('companies.id', $requestInfo->company_id)->first();

            /** */

            $memberData = array();
            if ($arrId[1] == 0) {
                $member = CompanyMember::leftJoin('settings', 'settings.id', '=', 'company_members.designation_type')
                    ->where('company_members.id', $arrId[0])
                    ->where('company_members.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                    ->select('company_members.id as id', 'company_members.title', 'company_members.first_name', 'company_members.last_name', 'settings.value as designation')
                    ->first();
                if ($member) {
                    $memberData = [
                        'designation' => $member->designation,
                        'name' => $member->title . '' . $member->first_name . ' ' . $member->last_name,
                    ];
                } else {
                    return response()->json(['error' => 'Can\'t access for your request'], 200);
                }
            }
            if ($arrId[1] == 1) {

                $memberfirm = CompanyFirms::where('id',  $arrId[0])
                    ->where('company_member_firms.status', $this->settings('COMMON_STATUS_ACTIVE', 'key')->id)
                    ->select('company_member_firms.id as id', 'company_member_firms.name as name')
                    ->first();

                if ($memberfirm) {
                    $memberData = [
                        'designation' => 'Firm',
                        'name' => $memberfirm->name,
                    ];
                } else {
                    return response()->json(['error' => 'Can\'t access for your request'], 200);
                }
            }

            $addaress = '';
            if ($user->is_srilankan == 'yes') {
                if ($user->address1 != null || !empty($user->address1)) {
                    $addaress =  $user->address1;
                }
                if ($user->address2  != null || !empty($user->address2)) {
                    $addaress .=  $user->address2 . ',';
                }
                if ($user->district  != null || !empty($user->district)) {
                    $addaress .=  $user->district . ',';
                }
                if ($user->city  != null || !empty($user->city)) {
                    $addaress .=  $user->city;
                }
            } else {
                $address = Address::where('id', $user->foreign_address_id)->first();

                if ($address->address1 != null || !empty($address->address1)) {
                    $addaress =  $address->address1;
                }
                if ($address->address2  != null || !empty($address->address2)) {
                    $addaress .=  $address->address2 . ',';
                }
                if ($address->district  != null || !empty($address->district)) {
                    $addaress .=  $address->district . ',';
                }
                if ($address->city  != null || !empty($address->city)) {
                    $addaress .=  $address->city;
                }
            }

            $data = [
                'refId' => $newData->registration_no,
                'CompanyName' => $newData->name . ' ' . $newData->postfix,
                'username' => $user->title . $user->first_name . ' ' . $user->last_name,
                'email' =>  $user->email,
                'telephonenumber' =>  $user->telephone,
                'share_capital_amount' =>  $redData->share_capital_amount,
                'reduction_amount' =>  $redData->reduction_amount,
                'reduction_capital_amount' =>  $redData->reduction_capital_amount,
                'mobile' =>  $user->mobile,
                'address' =>  $addaress,
                'resolution_date' => date('Ymd', strtotime($redData->resalution_date))
            ];
            $data = $data + $memberData;

            $pdf = PDF::loadView('vendor.form.reduction-stated-capital.Form8', $data);
            return $pdf->stream('Form8.pdf');
        } else {
            return response()->json(['error' => 'Can\'t access for your request'], 200);
        }
    }

    public function setResubmit(Request $request)
    {
        if ($request->id) {
            $table = CompanyChangeRequestItem::find($request->id);
            $table->status =  $this->settings('COMPANY_CHANGE_RESUBMITTED', 'key')->id;
            if ($table->save()) {
                return response()->json(['status' => true], 200);
            } else {
                return response()->json(['status' => false], 200);
            }
        } else {
            return response()->json(['status' => false, 'error' => 'Can\'t access for your request'], 200);
        }
    }


    function files_for_other_docs(Request $request){

        $company_id = $request->company_id;
        $request_id = $request->request_id;

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
        );
  
        if(!$company_id || !$request_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }


        $company_info = Company::where('id', $company_id)->first();
        $company_status = $this->settings($company_info->status,'id')->key;

      
        $form_other_docs = Documents::where('key', 'REDUCTION_STATED_CAPITAL_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

        $other_docs = CompanyDocuments::where('company_id', $company_id)
                                        ->where('document_id', $form_other_docs->id )
                                        ->where('request_id', $request_id)
                                        ->orderBy('id', 'DESC')
                                        ->get();
            foreach($other_docs as $docs ) {

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $docs->name;
                $file_row['file_type'] = '';
                $file_row['multiple_id'] = $docs->multiple_id;
                $file_row['uploaded_path'] = '';
                $file_row['is_admin_requested'] = false;
                        
                $uploadeDocStatus = @$docs->status;
                if($company_status == 'COMPANY_CHANGE_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
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
                                                    isset($docs->name) &&
                                                    $docs->file_token &&
                                                    $docs->path &&
                                                    ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                    $docs->name ? $docs->name : '';
                $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                                    isset($docs->path ) &&
                                                    isset($docs->name) &&
                                                    $docs->file_token &&
                                                    $docs->path &&
                                                    ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                    $docs->name ? $docs->file_token : '';
        
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
        $request_id = $request->request_id;

  
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
    
        $path = 'stated-capital/other-docs/'.substr($company_id,0,2);
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

    function removeOtherDoc(Request $request){

        $file_token = $request->file_token;
        
    
        CompanyDocuments::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
    
        ], 200);
    }

}
