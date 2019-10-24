<?php
namespace App\Http\Controllers\API\v1\societyBulk;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
;
use App\SettingType;
use App\Setting;
use App\User;
use App\People;
use App\Documents;
use App\SocietyBulk;
use App\Society;
use App\SocietyMember;
use App\Address;
use App\SocietyDocument;
use App\SocietyBulkUploadFeeds;

use Storage;
use App;
use URL;
use App\Http\Helper\_helper;

use PDF;

class SocietyBulkController extends Controller
{
    use _helper;
    private $member_min_limit;


    function __construct() {
        $this->member_min_limit = 8;
    }


    function removeSocietiesAction(Request $request){

        $loginUserEmail = $this->clearEmail($request->loginUserEmail);
        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->id;

        $bulks_count = Society::where('type_id', 2)
                                ->where('created_by',$loginUserId)
                                ->where('status',$this->settings('SOCIETY_PROCESSING','key')->id )
                                ->count();
       
        if($bulks_count){
            $bulks = Society::where('type_id', 2)
                            ->where('created_by',$loginUserId)
                            ->where('status',$this->settings('SOCIETY_PROCESSING','key')->id)
                            ->get();
            foreach($bulks  as $bulk ){
                $society_id = $bulk->id;

              
                //remove members
                SocietyMember::where('society_id', $society_id)
                               ->delete();
                //remove Documents
                SocietyDocument::where('society_id', $society_id)->delete();

                 //remove all bulks
                 Society::where('id', $society_id)->delete();

                 //remove uploaded docs feed
                 SocietyBulkUploadFeeds::where('created_by',$loginUserId)->delete();
                 $directory = "bulk-socities/$bulk->bulk_id";
                 @ Storage::deleteDirectory($directory);
       
                
            }
        

            return response()->json([
                'message' => 'Successfully removed all pending societies.',
                'status' =>true,
                'error'  => 'no'
                
                
            ], 200);
            
        } else {

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'error'  => 'yes'
                
                
            ], 200);

        }
        
       
    }


    function removeSociety( Request $request ) {
        $society_id = $request->society_id;

        if(!intval($society_id)) {
            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
             
            ], 200);
        }


         //remove members
       $remove1 =   SocietyMember::where('society_id', $society_id)
         ->delete();
        //remove Documents
        $remove2 =  SocietyDocument::where('society_id', $society_id)->delete();

        //remove all bulks
        $remove3 = Society::where('id', $society_id)->delete();

        if($remove1 && $remove3 ) {
            return response()->json([
                'message' => 'Society successfully deleted.',
                'status' =>true,
             
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed deleting the society.',
                'status' =>false,
                'remove1' => $remove1,
                'remove2' => $remove2,
                'remove3' => $remove3
             
            ], 200);
        }




    }



    private function removeBulk($loginUserId){
        $bulks_count = Society::where('type_id', 2)
                                ->where('created_by',$loginUserId)
                                ->where('status',$this->settings('SOCIETY_PROCESSING','key')->id)->count();

        if($bulks_count){
            $bulks = Society::where('type_id', 2)
                            ->where('created_by',$loginUserId)
                            ->where('status',$this->settings('SOCIETY_PROCESSING','key')->id)
                            ->get();
            foreach($bulks  as $bulk ){
                $society_id = $bulk->id;

                //remove members
                SocietyMember::where('society_id', $society_id)
                               ->delete();
                
            }
        }

        //remove all bulks
        Society::where('type_id', 2)->where('status',$this->settings('SOCIETY_PROCESSING','key')->id)->where('created_by',$loginUserId)->delete();
       
    }

    
    function getBulkSocieties($loginUserId){
        $bulks_count = Society::where('type_id',2)
        ->where('created_by', $loginUserId)
        ->where('status',$this->settings('SOCIETY_PROCESSING','key')->id )
        ->count();
        $bulk_sos_arr = array();
        $bulk_sos_arr['count'] = $bulks_count;
        $bulk_sos_arr['recs'] = array();
        $bulk_sos_arr['dummy_data'] = asset('other/society-bulk-dummy-data.csv');
        $bulk_sos_arr['sample_format'] = asset('other/society-bulk-sample-format.xlsx');
        
        if($bulks_count) {

            $application_doc = Documents::where('key', 'SOCIETY_APPLICATION')->first();
            $affidavit_letter = Documents::where('key', 'SOCIETY_AFFIDAVIT')->first();
            $bank_doc =  Documents::where('key', 'SOCIETY_BANK_LETTER')->first();
            $constitution_doc =  Documents::where('key', 'SOCIETY_CONSTITUTION')->first();
            $approval_doc = Documents::where('key', 'SOCIETY_APPROVAL_LETTER')->first();
            $nic_pass_doc = Documents::where('key','SOCIETY_NIC_PASSPORT')->first();
            $member_list_doc = Documents::where('key', 'SOCIETY_LIST')->first();
            $other_doc = Documents::where('key', 'SOCIETY_OTHER')->first();
            $officebarer_list_doc = Documents::where('key', 'SOCIETY_OFFICE_BARER')->first();



            $bulks = Society::where('type_id',2)
                              ->where('created_by', $loginUserId)
                              ->where('status',$this->settings('SOCIETY_PROCESSING','key')->id )
                              ->orderBy('id', 'DESC')
                              ->get();
            foreach($bulks as $so ){

                $has_all_uploaded_str = '';

                $bulk_sos = array();
                $bulk_sos['id'] = $so->id;
                $bulk_sos['name'] = $so->name;
                $bulk_sos['name_si'] = $so->name_si;
                $bulk_sos['name_ta'] = $so->name_ta;
                $bulk_sos['description'] = $so->abbreviation_desc;
                $bulk_sos['address'] = $so->address;
                $bulk_sos['address_si'] = $so->address_si;
                $bulk_sos['address_ta'] = $so->address_ta;
                $bulk_sos['name_of_society'] = $so->name_of_society;
                $bulk_sos['place_of_office'] = $so->place_of_office;
                $bulk_sos['name_of_society'] = $so->name_of_society;
                $bulk_sos['whole_of_the_objects'] = $so->whole_of_the_objects;
                $bulk_sos['funds'] = $so->funds;
                $bulk_sos['terms_of_admission'] = $so->terms_of_admission;
                $bulk_sos['condition_under_which_any'] = $so->condition_under_which_any;
                $bulk_sos['fines_and_foreitures'] = $so->fines_and_foreitures;
                $bulk_sos['mode_of_holding_meetings'] = $so->mode_of_holding_meetings;
                $bulk_sos['manner_of_rules'] = $so->manner_of_rules;
                $bulk_sos['appointment_and_removal_committee'] = $so->appointment_and_removal_committee;
                $bulk_sos['case_of_society'] = $so->case_of_society;
                $bulk_sos['investment_of_funds'] = $so->investment_of_funds;
                $bulk_sos['keeping_accounts'] = $so->keeping_accounts;
                $bulk_sos['audit_of_the_accounts'] = $so->audit_of_the_accounts;
                $bulk_sos['annual_returns'] = $so->annual_returns;
                $bulk_sos['number_of_members'] = $so->number_of_members;
                $bulk_sos['inspection_of_the_books'] = $so->inspection_of_the_books;
                $bulk_sos['disputes_manner'] = $so->disputes_manner;
                $bulk_sos['members'] = array();
                $bulk_sos['uploadDocs'] = array(

                    'docs' =>array(

                        'other' =>array(),
                        'member' => array()
                    ),
                    'uploadedAll'=> false
                );

                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $application_doc->name. ' for ' . $so->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $application_doc->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['society_id'] = $so->id;
                $file_row['member_info'] = null;

                $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                        ->where('document_id', $application_doc->id )
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;

                /*if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
                                                           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                           ->where('comment_type', 'external')
                                                           ->first();


                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }*/
               
                                    

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
                $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );

                $bulk_sos['uploadDocs']['docs']['other'][] = $file_row;


                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $bank_doc->name. ' for ' . $so->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $bank_doc->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['society_id'] = $so->id;
                $file_row['member_info'] = null;

                $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                        ->where('document_id', $bank_doc->id )
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;

                /*if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
                                                           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                           ->where('comment_type', 'external')
                                                           ->first();


                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }*/
               
                                    

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
                $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );

                $bulk_sos['uploadDocs']['docs']['other'][] = $file_row;


                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $constitution_doc->name. ' for ' . $so->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $constitution_doc->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['society_id'] = $so->id;
                $file_row['member_info'] = null;

                $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                        ->where('document_id', $constitution_doc->id )
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;

                /*if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted

                        $commentRow = TenderDocumentStatus::where('tender_document_id', $uploadedDoc->id )
                                                           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                           ->where('comment_type', 'external')
                                                           ->first();


                        $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';

                }*/
               
                                    

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
                $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );

                $bulk_sos['uploadDocs']['docs']['other'][] = $file_row;



                /********apprval letter ******/

                $societyName = $so->name;
                $societyName = strtoupper($societyName);

                $nameWords = explode(' ', $societyName);
                $checkLimitedWord = false;
                $checkSocietyWord = false;
                $checkLimitedWord = (in_array('LIMITED', $nameWords));
                $checkSocietyWord = (in_array('SOCIETY', $nameWords));

                if(!( $checkLimitedWord && $checkSocietyWord) ) {
                   
                    $file_row = array();
                    $file_row['doc_comment'] = '';
                    $file_row['doc_status'] = 'DOCUMENT_PENDING';
                    $file_row['is_required'] = true;
                    $file_row['file_name'] = $approval_doc->name. ' for ' . $so->name;
                    $file_row['file_type'] = '';
                    $file_row['dbid'] = $approval_doc->id;
                    $file_row['file_description'] = '';
                    $file_row['applicant_item_id'] = null;
                    $file_row['member_id'] = null;
                    $file_row['society_id'] = $so->id;
                    $file_row['member_info'] = null;

                    $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                            ->where('document_id', $approval_doc->id )
                                            ->first();
                    $uploadeDocStatus = @$uploadedDoc->status;                 

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
                    $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );

                    $bulk_sos['uploadDocs']['docs']['other'][] = $file_row;
                }
                /**** approval letter end */


                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = true;
                $file_row['file_name'] = $officebarer_list_doc->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $officebarer_list_doc->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['society_id'] = $so->id;
                $file_row['member_info'] = null;

                $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                        ->where('document_id', $officebarer_list_doc->id )
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;

                
               
                                    

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
                $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );

                $bulk_sos['uploadDocs']['docs']['other'][] = $file_row;




                
                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = false;
                $file_row['file_name'] = $member_list_doc->name. ' for ' . $so->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $member_list_doc->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['society_id'] = $so->id;
                $file_row['member_info'] = null;

                $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                        ->where('document_id', $member_list_doc->id )
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;

                            

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
                $has_all_uploaded_str = $has_all_uploaded_str.'1';

                $bulk_sos['uploadDocs']['docs']['other'][] = $file_row;



                $file_row = array();
                $file_row['doc_comment'] = '';
                $file_row['doc_status'] = 'DOCUMENT_PENDING';
                $file_row['is_required'] = false;
                $file_row['file_name'] = $other_doc->name. ' for ' . $so->name;
                $file_row['file_type'] = '';
                $file_row['dbid'] = $other_doc->id;
                $file_row['file_description'] = '';
                $file_row['applicant_item_id'] = null;
                $file_row['member_id'] = null;
                $file_row['society_id'] = $so->id;
                $file_row['member_info'] = null;

                $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                        ->where('document_id', $other_doc->id )
                                        ->first();
                $uploadeDocStatus = @$uploadedDoc->status;

                            

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
                $has_all_uploaded_str = $has_all_uploaded_str.'1';

                $bulk_sos['uploadDocs']['docs']['other'][] = $file_row;
                





                $members_count = SocietyMember::where('society_id', $so->id )
                                                ->where('is_affidavit', 1)
                                                ->count();
                $members = null;
                if($members_count) {
                    $members = SocietyMember::where('society_id', $so->id )
                                              ->where('is_affidavit', 1)
                                              ->orderBy('id', 'ASC')
                                              ->limit($this->member_min_limit)
                                              ->offset(0)
                                              ->get();

                    foreach($members as $m ){

                        $addrdessInfo = Address::where('id', $m->address_id)->first();
                         
                        $row = array();
                        $row['member_id'] = $m->id;
                        $row['designation_type'] = $m->designation_type;
                        $row['designation'] = $m->designation;
                        $row['type']    = $m->type;
                        $row['full_name'] = $m->full_name;
                     //   $row['last_name'] = $m->last_name;
                        $row['nic'] = $m->nic;
                        $row['contact_no'] = $m->contact_no;
                        $row['address1'] = $addrdessInfo->address1;
                        $row['address2'] = $addrdessInfo->address2;
                        $row['city'] = $addrdessInfo->city;
                        $row['district'] = $addrdessInfo->district;
                        $row['province'] = $addrdessInfo->province;
                        $row['divisional_secretariat'] = $m->divisional_secretariat;

                        $bulk_sos['members'][] = $row;


                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        $file_row['is_required'] = true;
                        $file_row['file_name'] = "$affidavit_letter->name for $m->full_name";
                        $file_row['file_type'] = '';
                        $file_row['dbid'] = $affidavit_letter->id;
                        $file_row['file_description'] = '';
                        $file_row['applicant_item_id'] = null;
                        $file_row['member_id'] = $m->id;
                        $file_row['society_id'] = $so->id;
                        $file_row['member_info'] = $row; 
                        $file_row['nic_doc'] = null;

                        $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                                ->where('document_id', $affidavit_letter->id )
                                                ->where('member_id',$m->id )
                                                ->first();
                        $uploadeDocStatus = @$uploadedDoc->status;


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
                        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );

                        
                       
                        $file_row_nic = array();
                        $file_row_nic['doc_comment'] = '';
                        $file_row_nic['doc_status'] = 'DOCUMENT_PENDING';
                        $file_row_nic['is_required'] = true;
                        $file_row_nic['file_name'] = "$nic_pass_doc->name for $m->full_name";
                        $file_row_nic['file_type'] = '';
                        $file_row_nic['dbid'] = $nic_pass_doc->id;
                        $file_row_nic['file_description'] = '';
                        $file_row_nic['applicant_item_id'] = null;
                        $file_row_nic['member_id'] = $m->id;
                        $file_row_nic['society_id'] = $so->id;
                        $file_row_nic['member_info'] = $row;
                        $file_row_nic['nic_doc'] = array();

                        $uploadedDoc =  SocietyDocument::where('society_id', $so->id)
                                                ->where('document_id', $nic_pass_doc->id )
                                                ->where('member_id',$m->id )
                                                ->first();
                        $uploadeDocStatus = @$uploadedDoc->status;

                       
                        $file_row_nic['uploaded_path'] =  isset($uploadedDoc->file_token)  &&
                                                    isset($uploadedDoc->path ) &&
                                                    isset($uploadedDoc->name) &&
                                                    $uploadedDoc->file_token &&
                                                    $uploadedDoc->path &&
                                                    $uploadedDoc->name ? $uploadedDoc->name : '';
                        $file_row_nic['uploaded_token'] =  isset($uploadedDoc->file_token)  &&
                                                    isset($uploadedDoc->path ) &&
                                                    isset($uploadedDoc->name) &&
                                                    $uploadedDoc->file_token &&
                                                    $uploadedDoc->path &&
                                                    $uploadedDoc->name ? $uploadedDoc->file_token : '';
                        
                        $file_row['nic_doc'] = $file_row_nic;
                        
                        $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row_nic['uploaded_path'] !== '' &&  $file_row_nic['uploaded_token'] !== '' ) );

                        $bulk_sos['uploadDocs']['docs']['member'][] = $file_row;

                    }
                }

                $bulk_sos['uploadDocs']['uploadedAll'] =  !( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '0') !== false ) ;

                $bulk_sos['downloadDocs'][] = $this->generate_files($so,$members,$bulk_sos['members'] );

                $bulk_sos_arr['recs'][] = $bulk_sos;

            }

        }

        return $bulk_sos_arr;

       
        
    }

    function getBulkSocietiesList(Request $request){

        $loginUserEmail = $this->clearEmail($request->loginUserEmail);

        

        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->id;

        $bulk_record = SocietyBulk::where('created_by', $loginUserId)->first();

        $bulk_sos_arr = $this->getBulkSocieties($loginUserId);

        return response()->json([
            'message' => 'Bulk Societies populated',
            'status' =>true,
            'records'  => $bulk_sos_arr,
            'bulk_id' => isset($bulk_record->id) ? $bulk_record->id : null
 
        ], 200);
     


    }


   
      function generate_files($so_record,$members = null , $bulk_members){

          $generated_files = array(
              'docs' => array(),

          );

        $president = null;
        $secretary = null;
        $treasure = null;
        $commityMemebers = array();

        foreach($bulk_members as $m ){
            
            if($m['designation_type'] == 1 ) {
                $president = $m;
            }

            if($m['designation_type'] == 2 ){
                $secretary = $m;
            }

            if($m['designation_type'] == 3 ){
                $treasure = $m;
            }

            if($m['designation_type'] == 4 ){
                $commityMemebers[] = $m;
            }


        }

       
        $file_name_key = 'SOCIETY_APPLICATION';
        $file_name = 'Society Application';
  
        $data = array(
                'public_path' => public_path(),
                'eroc_logo' => url('/').'/images/forms/eroc.png',
                'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                'css_file' => url('/').'/images/forms/form1/form1.css',
                'so' => $so_record,
                'president'=> $president,
                'secretary' => $secretary,
                'treasure' => $treasure,
                'commityMemebers' => $commityMemebers,
                'allMembers' => $bulk_members

              
        );
                      
        $directory = "society-bulk/application/$so_record->id";
        Storage::makeDirectory($directory);
  
 
        $view = 'forms.'.'so_bulk_application';
        $pdf = PDF::loadView($view, $data);
        $pdf->save(storage_path("app/$directory")."/society-$so_record->id.pdf");
  
        $file_row = array();
                          
        $file_row['name'] = $file_name.' for '.$so_record->name;
        $file_row['file_name_key'] = $file_name_key;
        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/society-$so_record->id.pdf");
        $generated_files['docs'][] = $file_row;

         /*************************** */

         if($members){

            foreach($members as $m ){

                $file_name_key = 'SOCIETY_AFFIDAVIT';
                $file_name = "Affidavit for $m->full_name";
    
                $directory = "society-bulk/affidavit/$so_record->id/$m->id";
                Storage::makeDirectory($directory);
    
                $data = array(
                    'public_path' => public_path(),
                    'eroc_logo' => url('/').'/images/forms/eroc.png',
                    'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                    'css_file' => url('/').'/images/forms/form1/form1.css',
                    'm' => $m,
                    'm_address' => Address::where('id', $m->address_id)->first(),
                    'so' => $so_record,
                  
                );
        
        
                $view = 'forms.'.'so_member_affidavit';
                $pdf = PDF::loadView($view, $data);
                $pdf->save(storage_path("app/$directory")."/society-$so_record->id-member-$m->id.pdf");
        
                $file_row = array();
                                
                $file_row['name'] = $file_name;
                $file_row['file_name_key'] = $file_name_key;
                $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/society-$so_record->id-member-$m->id.pdf");
                $generated_files['docs'][] = $file_row;
               
    
            }

         }
         
        


    
        
         return $generated_files;
      }

    

    function validateUploadingList($uploadFile){

        $duplicate_societies = array();
        $directory = 'bulk-socities';
       @ Storage::deleteDirectory($directory);
        $path = Storage::putFile($directory, $uploadFile);
 
        $file_path = str_replace('public','',url('/')).Storage::url("app/$path");

        if (($handle = fopen($file_path, "r")) !== FALSE) {
 
        


        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
        //   echo "<p> $num fields in line $row: <br /></p>\n";

        
        if( isset($data[0]) && $data[0] === 'SO') {

           

            $society_name = $data[1];

          


            $check_count = Society::where('name', $society_name)->count();

           
            if($check_count) {
                return false;
                die();

            }

         }

        }

        fclose($handle);

      }

      return true;

    }

    function uploadBulkSocieties(Request $request){

        $real_file_name = $request->fileRealName;
        $upload_method = $request->uploadMethod;

        $loginUserEmail = $this->clearEmail($request->loginUserEmail);

        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->id;

        $bulk_record_count = SocietyBulk::where('created_by', $loginUserId)->count();
        $bulk_id = null;
        if($bulk_record_count) {
            $bulk_record = SocietyBulk::where('created_by', $loginUserId)->first();
            $bulk_id = $bulk_record->id;
        } else {
             $bulk_record = new SocietyBulk();
             $bulk_record->created_by = $loginUserId;
             $bulk_record->name = $loginUserInfo->email.' bulk';
             $bulk_record->save();
             $bulk_id = $bulk_record->id;

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

        $directory = "bulk-socities/$bulk_id";
    //    @ Storage::deleteDirectory($directory);
        $path = Storage::putFile($directory, $request->file('uploadFile'));
 
        $file_path = str_replace('public','',url('/')).Storage::url("app/$path");


        if($path){
            $feed = new SocietyBulkUploadFeeds;
          //  $feed->id = mt_rand(1,200000000);
            $feed->bulk_id = $bulk_id;
            $feed->created_by = $loginUserId;
            $feed->doc_path = $file_path;
            $feed->doc_name = basename($real_file_name);
            $feed->save();
        }

        if (($handle = fopen($file_path, "r")) !== FALSE) {

            $society_id = null;
            $no_of_members=0;
            $total_socities =0;
            $total_ignored =0;
            $total_duplicated =0;
            $last_added_society = null;

           
          /* if(  !$this->validateUploadingList($request->file('uploadFile')) ){

            return response()->json([
                'message' => 'Some society items are already exist.',
                'status' =>false,
                'error'  => 'yes',
                'total_submitted' => null,
                'total_ignored'  => null,
                'total_success' => null,
                'total_duplicated' => null,
                'total_exist' => Society::where('type_id', 2)->where('created_by', $loginUserId)->count()
     
            ], 200);
           }*/

            $row = 1;

            

            if($upload_method === 'reset'){
                //remove all bulk records first
                $this->removeBulk($loginUserId);
            }
            
          //  print_r(fgetcsv($handle, 1000, ","));
           // die('fyck');

           

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if($row <= 4) {
                    $row++;
                    continue;
                }
                $num = count($data);
            //   echo "<p> $num fields in line $row: <br /></p>\n";

           

            
            if( isset($data[0]) && $data[0] === 'SO') {

                $total_socities++;

                $no_of_members_of_prev = ( $society_id ) ? SocietyMember::where('society_id', $society_id)->count() : 0;

                if($no_of_members_of_prev < $this->member_min_limit  && $society_id ){

                    //remove previous society members
                    SocietyMember::where('society_id', $society_id)->delete();
                    //remove previous society
                    Society::where('id', $society_id)->delete();

                    $total_ignored++;

                }


                $s = new Society;
                $s->type_id = 2; //set bulk flag
                $s->bulk_id = $bulk_id;
                $s->name =  isset($data[1]) && $data[1] ? $data[1] : null;
              //  $s->name_si =  isset($data[2]) && $data[2] ? $data[2] : null;
              //  $s->name_ta =  isset($data[3]) && $data[3] ? $data[3] : null;
                $s->abbreviation_desc =  isset($data[2]) && $data[2] ? $data[2] : null;
                $s->address = isset($data[3]) && $data[3] ? $data[3] : null;
               // $s->address_si = isset($data[4]) && $data[4] ? $data[4] : null;
              //  $s->address_ta = isset($data[5]) && $data[5] ? $data[5] : null;
                $s->name_of_society =  isset($data[4]) && $data[4] ? $data[4] : null;
                $s->place_of_office =  isset($data[5]) && $data[5] ? $data[5] : null;
                $s->whole_of_the_objects =  isset($data[6]) && $data[6] ? $data[6] : null;
                $s->funds =  isset($data[7]) && $data[7] ? $data[7] : null;
                $s->terms_of_admission =  isset($data[8]) && $data[8] ? $data[8] : null;
                $s->condition_under_which_any =  isset($data[9]) && $data[9] ? $data[9] : null;
                $s->fines_and_foreitures =  isset($data[10]) && $data[10] ? $data[10] : null;
                $s->mode_of_holding_meetings =  isset($data[11]) && $data[11] ? $data[11] : null;
                $s->manner_of_rules =  isset($data[12]) && $data[12] ? $data[12] : null;
                $s->appointment_and_removal_committee =  isset($data[13]) && $data[13] ? $data[13] : null;
                $s->case_of_society =  isset($data[14]) && $data[14] ? $data[14] : null;
                $s->investment_of_funds =  isset($data[15]) && $data[15] ? $data[15] : null;
                $s->keeping_accounts =  isset($data[16]) && $data[16] ? $data[16] : null;
                $s->audit_of_the_accounts =  isset($data[17]) && $data[17] ? $data[17] : null;
                $s->annual_returns =  isset($data[18]) && $data[18] ? $data[18] : null;
                $s->number_of_members =  isset($data[19]) && $data[19] ? $data[19] : null;
                $s->inspection_of_the_books =  isset($data[20]) && $data[20] ? $data[20] : null;
                $s->disputes_manner =  isset($data[21]) && $data[21] ? $data[21] : null;
                $s->created_by =  $loginUserId;
                $s->status = $this->settings('SOCIETY_PROCESSING','key')->id;


                $societyName = isset($data[1]) && $data[1] ? $data[1] : '';
                $societyName = strtoupper($societyName);

                $nameWords = explode(' ', $societyName);
                $checkLimitedWord = false;
                $checkSocietyWord = false;
                $checkLimitedWord = (in_array('LIMITED', $nameWords));
                $checkSocietyWord = (in_array('SOCIETY', $nameWords));

                if($checkLimitedWord && $checkSocietyWord) {
                    $s->approval_need =  0;
                }else {
                    $s->approval_need =  1;
                }




                $s->save();
                $society_id = $s->id;
                $last_added_society = $society_id;

             }

             if( isset($data[0]) && $data[0] === 'M') {
                 $no_of_members++;

                $societyAddress = new Address;
                $societyAddress->address1 = isset($data[7]) && $data[7] ? $data[7] : null;
                $societyAddress->address2 = isset($data[8]) && $data[8] ? $data[8] : null;
                $societyAddress->city = isset($data[9]) && $data[9] ? $data[9] : null;
                $societyAddress->district = isset($data[10]) && $data[10] ? $data[10] : null;
                $societyAddress->province = isset($data[11]) && $data[11] ? $data[11] : null;
                $societyAddress->gn_division = isset($data[12]) && $data[12] ? $data[12] : null;
                $societyAddress->postcode = isset($data[13]) && $data[13] ? $data[13] : null;

                $societyAddress->save();
                $societyAddressId = $societyAddress->id;

                $sm = new SocietyMember;
                $sm->society_id =  $society_id;
                $sm->designation_type =  isset($data[1]) && $data[1] ? $data[1] : null;
                $sm->designation =  isset($data[2]) && $data[2] ? $data[2] : null;
                $sm->type =  isset($data[3]) && $data[3] ? $data[3] : null;
                $sm->full_name =  isset($data[4]) && $data[4] ? $data[4] : null;
               // $sm->last_name =  isset($data[4]) && $data[4] ? $data[4] : null;
                $sm->nic =  isset($data[5]) && $data[5] ? $data[5] : null;
                $sm->contact_no =  isset($data[6]) && $data[6] ? $data[6] : null;
                $sm->divisional_secretariat = isset($data[14]) && $data[14] ? $data[14] : null;
                $sm->is_affidavit =  isset($data[15]) && intval($data[15]) ? $data[15] : 0;
                $sm->address_id = $societyAddressId;
                $sm->save();
                $society_member_id = $sm->id;

             }

             //$row++;

              
            }
        fclose($handle);
        }

        //check last added item
        $no_of_members_of_last_added = ( $last_added_society ) ? SocietyMember::where('society_id', $last_added_society)->count() : 0;

        if($no_of_members_of_last_added < $this->member_min_limit  && $last_added_society ){

            //remove previous society members
            SocietyMember::where('society_id', $last_added_society)->delete();
            //remove previous society
            Society::where('id', $last_added_society)->delete();

            $total_ignored++;

        }


        return response()->json([
            'message' => 'Bulk socities added.',
            'status' =>true,
            'total_submitted' => $total_socities,
            'total_ignored'  => $total_ignored,
            'total_success' => ($total_socities - $total_ignored ),
            'total_duplicated' => $total_duplicated,
            'total_exist' => Society::where('type_id', 2)->where('created_by', $loginUserId)->count()

        ], 200);


   

  }
  
  function removeFile(Request $request ) {

    $file_type_id = $request->fileTypeId;
    $society_id = $request->societyId;
    $member_id = (int) $request->memberId;
   

    $query = SocietyDocument::query();
    $query->where('document_id',$file_type_id);
    $query->where('society_id',$society_id);
    if ($member_id) {
     $query->where('member_id', $member_id );
    }
    $query->delete();

    return response()->json([
        'message' => 'File removed successfully.',
        'status' =>true,
      

], 200);


  }

  function upload(Request $request){

    $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
    $real_file_name = $request->fileRealName;
    $file_type_id = $request->fileTypeId;
    $society_id = $request->societyId;
    $member_id = (int) $request->memberId;

    $loginUserEmail = $this->clearEmail($request->loginUserEmail);

    $loginUserInfo = User::where('email', $loginUserEmail)->first();
    $loginUserId = $loginUserInfo->id;
   

    $size = $request->file('uploadFile2')->getClientSize() ;
    $ext = $request->file('uploadFile2')->getClientMimeType();

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

   $path = ($member_id) ? 'society-bulk/'.$society_id.'/'.$file_type_id.'/'.$member_id : 'society-bulk/'.$society_id.'/'.$file_type_id;
   $path=  $request->file('uploadFile2')->storeAs($path,$file_name,'sftp');

   $token = md5(uniqid());

    $query = SocietyDocument::query();
    $query->where('document_id',$file_type_id);
    $query->where('society_id',$society_id);
    if ($member_id) {
     $query->where('member_id', $member_id );
    }
    $query->delete();
    

   $doc = new SocietyDocument;
   $doc->document_id = $file_type_id;
   $doc->society_id = $society_id;
   $doc->path = $path;
   $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
   $doc->file_token = $token;
   $doc->name = $real_file_name;
   if ($member_id) {
      $doc->member_id = $member_id;
   }
   
   $doc->save();
  
   return response()->json([
     'message' => 'File uploaded successfully.',
     'status' =>true,
     'name' =>basename($path),
     'error'  => 'no',
     'records' =>   $this->getBulkSocieties($loginUserId)
     
 ], 200);
 }


 function updateSocietyOptional(Request $request ) {

    if(!( isset($request->societyId) && $request->societyId)) {
        return response()->json([
            'message' => 'Invalid Parameters',
            'status' =>false,
          ], 200);
    }


    $result = array(
        'name_si' => isset($request->name_si) && $request->name_si ? trim($request->name_si) : null,
        'name_ta' => isset($request->name_ta) && $request->name_ta ? trim($request->name_ta) : null,
        'address_si' => isset($request->address_si) && $request->address_si ? trim($request->address_si) : null,
        'address_ta' => isset($request->address_ta) && $request->address_ta ? trim($request->address_ta) : null,
         
    );
   
     $update = Society::where('id', $request->societyId)->update($result);
     
     if($update) {
        return response()->json([
            'message' => 'Successfully updated',
            'status' =>true,
          ], 200);
     } else {
        return response()->json([
            'message' => 'Failed updating info. Please try again.',
            'status' =>false,
          ], 200);
     }
     

 }



} //end class