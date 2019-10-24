<?php
namespace App\Http\Controllers\API\v1\Secretary\Changes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use App\Address;
use App\Secretary;
use App\SecretaryFirm;
use App\People;
use App\User;
use App\SettingType;
use App\Setting;
use App\SecretaryWorkingHistory;
use App\SecretaryCertificate;
use App\SecretaryFirmPartner;
use App\SecretaryDocument;
use App\SecretaryComment;
use App\DocumentsGroup;
use App\Documents;
use App\SecretaryCertificateRequest;
use App\SecretaryDocumentStatus;
use App\Translations;
use App\SecretaryChnagesFirm;
use App\SecretaryChnagesFirmPartners;

use Storage;
use Cache;
use App;
use URL;
use PDF;
class SecretaryFirmChangesController extends Controller
{
    use _helper;

    private function has_request_record($secretory_id) {
        
        $accepted_request_statuses = array(
            $this->settings('SECRETARY_CHANGE_APPROVED','key')->id,
            $this->settings('SECRETARY_CHANGE_REJECTED','key')->id
        );
       
        $record_count = SecretaryChnagesFirm::where('secretory_id', $secretory_id)
                                  ->whereNotIn('status', $accepted_request_statuses )
                                   ->count();
        if( $record_count === 1 ) {
            $record = SecretaryChnagesFirm::where('secretory_id', $secretory_id)
            ->whereNotIn('status', $accepted_request_statuses )
             ->first();
    
            return $record->id;
        } else {
            return false;
        }
    }


    private function valid_request_operation($secretory_id){

        $secInfo = SecretaryFirm::where('id', $secretory_id)->first();

        $secFirmType = $secInfo->type;

        
        if( 
           !(
            isset($secInfo->status) &&
            'SECRETARY_APPROVED' ==  $this->settings( $secInfo->status,'id')->key  
            
            )
         ) {
            return false;
        }

        $accepted_request_statuses = array(
            $this->settings('SECRETARY_CHANGE_APPROVED','key')->id,
            $this->settings('SECRETARY_CHANGE_REJECTED','key')->id
        );
    
        $exist_request_id = $this->has_request_record($secretory_id);
    
        if($exist_request_id) {
    
            return $exist_request_id; 
             
        } else {

            $user = $this->getAuthUser();
    
            $record = new SecretaryChnagesFirm;
            $record->secretory_id = $secretory_id;
            $record->status = $this->settings('SECRETARY_CHANGE_PROCESSING','key')->id;
            $record->created_by = $user->userid;
            $record->old_name = $secInfo->name;
            $record->old_name_si = $secInfo->name_si;
            $record->old_name_ta = $secInfo->name_ta;
            $record->old_address_id = $secInfo->address_id;
            $record->old_email_address = $secInfo->email;
            $record->old_mobile_no = $secInfo->mobile;
            $record->old_tel_no =  $secInfo->telephone;
            $record->type = $secFirmType;
            $record->save();
            $record_id =  $record->id;



            $existing_partners = SecretaryFirmPartner::where('firm_id', $secretory_id)
            ->get()->toArray();

            if(count($existing_partners)) {
                foreach($existing_partners as $partner ) {
                    
                    $change_partner = new SecretaryChnagesFirmPartners;
                    $change_partner->old_which_qualified = $partner['which_qualified'];
                    $change_partner->old_name = $partner['name'];
                    $change_partner->old_address = $partner['address'];
                    $change_partner->old_citizenship = $partner['citizenship'];
                    $change_partner->old_professional_qualifications = $partner['professional_qualifications'];
                    $change_partner->new_which_qualified = null;
                    $change_partner->nic = strtoupper($partner['nic']);
                    $change_partner->change_id = $record_id;
                    $change_partner->partner_id = $partner['id'];
                    $change_partner->status = $this->settings('UNCHANGED','key')->id;
                    $change_partner->save();

                }
            }
    
            if($record_id ) {
                return $record_id;
            }else{
                return false;
            }
        }
        
    }

