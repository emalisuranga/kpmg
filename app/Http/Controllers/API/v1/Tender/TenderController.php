<?php
namespace App\Http\Controllers\API\v1\tender;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Address;
use App\ForeignAddress;
use App\SettingType;
use App\Setting;
use App\User;
use App\People;
use App\TenderPublication;
use App\Tender;
use App\TenderItem;
use App\TenderUser;
use App\TenderApplication;
use App\TenderApplicantItem;
use App\TenderApplyMember;
use App\TenderDocument;
use App\TenderDocumentStatus;
use App\PublisherDocument;
use App\Documents;
use App\Country;
use App\TenderCertificate;
use App\TenderStatus;
use App\SmsQueue;
use App\JvCompanies;
use Carbon\Carbon;
use Storage;
use App;
use URL;
use App\TenderRenewalReRegistration;
use App\Http\Helper\_helper;

use PDF;

class TenderController extends Controller
{
    use _helper;

    private $items_per_page;

    function __construct() {
        
        $this->items_per_page = 3;
    }

    function openTenders(){

        $openTenderIds= array();

        $open_tender_key = $this->settings('OPEN_TENDER','key')->id;

        $tenders = Tender::where('type', $open_tender_key)
        ->orderBy('created_at', 'DESC')
        ->get();
        
        if(count($tenders)){
         foreach($tenders as $tender ){
                $openTenderIds[] = $tender->id;
         }
        }

       return $openTenderIds;

    }

    private function is_publisher_exist($publisherList,$pid){
         
          if(!count($publisherList)) {
                  return false;
          }
          foreach($publisherList as $p ) {

                return $p['id'] == $pid;
 
          }

          return false;
    }

    private function get_publishers(){

        $tender_user_data_count = User::where('is_tender_user', 'yes')->count();
        $publisherList = array();

        if($tender_user_data_count) {
            $tender_user_data = User::where('is_tender_user', 'yes')->get(); 

            foreach($tender_user_data as $tuser) {
                $people_info = People::where('id', $tuser->people_id )->first();

                $pub_name = ( $people_info->ministry || $people_info->department ) ? $people_info->ministry.'-'.$people_info->department : $people_info->first_name.' '.$people_info->last_name;

                $publisher = array(
                        'id' => $people_info->id,
                        'name' => $pub_name
                );
                $publisherList[] = $publisher;

            }
        }

        return $publisherList;

    }


    function getCloseTender(Request $request ){

        $ref_no= trim( $request->ref_no );
        //close tender Id;
        $closeTenderId = 0;
      

        $closeTenderitem =  TenderUser::where('ref_no',$ref_no )->first();
        $closeTenderId = isset( $closeTenderitem->tender_id ) ? $closeTenderitem->tender_id : 0;

        if(!$closeTenderId) {
                return response()->json([
                        'message'       => "Invalid Reference No.",
                        'closeTender'    => array(),
                        'status'        => false
                        ], 200);
        }
     
        $query = Tender::query();
        $query->where('id', $closeTenderId );
        $query->where('status', $this->settings('TENDER_PENDING','key')->id );
        $tender = $query->first();
        $tenderList = array();

        $tenderStatus = 'close';
        //$publisher_info = People::where('id', $tender->created_by )->first();

       // $publisher_info = People::leftjoin('users', 'users.people_id', '=', 'people.id')
       // ->select('users.id','people.first_name','people.last_name')
      //  ->where('users.id', $tender->created_by)
      //  ->first();
        $publisher_info = People::where('id', $tender->created_by )->first();

        $tenderInfo = array(
                'type'          => $tenderStatus,
                'number'        => $tender->number,
                'name'          => $tender->name,
                'descriptin'    => $tender->description,
                'id'            => $tender->id,
                'publication_id'  => $tender->publication_id,
                'publisher_name' => $publisher_info['first_name']. ' '.$publisher_info['last_name']
        );
      
        return response()->json([
                'message'       => "Successfully listed close tender.",
                'closeTender'    => $tenderInfo,
                'status'        => true
                ], 200);

    }

    private function getTenderPaginatePages($tender_name_part, $search_publisher, $publisherDivision, $tender_no = '' ){
      

        $open_tender_key = $this->settings('OPEN_TENDER','key')->id;

        $query = Tender::query();
        $query->where('type', $open_tender_key );
 
        if ($search_publisher) {
        $query->where('created_by', $search_publisher );
        }
        if ($tender_name_part) {
        $query->where('name', 'ilike', '%' . $tender_name_part . '%');
        }
        if($publisherDivision) {
                $query->where('division', 'ilike', '%' . $publisherDivision . '%');
        }
        if($tender_no) {
                  $query->where('number', 'ilike', '%' . $tender_no . '%');
          }
        $query->where('status', $this->settings('TENDER_PENDING','key')->id );
        $query->orderby('type', 'DESC');
      
        $result_count = $query->count();

       return  ($result_count % $this->items_per_page == 0  )
                        ? $result_count / $this->items_per_page
                        : intval($result_count / $this->items_per_page) + 1;

    }


    function getTenders(Request $request ){


        $ref_no= trim( $request->ref_no );
        $tender_name_part = trim( $request->tenderNamePart);
        $search_publisher = intval($request->publisher);
        $tender_no = trim($request->tenderNo);
        $page = intval($request->page);
        $publisherDivision = trim($request->publisherDivision);
        $offset = $page*$this->items_per_page;

        $open_tender_key = $this->settings('OPEN_TENDER','key')->id;

        $query = Tender::query();
        $query->where('type', $open_tender_key );
 
        if ($search_publisher) {
        $query->where('created_by', $search_publisher );
        }
        if ($tender_name_part) {
        $query->where('name', 'ilike', '%' . $tender_name_part . '%');
        }
        if($publisherDivision) {
          $query->where('division', 'ilike', '%' . $publisherDivision . '%');
        }

        if($tender_no) {
              //  $query->where('number', $tender_no );
                $query->where('number', 'ilike', '%' . $tender_no . '%');
        }
        $query->where('status', $this->settings('TENDER_PENDING','key')->id );

        
        $query->orderby('type', 'DESC');
      
        $tenders = $query->limit($this->items_per_page)->offset($offset)->get();

        $tenderList = array();
       
        $tenderCount =0;

        if(isset($tenders[0]) && $tenders[0]->name ){ //check at least one item
         foreach($tenders as $tender ){

                $tenderCount++;

                $tenderStatus = $this->settings($tender->type,'id')->key;
                $tenderStatus = ('CLOSE_TENDER' === $tenderStatus ) ? 'close' :'open';
               // $publisher_info = People::where('id', $tender->created_by )->first();

              // $publisher_info = People::leftjoin('users', 'users.people_id', '=', 'people.id')
              // ->select('users.id','people.first_name','people.last_name','people.ministry', 'people.department')
             //  ->where('users.id', $tender->created_by)
             //  ->first();
               $publisher_info = People::where('id', $tender->created_by )->first();

               $tenderItemsCount = TenderItem::where('tender_id', $tender->id )->count();
               
               $tenderItems = TenderItem::where('tender_id', $tender->id )->get();
               $today = date('Y-m-d',time());
               $todaydatetimestamp  = strtotime($today);
               $notFutureCount = 0;

               if( !$tenderItemsCount){
                     continue;
               }
               foreach($tenderItems as $item ) {

                  $from = strtotime($item->from);


                  if($todaydatetimestamp >= $from) {
                        $notFutureCount++;
                  }
               }

               if($notFutureCount <= 0 ) {
                  continue;
               }


             

               $tenderInfo = array(
                       'type'          => $tenderStatus,
                       'number'        => $tender->number,
                       'name'          => $tender->name,
                       'descriptin'    => $tender->description,
                       'ministry'      => $publisher_info->ministry.'/'.$publisher_info->department.'/'.$tender->division,
                      // 'from'          => $tender->from,
                     //  'to'            => $tender->to,
                       'id'            => $tender->id,
                       'publication_id'  => $tender->publication_id,
                       'publisher_name' =>$publisher_info->ministry.'/'.$publisher_info->department.'/'.$tender->division,
                       'publisher' => $publisher_info,
                       'tenderItems' => $tenderItems,
                       'notFutureCount' => $notFutureCount
               );
                $tenderList[] = $tenderInfo;

         }
        }

       @$this->rrmdir(storage_path("app/tender-apply"));
       @$this->rrmdir(storage_path("app/tender-awording"));

        return response()->json([
                'message'       => "Successfully listed open tenders.",
                'tenderList'    => $tenderList,
                'publisherList' => $this->get_publishers(),
                'tenderCount'   => $tenderCount,
                'status'        => true,
                'count'         => $query->count(),
                'total_pages'   => $this->getTenderPaginatePages($tender_name_part, $search_publisher,$publisherDivision, $tender_no),
                'current_page'  => ($page+1)
                ], 200);


    }



   function getAppliedTenders() {
        $user = $this->getAuthUser();
        $applications = TenderApplication::leftJoin('tenders', 'tender_applications.tender_id', '=', 'tenders.id')
        ->where('applied_by',$user->userid)
        ->select(
                'tenders.id as tender_id',
                'tenders.name as tender_name',
                'tenders.number as tender_number',
                'tender_applications.status as status',
                'tender_applications.id as application_id',
                'tender_applications.token as token',
                'tender_applications.created_at as published_date'
                )
        ->orderBy('tender_applications.updated_at' ,'DESC')
        ->orderBy('tender_applications.created_at' ,'DESC')
        ->get()->toArray();

        $application_array = array();

        if(count($applications)) {
                foreach($applications as $application) {
                        $row = array();

                        $status = $this->settings( $application['status'], 'id')->key;
                       

                        $button_enabled = true;
                        $label = '';

                        if($status == 'TENDER_CANCELED') {
                                $button_enabled = true;
                                $label = 'Update Application';
                        } else if($status == 'TENDER_REQUEST_TO_RESUBMIT') {
                                $button_enabled = true;
                                $label = 'Resubmit';
                        } else if($status == 'TENDER_RESUBMITTED') {
                                $button_enabled = false;
                                $label = 'Application Resubmitted';
                        }else if($status == 'TENDER_APPROVED'){
                                $button_enabled = false;
                                $label = 'Application Approved';
                        }else if($status == 'TENDER_REJECTED'){
                                $button_enabled = false;
                                $label = 'Application Rejected';
                        } else {
                                $button_enabled = false;
                                $label = 'Application Processing';
                        }

                        $row['button_enabled'] = $button_enabled;
                        $row['label'] = $label;
                        $row['status'] = $status;
                        $row['token'] = $application['token'];
                        $row['tender_id'] = $application['tender_id'];
                        $row['tender_name'] = $application['tender_name'];
                        $row['tender_number'] = $application['tender_number'];

                        $created_at =  new Carbon($application['published_date']);
                        $created_at = $created_at->toDateTimeString();
                        $row['tender_published_date'] = $created_at;
                        $row['application_id'] = $application['application_id'];



                        $tender_items = TenderApplicantItem::leftJoin('tender_applications','tender_application_items.tender_application_id', '=', 'tender_applications.id')
                        ->leftJoin('tender_items','tender_application_items.tender_item_id', '=', 'tender_items.id' )
                        ->leftJoin('tenders','tender_items.tender_id', '=', 'tenders.id' )
                        ->whereIn('tender_application_items.status', array(
                              $this->settings('TENDER_ITEM_AWARDED','key')->id,
                              $this->settings('TENDER_PCA2_REQUEST_TO_RESUBMIT','key')->id,
                              $this->settings('TENDER_PCA2_RESUBMITED','key')->id,
                              $this->settings('TENDER_PCA3_ISSUED','key')->id,
                              $this->settings('TENDER_PCA4_ISSUED','key')->id,
                              $this->settings('TENDER_ITEM_APPLIED','key')->id,
                              $this->settings('TENDER_ITEM_APPLIED','key')->id,
                              $this->settings('TENDER_PCA2_SUBMITED','key')->id,
                              $this->settings('TENDER_PCA2_REJECTED','key')->id,
                              $this->settings('TENDER_PCA2_APPROVED','key')->id
                        ))
                        ->where('tender_applications.id', $application['application_id'] )
                        ->where('tenders.id', $application['tender_id'] )
                        ->select(
                                 'tender_items.name as item_name',
                                 'tender_application_items.token as award_token',
                                 'tender_application_items.id as application_item_id',
                                 'tender_application_items.status as status'
                         )
                        ->get()
                        ->toArray();
                        $tender_items_arr = array();
                        if(count($tender_items)) {
                                foreach($tender_items as $item) {
                                        $row_item = array();
                
                                        $status = $this->settings( $item['status'], 'id')->key;
                                
                
                                        $button_enabled = true;
                                        $label = '';
                                        $token = '';
                                        $pca3_re_rereg_token = '';
                                        $pca4_re_rereg_token = '';
                                        $renewal_rereg_pca3_label = '';
                                        $reneal_rereg_pca3__button_enabled = false;
                                        $renewal_rereg_pca4_label = '';
                                        $reneal_rereg_pca4__button_enabled = false;
                                        $re_re3_status =  '';
                                        $re_re4_status =  '';

                                        $pca3_re_rereg_found = false;
                                        $pca4_re_rereg_found = false;
                                        

                                      /********************************************renewal rereg********* */


                                        $token = $item['award_token'];

                                        if($status == 'TENDER_ITEM_AWARDED') {
                                                $button_enabled = true;
                                                $label = 'Complete applying for PCA4 certificate';
                                        }else if($status == 'TENDER_PCA2_REQUEST_TO_RESUBMIT') {
                                                $button_enabled = true;
                                                $label = 'Resubmit request for PCA4 certificate';
                                        }else if($status == 'TENDER_PCA2_RESUBMITED') {
                                                $button_enabled = false;
                                                $label = 'Award Request Resubmitted';
                                        }else if($status == 'TENDER_ITEM_APPLIED') {
                                                $button_enabled = false;
                                                $label = 'Applied/Not awarded yet';
                                        } else if($status == 'TENDER_PCA2_SUBMITED') {
                                                $button_enabled = false;
                                                $label = 'Award process completed/Waiting for ROC approval';
                                        }else if($status == 'TENDER_PCA2_REJECTED'){
                                                $button_enabled = false;
                                                $label = 'Award request rejected';
                                        }else if($status == 'TENDER_PCA2_APPROVED'){
                                                $button_enabled = false;
                                                $label = 'Award request approved';
                                        } else if($status == 'TENDER_PCA3_ISSUED'){
                                                $button_enabled = false;
                                                $label = 'Application PCA1 approved';
                                        }  else if($status == 'TENDER_PCA4_ISSUED'){
                                                $button_enabled = false;
                                                $label = 'Application PCA2 approved';
                                        }else {
                                                $button_enabled = false;
                                                $label = 'Award request Processing';
                                        }

                                        $renewal_rereg_pca3 =  TenderRenewalReRegistration::where('tender_application_item_id', $item['application_item_id'])
                                        ->where('tender_application_id' ,$application['application_id'])
                                        ->where('certificate_type', $this->settings( 'CERT_TENDER_PCA3', 'key')->id)
                                        ->first();
                                        $pca3_type = $this->settings('CERT_TENDER_PCA3','key')->id;
                                        $uploadedPCA3Doc = TenderCertificate::where('item_id', $item['application_item_id'])
                                                                ->where('type', $pca3_type)
                                                                ->first();

                                        if(isset($renewal_rereg_pca3->id)) {
                                                $pca3_re_rereg_token = $renewal_rereg_pca3->token;
                                                $re_re3_status = $this->settings( $renewal_rereg_pca3->status, 'id')->key;
                                                $pca3_re_rereg_found = true;
                                                $new_pca3_re_rereg_record = false;
                                                $new_pca3_type = '';

                                                if($renewal_rereg_pca3->type == $this->settings( 'TENDER_RENEWAL', 'key')->id) {

                                                        if($re_re3_status == 'TENDER_RENEWAL_PCA3_SUBMITTED') {
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'PCA3 renewal request submitted/Waiting for ROC approval';
                                                        } else if($re_re3_status == 'TENDER_RENEWAL_PCA3_PENDING') {
                                                                $reneal_rereg_pca3__button_enabled = true;
                                                                $renewal_rereg_pca3_label = 'Renew PCA3 Certificate';
                                                        } else if($re_re3_status == 'TENDER_RENEWAL_PCA3_REQUEST_TO_RESUBMIT') {
                                                                $reneal_rereg_pca3__button_enabled = true;
                                                                $renewal_rereg_pca3_label = 'Resubmit PCA3 renewal request';
                                                        }else if($re_re3_status == 'TENDER_RENEWAL_PCA3_RESUBMITED'){
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'Resubmitted PCA3 renewal request/Waiting for ROC approval';
                                                        }else if($re_re3_status == 'TENDER_RENEWAL_PCA3_REJECTED'){
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'PCA3 Renewal request rejected';
                                                        } else {
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'PCA3 renewal request processing';
                                                        }
                                                }
                        
                                                if($renewal_rereg_pca3->type == $this->settings( 'TENDER_REREGISTRATION', 'key')->id) {
                        
                                                        if($re_re3_status == 'TENDER_REREGISTRATION_PCA3_SUBMITTED') {
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'PCA3 re registration request submitted/Waiting for ROC approval';
                                                        } else if($re_re3_status == 'TENDER_REREGISTRATION_PCA3_PENDING') {
                                                                $reneal_rereg_pca3__button_enabled = true;
                                                                $renewal_rereg_pca3_label = 'Re register PCA3 Certificate';
                                                        } else if($re_re3_status == 'TENDER_REREGISTRATION_PCA3_REQUEST_TO_RESUBMIT') {
                                                                $reneal_rereg_pca3__button_enabled = true;
                                                                $renewal_rereg_pca3_label = 'Resubmit PCA3 re registration request';
                                                        }else if($re_re3_status == 'TENDER_REREGISTRATION_PCA3_RESUBMITED'){
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'Resubmitted PCA3 re registration request/Waiting for ROC approval';
                                                        }else if($re_re3_status == 'TENDER_REREGISTRATION_PCA3_REJECTED'){
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'PCA3 re registration request rejected';
                                                        } else {
                                                                $reneal_rereg_pca3__button_enabled = false;
                                                                $renewal_rereg_pca3_label = 'PCA3 re registration request processing';
                                                        }
                        
                                                }
                                        } else {

                                                $pca3_re_rereg_found = false;
                                                $new_pca3_re_rereg_record = false;
                                                $new_pca3_type = '';

                                                if(isset($uploadedPCA3Doc->expires_at)) {
                                                        $pca3_re_rereg_found = false;
                                                        $new_pca3_re_rereg_record = true;
                                                        $pca3_expired_at = strtotime($uploadedPCA3Doc->expires_at);
                                                        $now = time();

                                                        if($now < $pca3_expired_at) { // new renewal on
                                                                $reneal_rereg_pca3__button_enabled = true;
                                                                $renewal_rereg_pca3_label = 'Renew PCA3 Certificate';
                                                                $new_pca3_type = 'renewal';

                                                        } else { // new reregistration on
                                                                $reneal_rereg_pca3__button_enabled = true;
                                                                $renewal_rereg_pca3_label = 'Re register PCA3 Certificate';
                                                                $new_pca3_type = 'reregister';
                                                        }
                                                }


                                        }

                                        $renewal_rereg_pca4 =  TenderRenewalReRegistration::where('tender_application_item_id', $item['application_item_id'])
                                        ->where('tender_application_id' ,$application['application_id'])
                                        ->where('certificate_type', $this->settings( 'CERT_TENDER_PCA4', 'key')->id)
                                        ->first();
                                        $pca4_type = $this->settings('CERT_TENDER_PCA4','key')->id;
                                        $uploadedPCA4Doc = TenderCertificate::where('item_id', $item['application_item_id'])
                                                                ->where('type', $pca4_type)
                                                                ->first();
                                        
                                        
                                        if(isset($renewal_rereg_pca4->id)) {
                                                $re_re4_status = $this->settings( $renewal_rereg_pca4->status, 'id')->key;
                                                $pca4_re_rereg_token = $renewal_rereg_pca4->token;
                                                $pca4_re_rereg_found = true;
                                                $new_pca4_re_rereg_record = false;
                                                $new_pca4_type = '';

                                                if($renewal_rereg_pca4->type == $this->settings( 'TENDER_RENEWAL', 'key')->id) {
                                                        if($re_re4_status == 'TENDER_RENEWAL_PCA4_SUBMITTED') {
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'PCA4 renewal request submitted/Waiting for ROC approval';
                                                        } else if($re_re4_status == 'TENDER_RENEWAL_PCA4_PENDING') {
                                                                $reneal_rereg_pca4__button_enabled = true;
                                                                $renewal_rereg_pca4_label = 'Renew PCA4 Certificate';
                                                        } else if($re_re4_status == 'TENDER_RENEWAL_PCA4_REQUEST_TO_RESUBMIT') {
                                                                $reneal_rereg_pca4__button_enabled = true;
                                                                $renewal_rereg_pca4_label = 'Resubmit PCA4 renewal request';
                                                        }else if($re_re4_status == 'TENDER_RENEWAL_PCA4_RESUBMITED'){
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'Resubmitted PCA4 renewal request/Waiting for ROC approval';
                                                        }else if($re_re4_status == 'TENDER_RENEWAL_PCA4_REJECTED'){
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'PCA4 Renewal request rejected';
                                                        } else {
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'PCA4 renewal request processing';
                                                        }
                                                }
                        
                                                if($renewal_rereg_pca4->type == $this->settings( 'TENDER_REREGISTRATION', 'key')->id) {
                        
                                                        if($re_re4_status == 'TENDER_REREGISTRATION_PCA4_SUBMITTED') {
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'PCA4 re registration request submitted/Waiting for ROC approval';
                                                        } else if($re_re4_status == 'TENDER_REREGISTRATION_PCA4_PENDING') {
                                                                $reneal_rereg_pca4__button_enabled = true;
                                                                $renewal_rereg_pca4_label = 'Re register PCA4 Certificate';
                                                        } else if($re_re4_status == 'TENDER_REREGISTRATION_PCA4_REQUEST_TO_RESUBMIT') {
                                                                $reneal_rereg_pca4__button_enabled = true;
                                                                $renewal_rereg_pca4_label = 'Resubmit PCA4 re registration request';
                                                        }else if($re_re4_status == 'TENDER_REREGISTRATION_PCA4_RESUBMITED'){
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'Resubmitted PCA4 re registration request/Waiting for ROC approval';
                                                        }else if($re_re4_status == 'TENDER_REREGISTRATION_PCA4_REJECTED'){
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'PCA4 re registration request rejected';
                                                        } else {
                                                                $reneal_rereg_pca4__button_enabled = false;
                                                                $renewal_rereg_pca4_label = 'PCA4 re registration request processing';
                                                        }
                        
                                                }
                                        } else {

                                                $pca4_re_rereg_found = false;
                                                $new_pca4_re_rereg_record = false;
                                                $new_pca4_type = '';

                                                if(isset($uploadedPCA4Doc->expires_at)) {
                                                        $pca4_re_rereg_found = false;
                                                        $new_pca4_re_rereg_record = true;
                                                        $pca4_expired_at = strtotime($uploadedPCA4Doc->expires_at);
                                                        $now = time();

                                                        if($now < $pca4_expired_at) { // new renewal on
                                                                $reneal_rereg_pca4__button_enabled = true;
                                                                $renewal_rereg_pca4_label = 'Renew PCA4 Certificate';
                                                                $new_pca4_type = 'renewal';

                                                        } else { // new reregistration on
                                                                $reneal_rereg_pca4__button_enabled = true;
                                                                $renewal_rereg_pca4_label = 'Re register PCA4 Certificate';
                                                                $new_pca4_type = 'reregister';
                                                        }
                                                }

                                                
                                        }

                                        /*************reneral rereg end******************** */




                                        $row_item['renewal_rereg_pca3_button_enabled'] = $reneal_rereg_pca3__button_enabled;
                                        $row_item['renewal_rereg_pca3_label'] = $renewal_rereg_pca3_label;
                                        $row_item['renewal_rereg_pca4_button_enabled'] = $reneal_rereg_pca4__button_enabled;
                                        $row_item['renewal_rereg_pca4_label'] = $renewal_rereg_pca4_label;

                                       // $row_item['renewal_rereg_found'] = isset($renewal_rereg->id);
                                        $row_item['re_re4_status'] = $re_re4_status;
                                        $row_item['re_re3_status'] = $re_re3_status;
                                        $row_item['status'] = $status;

                                        $row_item['button_enabled'] = $button_enabled;
                                        $row_item['label'] = $label;

                                        $row_item['token'] = $token;
                                        $row_item['re_rereg_pca3_token'] = $pca3_re_rereg_token;
                                        $row_item['re_rereg_pca4_token'] = $pca4_re_rereg_token;

                                        $row_item['pca3_re_rereg_found'] = $pca3_re_rereg_found;
                                        $row_item['pca4_re_rereg_found'] = $pca4_re_rereg_found;
                                        $row_item['new_pca3_re_rereg_record'] = $new_pca3_re_rereg_record;
                                        $row_item['new_pca4_re_rereg_record'] = $new_pca4_re_rereg_record;
                                        $row_item['new_pca3_type'] = $new_pca3_type;
                                        $row_item['new_pca4_type'] = $new_pca4_type;

                                        $row_item['item_name'] = $item['item_name'];
                                        $row_item['applicant_item_id'] = $item['application_item_id'];

                                        $row_item['pca1_token']  = isset($uploadedPCA3Doc->file_token) && $uploadedPCA3Doc->file_token  ? $uploadedPCA3Doc->file_token : '';
                                        $row_item['pca1_expired_at'] = isset($uploadedPCA3Doc->file_token) && $uploadedPCA3Doc->file_token  ? $uploadedPCA3Doc->expires_at : '';
                                        $row_item['pca2_token']  = isset($uploadedPCA4Doc->file_token) && $uploadedPCA4Doc->file_token   ? $uploadedPCA4Doc->file_token : '';
                                        $row_item['pca2_expired_at'] = isset($uploadedPCA4Doc->file_token) && $uploadedPCA4Doc->file_token  ? $uploadedPCA4Doc->expires_at : '';
                                        
                                        $tender_items_arr[] = $row_item;
                                }
                        }

                        $row['tender_items'] = $tender_items_arr;

                        $application_array[] = $row;
                      
                }
        }


        return response()->json([
                'message'       => "Successfully listed user applications.",
                'applicationList'    => $application_array,
                'status'        => true
                ], 200);
           
   }





