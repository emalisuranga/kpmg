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
use App\SecretaryChnagesIndividual;

use Storage;
use Cache;
use App;
use URL;
use PDF;
class SecretaryChangesController extends Controller
{
    use _helper;

    private function has_request_record($secretory_id) {
        
        $accepted_request_statuses = array(
            $this->settings('SECRETARY_CHANGE_APPROVED','key')->id,
            $this->settings('SECRETARY_CHANGE_REJECTED','key')->id
        );
       
        $record_count = SecretaryChnagesIndividual::where('secretory_id', $secretory_id)
                                  ->whereNotIn('status', $accepted_request_statuses )
                                   ->count();
        if( $record_count === 1 ) {
            $record = SecretaryChnagesIndividual::where('secretory_id', $secretory_id)
            ->whereNotIn('status', $accepted_request_statuses )
             ->first();
    
            return $record->id;
        } else {
            return false;
        }
    }


    private function valid_request_operation($secretory_id){

        $secInfo = Secretary::where('id', $secretory_id)->first();
        
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
    
            $record = new SecretaryChnagesIndividual;
            $record->secretory_id = $secretory_id;
            $record->status = $this->settings('SECRETARY_CHANGE_PROCESSING','key')->id;
            $record->created_by = $user->userid;
            $record->old_first_name = $secInfo->first_name;
            $record->old_last_name = $secInfo->last_name;
            $record->old_name_si = $secInfo->name_si;
            $record->old_name_ta = $secInfo->name_ta;
            $record->old_address_id = $secInfo->address_id;
            $record->old_email_address = $secInfo->email;
            $record->old_mobile_no = $secInfo->mobile;
            $record->old_tel_no =  $secInfo->telephone;
            $record->save();
            $record_id =  $record->id;
    
            if($record_id ) {
                return $record_id;
            }else{
                return false;
            }
        }
        
    }

    private function cleanUpforAlterOption($request_id) {

        $record = SecretaryChnagesIndividual::where('id', $request_id)->first();

        $alterTypes = explode(',', $record->change_types);

        $update_rec = array();
        

        if( !in_array('SECRETARY_CHANGE_NAME', $alterTypes) ) {
            $update_rec['new_first_name'] = null;
            $update_rec['new_last_name'] = null;
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

        if (!empty($update_rec)) {
            $update = SecretaryChnagesIndividual::where('id', $request_id)->update($update_rec);
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

        $secInfo = Secretary::where('id',$request->secretaryId)->first();

        if( ! isset($secInfo->id)) {

            return response()->json([
                'message' => 'We can \'t find the secretary information.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);

        }

        $request_id = $this->valid_request_operation($request->secretaryId);

        $record = SecretaryChnagesIndividual::where('id', $request_id)
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
        ->whereNotIn('id', array($this->settings('SECRETARY_CHANGE_PARTNERS','key')->id))
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

        $certificateInfo = Secretary::leftJoin('secretary_certificates','secretaries.id','=','secretary_certificates.secretary_id')
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

        $change_record['old_first_name'] = $record->old_first_name;
        $change_record['new_first_name'] = ($record->new_first_name) ? $record->new_first_name : '';
        $change_record['old_last_name'] = $record->old_last_name;
        $change_record['new_last_name'] = ($record->new_last_name) ? $record->new_last_name : '';

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


        return response()->json([
            'message' => 'Data is successfully loaded.',
            'status' =>true,
            'data'   => array(
                'createrValid' => true,  
                'changeRecord' => $change_record,
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
        $update = SecretaryChnagesIndividual::where('id', $request_id)->update($update_rec);
       
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
        $new_first_name = ($request->new_first_name) ? $request->new_first_name : null;
        $new_last_name = ($request->new_last_name) ? $request->new_last_name : null;
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
            'new_first_name'    => $new_first_name,
            'new_last_name'    => $new_last_name,
            'new_name_si'    => $new_name_si,
            'new_name_ta'    => $new_name_ta
        );
        $update = SecretaryChnagesIndividual::where('id', $request_id)->update($update_rec);
       
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
        $record = SecretaryChnagesIndividual::where('id', $request_id)->first();

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
        $update = SecretaryChnagesIndividual::where('id', $request_id)->update($update_rec);

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
        $update = SecretaryChnagesIndividual::where('id', $request_id)->update($update_rec);
       
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
        $update = SecretaryChnagesIndividual::where('id', $request_id)->update($update_rec);
       
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

        $record = SecretaryChnagesIndividual::where('id', $request_id)->first();
      
        // documents list
        $form_other_docs = Documents::where('key', 'SECRETARY_CHANGE_OTHER_DOCUMENTS')->first();
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        $generated_files['doc_id'] = $form_other_docs->id;

        $has_all_uploaded_str = '';

       

        $other_docs = SecretaryDocument::where('secretary_id', $record->secretory_id)
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

        $record = SecretaryChnagesIndividual::where('id', $request_id)->first();
  
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
    
        $path = 'secretary/changes/other-docs/'.substr($secretory_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());
          
        $doc_pending = $this->settings('DOCUMENT_PENDING','key')->id;
        $doc_req_resumbit = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $doc_requeted= $this->settings('DOCUMENT_REQUESTED','key')->id;
    
 
           $doc = new SecretaryDocument;
           $doc->document_id = $file_type_id;
           $doc->path = $path;
           $doc->secretary_id = $record->secretory_id;
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
    
        $path = 'secretary/changes/other-docs/'.substr($secretory_id,0,2);
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());

          
         $form_other_docs = Documents::where('key', 'SECRETARY_CHANGE_OTHER_DOCUMENTS')->first();


           $update_arr = array(
                'file_token' => $token,
                'path' => $path,
                'status' => $this->settings('DOCUMENT_PENDING','key')->id,
           );
           SecretaryDocument::where('secretary_id', $secretory_id)
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

        $record = SecretaryChnagesIndividual::where('id', $request_id)->first();

        if( !( isset($record->status) && $record->status === $this->settings('SECRETARY_CHANGE_PROCESSING', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update = SecretaryChnagesIndividual::where('id', $request_id)->update(['status' => $this->settings('SECRETARY_CHANGE_PENDING', 'key')->id]);
      
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

        $record = SecretaryChnagesIndividual::where('id', $request_id)->first();

        if( !( isset($record->status) && $record->status === $this->settings('SECRETARY_CHANGE_PROCESSING', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update = SecretaryChnagesIndividual::where('id', $request_id)->update(['status' => $this->settings('SECRETARY_CHANGE_PENDING', 'key')->id]);
      
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
                'message' => 'Failed Submitting the request. Please try again later.',
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

        $record = SecretaryChnagesIndividual::where('id', $request_id)->first();

        if( !( isset($record->status) && $record->status === $this->settings('SECRETARY_CHANGE_RESUBMIT', 'key')->id)){
            return response()->json([
                'message' => 'Invalid Status.',
                'status' =>false,
                'request_id'   => null,
                'change_id'    => null,
            ], 200);

             exit();
        }

        $update = SecretaryChnagesIndividual::where('id', $request_id)->update(['status' => $this->settings('SECRETARY_CHANGE_RESUBMITTED', 'key')->id]);
      
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