    private function cleanUpforAlterOption($request_id) {

        $record = SecretaryChnagesFirm::where('id', $request_id)->first();

        $alterTypes = explode(',', $record->change_types);

        $update_rec = array();
        

        if( !in_array('SECRETARY_CHANGE_NAME', $alterTypes) ) {
            $update_rec['new_name'] = null;
            $update_rec['new_name_si'] = null;
            $update_rec['new_name_ta'] = null;
        }
        if( !in_array('SECRETARY_CHANGE_ADDRESS', $alterTypes) ) {

            if($record->new_address_id) {
                Address::where('id', $record->new_address_id)->delete();
            }
            $update_rec['new_address_id'] = null; 

        }
        if( !in_array('SECRETARY_CHANGE_TELEPHONE', $alterTypes) ) {
            $update_rec['new_mobile_no'] = null; 
            $update_rec['new_tel_no'] = null;
        }
        if( !in_array('SECRETARY_CHANGE_EMAIL', $alterTypes) ) {
            $update_rec['new_email_address'] = null; 
        }

        if( !in_array('SECRETARY_CHANGE_PARTNERS', $alterTypes) ) {

            //remove all partners

            SecretaryChnagesFirmPartners::where('change_id', $request_id )->delete();

            //then reset partners

            $existing_partners = SecretaryFirmPartner::where('firm_id', $record->secretory_id)
            ->get()->toArray();

            if(count($existing_partners)) {
                foreach($existing_partners as $partner ) {
                    
                    $change_partner = new SecretaryChnagesFirmPartners;
                    $change_partner->old_which_qualified = $partner['which_qualified'];
                    $change_partner->old_name = $partner['name'];
                    $change_partner->old_address = $partner['address'];
                    $change_partner->old_citizenship = $partner['citizenship'];
                    $change_partner->old_professional_qualifications = $partner['professional_qualifications'];
                    $change_partner->new_which_qualified = null;
                    $change_partner->nic = strtoupper($partner['nic']);
                    $change_partner->change_id = $record->id;
                    $change_partner->partner_id = $partner['id'];
                    $change_partner->status = $this->settings('UNCHANGED','key')->id;
                    $change_partner->save();

                }
            }
        }

        if (!empty($update_rec)) {
            $update = SecretaryChnagesFirm::where('id', $request_id)->update($update_rec);
       }
        
        
    }

    function getProvincesDisctrictsCities() {


        $provinces_cache = Cache::rememberForever('provinces_cache', function () {
            $provinces_results = Province::all();

            $provinces = array();

            foreach($provinces_results as $p ) {

                 $rec = array(
                    'id' => $p->id,
                    'name' => $p->description_en
                 );
                 $provinces[] = $rec;
                 
            }

             return $provinces;
        });

        $districts_cache = Cache::rememberForever('districts_cache', function () {
            $districts_results = District::all();
            $districts = array();

            foreach( $districts_results as $d ) {

                $provinceName = Province::where('id', $d->province_code)->first();
                $rec = array(
                    'id' => $d->id,
                    'name' => $d->description_en,
                    'provinceName' => $provinceName->description_en
                );
                $districts[] = $rec;
            }
            return $districts;
        });

        $cities_cache = Cache::rememberForever('cities_cache', function () {
            $city_results = City::all();
            $cities = array();

            foreach( $city_results as $c ) {

                $districtName = District::where('id', $c->district_code )->first();
                $rec = array(
                    'id' => $c->id,
                    'name' => $c->description_en,
                    'districtName' => $districtName->description_en
                );
                $cities[] = $rec;
            }
            return $cities;
        });

        $gns_cache = Cache::rememberForever('gns_cache', function () {
            $gn_results = GNDivision::orderBy('description_en', 'asc')->get();
            $gns = array();

            foreach( $gn_results as $g ) {

                $cityName = City::where('id', $g->city_code )->first();

                $rec = array(
                    'id' => $g->id,
                    'name' => $g->description_en,
                    'cityName' => @$cityName->description_en
                );
                $gns[] = $rec;
            }
            return $gns;
        });

        return array(
            'provinces' => $provinces_cache,
            'districts' => $districts_cache,
            'cities' => $cities_cache,
            'gns'   => $gns_cache
        );
        

         
}

    public function loadHeavyData(Request $request){

        if(!$request->secretaryId){
    
            return response()->json([
                'message' => 'We can \'t find a secretary.',
                'status' =>false
            ], 200);
        }
    
       
    
        return response()->json([
            'message' => 'Data Loaded.',
            'status' =>true,
            'data'   => array(
                    'pdc' => $this->getProvincesDisctrictsCities(),
            )
        ], 200);
    
        
    }