    function getUserTenders (Request $request ){

        $loginUserEmail = $this->clearEmail($request->loginUser);
        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->people_id;

        $publicationsList = array(

                'list' =>  array()
        );

        $publications = TenderPublication::where('created_by', $loginUserId )->get();

        $publications_count = TenderPublication::where('created_by', $loginUserId )->count();

        if($publications_count){
                foreach($publications  as $p ) {

                  $pub = array();

                  $pub['id'] = $p->id;
                  $pub['created_by'] = $p->created_by;
                  $pub['name'] = $p->name;
                  $pub['openList'] = false;

                  $tenders = Tender::where('created_by', $loginUserId)
                        ->where('publication_id', $p->id)
                        ->orderBy('published_date','DESC')
                        ->orderBy('id', 'DESC')
                        ->get();
                  foreach( $tenders as $tender ){
                        $tenderStatus = $this->settings($tender->type,'id')->key;
                        $tenderStatus = ('CLOSE_TENDER' === $tenderStatus ) ? 'close' :'open';

                        
                        $max_date = 0;
                        $item_closing_dates = array();
                        $today = time();
                        // get tender items
                        $tender_items = TenderItem::where('tender_id', $tender->id)->get();
                        if(isset($tender_items[0]->id)){
                                foreach($tender_items as $tender_item) {
                                        $item_closing_dates[] = strtotime($tender_item->to_time);
                                }
                           $max_date = max($item_closing_dates);
                        }


                
                        $appliedCountArr = array(
                                $this->settings('TENDER_APPROVED','key')->id,
                             //   $this->settings('TENDER_PENDING','key')->id,
                             //   $this->settings('TENDER_REQUEST_TO_RESUBMIT','key')->id,
                             //   $this->settings('TENDER_RESUBMITTED','key')->id,
                        );
                                       
                         $get_applied_counts = TenderApplication::where('tender_id',$tender->id)
                                        ->whereIn('status',  $appliedCountArr)
                                        ->count();

                         $arr = array(
                                'type' => $tenderStatus,
                                'number' => $tender->number,
                                'name' => $tender->name,
                                'description' => $tender->description,
                                'id' => $tender->id,
                                'show_info' => ($today > $max_date),
                                'publicationId' => $tender->publication_id,
                                'applied_count' => $get_applied_counts,
                                'publishedDate' =>  ($tender->published_date) ? date('l jS \of F Y h:i:s A', strtotime($tender->published_date)) : ''
                         );
                         $pub['tendersList'][] = $arr;
                      
                  }

                  $publicationsList['list'][] = $pub;
                  

                }
        }

        return response()->json([
                'message'       => "Successfully listed user tenders.",
                'tenderList'    => $publicationsList,
                'tenderCount'   => $publications_count,
                'status'        => true
                ], 200);

         

    }

    function getUserTenders_old(Request $request ){

        $loginUserEmail = $this->clearEmail($request->loginUser);
        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->people_id;

        $tenders = Tender::where('created_by', $loginUserId)
        ->orderBy('id', 'DESC')
        ->get();
        $tenderList = array();
        $tenderCount =0;

        if(count($tenders)){
         foreach($tenders as $tender ){

                $tenderCount++;

                $tenderStatus = $this->settings($tender->type,'id')->key;
                $tenderStatus = ('CLOSE_TENDER' === $tenderStatus ) ? 'close' :'open';

                $tenderInfo = array(
                        'type' => $tenderStatus,
                        'number' => $tender->number,
                        'name'   => $tender->name,
                        'name'  => $tender->name,
                        'descriptin' => $tender->description,
                        'from' => $tender->from,
                        'to'    => $tender->to,
                        'id'    => $tender->id
                );
                $tenderList[] = $tenderInfo;

         }
        }

        return response()->json([
                'message'       => "Successfully listed user tenders.",
                'tenderList'    => $tenderList,
                'tenderCount'   => $tenderCount,
                'status'        => true
                ], 200);
        


    }

   


    function createTender(Request $request ){

       $loginUserEmail = $this->clearEmail($request->loginUser);
       $loginUserInfo = User::where('email', $loginUserEmail)->first();
       $loginUserId = $loginUserInfo->people_id;

       $tenderNo = $request->tenderNo;

       $type= $request->tenderType;

       $type_db = ($type === 'close') ?  $this->settings('CLOSE_TENDER','key')->id : $this->settings('OPEN_TENDER','key')->id;
       $number = $request->tenderNo;
       $name = $request->tenderName;
       $description = $request->description;
      // $from = $request->dateFrom;
      // $to = $request->dateTo;
       $members = $request->tenderMembers;

       $tenderPublicationId = $request->publicationId;
       $tenderPublicationNewName = $request->newPublicationName;

      
       if(!$tenderPublicationId){

    
           $tenderPublication = new TenderPublication;

           $tenderPublication->name = $tenderPublicationNewName;
           $tenderPublication->created_by = $loginUserId;
           $tenderPublication->save();

           $tenderPublicationId = $tenderPublication->id;
       }
    
       $tenderId = intval( $request->tenderId );
       $tenderInfo = null;

       if ($tenderId) {
            $tender = Tender::find($tenderId);
            $tenderInfo = Tender::where('id', $tenderId)->first();
       } else {
            $tender = new Tender();
            //$tenderId  = abs( crc32( uniqid() ) ); 
            $tenderId=  $this->genarateTenderId();
            $tender->id = $tenderId ;
          
       }

       $tender->type = $type_db;
       $tender->number = $number;
       $tender->name = $name;
       $tender->description = $description;
       $tender->publication_id = $tenderPublicationId;
       if(! ( isset($tenderInfo->status) && $tenderInfo->status === $this->settings('TENDER_PENDING','key')->id) ) {
         $tender->status = $this->settings('COMMON_STATUS_DEACTIVE','key')->id;
       }
       $tender->created_by = $loginUserId;
       $tender->amount = $request->tenderAmount;
       $tender->ministry = $request->ministry;
       $tender->department = $request->department;
       $tender->division = $request->division;
       $tender->authorized_person_name = $request->authorized_person_name;
       $tender->authorized_person_designation = $request->authorized_person_designation;
       $tender->authorized_person_address = $request->authorized_person_address;
       $tender->authorized_person_phone = $request->authorized_person_phone;
       $tender->authorized_person_email = $request->authorized_person_email;
       

       if($type === 'close'){

        if( isset($members['members'] ) && is_array($members['members']) && count($members['members'] ) ) {
   
           /* if ($tenderId) {
                TenderUser::where('tender_id', $tenderId)
                            ->delete();
            }*/
            
            $tender->save();

            // get already listed tender users
            $already_users_count = TenderUser::where('tender_id', $tenderId)
                            ->count();
            $already_users_array = array();
            if($already_users_count){

                $already_users = TenderUser::where('tender_id', $tenderId)
                ->get();
                foreach($already_users as $u ) {
                        $already_users_array[] = $u->id;
                }

            }
            $submit_user_array = array();  
            foreach($members['members'] as $m ){

                if(isset($m['memberId']) && intval($m['memberId'])) {

                        $updateTenderUser = array(
                                
                                'name' => $m['name'],
                                'address' => $m['address'],
                                'contact_no' => $m['contactNo'],
                                'email'        => $m['email'],
                                'tender_id'  => $tenderId,
                        );
                        TenderUser::where('id', $m['memberId'] )->update($updateTenderUser);
                        $submit_user_array[] = $m['memberId'];

                } else {

                        $tenderMember = new TenderUser();

                        $refno = $this->genarateUserRefNo();
                        $tenderMember->name = $m['name'];
                        $tenderMember->address = $m['address'];
                        $tenderMember->contact_no = $m['contactNo'];
                        $tenderMember->email = $m['email'];
                        $tenderMember->tender_id = $tenderId;
                        $tenderMember->ref_no = $refno;
                        $tenderMember->save();
                        $submit_user_array = $tenderMember->id;


                }
                   
            }

            if(count($already_users_array)){
                    foreach($already_users_array as $uId) {

                        if(!in_array($uId, $submit_user_array)) {
                          TenderUser::where('id', $uId)
                                ->delete();
                        }
                    }
            }



        } else {
                return response()->json([
                        'message' => "Please submit members for this close tender",
                        'status' =>false
                ], 200);
        }
       

       }else {

        $tender->save();

       }


       return response()->json([
        'message' => "Successfully published the tender.",
        'tenderId' => $tenderId,
        'publicationId' => $tenderPublicationId,
        'status' =>true
        ], 200);

    }

    public function tenderPublications( Request $request ){

        $loginUserEmail = $this->clearEmail($request->loginUser);
        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->people_id;

        $publications = TenderPublication::where('created_by', $loginUserId )->get();

        return response()->json([
                'message' => "Successfully published the tender publications.",
                'publications' => $publications,
                'close_tender_applicant_csv' => asset('other/close_tender_applicant.csv'),
                
                'status' =>true
                ], 200);

    }

    private function sendTenderCloseUserMail($to, $tender_no, $ref_number){

        $headers .= "Reply-To: The Sender <noreply@eroc.com>\r\n"; 
        $headers .= "Return-Path: The Sender <noreply@eroc.com>\r\n";
        $headers .= "From: The Sender <noreply@eroc.com>\r\n";
        $headers .= "Organization: Eroc\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
        $headers .= "X-Priority: 3\r\n";
        $headers .= "X-Mailer: PHP". phpversion() ."\r\n";

        mail($to, "Your Reference No of tender No $tender_no", "Your Reference No is $ref_number", $headers); 

    }

    private function checkTenderNoExist($tenderNo){

        return  Tender::where('number',$tenderNo )->get()->count();
    }


  


    function createTenderItems(Request $request ){

        $tenderId = $request->tenderId;
        $items = $request->items;
        $action = $request->action;



 
        //first delete previous saved data
       // TenderItem::where('tender_id', $tenderId)->delete();
 
        if( isset($items['items'] ) && is_array($items['items']) && count($items['items'] ) ) {

             $i = 0;
             foreach($items['items'] as $item ){ 
                     $i++;

                     $date_from = date('D M d Y H:i:s',strtotime($item['dateFrom']));
                     $date_to = date('D M d Y H:i:s',strtotime($item['dateTo']) );
                    

                    if(isset($item['itemId']) && intval($item['itemId'])){

                        $date_update =  array(
                                'from' => $date_from,
                                'to'   => $date_to,
                                'from_time' => $date_from,
                                'to_time'   => $date_to,
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'quantity' => $item['qty'],
                                'number'   => isset( $item['itemNo'] ) && $item['itemNo'] ? $item['itemNo'] : 'ITEM-'.$i,
                        );
                        TenderItem::where('id', $item['itemId'])->update($date_update);
                          
                    } else {

                     $tenderItem = new TenderItem();
                     $tenderItem->name = $item['name'];
                     $tenderItem->description = $item['description'];
                     $tenderItem->quantity = floatval( $item['qty'] );
                     $tenderItem->from = $date_from;
                     $tenderItem->to = $date_to;
                     $tenderItem->from_time = $date_from;
                     $tenderItem->to_time = $date_to;
                     $tenderItem->number = isset($item['itemNo'] ) && $item['itemNo'] ? $item['itemNo'] : 'ITEM-'.$i;
                     $tenderItem->tender_id = $tenderId;

                     $tenderItem->status =  $this->settings( 'COMMON_STATUS_ACTIVE' ,'key')->id;
                     $tenderItem->save();
                    }
             }

             // update tender status
             $status_tender = array(
                     'status' => ($action ==='publish') ?  $this->settings( 'TENDER_PENDING' ,'key')->id : $this->settings( 'COMMON_STATUS_DEACTIVE' ,'key')->id,
                     'published_date' => date('Y-m-d H:i:s', time())
             );
             $update_done = Tender::where('id',$tenderId)->update($status_tender);

             if($action ==='publish') {

                $members_count = TenderUser::where('tender_id', $tenderId)->count();

                if($members_count){
                        $members = TenderUser::where('tender_id', $tenderId)->get();
                        $tenderInfo = Tender::where('id', $tenderId)->first();
                        

                        foreach($members as $m ) {

                          if( isset($m->ref_mail_sent) &&  $m->ref_mail_sent != 1 ) { // check mail with ref no is sent

                                $mail_sent_update = array(
                                        'ref_mail_sent' => 1
                                );

                                TenderUser::where('id', $m->id)
                                ->update($mail_sent_update);

                                $refno = $m->ref_no;
                                $message = $refno;
                                $message.= '<p><strong>Tender Name: </strong>'.$tenderInfo->name.'</p>';
                                $message.= '<p><strong>Tender Number: </strong>'.$tenderInfo->number.'</p>';
                                // @$this->sendTenderCloseUserMail($m->email,$tenderId,$refno);
                                @$this->ship($m->email, 'tokenwithemail', null, $message);

                          }
                          
                       
                        }
                }

             }
           





 
        } else {
                 return response()->json([
                         'message' => "Please submit tender items for this tender",
                         'status' =>false
                 ], 200);
        }
        

        return response()->json([
         'message' => "Successfully added the tender items.",
         'status' =>true
         ], 200);
 
     }


     function checkAlreadyApplyiedForsameTender($applicationId,$selected_items,$pass_or_nic="nic", $pass_or_nic_val) {

  
        $items = TenderApplicantItem::whereIn('tender_item_id', $selected_items)
        ->get();
        $check_field = ($pass_or_nic === 'passport') ? 'passport' : 'nic';
        foreach($items as $item) {
            
            if($pass_or_nic === 'passport') {

                if($item->passport === 'pass_or_nic_val' && $item->tender_application_id != $applicationId ){
                        return true;
                }
               
            }
            else {
            if($item->nic === 'pass_or_nic_val' && $item->tender_application_id != $applicationId ){
                return true;
            }

           }


        }

        return false;

        



     }
     


     function is_director_shareholder( $application_id, $isSriLankan, $checkvalue){

        if( $isSriLankan == 'yes' ) {

          $hasShareholdder=  TenderApplyMember::where('application_id',$application_id)
                             ->where('type',  $this->settings('TENDER_MEMBER_','key')->id)
                             ->where('nic', $checkvalue)
                              ->count();
        } else if ( $isSriLankan == 'no'){
           $hasShareholdder=  TenderApplyMember::where('application_id',$application_id)
                ->where('type',  $this->settings('TENDER_MEMBER_','key')->id)
                ->where('passport_no', $checkvalue)
                 ->count();
        }

        else {
           $hasShareholdder = 0;
        }

        return $hasShareholdder;

     }

     function submitTender( Request $request ) {

        $tender_items = $request->selectedItems;
        $applicant_type = $request->applicantType;
        $applicant_sub_type = $request->applicantSubType;
        $tenderer_sub_type = $request->tendererSubType;
        $tender_info = $request->applicntRecord;
        $tender_id = $request->tenderId;
        $id = intval( $request->id );

        

        $user = $this->getAuthUser();

      //  print_r($tender_info['tender_directors']['directors']);
      //  print_r($tenderInfo['tender_shareholders']['shareholder']);
      //  die();

        $itemCost = 0 ;

        foreach( $tender_items as $item ){

              $item = TenderItem::where('id', $item)->first();
              $itemCost += (float)$item->cost;
        }

        if(isset($id) && $id ){
                $tender_apply = TenderApplication::find($id );
        }else{
              $tender_apply = new TenderApplication();
         }
     
  


       $tender_apply->tender_id = $tender_id;
       $tender_apply->applicant_type = $this->settings( $applicant_type,'key')->id;
       $tender_apply->applicant_sub_type = ($applicant_sub_type) ? $this->settings( $applicant_sub_type,'key')->id : null;
       $tender_apply->tenderer_sub_type = ($tenderer_sub_type) ? $this->settings( $tenderer_sub_type,'key')->id : null;
       $tender_apply->amount = $itemCost;
       $tender_apply->is_srilankan = ( $tender_info['is_srilankan'] == 'Srilankan' ) ? 'yes' : 'no';
       $tender_apply->is_applying_from_srilanka = ( $tender_info['apply_from'] == '' || $tender_info['apply_from'] == 'Srilanka' ) ? 'yes' : 'no';
       $tender_apply->is_tenderer_applying_from_srilanka = ( $tender_info['tenderer_apply_from'] == '' || $tender_info['tenderer_apply_from'] == 'Srilanka' ) ? 'yes' : 'no';
      

       $tender_apply->applicant_fullname = isset($tender_info['applicant_name']) ? $tender_info['applicant_name'] : null;
       $tender_apply->applicant_email = isset($tender_info['appliant_email']) ? $tender_info['appliant_email'] : null;
       $tender_apply->applicant_mobile = isset($tender_info['appliant_mobile']) ? $tender_info['appliant_mobile'] : null;
       $tender_apply->signature_name = isset($tender_info['signing_party_name']) ? $tender_info['signing_party_name'] : null;
       $tender_apply->signature_designation = isset($tender_info['signing_party_designation']) ? $tender_info['signing_party_designation'] : null;
       $tender_apply->signature_other_designation = isset($tender_info['signing_party_designation']) && $tender_info['signing_party_designation'] == 'Other' ? $tender_info['signing_party_designation_other'] : null;
       $tender_apply->applicant_address = ( $tender_info['applicant_address']) ? $tender_info['applicant_address'] : null;
       $tender_apply->applicant_nationality = ($tender_info['applicant_natianality']) ?  $tender_info['applicant_natianality'] : null;
       $tender_apply->applicant_nic = ( $tender_info['is_srilankan'] == 'Srilankan' ) ? trim(strtoupper($tender_info['nic'])) : null;
       $tender_apply->applicant_passport = ( $tender_info['is_srilankan'] != 'Srilankan' ) ? trim(strtoupper($tender_info['passport'])) : null;

       $tender_apply->is_tenderer_srilankan = isset($tender_info['is_tenderer_srilankan']) ? (( $tender_info['is_tenderer_srilankan'] == 'Srilankan' ) ? 'yes' : 'no')  : null;
       $tender_apply->tenderer_nic = isset($tender_info['tenderer_nic']) && $tender_info['tenderer_nic'] ? trim(strtoupper($tender_info['tenderer_nic'])) : null;
       $tender_apply->tenderer_passport = isset($tender_info['tenderer_passport']) && $tender_info['tenderer_passport'] ? trim(strtoupper($tender_info['tenderer_passport'])) : null;

       if($applicant_type =='TENDER_TENDERER') {
              $tender_apply->applicant_fullname = isset($tender_info['tenderer_name']) ? $tender_info['tenderer_name'] : null;
              $tender_apply->applicant_address = ( $tender_info['tenderer_address']) ? $tender_info['tenderer_address'] : null;
              $tender_apply->applicant_nationality = ($tender_info['tenderer_natianality']) ?  $tender_info['tenderer_natianality'] : null;
       }
       $tender_apply->tenderer_fullname =  (isset($tender_info['tenderer_name']) && $tender_info['tenderer_name'] ) ? $tender_info['tenderer_name'] : null;
       $tender_apply->tenderer_address = (isset($tender_info['tenderer_address']) && $tender_info['tenderer_address'] ) ? $tender_info['tenderer_address'] : null;
       $tender_apply->tenderer_nationality = (isset($tender_info['tenderer_natianality']) && $tender_info['tenderer_natianality'] ) ? $tender_info['tenderer_natianality'] : null;
       $tender_apply->registration_number = (isset($tender_info['tender_company_reg_no']) && $tender_info['tender_company_reg_no']) ? trim(strtoupper( preg_replace('/\s+/', '', $tender_info['tender_company_reg_no']) )) : ''; 
       $tender_apply->tenderer_registration_number = (isset($tender_info['tender_tenderer_company_reg_no']) && $tender_info['tender_tenderer_company_reg_no']) ? trim(strtoupper( preg_replace('/\s+/', '', $tender_info['tender_tenderer_company_reg_no']))) : ''; 
       $tender_apply->status = $this->settings( 'TENDER_CANCELED','key')->id;
       
       $tender_apply->applied_by = $user->userid;
       $tender_apply->save();

       $tender_application_id = ( isset($id) && $id ) ? $id : $tender_apply->id;

       if(( isset($id) && $id )){ // if already exist applicaiton

             $tenderItemIds = array();
            
             // TenderApplicantItem::where('tender_application_id',$id)->delete();

              foreach( $tender_items as $item ){ // add tender items

                      $item_save_count = TenderApplicantItem::where('tender_application_id',$tender_application_id)
                                         ->where('tender_item_id', $item)
                                         ->count();
                      if($item_save_count){ // existing item for already saved application
                         
                        $tenderItemIds[] = $item;
                        continue; // just skip since it is already exists

                      }else { // new items for already saved application

                        $tenderItemIds[] = $item;

                              $tenderitem = new TenderApplicantItem();
                              $tenderitem->tender_application_id = $tender_application_id;
                              $tenderitem->tender_item_id = $item;
                              $tenderitem->status = $this->settings( 'TENDER_CANCELED','key')->id;
                              $tenderitem->applicant_nic = ( $tender_info['is_srilankan'] == 'Srilankan' ) ? $tender_info['nic'] : null;
                              $tenderitem->applicant_passport = ( $tender_info['is_srilankan'] != 'Srilankan' ) ? $tender_info['passport'] : null;
                            //  $tenderitem->is_tenderer_srilankan = isset($tender_info['is_tenderer_srilankan']) ? (( $tender_info['is_tenderer_srilankan'] == 'Srilankan' ) ? 'yes' : 'no')  : null;
                              $tenderitem->tenderer_nic = isset($tender_info['tenderer_nic']) && $tender_info['tenderer_nic'] ? $tender_info['tenderer_nic'] : null;
                              $tenderitem->tenderer_passport = isset($tender_info['tenderer_passport']) && $tender_info['tenderer_passport'] ? $tender_info['tenderer_passport'] : null;
                              $tenderitem->applicant_type = $this->settings( $applicant_type,'key')->id;
                              
                              $tenderitem->applicant_sub_type = ($applicant_sub_type) ? $this->settings( $applicant_sub_type,'key')->id : null;
                              $tenderitem->tenderer_sub_type = ($tenderer_sub_type) ? $this->settings( $tenderer_sub_type,'key')->id : null;

                              $tenderitem->registration_number = isset($tender_info['tender_company_reg_no']) && $tender_info['tender_company_reg_no'] ? $tender_info['tender_company_reg_no'] : null;
                              $tenderitem->tenderer_registration_number = isset($tender_info['tender_tenderer_company_reg_no']) && $tender_info['tender_tenderer_company_reg_no'] ? $tender_info['tender_tenderer_company_reg_no'] : null;
                              $tenderitem->save();

                      }


                      
              }

              if(count($tenderItemIds)) {
                 TenderApplicantItem::where('tender_application_id',$tender_application_id)
                ->whereNotIn('tender_item_id', $tenderItemIds)
                ->delete();
              }
              


       } else { // new application

              foreach( $tender_items as $item ){ // add tender items
                      $tenderitem = new TenderApplicantItem();
                      $tenderitem->tender_application_id = $tender_application_id;
                      $tenderitem->tender_item_id = $item;
                      $tenderitem->applicant_nic = ( $tender_info['is_srilankan'] == 'Srilankan' ) ? $tender_info['nic'] : null;
                      $tenderitem->applicant_passport = ( $tender_info['is_srilankan'] != 'Srilankan' ) ? $tender_info['passport'] : null;
                     // $tenderitem->is_tenderer_srilankan = isset($tender_info['is_tenderer_srilankan']) ? (( $tender_info['is_tenderer_srilankan'] == 'Srilankan' ) ? 'yes' : 'no')  : null;
                      $tenderitem->tenderer_nic = isset($tender_info['tenderer_nic']) && $tender_info['tenderer_nic'] ? $tender_info['tenderer_nic'] : null;
                      $tenderitem->tenderer_passport = isset($tender_info['tenderer_passport']) && $tender_info['tenderer_passport'] ? $tender_info['tenderer_passport'] : null;
                      $tenderitem->registration_number = isset($tender_info['tender_company_reg_no']) && $tender_info['tender_company_reg_no'] ? $tender_info['tender_company_reg_no'] : null;
                      $tenderitem->tenderer_registration_number = isset($tender_info['tender_tenderer_company_reg_no']) && $tender_info['tender_tenderer_company_reg_no'] ? $tender_info['tender_tenderer_company_reg_no'] : null;
                      $tenderitem->status = $this->settings( 'TENDER_CANCELED','key')->id;
                      $tenderitem->applicant_type = $this->settings( $applicant_type,'key')->id;
                      $tenderitem->applicant_sub_type = ($applicant_sub_type) ? $this->settings( $applicant_sub_type,'key')->id : null;
                      $tenderitem->tenderer_sub_type = ($tenderer_sub_type) ? $this->settings( $tenderer_sub_type,'key')->id : null;
                      $tenderitem->save();
               }

               
              

       }
       


       if(( isset($id) && $id )){
              //remove all members if update works
           //   TenderApplyMember::where('application_id',$id)->delete();
       }

       $directorsIds = array();

       if(isset($tender_info['tender_directors']['directors']) && is_array($tender_info['tender_directors']['directors']) && count($tender_info['tender_directors']['directors'])){

              foreach( $tender_info['tender_directors']['directors'] as $director ){

                      if(isset($director['id'] ) && intval($director['id'])){
                        $directorsIds[] = $director['id'];
                      }
                      
                      $member = (isset($director['id'] ) && intval($director['id'])) ?  TenderApplyMember::find(intval($director['id']) )    :   new TenderApplyMember();
                      $member->type =  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id;
                      $member->name = $director['name'];
                      $member->address = $director['address'];
                      $member->nationality =  $director['is_srilankan'] == 'no' ? $director['natianality'] : 'Sri Lanka';
                      $member->nationality_of_origin = $director['natianality_origin'];
                      $member->percentage_of_shares = $director['shares'];
                      $member->application_id = $tender_application_id;
                      $member->is_srilankan = $director['is_srilankan'] == 'no' ? 'no' : 'yes';
                      $member->nic =  $director['is_srilankan'] == 'yes' ?  $director['nic'] : null;
                      $member->passport_no =  $director['is_srilankan'] == 'no' ?  $director['passport'] : null;
                      $member->passport_issued_country =  $director['is_srilankan'] == 'no' ?  $director['passport_issued_country'] : null;
                      $member->save();

                      if( !( isset($director['id'] ) && intval($director['id'])) ){
                        $directorsIds[] = $member->id;
                      }
              }

              if(count($directorsIds)) {
                TenderApplyMember::where('application_id',$id)
                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                ->whereNotIn('id', $directorsIds)
                ->delete();
              }
       }

       //remove all members 

       $shIds = array();
       if(isset($tender_info['tender_shareholders']['shareholder']) && is_array($tender_info['tender_shareholders']['shareholder']) && count($tender_info['tender_shareholders']['shareholder'])){

              foreach($tender_info['tender_shareholders']['shareholder'] as $sh ){

                     if(isset($sh['id'] ) && intval($sh['id'])){
                        $shIds[] = $sh['id'];
                      }
                      
                      $member = (isset($sh['id'] ) && intval($sh['id'])) ?  TenderApplyMember::find(intval($sh['id']) )    :   new TenderApplyMember();
                      $member->type =  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id;
                      $member->is_firm = (isset($sh['is_firm']) && $sh['is_firm']) ? 1 : 0;
                      $member->firm_reg_no = (isset($sh['is_firm']) && isset($sh['firm_reg_no']) && $sh['is_firm'] && $sh['firm_reg_no']) ? $sh['firm_reg_no'] : '';
                      $member->name = $sh['name'];
                      $member->address = $sh['address'];
                      $member->nationality =  $sh['is_srilankan'] == 'no' ? $sh['natianality'] : 'Sri Lanka';
                      $member->nationality_of_origin = $sh['natianality_origin'];
                      $member->percentage_of_shares = $sh['shares'];
                      $member->application_id = $tender_application_id;
                      $member->is_srilankan = $sh['is_srilankan'] == 'no' ? 'no' : 'yes';
                      $member->nic =  $sh['is_srilankan'] == 'yes' ?  $sh['nic'] : null;
                      $member->passport_no =  $sh['is_srilankan'] == 'no' ?  $sh['passport'] : null;
                      $member->passport_issued_country =  $sh['is_srilankan'] == 'no' ?  $sh['passport_issued_country'] : null;
                      $member->save();

                      if( !( isset($sh['id'] ) && intval($sh['id'])) ){
                        $shIds[] = $member->id;
                      }
              }
              if(count($shIds)) {
                TenderApplyMember::where('application_id',$id)
                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                  ->whereNotIn('id', $shIds)
                  ->delete();
              }
       }

       $memberIds = array();
       if(isset($tender_info['tender_members']['member']) && is_array($tender_info['tender_members']['member']) && count($tender_info['tender_members']['member'])){

              foreach($tender_info['tender_members']['member'] as $m ){

                      if(isset($m['id'] ) && intval($m['id'])){
                        $memberIds[] = $m['id'];
                      }
                      
                      $member = (isset($m['id'] ) && intval($m['id'])) ?  TenderApplyMember::find(intval($m['id']) )  :  new TenderApplyMember();
                      $member->type =  $this->settings('TENDER_MEMBER_PARTNERS','key')->id;
                      $member->name = $m['name'];
                      $member->address = $m['address'];
                      $member->nationality =  $m['is_srilankan'] == 'no' ? $m['natianality'] : 'Sri Lanka';
                      $member->nationality_of_origin = $m['natianality_origin'];
                      $member->percentage_of_shares = $m['shares'];
                      $member->application_id = $tender_application_id;

                   //   if ( $request->tendererSubType != 'TENDER_JOIN_VENTURE' ) {

                        if(isset($m['is_srilankan'])) {
                                 $member->is_srilankan = $m['is_srilankan'] == 'no' ? 'no' : 'yes';
                        }

                        if(isset($m['nic']) && isset($m['is_srilankan'])) {
                                $member->nic =  $m['is_srilankan'] == 'yes' ?  $m['nic'] : null;
                        }

                        if(isset($m['is_srilankan']) && $m['passport']) {
                                $member->passport_no =  $m['is_srilankan'] == 'no' ?  $m['passport'] : null;
                        }

                        if(isset($m['is_srilankan']) && isset($m['passport_issued_country'])) {
                                $member->passport_issued_country =  $m['is_srilankan'] == 'no' ?  $m['passport_issued_country'] : null;
                        }
                              
                              //$member->is_srilankan = $m['is_srilankan'] == 'no' ? 'no' : 'yes';
                              //$member->nic =  $m['is_srilankan'] == 'yes' ?  $m['nic'] : null;
                              //$member->passport_no =  $m['is_srilankan'] == 'no' ?  $m['passport'] : null;
                              //$member->passport_issued_country =  $m['is_srilankan'] == 'no' ?  $m['passport_issued_country'] : null;
                   //   }

                      $member->save();

                      if( !( isset($m['id'] ) && intval($m['id'])) ){
                        $memberIds[] = $member->id;
                      }
              }

              if(count($memberIds)) {

                TenderApplyMember::where('application_id',$id)
                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                        ->whereNotIn('id', $memberIds)
                        ->delete();

              }
       }

       /** join venture company */

   

       if ( $request->tendererSubType == 'TENDER_JOIN_VENTURE' ) {

        $jv_companies = $request->jv_companies;
        $jvCompanyIds = array();

    
        if(isset($jv_companies['companies']) && is_array($jv_companies['companies']) && count($jv_companies['companies'])) {

                foreach($jv_companies['companies'] as $company ) {

                        if(isset($company['id'] ) && intval($company['id'])){
                                $jvCompanyIds[] = $company['id'];
                        }
                        $company_record = (isset($company['id'] ) && intval($company['id'])) ?  JvCompanies::find(intval($company['id']) )  :  new JvCompanies();
                        if(! ( isset($company['id'] ) && intval($company['id']) ) ){
                                $company_record->application_id = $tender_application_id;
                        }
                        $company_record->name = $company['name'];
                        $company_record->save();

                        if(! ( isset($company['id'] ) && intval($company['id']) ) ){
                                $jvCompanyIds[] = $company_record->id;
                        }

                        
                }

                if(count($jvCompanyIds)) {
                        $br_doc = Documents::where('key', 'TENDER_COPY_OF_BR')->first(); 
                        JvCompanies::where('application_id',$tender_application_id)
                                ->whereNotIn('id', $jvCompanyIds)
                                ->delete();
                        TenderDocument::where('appication_id',$tender_application_id)
                        ->where('document_id', $br_doc->id )
                        ->whereNotIn('company_id', $jvCompanyIds)
                        ->delete();
                }
          }
        } else {
                $br_doc = Documents::where('key', 'TENDER_COPY_OF_BR')->first(); 
                JvCompanies::where('application_id',$tender_application_id)
                                ->delete(); 
                TenderDocument::where('appication_id',$tender_application_id)
                                ->where('document_id', $br_doc->id )
                                ->delete();
        }

        /** end join venture company */

       $download_list = $this->generate_files($applicant_type,$tender_application_id);

       $files_for_upload = $this->files_for_upload($applicant_type,$tender_application_id);
       


       $uploadedListArr = array();
       $uploadedListArrWithToken = array();
       $tenderApplicantId = $tender_application_id;


      $uploadedList =  TenderDocument::where('tender_id', $tender_id)
                                               ->where('appication_id',$tender_application_id)
                                               ->get();
      $uploadedCount = TenderDocument::where('tender_id', $tender_id)
               ->where('appication_id',$tender_application_id)
               ->count();

      if($uploadedCount){
        foreach($uploadedList as $item ){
       
              $uploadedListArr[$item->document_id] = basename($item->name);
       
        }
      }

      if($uploadedCount){
              foreach($uploadedList as $item ){
             
                    $uploadedListArrWithToken[$item->document_id] = $item->file_token;
             
              }
      }
     

        return response()->json([
              'message' => "Successfully applied the tender.",
              'status' =>true,
              'tender_items' => $tender_items,
              'applicant_type' => $applicant_type,
              'tender_info'  => $tender_info,
              'itemCost'  => $itemCost,
              'id' => $tender_application_id,
              'downloadDocs' => $download_list,
              'uploadDocs'   => $files_for_upload,
              'uploadOtherDocs' => $this->files_for_other_docs($tender_application_id),
              'uploadedList' => $uploadedListArr,
              'uploadedListArrWithToken' => $uploadedListArrWithToken



              ], 200);
      

   }