    public function loadData(Request $request) {
        if(!$request->secretaryId){

            return response()->json([
                'message' => 'We can \'t find the Secretary.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $secInfo = SecretaryFirm::where('id',$request->secretaryId)->first();

        if( ! isset($secInfo->id)) {

            return response()->json([
                'message' => 'We can \'t find the secretary firm information.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);

        }

        $request_id = $this->valid_request_operation($request->secretaryId);

        $record = SecretaryChnagesFirm::where('id', $request_id)
             ->first();

        if(!$request_id ) {
            return response()->json([
                'message' => 'Invalid Request status.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }
        $this->cleanUpforAlterOption($request_id);

        $alter_options_group = SettingType::where('key', 'SECRETARY_CHANGE_TYPES')->first();
        $alter_options = Setting::where('setting_type_id', $alter_options_group->id )
        ->get();
        $alterTypes = explode(',',$record->change_types);
        $alterTypes = $alterTypes[0] ? $alterTypes : array();

        $alter_option_array = array();

        foreach($alter_options as $option ) {

            $row = array(
                'key' =>$option->key,
                'value' => $option->value,
                'isSelected' =>  in_array($option->key, $alterTypes)
            );
            $alter_option_array[] = $row; 
        }

        $certificateInfo = SecretaryFirm::leftJoin('secretary_certificates','secretary_firm.id','=','secretary_certificates.secretary_id')
        ->select('secretary_certificates.certificate_no as certificate_no')
        ->first()->toArray();

    
        $change_record = array();

        $old_address = Address::where('id', $record->old_address_id)->first();

        $change_record['old_address_address1'] = $old_address->address1;
        $change_record['old_address_address2'] = $old_address->address2;
        $change_record['old_address_city'] = $old_address->city;
        $change_record['old_address_district'] = $old_address->district;
        $change_record['old_address_province'] = $old_address->province;
        $change_record['old_address_postcode'] = $old_address->postcode;

        $new_address = Address::where('id', $record->new_address_id)->first();
        $change_record['new_address_address1'] = isset($new_address->address1) ? $new_address->address1 : '';
        $change_record['new_address_address2'] = isset($new_address->address2) ? $new_address->address2 : '';
        $change_record['new_address_city']  = isset($new_address->city) ? $new_address->city : '';
        $change_record['new_address_district'] = isset($new_address->district) ? $new_address->district : '';
        $change_record['new_address_province'] = isset($new_address->province) ? $new_address->province : '';
        $change_record['new_address_postcode'] = isset($new_address->postcode) ? $new_address->postcode : '';

        $change_record['old_name'] = $record->old_name;
        $change_record['new_name'] = ($record->new_name) ? $record->new_name : '';
       
        $change_record['old_name_si'] = $record->old_name_si;
        $change_record['old_name_ta'] = $record->old_name_ta;
        $change_record['new_name_si'] = ($record->new_name_si) ? $record->new_name_si : '';
        $change_record['new_name_ta'] = ($record->new_name_ta) ? $record->new_name_ta : '';

        $change_record['old_email_address'] = $record->old_email_address;
        $change_record['new_email_address'] = ($record->new_email_address) ? $record->new_email_address : '';

        $change_record['old_mobile_no'] = $record->old_mobile_no;
        $change_record['new_mobile_no'] = ($record->new_mobile_no) ? $record->new_mobile_no : '';
        $change_record['old_tel_no'] = $record->old_tel_no;
        $change_record['new_tel_no'] = ($record->new_tel_no) ? $record->new_tel_no : '';
        $change_record['certificate_no'] = $certificateInfo['certificate_no'];

        $partner_list = array();

        $partners = SecretaryChnagesFirmPartners::where('change_id', $record->id)
        ->whereNotIn('status', array($this->settings('DELETE','key')->id) )
        ->get()->
        toArray();

        if(count($partners) ) {
            foreach($partners as $partner) {

                $check_reg_sec_upper_nic = Secretary::where('nic', strtoupper($partner['nic']))
                ->where('status', $this->settings('SECRETARY_APPROVED','key')->id)
                ->count();
                $check_reg_sec_lower_nic = Secretary::where('nic', strtolower($partner['nic']))
                ->where('status', $this->settings('SECRETARY_APPROVED','key')->id)
                ->count();

                $registeredSec = ($check_reg_sec_upper_nic || $check_reg_sec_lower_nic);

                
                $row = array(
                    'id' => $partner['id'],
                    'name' => ($registeredSec) ? $partner['old_name'] : $partner['new_name'] ,
                    'nic' => $partner['nic'],
                    'address' => ($registeredSec) ? $partner['old_address'] : $partner['new_address'],
                    'citizenship' => ($registeredSec) ? $partner['old_citizenship'] : $partner['new_citizenship'],
                    'professional_qualifications' => ($registeredSec) ? $partner['old_professional_qualifications'] : $partner['new_professional_qualifications'],
                    'which_qualified' => ( $partner['new_which_qualified'] ) ? $partner['new_which_qualified'] : $partner['old_which_qualified'],
                    'existing_patner' => intval($partner['partner_id']) > 0,
                    'registeredSec' => $registeredSec
                );
                $partner_list[] = $row;

            }

        }


        return response()->json([
            'message' => 'Data is successfully loaded.',
            'status' =>true,
            'data'   => array(
                'createrValid' => true,  
                'changeRecord' => $change_record,
                'partners' => $partner_list,
                'alterTypes' => $alter_option_array,
                'selectedTypes' => $alterTypes,
                'requestId' => $request_id,
                'status' =>  $this->settings($record->status,'id')->key,
                'additionalDocs' => $this->files_for_additional_docs($request->secretaryId),
                'external_global_comment' => ''
                )
        ], 200);

    }


    public function updateAlterationTypes(Request $request){

        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);
        $alter_type = is_array($request->alter_type) ? $request->alter_type : array();

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
              
            ], 200);

             exit();

        }
  
        $update_rec =  array(
            'change_types'    => implode(',', $alter_type)
        );
        $update = SecretaryChnagesFirm::where('id', $request_id)->update($update_rec);
       
        if($update) {
            return response()->json([
    
                'status' =>true,
                'request_id'   => $request_id,
            ], 200);
    
        }else {
            return response()->json([  
                'status' =>false,
                'request_id'   => $request_id,
            ], 200);

        }
        
    }


    public function updateNameSection(Request $request){

        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);
        $new_name = ($request->new_name) ? $request->new_name : null;
        $new_name_si = ($request->new_name_si) ? $request->new_name_si : null;
        $new_name_ta = ($request->new_name_ta) ? $request->new_name_ta : null;

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
              
            ], 200);

             exit();

        }
  
        $update_rec =  array(
            'new_name'    => $new_name,
            'new_name_si'    => $new_name_si,
            'new_name_ta'    => $new_name_ta
        );
        $update = SecretaryChnagesFirm::where('id', $request_id)->update($update_rec);
       
        if($update) {
            return response()->json([
    
                'status' =>true,
                'request_id'   => $request_id,
            ], 200);
    
        }else {
            return response()->json([  
                'status' =>false,
                'request_id'   => $request_id,
            ], 200);

        }
        
    }