     function resubmitTender( Request $request ) {

        $tender_items = $request->selectedItems;
        $applicant_type = $request->applicantType;
        $tenderer_sub_type = $request->tendererSubType;
        $tender_info = $request->applicntRecord;
        $tender_id = $request->tenderId;
        $id = intval( $request->id );

      //  print_r($tender_info['tender_directors']['directors']);
      //  print_r($tenderInfo['tender_shareholders']['shareholder']);
      //  die();

        $itemCost = 0 ;

        foreach( $tender_items as $item ){

              $item = TenderItem::where('id', $item)->first();
              $itemCost += (float)$item->cost;
        }

        if(isset($id) && $id ){
              $tender_apply = TenderApplication::find($id );
        }else{
              $tender_apply = new TenderApplication();
         }
     
  


       $tender_apply->tender_id = $tender_id;
       $tender_apply->applicant_type = $this->settings( $applicant_type,'key')->id;
       $tender_apply->applicant_sub_type = ($request->applicantSubType) ? $this->settings( $request->applicantSubType,'key')->id : null;
       $tender_apply->tenderer_sub_type = ($tenderer_sub_type) ? $this->settings( $tenderer_sub_type,'key')->id : null;
       $tender_apply->amount = $itemCost;
       $tender_apply->is_srilankan = ( $tender_info['is_srilankan'] == 'Srilankan' ) ? 'yes' : 'no';
       $tender_apply->is_applying_from_srilanka = ( $tender_info['apply_from'] == '' || $tender_info['apply_from'] == 'Srilanka' ) ? 'yes' : 'no';
       $tender_apply->applicant_fullname = isset($tender_info['applicant_name']) ? $tender_info['applicant_name'] : null;
       $tender_apply->applicant_address = ( $tender_info['applicant_address']) ? $tender_info['applicant_address'] : null;
       $tender_apply->applicant_email = isset($tender_info['appliant_email']) ? $tender_info['appliant_email'] : null;
       $tender_apply->signature_name = isset($tender_info['signing_party_name']) ? $tender_info['signing_party_name'] : null;
       $tender_apply->signature_designation = isset($tender_info['signing_party_designation']) ? $tender_info['signing_party_designation'] : null;
       $tender_apply->signature_other_designation = isset($tender_info['signing_party_designation']) && $tender_info['signing_party_designation'] == 'Other' ? $tender_info['signing_party_designation_other'] : null;
        
       $tender_apply->applicant_nic = ( $tender_info['is_srilankan'] == 'Srilankan' ) ? $tender_info['nic'] : null;
       $tender_apply->applicant_passport = ( $tender_info['is_srilankan'] != 'Srilankan' ) ? $tender_info['passport'] : null;

       $tender_apply->is_tenderer_srilankan = isset($tender_info['is_tenderer_srilankan']) ? (( $tender_info['is_tenderer_srilankan'] == 'Srilankan' ) ? 'yes' : 'no')  : null;
       $tender_apply->tenderer_nic = isset($tender_info['tenderer_nic']) && $tender_info['tenderer_nic'] ? $tender_info['tenderer_nic'] : null;
       $tender_apply->tenderer_passport = isset($tender_info['tenderer_passport']) && $tender_info['tenderer_passport'] ? $tender_info['tenderer_passport'] : null;
       
       if($applicant_type =='TENDER_TENDERER') {
                $tender_apply->applicant_fullname = isset($tender_info['tenderer_name']) ? $tender_info['tenderer_name'] : null;
                $tender_apply->applicant_address = ( $tender_info['tenderer_address']) ? $tender_info['tenderer_address'] : null;
                $tender_apply->applicant_nationality = ($tender_info['tenderer_natianality']) ?  $tender_info['tenderer_natianality'] : null;
        }
       
       $tender_apply->applicant_nationality = ($tender_info['applicant_natianality']) ?  $tender_info['applicant_natianality'] : null;
       $tender_apply->tenderer_fullname =  ($tender_info['tenderer_name'] ) ? $tender_info['tenderer_name'] : null;
       $tender_apply->tenderer_address = ($tender_info['tenderer_address'] ) ? $tender_info['tenderer_address'] : null;
       $tender_apply->tenderer_nationality = ($tender_info['tenderer_natianality'] ) ? $tender_info['tenderer_natianality'] : null;
       $tender_apply->registration_number = ($tender_info['tender_company_reg_no']) ? $tender_info['tender_company_reg_no'] : ''; 
      // $tender_apply->status = $this->settings( 'TENDER_PENDING','key')->id;
      
       $tender_apply->save();

       $tender_application_id = ( isset($id) && $id ) ? $id : $tender_apply->id;

      /* if(( isset($id) && $id )){
               //remove all items if update works
              TenderApplicantItem::where('tender_application_id',$id)->delete();
       }
       foreach( $tender_items as $item ){ // add tender items
              $tenderitem = new TenderApplicantItem();
              $tenderitem->tender_application_id = $tender_application_id;
              $tenderitem->tender_item_id = $item;
              $tenderitem->status = $this->settings( 'TENDER_PENDING','key')->id;
              $tenderitem->save();
       }*/


       if(( isset($id) && $id )){
              //remove all members if update works
              TenderApplyMember::where('application_id',$id)->delete();
       }

       if(isset($tender_info['tender_directors']['directors']) && is_array($tender_info['tender_directors']['directors']) && count($tender_info['tender_directors']['directors'])){

              foreach( $tender_info['tender_directors']['directors'] as $director ){
                      
                      $member = new TenderApplyMember();
                      $member->type =  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id;
                      $member->name = $director['name'];
                      $member->address = $director['address'];
                      $member->nationality =  $director['is_srilankan'] == 'no' ? $director['natianality'] : 'Sri Lanka';
                      $member->nationality_of_origin = $director['natianality_origin'];
                      $member->percentage_of_shares = $director['shares'];
                      $member->is_srilankan = $director['is_srilankan'] == 'no' ? 'no' : 'yes';
                      $member->nic =  $director['is_srilankan'] == 'yes' ?  $director['nic'] : null;
                      $member->passport_no =  $director['is_srilankan'] == 'no' ?  $director['passport'] : null;
                      $member->passport_issued_country =  $director['is_srilankan'] == 'no' ?  $director['passport_issued_country'] : null;
                      $member->application_id = $tender_application_id;
                      $member->save();

                      $newMemberId = $member->id;


                      $update_doc_member = array(
                              'member_id' => $newMemberId
                      );
                      
                      if(isset($director['id']) && $director['id'] ) {
                        
                        $c = TenderDocument::where('tender_id', $tender_id)
                        ->where('appication_id',$tender_application_id)
                        ->where('member_id', $director['id'])
                        ->count();

                        if( $c) {
                                TenderDocument::where('tender_id', $tender_id)
                                ->where('appication_id',$tender_application_id)
                                ->where('member_id', $director['id'])
                                ->update($update_doc_member);

                        }

                       
                      }
                     
 

              }
       }

       //remove all members 


       if(isset($tender_info['tender_shareholders']['shareholder']) && is_array($tender_info['tender_shareholders']['shareholder']) && count($tender_info['tender_shareholders']['shareholder'])){

              foreach($tender_info['tender_shareholders']['shareholder'] as $sh ){
                      
                      $member = new TenderApplyMember();
                      $member->type =  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id;
                      $member->name = $sh['name'];
                      $member->address = $sh['address'];
                      $member->nationality =  $sh['is_srilankan'] == 'no' ? $sh['natianality'] : 'Sri Lanka';
                      $member->nationality_of_origin = $sh['natianality_origin'];
                      $member->percentage_of_shares = $sh['shares'];
                      $member->is_srilankan = $sh['is_srilankan'] == 'no' ? 'no' : 'yes';
                      $member->nic =  $sh['is_srilankan'] == 'yes' ?  $sh['nic'] : null;
                      $member->passport_no =  $sh['is_srilankan'] == 'no' ?  $sh['passport'] : null;
                      $member->passport_issued_country =  $sh['is_srilankan'] == 'no' ?  $sh['passport_issued_country'] : null;
                      $member->application_id = $tender_application_id;
                      $member->save();

                      $newMemberId = $member->id;

                      $update_doc_member = array(
                              'member_id' => $newMemberId
                      );
                      if(isset($sh['id']) && $sh['id'] ) {

                        $c =  TenderDocument::where('tender_id', $tender_id)
                        ->where('appication_id',$tender_application_id)
                        ->where('member_id', $sh['id'])
                        ->count();

                        if($c){
                                TenderDocument::where('tender_id', $tender_id)
                                ->where('appication_id',$tender_application_id)
                                ->where('member_id', $sh['id'])
                                ->update($update_doc_member);
                        }
                        
                      }
                     
              }
       }

       if(isset($tender_info['tender_members']['member']) && is_array($tender_info['tender_members']['member']) && count($tender_info['tender_members']['member'])){

              foreach($tender_info['tender_members']['member'] as $m ){
                      
                      $member = new TenderApplyMember();
                      $member->type =  $this->settings('TENDER_MEMBER_PARTNERS','key')->id;
                      $member->name = $m['name'];
                      $member->address = $m['address'];
                      $member->nationality =  $m['is_srilankan'] == 'no' ? $m['natianality'] : 'Sri Lanka';
                      $member->nationality_of_origin = $m['natianality_origin'];
                      $member->percentage_of_shares = $m['shares'];

                      if ( $request->applicantSubType != 'TENDER_JOIN_VENTURE' ) {
                                
                        $member->is_srilankan = $m['is_srilankan'] == 'no' ? 'no' : 'yes';
                        $member->nic =  $m['is_srilankan'] == 'yes' ?  $m['nic'] : null;
                        $member->passport_no =  $m['is_srilankan'] == 'no' ?  $m['passport'] : null;
                        $member->passport_issued_country =  $m['is_srilankan'] == 'no' ?  $m['passport_issued_country'] : null;
                }


                      $member->application_id = $tender_application_id;
                      $member->save();

                      $newMemberId = $member->id;


                      $update_doc_member = array(
                              'member_id' => $newMemberId
                      );

                      if( isset($m['id'])  && $m['id'] ) {

                        $c =  TenderDocument::where('tender_id', $tender_id)
                        ->where('appication_id',$tender_application_id)
                        ->where('member_id', $m['id'])
                        ->count();

                        if( $c ) {
                                TenderDocument::where('tender_id', $tender_id)
                                ->where('appication_id',$tender_application_id)
                                ->where('member_id', $m['id'])
                                ->update($update_doc_member);
                        }
                        
                      }
                     
              }
       }

       $download_list = $this->generate_files($applicant_type,$tender_application_id);

       $files_for_upload = $this->files_for_upload($applicant_type,$tender_application_id);


       $uploadedListArr = array();
       
       $uploadedListArrWithToken = array();
       $tenderApplicantId = $tender_application_id;



        return response()->json([
              'message' => "Successfully applied the tender.",
              'status' =>true,
              'tender_items' => $tender_items,
              'applicant_type' => $applicant_type,
              'tender_info'  => $tender_info,
              'itemCost'  => $itemCost,
              'id' => $tender_application_id,
              'downloadDocs' => $download_list,
              'uploadDocs'   => $files_for_upload,
              'uploadedList' => $uploadedListArr,
              'uploadedListArrWithToken' => $uploadedListArrWithToken,
              'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$tender_application_id),
              'uploadOtherDocs' => $this->files_for_other_docs($tender_application_id),



              ], 200);
      

   }


     function documents($is_resubmission=false){
        $docs = array();

        $private_public_companies = array('TENDER_COMPANY_PRIVATE', 'TENDER_COMPANY_PUBLIC');       
        foreach($private_public_companies as $type ){

        $docs[$type] = array(

                        'download' => array(
                                array('name' =>'PCA 01', 'savedLocation' => "", 'view'=>'pca1', 'specific' =>'','file_name_key' =>'pca1' ),
                        ),
                        'upload'   => array( 
                                array( 'dbid' => 1,'name' => 'PCA 1','required' =>  true ,'type'   =>'pca1','fee' => 0,'uploaded_path' => '','comments' =>''),

                        )
        
                );

        }

        $prop_and_partnership = array('TENDER_PROPRIETORSHIP', 'TENDER_PARTNERSHIP');

        foreach($prop_and_partnership as $type ){

                $docs[$type] = array(

                        'download' => array(
                                array('name' =>'PCA 01', 'savedLocation' => "", 'view'=>'pca1', 'specific' =>'','file_name_key' =>'pca1' ),
                                ),
                                'upload'   => array( 
                                array( 'dbid' => 1,'name' => 'PCA 1','required' =>  true ,'type'   =>'pca1','fee' => 0,'uploaded_path' => '','comments' =>''),
                                array( 'dbid' => 2,'name' => 'Copy of Business Registration','required' =>  true ,'type'   =>'copy-of-business-registration','fee' => 0,'uploaded_path' => '','comments' =>'' ),
                        
                                )
                
                        );
        
        }

        $joint_venture = array('TENDER_JOIN_VENTURE');
        foreach($joint_venture as $type ){

                $docs[$type] = array(

                        'download' => array(
                                array('name' =>'PCA 01', 'savedLocation' => "", 'view'=>'pca1', 'specific' =>'','file_name_key' =>'pca1' ),
                                ),
                                'upload'   => array( 
                                array( 'dbid' => 1,'name' => 'PCA 1','required' =>  true ,'type'   =>'pca1','fee' => 0,'uploaded_path' => '','comments' =>''),
                                array( 'dbid' => 3,'name' => 'Joint Venture Agreement Letter','required' =>  true ,'type'   =>'joint-venture-agreement-letter','fee' => 0,'uploaded_path' => '','comments' =>'' ),
                                array( 'dbid' => 4,'name' => 'Authorization Letter','required' =>  true ,'type'   =>'auth-lettere','fee' => 0,'uploaded_path' => '','comments' =>'' ),
                        
                                )
                
                        );
        
        }

        $agent = array('TENDER_AGENT');
        foreach($agent as $type ){

                $docs[$type] = array(

                        'download' => array(
                                array('name' =>'PCA 01', 'savedLocation' => "", 'view'=>'pca1', 'specific' =>'','file_name_key' =>'pca1' ),
                                ),
                                'upload'   => array( 
                                array( 'dbid' => 1,'name' => 'PCA 1','required' =>  true ,'type'   =>'pca1','fee' => 0,'uploaded_path' => '','comments' =>''),
                                array( 'dbid' => 4,'name' => 'Authorization Letter','required' =>  true ,'type'   =>'auth-lettere','fee' => 0,'uploaded_path' => '','comments' =>'' ),
                                array( 'dbid' => 5,'name' => 'PCA 10','required' =>  true ,'type'   =>'pca10','fee' => 0,'uploaded_path' => '','comments' =>'' ),

                        
                                )
                
                        );
        
        }

        $tenderer = array('TENDER_TENDERER');
        foreach($tenderer as $type ){

                $docs[$type] = array(

                        'download' => array(
                                array('name' =>'PCA 01', 'savedLocation' => "", 'view'=>'pca1', 'specific' =>'','file_name_key' =>'pca1' ),
                                ),
                                'upload'   => array( 
                                array( 'dbid' => 1,'name' => 'PCA 1','required' =>  true ,'type'   =>'pca1','fee' => 0,'uploaded_path' => '','comments' =>''),
                                array( 'dbid' => 6,'name' => 'NIC Cpopy','required' =>  true ,'type'   =>'nic-copy','fee' => 0,'uploaded_path' => '','comments' =>'' ),
                               

                        
                                )
                
                        );
        
        }

        return $docs;

   }

   private function getDocs($doc_type){

        $docs = $this->documents();

        return isset(  $docs[$doc_type] ) ?   $docs[$doc_type]  : false;

    }

   function generate_files($doc_type,$tender_applicant_id){


      //  $docs = $this->getDocs($doc_type );

     //   $downloaded = $docs['download'];

        $generated_files = array(

            'docs' => array(),

        );
     //   return  $generated_files;
       // $pdff = App::make('dompdf.wrapper');
       

      //  if(count($downloaded)){
         //   foreach($downloaded as $file ){

                $file_name_key = 'pca1';
                $file_name = 'PCA 01';

                
                $tender_application = TenderApplication::where('id', $tender_applicant_id)->first();
                
                $tender_items = TenderApplicantItem::where('tender_application_id',$tender_applicant_id)
                                ->whereNull('token')
                                ->get();

                $director_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                 ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                                ->get();
                
                


                $director_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                               ->count();
                $directorListArr = array();

                if($director_list_count) {
                        foreach($director_list as $d ){
                                $row = array();
                                $row['name'] = $d->name;
                                $row['address'] = $d->address;
                                $row['nationality'] = $d->nationality;
                                $row['nationality_of_origin'] = $d->nationality_of_origin;


                               if( $d->is_srilankan == 'yes') {

                                 $director_shareholder_count =  TenderApplyMember::where('application_id',$tender_applicant_id)
                                 ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                                 ->where('nic', $d->nic)
                                  ->count();

                                  if($director_shareholder_count ){
                                        $director_shareholder =  TenderApplyMember::where('application_id',$tender_applicant_id)
                                        ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                                        ->where('nic', $d->nic)
                                         ->first();
                                         $row['percentage_of_shares'] = $director_shareholder->percentage_of_shares;

                                  }else {
                                        $row['percentage_of_shares'] = '';
                                  }
                               }
                               if( $d->is_srilankan == 'no') {

                                $director_shareholder_count =  TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                                ->where('passport_no', $d->passport_no)
                                 ->count();

                                 if($director_shareholder_count ){
                                       $director_shareholder =  TenderApplyMember::where('application_id',$tender_applicant_id)
                                       ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                                       ->where('nic', $d->nic)
                                        ->first();
                                        $row['percentage_of_shares'] = $director_shareholder->percentage_of_shares;

                                 }else {
                                       $row['percentage_of_shares'] = '';
                                 }
                               }


                               $directorListArr[] = $row;

                        }
                }
                               

                $shareholder_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                               ->get(); 
                $shareholder_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                              ->count();
                                     
                $member_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                              ->get();  
                $member_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                              ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                             ->count(); 
     
                
          foreach($tender_items as $tender_item ) {

                $itemInfo = TenderItem::where('id', $tender_item->tender_item_id )->first();
                $tenderInfo = Tender::where('id',  $itemInfo->tender_id )->first();
                

              

                $data = array(
                        'public_path' => public_path(),
                        'eroc_logo' => url('/').'/images/forms/eroc.png',
                        'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                        'css_file' => url('/').'/images/forms/form1/form1.css',
                        'tender_application' => $tender_application,
                        'applicant_main_type' => $this->settings( $tender_application->applicant_type,'id')->key,
                        'applicant_type'   => ($tender_application->applicant_type === $this->settings( 'TENDER_TENDERER','key')->id) ? $this->settings( $tender_application->tenderer_sub_type,'id')->key  : $this->settings( $tender_application->applicant_sub_type,'id')->key,
                        'tenderer_sub_type' => isset($tender_application->tenderer_sub_type) && $tender_application->tenderer_sub_type ? $tender_application->tenderer_sub_type : '',
                        'tender_items' => $itemInfo,
                        'tender_info' => $tenderInfo,
                        'director_list' => $directorListArr,
                        'director_list_count' => $director_list_count,
                        'shareholder_list' => $shareholder_list,
                        'shareholder_list_count'=> $shareholder_list_count,
                        'member_list' => $member_list,
                        'member_list_count' => $member_list_count
                    );
                    
                        $directory = "tender-apply/$tender_applicant_id";
                        Storage::makeDirectory($directory);

                        $item_name = $itemInfo->name;
                        $item_id = $itemInfo->id;
        
                        $view = 'forms.'.'pca1';
                        $pdf = PDF::loadView($view, $data);
                        $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$tender_applicant_id.'-'.$item_id.'.pdf');

                        $file_row = array();
                        
                        $file_row['name'] = $file_name.' for '.$item_name;
                        $file_row['file_name_key'] = $file_name_key.'pdf';
                        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$tender_applicant_id-$item_id.pdf");
                        $generated_files['docs'][] = $file_row;
  
            } //end foreach of items

            $applicant_type = $this->settings($tender_application->applicant_type,'id')->key;
             if($applicant_type != 'TENDER_TENDERER') {

                        $file_name_key = 'auth-letter.docx';
                        $file_name = 'Authorization Letter (PCA 10)';
                     

                        $file_row = array();
                        
                        $file_row['name'] = $file_name;
                        $file_row['file_name_key'] = $file_name_key;
                        $file_row['download_link']  = asset('other/PCA10.docx');
                        $generated_files['docs'][] = $file_row;

            }

         
      //  }
        
        return $generated_files;
    }


    function rrmdir($dir) { 
        if (is_dir($dir)) { 
          $objects = scandir($dir); 
          foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
              if (is_dir($dir."/".$object))
                $this->rrmdir($dir."/".$object);
              else
                unlink($dir."/".$object); 
            } 
          }
          rmdir($dir); 
        } 
    }

    function checkpca1(){

        $tender_applicant_id = 27;

        $tender_application = TenderApplication::where('id', $tender_applicant_id)->first();
                
                $tender_items = TenderApplicantItem::where('tender_application_id',$tender_applicant_id)->get();

                $director_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                 ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                                ->get();
                $director_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                               ->count();

                $shareholder_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                               ->get(); 
                $shareholder_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                              ->count();
                                     
                $member_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                              ->get();  
                $member_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                              ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                             ->count(); 
     
                $data = array(
                        'public_path' => public_path(),
                        'eroc_logo' => url('/').'/images/forms/eroc.png',
                        'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                        'css_file' => url('/').'/images/forms/form1/form1.css',
                        'tender_application' => $tender_application,
                        'applicant_type'   => $this->settings( $tender_application->applicant_type,'id')->key,
                        'tender_items' => $tender_items,
                        'director_list' => $director_list,
                        'director_list_count' => $director_list_count,
                        'shareholder_list' => $shareholder_list,
                        'shareholder_list_count'=> $shareholder_list_count,
                        'member_list' => $member_list,
                        'member_list_count' => $member_list_count
                    );

       // print_r($stakeholder_store['secs'] );

        return view('forms.pca1', $data);


    }


    /*function files_for_upload($doc_type,$tender_applicant_id){

        $docs = $this->getDocs($doc_type );

        $uploaded = $docs['upload'];

        $generated_files = array(
                'docs' => array(),
        );

       // $document_resubmit_status = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
      //  $document_request_status = $this->settings('DOCUMENT_REQUESTED','key')->id;
      //  $company_info = Company::where('id',$companyId)->first();
      //  $company_status = $this->settings($company_info->status,'id')->key;
        
     

        if(count($uploaded)){
           
            foreach($uploaded as $file ){
                  
                $name = $file['name'];
            
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
        
                $file_row['is_required'] = $file['required'];
                $file_row['file_name'] = $file['name'];
                $file_row['file_type'] = $file['type'];
                $file_row['dbid'] = $file['dbid'];

                $generated_files['docs'][] = $file_row;

           
            }
        }

        return $generated_files;
    
    }*/

    function files_for_upload($doc_type,$tender_applicant_id){

       // $docs = $this->getDocs($doc_type );

      //  $uploaded = $docs['upload'];

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'applied_expired_item' => false,
                'doc_id' => 0
              
        );

        if(!$tender_applicant_id) {
                return array(
                    'docs' => array(),
                    'uploadedAll' => false,
                    'applied_expired_item' => false,
                    'doc_id' => 0
            );
            }

        $tender_application = TenderApplication::where('id', $tender_applicant_id)->first();

        $status =  $this->settings($tender_application->status,'id')->key;

                
        $tender_items = TenderApplicantItem::where('tender_application_id',$tender_applicant_id)
        ->whereNull('token')->get();

        $director_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                 ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                                ->get();
        $director_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                               ->count();

        $shareholder_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                               ->get(); 
        $shareholder_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                              ->count();
                                     
        $member_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                              ->get();
                              
                             
        $member_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                              ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                             ->count(); 

        // documents list
        $pca_form_info = Documents::where('key', 'TENDER_PCA1')->first();
        $special_letter_doc = Documents::where('key', 'TENDER_LETTER_OF_APPROVAL')->first(); // when item expired
        $br_doc = Documents::where('key', 'TENDER_COPY_OF_BR')->first(); 
        $jv_agreement_letter = Documents::where('key', 'TENDER_JV_AGREEMENT_LETTER')->first(); 
        $nic_doc = Documents::where('key', 'TENDER_COPY_OF_NIC')->first();
        $tenderer_nic_doc = Documents::where('key', 'TENDER_TENDERER_COPY_OF_NIC')->first();
        $auth_letter = Documents::where('key', 'TENDER_AUTHORIZATION_LETTER')->first();
        $passport_doc =  Documents::where('key', 'TENDER_COPY_OF_PASSPORT')->first();
        $tenderer_passport_doc =  Documents::where('key', 'TENDER_TENDERER_COPY_OF_PASSPORT')->first();
        $visa_doc =  Documents::where('key', 'TENDER_COPY_OF_VISA')->first();
        $tenderer_visa_doc =  Documents::where('key', 'TENDER_TENDERER_COPY_OF_VISA')->first();
        $form_20 =  Documents::where('key', 'FORM_20')->first();
        $form_15 =  Documents::where('key', 'FORM_15')->first();
        $form_6 =  Documents::where('key', 'FORM_6')->first();
        $form_auth_not_director =  Documents::where('key', 'TENDER_NO_DIRECTOR_AUTHORIZATION_LETTER')->first();

        $applicant_type = $this->settings($tender_application->applicant_type,'id')->key;
        $applicant_sub_type =  isset($tender_application->applicant_sub_type) && $tender_application->applicant_sub_type  ? $this->settings($tender_application->applicant_sub_type,'id')->key : '';
        $tenderer_sub_type =  isset($tender_application->tenderer_sub_type) && $tender_application->tenderer_sub_type  ? $this->settings($tender_application->tenderer_sub_type,'id')->key : '';
        $tenderer_sub_type = $this->settings($tender_application->tenderer_sub_type,'id')->key;
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';


        foreach($tender_items as $tender_item ) {
                $itemInfo = TenderItem::where('id', $tender_item->tender_item_id )->first();
                $tenderInfo = Tender::where('id',  $itemInfo->tender_id )->first();

            
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $pca_form_info->name. ' for ' . $itemInfo->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $pca_form_info->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] = $tender_item->id;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $pca_form_info->id )
                                        ->where('application_item_id', $tender_item->id)
                                        ->orderBy('id', 'DESC')
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                $today = time();
                $to  = strtotime($itemInfo->to_time);

                if($today > $to &&  $applicant_type === 'TENDER_TENDERER') {
 
                        $generated_files['applied_expired_item'] = true;

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        $file_row['is_required'] = true;
                        $file_row['file_name'] = $special_letter_doc->name. ' for ' . $itemInfo->name;
                        $file_row['file_type'] = '';
                        $file_row['dbid'] = $special_letter_doc->id;
                        $file_row['file_description'] = $special_letter_doc->description;
                        $file_row['applicant_item_id'] = $tender_item->id;
                        $file_row['member_id'] = null;
                        $file_row['company_id'] = null;
                        $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $special_letter_doc->id )
                                        ->where('application_item_id', $tender_item->id)
                                        ->orderBy('id', 'DESC')
                                        ->first();
                       
                        $uploadeDocStatus = @$uploadedDoc->status;
                        if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                         }

                        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
                }



        }

        if($applicant_sub_type === 'TENDER_PROPRIETORSHIP' ||
           $tenderer_sub_type === 'TENDER_PROPRIETORSHIP' ) {
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $br_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $br_doc->id;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $br_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
        }



        /*if($applicant_sub_type === 'TENDER_COMPANY_PUBLIC' || $applicant_sub_type === 'TENDER_COMPANY_PRIVATE' ) {
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $form_20->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $form_20->id;
                $file_row['file_description'] = $form_20->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $form_20->id )
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
                                                           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                           ->where('comment_type', 'external')
                                                           ->first();

                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }

                $file_row['uploaded_path'] =  isset($uploadedDoc->file_token)  &&
                                              isset($uploadedDoc->path ) &&
                                              isset($uploadedDoc->name) &&
                                              $uploadedDoc->file_token &&
                                              $uploadedDoc->path &&
                                              $uploadedDoc->name ? $uploadedDoc->name : '';
                $file_row['uploaded_token'] =  isset($uploadedDoc->file_token)  &&
                                              isset($uploadedDoc->path ) &&
                                              isset($uploadedDoc->name) &&
                                              $uploadedDoc->file_token &&
                                              $uploadedDoc->path &&
                                              $uploadedDoc->name ? $uploadedDoc->file_token : '';

               // $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );
                $has_all_uploaded_str = $has_all_uploaded_str.'1';
                $generated_files['docs'][] = $file_row;
        }*/


        /*if($applicant_sub_type === 'TENDER_COMPANY_PRIVATE' ) {

                $form_15_or_6_uploaded = false;


                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $form_15->name;
                $file_row['file_type'] = '';
                $file_row['file_description'] = $form_15->description;
                $file_row['dbid'] = $form_15->id;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $form_15->id )
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
                                                           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                           ->where('comment_type', 'external')
                                                           ->first();

                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }

                $file_row['uploaded_path'] =  isset($uploadedDoc->file_token)  &&
                                              isset($uploadedDoc->path ) &&
                                              isset($uploadedDoc->name) &&
                                              $uploadedDoc->file_token &&
                                              $uploadedDoc->path &&
                                              $uploadedDoc->name ? $uploadedDoc->name : '';
                $file_row['uploaded_token'] =  isset($uploadedDoc->file_token)  &&
                                              isset($uploadedDoc->path ) &&
                                              isset($uploadedDoc->name) &&
                                              $uploadedDoc->file_token &&
                                              $uploadedDoc->path &&
                                              $uploadedDoc->name ? $uploadedDoc->file_token : '';

                $has_uploaded_form_15 =   $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== ''  ;

                //$has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );
               
                $generated_files['docs'][] = $file_row;



                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $form_6->name;
                $file_row['file_type'] = '';
                $file_row['file_description'] = $form_6->description;
                $file_row['dbid'] = $form_6->id;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $form_6->id )
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
                                                           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                           ->where('comment_type', 'external')
                                                           ->first();

                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }

                $file_row['uploaded_path'] =  isset($uploadedDoc->file_token)  &&
                                              isset($uploadedDoc->path ) &&
                                              isset($uploadedDoc->name) &&
                                              $uploadedDoc->file_token &&
                                              $uploadedDoc->path &&
                                              $uploadedDoc->name ? $uploadedDoc->name : '';
                $file_row['uploaded_token'] =  isset($uploadedDoc->file_token)  &&
                                              isset($uploadedDoc->path ) &&
                                              isset($uploadedDoc->name) &&
                                              $uploadedDoc->file_token &&
                                              $uploadedDoc->path &&
                                              $uploadedDoc->name ? $uploadedDoc->file_token : '';

                $has_uploaded_form_6 =   $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== ''  ;

                //$has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );
               
                $generated_files['docs'][] = $file_row;

                // $form_15_or_6_uploaded =  $has_uploaded_form_15 || $has_uploaded_form_6;
                $form_15_or_6_uploaded =  true;
                $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $form_15_or_6_uploaded ) );



        }*/



        if( $applicant_sub_type === 'TENDER_JOIN_VENTURE' ||
            $tenderer_sub_type === 'TENDER_JOIN_VENTURE' ) {
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $jv_agreement_letter->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $jv_agreement_letter->id;
                $file_row['file_description'] = $jv_agreement_letter->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $jv_agreement_letter->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                $company_list =  JvCompanies::where('application_id',$tender_applicant_id)->get();
                if(isset($company_list[0]->id)) {

                        foreach($company_list as $company ) { // br copies of each members

                                $file_row = array();
                                $file_row['doc_comment'] = '';
                                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                                $file_row['is_required'] = true;
                                $file_row['file_name'] = $br_doc->name . ' for ' . $company->name;
                                $file_row['file_type'] = '';
                                $file_row['dbid'] = $br_doc->id;
                                $file_row['file_description'] = $br_doc->description;
                                $file_row['applicant_item_id'] = null;
                                $file_row['member_id'] = null;
                                $file_row['company_id'] = $company->id;
                                $file_row['uploaded_path'] = '';
        
                                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                                ->where('appication_id',$tender_applicant_id)
                                                ->where('document_id', $br_doc->id )
                                                ->where('company_id', $company->id )
                                                ->orderBy('id', 'DESC')
                                                ->first();
                                
                                $uploadeDocStatus = @$uploadedDoc->status;
                                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                                 }
        
                                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
        
                                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
                        }

                }
                

        }

        if($applicant_sub_type === 'TENDER_PARTNERSHIP' ||
            $tenderer_sub_type === 'TENDER_PARTNERSHIP' ) {
                
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $br_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $br_doc->id;
                $file_row['file_description'] = $br_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $br_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
        }

        if( $applicant_type != 'TENDER_TENDERER' && $applicant_sub_type === 'TENDER_INDIVIDUAL' ) {
                
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $nic_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $nic_doc->id;
                $file_row['file_description'] = $nic_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $nic_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();

                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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



               
        }

        if( $applicant_type != 'TENDER_TENDERER' && $tenderer_sub_type === 'TENDER_INDIVIDUAL' ) {


                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $tenderer_nic_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $tenderer_nic_doc->id;
                $file_row['file_description'] = $tenderer_nic_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $tenderer_nic_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();

                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
        }


        if( $applicant_type == 'TENDER_TENDERER' && $tenderer_sub_type === 'TENDER_INDIVIDUAL' ) {
                

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $tenderer_nic_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $tenderer_nic_doc->id;
                $file_row['file_description'] = $tenderer_nic_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $tenderer_nic_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();

                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
        }

        if($applicant_type != 'TENDER_TENDERER') {
                
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $auth_letter->name. '(When the tenderer and agent are different)';
                $file_row['file_type'] = '';
                $file_row['dbid'] = $auth_letter->id;
                $file_row['file_description'] = $auth_letter->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $auth_letter->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
        }

        if(
          $applicant_type != 'TENDER_TENDERER' &&  $applicant_sub_type === 'TENDER_INDIVIDUAL' && $tender_application->is_srilankan == 'no' && $tender_application->is_applying_from_srilanka == 'yes'
         
        ) {

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $passport_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $passport_doc->id;
                $file_row['file_description'] = $passport_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $passport_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $visa_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $visa_doc->id;
                $file_row['file_description'] = $visa_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';
                
                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $visa_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                
              

        }
        if($applicant_type != 'TENDER_TENDERER' && $tenderer_sub_type === 'TENDER_INDIVIDUAL' && $tender_application->is_tenderer_srilankan == 'no' && $tender_application->is_applying_from_srilanka == 'yes') {

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $tenderer_passport_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $tenderer_passport_doc->id;
                $file_row['file_description'] = $tenderer_passport_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $tenderer_passport_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $tenderer_visa_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $tenderer_visa_doc->id;
                $file_row['file_description'] = $tenderer_visa_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';
                
                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $tenderer_visa_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
              
                
              
        }


        if($applicant_type == 'TENDER_TENDERER' &&  $tenderer_sub_type === 'TENDER_INDIVIDUAL' && $tender_application->is_tenderer_srilankan == 'no' && $tender_application->is_applying_from_srilanka == 'yes') {

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $tenderer_passport_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $tenderer_passport_doc->id;
                $file_row['file_description'] = $tenderer_passport_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';

                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $tenderer_passport_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }

                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $tenderer_visa_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $tenderer_visa_doc->id;
                $file_row['file_description'] = $tenderer_visa_doc->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';
                
                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $tenderer_visa_doc->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
              
                
              
        }

        if($tender_application->signature_designation != 'Director') {

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $form_auth_not_director->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $form_auth_not_director->id;
                $file_row['file_description'] = $form_auth_not_director->description;
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['company_id'] = null;
                $file_row['uploaded_path'] = '';
                
                $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_applicant_id)
                                        ->where('document_id', $form_auth_not_director->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;
                if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                        $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
                 }
                if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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

        }



        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
      

        return $generated_files;
    
    }

    function files_for_other_docs($tender_applicant_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
        );
 
        if(!$tender_applicant_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $tender_application = TenderApplication::where('id', $tender_applicant_id)->first();

        $status =  $this->settings($tender_application->status,'id')->key;


      
        // documents list
        $form_other_docs = Documents::where('key', 'APPLYING_TENDER_ADDITIONAL_DOCUMENT')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

       
        $other_docs = TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id', $tender_applicant_id )
                                        ->where('document_id', $form_other_docs->id )
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
            if($status == 'TENDER_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                            $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
            }
            if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
    
                $commentRow = TenderDocumentStatus::where('tender_document_id', $docs->id )
                ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                ->where('comment_type', $external_comment_type_id )
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

    function removeTenderDoc(Request $request){
        $doc_type = $request->docType;
        $tender_id = $request->tenderId; 

        $directory = "tender-documents/$tender_id/$doc_type";
        Storage::deleteDirectory($directory);

        if($doc_type === 'datasheet'){
                $db_field = 'bid_data_sheet';

                $upload_save = array(
 
                        $db_field => null
                );
                Tender::where('id', $tender_id )->update($upload_save);
         }
         if($doc_type === 'advertisment'){
                 $db_field = 'paper_advertisement';
                 $upload_save = array(

                        'paper_advertisement' => null
                );
                $tenderDetails = Tender::where('id',$tender_id)->first();
                $publicaiton = TenderPublication::where('id', $tenderDetails->publication_id)->first();
                TenderPublication::where('id', $publicaiton->id )->update($upload_save);
         }
 
        


         return response()->json([
                'message' => 'Successfully remove the document.',
                'status' =>true,
                'error'  => 'no',
                
            ], 200);
    }


    function upload_tender_document(Request $request){

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $doc_type = $request->docType;
        $tender_id = $request->tenderId; 

       // print_r($request->file('uploadTenderFile'));
      //  die();
      
       
        $size = $request->file('uploadTenderFile')->getClientSize() ;
        $ext = $request->file('uploadTenderFile')->getClientMimeType();

        $allowed_mimes = array(
                'image/bmp',
                'image/gif',
                'image/jpeg',
                'image/svg+xml',
                'image/png',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/octet-stream'
        );

        if( !in_array($ext, $allowed_mimes) ){

         return response()->json([
             'message' => 'Please upload pdf/word/image document.',
             'status' =>false,
             'error'  => 'yes',
             'ext' => $ext
             
             
         ], 200);
        }

        if( $size > 1024 * 1024 * 4) {

         return response()->json([
             'message' => 'File size should be less than 4 MB.',
             'status' =>false,
             'error'  => 'yes'
             
             
         ], 200);
        }

        $directory = "tender-documents/$tender_id/$doc_type";
        Storage::deleteDirectory($directory);
        $path = Storage::putFile($directory, $request->file('uploadTenderFile'));
 
 
        $file_path = str_replace('public','',url('/')).Storage::url("app/$path");

        if($doc_type === 'datasheet'){
               $db_field = 'bid_data_sheet';
               $upload_save = array(

                'bid_data_sheet' => $file_path
        );
        Tender::where('id', $tender_id )->update($upload_save);
        }
        if($doc_type === 'advertisment'){
                $db_field = 'paper_advertisement';

                $upload_save = array(

                        'paper_advertisement' => $file_path,
                        'paper_advertisement_file_name' => $real_file_name
                );
                $tenderDetails = Tender::where('id',$tender_id)->first();
                $publicaiton = TenderPublication::where('id', $tenderDetails->publication_id)->first();
                TenderPublication::where('id', $publicaiton->id )->update($upload_save);
        }

     


       return response()->json([
         'message' => 'File uploaded successfully.',
         'status' =>true,
         'name' =>basename($path),
         'error'  => 'no',
         
     ], 200);
     }




    function upload(Request $request){

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $tender_id = $request->tenderId; 
        $applicant_id = (int) $request->applicantId;
        $item_id = (int) $request->itemId;
        $member_id = (int) $request->memberId;
        $company_id = (int) $request->companyId;
       

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

        $path = 'tender/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$applicant_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');

      
        $token = md5(uniqid());

        $get_query = TenderDocument::query();
        $get_query->where('tender_id', $tender_id );
        $get_query->where('appication_id', $applicant_id);
        $get_query->where('document_id',$file_type_id);
           
        if ($item_id) {
                $get_query->where('application_item_id', $item_id );
        }
        if ($member_id) {
                $get_query->where('member_id', $member_id );
        }
        if ($company_id) {
                $get_query->where('company_id', $company_id );
        }
        
        $old_doc_info = $get_query->first();

        $old_doc_id = isset($old_doc_info->id) && $old_doc_info->id ? $old_doc_info->id : null;
    
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;


        $query = TenderDocument::query();
        $query->where('tender_id', $tender_id );
        $query->where('appication_id', $applicant_id);
        $query->where('document_id',$file_type_id);

        if ($item_id) {
          $query->where('application_item_id', $item_id );
        }
        if ($member_id) {
         $query->where('member_id', $member_id );
        }
        if ($company_id) {
                $query->where('company_id', $company_id );
        }
        $query->whereIn('status', array($doc_pending,$doc_req_resumbit));
        $query->delete();
        

       $doc = new TenderDocument;
       $doc->document_id = $file_type_id;
       $doc->path = $path;
       $doc->tender_id = $tender_id;
       $doc->appication_id = $applicant_id;
       $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
       $doc->file_token = $token;
       $doc->name = $real_file_name;

       if ($item_id) {
          $doc->application_item_id = $item_id;
       }
       if ($member_id) {
          $doc->member_id = $member_id;
       }
       if ($company_id) {
        $doc->company_id = $company_id;
     }
       
       $doc->save();
       $new_doc_id = $doc->id;

       if( $old_doc_id ) { //update new doc id to old doc id in tenderdocument status row
         
         $update_new_id_info = array(
                 'tender_document_id' => $new_doc_id
         );
         $updated = TenderDocumentStatus::where('tender_document_id', $old_doc_id)->update($update_new_id_info);
       }


       $uploadedListArr = array();
       $uploadedListArrWithToken = array();


       return response()->json([
         'message' => 'File uploaded successfully.',
         'status' =>true,
         'name' =>basename($path),
         'error'  => 'no',
         'uploadedList' => $uploadedListArr,
         'uploadedListArrWithToken' => $uploadedListArrWithToken,
         'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$applicant_id),
         'uploadOtherDocs' => $this->files_for_other_docs($applicant_id)
         
     ], 200);
     }


     function uploadOtherDocs(Request $request){
        
        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $tender_id = $request->tenderId; 
        $applicant_id = (int) $request->applicantId;
        $file_description = $request->fileDescription;
  
        
  
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

        $path = 'tender/other-docs/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$applicant_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $doc = new TenderDocument;
        $doc->document_id = $file_type_id;
        $doc->path = $path;
        $doc->tender_id = $tender_id;
        $doc->appication_id = $applicant_id;
        $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $doc->multiple_id = mt_rand(1,1555400976);
        $doc->file_token = $token;
        $doc->name = $real_file_name;
        $doc->file_description = $file_description;

        $doc->save();
        $new_doc_id = $doc->id;

        $uploadedListArr = array();
        $uploadedListArrWithToken = array();

        return response()->json([
         'message' => 'File uploaded successfully.',
         'status' =>true,
         'name' =>basename($path),
         'error'  => 'no',
         'uploadedList' => $uploadedListArr,
         'uploadedListArrWithToken' => $uploadedListArrWithToken,
         'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$applicant_id),
         'uploadOtherDocs' => $this->files_for_other_docs($applicant_id)
         
        ], 200);
    
 
    

    }

    function uploadOtherResubmittedDocs(Request $request){
        

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $multiple_id = $request->multiple_id;
        $tender_id = $request->tenderId; 
        $applicant_id = (int) $request->applicantId;

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

        $path = 'tender/other-docs/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$applicant_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'APPLYING_TENDER_ADDITIONAL_DOCUMENT')->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
           );
           TenderDocument::where('tender_id', $tender_id)
           ->where('multiple_id', $multiple_id)
           ->where('document_id',$form_other_docs->id )
           ->where('appication_id',$applicant_id)
           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id)
            ->update($update_arr);
    
 
            $uploadedListArr = array();
            $uploadedListArrWithToken = array();
    
            return response()->json([
             'message' => 'File uploaded successfully.',
             'status' =>true,
             'name' =>basename($path),
             'error'  => 'no',
             'uploadedList' => $uploadedListArr,
             'uploadedListArrWithToken' => $uploadedListArrWithToken,
             'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$applicant_id),
             'uploadOtherDocs' => $this->files_for_other_docs($applicant_id)
             
            ], 200);
    

    }


     function removeFile( Request $request ){

        $tender_id = $request->tenderId;
        $applicant_id = $request->applicantId;
        $file_type_id = $request->fileTypeId;
        $item_id = $request->itemId;
        $member_id = $request->memberId;
        $company_id = intval($request->companyId);

        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;


        $query = TenderDocument::query();
        $query->where('tender_id', $tender_id );
        $query->where('appication_id', $applicant_id);
        $query->where('document_id',$file_type_id);

        if ($item_id) {
          $query->where('application_item_id', $item_id );
        }
        if ($member_id) {
         $query->where('member_id', $member_id );
        }
        if ($company_id) {
                $query->where('member_id', $company_id );
        }
        $query->where('status', $doc_pending);
        $query->delete();


       /* TenderDocument::where('tender_id', $tender_id)
                     ->where('appication_id',$applicant_id)
                     ->where('document_id', $file_type_id)
                     ->delete();*/


        $uploadedListArr = array();
       

        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        'uploadedList' => $uploadedListArr,
                        'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$applicant_id),
                        'uploadOtherDocs' => $this->files_for_other_docs($applicant_id)

        ], 200);

        
     }

     function removeOtherDoc(Request $request){

        $file_token = $request->file_token;
        
    
        TenderDocument::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
        ], 200);
    }

     function tenderApplyPay( Request $request ){

        $applicant_id = $request->applicantId;

        $update =  array(

                'status'    => $this->settings('TENDER_PENDING','key')->id
        );
      $updated = TenderApplication::where('id', $applicant_id)->update($update);

       //change status for applied items
       $item_update =  array(

        'status'    => $this->settings('TENDER_ITEM_APPLIED','key')->id
        );
       $updated = TenderApplicantItem::where('tender_application_id', $applicant_id )->update($item_update);

       
     

        if($updated){
                return response()->json([
                        'message' => 'Successfully paid.',
                        'status' =>true,
                      

                ], 200);

        } else {

                return response()->json([
                        'message' => 'Failed.',
                        'status' =>false

                 ], 200);
        }

     }

     function tenderResbumit( Request $request ){

        $applicant_id = $request->applicantId;

        $update =  array(
                 'token'    => null,
                'status'    => $this->settings('TENDER_RESUBMITTED','key')->id
        );
      $updated = TenderApplication::where('id', $applicant_id)->update($update);

       //change status for applied items

       $update_item =  array(
        'token'    => null,
       'status'    => $this->settings('TENDER_ITEM_APPLIED','key')->id
      );

       $updated = TenderApplicantItem::where('tender_application_id', $applicant_id )->update($update_item);

        if($updated){
                return response()->json([
                        'message' => 'Successfully paid.',
                        'status' =>true,
                      

                ], 200);

        } else {

                return response()->json([
                        'message' => 'Failed.',
                        'status' =>false

                 ], 200);
        }
     }

     /***********Get applied/applying tender ***********/
     
     function getTender( Request $request ){

        $tenderId = intval( $request->tenderId );

        $applicationId = isset( $request->tenderApplicantId ) ? intval($request->tenderApplicantId) : null;

        $user = $this->getAuthUser();

        $tenderInfo = Tender::where('id',$tenderId)->first();

        $tenderUsers = TenderUser::where('tender_id',$tenderId)->get();

        $tenderItems = TenderItem::where('tender_id', $tenderId )->get();

      //  $application_approved_status_id = $this->settings('TENDER_APPROVED','key')->id;


         $applicationInfo = array(
                'applicant_type' =>  null,
                'applicant_sub_type' => null,
                'tenderer_sub_type' => null,
                'is_srilankan' => '',
                'apply_from' => '',
                'tenderer_apply_from'=> '',
                'applicant_name' => '',
                'applicant_address'=> '',
                'applicant_natianality' => '',
                'appliant_email'=> '',
                'appliant_mobile'=> '',
                'signing_party_name'=> '',
                'signing_party_designation'=> '',
                'signing_party_designation_other' => '',
                'tenderer_name' => '',
                'tenderer_address' => '',
                'tenderer_natianality' => '',
                'tender_company_reg_no' => '',
                'tender_tenderer_company_reg_no'=> '',
                'tender_directors' => array(),
                'tender_shareholders' => array(),
                'tender_members' => array(),
                'nic'=> '',
                'passport'=> '',
                'is_tenderer_srilankan'=> '',
                'tenderer_nic'=> '',
                'tenderer_passport'=> '',
                'id'=> null

         );
         $applicantType = '';
         $applicantSubType = '';
         $tendererSubType = '';
         $tenderSelectedItemsIds = array();

         $jv_companies = array();
         $directorList = array();
         $directorListCount = 0;
         $shareholderList = array();
         $shareholderListCount = 0;
         $memberList = array();
         $memberListCount = 0;

         $applicationInfoCount = 0;
         if($applicationId) {
                $applicationInfoCount = TenderApplication::where('applied_by',$user->userid)->where('id', $applicationId)->count();

               
         }
       
        if($applicationInfoCount) {

                $applicationInfo1 = TenderApplication::where('applied_by',$user->userid)->where('id', $applicationId)->first();

                $update_updated_at = array(
                        'updated_at' => date('Y-m-d H:i:s', time())
                );
                TenderApplication::where('id', $applicationId)
                ->update($update_updated_at);


                if(
                      ! ( $applicationInfo1->status == $this->settings( 'TENDER_APPROVED','key')->id ||
                        $applicationInfo1->status == $this->settings( 'TENDER_REJECTED','key')->id )
                ) {
                        $applicationInfo = $applicationInfo1;

                        $applicantType = $this->settings( $applicationInfo->applicant_type ,'id')->key;
                        $applicantSubType =  @$this->settings( $applicationInfo->applicant_sub_type ,'id')->key;
                        $tendererSubType = @$this->settings( $applicationInfo->tenderer_sub_type ,'id')->key;

                        $tenderApplicantId = $applicationInfo->id;

                        $tenderSelectedItems = TenderApplicantItem::where('tender_application_id', $tenderApplicantId )->get();
                        foreach($tenderSelectedItems as $item ){
                                $tenderSelectedItemsIds[] = $item->tender_item_id;
                        }

                        
                        
                        if($tenderApplicantId){
                                $directorList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                                                                ->get();
                        
                                $directorListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                                ->count();

                        }

                        
                       
                        if($tenderApplicantId){
                                $shareholderList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                                                                ->get();
                        
                                $shareholderListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                                ->count();

                        }


                        if($tenderApplicantId){
                                $memberList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                                                                ->get();
                        
                                $memberListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                                ->count();
                        }


                        $jv_companies = JvCompanies::where('application_id', $tenderApplicantId)
                        ->get()->toArray();

                }  
          
        }

        $application_approved_status_ids = array(
                $this->settings('TENDER_APPROVED','key')->id,
             //   $this->settings('TENDER_PENDING','key')->id,
             //   $this->settings('TENDER_REQUEST_TO_RESUBMIT','key')->id,
             //   $this->settings('TENDER_RESUBMITTED','key')->id
        );

        $applied_list_arr = array();
        $winnerList = array();

        $tenderItemsArray = [];

        $user = $this->getAuthUser();

        $today = time();

        foreach( $tenderItems as $item ){

              $awarded =   TenderApplicantItem::leftJoin('tender_applications', 'tender_application_items.tender_application_id', '=', 'tender_applications.id')
                ->where('tender_applications.applied_by', $user->userid)
                ->where('tender_application_items.tender_item_id', $item->id)
                ->whereNotNull('tender_application_items.token')
                ->get()
                ->toArray();

              //  if(count($awarded)) {
              //          continue;
              //  }

            //    if( $user->tender_user != 'yes' &&  strtotime($item->from_time) > $today ) {
            //            continue;
            //    }

                $item_row = array();
                $item_row['name'] = $item->name;
                $item_row['description'] = $item->description;
                $item_row['quantity'] = $item->quantity;
                $item_row['from_time'] = strtotime($item->from_time);
                $item_row['to_time'] = strtotime($item->to_time);
                $item_row['id'] = $item->id;
                $item_row['number'] = $item->number;
                $tenderItemsArray[] = $item_row;

                
                $has_applied = TenderApplicantItem::where('tender_item_id',$item->id)->count();
                $applied_list_arr[$item->id] = array();
               

                if( $has_applied ) {

                        $applied_list = TenderApplicantItem::where('tender_item_id',$item->id)->get();

                        $current_time = time();

                        foreach($applied_list as $a ) {

                                $application_id = $a->tender_application_id;

                                $application_info = TenderApplication::where('id', $application_id)->first();

                              //  if( $application_approved_status_id != $application_info->status ){
                              //          continue;
                              //  }

                                if( !in_array( $application_info->status, $application_approved_status_ids)) {
                                        continue;
                                }

                               
 
                                if(    strtotime($item->to_time)  > $current_time ) { // closing date not exeeded
                                        continue;
                                }
                                $pca_form_info = Documents::where('key', 'TENDER_PCA3')->first();
                                $pca_type = $this->settings('CERT_TENDER_PCA3','key')->id;

                               /* $uploadedDoc =  TenderDocument::where('tender_id', $application_info->tender_id)
                                ->where('appication_id',$application_info->id)
                                ->where('document_id', $pca_form_info->id )
                                ->where('application_item_id', $a->id)
                                ->first();*/
                                $uploadedDoc = TenderCertificate::where('item_id', $a->id)
                                                                ->where('type', $pca_type)
                                                                ->first();

                                $pca4_form_info = Documents::where('key', 'TENDER_PCA4')->first();
                                $pca4_type = $this->settings('CERT_TENDER_PCA4','key')->id;
                                   
                                $uploadedPCA4Doc = TenderCertificate::where('item_id', $a->id)
                                                                ->where('type', $pca4_type)
                                                                ->first();

                             
                                
                               // $uploadeDocStatus = @$uploadedDoc->status;
                                
                                $row = array(
                                        'applicant_name'        => ( $application_info->applicant_fullname ) ? $application_info->applicant_fullname : $application_info->tenderer_fullname,
                                        'application_id'        =>  $application_info->id,
                                        'status'                => $this->settings($application_info->status,'id')->key,
                                        'pca1_token'            => isset($uploadedDoc->file_token) && $uploadedDoc->file_token && $today > strtotime($item->to_time)  ? $uploadedDoc->file_token : '',
                                        'pca2_token'            => isset($uploadedPCA4Doc->file_token) && $uploadedPCA4Doc->file_token && $today > strtotime($item->to_time)   ? $uploadedPCA4Doc->file_token : '',
                                        'pca1_file_name'        => 'pca1.pdf',
                                        'pca2_file_name'        => 'pca2.pdf',
                                        'item_status'           => $this->settings($a->status,'id')->key,
                                        'awarded_portion'       => floatval($a->awarded_portion),
                                        'winner'                => floatval($a->awarded_portion) ?  true : false,
                                       // 'from_time' => strtotime($item->from_time),
                                       //  'to_time' => strtotime($item->to_time),
                                         'expired' => $current_time > strtotime($item->to_time)


                                );
                                array_push($applied_list_arr[$item->id],$row);
               

                                if( !( $a->status == $this->settings('TENDER_ITEM_AWARDED','key')->id  || $a->status == $this->settings('TENDER_PCA2_SUBMITED','key')->id)){
                                        $winnerList[$a->tender_item_id] = null;
                                } else {
                                        $winnerList[$a->tender_item_id] = $a->tender_application_id;
                                }
                          

                        
                        }

                        

                       
                        
                } 

                
                
               

        }

       

       

        $tenderStatus = $this->settings($tenderInfo->type,'id')->key;
        $tenderStatus = ('CLOSE_TENDER' === $tenderStatus ) ? 'close' :'open';

        
        $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
        $applicant_type_id = $applicant_type->id;

        $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
        $applicantTypeArr = array();
        foreach($applicantTypes as $t ){
                $applicantTypeArr[] = array(

                        'key' => $t->key,
                        'value' => $t->value
                );
                
                
        }

        $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
        $applicant_sub_type_id = $applicant_sub_type->id;

        $applicantSubTypes = Setting::where('setting_type_id', $applicant_sub_type_id )->get();
        $applicantSubTypeArr = array();
        foreach($applicantSubTypes as $t ){
                $applicantSubTypeArr[] = array(

                        'key' => $t->key,
                        'value' => $t->value
                );
                
                
        }


        $uploadedListArr = array();
        $tenderApplicantId = intval( $request->tenderApplicantId );

        if($tenderApplicantId){
                $uploadedList =  TenderDocument::where('tender_id', $tenderId)
                                                ->where('appication_id',$tenderApplicantId)
                                                ->get();
                $uploadedCount = TenderDocument::where('tender_id', $request->tenderId)
                ->where('appication_id',$tenderApplicantId)
                ->count();

                if($uploadedCount){
                        foreach($uploadedList as $item ){
        
                                $uploadedListArr[$item->document_id] = basename($item->path);
        
                        }
                }
        }
        
        $appliedCountArr = array(
                $this->settings('TENDER_APPROVED','key')->id,
              //  $this->settings('TENDER_PENDING','key')->id,
              //  $this->settings('TENDER_REQUEST_TO_RESUBMIT','key')->id,
              //  $this->settings('TENDER_RESUBMITTED','key')->id,
                
        );
       
        $get_applied_counts = TenderApplication::where('tender_id',$tenderId)
        ->where('applied_by',$user->userid)
        ->whereIn('status',  $appliedCountArr)
        ->count();

       
        $publicaiton = TenderPublication::where('id', $tenderInfo->publication_id)->first();

        

        $paper_ad_ext =  ($publicaiton->paper_advertisement ) ? pathinfo($publicaiton->paper_advertisement, PATHINFO_EXTENSION) : '';
        $paper_ad_type = $this->check_file_type_category($paper_ad_ext);

        $bid_data_ext =  ($tenderInfo->bid_data_sheet ) ? pathinfo($tenderInfo->bid_data_sheet, PATHINFO_EXTENSION) : '';
        $bid_data_type = $this->check_file_type_category($bid_data_ext);
       


        return response()->json([
                'message'         => "Successfully populated tender details.",
                'applicationId'   => $applicationId,
                'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$applicationId),
                'uploadOtherDocs' => $this->files_for_other_docs($applicationId),
                'publisherDocs'   => $this->files_for_other_docs_for_publisher($tenderId),
                'tenderInfo'      => $tenderInfo,
                'tenderUsers'     => $tenderUsers,
                'tenderItems'     => $tenderItemsArray,
                'tenderStatus'    => $tenderStatus,
                'status'          => true,
                'applicant_types' => $applicantTypeArr,
                'applicant_sub_types' => $applicantSubTypeArr,
                'uploadedList' => $uploadedListArr,
                'appliedCount' => $this->settings($tenderInfo->status,'id')->key === 'COMMON_STATUS_DEACTIVE' ? 0 : count($applied_list_arr),
                'applied_list' => $applied_list_arr,
                'tenderStatusCode' => $this->settings($tenderInfo->status,'id')->key,
                'winnerList' => $winnerList,
                'paper_advertisement' => $publicaiton->paper_advertisement,
                'paper_advertisement_file_name' => $publicaiton->paper_advertisement_file_name,
                'paper_ad_type' => $paper_ad_type,
                'bid_data_type' => $bid_data_type,
                'countries'  => Country::all(),
                'pca1_payment' => $this->settings('PAYMENT_TENDER_PCA1','key')->value,
                'pca2_payment' => $this->settings('PAYMENT_TENDER_PCA2','key')->value,
                'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,

                'applicationInfo' => $applicationInfo,
                'tenderSelectedItemsIds' => $tenderSelectedItemsIds,
                'applicantType' => $applicantType,
                'applicantSubType' => @$applicantSubType,
                'tendererSubType' => @$tendererSubType,
                'directoListCount' => @$directorListCount,
                'directoList' => @$directorList,
                'shareholderListCount' => @$shareholderListCount,
                'shareholderList' => @$shareholderList,
                'memberList' => @$memberList,
                'memberListCount' => @$memberListCount,
                'jv_companies' => $jv_companies,
                'publisherdateFromCutOffStatus'=> $this->settings('TENDER_ITEM_APPLIED','key')->id,
                'close_tender_applicant_csv' => asset('other/close_tender_applicant.csv'),
                ], 200);

    }

    function checkAlreadyApplied(Request $request) {

        $applied_items = $request->applied_items;
        $applicant_nic_or_pass = $request->applicant_nic_or_pass;
        $applicant_nic_pass_val = $request->applicant_nic_pass_val;
        $tenderer_nic_or_pass = $request->tenderer_nic_or_pass;
        $tenderer_nic_pass_val = $request->tenderer_nic_pass_val;

        $applicant_reg_no = $request->applicant_reg_no;
        $applicant_reg_no = ($applicant_reg_no) ? preg_replace('/\s+/', '', $applicant_reg_no) : '';

        $tenderer_reg_no = $request->tenderer_reg_no;
        $tenderer_reg_no = ($tenderer_reg_no) ? preg_replace('/\s+/', '', $tenderer_reg_no) : '';

        $application_id = $request->application_id;
        $applicant_type = $request->applicant_type;
        $applicant_sub_type = $request->applicant_sub_type;
        $tenderer_sub_type = $request->tenderer_sub_type;
        
        $user = $this->getAuthUser();
        
 
         if( ! ( is_array($applied_items) && count($applied_items) ) ) {
                return response()->json([
                        'message' => 'No items selected.',
                        'status' => false

                 ], 200);
         }


         if(intval($application_id)) {
                TenderApplyMember::where('application_id', $application_id)
                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                ->delete();

                TenderApplyMember::where('application_id', $application_id)
                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                ->delete();

                TenderApplyMember::where('application_id', $application_id)
                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                ->delete();
         }
        

         $applicant_nic_pass_field = ('passport' !== $applicant_nic_or_pass) ? 'applicant_nic' : 'applicant_passport';
         $tenderer_nic_pass_field = ('passport' !== $tenderer_nic_or_pass) ? 'tenderer_nic' : 'tenderer_passport';

         $nic_pass_applicant_data = ($applicant_nic_pass_val) ? TenderApplication::where($applicant_nic_pass_field, trim(strtoupper($applicant_nic_pass_val)) )->where('applied_by', $user->userid )->orderBy('id','DESC')->first() : null;
         $nic_pass_tenderer_data = ($tenderer_nic_pass_val) ? TenderApplication::where($tenderer_nic_pass_field, trim(strtoupper($tenderer_nic_pass_val)) )->where('applied_by', $user->userid )->orderBy('id','DESC')->first() : null;

         
         $tenderer_registration_no_data = ($tenderer_reg_no) ? TenderApplication::where('tenderer_registration_number', trim(strtoupper($tenderer_reg_no)) )->where('applied_by', $user->userid )->orderBy('id','DESC')->first() : null;
         $directorList = array();
         $directorListCount = 0;
        if(isset($tenderer_registration_no_data->id)){
                $directorList =  TenderApplyMember::where('application_id', $tenderer_registration_no_data->id)
                                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                                ->get();
                        
                $directorListCount = TenderApplyMember::where('application_id', $tenderer_registration_no_data->id)
                                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                                ->count();

        }

        $shareholderList = array();
        $shareholderListCount = 0;
        if(isset($tenderer_registration_no_data->id)){
                $shareholderList =  TenderApplyMember::where('application_id', $tenderer_registration_no_data->id)
                                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                                ->get();
                        
                $shareholderListCount = TenderApplyMember::where('application_id', $tenderer_registration_no_data->id)
                                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                                ->count();

        }

        $memberList = array();
        $memberListCount = 0;
        if(isset($tenderer_registration_no_data->id)){
                $memberList =  TenderApplyMember::where('application_id', $tenderer_registration_no_data->id)
                                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                                ->get();
                        
                $memberListCount = TenderApplyMember::where('application_id', $tenderer_registration_no_data->id)
                                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                                ->count();
        } 
         
         
         
         $data = array(

                'applicant_name' => isset($nic_pass_applicant_data->applicant_fullname) ? $nic_pass_applicant_data->applicant_fullname : '' ,
                'applicant_nationality' => isset($nic_pass_applicant_data->applicant_nationality) ? $nic_pass_applicant_data->applicant_nationality : '' ,
                'applicant_mobile' => isset($nic_pass_applicant_data->applicant_mobile) ? $nic_pass_applicant_data->applicant_mobile : '' ,
                'applicant_email' => isset($nic_pass_applicant_data->applicant_email) ? $nic_pass_applicant_data->applicant_email : '' ,
                'applicant_address' => isset($nic_pass_applicant_data->applicant_address) ? $nic_pass_applicant_data->applicant_address : '' ,

                'tenderer_fullname' => isset($nic_pass_tenderer_data->tenderer_fullname) ? $nic_pass_tenderer_data->tenderer_fullname : '' ,
                'tenderer_nationality' => isset($nic_pass_tenderer_data->tenderer_nationality) ? $nic_pass_tenderer_data->tenderer_nationality : '' ,
                'tenderer_address' => isset($nic_pass_tenderer_data->tenderer_address) ? $nic_pass_tenderer_data->tenderer_address : '' ,

                'directoListCount' => @$directorListCount,
                'directoList' => @$directorList,
                'shareholderListCount' => @$shareholderListCount,
                'shareholderList' => @$shareholderList,
                'memberList' => @$memberList,
                'memberListCount' => @$memberListCount,

         );





        $where_not_in_statuses = array(
                $this->settings( 'TENDER_CANCELED' ,'key')->id,
                $this->settings( 'TENDER_REJECTED' ,'key')->id,
                $this->settings('TENDER_NOT_RECOMMEND_FOR_APPROVAL', 'key')->id
        );

        
         foreach( $applied_items as $item ) {

                $query = TenderApplicantItem::query();

                if($applicant_type === 'TENDER_TENDERER') {
                        if($tenderer_sub_type === 'TENDER_INDIVIDUAL') {
                                $query->where($tenderer_nic_pass_field, $tenderer_nic_pass_val );
                        } else {
                                $query->where('tenderer_registration_number', $tenderer_reg_no );
                        }
                        $query->where('tenderer_sub_type', $this->settings( $tenderer_sub_type ,'key')->id );

                } else {
                        if($tenderer_sub_type === 'TENDER_INDIVIDUAL') {
                                $query->where($tenderer_nic_pass_field, $tenderer_nic_pass_val );
                        } else {
                                $query->where('tenderer_registration_number', $tenderer_reg_no );
                        }
                        if($tenderer_sub_type){
                                $query->where('tenderer_sub_type', $this->settings( $tenderer_sub_type ,'key')->id );
                        }
                       

                        if($applicant_sub_type === 'TENDER_INDIVIDUAL') {
                                $query->where($applicant_nic_pass_field, $applicant_nic_pass_val );
                        } else {
                                $query->where('registration_number', $applicant_reg_no );
                        }
                        if($applicant_sub_type){
                                $query->where('applicant_sub_type', $this->settings( $applicant_sub_type ,'key')->id );
                        }
                       
                      
                }
                $query->where('applicant_type', $this->settings( $applicant_type ,'key')->id );
                $query->where('tender_item_id', $item );
                $query->whereNotIn('status', $where_not_in_statuses);
                $is_applied_count = $query->count();


                if($is_applied_count) {

                         if($application_id){

                                $selected_q = $query->first();

                             

                                if($selected_q->tender_application_id == $application_id ) {

                                        return response()->json([
                                                'message' => "Selected items can be applied.",
                                                'status' => true
                                
                                         ], 200);

                                }

                         }

                        return response()->json([
                                'message' => "You have already applied some of the selected items previously.",
                                'status' => false,
                                'data' => $data
        
                         ], 200);

                         die();

                }
         }



         return response()->json([
                'message' => "Selected items can be applied.",
                'status' => true,
                'data' => $data

         ], 200);




     }

    private function check_file_type_category($ext){

        $pdf_file = array('pdf');
        $word_doc = array('doc','docx');
        $image_doc = array('png','jpg', 'gif','bmp','svg','jpeg');

        if(in_array($ext,$pdf_file)){
                return 'fa-file-pdf';
        }
        else if(in_array($ext,$word_doc)){
                return 'fa-file-word';
        }else if(in_array($ext,$image_doc)){
                return 'fa-file-image';
        }
        else {
                return 'fa-file';
        }
        

    }


     /********************* RESUBMIT TENDER***************/
     function getResubmttedTender( Request $request ){

        $tenderResubmitToken =  $request->token;

        if( !$tenderResubmitToken ) {
                return response()->json([
                        'message'         => "Invalid Tender1.",
                        'status'          => false,
                        ], 200);
        }

        $applicationInfoCount = TenderApplication::where('token',$tenderResubmitToken)->count();

        
        if( $applicationInfoCount  !== 1) {
                return response()->json([
                        'message'         => "Invalid Tender2.",
                        'status'          => false,
                        ], 200);
        }

        $applicationInfo = TenderApplication::where('token',$tenderResubmitToken)->first();

        $tenderApplicantId = $applicationInfo->id;

        $update_updated_at = array(
                'updated_at' => date('Y-m-d H:i:s', time())
        );
        TenderApplication::where('id', $tenderApplicantId)
        ->update($update_updated_at);
        
        //original tender info
        $tenderId =  $applicationInfo->tender_id;
        $tenderInfo = Tender::where('id', $tenderId)->first();

        
        
       

        $applicationStatus = $this->settings($applicationInfo->status,'id')->key;

        if( $applicationStatus  !== 'TENDER_REQUEST_TO_RESUBMIT' ) {

                return response()->json([
                        'message'         => "Invalid Tender3.",
                        'status'          => false,
                        ], 200);
        }

     //   $tenderUsers = TenderUser::where('tender_id',$tenderId)->get();

     $winnerList = array();

      
       $tenderSelectedItemsIds = array();
       $tenderSelectedItems = TenderApplicantItem::where('tender_application_id', $tenderApplicantId )->get();

       //print_r($tenderSelectedItems);
       foreach($tenderSelectedItems as $item ){
         $tenderSelectedItemsIds[] = $item->tender_item_id;

         if( !( $item->status == $this->settings('TENDER_ITEM_AWARDED','key')->id  || $item->status == $this->settings('TENDER_PCA2_SUBMITED','key')->id)){
                $winnerList[$item->tender_item_id] = null;
        } else {
                $winnerList[$item->tender_item_id] = $item->tender_application_id;
        }
        

       }

       
 


       $tenderItems = TenderItem::where('tender_id', $tenderId )
       ->whereIn('id',$tenderSelectedItemsIds)
       ->get();

        
        $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
        $applicant_type_id = $applicant_type->id;

        $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
        $applicantTypeArr = array();
        foreach($applicantTypes as $t ){
                $applicantTypeArr[] = array(

                        'key' => $t->key,
                        'value' => $t->value
                );
                
                
        }
        $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
        $applicant_sub_type_id = $applicant_sub_type->id;

        $applicantSubTypes = Setting::where('setting_type_id', $applicant_sub_type_id )->get();
        $applicantSubTypeArr = array();
        foreach($applicantSubTypes as $t ){
                $applicantSubTypeArr[] = array(

                        'key' => $t->key,
                        'value' => $t->value
                );
                
                
        }

        $uploadedListArr = array();
        $uploadedListArrWithToken = array();
      
       

        $directorList = array();
        $directorListCount = 0;
        if($tenderApplicantId){
                $directorList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                                                ->get();
               
                $directorListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                ->count();

               
        }

        $shareholderList = array();
        $shareholderListCount = 0;
        if($tenderApplicantId){
                $shareholderList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                                                ->get();
               
                $shareholderListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                ->count();

               
        }
        $memberList = array();
        $memberListCount = 0;
        if($tenderApplicantId){
                $memberList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                                                ->get();
               
                $memberListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                ->count();

               
        }

        $jv_companies = JvCompanies::where('application_id', $tenderApplicantId)
                        ->get()->toArray();

        $external_global_comment = '';

        $resumbmit_key_id = $this->settings('TENDER_REQUEST_TO_RESUBMIT','key')->id;
        $external_comment_key_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $external_comment_query = TenderStatus::where('appication_id',$tenderApplicantId)
                                                        ->where('comment_type', $external_comment_key_id )
                                                        ->where('status', $resumbmit_key_id )
                                                        ->orderBy('id', 'desc')
                                                        ->first();
        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                          ?  $external_comment_query->comments
                                          : '';
    
   
        
       
        return response()->json([
                'message'         => "Successfully populated tender details.",
                'tenderInfo'      => $tenderInfo,
                'applicationInfo' => $applicationInfo,
                'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$applicationInfo->id),
                'uploadOtherDocs' => $this->files_for_other_docs($applicationInfo->id),
                'publisherDocs'   => $this->files_for_other_docs_for_publisher($tenderId),
              //  'tenderUsers'     => $tenderUsers,
                'tenderItems'     => $tenderItems,
                'status'          => true,
                'applicant_types' => $applicantTypeArr,
                'applicant_sub_types' => $applicantSubTypeArr,
                'uploadedList' => $uploadedListArr,
                'uploadedListArrWithToken' => $uploadedListArrWithToken,
                'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                'applicantSubType' => @$this->settings( $applicationInfo->applicant_sub_type ,'id')->key,
                'tendererSubType' => @$this->settings( $applicationInfo->tenderer_sub_type ,'id')->key,
                'directoListCount' => $directorListCount,
                'directoList' => $directorList,
                'shareholderListCount' => $shareholderListCount,
                'shareholderList' => $shareholderList,
                'memberList' => $memberList,
                'memberListCount' => $memberListCount,
                'jv_companies' => $jv_companies,
                'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$tenderApplicantId),
                'winnerList' => $winnerList,
                'countries'  => Country::all(),
                'external_global_comment' => $external_global_comment

              
                ], 200);

    }



    /***************************awording******************************** */

    function notifyTenderItemDateChange( Request $request) {
        $item_id = $request->itemId;
       
        $date_to = date('D M d Y H:i:s',strtotime($request->dateTo) );

        $update =  array(
                'to_time'    => $date_to,
        );
        $updated = TenderItem::where('id', $item_id)->update($update);

     $records =    TenderApplicantItem::leftJoin('tender_applications','tender_application_items.tender_application_id', '=', 'tender_applications.id')
                  ->leftJoin('tender_items','tender_application_items.tender_item_id', '=', 'tender_items.id' )
                  ->leftJoin('tenders','tender_items.tender_id', '=', 'tenders.id' )
                  ->whereIn('tender_application_items.status', array(
                        $this->settings('TENDER_ITEM_AWARDED','key')->id,
                        $this->settings('TENDER_ITEM_APPLIED','key')->id,
                        $this->settings('TENDER_PCA2_SUBMITED','key')->id
                  ))
                  ->where('tender_items.id', $item_id )
                  ->select(
                           'tender_applications.applicant_mobile as applicant_mobile',
                           'tender_applications.applicant_email as applicant_email',
                           'tender_items.name as item_name',
                           'tenders.number as tender_number',
                           'tenders.name as tender_name'
                   )
                  ->get()
                  ->toArray();

        if(count($records) && $updated) {

                

                foreach($records as $record) {
                       $message = "Closing date of the item ". $record['item_name'] ." of ". $record['tender_name']."(Tender NO: ".$record['tender_number'].") has been change to ". $date_to;
                  if($record['applicant_mobile']) {
                        
                        $sms =  new SmsQueue;
                        $sms->to = trim($record['applicant_mobile']);
                        $sms->message = $message;
                        $sms->status = 0;
                        $sms->save();

                  }

                  if($record['applicant_email']) {
                        @$this->ship($record['applicant_email'], 'tenderclosingdate', null, $message,null,null,null,'Closing date Changed');
                  }

                       
                }  

        }

            



        
   }


    function awordForApplicant( Request $request ){

        $awordedList = $request->awordedList;
        $tenderId = $request->tenderId;

        $item = $request->itemId;
        $application_id = $request->applicationId;

        $winnerList = array();

      //  $applicantInfo = TenderApplicant::where('id', $tenderId);
       // $applicationInfo = TenderApplication::where('id',$tenderId)->first();


       foreach( $awordedList as $itemId => $records ) {

               if(! ( is_array($records) && count($records)) ) {
                       continue;
               }

               if($itemId != $item) {
                       continue;
               }

            foreach($records as $record )   {


                if( $record['application_id'] != $application_id  ) {
                        continue;
                }

             //   if( $record['winner'] ) {
             //           continue;
             //   }

                $applicationInfo = TenderApplication::where('id',$record['application_id'])->first();

                $tenderId = $applicationInfo->tender_id;
                $tenderInfo = Tender::where('id', $tenderId)->first();
                $publisherId = $tenderInfo->created_by;
        
                $publisherDetails = People::where('id', $publisherId )->first();

        
                $applicantItemInfo = TenderApplicantItem::where('tender_application_id', $record['application_id'] )
                                                        ->where('tender_item_id', $itemId)
                                                        ->first();
                //  if(!( isset($applicantItemInfo->token) && $applicantItemInfo->token ) ) {

                $award_unique_key =  md5( uniqid() );

                $update_item_aword = array(

                                'status' => floatval($record['awarded_portion']) ? $this->settings('TENDER_ITEM_AWARDED','key')->id : $this->settings('TENDER_ITEM_APPLIED','key')->id,
                                'token' => floatval($record['awarded_portion']) ? $award_unique_key : null, // this will also need to change
                                'awarded_date' => floatval($record['awarded_portion']) ? date('Y-m-d H:i:s', time()) : null,
                                'awarded_portion' => floatval($record['awarded_portion']),
                                'cancled_by_publisher' => floatval($record['awarded_portion']) ? 0 : 1,
                );
                $updated = TenderApplicantItem::where('tender_application_id',$record['application_id'] )
                                                        ->where('tender_item_id', $itemId)
                                                ->update($update_item_aword);
                

                if($updated && floatval($record['awarded_portion'])) {

                        $award_link = env('FRONT_APP_URL', '')."/home/tenders/awarding/$tenderId/$award_unique_key";
                        //send notification mail with link
                        @$this->ship($applicationInfo->applicant_email, 'tokenwithemail', $award_link, null,null,null,'Tender Awarding',$applicationInfo->tenderer_fullname,true );
                        
                } else { 

                        // remove certificate if exist 
                        $uploadedPCA4Doc = TenderCertificate::where('item_id', $applicantItemInfo->id)
                        ->where('type', $this->settings('CERT_TENDER_PCA4','key')->id)
                        ->delete();

                        // inform to ROC and applicant

                        $aword_listing_status_on_admin_side = array(
                                'TENDER_PCA2_SUBMITED',
                                'TENDER_PCA2_RESUBMITTED',
                                'TENDER_PCA2_REQUEST_TO_RESUBMIT',
                                'TENDER_PCA2_RECOMMEND_FOR_APPROVAL',
                                'TENDER_PCA2_NOT_RECOMMEND_FOR_APPROVAL'
                        );

                        if(in_array($applicantItemInfo->status, $aword_listing_status_on_admin_side ) ) {

                                $tenderName = $tenderInfo->name;
                                $tenderName .= ($tenderInfo->number) ? '(Number:' . $tenderInfo->number . ')' : '';

                                
                                $applicationId = $applicationInfo->id; 

                                $itemInfo = TenderItem::where('id', $itemId)->first();
                                $item_name = $itemInfo->name;
                                $item_name.= ($itemInfo->number) ? '(Item Number:' . $itemInfo->number . ')' : '';

                                $applicant_name = $applicationInfo->applicant_fullname;
                                $applicant_address = $applicationInfo->applicant_address;
                                $applicant_email = $applicationInfo->applicant_email;

                                $publisehr_name = $publisherDetails->first_name . ' ' . $publisherDetails->last_name;
                                $publisehr_mobile = $publisherDetails->mobile;
                                $publisehr_email = $publisherDetails->email;

                                $message = "<p><strong>Tender ID: </strong>$tenderId</p>";
                                $message.= "<p><strong>Tender Name: </strong>$tenderName</p>";
                                $message.= "<p><strong>Application ID: </strong>$applicationId</p>";
                                $message.= "<p><strong>Item : </strong>$item_name</p>";
                                

                                $message.= "<h5>Applicant Information</h5>";
                                $message.= "<p><strong>Name : </strong>$applicant_name";
                                $message.= "<p><strong>Address : </strong>$applicant_address";
                                $message.= "<p><strong>Email : </strong>$applicant_email";

                                $message.= "<h5>Publisher Information</h5>";
                                $message.= "<p><strong>Name : </strong>$publisehr_name";
                                $message.= "<p><strong>Mobile : </strong>$publisehr_mobile";
                                $message.= "<p><strong>Email : </strong>$publisehr_email";

                                $roc_email = isset($this->settings('ROC_ALERT_EMAIL','key')->value) ? $this->settings('ROC_ALERT_EMAIL','key')->value : '';

                                if($roc_email) {
                                        @$this->ship($roc_email, 'alert-to-roc-cancel-aword.blade', $message, null,null,null,'Cancel Awarding alert',null, false );
                                }
                               
                        }


                }

        //  }

          }

        }



     /*  foreach( $awordedList as $itemId => $applicantId ) {

      

        if( !$applicantId ) {
                $winnerList[$itemId] = null;
                continue;
        }

        $applicationInfo = TenderApplication::where('id',$applicantId)->first();

      


      $tenderId = $applicationInfo->tender_id;
      $tenderInfo = Tender::where('id', $tenderId)->first();
      $publisherId = $tenderInfo->created_by;
 
      $publisherDetails = People::where('id', $publisherId )->first();

     
      $applicantItemInfo = TenderApplicantItem::where('tender_application_id', $applicantId )
                                                ->where('tender_item_id', $itemId)
                                                ->first();
      if(!( isset($applicantItemInfo->token) && $applicantItemInfo->token ) ) {

                $award_unique_key =  md5( uniqid() );

                $update_item_aword = array(

                        'status' => $this->settings('TENDER_ITEM_AWARDED','key')->id,
                        'token' => $award_unique_key, // this will also need to change
                        'awarded_date' => date('Y-m-d H:i:s', time())
                );
                $updated = TenderApplicantItem::where('tender_application_id', $applicantId )
                                                ->where('tender_item_id', $itemId)
                                        ->update($update_item_aword);

                if($updated) {

                // $award_link = str_replace('public','',url('/'))."/home/tenders/awording/$tenderId/$award_unique_key";
                $award_link = env('FRONT_APP_URL', '')."/home/tenders/awarding/$tenderId/$award_unique_key";

                        //send notification mail with link
                        @$this->ship($applicationInfo->applicant_email, 'tokenwithemail', $award_link, null,null,null,'Tender Awarding',$applicationInfo->tenderer_fullname,true );
                        
                        $winnerList[$itemId] = $applicantId;
                }

        }


            
       }*/
     
       return response()->json([
        'message'         => "Successfully aworded for selected applicants.",
        'status'          => true,
        'winnerList'      => $winnerList
        ], 200);

  
    }



    function aworded_documents($is_resubmission=false){
        $docs = array();

        $pca_2 =  Documents::where('key', 'TENDER_PCA2')->first();

        $docs['awording'] = array(

                'download' => array(
                        array('name' =>'PCA 02', 'savedLocation' => "", 'view'=>'pca2', 'specific' =>'','file_name_key' =>'pca2' ),
                ),
                'upload'   => array( 
                        array( 'dbid' => $pca_2->id,'name' => $pca_2->name,'required' =>  true ,'type'   =>'pca2','fee' => 0,'uploaded_path' => '','comments' =>''),

                )

        );

        return $docs;

   }
   private function getAwordingDocs($doc_type){

        $docs = $this->aworded_documents();

        return isset(  $docs[$doc_type] ) ?   $docs[$doc_type]  : false;

    }

   function generate_awording_files($doc_type,$applicant_item_id){


        $docs = $this->getAwordingDocs($doc_type );

        $downloaded = $docs['download'];

        $generated_files = array(

            'docs' => array(),

        );
     //   return  $generated_files;
       // $pdff = App::make('dompdf.wrapper');

        if(count($downloaded)){
            foreach($downloaded as $file ){
                  
                $name = $file['name'];
                $file_name_key = $file['file_name_key'];

                
             
                
                $tender_items = TenderApplicantItem::where('id',$applicant_item_id)->first();

                $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
                $certificateInfo = TenderCertificate::where('item_id', $tender_items->id)
                                                           ->where('type', $cert_type)
                                                           ->orderBy('id', 'DESC')
                                                           ->first();

                $item_id = $tender_items->tender_item_id;
                $itemInfo = TenderItem::where('id', $item_id)->first();

                // print_r( $tender_items);

                $tender_application = TenderApplication::where('id', $tender_items->tender_application_id)->first();
               
                $tenderId =  $tender_application->tender_id;
                $tenderInfo = Tender::where('id', $tenderId)->first();
                $publisherId = $tenderInfo->created_by;
               //  $publisherDetails = People::where('id', $publisherId)->first();

                 //$publisher_user_info = User::where('id', $publisherId )->first();
               //  $publisher_id = $publisher_user_info->people_id;

               //  $publisherDetails = People::where('id', $publisher_id )->first();

            //     $publisherDetails = People::leftjoin('users', 'users.people_id', '=', 'people.id')
            //   ->select('users.id','people.first_name','people.last_name','people.ministry', 'people.department')
            //   ->where('users.id', $tenderInfo->created_by)
           //    ->first();
                $publisherDetails = People::where('id', $publisherId )->first();

               
                $tender_applicant_id = $tender_items->tender_application_id;
                $director_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                 ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                                ->get();
                $director_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_DIRECTORS','key')->id )
                               ->count();

                $shareholder_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                                ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                               ->get(); 
                $shareholder_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id )
                              ->count();
                                     
                $member_list = TenderApplyMember::where('application_id',$tender_applicant_id)
                               ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                              ->get();  
                $member_list_count = TenderApplyMember::where('application_id',$tender_applicant_id)
                              ->where('type',  $this->settings('TENDER_MEMBER_PARTNERS','key')->id )
                             ->count(); 
     
                $data = array(
                        'public_path' => public_path(),
                        'eroc_logo' => url('/').'/images/forms/eroc.png',
                        'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                        'css_file' => url('/').'/images/forms/form1/form1.css',
                        'tender_application' => $tender_application,
                        'applicant_type'   => $this->settings( $tender_application->applicant_type,'id')->key,
                        'tender_items' => $tender_items,
                        'itemInfo'   => $itemInfo,
                        'director_list' => $director_list,
                        'director_list_count' => $director_list_count,
                        'shareholder_list' => $shareholder_list,
                        'shareholder_list_count'=> $shareholder_list_count,
                        'member_list' => $member_list,
                        'member_list_count' => $member_list_count,
                        'company' => ($tender_items->contract_awarded) ? $tender_items->contract_awarded  : $publisherDetails->ministry.'/'.$publisherDetails->department.'/'.$tenderInfo->division,
                        'tenderInfo' => $tenderInfo,
                        'certificate' => isset($certificateInfo->certificate_no) && $certificateInfo->certificate_no ? $certificateInfo->certificate_no : ''
                    );
                       
              
                    
                        $directory = "tender-awording/$tender_applicant_id/$applicant_item_id";
                        Storage::makeDirectory($directory);
        
                         $view = 'forms.'.$file['view'];

                         $pdf = PDF::loadView($view, $data);
                        $pdf->save(storage_path("app/$directory")."/$file_name_key-$tender_applicant_id-$applicant_item_id.pdf");

                        $file_row = array();
                        $file_row['name'] = $file['name'];
                        $file_row['file_name_key'] = $file_name_key;
                        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$tender_applicant_id-$applicant_item_id.pdf");
                        $generated_files['docs'][] = $file_row;
  
            }
        }
        
        return $generated_files;
    }

  /*  function files_for_upload_for_awording($doc_type,$applicant_item_id){

   
          $docs = $this->getAwordingDocs($doc_type );
   
           $uploaded = $docs['upload'];
   
           $generated_files = array(
                   'docs' => array(),
           );
   

   
           if(count($uploaded)){
              
               foreach($uploaded as $file ){
                     
                   $name = $file['name'];
               
                   $file_row = array();
                   $file_row['doc_comment'] = '';
                   $file_row['doc_status'] = 'DOCUMENT_PENDING';
   
           
                   $file_row['is_required'] = $file['required'];
                   $file_row['file_name'] = $file['name'];
                   $file_row['file_type'] = $file['type'];
                   $file_row['dbid'] = $file['dbid'];
   
                   $generated_files['docs'][] = $file_row;
   
              
               }
           }
   
           return $generated_files;
       
    }*/


 function files_for_upload_for_awording($doc_type,$applicant_item_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
              
        );

        if(!$applicant_item_id) {
                return array(
                    'docs' => array(),
                    'uploadedAll' => false,
                    'doc_id' => 0
            );
            }


                
        $tender_item = TenderApplicantItem::where('id',$applicant_item_id)->first();
        $tender_application = TenderApplication::where('id', $tender_item->tender_application_id)->first();
        $status =  $this->settings($tender_item->status,'id')->key;

     
        // documents list
        $pca_2 = Documents::where('key', 'TENDER_PCA2')->first();
       
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

        $has_all_uploaded_str = '';


        $file_row = array();
        $file_row['doc_comment'] = '';
        $file_row['doc_status'] = 'DOCUMENT_PENDING';
        $file_row['is_required'] = true;
        $file_row['file_name'] = $pca_2->name;
        $file_row['file_type'] = '';
        $file_row['dbid'] = $pca_2->id;
        $file_row['applicant_item_id'] = null;
        $file_row['member_id'] = null;
        $file_row['company_id'] = null;
        $file_row['uploaded_path'] = '';

        $uploadedDoc =  TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id',$tender_item->tender_application_id)
                                        ->where('application_item_id',$applicant_item_id)
                                        ->where('document_id', $pca_2->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
                
        $uploadeDocStatus = @$uploadedDoc->status;
        if($status == 'TENDER_PCA2_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
        }

        if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
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
    
        $generated_files['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;
      

        return $generated_files;
    
    }

function files_for_other_docs_for_tender_aword($tender_applicant_id, $applicant_item_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
        );
 
        if(!$tender_applicant_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $tender_application = TenderApplication::where('id', $tender_applicant_id)->first();

        $status =  $this->settings($tender_application->status,'id')->key;


        // documents list
        $form_other_docs = Documents::where('key', 'AWARD_TENDER_ADDITIONAL_DOCUMENT')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

       
        $other_docs = TenderDocument::where('tender_id', $tender_application->tender_id)
                                        ->where('appication_id', $tender_applicant_id )
                                        ->where('application_item_id', $applicant_item_id )
                                        ->where('document_id', $form_other_docs->id )
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
            if($status == 'TENDER_PCA2_REQUEST_TO_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                            $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
            }
            if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
    
                $commentRow = TenderDocumentStatus::where('tender_document_id', $docs->id )
                ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                ->where('comment_type', $external_comment_type_id )
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




function tenderAwardedUpdateContract( Request $request){
        $tenderAwordingToken =  $request->token;

        $contract_date_from = date('D M d Y H:i:s',strtotime($request->contract_date_from));
        $contract_date_to = date('D M d Y H:i:s',strtotime($request->contract_date_to) );

        $update_contact_arr = array(

                'nature_of_contract' => $request->nature_of_contract,
                'accepted_amount' =>  $request->accepted_amount,
                'vat_no'                => $request->vat_fileno,
                'income_tax_no'         => $request->incometax_fileno,
                'income_tax_3'    => $request->contract_tax_year3,
                'income_tax_2'    => $request->contract_tax_year2,
                'income_tax_1'    => $request->contract_tax_year1,
                'vat_3'           => $request->vat_year3,
                'vat_2'           => $request->vat_year2,
                'vat_1'           => $request->vat_year1,
                'contract_date_from' => $contract_date_from,
                'contract_date_to' => $contract_date_to,
                'contract_awarded' => $request->contract_awarded
        );
        $updated = TenderApplicantItem::where('token', $tenderAwordingToken)->update($update_contact_arr);

        if( $updated ) {
                return response()->json([
                        'message'         => "Successfully updated contract details.",
                        'status'          => true,
                        ], 200);
        } else {
                return response()->json([
                        'message'         => "Failed updating contract details.",
                        'status'          => false,
                        ], 200);
        }




}


function updateAwardingSigningPartyDetails( Request $request){
        $tenderAwordingToken =  $request->token;

      

        $update_arr = array(

                'signing_party_name' => $request->signing_party_name,
                'signing_party_designation' =>  $request->signing_party_designation,
                'signing_party_designation_other'  => $request->signing_party_designation_other,
               
        );
        $updated = TenderApplicantItem::where('token', $tenderAwordingToken)->update($update_arr);

        if( $updated ) {
                return response()->json([
                        'message'         => "Successfully updated signing party details.",
                        'status'          => true,
                        ], 200);
        } else {
                return response()->json([
                        'message'         => "Failed updating signing party details.",
                        'status'          => false,
                        ], 200);
        }




}


    function getAwordingTender( Request $request ){

        $tenderAwordingToken =  $request->token;

        if( !$tenderAwordingToken ) {
                return response()->json([
                        'message'         => "Invalid Token.",
                        'status'          => false,
                        ], 200);
        }

        $applicationItemInfoCount = TenderApplicantItem::where('token',$tenderAwordingToken)->count();

        
        if( $applicationItemInfoCount  !== 1) {
                return response()->json([
                        'message'         => "You are not allowed to request awarding process for this tender item.",
                        'status'          => false,
                        ], 200);
        }

        

        

        $applicationItemInfo = TenderApplicantItem::where('token',$tenderAwordingToken)->first();
        
        if(!( $applicationItemInfo->status  ==   $this->settings('TENDER_ITEM_AWARDED','key')->id  || $applicationItemInfo->status  ==   $this->settings('TENDER_PCA2_REQUEST_TO_RESUBMIT','key')->id  ) ) {
                return response()->json([
                        'message'         => "You are not allowed to request awarding process for this tender item.",
                        'status'          => false,
                        ], 200);
        }

       $awarded_date_timestamp =  ($applicationItemInfo->awarded_date ) ? strtotime($applicationItemInfo->awarded_date) : '';

       if(!$awarded_date_timestamp) {
        return response()->json([
                'message'         => "You are not allowed to request awarding process for this tender item.",
                'status'          => false,
                ], 200);
       }

       $date_gap =  intval( ( time() - $awarded_date_timestamp )/(24*60*60) );

       if( $date_gap > 60 ) {
        return response()->json([
                'message'         => "You can not proceed the awarding process since your request has expired.",
                'status'          => false,
                ], 200);
       } 
       
        $tenderApplicantId = $applicationItemInfo->tender_application_id;


        $applicationInfo = TenderApplication::where('id',$tenderApplicantId)->first();


        $update_updated_at = array(
                'updated_at' => date('Y-m-d H:i:s', time())
        );
        TenderApplication::where('id', $tenderApplicantId)
        ->update($update_updated_at);


        //original tender info
        $tenderId =  $applicationInfo->tender_id;
        $tenderInfo = Tender::where('id', $tenderId)->first();

        //$publisherDetails = People::leftjoin('users', 'users.people_id', '=', 'people.id')
        //->select('users.id','people.first_name','people.last_name','people.ministry', 'people.department')
       // ->where('users.id', $tenderInfo->created_by)
       // ->first();
       $publisherDetails = People::where('id',$tenderInfo->created_by )->first();

        

        $applicationStatus = $this->settings($applicationInfo->status,'id')->key;


      
       $tenderSelectedItemsIds = array();
      /* $tenderSelectedItems = TenderApplicantItem::where('tender_application_id', $tenderApplicantId )->get();
       foreach($tenderSelectedItems as $item ){
         $tenderSelectedItemsIds[] = $item->tender_item_id;

       }*/
       $tenderSelectedItemsIds[] = $applicationItemInfo->tender_item_id;


       $tenderItems = TenderItem::where('tender_id', $tenderId )
       ->whereIn('id',$tenderSelectedItemsIds)
       ->get();

       $tenderItemsArray = array();
       foreach($tenderItems as $item) {
        $item_row = array();
        $item_row['name'] = $item->name;
        $item_row['description'] = $item->description;
        $item_row['quantity'] = $item->quantity;
        $item_row['from_time'] = strtotime($item->from_time);
        $item_row['to_time'] = strtotime($item->to_time);
        $item_row['id'] = $item->id;
        $item_row['number'] = $item->number;
        $tenderItemsArray[] = $item_row;
       }

      

        
        $applicant_type = SettingType::where('key','TENDER_APPLICANT_TYPE')->first();
        $applicant_type_id = $applicant_type->id;

        $applicantTypes = Setting::where('setting_type_id', $applicant_type_id )->get();
        $applicantTypeArr = array();
        foreach($applicantTypes as $t ){
                $applicantTypeArr[] = array(

                        'key' => $t->key,
                        'value' => $t->value
                );
                
                
        }
        $applicant_sub_type = SettingType::where('key','TENDER_APPLICANT_SUB_TYPE')->first();
        $applicant_sub_type_id = $applicant_sub_type->id;

        $applicantSubTypes = Setting::where('setting_type_id', $applicant_sub_type_id )->get();
        $applicantSubTypeArr = array();
        foreach($applicantSubTypes as $t ){
                $applicantSubTypeArr[] = array(

                        'key' => $t->key,
                        'value' => $t->value
                );
                
                
        }

    

        $directorList = array();
        $directorListCount = 0;
        if($tenderApplicantId){ 
                $directorList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                                                ->get();
               
                $directorListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                ->where('type', $this->settings('TENDER_MEMBER_DIRECTORS','key')->id)
                ->count();

               
        }

        $shareholderList = array();
        $shareholderListCount = 0;
        if($tenderApplicantId){
                $shareholderList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                                                ->get();
               
                $shareholderListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                ->where('type', $this->settings('TENDER_MEMBER_SHAREHOLDERS','key')->id)
                ->count();

               
        }

        $memberList = array();
        $memberListCount = 0;
        if($tenderApplicantId){
                $memberList =  TenderApplyMember::where('application_id', $tenderApplicantId)
                                                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                                                ->get();
               
                $memberListCount = TenderApplyMember::where('application_id', $tenderApplicantId)
                ->where('type', $this->settings('TENDER_MEMBER_PARTNERS','key')->id)
                ->count();

               
        }

        $tender_pca2 = Documents::where('key', 'TENDER_PCA2')->first();

        $uploadedList =  TenderDocument::where('tender_id', $tenderId)
                                        ->where('appication_id',$tenderApplicantId)
                                        ->where('application_item_id',$applicationItemInfo->id)
                                        ->where('document_id', $tender_pca2->id)
                                        ->get();
        $uploadedCount = TenderDocument::where('tender_id', $tenderId)
        ->where('appication_id',$tenderApplicantId)
        ->where('application_item_id',$applicationItemInfo->id)
        ->where('document_id', $tender_pca2->id)
        ->count();
        
        $uploadedListArr = array();
        if($uploadedCount){
                foreach($uploadedList as $item ){

                        $uploadedListArr[$item->document_id] = basename($item->name);

                }
        }

        $uploadedListArrWithToken = array();
        if($uploadedCount){
                foreach($uploadedList as $item ){

                        $uploadedListArrWithToken[$item->document_id] = $item->file_token;

                }
        }

        $cert_type = $this->settings('CERT_TENDER_PCA3','key')->id;
        $certificateInfo = TenderCertificate::where('item_id', $applicationItemInfo->id)
                                                   ->where('type', $cert_type)
                                                   ->orderBy('id', 'DESC')
                                                   ->first();
        
       
        
        $external_global_comment = '';
        $resumbmit_key_id = $this->settings('TENDER_PCA2_REQUEST_TO_RESUBMIT','key')->id;
        $external_comment_key_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        
        $external_comment_query = TenderStatus::where('appication_id',$tenderApplicantId)
                                ->where('comment_type', $external_comment_key_id )
                                ->where('appication_item_id',$applicationItemInfo->id)
                                ->where('status', $resumbmit_key_id )
                                ->orderBy('id', 'desc')
                                ->first();
        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                ?  $external_comment_query->comments
                                : '';


        return response()->json([
                'message'         => "Successfully populated tender details.",
                'tenderInfo'      => $tenderInfo,
                'applicationInfo' => $applicationInfo,
                'applicationItemInfo' => array(
                                       'accepted_amount' => $applicationItemInfo->accepted_amount,
                                       'awarded_date'   => $applicationItemInfo->awarded_date,
                                       'income_tax_1'   => $applicationItemInfo->income_tax_1,
                                       'income_tax_2'   => $applicationItemInfo->income_tax_2,
                                       'income_tax_3'   => $applicationItemInfo->income_tax_3,
                                       'nature_of_contract'   => $applicationItemInfo->nature_of_contract,
                                       'vat_1'   => $applicationItemInfo->vat_1,
                                       'vat_2'   => $applicationItemInfo->vat_2,
                                       'vat_3'   => $applicationItemInfo->vat_3,
                                       'incometax_file' => $applicationItemInfo->income_tax_no,
                                       'vat_file' => $applicationItemInfo->vat_no,
                                       'contract_date_from' => $applicationItemInfo->contract_date_from ? strtotime($applicationItemInfo->contract_date_from) : '',
                                       'contract_date_to' => $applicationItemInfo->contract_date_to ? strtotime($applicationItemInfo->contract_date_to) : '',
                                       'signing_party_name' => $applicationItemInfo->signing_party_name,
                                       'signing_party_designation' => $applicationItemInfo->signing_party_designation,
                                       'signing_party_designation_other' => $applicationItemInfo->signing_party_designation_other,
                                       'date_gap' => $date_gap,
                                       
                ),
              //  'tenderUsers'     => $tenderUsers,
                'tenderItems'     => $tenderItemsArray,
                'status'          => true,
                'applicant_types' => $applicantTypeArr,
                'applicant_sub_types' => $applicantSubTypeArr,
                'applicantType' => $this->settings( $applicationInfo->applicant_type ,'id')->key,
                'applicantSubType' => @$this->settings( $applicationInfo->applicant_sub_type ,'id')->key,
                'tendererSubType' => @$this->settings( $applicationInfo->tenderer_sub_type ,'id')->key,
                'directoListCount' => $directorListCount,
                'directoList' => $directorList,
                'shareholderListCount' => $shareholderListCount,
                'shareholderList' => $shareholderList,
                'memberList' => $memberList,
                'memberListCount' => $memberListCount,
                 'downloadDocs' => $this->generate_awording_files('awording',$applicationItemInfo->id),
                'uploadDocs' => $this->files_for_upload_for_awording('awording',$applicationItemInfo->id ),
                'uploadOtherDocs' => $this->files_for_other_docs_for_tender_aword( $applicationInfo->id, $applicationItemInfo->id),
                'uploadedList' => $uploadedListArr,
                'uploadedListArrWithToken' => $uploadedListArrWithToken,
                'pca1_payment' => $this->settings('PAYMENT_TENDER_PCA1','key')->value,
                'pca2_payment' => $this->settings('PAYMENT_TENDER_PCA2','key')->value,
                'vat' => $this->settings('PAYMENT_GOV_VAT','key')->value,
                'other_tax'=> $this->settings('PAYMENT_OTHER_TAX','key')->value,
                'convinienceFee'=> $this->settings('PAYMENT_CONVENIENCE_FEE','key')->value,
                'certificate' => isset($certificateInfo->certificate_no) && $certificateInfo->certificate_no ? $certificateInfo->certificate_no : '',
                'company' => ($applicationItemInfo->contract_awarded) ? $applicationItemInfo->contract_awarded : $publisherDetails['ministry'].'/'.$publisherDetails['department'].'/'.$tenderInfo->division,
                'external_global_comment' => $external_global_comment,
                'processStatus' =>  $this->settings( $applicationItemInfo->status,'id')->key
                ], 200);

    }


    function uploadForAwording(Request $request){

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $file_type_id = $request->fileTypeId;
        $tender_id = $request->tenderId; 
        $applicant_id = $request->applicantId;
        $item_id = $request->itemId;
        $real_file_name = $request->fileRealName;

       // $applicationInfo = TenderApplication::where('id',$applicant_id)->first();
        $applicationItemInfo = TenderApplicantItem::where('tender_application_id',$applicant_id)
                                                    ->where('tender_item_id', $item_id)
                                                    ->first();
       

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

       $path = 'tender-awording/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$applicant_id.'/'.$item_id;
       $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');

      
       $token = md5(uniqid());

       TenderDocument::where('tender_id', $tender_id)
                     ->where('appication_id',$applicant_id)
                     ->where('application_item_id',$applicationItemInfo->id)
                     ->where('document_id', $file_type_id)
                     ->delete();

       $doc = new TenderDocument;
       $doc->document_id = $file_type_id;
       $doc->path = $path;
       $doc->tender_id = $tender_id;
       $doc->appication_id = $applicant_id;
       $doc->application_item_id = $applicationItemInfo->id;
       $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
       $doc->file_token = $token;
       $doc->name = $real_file_name;
       
       $doc->save();

       $uploadedList =  TenderDocument::where('tender_id', $tender_id)
                                        ->where('appication_id',$applicant_id)
                                        ->where('application_item_id',$applicationItemInfo->id)
                                        ->where('document_id', $file_type_id)
                                        ->get();
        $uploadedCount = TenderDocument::where('tender_id', $tender_id)
        ->where('appication_id',$applicant_id)
        ->where('application_item_id',$applicationItemInfo->id)
        ->where('document_id', $file_type_id)
        ->count();
        
        $uploadedListArr = array();
        if($uploadedCount){
                foreach($uploadedList as $item ){

                        $uploadedListArr[$item->document_id] = basename($item->name);

                }
        }

        $uploadedListArrWithToken = array();
        if($uploadedCount){
                foreach($uploadedList as $item ){

                        $uploadedListArrWithToken[$item->document_id] = $item->file_token;

                }
        }


       return response()->json([
         'message' => 'File uploaded successfully.',
         'status' =>true,
         'name' =>basename($path),
         'error'  => 'no',
         'uploadedList' => $uploadedListArr,
         'uploadedListArrWithToken' => $uploadedListArrWithToken
         
     ], 200);
     }



     function uploadAwardingOtherDocs(Request $request){
        
        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $file_type_id = $request->fileTypeId;
        $tender_id = $request->tenderId; 
        $applicant_id = $request->applicantId;
        $item_id = $request->itemId;
        $real_file_name = $request->fileRealName;
        $file_description = $request->fileDescription;

        $applicationItemInfo = TenderApplicantItem::where('tender_application_id',$applicant_id)
        ->where('tender_item_id', $item_id)
        ->first();

        
  
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

        $path = 'tender-awording/other-docs/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$applicant_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $doc = new TenderDocument;
        $doc->document_id = $file_type_id;
        $doc->path = $path;
        $doc->tender_id = $tender_id;
        $doc->appication_id = $applicant_id;
        $doc->application_item_id = $applicationItemInfo->id;
        $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $doc->multiple_id = mt_rand(1,1555400976);
        $doc->file_token = $token;
        $doc->name = $real_file_name;
        $doc->file_description = $file_description;


        $doc->save();
        $new_doc_id = $doc->id;

        $uploadedListArr = array();
        $uploadedListArrWithToken = array();

        return response()->json([
         'message' => 'File uploaded successfully.',
         'status' =>true,
         'name' =>basename($path),
         'error'  => 'no',
         'uploadedList' => $uploadedListArr,
         'uploadedListArrWithToken' => $uploadedListArrWithToken,

        ], 200);
    
    }




     function removeAworddingFile( Request $request ){

        $tender_id = $request->tenderId;
        $applicant_id = $request->applicantId;
        $file_type_id = $request->fileTypeId;
        $item_id= $request->itemId;

        $applicationItemInfo = TenderApplicantItem::where('tender_application_id',$applicant_id)
                                                    ->where('tender_item_id', $item_id)
                                                    ->first();


        TenderDocument::where('tender_id', $tender_id)
                        ->where('appication_id',$applicant_id)
                        ->where('application_item_id',$applicationItemInfo->id)
                        ->where('document_id', $file_type_id)
                        ->delete();

        $uploadedList =  TenderDocument::where('tender_id', $tender_id)
        ->where('appication_id',$applicant_id)
        ->where('application_item_id',$applicationItemInfo->id)
        ->where('document_id', $file_type_id)
                     ->get();
                        $uploadedCount = TenderDocument::where('tender_id', $tender_id)
                        ->where('appication_id',$applicant_id)
                        ->where('application_item_id',$applicationItemInfo->id)
                        ->where('document_id', $file_type_id)
                        ->count();

        $uploadedListArr = array();
        if($uploadedCount){
           foreach($uploadedList as $item ){

                $uploadedListArr[$item->document_id] = basename($item->name);

           }
        }

        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        'uploadedList' => $uploadedListArr

        ], 200);

        
     }


     function uploadAwordOtherResubmittedDocs(Request $request){
        

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $multiple_id = $request->multiple_id;
        $file_type_id = $request->fileTypeId;
        $tender_id = $request->tenderId; 
        $applicant_id = $request->applicantId;
        $item_id = $request->itemId;
        $real_file_name = $request->fileRealName;

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

        $path = 'tender-awording/other-docs/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$applicant_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'AWARD_TENDER_ADDITIONAL_DOCUMENT')->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
           );
           TenderDocument::where('tender_id', $tender_id)
           ->where('multiple_id', $multiple_id)
           ->where('document_id',$form_other_docs->id )
           ->where('appication_id',$applicant_id)
           ->where('application_item_id',$item_id)
           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id)
            ->update($update_arr);
    
 
            $uploadedListArr = array();
            $uploadedListArrWithToken = array();
    
            return response()->json([
             'message' => 'File uploaded successfully.',
             'status' =>true,
             'name' =>basename($path),
             'error'  => 'no',
             'uploadedList' => $uploadedListArr,
             'uploadedListArrWithToken' => $uploadedListArrWithToken,
             'uploadDocs' =>   $this->files_for_upload( 'depricated_value',$applicant_id),
             'uploadOtherDocs' => $this->files_for_other_docs($applicant_id)
             
            ], 200);



    }


     function removeAwardingOtherDoc(Request $request){

        $file_token = $request->file_token;
        
    
        TenderDocument::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
        ], 200);
    }


     function tenderAwarded( Request $request ){

        $token = $request->item_token;

        $update =  array(

                'status'    => $this->settings('TENDER_PCA2_SUBMITED','key')->id,
                'token'     => null
        );
      $updated = TenderApplicantItem::where('token', $token)->update($update);

       //change status for applied items



        if($updated){
                return response()->json([
                        'message' => 'Successfully awarded.',
                        'status' =>true,
                      

                ], 200);

        } else {

                return response()->json([
                        'message' => 'Failed.',
                        'status' =>false

                 ], 200);
        }
     }



     function tenderAwardingResubmitted( Request $request ){

        $token = $request->item_token;

        $update =  array(

                'status'    => $this->settings('TENDER_PCA2_RESUBMITED','key')->id,
        );
      $updated = TenderApplicantItem::where('token', $token)->update($update);

       //change status for applied items



        if($updated){
                return response()->json([
                        'message' => 'Successfully resubmitted.',
                        'status' =>true,
                      
                ], 200);

        } else {

                return response()->json([
                        'message' => 'Failed.',
                        'status' =>false

                 ], 200);
        }
     }







     function files_for_other_docs_for_publisher($tender_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0,
        );
 
        if(!$tender_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $user = $this->getAuthUser();
        
     //   if($user->tender_user != 'yes') {
     //           return array(
      //                  'docs' => array(),
      //                  'uploadedAll' => false,
     //                   'doc_id' => 0
      //          );
     //   }


        // documents list
        $form_other_docs = Documents::where('key', 'PUBLISHER_ADDITIONAL_DOCUMEMNT')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

       
        $other_docs = PublisherDocument::where('tender_id', $tender_id)
                                      //  ->where('publisher_id', $user->userid )
                                        ->where('document_id', $form_other_docs->id )
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


   function uploadPublisherOtherDocs(Request $request){
        
        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $tender_id = $request->tenderId; 
        $file_description = $request->fileDescription;


        $user = $this->getAuthUser();
        
        if($user->tender_user != 'yes') {
                return response()->json([
                        'message' => 'Invalid User.',
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

        $path = 'tender/publisher/other-docs/'.substr($tender_id,0,2).'/'.$tender_id.'/'.$user->userid;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        $doc = new PublisherDocument;
        $doc->document_id = $file_type_id;
        $doc->path = $path;
        $doc->tender_id = $tender_id;
        $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $doc->multiple_id = mt_rand(1,1555400976);
        $doc->file_token = $token;
        $doc->name = $real_file_name;
        $doc->file_description = $file_description;
        $doc->publisher_id = $user->userid;
        

        $doc->save();
        $new_doc_id = $doc->id;

        $uploadedListArr = array();
        $uploadedListArrWithToken = array();

        return response()->json([
         'message' => 'File uploaded successfully.',
         'status' =>true,
         'name' =>basename($path),
         'error'  => 'no',
         
        ], 200);
    
 
    

    }

    function removePublisherOtherDoc(Request $request){

        $file_token = $request->file_token;
        
    
        PublisherDocument::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
        ], 200);
    }



     /****************reports************/

     function applicationsForUserTenders(Request $request) {
        $tender_id = $request->tenderId;

        $user = $this->getAuthUser();

        $tenders = Tender::where('created_by', $user->userid)
        ->where('status',$this->settings('TENDER_PENDING','key')->id)
        ->get();

        if(isset($tenders[0]->id)){
                foreach($tenders as $tender ) {
                        $tender_items = TenderApplicantItem::leftJoin('tender_applications','tender_application_items.tender_application_id', '=', 'tender_applications.id')
                        ->leftJoin('tender_items','tender_application_items.tender_item_id', '=', 'tender_items.id' )
                        ->leftJoin('tenders','tender_items.tender_id', '=', 'tenders.id' )
                        ->whereIn('tender_application_items.status', array(
                              $this->settings('TENDER_ITEM_AWARDED','key')->id,
                              $this->settings('TENDER_ITEM_APPLIED','key')->id,
                              $this->settings('TENDER_PCA2_SUBMITED','key')->id,
                              $this->settings('TENDER_PCA2_REJECTED','key')->id,
                              $this->settings('TENDER_PCA2_APPROVED','key')->id
                        ))
                        ->where('tenders.id', $tender->id )
                        ->select(
                                 
                                 'tender_items.name as item_name',
                                 'tender_application_items.token as award_token',
                                 'tender_application_items.status as status'
                         )
                        ->get()
                        ->toArray();
                        $tender_items_arr = array();
                }
        }

     }

     


        

            
             

}