    public function updateAddressSection(Request $request){

        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);
        $new_address_address1 = ($request->new_address_address1) ? $request->new_address_address1 : null;
        $new_address_address2 = ($request->new_address_address2) ? $request->new_address_address2 : null;
        $new_address_city = ($request->new_address_city) ? $request->new_address_city : null;
        $new_address_district = ($request->new_address_district) ? $request->new_address_district : null;
        $new_address_province = ($request->new_address_province) ? $request->new_address_province : null;
        $new_address_postcode = ($request->new_address_postcode) ? $request->new_address_postcode : null;

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
              
            ], 200);

             exit();

        }
        $record = SecretaryChnagesFirm::where('id', $request_id)->first();

        $address = new Address;
        $address->address1 = $new_address_address1;
        $address->address2 = $new_address_address2;
        $address->city = $new_address_city;
        $address->district = $new_address_district;
        $address->province = $new_address_province;
        $address->postcode = $new_address_postcode;

        $address->save();
  
        $update_rec =  array(
            'new_address_id'    => $address->id,
        );
        $update = SecretaryChnagesFirm::where('id', $request_id)->update($update_rec);

        if($record->new_address_id) {
            Address::where('id', $record->new_address_id)->delete();
        }
       
        if($update) {
            return response()->json([
    
                'status' =>true,
                'request_id'   => $request_id,
            ], 200);
    
        }else {
            return response()->json([  
                'status' =>false,
                'request_id'   => $request_id,
            ], 200);

        }
        
    }


    public function updateEmailSection(Request $request){

        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);
        $new_email_address = ($request->new_email_address) ? $request->new_email_address : null;
    
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
              
            ], 200);

             exit();

        }
  
        $update_rec =  array(
            'new_email_address'    => $new_email_address,
        );
        $update = SecretaryChnagesFirm::where('id', $request_id)->update($update_rec);
       
        if($update) {
            return response()->json([
    
                'status' =>true,
                'request_id'   => $request_id,
            ], 200);
    
        }else {
            return response()->json([  
                'status' =>false,
                'request_id'   => $request_id,
            ], 200);

        }
        
    }


    public function updateContactSection(Request $request){

        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);
        $new_mobile_no = ($request->new_mobile_no) ? $request->new_mobile_no : null;
        $new_tel_no = ($request->new_tel_no) ? $request->new_tel_no : null;
    
        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
              
            ], 200);

             exit();

        }
  
        $update_rec =  array(
            'new_mobile_no'    => $new_mobile_no,
            'new_tel_no'  => $new_tel_no
        );
        $update = SecretaryChnagesFirm::where('id', $request_id)->update($update_rec);
       
        if($update) {
            return response()->json([
    
                'status' =>true,
                'request_id'   => $request_id,
            ], 200);
    
        }else {
            return response()->json([  
                'status' =>false,
                'request_id'   => $request_id,
            ], 200);

        }
        
    }


    function files_for_additional_docs($secretory_id){

        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => true,
                'doc_id' => 0,
        );

        if(!$secretory_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $request_id = null;
        $request_id = $this->valid_request_operation($secretory_id);

        if(!$request_id) {
            return array(
                'docs' => array(),
                'uploadedAll' => false,
                'doc_id' => 0
        );
        }

        $record = SecretaryChnagesFirm::where('id', $request_id)->first();
      
        // documents list
        if($record->type === 'pvt') {
            $form_other_docs = Documents::where('key', 'SECRETARY_PVT_CHANGE_OTHER_DOCUMENTS')->first();
        } else {
            $form_other_docs = Documents::where('key', 'SECRETARY_FIRM_CHANGE_OTHER_DOCUMENTS')->first();
        }
      
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

       

        $other_docs = SecretaryDocument::where('firm_id', $record->secretory_id)
                                        ->where('document_id', $form_other_docs->id )
                                        ->where('request_id', $request_id)
                                        ->whereNotIn('status', array($this->settings('DOCUMENT_DELETED','key')->id))
                                        ->orderBy('id', 'DESC')
                                        ->get();

      
        foreach($other_docs as $docs ) {

            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'DOCUMENT_PENDING';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $docs->description;
            $file_row['file_type'] = '';
            $file_row['multiple_id'] = $docs->multiple_id;
            $file_row['uploaded_path'] = '';
            $file_row['is_admin_requested'] = false;
                    
            $uploadeDocStatus = @$docs->status;
            $file_row['doc_status'] = isset($this->settings($uploadeDocStatus,'id')->key) ? $this->settings($uploadeDocStatus,'id')->key : 'DOCUMENT_PENDING';
            if($record->status == 'SECRETARY_CHANGE_RESUBMIT' && isset($uploadeDocStatus) && $uploadeDocStatus ){
                            $file_row['doc_status'] = $this->settings($uploadeDocStatus,'id')->key;
            }
            if($uploadeDocStatus == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id ) { //if doc is resubmitted
    
                           $commentRow = SecretaryDocumentStatus::where('secretary_document_id', $docs->id )
                                                                ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id )
                                                                ->where('comment_type', $external_comment_type_id )
                                                                // ->where('multiple_id', $docs->multiple_id )
                                                                ->first();
                            $file_row['doc_comment'] = ( isset( $commentRow->comments ) && $commentRow->comments ) ? $commentRow->comments : '';
    
            }
    
            $file_row['uploaded_path'] =  isset($docs->file_token)  &&
                                                isset($docs->path ) &&
                                                isset($docs->description) &&
                                                $docs->file_token &&
                                                $docs->path &&
                                                ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                $docs->description ? $docs->file_description : '';
            $file_row['uploaded_token'] =  isset($docs->file_token)  &&
                                                isset($docs->path ) &&
                                                isset($docs->description) &&
                                                $docs->file_token &&
                                                $docs->path &&
                                                ($file_row['doc_status'] != 'DOCUMENT_DELETED') &&
                                                $docs->description ? $docs->file_token : '';
    
            $has_all_uploaded_str = $has_all_uploaded_str.( intval ( $file_row['uploaded_path'] !== '' &&  $file_row['uploaded_token'] !== '' ) );               
                    
                    
            $generated_files['docs'][] = $file_row;

        }

 

        $generated_files['uploadedAll'] =  ( $has_all_uploaded_str != '' && strpos($has_all_uploaded_str, '1') !== false ) ;
        
        return $generated_files;
    
    }


    function uploadOtherDocs(Request $request){

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $file_type_id = $request->fileTypeId;
        $secretory_id = $request->secretaryId;
        $file_description = $request->fileDescription;
        
        $request_id = $this->valid_request_operation($secretory_id);

        $record = SecretaryChnagesFirm::where('id', $request_id)->first();
  
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
        
        if($record->type == 'pvt') {
            $path = 'secretary-pvt/changes/other-docs/'.substr($secretory_id,0,2);
        } else {
            $path = 'secretary-firm/changes/other-docs/'.substr($secretory_id,0,2);
        }
        
       // $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());
          
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
    
 
           $doc = new SecretaryDocument;
           $doc->document_id = $file_type_id;
           $doc->path = $path;
           $doc->firm_id = $record->secretory_id;
           $doc->request_id = $request_id;
           $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
           $doc->file_token = $token;
           $doc->multiple_id = mt_rand(1,1555400976);
           $doc->name = $real_file_name;
           $doc->description = $file_description;
           $doc->save();
           $new_doc_id = $doc->id;

           return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'error'  => 'no',
        ], 200);
    

    }


    function uploadOtherResubmittedDocs(Request $request){
        
        $secretory_id = $request->secretaryId;
        $multiple_id = $request->multiple_id;
        $request_id = $this->valid_request_operation($secretory_id);

        $record = SecretaryChnagesFirm::where('id', $request_id)->first();

        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';

  
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

        if($record->type == 'pvt') {
            $path = 'secretary-pvt/changes/other-docs/'.substr($secretory_id,0,2);
            $doc_key = 'SECRETARY_PVT_CHANGE_OTHER_DOCUMENTS';
        } else {
            $path = 'secretary-firm/changes/other-docs/'.substr($secretory_id,0,2);
            $doc_key = 'SECRETARY_FIRM_CHANGE_OTHER_DOCUMENTS';
        }

        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

        
         $form_other_docs = Documents::where('key', $doc_key)->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
           );
           SecretaryDocument::where('firm_id', $secretory_id)
           ->where('multiple_id', $multiple_id)
           ->where('document_id',$form_other_docs->id )
           ->where('request_id',$request_id)
           ->where('status', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id)
            ->update($update_arr);
    
 
           return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'error'  => 'no',
        ], 200);
    

    }

    function removeOtherDoc(Request $request){

        $file_token = $request->file_token;
        
    
        SecretaryDocument::where('file_token', $file_token)
                         ->delete();
    
        return response()->json([
                        'message' => 'File removed successfully.',
                        'status' =>true,
                        
    
        ], 200);
    }

    function editPartner(Request $request ) {
        
        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);

        if(!$request_id) {
            return response()->json([
                'message' => 'Invalid Operation.',
                'status' =>false,

            ], 200);
        }

        $partner = $request->partner;

        $partner_change_id = $partner['id'];

        if( !( isset($partner_change_id) && intval($partner_change_id) ) )  {
            return response()->json([
                'message' => 'Invalid Partner.',
                'status' =>false,

            ], 200);
        }

        $partner_id = $partner['id'];
        $new_which_qualified = $partner['which_qualified'];

        $update = false;

        if( $partner['existing_patner'] ) { // edit


            if($partner['registeredSec']) {

                $update_record = array(
                    'new_which_qualified' => $new_which_qualified,
                    'status' => $this->settings('EDIT','key')->id
                );

            } else {
                $update_record = array(
                    'new_name' => $partner['name'],
                    'new_citizenship' => $partner['citizenship'],
                    'new_professional_qualifications' => $partner['professional_qualifications'],
                    'new_address' => $partner['address'],
                    'new_which_qualified' => $new_which_qualified,
                    'status' => $this->settings('EDIT','key')->id
                );
            }
           


            $update = SecretaryChnagesFirmPartners::where('id' , $partner_change_id)->update($update_record);

        } else { // add

            if($partner['registeredSec']) {
                $update_record = array(
                    'new_which_qualified' => $new_which_qualified,
                    'status' => $this->settings('ADD','key')->id
                );
            } else {
                $update_record = array(
                    'new_name' => $partner['name'],
                    'new_citizenship' => $partner['citizenship'],
                    'new_professional_qualifications' => $partner['professional_qualifications'],
                    'new_address' => $partner['address'],
                    'new_which_qualified' => $new_which_qualified,
                    'status' => $this->settings('ADD','key')->id
                );
            }
            
            $update = SecretaryChnagesFirmPartners::where('id' , $partner_change_id)->update($update_record);

        }

        if($update) {
            return response()->json([
                'message' => 'Successfully updated.',
                'status' =>true,

            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed Updating.',
                'status' =>false,

            ], 200);
        }

    }

    function removePartner(Request $request ) {
        
        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);

        if(!$request_id) {
            return response()->json([
                'message' => 'Invalid Operation.',
                'status' =>false,

            ], 200);
        }

        $partner = $request->partner;

        $partner_change_id = $partner['id'];

        if( !( isset($partner_change_id) && intval($partner_change_id) ) )  {
            return response()->json([
                'message' => 'Invalid Partner.',
                'status' =>false,

            ], 200);
        }

        $partner_id = $partner['id'];

        $del = false;

        if( $partner['existing_patner'] ) { // set delete status

            $update_record = array(
                'status' => $this->settings('DELETE','key')->id
            );
            $del = SecretaryChnagesFirmPartners::where('id' , $partner_change_id)->update($update_record);

        } else { // delete
            $del = SecretaryChnagesFirmPartners::where('id' , $partner_change_id)->delete();
        }

        if($del) {
            return response()->json([
                'message' => 'Successfully deleted.',
                'status' =>true,

            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed Deleting.',
                'status' =>false,

            ], 200);
        }

    }

    function addPartner(Request $request ) {
        
        $secretory_id = $request->secretaryId;
        $request_id = $this->valid_request_operation($secretory_id);

        if(!$request_id) {
            return response()->json([
                'message' => 'Invalid Operation.',
                'status' =>false,

            ], 200);
        }

        $partner = $request->partner;


        $add = false;

        $new_partner = new SecretaryChnagesFirmPartners;
        
        $new_partner->nic = strtoupper($partner['nic']);

        $new_partner->old_name = !($partner['registeredSec']) ? null :$partner['name'];
        $new_partner->new_name = ($partner['registeredSec']) ? null : $partner['name'];

        $new_partner->new_which_qualified  = $partner['which_qualified'];
        $new_partner->old_which_qualified  = $partner['which_qualified'];

        $new_partner->old_address = !($partner['registeredSec']) ? null : $partner['address'];
        $new_partner->new_address = ($partner['registeredSec']) ? null : $partner['address'];


        $new_partner->old_citizenship = !($partner['registeredSec']) ? null : $partner['citizenship'];
        $new_partner->new_citizenship = ($partner['registeredSec']) ?  null : $partner['citizenship'];


        $new_partner->old_professional_qualifications = !($partner['registeredSec']) ? null : $partner['professional_qualifications'];
        $new_partner->new_professional_qualifications = ($partner['registeredSec']) ? null : $partner['professional_qualifications'];

        $new_partner->change_id = $request_id;
        $new_partner->partner_id = null;
        $new_partner->status = $this->settings('ADD','key')->id;
        $new_partner->save();

        $add = isset($new_partner->id);


        if($add) {
            return response()->json([
                'message' => 'Successfully added.',
                'status' =>true,

            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed adding new partner.',
                'status' =>false,

            ], 200);
        }

    }


    function complete(Request $request ) {

        $secretory_id = $request->secretaryId;

        $request_id = $this->valid_request_operation($secretory_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $record = SecretaryChnagesFirm::where('id', $request_id)->first();

        if( !( isset($record->status) && $record->status === $this->settings('SECRETARY_CHANGE_PROCESSING', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update = SecretaryChnagesFirm::where('id', $request_id)->update(['status' => $this->settings('SECRETARY_CHANGE_PENDING', 'key')->id]);
      
        if($update) {
            return response()->json([
                'message' => 'Successfully Resubmitted.',
                'status' =>true,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        } else {
            return response()->json([
                'message' => 'Failed Resubmitting. Please try again later.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        }
        
    }

    function checkNic(Request $request ){

          $nic_upper = trim(strtoupper($request->nic));
          $nic_lower = trim(strtolower($request->nic));

          $sec_info = null;

          if($nic_upper) {
            $sec_info = Secretary::where('nic', $nic_upper)
            ->where('status', $this->settings('SECRETARY_APPROVED','key')->id)
            ->first();

            if(isset($sec_info->id)) {

                $address = Address::where('id', $sec_info->address_id )->first();

                $address_value = '';
                if(isset($address->id)) {
                    $address_value.= $address->address1.',';
                    $address_value.= ($address->address2) ? $address->address2.',' : '';
                    $address_value.= $address->city;
                }

                $sec_reocord = array(
                    'id' => null,
                    'name' => $sec_info->first_name .' '. $sec_info->last_name,
                    'nic' => $sec_info->nic,
                    'address' => $address_value,
                    'citizenship' => 'SriLankan',
                    'professional_qualifications' => $sec_info->professional_qualifications,
                    'which_qualified' => $sec_info->which_applicant_is_qualified,
                    'existing_patner' => false
                );

                return response()->json([
                    'message' => 'Registerd secretory found.',
                    'status' =>true,
                    'sec'   => $sec_reocord,
                ], 200);
        
                 exit();
            } else {
                return response()->json([
                    'message' => 'Registerd secretory not found.',
                    'status' =>false,
                    'sec'   => null,
                ], 200);
            }

          } else if($nic_lower) {

            $sec_info = Secretary::where('nic', $nic_lower)
            ->where('status', $this->settings('SECRETARY_APPROVED','key')->id)
            ->first()->toArray();

            if(isset($sec_info['id'])) {

                $address = Address::where('id', $sec_info['address_id'])->first();

                $address_value = '';
                if(isset($address->id)) {
                    $address_value.= $address->address1.',';
                    $address_value.= ($address->address2) ? $address->address2.',' : '';
                    $address_value.= $address->city;
                }

                $sec_reocord = array(
                    'id' => null,
                    'name' => $sec_info['first_name'] .' '. $sec_info['last_name'],
                    'nic' => strtoupper($sec_info['nic']),
                    'address' => $address_value,
                    'citizenship' => 'SriLankan',
                    'professional_qualifications' => $sec_info['professional_qualifications'],
                    'which_qualified' => $sec_info['which_applicant_is_qualified'],
                    'existing_patner' => false
                );

                return response()->json([
                    'message' => 'Registerd secretory found.',
                    'status' =>true,
                    'sec'   => $sec_reocord,
                ], 200);
        
                 exit();
            } else {
                return response()->json([
                    'message' => 'Registerd secretory not found.',
                    'status' =>false,
                    'sec'   => null,
                ], 200);
            }

          } else {

            return response()->json([
                'message' => 'Registerd secretory not found.',
                'status' =>false,
                'sec'   => null,
            ], 200);

          }

 
     }


     function submit(Request $request ) {

        $secretory_id = $request->secretaryId;

        $request_id = $this->valid_request_operation($secretory_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $record = SecretaryChnagesFirm::where('id', $request_id)->first();

        if( !( isset($record->status) && $record->status === $this->settings('SECRETARY_CHANGE_PROCESSING', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update = SecretaryChnagesFirm::where('id', $request_id)->update(['status' => $this->settings('SECRETARY_CHANGE_PENDING', 'key')->id]);
      
        if($update) {
            return response()->json([
                'message' => 'Successfully Submitted.',
                'status' =>true,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        } else {
            return response()->json([
                'message' => 'Failed Submitting. Please try again later.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        }
        
    }

    function resubmit(Request $request ) {

        $secretory_id = $request->secretaryId;

        $request_id = $this->valid_request_operation($secretory_id);

        if(!$request_id) { 

            return response()->json([
                'message' => 'Invalid Request.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();

        }

        $record = SecretaryChnagesFirm::where('id', $request_id)->first();

        if( !( isset($record->status) && $record->status === $this->settings('SECRETARY_CHANGE_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update = SecretaryChnagesFirm::where('id', $request_id)->update(['status' => $this->settings('SECRETARY_CHANGE_RESUBMITTED', 'key')->id]);
      
        if($update) {
            return response()->json([
                'message' => 'Successfully Resubmitted.',
                'status' =>true,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        } else {
            return response()->json([
                'message' => 'Failed Resubmitting. Please try again later.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);
    
             exit();
        }
        
    }




   
} // end class