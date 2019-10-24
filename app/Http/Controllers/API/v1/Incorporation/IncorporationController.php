<?php
namespace App\Http\Controllers\API\v1\incorporation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyPostfix;
use App\Company;
use App\Address;
use App\Setting;
use App\CompanyMember;
use App\CompanyFirms;
use App\DocumentsGroup;
use App\Documents;
use App\Country;
use App\Share;
use App\ShareGroup;
use App\CompanyDocuments;
use App\CompanyDocumentStatus;
use App\User;
use App\People;
use App\CompanyMemberFirmBenif;
// use App\CompanyObjective;
use App\CompanyObjective1;
use App\CompanyObjective2;
use App\CompanyObjective3;
use App\CompanyObjective4;
use App\CompanyObjective5;
use App\CompanyObjective;
use App\CompanyStatus;
use App\Order;
use App\Secretary;
use App\SecretaryCertificate;
use App\Province;
use App\District;
use App\City;
use App\GNDivision;
use App\CompanyDocumentCopies;
use App\InlandRevenueDetails;
use App\LabourDetails;
use App\IRDregPurposes;
use App\LabourBusinessCats;
use App\SecDivision;
use App\CompanySalutations;
use App\IrdBusinessActivityCodes;
use Storage;
use Cache;
use App;
use URL;
use App\Http\Helper\_helper;
use PDF;

class IncorporationController extends Controller
{
    use _helper;
    function generate_gns(){

        $cities = City::all();
        $n = 0;
        foreach($cities as $c ) {
            $rand_no_of_gns = rand(5,15);

            for($i=0;$i< $rand_no_of_gns; $i++ ) {
                $n++;
                $gn = new GNDivision;
                $gn->id = $c->code.$n;
                $gn->description_en = $c->name. '-GN' .($i+1);
                $gn->description_si = $c->name. '-GN' .($i+1);
                $gn->description_ta = $c->name. '-GN' .($i+1);
                $gn->city_id = $c->code;
                $gn->district_id = $c->district_id;
                $gn->province_id = $c->province_code;
                $gn->save();
            }
        }
    }

    public function downloadForm(){
        $data = array();
        $pdf = PDF::loadView('forms.form1', $data);
        return $pdf->download('invoice.pdf');
    }


    function removeSecForDirector(Request $request){
        
        $company_id = $request->companyId;
        $user_id = $request->userId;
        $shaareUser = CompanyMember::where('id', $user_id)->first();
        $sec_nic_or_pass = ($shaareUser->is_srilankan  =='yes') ? $shaareUser->nic : $shaareUser->passport_no;
        $sec_nic_or_pass_field = ($shaareUser->is_srilankan  =='yes') ? 'nic' : 'passport_no';

        $removeDoc = CompanyDocuments::where('company_id', $company_id)
          ->where('company_member_id', $user_id)
          ->delete();
        
        $delete = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                ->where($sec_nic_or_pass_field,$sec_nic_or_pass )
                                ->delete();

        if($delete){
            return response()->json([
                'message' => 'Successfully remove seretary position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing seretary position',
                'status' =>false
            ], 200);
        }
        
    }

    function removeShForDirector(Request $request){

        $company_id = $request->companyId;
        $user_id = $request->userId;

        $shaareUser = CompanyMember::where('id', $user_id)->first();
        $sh_nic_or_pass = ($shaareUser->is_srilankan  =='yes') ? $shaareUser->nic : $shaareUser->passport_no;
        $sh_nic_or_pass_field = ($shaareUser->is_srilankan  =='yes') ? 'nic' : 'passport_no';

    
        $delete = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where($sh_nic_or_pass_field,$sh_nic_or_pass )
                                ->delete();
        if($delete){
            return response()->json([
                'message' => 'Successfully remove shareholder position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }

    function removeShForSec(Request $request){

        $company_id = $request->companyId;
        $user_id = $request->userId;

        $shaareUser = CompanyMember::where('id', $user_id)->first();
        $sh_nic_or_pass = ($shaareUser->is_srilankan  =='yes') ? $shaareUser->nic : $shaareUser->passport_no;
        $sh_nic_or_pass_field = ($shaareUser->is_srilankan  =='yes') ? 'nic' : 'passport_no';

        $delete = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where($sh_nic_or_pass_field,$sh_nic_or_pass )
                                ->whereNull('company_member_firm_id' )
                                ->delete();

        
        if($delete){
            return response()->json([
                'message' => 'Successfully remove shareholder position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }


    function removeShForSecFirm(Request $request){

        $company_id = $request->companyId;
        $firm_id = $request->userId;

        $shaareUser = CompanyFirms::where('id', $firm_id)->first();
        $shFirmRecordId = $shaareUser->sh_firm_of;

        //delete benif owner
        $delete_benif = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where('company_member_firm_id',$shFirmRecordId )
                                ->delete();
        //delete sh firm
        $delete_sh_firm = CompanyFirms::where('id',$shFirmRecordId )->delete();

        //update sec firm record
        $firm_update =  array(
            'sh_firm_of'    => null
        );
        $update_sec_firm = CompanyFirms::where('id', $firm_id)->update($firm_update);

        
        if( $delete_benif && $delete_sh_firm  && $update_sec_firm ){
            return response()->json([
                'message' => 'Successfully remove shareholder position',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }


    function removeShFirm(Request $request){

        $company_id = $request->companyId;
        $firm_id = $request->userId;

        //delete benif owner
        $delete_benif = CompanyMember::where('company_id',$company_id)
                                ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                ->where('company_member_firm_id',$firm_id )
                                ->where('is_beneficial_owner','yes')
                                ->delete();
        //delete sh firm
        $delete_sh_firm = CompanyFirms::where('id',$firm_id )->delete();

    

        
        if( $delete_benif && $delete_sh_firm   ){
            return response()->json([
                'message' => 'Successfully remove shareholder firm',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing shareholder position',
                'status' =>false
            ], 200);
        }
        
    }


    function removeSecFirm(Request $request){

        $company_id = $request->companyId;
        $firm_id = $request->firmId;

     
        //delete sec firm
        $delete_sec_firm = CompanyFirms::where('id',$firm_id )->delete();

        $remove = CompanyDocuments::where('company_id', $company_id)
        ->where('company_firm_id', $firm_id)
        ->delete();


        if( $delete_sec_firm   ){
            return response()->json([
                'message' => 'Successfully remove secretory firm',
                'status' =>true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Failed removing secretory position',
                'status' =>false
            ], 200);
        }
        
    }


   

    function uploadedDocs($companyId){
          //uploaded docs array
      $uploaded_docs = array();
    
      $company_info = Company::where('id',$companyId)->first();


      $companyTypeKey = $this->settings($company_info->type_id,'id')->key;

      $docs = $this->documents();
      $docs_type_ids=array();

    // print_r($docs[$companyTypeKey]['upload']);
     //die();

      if( isset($docs[$companyTypeKey]['upload'])){
          foreach($docs[$companyTypeKey]['upload'] as $doc){
            $docs_type_ids[] = $doc['dbid'];
          }
      }
     $requested_doc_status = array(
        $this->settings('DOCUMENT_PENDING','key')->id,
        $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
        $this->settings('DOCUMENT_APPROVED','key')->id
     );
    //  foreach($docs_type_ids as $id ){

       // $doc =CompanyDocuments::where('document_id', $id)
                     //   ->where('company_id', $companyId)
                     //   ->whereIn('status', $requested_doc_status)
                   //     ->get();
         $doc =CompanyDocuments::where('company_id', $companyId)
                        ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
                      // ->whereIn('status', $requested_doc_status)
                        ->get();

        if(count($doc)){
            
         foreach($doc as $d ){
            if($d->company_member_id){

                $uploaded_docs[$d->document_id][ $d->company_member_id ] = (isset($d->path)) ? basename($d->path) : '';
            }elseif($d->company_firm_id){
                $uploaded_docs[$d->document_id][ 'firm-'.$d->company_firm_id ] = (isset($d->path)) ? basename($d->path) : '';
            }else if(isset($d->multiple_id) && $d->multiple_id >=0){
                $uploaded_docs[$d->document_id][ $d->multiple_id ] = (isset($d->path)) ? basename($d->path) : '';
            }else{
                $uploaded_docs[$d->document_id]  = (isset($d->path)) ? basename($d->path) : '';
            }  
               
         }
        }
   
   //   }

      return $uploaded_docs;
    }

    function uploadedDocsWithToken($companyId){
        //uploaded docs array
    $uploaded_docs = array();
  
    $company_info = Company::where('id',$companyId)->first();


    $companyTypeKey = $this->settings($company_info->type_id,'id')->key;

    $docs = $this->documents();
    $docs_type_ids=array();
    if( isset($docs[$companyTypeKey]['upload'])){
        foreach($docs[$companyTypeKey]['upload'] as $doc){
          $docs_type_ids[] = $doc['dbid'];
        }
    }
 
   // foreach($docs_type_ids as $id ){

     // $doc =CompanyDocuments::where('document_id', $id)
                    //  ->where('company_id', $companyId)
                   //   ->get();
      $doc =CompanyDocuments::where('company_id', $companyId)
      ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
                      ->get();

      if(count($doc)){
          
       foreach($doc as $d ){
          if($d->company_member_id){

              $uploaded_docs[$d->document_id][ $d->company_member_id ] = (isset($d->file_token)) ? $d->file_token : '';
          }elseif($d->company_firm_id){
              $uploaded_docs[$d->document_id][ 'firm-'.$d->company_firm_id ] = (isset($d->file_token)) ? $d->file_token : '';
          }else if(isset($d->multiple_id) && $d->multiple_id >=0){
            $uploaded_docs[$d->document_id][ $d->multiple_id ] = (isset($d->file_token)) ?$d->file_token : '';
          }else{
              $uploaded_docs[$d->document_id]  = (isset($d->file_token)) ? $d->file_token : '';
          }  
             
       }
      }
 
   // }

    return $uploaded_docs;
  }


function uploadedDocsWithNoOfPages($companyId){
    //uploaded docs array
$uploaded_docs = array();

$company_info = Company::where('id',$companyId)->first();


$companyTypeKey = $this->settings($company_info->type_id,'id')->key;

$docs = $this->documents();
$docs_type_ids=array();
if( isset($docs[$companyTypeKey]['upload'])){
    foreach($docs[$companyTypeKey]['upload'] as $doc){
      $docs_type_ids[] = $doc['dbid'];
    }
}

//foreach($docs_type_ids as $id ){

  //$doc =CompanyDocuments::where('document_id', $id)
                //  ->where('company_id', $companyId)
                //  ->get();
   $doc =CompanyDocuments::where('company_id', $companyId)
   ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
                  ->get();

  if(count($doc)){
      
   foreach($doc as $d ){
      if($d->company_member_id){

       @   $uploaded_docs[$d->document_id][ $d->company_member_id ] = (isset($d->no_of_pages)) ? $d->no_of_pages : '';
      }elseif($d->company_firm_id){
       @   $uploaded_docs[$d->document_id][ 'firm-'.$d->company_firm_id ] = (isset($d->no_of_pages)) ? $d->no_of_pages : '';
      }else if(isset($d->multiple_id) && $d->multiple_id >=0){
       @ $uploaded_docs[$d->document_id][ $d->multiple_id ] = (isset($d->no_of_pages)) ?$d->no_of_pages : '';
      }else{
        @  $uploaded_docs[$d->document_id]  = (isset($d->no_of_pages)) ? $d->no_of_pages : '';
      }  
         
   }
  }

//}

return $uploaded_docs;
}

function get_ird_reg_purposes() {
    //$ird_purposes_cache = Cache::rememberForever('ird_purposes_cache', function () {
        $ird_purposes_results = IRDregPurposes::all();

        $ird_purposes = array();

        foreach($ird_purposes_results as $p ) {

             $rec = array(
                'code' => $p->code,
                'description' => $p->description
             );
             $ird_purposes[] = $rec;
             
        }

         return $ird_purposes;
   // });
   // return $ird_purposes_cache;
}

function get_ird_business_activity_codes() {
    //$ird_purposes_cache = Cache::rememberForever('ird_purposes_cache', function () {
        $ird_bac = IrdBusinessActivityCodes::all();

        $ird_codes = array();

        foreach($ird_bac as $p ) {

             $rec = array(
                'id' => $p->id,
                'code' => $p->code
             );
             $ird_codes[] = $rec;
             
        }

         return $ird_codes;
   // });
   // return $ird_purposes_cache;
}

function get_ird_company_salutations() {
    //$ird_purposes_cache = Cache::rememberForever('ird_purposes_cache', function () {
        $ird_company_salutations = CompanySalutations::all();

        $ird_salutations = array();

        foreach($ird_company_salutations as $p ) {

             $rec = array(
                'code' => $p->id,
                'description' => $p->description
             );
             $ird_salutations[] = $rec;
             
        }

         return $ird_salutations;
   // });
   // return $ird_purposes_cache;
}

function get_ird_sec_divisions() {
    //$ird_purposes_cache = Cache::rememberForever('ird_purposes_cache', function () {
        $sec_div_results = SecDivision::all();

        $divs = array();

        foreach($sec_div_results as $p ) {

             $rec = array(
                'id' => $p->id,
                'description' => $p->description_en
             );
             $divs[] = $rec;
             
        }

         return $divs;
   // });
   // return $ird_purposes_cache;
}

  public function loadHeavyData(Request $request){

    if(!$request->companyId){

        return response()->json([
            'message' => 'We can \'t find a company.',
            'status' =>false
        ], 200);
    }

   
    $labour_buinsess_cats = $this->LabourBusinessCats();
    return response()->json([
        'message' => 'Data Loaded.',
        'status' =>true,
        'data'   => array(
                'pdc' => $this->getProvincesDisctrictsCities(),
                'objectives' => $this->get_company_objectives(),
                'ird_purposes' => $this->get_ird_reg_purposes(),
                'sec_divisions' => $this->get_ird_sec_divisions(),
                'ird_salutations' => $this->get_ird_company_salutations(),
                'ird_bac' => $this->get_ird_business_activity_codes(),
                'labour_business_level1_cats' => $labour_buinsess_cats[0],
                'labour_business_level2_cats' => $labour_buinsess_cats[1],
        )
    ], 200);

    
  }

  private function LabourBusinessCats() {

    $level1  = array();
    $level2  = array();
    $level1_list = LabourBusinessCats::orderBy('id','ASC')->get();

    foreach($level1_list as $one ) {


      $rec = array(
          'id' => $one->id,
          'name' => $one->name,
          'parent_id' => intval( $one->parent_id )
       );
      if(intval( $one->parent_id )) {
        $level2[] = $rec;
      } else {
        $level1[] = $rec;
      }
       
    }

    return array($level1,$level2);

}

  private function getCompanyPostFix($type_id) {
     $company_types = CompanyPostfix::all();
   //  $company_types = $company_types->toArray();

     foreach($company_types as $type ) {
         if($type->company_type_id == $type_id ) {

            return array(

                 'postfix' => $type->postfix,
                 'postfix_si' => $type->postfix_si,
                 'postfix_ta' => $type->postfix_ta,

            );
         }
     }

      return array(

        'postfix' => '',
        'postfix_si' => '',
        'postfix_ta' => '',

   );


  }

  private function getPostfixValues( $postfix_en_value ){

     $postix_values = CompanyPostfix::where('postfix', $postfix_en_value)->first();

     return array(
         'postfix_si' => ( isset($postix_values->postfix_si) && $postix_values->postfix_si) ? $postix_values->postfix_si : '',
         'postfix_ta' => ( isset($postix_values->postfix_ta) && $postix_values->postfix_ta ) ? $postix_values->postfix_ta : '',
     );

  }


    public function loadData(Request $request){

        
        if(!$request->companyId){

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
            ], 200);
        }

        $company_info = Company::where('id',$request->companyId)->first();

        if( ! isset($company_info->id)) {

            return response()->json([
                'message' => 'We can \'t find a company.',
                'status' =>false,
                'data' => array(
                    'createrValid' => false
                ),
               
            ], 200);

        }

        $loginUserEmail = $this->clearEmail($request->loginUser);

        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $loginUserId = $loginUserInfo->people_id;

        $userPeople = People::where('id',$loginUserId)->first();
        $userAddressId = $userPeople->address_id;
        $userAddress = Address::where('id', $userAddressId)->first();

        $company_types = CompanyPostfix::all();
        $company_types = $company_types->toArray();
       

        

        $companyType = $this->settings($company_info->type_id,'id');

        if($company_info->address_id ){
            $company_address = Address::where('id',$company_info->address_id)->first();
            $company_for_address = Address::where('id',$company_info->foreign_address_id)->first();
        }else {
            $company_address = $company_for_address = array(

                'address1'=> "",
                'address2'=> "",
                'city'=> "",
                'country'=> "",
                'district'=> "",
                'id' => 0,
                'postcode'=> "",
                'province'=> "",
            );
        }
    
       $director_as_sec_count = 0;
       $director_as_sh_count =0;
       $dir_count = 0;
       $sec_count =0;
       $sh_count = 0;
       $sh_firm_count = 0;
       $sec_firm_count =0;
        /******director list *****/
      
        $director_list = CompanyMember::where('company_id',$request->companyId)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->orderBy('id','ASC')
                                       ->get();
        $directors = array();
        foreach($director_list as $director){

            $dir_count++;
             
             $director_nic_or_pass = ($director->is_srilankan  =='yes') ? $director->nic : $director->passport_no;
             $director_nic_or_pass_field = ($director->is_srilankan  =='yes') ? 'nic' : 'passport_no';

             //director as a secrotory list
             $directors_as_sec = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                        ->where('company_id', $request->companyId)
                                        ->where('designation_type',$this->settings('SECRETARY','key')->id)
                                        ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                        ->get()
                                        ->count();
             
            if($directors_as_sec){
                $director_as_sec_count ++;
            }
            

            //director as a shareholder list
            $directors_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                                       ->where('company_id', $request->companyId)
                                       ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                       ->where('is_beneficial_owner','no')
                                       ->where($director_nic_or_pass_field,$director_nic_or_pass)
                                       ->get()
                                       ->count();
             
            if($directors_as_sh){
                $director_as_sh_count ++;
            }                         

             $address ='';
             $forAddress = '';
             if( $director->address_id) {
                $address = Address::where('id',$director->address_id)->first();
             }
             if( $director->foreign_address_id) {
                $forAddress = Address::where('id', $director->foreign_address_id)->first();
             }

             $can_director_as_sec = true;
             $sec_reg_no = '';

            if( $director->nic  && ( $companyType->key =='COMPANY_TYPE_PUBLIC' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_32' ||  $companyType->key =='COMPANY_TYPE_GUARANTEE_34' ))  {
                
               
                $members_sec_nic_lower =Secretary::where('nic', strtolower($director->nic))->first();
                $members_sec_nic_lowercount = Secretary::where('nic', strtolower($director->nic))->count();
        
                $members_sec_nic_upper =Secretary::where('nic', strtoupper($director->nic))->first();
                $members_sec_nic_uppercount = Secretary::where('nic',strtoupper($director->nic))->count();
        
                $members_sec = ($members_sec_nic_lowercount ) ? $members_sec_nic_lower : $members_sec_nic_upper;
 
                $sec_reg_no = isset($members_sec->certificate_no) && $members_sec->certificate_no  ? $members_sec->certificate_no : '';
                $can_director_as_sec = ($sec_reg_no) ? true : false;
              

            }

             $rec = array(
                'id' => $director['id'],
                'type' => ($director->is_srilankan  =='yes' ) ? 'local' : 'foreign',

                'firstname' => $director->first_name,
                'lastname' => $director->last_name,
                'title' => $director->title,

                'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

                'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',

                'nic'       => $director->nic,
                'passport'  => $director->passport_no,
               // 'country'   =>($address->country) ? $address->country : '',
                'country'  => @( $director->foreign_address_id)  ? @$forAddress->country : @$address->country,
                'passport_issued_country'   => $director->passport_issued_country,
              //  'share'     => $director->no_of_shares,
                'date'      => '1970-01-01' == $director->date_of_appointment ? null : $director->date_of_appointment,
                'phone' => $director->telephone,
                'mobile' => $director->mobile,
                'email' => $director->email,
                'occupation' => $director->occupation,
                'directors_as_sec' =>$directors_as_sec,
                'directors_as_sh' => $directors_as_sh,
                'can_director_as_sec' => $can_director_as_sec,
                'secRegDate' => $sec_reg_no
               
             );
             $directors[] = $rec;
        }

        /******secretory firms list *****/
        $sec_list = CompanyFirms::where('company_id',$request->companyId)
        ->where('type_id',$this->settings('SECRETARY','key')->id)
        ->where('status',1)
        ->orderBy('id','ASC')
        ->get();
        $secs_firms = array();

        foreach($sec_list as $sec){

        //sec firm as a shareholder list
        
        $sec_as_sh_count =  ( intval( $sec->sh_firm_of ) > 0 )  ? 1 : 0 ;
        $sec_firm_count++;

        $address_id =  $sec->foreign_address_id ? $sec->foreign_address_id : $sec->address_id;

         if(!$sec->foreign_address_id){
            $address = Address::where('id',$address_id)->first();
         }else{
           // $address = ForeignAddress::where('id',$address_id)->first();
           $address = Address::where('id',$address_id)->first();
         }

        $rec = array(
        'id' => $sec['id'],
        'type' => ($address->country != 'Sri Lanka') ? 'foreign' : 'local',
        'pvNumber' => $sec->registration_no,
        'firm_name' => $sec->name,
        'firm_province' =>  ( $address->province) ? $address->province : '',
        'firm_district' =>  ($address->district) ? $address->district : '',
        'firm_city' =>  ( $address->city) ? $address->city : '',
        'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
        'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
        'firm_country'      => ($address->country) ? $address->country : '',
        'firm_postcode' => ($address->postcode) ? $address->postcode : '',
        'firm_email' => $sec->email,
        'firm_phone' => $sec->phone,
        'firm_mobile' => $sec->mobile,
        'firm_date'  => $sec->date_of_appointment,
        'sec_as_sh' => $sec_as_sh_count,
        'secType' => 'firm',
        'secBenifList' => array(
             'ben' => array()
        )
        );
        $secs_firms[] = $rec;
        }

        /******secretory list *****/
        $sec_list = CompanyMember::where('company_id',$request->companyId)
                ->where('designation_type',$this->settings('SECRETARY','key')->id)
                ->where('status',1)
                ->orderBy('id','ASC')
                ->get();
         $secs = array();
        foreach($sec_list as $sec){
        
                $sec_nic_or_pass = ($sec->is_srilankan  =='yes') ? $sec->nic : $sec->passport_no;
                $sec_nic_or_pass_field = ($sec->is_srilankan  =='yes') ? 'nic' : 'passport_no';
        
                //sec as a shareholder list
                $sec_as_sh = CompanyMember::select('id','first_name','last_name','title','nic','passport_no')
                    ->where('company_id', $request->companyId)
                     ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                     ->where('nic',$sec->nic)
                     ->whereNull('company_member_firm_id' )
                     ->get();
                $sec_as_sh_count = $sec_as_sh->count();
                $sec_sh_comes_from_director = false;

                $sec_count ++;
              
               
                 $address ='';
                 $forAddress = '';
                 if( $sec->address_id) {
                    $address = Address::where('id',$sec->address_id)->first();
                 }
                 if( $sec->foreign_address_id) {
                    $forAddress = Address::where('id', $sec->foreign_address_id)->first();
                 }
        
                 $firm_info = array();
                 if($sec->company_member_firm_id){
                     $firm_info = CompanyFirms::where('id',$sec->company_member_firm_id)->first();
        
                     $firm_address = Address::where('id', $firm_info->address_id)->first();
        
                     $firm_info['address']=$firm_address;
        
                 }
        
                $rec = array(
                'id' => $sec['id'],
                'type' => ($sec->is_srilankan =='yes' ) ? 'local' : 'foreign',
                'firstname' => $sec->first_name,
                'lastname' => $sec->last_name,

                'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

                'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',

                'nic'       => $sec->nic,
                'passport'  => $sec->passport_no,
               // 'country'   =>($address->country) ? $address->country : '',
                'country'  => @( $sec->foreign_address_id && isset( $forAddress->country) )  ? @$forAddress->country : @$address->country,
              //  'country'  => ( $sec->foreign_address_id && isset( $forAddress->country) ) ? 'test' : 'fail',
                'passport_issued_country'   => $sec->passport_issued_country,
                //'share'     =>0,
                'date'      => '1970-01-01' == $sec->date_of_appointment ? null : $sec->date_of_appointment,
                'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
                'regDate'      => ($sec->is_registered_secretary =='yes' || $companyType->key =='COMPANY_TYPE_PUBLIC' ) ? $sec->secretary_registration_no :'',
                'phone' => $sec->telephone,
                'mobile' => $sec->mobile,
                'email' => $sec->email,
                'occupation' => $sec->occupation,
              //  'secType' => ( $sec->is_natural_person == 'yes') ? 'natural' : 'firm',
                'secType' => 'natural',
                'secCompanyFirmId' => $sec->company_member_firm_id,
                'sec_as_sh' => $sec_as_sh_count,
                'sec_sh_comes_from_director' => $sec_sh_comes_from_director,
                'firm_info' =>$firm_info,
                'pvNumber' => ($sec->company_member_firm_id) ? $firm_info['registration_no'] : '',
                'firm_name' => ($sec->company_member_firm_id) ? $firm_info['name'] : '',
                'firm_province' => ($sec->company_member_firm_id) ? $firm_address['province'] : '',
                'firm_district' => ($sec->company_member_firm_id) ? $firm_address['district'] : '',
                'firm_city' => ($sec->company_member_firm_id) ? $firm_address['city'] : '',
                'firm_localAddress1' => ($sec->company_member_firm_id) ? $firm_address['address1'] : '',
                'firm_localAddress2' => ($sec->company_member_firm_id) ? $firm_address['address2'] : '',
                'firm_postcode' => ($sec->company_member_firm_id) ? $firm_address['postcode'] : ''
        
                );
                $secs[] = $rec;
                }

        /******share holder list *****/
        
        $shareholder_list = CompanyMember::where('company_id',$request->companyId)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->whereNull('company_member_firm_id' )
        ->where('status',1)
        ->orderBy('id','ASC')
        ->get();
        $shareholders = array();
        foreach($shareholder_list as $shareholder){

            $sh_count++;

            $address ='';
            $forAddress = '';
            if( $shareholder->address_id) {
               $address = Address::where('id',$shareholder->address_id)->first();
            }
            if( $shareholder->foreign_address_id) {
               $forAddress = Address::where('id', $shareholder->foreign_address_id)->first();
            }


        //check share row
        $shareRecord = array(
            'type' => '', 'name' => '' , 'no_of_shares' =>0
        );

        $shareRow = Share::where('company_member_id', $shareholder->id)->orderBy('id', 'DESC')->first();
   
       // print_R($shareRow->company_member_id);
         $shareType ='';
         $noOfShares ='';
         $groupName= '';
         $sharegroupId='';

        if(isset($shareRow->company_member_id ) && $shareRow->company_member_id ){

            $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();

            $shareRecord['type'] = $shareGroupInfo['type'];
            $shareRecord['name'] = $shareGroupInfo['name'];
            $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
            $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];

            $shareType = $shareGroupInfo['type'] == 'core_share' ? 'core' :'single';
            $noOfShares = $shareGroupInfo['no_of_shares'];

            if($shareType == 'core'){
                $groupName= $shareGroupInfo['name'];
                $sharegroupId = $shareGroupInfo['id'];
            }
        }

        $rec = array(
        'id' => $shareholder['id'],
        'type' => ($shareholder->is_srilankan =='yes' ) ? 'local' : 'foreign',
        'firstname' => $shareholder->first_name,
        'lastname' => $shareholder->last_name,
        'title'    => $shareholder->title,
       // 'province' =>  ( $address->province) ? $address->province : '',
      //  'district' =>  ($address->district) ? $address->district : '',
       // 'city' =>  ( $address->city) ? $address->city : '',
       // 'localAddress1' => ($address->address1) ? $address->address1 : '',
       // 'localAddress2' => ($address->address2) ? $address->address2 : '',
       // 'postcode' => ($address->postcode) ? $address->postcode : '',
        'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
        'nic'       => $shareholder->nic,
        'passport'  => $shareholder->passport_no,
      //  'country'   =>($address->country) ? $address->country : '',
        'country'  => @( $shareholder->foreign_address_id)  ? @$forAddress->country : @$address->country,
        'passport_issued_country' => $shareholder->passport_issued_country,
       // 'share'     => $shareholder->no_of_shares,
        'date'      => '1970-01-01' == $shareholder->date_of_appointment ? null : $shareholder->date_of_appointment,
        'phone' => $shareholder->telephone,
        'mobile' => $shareholder->mobile,
        'email' => $shareholder->email,
        'occupation' => $shareholder->occupation,
        'shareRow' => $shareRecord,
        'shList'  =>$shareholder,
        'shareType' => $shareType,
        'noOfShares' => ($sharegroupId) ? '' : $noOfShares,
        'coreGroupSelected' => $sharegroupId,
        'shareholderType' => 'natural'
        );
        $shareholders[] = $rec;
        }

         /******share holder firms list *****/
         $shareholder_list = CompanyFirms::where('company_id',$request->companyId)
         ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
         ->where('status',1)
         ->orderBy('id','ASC')
         ->get();
         $shareholderFirms = array();
         foreach($shareholder_list as $shareholder){

            $sh_firm_count ++;

           // benifi list 
           $benifListArr = array();
           $benifList = CompanyMember::where('company_id', $request->companyId)
                                    ->where('designation_type', $this->settings('SHAREHOLDER','key')->id)
                                    ->where('company_member_firm_id',  $shareholder->id )
                                    ->where('is_beneficial_owner', 'yes')
                                    ->get();
            if(count($benifList)){
                foreach($benifList as $ben ){
                    
                    $ben_address_id =  $ben->address_id;
                    $ben_address = Address::where('id',$ben_address_id)->first();

                     $row = array(
                        'type' => $ben->is_srilankan == 'yes' ? 'local' : 'foreign',
                        'title' => $ben->title,
                        'firstname' => $ben->first_name,
                        'lastname'  => $ben->last_name,
                        'province'  => $ben_address->province,
                        'district'  => $ben_address->district,
                        'city'      => $ben_address->city,
                        'localAddress1' => $ben_address->address1,
                        'localAddress2' => $ben_address->address2,
                        'postcode'     => $ben_address->postcode,
                        'nic'           => $ben->nic,
                        'passport'      => $ben->passport_no,
                        'country'      => $ben_address->country,
                        'date'          => ( $ben->date_of_oppointment == '1970-01-01' ) ? '' : $ben->date_of_oppointment,
                        'occupation'   => $ben->occupation,
                        'phone'        => $ben->telephone,
                        'mobile'       => $ben->mobile,
                        'email'        => $ben->email,
                        'id'           => $ben->id

                     );
                     $benifListArr[] = $row;
                }
            }
 
         $address_id =  $shareholder->address_id;
         $address = Address::where('id',$address_id)->first();

         //check share row
         $shareRecord = array(
             'type' => '', 'name' => '' , 'no_of_shares' =>0
         );
         $shareRow = Share::where('company_firm_id', $shareholder->id)->orderBy('id', 'DESC')->first();
 
        // print_R($shareRow->company_member_id);
          $shareType ='';
          $noOfShares ='';
          $groupName= '';
          $sharegroupId='';

         if(isset($shareRow->company_firm_id ) && $shareRow->company_firm_id ){

             $shareGroupInfo = ShareGroup::where('id', $shareRow->group_id)->first();
             $shareRecord['type'] = $shareGroupInfo['type'];
             $shareRecord['name'] = $shareGroupInfo['name'];
             $shareRecord['sharegroupId'] = $shareGroupInfo['id'];
             $shareRecord['no_of_shares'] = $shareGroupInfo['no_of_shares'];
 
             $shareType = $shareGroupInfo['type'] == 'core_share' ? 'core' :'single';
             $noOfShares = $shareGroupInfo['no_of_shares'];
 
             if($shareType == 'core'){
                 $groupName= $shareGroupInfo['name'];
                 $sharegroupId = $shareGroupInfo['id'];
             }
 
         }

         $rec = array(
         'id' => $shareholder['id'],
         'type' => ($shareholder->is_srilankan =='yes' ) ? 'local' : 'foreign',
         'pvNumber' => $shareholder->registration_no,
         'firm_name' => $shareholder->name,
        'firm_province' =>  ( $address->province) ? $address->province : '',
        'firm_district' =>  ($address->district) ? $address->district : '',
        'firm_city' =>  ( $address->city) ? $address->city : '',
        'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
        'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
        'firm_postcode' => ($address->postcode) ? $address->postcode : '',
        'firm_email' => $shareholder->email,
        'firm_date'  => $shareholder->date_of_appointment,
        'firm_phone' => $shareholder->phone,
        'firm_mobile' => $shareholder->mobile,
         'shareRow' => $shareRecord,
         'shList'  =>$shareholder,
         'shareType' => $shareType,
         'noOfShares' => ($sharegroupId) ?  '' : $noOfShares,
         'coreGroupSelected' => $sharegroupId,
         'benifiList' => array('ben' => $benifListArr ),
         'shareholderType' => 'firm',
         );
         $shareholderFirms[] = $rec;
         }

          /******company documents *****/
        $documentsGroups = DocumentsGroup::where('company_type',$company_info->type_id )
                                                ->where('request_type','COM_REG')
                                                ->get();
        $documentList = array();

        foreach($documentsGroups as $group ){

            $group_id = @$group->id;

            $docs =  \DB::table('documents')->where('document_group_id', $group_id )->get();

            if(count($docs)){

                $data = array(

                    'group_name' => $group->description,
                    'documents'  =>  $docs,
                    'docs_count' => count($docs)
    
                );
                $documentList[] = $data;

            }


        }
      ////////share groups////////
      $core_groups_list = array();
      $core_groups = ShareGroup::where('type','core_share')
                                  ->where('company_id', $request->companyId )
                                ->get();
      if(count($core_groups)){
          foreach($core_groups as $g ){
        
          $grec = array(
              'group_id' => $g->id,
              'group_name' => "$g->name ($g->no_of_shares)"
          );
          $core_groups_list[] = $grec;
        }
      }

     $payment_row =  $this->document_map($companyType->key);

     $payment = $payment_row['form_map_fee'];

     $payment_new_row = $this->document_map_new($companyType->key,$directors,$secs,$secs_firms);

     $payment_new = $payment_new_row['form_map_fee'];

     /*****security checkpoint - check company status */
     $process_status = $this->settings($company_info->status,'id')->key;

   /* if( $loginUserId  != $company_info->created_by ){

        return response()->json([
            'message' => 'Invalid Company Incorporation.',
            'status' =>true,
            'data'   => array(
                    'processStatus' => $this->settings($company_info->status,'id')->key,
                    'createrValid' => false,  
            )
        ], 200);
    }*/
    $stakehodlerE = true;
    $companyTypes_for_two_directors = array(
        'COMPANY_TYPE_PUBLIC',
        'COMPANY_TYPE_GUARANTEE_32',
        'COMPANY_TYPE_GUARANTEE_34'
    );
    $invalidate_sh_companyTypes = array(
        'COMPANY_TYPE_OVERSEAS',
        'COMPANY_TYPE_OFFSHORE'
    );
    $companyTypes_for_two_directors_check = (in_array($companyType->key ,$companyTypes_for_two_directors)) ? ($dir_count >=2 ) : $dir_count > 0 ;

    if(in_array($companyType->key ,$invalidate_sh_companyTypes)) {

        if( $dir_count > 0 && ( $sec_count || $sec_firm_count )) {

         //   if(  $sec_count == $director_as_sec_count ) {
          //      $stakehodlerE = !( $sec_firm_count > 0  || $dir_count > $sec_count || ( $dir_count >1  && $dir_count == $sec_count ) );
          //  } else {
          //      $stakehodlerE = !($sec_count || $sec_firm_count);
         //   }

         $stakehodlerE = false;


        } else {
            $stakehodlerE = true;
        }
    }

    else if(
        !in_array($companyType->key ,$invalidate_sh_companyTypes) &&
        $companyTypes_for_two_directors_check &&
        ($sec_count || $sec_firm_count ) &&
        ($sh_count || $sh_firm_count )
    ) {

       

        if($dir_count == 1 && $sec_count == 1 && $sec_firm_count == 0){

            $director_nic = isset($director_list[0]->nic) ? strtoupper($director_list[0]->nic) : '';
            $sec_nic = isset($sec_list[0]->nic) ? strtoupper($sec_list[0]->nic) : '';

            if($sec_nic != $director_nic) {
                $stakehodlerE = false;
            } else {
                $stakehodlerE = true;
            }

        } else {
            if( $sec_count == $director_as_sec_count  && $sh_count == $director_as_sh_count){ //if directors as sh and secs

                if(  $sec_firm_count || $sh_firm_count  ){
                    $stakehodlerE = false;
                }else{
                    if( $dir_count > 1 || $sec_count > 1 || $sh_count >1 ){
                        $stakehodlerE = false;
                    }else {
                        $stakehodlerE = true;
                    }
                   
                }
            }else{
                $stakehodlerE = false;
            }
        }


    }else {
       $stakehodlerE = true;
        
    }

    $external_global_comment = '';

    if( 
        
        $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
        $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
        $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
        $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'

    ){
           $resumbmit_key_id = array(
               $this->settings('COMPANY_STATUS_REQUEST_TO_RESUBMIT','key')->id,
               $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT','key')->id,
               $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1','key')->id,
               $this->settings('COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2','key')->id
               
           );
           $external_comment_key_id = $this->settings('COMMENT_EXTERNAL','key')->id;
           $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                    ->where('comment_type', $external_comment_key_id )
                                                    ->whereIn('status', $resumbmit_key_id )
                                                    ->orderBy('id', 'desc')
                                                    ->first();
           $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                                      ?  $external_comment_query->comments
                                      : '';

    } 

    $countries_cache = Cache::rememberForever('countries_cache', function () {
        return Country::all();
    });
    $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

    $postfix_values = $this->getPostfixValues($company_info->postfix);


     $ird_count = InlandRevenueDetails::where('company_id', $request->companyId)->count();
    $ird_info = null;
    if($ird_count){
        $ird_info = InlandRevenueDetails::where('company_id', $request->companyId)->first();
    }

    $labour_info = LabourDetails::where('company_id', $request->companyId)->first();
    $labour_info_arr = array(
        'id' => null,
        'nature_category' => '',
        'sub_nature_category' => '',
        'total_no_emp' => '',
        'total_no_cov_emp' => '',
        'total_no_other_than_cov_emp' => '',
        'recruited_date' => ''

    );
    if(isset($labour_info->id)){
        
        $labour_info_arr['id'] = $labour_info->id;
        $labour_info_arr['nature_category'] = $labour_info->nature_category;
        $labour_info_arr['sub_nature_category'] = ($labour_info->sub_nature_category) ? $labour_info->sub_nature_category : '';
        $labour_info_arr['total_no_emp'] = $labour_info->total_no_emp;
        $labour_info_arr['total_no_cov_emp'] = $labour_info->total_no_cov_emp;
        $labour_info_arr['total_no_other_than_cov_emp'] = $labour_info->total_no_other_than_cov_emp;
        $labour_info_arr['recruited_date'] = $labour_info->recruited_date;
    }
    

    if (
        !(  $process_status === 'COMPANY_NAME_APPROVED' ||
        $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
        $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
        $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
        $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' ||
            
            ( ($companyType->key === 'COMPANY_TYPE_OVERSEAS' || $companyType->key === 'COMPANY_TYPE_OFFSHORE') &&   $process_status === 'COMPANY_FOREIGN_STATUS_PAYMENT_PENDING') )
        ) {


           

          
            

            return response()->json([
                'message' => 'Invalid Company Incorporation.',
                'status' =>true,
                'data'   => array(
                        'processStatus' => $this->settings($company_info->status,'id')->key,
                        'createrValid' => true,  
                    //    'compnayTypes' => $company_types,
                        'companyInfo'  => $company_info,
                        'companyAddress' => $company_address,
                        'companyForAddress' => $company_for_address,
                       // 'companyObjectives' => $company_objectives,
                        'companyType'    =>$companyType,
                        'countries'     => $countries_cache,
                        'loginUser'     => $userPeople,
                        'loginUserAddress'=> $userAddress,
                        //'payment' => $this->documents(),
                        'payment' =>$payment,
                        'payment_new' => $payment_new,
                        
                        'directors' => $directors,
                        'secs' => $secs,
                        'secs_firms' => $secs_firms,
                        'shareholders' => $shareholders,
                        'shareholderFirms' => $shareholderFirms,
                        'documents' =>$documentList,
                        'public_path' =>  storage_path(),
                       // 'companyTypes' => $this->settings('COMPANY_TYPES','key'),

                      //  'postfix' => $postfix_arr['postfix'],
                      //  'postfix_si' => $postfix_arr['postfix_si'],
                      //  'postfix_ta' => $postfix_arr['postfix_ta'],

                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],

                        //'docList' => $this->getDocs($companyType->key),
                         'docList' => @$this->generate_files($companyType->key,$request->companyId,$loginUserEmail),
                        'coreShareGroups' => $core_groups_list,
                        'uploadedDocs' => $this->uploadedDocs($request->companyId),
                        'uploadedDocsWithData' => $this->uploadedDocsWithToken($request->companyId),
                        'uploadedDocsWithPages' => $this->uploadedDocsWithNoOfPages($request->companyId),
                        'uploadList' => $this->files_for_upload($companyType->key,$request->companyId),
                        'enableStep2Next' => count($directors) && ( count($secs ) || count($secs_firms) )  && ( count($shareholders) || count($shareholderFirms) ),
                        'stakehodlerE' => $stakehodlerE,
                        'external_global_comment' => $external_global_comment,
                        'incorporationPrice' => floatval( $this->incorporationPaymentValue( $companyType->key,'key') ),
                        'incorporationVat' => floatval( $this->settings('PAYMENT_GOV_VAT','key')->value ),
                        'incorporationOtherTaxes' =>floatval($this->settings('PAYMENT_OTHER_TAX','key')->value),
                        'incorporationConvenienceFee' =>floatval($this->settings('PAYMENT_CONVENIENCE_FEE','key')->value),
                        'additionalPageFee' => 50,
                      // 'incorporationConvenienceFee' =>0.5,
                      'companyObjectiveList' => CompanyObjective::where('company_id', $request->companyId)->get(),
                       'companyObjectiveListCount' => CompanyObjective::where('company_id', $request->companyId)->count(),
                       'irdCount' => $ird_count,
                       'irdInfo' => $ird_info,
                       'irdStatus' => isset($ird_info->status) ? $this->settings($ird_info->status,'id')->key : '',
                       'irdRejectMessage'=>isset($ird_info->rejected_resion) ? nl2br($ird_info->rejected_resion) : '',
                       'irdDirectorNic' => $this->files_for_upload_docs_for_ird($request->companyId),
                       'labourInfo' => $labour_info_arr,
                     
                    
                )
            ], 200);
       
        }

    return response()->json([
            'message' => 'Incorpartiaon Data is successfully loaded.',
            'status' =>true,
            'data'   => array(
                            'createrValid' => true,  
                          //  'compnayTypes' => $company_types,
                            'companyInfo'  => $company_info,
                            'processStatus' => $this->settings($company_info->status,'id')->key,
                            'companyAddress' => $company_address,
                            'companyForAddress' => $company_for_address,
                          //  'companyObjectives' => $company_objectives,
                            'companyType'    =>$companyType,
                            'countries'     => $countries_cache,
                            'loginUser'     => $userPeople,
                            'loginUserAddress'=> $userAddress,
                            'payment' =>$payment,
                            'payment_new' => $payment_new,
                            'directors' => $directors,
                            'secs' => $secs,
                            'secs_firms' => $secs_firms,
                            'shareholders' => $shareholders,
                            'shareholderFirms' => $shareholderFirms,
                            'documents' =>$documentList,
                            'public_path' =>  storage_path(),
                           // 'companyTypes' => $this->settings('COMPANY_TYPES','key'),
                           // 'postfix' => $postfix_arr['postfix'],
                           // 'postfix_si' => $postfix_arr['postfix_si'],
                           // 'postfix_ta' => $postfix_arr['postfix_ta'],

                           'postfix' => $company_info->postfix,
                           'postfix_si' => $postfix_values['postfix_si'],
                           'postfix_ta' => $postfix_values['postfix_ta'],

                            //'docList' => $this->getDocs($companyType->key),
                             'docList' => @$this->generate_files($companyType->key,$request->companyId,$loginUserEmail),
                            'coreShareGroups' => $core_groups_list,
                            'uploadedDocs' => $this->uploadedDocs($request->companyId),
                            'uploadedDocsWithData' => $this->uploadedDocsWithToken($request->companyId),
                            'uploadedDocsWithPages' => $this->uploadedDocsWithNoOfPages($request->companyId),
                            'uploadList' => $this->files_for_upload($companyType->key,$request->companyId),
                            'enableStep2Next' => count($directors) && ( count($secs ) || count($secs_firms) )  && ( count($shareholders) || count($shareholderFirms) ),
                            'stakehodlerE' => $stakehodlerE,
                            'external_global_comment' => $external_global_comment,
                            'incorporationPrice' => floatval( $this->incorporationPaymentValue( $companyType->key,'key') ),
                        'incorporationVat' => floatval( $this->settings('PAYMENT_GOV_VAT','key')->value ),
                        'incorporationOtherTaxes' =>floatval($this->settings('PAYMENT_OTHER_TAX','key')->value),
                        'additionalPageFee' => 50,
                       // 'incorporationConvenienceFee' =>0.5,
                       'incorporationConvenienceFee' =>floatval($this->settings('PAYMENT_CONVENIENCE_FEE','key')->value),
                        'companyObjectiveList' => CompanyObjective::where('company_id', $request->companyId)->get(),
                          'companyObjectiveListCount' => CompanyObjective::where('company_id', $request->companyId)->count(),
                          'irdCount' => $ird_count,
                          'irdInfo' => $ird_info,
                          'irdStatus' => isset($ird_info->status) ? $this->settings($ird_info->status,'id')->key : '',
                       'irdRejectMessage'=>isset($ird_info->rejected_resion) ? nl2br($ird_info->rejected_resion) : '',
                          'irdDirectorNic' => $this->files_for_upload_docs_for_ird($request->companyId),
                          'labourInfo' => $labour_info_arr,
                        )
        ], 200);
          
    }

    private function incorporationPaymentValue( $company_type ) {

        if($company_type == 'COMPANY_TYPE_PRIVATE') {
            return  $this->settings('PAYMENT_PRIVATE_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_PUBLIC') {
            return  $this->settings('PAYMENT_PUBLIC_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_UNLIMITED') {
            return  $this->settings('PAYMENT_UNLIMITED_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_GUARANTEE_32' || $company_type == 'COMPANY_TYPE_GUARANTEE_34' ) {
            return  $this->settings('PAYMENT_GURANTEE_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_OVERSEAS') {
            return  $this->settings('PAYMENT_OVERSEAS_COMPANY_REGISTRATION','key')->value;
        }
        if($company_type == 'COMPANY_TYPE_OFFSHORE') {
            return  $this->settings('PAYMENT_OFFSHORE_COMPANY_REGISTRATION','key')->value;
        }
    }

    public function resubmit(Request $request ){

        $type = $request->type;
        
        $status_tobe_updated = $type === 'foreign' ? $this->settings('COMPANY_FOREIGN_RESUBMITTED','key')->id  : $this->settings('COMPANY_STATUS_RESUBMITTED','key')->id;

        $company_update =  array(

            'status'    => $status_tobe_updated
        );
        Company::where('id', $request->company_id)->update($company_update);

        return response()->json([
            'message' => ' Successfully Resubmitted',
            'status' =>true,
           
        ], 200);
    }

    public function requestApprovalForForeign(Request $request ){

        $company_update =  array(

            'status'    => $this->settings('COMPANY_FOREIGN_STATUS_PENDING','key')->id 
        );
        Company::where('id', $request->company_id)->update($company_update);

        return response()->json([
            'message' => 'Successfully requested the registration approval',
            'status' =>true,
           
        ], 200);
    }


    public function submitPay(Request $request ){

        $company_update =  array(
            'status'    => $this->settings('COMPANY_STATUS_PENDING','key')->id 
        );
        Company::where('id', $request->company_id)->update($company_update);

        return response()->json([
            'message' => 'Payment Successful.',
            'status' =>true,
           
        ], 200);
    }


    public function submitStep1(Request $request){

        $company_id = $request->companyId;
        $company_info = Company::where('id',$company_id)->first();

        $addressId = $company_info->address_id;
        $forAddressId = $company_info->foreign_address_id;

        $companyType = $this->settings($company_info->type_id,'id')->key;

        $objective_collection = $request->objective_array;
        
        //remove all objective first
        CompanyObjective::where('company_id', $company_id)->delete();
        if(isset($objective_collection['collection']) && is_array($objective_collection['collection']) && count($objective_collection['collection'])) {
            foreach($objective_collection['collection'] as $obj ) {
                 
                $newobjective = new CompanyObjective;
                $newobjective->company_id = $company_id;
                $newobjective->objective1 = $obj['objective1'];
                $newobjective->objective2 = $obj['objective2'];
                $newobjective->objective3 = $obj['objective3'];
                $newobjective->objective4 = $obj['objective4'];
                $newobjective->objective5 = $obj['objective5'];
                $newobjective->save();
            }
        }

        $company_update =  array(
            'email'     => $request->email,
            'type_id'   => $request->companyType,
          //  'objective1' => $request->objective1,
         //   'objective2' => $request->objective2,
         //   'objective3' => $request->objective3,
          //  'objective4' => $request->objective4,
          //  'objective5' => $request->objective5,
          //  'otherObjective'=> ($request->objective1 == 999999 ) ? $request->objectiveOther : null

        );
        
        Company::where('id', $company_id)->update($company_update);

        if( $addressId ) {
            $address_update = array(
                'address1' => $request->address1,
                'address2' => $request->address2,
                'city' => $request->city,
                'district' => $request->district,
                'province'  => $request->province,
                'gn_division'=>  $request->gn_division,
                'postcode' => $request->postcode
            );
            Address::where('id', $addressId)->update($address_update);
        } else {
             
            $company_address = new Address;
            $company_address->address1 = $request->address1;
            $company_address->address2 = $request->address2;
            $company_address->city = $request->city;
            $company_address->district = $request->district;
            $company_address->province = $request->province;
            $company_address->gn_division = $request->gn_division;
            $company_address->postcode = $request->postcode;

            $company_address->save();
            $new_company_address_id = $company_address->id;

            $company_update =  array(
                'address_id' => $new_company_address_id,
            );
            Company::where('id', $company_id)->update($company_update);
        }
        
        if($companyType == 'COMPANY_TYPE_OVERSEAS' || $companyType == 'COMPANY_TYPE_OFFSHORE' ){

            if($forAddressId){
    
                $address_update = array(
                    'address1' => $request->forAddress1,
                    'address2' => $request->forAddress2,
                    'city' => $request->forCity,
                    'district' => $request->forProvince,
                    'province'  => $request->forProvince,
                    'country'   => $request->forCountry,
                    'postcode' => $request->forPostcode
                );
                Address::where('id', $forAddressId)->update($address_update);
    
            }else{
                $forAddress = new Address;
                $forAddress->address1 = $request->forAddress1;
                $forAddress->address2 = $request->forAddress2;
                $forAddress->city = $request->forCity;
                $forAddress->district = $request->forProvince;
                $forAddress->province = $request->forProvince;
                $forAddress->country = $request->forCountry;
                $forAddress->postcode = $request->forPostcode;
                $forAddress->save();
                $addressId = $forAddress->id;
    
                $company_update =  array(
                    'foreign_address_id' => $addressId,
                );
                Company::where('id', $company_id)->update($company_update);
    
            }

        }
       
        return response()->json([
            'message' => 'data.',
            'status' =>true,
            'data'   => Company::where('id',$request->companyId)->first()
        ], 200);

    }
 
    function removeStakeHolder(Request  $request ){

          $stakeholder_id = $request->userId;
          $company_id = $request->companyId;

          $delete = CompanyMember::where('id', $stakeholder_id)->delete();

          $remove = CompanyDocuments::where('company_id', $company_id)
          ->where('company_member_id', $stakeholder_id)
          ->delete();

          if($delete){

            return response()->json([
                'message' => 'Successfully deleted the stakeholder',
                'status' =>true
               
            ], 200);

          }else{
            return response()->json([
                'message' => 'Failed deleting the stakeholder. Please try again',
                'status' =>false
              
            ], 200);
          }

         
    }

    function submitStep2(Request $request){

        $company_id = $request->companyId;
        $loginUserEmail = $this->clearEmail($request->loginUser);
        $direcotrList = array();
        $secList = array();
        $shareHolderList = array();

        $company_info = Company::where('id',$company_id)->first();
        $companyType = $this->settings($company_info->type_id,'id');


        //loop through add director list
        foreach($request->directors['directors'] as $director ){

               $addressId= null;
               $forAddressId = null;
               
               if($director['province'] || $director['district'] ||  $director['city'] || $director['localAddress1'] || $director['localAddress2'] || $director['postcode'] ) {
                $address = new Address;
              //  $address->id = 9999;
                $address->province = $director['province'];
                $address->district =  $director['district'];
                $address->city =  $director['city'];
                $address->address1 =  $director['localAddress1'];
                $address->address2 =  $director['localAddress2'];
                $address->postcode = $director['postcode'];
                $address->country =   'Sri Lanka';
              
                $address->save();
                $addressId = $address->id;
               }

               if($director['forProvince'] ||  $director['forCity'] || $director['forAddress1'] || $director['forAddress2'] || $director['forPostcode'] ) {
                $forAddress = new Address;
              //  $address->id = 9999;
                $forAddress->province = $director['forProvince'];
                $forAddress->district = null;
                $forAddress->city =  $director['forCity'];
                $forAddress->address1 =  $director['forAddress1'];
                $forAddress->address2 =  $director['forAddress2'];
                $forAddress->postcode = $director['forPostcode'];
                $forAddress->country =  $director['country'];
              
                $forAddress->save();
                $forAddressId = $forAddress->id;
               }

             //if director as a shareholder
             if( 
                  (isset($director['isShareholder']) &&  $director['isShareholder'] ) ||
                  ( isset($director['isShareholderEdit']) &&  $director['isShareholderEdit'] ) 
             ){

                if( 
                    ( isset($director['shareType']) && $director['shareType'] ) ||
                    ( isset($director['shareTypeEdit']) && $director['shareTypeEdit'] )
                 ){

                    $shareHolderAddressId = null;
                    $shareHolderAddressForId = null;

                    if($director['province'] || $director['district'] || $director['city'] || $director['localAddress1'] || $director['localAddress2'] || $director['postcode']  ) {
                        $dir_to_share_address = new Address;
                        $dir_to_share_address->province = $director['province'];
                        $dir_to_share_address->district =  $director['district'];
                        $dir_to_share_address->city =  $director['city'];
                        $dir_to_share_address->address1 =  $director['localAddress1'];
                        $dir_to_share_address->address2 =  $director['localAddress2'];
                        $dir_to_share_address->postcode = $director['postcode'];
                        $dir_to_share_address->country = 'Sri Lanka';
                        $dir_to_share_address->save();
                        $shareHolderAddressId = $dir_to_share_address->id;
                    }
                    if($director['forProvince'] || $director['forCity'] || $director['forAddress1'] || $director['forAddress2'] || $director['forPostcode']  ) {
                        $dir_to_share_for_address = new Address;
                        $dir_to_share_for_address->province = $director['forProvince'];
                        $dir_to_share_for_address->city =  $director['forCity'];
                        $dir_to_share_for_address->address1 =  $director['forAddress1'];
                        $dir_to_share_for_address->address2 =  $director['forAddress2'];
                        $dir_to_share_for_address->postcode = $director['forPostcode'];
                        $dir_to_share_for_address->country =  $director['country'];
                        $dir_to_share_for_address->save();
                        $shareHolderAddressForId = $dir_to_share_for_address->id;
                    }

                    if( 
                        ( isset($director['shareType']) &&  $director['shareType'] == 'single' && isset($director['noOfSingleShares']) &&  intval($director['noOfSingleShares']) ) ||
                        ( isset($director['shareTypeEdit']) && $director['shareTypeEdit'] == 'single' && $director['noOfSingleSharesEdit'] &&  intval($director['noOfSingleSharesEdit']) )
                    ) {
                        $dir_shareholder = new CompanyMember;
                        $dir_shareholder->company_id = $company_id;
                        $dir_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $dir_shareholder->is_srilankan = ( isset( $director['type'] ) && $director['type'] == 'foreign' ) ? 'no' : 'yes';
                        $dir_shareholder->title = $director['title'];
                        $dir_shareholder->first_name = $director['firstname'];
                        $dir_shareholder->last_name =$director['lastname'];
                        $dir_shareholder->address_id = $shareHolderAddressId;
                        $dir_shareholder->foreign_address_id = $shareHolderAddressForId;
                        $dir_shareholder->nic = strtoupper($director['nic']);
                        $dir_shareholder->passport_no = strtoupper($director['passport']);
    
                        $dir_shareholder->passport_issued_country = ( isset( $director['type'] ) && $director['type'] == 'foreign' && isset($director['passport_issued_country']) ) ? $director['passport_issued_country'] :  'Sri Lanka';
                        $dir_shareholder->telephone =$director['phone'];
                        $dir_shareholder->mobile =$director['mobile'];
                        $dir_shareholder->email =$director['email'];
                        $dir_shareholder->occupation =$director['occupation'];
                        $dir_shareholder->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
                        $dir_shareholder->status =1;
                        $dir_shareholder->save();
                        $newDirShareHolderID = $dir_shareholder->id;
                        $singleShares=0;

                        if(isset($director['noOfSingleShares']) &&  intval($director['noOfSingleShares'])){
                            $singleShares = intval($director['noOfSingleShares']);
                        }
                        if( isset($director['noOfSingleSharesEdit']) &&  intval($director['noOfSingleSharesEdit']) ){
                            $singleShares = intval($director['noOfSingleSharesEdit']);
                        }
    
                        //add to single share group
                        $dir_shareholder_sharegroup = new ShareGroup;
                        $dir_shareholder_sharegroup->type ='single_share';
                        $dir_shareholder_sharegroup->name ='single_share_no_name';
                        $dir_shareholder_sharegroup->no_of_shares =$singleShares;
                        $dir_shareholder_sharegroup->status = 1;
                        $dir_shareholder_sharegroup->company_id = $company_id;
    
                        $dir_shareholder_sharegroup->save();
                        $dir_shareholder_sharegroupID = $dir_shareholder_sharegroup->id;
    
                        //add to share table
                        $dir_shareholder_share = new Share;
                        $dir_shareholder_share->company_member_id = $newDirShareHolderID;
                        $dir_shareholder_share->group_id = $dir_shareholder_sharegroupID;
                        $dir_shareholder_share->save();
                    }
    
                    if(
                       ( isset($director['shareType']) &&  $director['shareType'] == 'core' &&  isset($director['coreGroupSelected']) && intval( $director['coreGroupSelected']) ) || 
                       ( isset($director['shareTypeEdit']) &&  $director['shareTypeEdit'] == 'core' &&  isset($director['coreGroupSelectedEdit']) && intval( $director['coreGroupSelectedEdit']) ) 
                        
                    ){

                        $dir_shareholder = new CompanyMember;
                        $dir_shareholder->company_id = $company_id;
                        $dir_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $dir_shareholder->is_srilankan = ( isset( $director['type'] ) && $director['type'] == 'foreign' ) ? 'no' : 'yes';
                        $dir_shareholder->title = $director['title'];
                        $dir_shareholder->first_name = $director['firstname'];
                        $dir_shareholder->last_name =$director['lastname'];
                        $dir_shareholder->address_id = $shareHolderAddressId;
                        $dir_shareholder->foreign_address_id = $shareHolderAddressForId;
                        $dir_shareholder->nic = strtoupper($director['nic']);
    
                        $dir_shareholder->passport_no = strtoupper($director['passport']);
                        $dir_shareholder->passport_issued_country = ( isset( $director['type'] ) && $director['type'] == 'foreign' && isset($director['passport_issued_country']) ) ? $director['passport_issued_country'] :  'Sri Lanka';
                        $dir_shareholder->telephone =$director['phone'];
                        $dir_shareholder->mobile =$director['mobile'];
                        $dir_shareholder->email =$director['email'];
                        $dir_shareholder->occupation =$director['occupation'];
                      //  $dir_shareholder->no_of_shares ='100';
                        $dir_shareholder->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
                        $dir_shareholder->status =1;
                        $dir_shareholder->save();
                        $newDirShareHolderID = $dir_shareholder->id;


                        $selectedShareGroup='';
                        if(isset($director['coreGroupSelected']) && intval( $director['coreGroupSelected'])){
                            $selectedShareGroup =  $director['coreGroupSelected'];
                        }
                        if(isset($director['coreGroupSelectedEdit']) && intval( $director['coreGroupSelectedEdit'])){
                            $selectedShareGroup =  $director['coreGroupSelectedEdit'];
                        }

                        //add to share table
                        $dir_shareholder_share = new Share;
                        $dir_shareholder_share->company_member_id = $newDirShareHolderID;
                        $dir_shareholder_share->group_id =intval( $selectedShareGroup);
                        $dir_shareholder_share->save();
                    }
    
                    if( 
                        ( isset($director['shareType']) && $director['shareType'] == 'core' && empty( $director['coreGroupSelected'])  && $director['coreShareGroupName'] && intval($director['coreShareValue']) ) ||
                        ( isset($director['shareTypeEdit']) && $director['shareTypeEdit'] == 'core' && empty( $director['coreGroupSelectedEdit'])  && $director['coreShareGroupNameEdit'] && intval($director['coreShareValueEdit']) )
                    ) {

                        $dir_shareholder = new CompanyMember;
                        $dir_shareholder->company_id = $company_id;
                        $dir_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $dir_shareholder->is_srilankan = ( isset( $director['type'] ) && $director['type'] == 'foreign' ) ? 'no' : 'yes';
                        $dir_shareholder->title = $director['title'];
                        $dir_shareholder->first_name = $director['firstname'];
                        $dir_shareholder->last_name =$director['lastname'];
                        $dir_shareholder->address_id = $shareHolderAddressId;
                        $dir_shareholder->foreign_address_id = $shareHolderAddressForId;
                        $dir_shareholder->nic = strtoupper($director['nic']);
                        $dir_shareholder->passport_no = strtoupper($director['passport']);
                        $dir_shareholder->passport_issued_country = ( isset( $director['type'] ) && $director['type'] == 'foreign' && isset($director['passport_issued_country']) ) ? $director['passport_issued_country'] :  'Sri Lanka';
                        $dir_shareholder->telephone =$director['phone'];
                        $dir_shareholder->mobile =$director['mobile'];
                        $dir_shareholder->email =$director['email'];
                        $dir_shareholder->occupation =$director['occupation'];
                      //  $dir_shareholder->no_of_shares ='100';
                        $dir_shareholder->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
                        $dir_shareholder->status =1;
    
                        $dir_shareholder->save();
                        $newDirShareHolderID = $dir_shareholder->id;

                        $coreShareGroupName='';
                        $coreShareValue = '';
                        if(isset($director['shareType']) && $director['shareType'] == 'core' && empty( $director['coreGroupSelected'])  && $director['coreShareGroupName'] && intval($director['coreShareValue'])){

                            $coreShareGroupName = $director['coreShareGroupName'];
                            $coreShareValue = $director['coreShareValue'];
                        }
                        if(isset($director['shareTypeEdit']) && $director['shareTypeEdit'] == 'core' && empty( $director['coreGroupSelectedEdit']) && $director['coreShareGroupNameEdit'] && intval($director['coreShareValueEdit'])){

                            $coreShareGroupName = $director['coreShareGroupNameEdit'];
                            $coreShareValue = $director['coreShareValueEdit'];
                        }
    
                        //add to single share group
                        $dir_shareholder_sharegroup = new ShareGroup;
                        $dir_shareholder_sharegroup->type ='core_share';
                        $dir_shareholder_sharegroup->name = $coreShareGroupName;
                        $dir_shareholder_sharegroup->no_of_shares =intval( $coreShareValue );
                        $dir_shareholder_sharegroup->company_id = $company_id;
                        $dir_shareholder_sharegroup->status = 1;
    
                        $dir_shareholder_sharegroup->save();
                        $dir_shareholder_sharegroupID = $dir_shareholder_sharegroup->id;
    
                        //add to share table
                        $dir_shareholder_share = new Share;
                        $dir_shareholder_share->company_member_id = $newDirShareHolderID;
                        $dir_shareholder_share->group_id = $dir_shareholder_sharegroupID;
                        $dir_shareholder_share->save();
                    }

               }
    

             } //end if director is a shareholder

             //if director is a secretory
             if( ( isset($director['isSec']) &&  $director['isSec'] ) || (isset($director['isSecEdit']) &&  $director['isSecEdit']) ){

                $dir_to_sec_address = new Address;
                $dir_to_sec_address->province = $director['province'];
                $dir_to_sec_address->district =  $director['district'];
                $dir_to_sec_address->city =  $director['city'];
                $dir_to_sec_address->address1 =  $director['localAddress1'];
                $dir_to_sec_address->address2 =  $director['localAddress2'];
                $dir_to_sec_address->postcode = $director['postcode'];
                $dir_to_sec_address->country = 'Sri Lanka';
                
                $dir_to_sec_address->save();
                $secAddressId = $dir_to_sec_address->id;

                $dir_sec = new CompanyMember;
                        $dir_sec->company_id = $company_id;
                        $dir_sec->designation_type = $this->settings('SECRETARY','key')->id;
                        $dir_sec->is_srilankan = 'yes';
                        $dir_sec->title = $director['title'];
                        $dir_sec->first_name = $director['firstname'];
                        $dir_sec->last_name =$director['lastname'];
                        $dir_sec->address_id = $secAddressId;
                        $dir_sec->nic = strtoupper($director['nic']);
    
                        $dir_sec->passport_issued_country ='Sri Lanka';
                        $dir_sec->telephone =$director['phone'];
                        $dir_sec->mobile =$director['mobile'];
                        $dir_sec->email =$director['email'];
                        $dir_sec->occupation =$director['occupation'];
                      //  $dir_sec->no_of_shares ='0';
                        $dir_sec->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
                        $dir_sec->is_registered_secretary = isset($director['secRegDate']) ? 'yes'  : 'no';
                        $dir_sec->secretary_registration_no = isset($director['secRegDate']) ? $director['secRegDate'] : NULL;
                        $dir_sec->is_natural_person ="yes";
                        $dir_sec->status =1;
                        $dir_sec->save();
                        $newDirSecID = $dir_sec->id;
             }


            if(isset($director['id']) && $director['id'] ){
                $updateDirector = CompanyMember::find($director['id']);
            }else{
                $updateDirector = new CompanyMember;
            }

            $updateDirector->company_id = $company_id;
            $updateDirector->designation_type =  $this->settings('DERECTOR','key')->id;
            $updateDirector->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
            
            $company_info = Company::where('id',$company_id)->first(); 
            $process_status = $this->settings($company_info->status,'id')->key;
            $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
            $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
            $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
            $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
            
            if(!$process_status_val){
               // $updateDirector->title = $director['title'];
               // $updateDirector->first_name = $director['firstname'];
              //  $updateDirector->last_name = $director['lastname'];
                $updateDirector->nic = strtoupper($director['nic']);
                $updateDirector->passport_no = $director['passport'];
            }
            $updateDirector->title = $director['title'];
            $updateDirector->first_name = $director['firstname'];
            $updateDirector->last_name = $director['lastname'];
            
            $updateDirector->address_id = $addressId;
            $updateDirector->foreign_address_id =  $forAddressId;
            $updateDirector->passport_issued_country = isset( $director['passport_issued_country']) ? $director['passport_issued_country'] : $director['country'];
            $updateDirector->telephone = $director['phone'];
            $updateDirector->mobile =$director['mobile'];
            $updateDirector->email = $director['email'];
           // $updateDirector->foreign_address_id =($director['type'] !='local') ? $addressId: 0;
            $updateDirector->occupation = $director['occupation'];
           // $updateDirector->no_of_shares = $director['share'];
            $updateDirector->date_of_appointment = date('Y-m-d',strtotime($director['date']) );
            $updateDirector->status = 1;

            $updateDirector->save();

             //add to peoples table
             if( $director['nic'] ){
                $check_people = People::where('nic', $director['nic'] )->count();
                if($check_people == 0 ){

                    $people = new People;
                    $people->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
                    $people->title = $this->settings('TITLE_MR','key')->id;
                    $people->first_name = $director['firstname'];
                    $people->last_name =$director['lastname'];
                    $people->address_id = $addressId;
                    $people->foreign_address_id =  $forAddressId;
                    $people->nic = strtoupper($director['nic']);

                    $people->passport_issued_country ='Sri Lanka';
                    $people->telephone =$director['phone'];
                    $people->mobile =$director['mobile'];
                    $people->email =$director['email'];
                    $people->occupation =$director['occupation'];
                    $people->sex ='male';
                    $people->status =1;
                    $people->save();

                }

            }
            //add to peoples table
            if( $director['passport'] ){
                $check_people = People::where('passport_no', $director['passport'] )->count();
                if($check_people == 0 ){

                    $people = new People;
                    $people->is_srilankan =  $director['type'] != 'local' ?  'no' : 'yes';
                    $people->title = $this->settings('TITLE_MR','key')->id;
                    $people->first_name = $director['firstname'];
                    $people->last_name =$director['lastname'];
                    $people->address_id = $addressId;
                    $people->foreign_address_id =  $forAddressId;
                    $people->passport_no = strtoupper($director['passport']);
                    $people->passport_issued_country =$director['passport_issued_country'];
                    $people->telephone =$director['phone'];
                    $people->mobile =$director['mobile'];
                    $people->email =$director['email'];
                    $people->occupation =$director['occupation'];
                    $people->sex ='male';
                    $people->status =1;
                    $people->save();

                }

            }
        }
        
        //loop through add secrotory list
        foreach($request->secretories['secs'] as $sec ){

            $companyFirmAddressId = null;

            $addressId= null;
             $forAddressId = null;
     
            if( $sec['secType'] == 'firm' ) {

                $companyFirmAddress = new Address;
                $companyFirmAddress->province = $sec['firm_province'];
                $companyFirmAddress->district =  $sec['firm_district'];
                $companyFirmAddress->city =  $sec['firm_city'];
                $companyFirmAddress->address1 =  $sec['firm_localAddress1'];
                $companyFirmAddress->address2 =  $sec['firm_localAddress2'];
                $companyFirmAddress->postcode = $sec['firm_postcode'];
                $companyFirmAddress->country = isset($sec['firm_country'] ) ? $sec['firm_country'] : 'Sri Lanka';
              
                $companyFirmAddress->save();
                $companyFirmAddressId = $companyFirmAddress->id;

                if(isset($sec['id']) && $sec['id'] ){
                    $updateSec = CompanyFirms::find($sec['id']);
                }else{
                    $updateSec = new CompanyFirms;
                }

            } else {

                if($sec['province'] || $sec['district'] ||  $sec['city'] || $sec['localAddress1'] || $sec['localAddress2'] || $sec['postcode'] ) {
                 $address = new Address;
               //  $address->id = 9999;
                 $address->province = $sec['province'];
                 $address->district =  $sec['district'];
                 $address->city =  $sec['city'];
                 $address->address1 =  $sec['localAddress1'];
                 $address->address2 =  $sec['localAddress2'];
                 $address->postcode = $sec['postcode'];
                 $address->country =  'Sri Lanka';
               
                 $address->save();
                 $addressId = $address->id;
                }
                
                $postcodecheck =  ($companyType->key === 'COMPANY_TYPE_OVERSEAS' || $companyType->key === 'COMPANY_TYPE_OFFSHORE') ? true : isset($sec['forPostcode']);
                if(
                   ( isset( $sec['forProvince'] ) && isset($sec['forCity']) && isset($sec['forAddress1']) && isset($sec['forAddress2']) && $postcodecheck ) && 
                   ( $sec['forProvince'] ||  $sec['forCity'] || $sec['forAddress1'] || $sec['forAddress2'] || $sec['forPostcode'] )
                    
                ) {
                 $forAddress = new Address;
               //  $address->id = 9999;
                 $forAddress->province = $sec['forProvince'];
                 $forAddress->district = null;
                 $forAddress->city =  $sec['forCity'];
                 $forAddress->address1 =  $sec['forAddress1'];
                 $forAddress->address2 =  $sec['forAddress2'];
                 $forAddress->postcode = $sec['forPostcode'];
                 $forAddress->country =  $sec['country'];
               
                 $forAddress->save();
                 $forAddressId = $forAddress->id;
                }





                if(isset($sec['id']) && $sec['id'] ){
                    $updateSec = CompanyMember::find($sec['id']);
                }else{
                    $updateSec = new CompanyMember;
                }

            }

            $newSecShareHolderID = null;

            /*** */
            //if sec as a shareholder
            if(
                ( isset($sec['isShareholder']) &&  $sec['isShareholder'] ) || 
                ( isset($sec['isShareholderEdit']) &&  $sec['isShareholderEdit'] )
            ){

                if(
                     ( isset($sec['shareType']) && $sec['shareType'] ) || 
                     ( isset($sec['shareTypeEdit']) && $sec['shareTypeEdit'] )
                ){



                 if($sec['secType'] == 'firm'){

                        $secFirmId = ( isset($sec['id']) && $sec['id'] ) ? $sec['id'] : $updateSec->id;

                        if(isset($sec['id']) && $sec['id'] ){

                            $cf = CompanyFirms::find($sec['id']);
                           // $sec_as_sh_count =  ( intval( $cf->sh_firm_of ) > 0 )  ? 1 : 0 ;
                            $sec_as_sh_count =  ( isset($cf->sh_firm_of) &&  intval( $cf->sh_firm_of ) > 0 )  ? 1 : 0 ;
                        } else {
                            $sec_as_sh_count = 0;
                        }

                    if( $sec_as_sh_count  == 0 ) { // check shareholdr firm

                        $sec_shareholder = new CompanyFirms;
                        $sec_shareholder->registration_no = $sec['pvNumber'];
                        $sec_shareholder->name = $sec['firm_name'];
                        $sec_shareholder->email = $sec['firm_email'];
                        $sec_shareholder->mobile = $sec['firm_mobile'];
                        $sec_shareholder->phone = $sec['firm_phone'];
                        $sec_shareholder->date_of_appointment =  date('Y-m-d',strtotime($sec['firm_date']) );
                        $sec_shareholder->company_id = $company_id;
                        $sec_shareholder->address_id = $companyFirmAddressId;
                        $sec_shareholder->type_id = $this->settings('SHAREHOLDER','key')->id;
                        $sec_shareholder->status = 1;
                      //  $sec_shareholder->sec_firm_id = $secFirmId;
                        $sec_shareholder->save();
                        $newSecShareHolderID = $sec_shareholder->id;

                        //ADD BENIFICIARY OWNER

                        if( isset($sec['secBenifList']['ben'])  &&  is_array($sec['secBenifList']['ben'])) {

                            //first remove all records of benif
                            CompanyMember::where('company_id', $company_id)
                                        ->where('company_member_firm_id', $newSecShareHolderID )
                                        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                        ->where('is_beneficial_owner', 'yes')
                                        ->delete();

                            foreach(  $sec['secBenifList']['ben'] as $ben ) {

                                $benAddress = new Address;
                                $benAddress->province = $ben['province'];
                                $benAddress->district =  ($ben['type'] == 'local' ) ? $ben['district'] : null;
                                $benAddress->city =  $ben['city'];
                                $benAddress->address1 =  $ben['localAddress1'];
                                $benAddress->address2 =  $ben['localAddress2'];
                                $benAddress->postcode = $ben['postcode'];
                                $benAddress->country =  ($ben['type'] == 'local' ) ? 'Sri Lanka' : $ben['country'];
                
                                $benAddress->save();
                                $benAddress_id = $benAddress->id;
                                $benuUser = new CompanyMember;
                                $benuUser->company_id = $company_id;
                                $benuUser->designation_type = $this->settings('SHAREHOLDER','key')->id;
                                $benuUser->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
                                $benuUser->title = $ben['title'];
                                $benuUser->first_name = $ben['firstname'];
                                $benuUser->last_name = $ben['lastname'];
                                $benuUser->address_id = $benAddress_id;
                                $benuUser->nic = ( $ben['type'] == 'local' ) ? strtoupper($ben['nic']) : null;
                                $benuUser->passport_no = ( $ben['type'] == 'local' ) ? null : $ben['passport'];
                                $benuUser->passport_issued_country = ( $ben['type'] == 'local' )  ? null : $ben['country'];
                                $benuUser->telephone = $ben['phone'];
                                $benuUser->mobile =$ben['mobile'];
                                $benuUser->email = $ben['email'];
                                $benuUser->is_beneficial_owner = 'yes';
                                $benuUser->company_member_firm_id = $newSecShareHolderID;
                            
                                $benuUser->occupation = $ben['occupation'];
                                $benuUser->date_of_appointment = date('Y-m-d',strtotime($ben['date']) );
                                $benuUser->status = 1;

                                $benuUser->save();
                                $benUserId = $benuUser->id;

                            }
         
                            }


                        } //end check of already have shareholder firm

                      }else{

                        $sec_shareholder = new CompanyMember;
                        $sec_shareholder->company_id = $company_id;
                        $sec_shareholder->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $sec_shareholder->is_srilankan = 'yes';
                        $sec_shareholder->title = 'Mr.'; //$sec['title'];
                        $sec_shareholder->first_name = $sec['firstname'];
                        $sec_shareholder->last_name =$sec['lastname'];
                        $sec_shareholder->address_id = $addressId;
                        $sec_shareholder->nic = strtoupper($sec['nic']);
    
                        $sec_shareholder->passport_issued_country ='Sri Lanka';
                        $sec_shareholder->telephone =$sec['phone'];
                        $sec_shareholder->mobile =$sec['mobile'];
                        $sec_shareholder->email =$sec['email'];
                        $sec_shareholder->occupation =$sec['occupation'];
                      //  $sec_shareholder->no_of_shares ='100';
                        $sec_shareholder->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                        $sec_shareholder->status =1;
    
                        $sec_shareholder->save();
                        $newSecShareHolderID = $sec_shareholder->id;

                      }
    
                    if( 
                        ( isset($sec['shareType']) && $sec['shareType'] == 'single' && isset($sec['noOfSingleShares']) && intval($sec['noOfSingleShares']) ) || 
                        ( isset($sec['shareTypeEdit']) && $sec['shareTypeEdit'] == 'single' && isset($sec['noOfSingleSharesEdit']) && intval($sec['noOfSingleSharesEdit']) )
                        
                    ) {
                        $singleShares=0;

                        if(isset($sec['noOfSingleShares']) &&  intval($sec['noOfSingleShares'])){
                            $singleShares = intval($sec['noOfSingleShares']);
                        }
                        if( isset($sec['noOfSingleSharesEdit']) &&  intval($sec['noOfSingleSharesEdit']) ){
                            $singleShares = intval($sec['noOfSingleSharesEdit']);
                        }
    
    
                        //add to single share group
                        $sec_shareholder_sharegroup = new ShareGroup;
                        $sec_shareholder_sharegroup->type ='single_share';
                        $sec_shareholder_sharegroup->name ='single_share_no_name';
                        $sec_shareholder_sharegroup->no_of_shares = $singleShares;
                        $sec_shareholder_sharegroup->company_id = $company_id;
                        $sec_shareholder_sharegroup->status = 1;
    
                        $sec_shareholder_sharegroup->save();
                        $sec_shareholder_sharegroupID = $sec_shareholder_sharegroup->id;
    
                        //add to share table
    
                        $sec_shareholder_share = new Share;

                        if( $sec['secType'] == 'firm'){
                            $sec_shareholder_share->company_firm_id = $newSecShareHolderID;
                        }else {
                            $sec_shareholder_share->company_member_id = $newSecShareHolderID;
                        }

                        $sec_shareholder_share->group_id = $sec_shareholder_sharegroupID;
                        $sec_shareholder_share->save();
                    }
    
                    if(
                       ( isset( $sec['shareType']) &&  $sec['shareType'] == 'core' &&  isset($sec['coreGroupSelected']) && intval( $sec['coreGroupSelected']) ) || 
                       ( isset( $sec['shareTypeEdit']) &&  $sec['shareTypeEdit'] == 'core' &&  isset($sec['coreGroupSelectedEdit']) && intval( $sec['coreGroupSelectedEdit']) )
                    
                    ){
    
                        $selectedShareGroup='';
                        if(isset($sec['coreGroupSelected']) && intval( $sec['coreGroupSelected'])){
                            $selectedShareGroup =  $sec['coreGroupSelected'];
                        }
                        if(isset($sec['coreGroupSelectedEdit']) && intval( $sec['coreGroupSelectedEdit'])){
                            $selectedShareGroup =  $sec['coreGroupSelectedEdit'];
                        }
    
                        //add to share table
                        $sec_shareholder_share = new Share;
                        if( $sec['secType'] == 'firm'){
                            $sec_shareholder_share->company_firm_id = $newSecShareHolderID;
                        }else {
                            $sec_shareholder_share->company_member_id = $newSecShareHolderID;
                        }
                        $sec_shareholder_share->group_id =intval($selectedShareGroup );
                        $sec_shareholder_share->save();
                    }
    
                    if( 
                        ( isset( $sec['shareType'] ) &&  $sec['shareType'] == 'core' && empty( $sec['coreGroupSelected'])  && $sec['coreShareGroupName'] && intval($sec['coreShareValue']) ) || 
                        ( isset( $sec['shareTypeEdit'] ) &&  $sec['shareTypeEdit'] == 'core' && empty( $sec['coreGroupSelectedEdit'])  && $sec['coreShareGroupNameEdit'] && intval($sec['coreShareValueEdit']) )
                        
                    ) {
    
                        $coreShareGroupName='';
                        $coreShareValue = '';
                        if(isset($sec['shareType']) && $sec['shareType'] == 'core' && empty( $sec['coreGroupSelected'])  && $sec['coreShareGroupName'] && intval($sec['coreShareValue'])){

                            $coreShareGroupName = $sec['coreShareGroupName'];
                            $coreShareValue = $sec['coreShareValue'];
                        }
                        if(isset($sec['shareTypeEdit']) && $sec['shareTypeEdit'] == 'core' && empty( $sec['coreGroupSelectedEdit']) && $sec['coreShareGroupNameEdit'] && intval($sec['coreShareValueEdit'])){

                            $coreShareGroupName = $sec['coreShareGroupNameEdit'];
                            $coreShareValue = $sec['coreShareValueEdit'];
                        }
    
    
                        //add to single share group
                        $sec_shareholder_sharegroup = new ShareGroup;
                        $sec_shareholder_sharegroup->type ='core_share';
                        $sec_shareholder_sharegroup->name = $coreShareGroupName;
                        $sec_shareholder_sharegroup->no_of_shares =intval( $coreShareValue );
                        $sec_shareholder_sharegroup->company_id = $company_id;
                        $sec_shareholder_sharegroup->status = 1;
    
                        $sec_shareholder_sharegroup->save();
                        $sec_shareholder_sharegroupID = $sec_shareholder_sharegroup->id;
    
                        //add to share table
                        $sec_shareholder_share = new Share;
                        if( $sec['secType'] == 'firm'){
                            $sec_shareholder_share->company_firm_id = $newSecShareHolderID;
                        }else {
                            $sec_shareholder_share->company_member_id = $newSecShareHolderID;
                        }
                        $sec_shareholder_share->group_id = $sec_shareholder_sharegroupID;
                        $sec_shareholder_share->save();
                    }
               }
    
             } //end if sesc is a shareholder

            if( $sec['secType'] == 'firm' ) {

               // print_r($sec);

                if(isset($sec['id']) && $sec['id'] ){

                    $cf = CompanyFirms::find($sec['id']);
                    $sec_as_sh_count =  ( isset($cf->sh_firm_of) &&  intval( $cf->sh_firm_of ) > 0 )  ? 1 : 0 ;
                } else {
                    $sec_as_sh_count = 0;
                }

                $updateSec->email  = $sec['firm_email'];
                $updateSec->mobile = $sec['firm_mobile'];
                $updateSec->phone  = $sec['firm_phone'];
                $updateSec->date_of_appointment = date('Y-m-d',strtotime($sec['firm_date']) );
                $updateSec->company_id = $company_id;
                $updateSec->address_id = $companyFirmAddressId;
                $updateSec->type_id = $this->settings('SECRETARY','key')->id;
                $updateSec->status = 1;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';

                

                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                    $updateSec->registration_no = $sec['pvNumber'];
                    $updateSec->name = $sec['firm_name'];
                }
                

                if($sec_as_sh_count == 0) {
                    $updateSec->sh_firm_of = ( $newSecShareHolderID ) ? $newSecShareHolderID : null;;
                }
                $updateSec->save();
              
            }else {
                $updateSec->company_id = $company_id;
                $updateSec->designation_type = $this->settings('SECRETARY','key')->id;
                $updateSec->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
            // $updateSec->title = $sec['title'];
                
                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  (  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                if(!$process_status_val){
                 //   $updateSec->title = $sec['title'];
                  //  $updateSec->first_name = $sec['firstname'];
                  //  $updateSec->last_name = $sec['lastname']; 
                    $updateSec->nic = isset( $sec['nic'] ) ? strtoupper($sec['nic']) : null;
                    $updateSec->passport_no = isset($sec['passport']) ? $sec['passport'] : null;
                }
                $updateSec->first_name = $sec['firstname'];
                $updateSec->last_name = $sec['lastname']; 
                
                $updateSec->address_id = $addressId;
                $updateSec->foreign_address_id = $forAddressId;
                $updateSec->passport_issued_country = isset( $sec['passport_issued_country'] ) ? $sec['passport_issued_country']  : null ;
                $updateSec->telephone = $sec['phone'];
                $updateSec->mobile =$sec['mobile'];
                $updateSec->email = $sec['email'];
            // $updateSec->foreign_address_id =($sec['type'] !='local') ? $addressId: 0;
                $updateSec->occupation = $sec['occupation'];
            //  $updateSec->no_of_shares =0;
                $updateSec->date_of_appointment = date('Y-m-d',strtotime($sec['date']) );
                $updateSec->is_registered_secretary = ($sec['isReg'] == true ) ? 'yes' : 'no';
                $updateSec->secretary_registration_no =  (isset($sec['regDate']) && $sec['regDate'] ) ? $sec['regDate'] : NULL;
                $updateSec->status = 1;
                $updateSec->is_natural_person = $sec['secType'] =='natural' ? 'yes' : 'no';
              //  $updateSec->company_member_firm_id = $companyFirmId;

                $updateSec->save();


                 //add to peoples table
                $check_people = People::where('nic', $sec['nic'] )->count();
                if($check_people == 0 ){

                    $people = new People;
                    $people->is_srilankan =  $sec['type'] != 'local' ?  'no' : 'yes';
                    $people->title = $this->settings('TITLE_MR','key')->id;
                    $people->first_name = $sec['firstname'];
                    $people->last_name =$sec['lastname'];
                    $people->address_id = $addressId;
                    $people->nic = strtoupper($sec['nic']);

                    $people->passport_issued_country ='Sri Lanka';
                    $people->telephone =$sec['phone'];
                    $people->mobile =$sec['mobile'];
                    $people->email =$sec['email'];
                    $people->occupation =$sec['occupation'];
                    $people->sex ='male';
                    $people->status =1;
                    $people->save();

                }
            }

            
        }
        

        //loop through add shareholder list
        foreach($request->shareholders['shs'] as $shareholder ){

            $address = new Address;
            $forAddress = new Address;

            $addressId= null;
            $forAddressId = null;
    

            if( $shareholder['shareholderType'] === 'natural' ){
                if( $shareholder['province'] || $shareholder['district'] || $shareholder['city'] || $shareholder['localAddress1'] || $shareholder['localAddress2'] || $shareholder['postcode'] ) {
                    $address->province = $shareholder['province'];
                    $address->district =  ($shareholder['type'] == 'local') ? $shareholder['district'] : null;
                    $address->city =  $shareholder['city'];
                    $address->address1 =  $shareholder['localAddress1'];
                    $address->address2 =  $shareholder['localAddress2'];
                    $address->postcode =  $shareholder['postcode'];
                    $address->country =  'Sri Lanka';
                }
                
            } else {

                $address->province = $shareholder['firm_province'];
                $address->district =  ( $shareholder['type'] == 'local') ? $shareholder['firm_district'] : '' ;
                $address->city =  $shareholder['firm_city'];
                $address->address1 =  $shareholder['firm_localAddress1'];
                $address->address2 =  $shareholder['firm_localAddress2'];
                $address->postcode = $shareholder['firm_postcode'];
                $address->country = $shareholder['country'];

            }
            $address->save();
            $addressId = $address->id;

            if( $shareholder['shareholderType'] === 'natural' ){

                    if( @$shareholder['forProvince'] || @$shareholder['forCity'] || @$shareholder['forAddress1'] || @$shareholder['forAddress2'] || @$shareholder['forPostcode']) {
                        $forAddress->province = @$shareholder['forProvince'];
                        $forAddress->city =  @$shareholder['forCity'];
                        $forAddress->address1 =  @$shareholder['forAddress1'];
                        $forAddress->address2 =  @$shareholder['forAddress2'];
                        $forAddress->postcode =  @$shareholder['forPostcode'];
                        $forAddress->country =   $shareholder['country'];
                        $forAddress->save();
                        $forAddressId = $forAddress->id;
                    }
            }

            if ( $shareholder['shareholderType'] === 'natural' ) {

                if(isset($shareholder['id']) && $shareholder['id'] ){
                    $updateSh = CompanyMember::find($shareholder['id']);
                }else{
                    $updateSh = new CompanyMember;
                }
                $updateSh->company_id = $company_id;
                $updateSh->designation_type = $this->settings('SHAREHOLDER','key')->id;
                $updateSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                
                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  ( $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
               
                $updateSh->title = $shareholder['title'];
                $updateSh->first_name = $shareholder['firstname'];
                $updateSh->last_name = $shareholder['lastname'];
                $updateSh->nic = strtoupper($shareholder['nic']);
                $updateSh->passport_no = $shareholder['passport'];
                $updateSh->address_id = $addressId;
                $updateSh->foreign_address_id = $forAddressId;
                $updateSh->passport_issued_country = isset($shareholder['passport_issued_country']) ?  $shareholder['passport_issued_country'] : $shareholder['country'];
                $updateSh->telephone = $shareholder['phone'];
                $updateSh->mobile =$shareholder['mobile'];
                $updateSh->email = $shareholder['email'];
                $updateSh->occupation = $shareholder['occupation'];
                $updateSh->date_of_appointment = date('Y-m-d',strtotime($shareholder['date']) );
                $updateSh->status = 1;
                $updateSh->save();

                $shareHolderId = ( isset($shareholder['id']) && $shareholder['id'] ) ? $shareholder['id'] : $updateSh->id;

                  //add to peoples table
             if( $shareholder['nic'] ){
                $check_people = People::where('nic', $shareholder['nic'] )->count();
                if($check_people == 0 ){

                    $people = new People;
                    $people->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                    $people->title = $this->settings('TITLE_MR','key')->id;
                    $people->first_name = $shareholder['firstname'];
                    $people->last_name =$shareholder['lastname'];
                    $people->address_id = $addressId;
                    $people->nic = strtoupper($shareholder['nic']);
                    $people->passport_issued_country ='Sri Lanka';
                    $people->telephone =$shareholder['phone'];
                    $people->mobile =$shareholder['mobile'];
                    $people->email =$shareholder['email'];
                    $people->occupation =$shareholder['occupation'];
                    $people->sex ='male';
                    $people->status =1;
                    $people->save();

                }

            }

            //add to peoples table
            if( $shareholder['passport'] ){
                $check_people = People::where('passport_no', $shareholder['passport'] )->count();
                if($check_people == 0 ){

                    $people = new People;
                    $people->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                    $people->title = $this->settings('TITLE_MR','key')->id;
                    $people->first_name = $shareholder['firstname'];
                    $people->last_name =$shareholder['lastname'];
                    $people->address_id = $addressId;
                    $people->passport_no = strtoupper($shareholder['passport']);
                 //   $people->passport_issued_country =$shareholder['country'];
                    $people->passport_issued_country = isset($shareholder['passport_issued_country']) ?  $shareholder['passport_issued_country'] : $shareholder['country'];
                    $people->telephone =$shareholder['phone'];
                    $people->mobile =$shareholder['mobile'];
                    $people->email =$shareholder['email'];
                    $people->occupation =$shareholder['occupation'];
                    $people->sex ='male';
                    $people->status =1;
                    $people->save();

                }

            }
            
            } else {

                if(isset($shareholder['id']) && $shareholder['id'] ){
                    $updateSh = CompanyFirms::find($shareholder['id']);
                }else{
                    $updateSh = new CompanyFirms;
                }

                $company_info = Company::where('id',$company_id)->first(); 
                $process_status = $this->settings($company_info->status,'id')->key;
                $process_status_val =  ( $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                $process_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2' );
                //if(!$process_status_val){
                    $updateSh->registration_no = $shareholder['pvNumber'];
                    $updateSh->name = $shareholder['firm_name'];
                    
                //}
                
                $updateSh->email = $shareholder['firm_email'];
                $updateSh->mobile = $shareholder['firm_mobile'];
                $updateSh->date_of_appointment =  date('Y-m-d',strtotime($shareholder['firm_date']) );
                $updateSh->company_id = $company_id;
                $updateSh->address_id = $addressId;
                $updateSh->type_id = $this->settings('SHAREHOLDER','key')->id;
                $updateSh->is_srilankan =  $shareholder['type'] != 'local' ?  'no' : 'yes';
                $updateSh->status = 1;
                $updateSh->save();


                $shareHolderId = ( isset($shareholder['id']) && $shareholder['id'] ) ? $shareholder['id'] : $updateSh->id;

                if( isset($shareholder['benifiList']['ben'])  &&  is_array($shareholder['benifiList']['ben'])) {

                    //first remove all records of benif
                    CompanyMember::where('company_id', $company_id)
                                  ->where('company_member_firm_id', $shareHolderId)
                                  ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
                                  ->where('is_beneficial_owner', 'yes')
                                  ->delete();

                    foreach(  $shareholder['benifiList']['ben'] as $ben ) {

                        $benAddress = new Address;
                        $benAddress->province = $ben['province'];
                        $benAddress->district =  ($ben['type'] == 'local' ) ? $ben['district'] : null;
                        $benAddress->city =  $ben['city'];
                        $benAddress->address1 =  $ben['localAddress1'];
                        $benAddress->address2 =  $ben['localAddress2'];
                        $benAddress->postcode = $ben['postcode'];
                        $benAddress->country =  ($ben['type'] == 'local' ) ? 'Sri Lanka' : $ben['country'];
        
                        $benAddress->save();
                        $benAddress_id = $benAddress->id;

                        $benuUser = new CompanyMember;
                        $benuUser->company_id = $company_id;
                        $benuUser->designation_type = $this->settings('SHAREHOLDER','key')->id;
                        $benuUser->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
                        $benuUser->title = $ben['title'];
                        $benuUser->first_name = $ben['firstname'];
                        $benuUser->last_name = $ben['lastname'];
                        $benuUser->address_id = $benAddress_id;
                        $benuUser->nic = ( $ben['type'] == 'local' ) ? strtoupper($ben['nic']) : null;
                        $benuUser->passport_no = ( $ben['type'] == 'local' ) ? null : $ben['passport'];
                        $benuUser->passport_issued_country = ( $ben['type'] == 'local' )  ? null : $ben['country'];
                        $benuUser->telephone = $ben['phone'];
                        $benuUser->mobile =$ben['mobile'];
                        $benuUser->email = $ben['email'];
                        $benuUser->is_beneficial_owner = 'yes';
                        $benuUser->company_member_firm_id = $shareHolderId;
                    
                        $benuUser->occupation = $ben['occupation'];
                        $benuUser->date_of_appointment = date('Y-m-d',strtotime($ben['date']) );
                        $benuUser->status = 1;

                        $benuUser->save();
                        $benUserId = $benuUser->id;

                        //add to peoples table
                        if( $ben['nic'] ){
                            $check_people = People::where('nic', $ben['nic'] )->count();
                            if($check_people == 0 ){

                                $people = new People;
                                $people->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
                                $people->title = $this->settings('TITLE_MR','key')->id;
                                $people->first_name = $ben['firstname'];
                                $people->last_name =$ben['lastname'];
                                $people->address_id = $benAddress_id;
                                $people->nic = strtoupper($ben['nic']);

                                $people->passport_issued_country ='Sri Lanka';
                                $people->telephone =$ben['phone'];
                                $people->mobile =$ben['mobile'];
                                $people->email =$ben['email'];
                                $people->occupation =$ben['occupation'];
                                $people->sex ='male';
                                $people->status =1;
                                $people->save();

                            }

                        }

                        //add to peoples table
                        if( $ben['passport'] ){
                            $check_people = People::where('passport_no', $ben['passport'] )->count();
                            if($check_people == 0 ){

                                $people = new People;
                                $people->is_srilankan =  $ben['type'] != 'local' ?  'no' : 'yes';
                                $people->title = $this->settings('TITLE_MR','key')->id;
                                $people->first_name = $ben['firstname'];
                                $people->last_name =$ben['lastname'];
                                $people->address_id = $benAddress_id;
                                $people->passport_no = strtoupper($ben['passport']);
                                $people->passport_issued_country =$ben['country'];
                                $people->telephone =$ben['phone'];
                                $people->mobile =$ben['mobile'];
                                $people->email =$ben['email'];
                                $people->occupation =$ben['occupation'];
                                $people->sex ='male';
                                $people->status =1;
                                $people->save();

                            }

                        }


                      }
 
                    }
             
            }

          //  $shareHolderId = ( isset($shareholder['id']) && $shareholder['id'] ) ? $shareholder['id'] : $updateSh->id;

            if(  $shareholder['shareType'] == 'single' && intval($shareholder['noOfShares']) ) {

                if(isset($shareholder['id']) && $shareholder['id'] ){

                   /* if($shareholder['shareholderType']  == 'natural'){
                        $shareRow = Share::where('company_member_id', $shareholder['id'] )->first();
                    }else{
                        $shareRow = Share::where('company_firm_id', $shareholder['id'] )->first();
                    }

                    $shareholder_share = Share::find($shareRow['id']);*/

                    if($shareholder['shareholderType']  == 'natural'){
                       Share::where('company_member_id', $shareholder['id'] )->delete();
                    }else{
                        Share::where('company_firm_id', $shareholder['id'] )->delete();
                    }
                    $shareholder_share = new Share;

                   
                   
                }else{
                    $shareholder_share = new Share;
                }

                if(isset($shareholder['id']) && $shareholder['id']  ){
                      //  $shareholder_sharegroup = ShareGroup::find($shareRow['group_id']);

                       $shareholder_sharegroup = new ShareGroup;

                        $shareholder_sharegroup->type ='single_share';
                        $shareholder_sharegroup->name ='single_share_no_name';
                        $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfShares'] );
                        $shareholder_sharegroup->company_id = $company_id;
                        $shareholder_sharegroup->status = 1;
    
                        $shareholder_sharegroup->save();
                        $shareholder_sharegroupID =  $shareholder_sharegroup->id;

                  

                 
                }else{

                    $shareholder_sharegroup = new ShareGroup;
                    $shareholder_sharegroup->type ='single_share';
                    $shareholder_sharegroup->name ='single_share_no_name';
                    $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfShares'] );
                    $shareholder_sharegroup->company_id = $company_id;
                    $shareholder_sharegroup->status = 1;
                    $shareholder_sharegroup->save();
                    $shareholder_sharegroupID = $shareholder_sharegroup->id;
                }
  
                //add to share table
                
                if ( $shareholder['shareholderType']  == 'natural' ) {
                  $shareholder_share->company_member_id = $shareHolderId;
                }else{
                    
                  $shareholder_share->company_firm_id = $shareHolderId;
                }
                $shareholder_share->group_id = $shareholder_sharegroupID;
                $shareholder_share->save();
            }

            if($shareholder['shareType'] == 'core' && isset($shareholder['coreGroupSelected']) &&  intval( $shareholder['coreGroupSelected']) ){

                if(isset($shareholder['id']) && $shareholder['id'] ){


                    if ( $shareholder['shareholderType']  == 'natural' ) {
                        $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                        $singleGroups = array();
                        if($companyGroupsCount) {
                            $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                            foreach($companyGroups as $g ){
                                $singleGroups[] = $g['id'];
                            }
        
                            Share::whereIn('group_id', $singleGroups )->where('company_member_id', $shareHolderId )->delete();
                        }
        
                      }else{
        
                        $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                        $singleGroups = array();
                        if($companyGroupsCount) {
                            $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                            foreach($companyGroups as $g ){
                                $singleGroups[] = $g['id'];
                            }
        
                            Share::whereIn('group_id', $singleGroups )->where('company_firm_id', $shareHolderId )->delete();
                        }
                      }




                    if ( $shareholder['shareholderType']  == 'natural' ) {

                        $shareRow = Share::where('company_member_id', $shareholder['id'] )->first();
                    }else {
                        $shareRow = Share::where('company_firm_id', $shareholder['id'] )->first();
                    }

                    $shareholder_share = Share::find($shareRow['id']);
                }else{
                    $shareholder_share = new Share;
                }

                if(isset($shareholder['id']) && $shareholder['id'] ){
                    $shareholder_sharegroup = ShareGroup::find($shareRow['group_id']);
                }else{
                    $shareholder_sharegroup = new ShareGroup;
                }
                //add to share table
               
                if ( $shareholder['shareholderType']  == 'natural' ) {
                   $shareholder_share->company_member_id = $shareHolderId;
                }else{
                    $shareholder_share->company_firm_id = $shareHolderId;
                }
                $shareholder_share->group_id =intval( $shareholder['coreGroupSelected']);
                $shareholder_share->save();
            }

            if(
              $shareholder['shareType'] == 'core' &&
               ( empty( $shareholder['coreGroupSelected'])  ||  !intval( $shareholder['coreGroupSelected']) )  &&
                isset( $shareholder['coreShareGroupName']) && 
                $shareholder['coreShareGroupName'] && 
              intval($shareholder['noOfSharesGroup']) ) {

              //  die('come here');

              if ( $shareholder['shareholderType']  == 'natural' ) {
            
                $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                $singleGroups = array();
                if($companyGroupsCount) {
                    $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                    foreach($companyGroups as $g ){
                        $singleGroups[] = $g['id'];
                    }

                    Share::whereIn('group_id', $singleGroups )->where('company_member_id', $shareHolderId )->delete();
                }

              }else{

                $companyGroupsCount = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->count();
                $singleGroups = array();
                if($companyGroupsCount) {
                    $companyGroups = ShareGroup::where('company_id', $shareHolderId )->where('type','single_share')->get();
                    foreach($companyGroups as $g ){
                        $singleGroups[] = $g['id'];
                    }

                    Share::whereIn('group_id', $singleGroups )->where('company_firm_id', $shareHolderId )->delete();
                }
              }
               


                //add to single share group
                $shareholder_sharegroup = new ShareGroup;
                $shareholder_sharegroup->type ='core_share';
                $shareholder_sharegroup->name = $shareholder['coreShareGroupName'];
                $shareholder_sharegroup->no_of_shares =intval( $shareholder['noOfSharesGroup'] );
                $shareholder_sharegroup->company_id = $company_id;
                $shareholder_sharegroup->status = 1;

                $shareholder_sharegroup->save();
                $shareholder_sharegroupID = $shareholder_sharegroup->id;

                //add to share table
                $shareholder_share = new Share;
                if ( $shareholder['shareholderType']  == 'natural' ) {
                    $shareholder_share->company_member_id = $shareHolderId;
                  }else{
                    $shareholder_share->company_firm_id = $shareHolderId;
                  }
                $shareholder_share->group_id = $shareholder_sharegroupID;
                $shareholder_share->save();
            }
            
        }
    
        return response()->json([
            'message' => 'Successfully submitted stakeholders',
            'status' =>true,
            'data'   => array(
                 'docList' => @$this->generate_files($companyType->key,$request->companyId,$loginUserEmail),
                 'uploadList' => $this->files_for_upload($companyType->key,$request->companyId),
                 'uploadedList' => $this->uploadedDocs($request->companyId),
                
            )
        ], 200);
    }


    function checkNic(Request $request ){

       //  $nic = strtoupper($request->nic);
         $company_id = $request->companyId;
         $company_info = Company::where('id',$request->companyId)->first();

         $companyType = $this->settings($company_info->type_id,'id')->key;

       
         $member_type = $request->memberType; // 1- director, 2- secrotory , 3 - shareholder

         if($member_type == 1 ) {
             $member_type = $this->settings('DERECTOR','key')->id;
         }
         if($member_type == 2 ){
              $member_type = $this->settings('SECRETARY','key')->id;
         }
         if($member_type == 3 ){
            $member_type = $this->settings('SHAREHOLDER','key')->id;
         }

        //$members =CompanyMember::where('company_id','!=', $company_id)
                              ///  ->where('nic', $nic)
                              //  ->where('designation_type', $member_type )
                              //  ->get();

                           

        $members_nic_lower =People::where('nic', strtolower($request->nic))->first();
        $members_nic_lowercount = People::where('nic', strtolower($request->nic))->count();

        $members_nic_upper =People::where('nic', strtoupper($request->nic))->first();
        $members_nic_uppercount = People::where('nic',strtoupper($request->nic))->count();


        $members = ($members_nic_lowercount ) ? $members_nic_lower : $members_nic_upper;
        $members_count = ($members_nic_lowercount) ? $members_nic_lowercount : $members_nic_uppercount;
     
         $sec_reg_no = '';

         if( $request->memberType == 1 || $request->memberType == 2) {

            $members_sec_nic_lower =Secretary::where('nic', strtolower($request->nic))->first();
            $members_sec_nic_lowercount = Secretary::where('nic', strtolower($request->nic))->count();
    
            $members_sec_nic_upper = Secretary::where('nic', strtoupper($request->nic))->first();
            $members_sec_nic_uppercount = Secretary::where('nic',strtoupper($request->nic))->count();
    
            $members_sec = ($members_sec_nic_lowercount ) ? $members_sec_nic_lower : $members_sec_nic_upper; 

            if(isset($members_sec->id)) {
                
                $sec_sertificate_count = SecretaryCertificate::where('secretary_id', $members_sec->id )->count();
                if($sec_sertificate_count) {
                    $sec_sertificate = SecretaryCertificate::where('secretary_id', $members_sec->id )->first();
                    $sec_reg_no = isset($sec_sertificate->certificate_no) && $sec_sertificate->certificate_no  ? $sec_sertificate->certificate_no : '';
                }else {
                    $sec_reg_no = '';
                }

            }

           // $sec_reg_no = isset($members_sec->certificate_no) && $members_sec->certificate_no  ? $members_sec->certificate_no : '';
         }

         if($members_count >= 1 ){

            $address = Address::where( 'id',$members->address_id )->get()->first();
            
            return response()->json([
                'message' => 'Director record exists under this NIC.',
                'status' =>true,
                'data'   => array(
                    // 'member_count' =>$members_count,
                     'member_count' =>1,
                      'member_record'      => array($members),
                      'address_record'     => $address,
                      'sec_reg_no'         => $sec_reg_no,
                      'openLocalAddress'    => $this->localAddressOpenStatus($address),
                      'title'              => isset($this->settings($members->title,'id')->value) && $this->settings($members->title,'id')->value ? $this->settings($members->title,'id')->value : NULL

                      
                )
            ], 200);
         }else{
            return response()->json([
                'message' => 'No record found under this NIC',
                'status' =>true,
                'data'   => array(
                     'member_count' =>0,
                     'sec_reg_no'         => $sec_reg_no,
                     'openLocalAddress' => true,
                     'title' => null
                )
            ], 200);
         }


    }
    private function localAddressOpenStatus($address){

        if(!isset($address->province) || !$address->province ){
            return true;
        }else{
            $province_count = Province::where('description_en', $address->province )->count();
            if(!$province_count){
                return true;
            }
        }

        if(!isset($address->district) || !$address->district ){
            return true;
        }else{
            $district_count = District::where('description_en', $address->district )->count();
            if(!$district_count){
                return true;
            }
        }

        if(!isset($address->city) || !$address->city ){
            return true;
        }else{
            $city_count = City::where('description_en', $address->city )->count();
            if(!$city_count){
                return true;
            }
        }

        return false;


    }

    function getDocs($doc_type, $companyId= null){
        $docs = $this->documents(false, $companyId);
        return isset(  $docs[$doc_type] ) ?   $docs[$doc_type]  : false;
    }

    function documents($is_resubmission=false, $companyId= null){

        $docs = array();
        $private_public_unlimited = array('COMPANY_TYPE_PRIVATE', 'COMPANY_TYPE_PUBLIC','COMPANY_TYPE_UNLIMITED');

        foreach($private_public_unlimited as $type ){

            $docs[$type] = array(
                'download' => array(
                    array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                ),
                'upload'   => array()
            );
    
            $type_id = $this->settings($type,'key')->id;
    
            $group = DocumentsGroup::where('company_type', $type_id)
                                            ->where('request_type', 'COM_REG')
                                            ->first();
            $group_id = @$group->id;

            if($is_resubmission){
                $uploads = Documents::where('document_group_id', $group_id)
                ->where('status', 1)
                ->get();
            }else{
                
                $uploads = Documents::where('document_group_id', $group_id)
                ->where('status', 1)
                ->get();
            }
    
            $upload_arr = array();
    
            foreach($uploads as $d ){

                $upload_arr[] = $d->id;
                  
                $rec = array(
                    'dbid' => $d->id,
                   'name' => $d->name,
                   'savedLocation' => '',
                   'required' => ($d->is_required == 'yes' ) ? true : false,
                   'specific' => $d->specific_company_member_type,
                   'type'   =>$this->slugify($d->name),
                   'fee'    => $d->fee,
                   'key' => $d->key,
                  // 'fee'    =>mt_rand(2000, 5000),
                   'uploaded_path' => '',
                   'comments' =>'',
                   'description' => $d->description,
                   'issue_certified_copy' => $d->issue_certified_copy,
                   'doc_requested' => 'no',
                   'admin_set'  => 'no',

                );
                $docs[$type]['upload'][] = $rec;
            }
            
            if($companyId) {

               
                //   echo ('echo::' .$companyId);
                   $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
                   ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                   ->whereNotIn('document_id', $upload_arr)
                   ->count();
                  // print_r( $additional_docs_count);
   
                   if( $additional_docs_count) {
                     
   
                       $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                       ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                       ->whereNotIn('document_id', $upload_arr)
                                                       ->get();
                                                     //  print_r( $additional_docs);   
                       foreach($additional_docs as $adoc) {
   
                           $doc_id_in_list = $adoc->document_id;
                           $docInfo =  Documents::where('id', $doc_id_in_list)->first();
   
                           $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';
   
                        //   print_r(  $docInfo);
   
                           $rec = array(
                               'dbid' => $doc_id_in_list,
                              'name' => $docInfo->name,
                              'savedLocation' => '',
                              'required' => true,
                              'specific' => $docInfo->specific_company_member_type,
                              'type'   =>$this->slugify($docInfo->name),
                              'fee'    => $docInfo->fee,
                              'key' => $docInfo->key,
                              'uploaded_path' => '',
                              'comments' =>'',
                              'description' => $docInfo->description,
                              'issue_certified_copy' => $docInfo->issue_certified_copy,
                              'doc_requested' => $doc_requested,
                              'admin_set'  => 'yes'
               
                           );
                           $docs[$type]['upload'][] = $rec;
   
   
   
                       }
   
                   }
                   
               }

            

        }

        $guarantee = array( 'COMPANY_TYPE_GUARANTEE_34', 'COMPANY_TYPE_GUARANTEE_32');

        foreach($guarantee as $type ){
            $docs[$type] = array(
                'download' => array(
                    array('name' =>'FORM 05', 'savedLocation' => "", 'view'=>'form5', 'specific' =>'','file_name_key' =>'form05' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                    array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                ),
                'upload'   => array()
            );
    
            $type_id = $this->settings($type,'key')->id;
            $group = DocumentsGroup::where('company_type', $type_id)
                                            ->where('request_type', 'COM_REG')
                                            ->first();
            $group_id = @$group->id;
    
            $uploads = Documents::where('document_group_id', $group_id)
                                ->where('status', 1)
                                ->get();

            $upload_arr = array();
    
            foreach($uploads as $d ){

                $upload_arr[] = $d->id;
                  
                $rec = array(
                   'dbid' => $d->id,
                   'name' => $d->name,
                   'savedLocation' => '',
                   'required' => ($d->is_required == 'yes' ) ? true : false,
                   'specific' => $d->specific_company_member_type,
                   'type'   =>$this->slugify($d->name),
                   'fee'    => $d->fee,
                   'key' => $d->key,
                   'uploaded_path' => '',
                   'description' => $d->description,
                   'issue_certified_copy' => $d->issue_certified_copy,
                   'doc_requested' => 'no',
                    'admin_set'  => 'no'
    
                );
                $docs[$type]['upload'][] = $rec;
    
            }

            if($companyId) {

               
             //   echo ('echo::' .$companyId);
                $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
                ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                ->whereNotIn('document_id', $upload_arr)
                ->count();
               // print_r( $additional_docs_count);

                if( $additional_docs_count) {
                  

                    $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                    ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                    ->whereNotIn('document_id', $upload_arr)
                                                    ->get();
                                                  //  print_r( $additional_docs);   
                    foreach($additional_docs as $adoc) {

                        $doc_id_in_list = $adoc->document_id;
                        $docInfo =  Documents::where('id', $doc_id_in_list)->first();

                        $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';

                     //   print_r(  $docInfo);

                        $rec = array(
                            'dbid' => $doc_id_in_list,
                           'name' => $docInfo->name,
                           'savedLocation' => '',
                           'required' => true,
                           'specific' => $docInfo->specific_company_member_type,
                           'type'   =>$this->slugify($docInfo->name),
                           'fee'    => $docInfo->fee,
                           'key' => $docInfo->key,
                           'uploaded_path' => '',
                           'comments' =>'',
                           'description' => $docInfo->description,
                           'issue_certified_copy' => $docInfo->issue_certified_copy,
                           'doc_requested' => $doc_requested,
                           'admin_set'  => 'yes'
            
                        );
                        $docs[$type]['upload'][] = $rec;



                    }

                }
                
            }

         //   print_r( $docs[$type]['upload']);
         // die('come here');

        }

        //overseas
        $type = 'COMPANY_TYPE_OVERSEAS';
        $docs[$type] = array(

            'download' => array(
                array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
            ),
            'upload'   => array()

        );

        $type_id = $this->settings($type,'key')->id;

        $group = DocumentsGroup::where('company_type', $type_id)
                                        ->where('request_type', 'COM_REG')
                                        ->first();
        $group_id = @$group->id;

        $uploads = Documents::where('document_group_id', $group_id)
                            ->where('status', 1)
                            ->orderBy('sort', 'asc')
                            ->get();

        $upload_arr = array();
    
        foreach($uploads as $d ){
                
            $upload_arr[] = $d->id;
              
            $rec = array(
               'dbid' => $d->id,
               'name' => $d->name,
               'savedLocation' => '',
               'required' => ($d->is_required == 'yes' ) ? true : false,
               'specific' => $d->specific_company_member_type,
               'type'   =>$this->slugify($d->name),
               'fee'    => $d->fee,
               'key' => $d->key,
               'uploaded_path' => '',
               'description' => $d->description,
               'issue_certified_copy' => $d->issue_certified_copy,
               'doc_requested' => 'no',
               'admin_set'  => 'no'

            );
        $docs[$type]['upload'][] = $rec;
        }

        if($companyId) {

               
            //   echo ('echo::' .$companyId);
               $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
               ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
               ->whereNotIn('document_id', $upload_arr)
               ->count();
              // print_r( $additional_docs_count);

               if( $additional_docs_count) {
                 

                   $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                   ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                   ->whereNotIn('document_id', $upload_arr)
                                                   ->get();
                                                 //  print_r( $additional_docs);   
                   foreach($additional_docs as $adoc) {

                       $doc_id_in_list = $adoc->document_id;
                       $docInfo =  Documents::where('id', $doc_id_in_list)->first();

                       $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';

                    //   print_r(  $docInfo);

                       $rec = array(
                           'dbid' => $doc_id_in_list,
                          'name' => $docInfo->name,
                          'savedLocation' => '',
                          'required' => true,
                          'specific' => $docInfo->specific_company_member_type,
                          'type'   =>$this->slugify($docInfo->name),
                          'fee'    => $docInfo->fee,
                          'key' => $docInfo->key,
                          'uploaded_path' => '',
                          'comments' =>'',
                          'description' => $docInfo->description,
                          'issue_certified_copy' => $docInfo->issue_certified_copy,
                          'doc_requested' => $doc_requested,
                          'admin_set'  => 'yes'
           
                       );
                       $docs[$type]['upload'][] = $rec;



                   }

               }
               
           }

        //offshore
        $type = 'COMPANY_TYPE_OFFSHORE';
        $docs[$type] = array(

            'download' => array(
                array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                       array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                       array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
            ),
            'upload'   => array()

        );

        $type_id = $this->settings($type,'key')->id;

        $group = DocumentsGroup::where('company_type', $type_id)
                                        ->where('request_type', 'COM_REG')
                                        ->first();
        $group_id = @$group->id;

        $uploads = Documents::where('document_group_id', $group_id)
                            ->where('status', 1)
                            ->orderBy('sort', 'asc')
                            ->get();

        $upload_arr = array();
    
        foreach($uploads as $d ){
                
            $upload_arr[] = $d->id;
              
            $rec = array(
               'dbid' => $d->id,
               'name' => $d->name,
               'savedLocation' => '',
               'required' => ($d->is_required == 'yes' ) ? true : false,
               'specific' => $d->specific_company_member_type,
               'type'   =>$this->slugify($d->name),
               'fee'    => $d->fee,
               'key' => $d->key,
               'uploaded_path' => '',
               'description' => $d->description,
               'issue_certified_copy' => $d->issue_certified_copy

            );
        $docs[$type]['upload'][] = $rec;
        
        }
        if($companyId) {

               
            //   echo ('echo::' .$companyId);
               $additional_docs_count = CompanyDocuments::where('company_id', $companyId)
               ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
               ->whereNotIn('document_id', $upload_arr)
               ->count();
              // print_r( $additional_docs_count);

               if( $additional_docs_count) {
                 

                   $additional_docs = CompanyDocuments::where('company_id', $companyId)
                                                   ->whereIn('status', array( $this->settings('DOCUMENT_REQUESTED','key')->id ,  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,  $this->settings('DOCUMENT_PENDING','key')->id) )
                                                   ->whereNotIn('document_id', $upload_arr)
                                                   ->get();
                                                 //  print_r( $additional_docs);   
                   foreach($additional_docs as $adoc) {

                       $doc_id_in_list = $adoc->document_id;
                       $docInfo =  Documents::where('id', $doc_id_in_list)->first();

                       $doc_requested =  $this->settings('DOCUMENT_REQUESTED','key')->id  !=  $adoc->status ? 'no' : 'yes';

                    //   print_r(  $docInfo);

                       $rec = array(
                           'dbid' => $doc_id_in_list,
                          'name' => $docInfo->name,
                          'savedLocation' => '',
                          'required' => true,
                          'specific' => $docInfo->specific_company_member_type,
                          'type'   =>$this->slugify($docInfo->name),
                          'fee'    => $docInfo->fee,
                          'uploaded_path' => '',
                          'key' => $docInfo->key,
                          'comments' =>'',
                          'description' => $docInfo->description,
                          'issue_certified_copy' => $docInfo->issue_certified_copy,
                          'doc_requested' => $doc_requested,
                          'admin_set'  => 'yes'
           
                       );
                       $docs[$type]['upload'][] = $rec;



                   }

               }
               
           }
      
        
        return $docs;


}

function document_map_new($company_type, $dirList, $secList,$secFirms ){
      
    $form_map = array(
       'form_map_id' =>array(),
       'form_map_fee' => array(
       ),
    );

    $docs = $this->documents();
    foreach($docs as $doc_type=>$doc_val ){

       foreach($doc_val['upload'] as $doc ){

          if($doc_type == $company_type){

              $form_map['form_map_id'][$doc['type']] = $doc['dbid'];
              
              if($doc['specific'] == 'director' ){

                if(count($dirList)) {
                    $payment_key = 'PAYMENT_'.$doc['key'];
                    $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;
                  foreach($dirList as $dir ){
                    $form_map['form_map_fee']['director'][$dir['id']] = array(
                        'val' => floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                        'copies' => 0,
                        'original_val' => $payment_value,
                        'key'       => $payment_key,
                        'original_copies' => 1,
                        'for' => $doc['specific'],
                        'stakeholder_id' =>$dir['id'],
                        'issue_certified_copy' => $doc['issue_certified_copy'],
                        'doc_id' => $doc['dbid']

                   );
                      
                  }
                }

              }

              else if($doc['specific'] == 'sec' ){

                if(count($secList)) {
                    $payment_key = 'PAYMENT_'.$doc['key'];
                    $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;

                    foreach($secList as $sec ){

                        $form_map['form_map_fee']['sec'][$sec['id']] = array(
                             'val' => floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                            'copies' => 0,
                            'original_val' => $payment_value,
                            'key'       => $payment_key,
                            'original_copies' => 1,
                            'for' => $doc['specific'],
                            'stakeholder_id' => $sec['id'],
                            'issue_certified_copy' => $doc['issue_certified_copy'],
                            'doc_id' => $doc['dbid']
                       );
                          
      
                      }

                }

                if(count($secFirms)) {
                    $payment_key = 'PAYMENT_'.$doc['key'];
                    $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;

                    foreach($secFirms as $sec ){

                        $form_map['form_map_fee']['secFirm'][$sec['id']] = array(
                             'val' =>floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                            'copies' => 0,
                            'original_val' => $payment_value,
                            'key'       => $payment_key,
                            'original_copies' => 1,
                            'for' => $doc['specific'],
                            'stakeholder_id' => $sec['id'],
                            'issue_certified_copy' => $doc['issue_certified_copy'],
                            'doc_id' => $doc['dbid']
                       );    
      
                      }
                }
               
            }else {


               // if($doc['issue_certified_copy'] === 'yes'){
                    
                     $payment_key = 'PAYMENT_'.$doc['key'];
                    $payment_value = isset($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) ? floatval($this->settings('PAYMENT_'.$doc['key'] ,'key')->value) : 0;
                    $form_map['form_map_fee'][$doc['type']] = array(
                        //'val' => floatval($doc['fee']),
                         'val' => floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                       // 'val' => mt_rand(10,100),
                        'copies' => 0,
                        'original_val' =>$payment_value,
                        'key'       => $payment_key,
                         'original_copies' => 1,
                        'for' => $doc['specific'],
                        'issue_certified_copy' => $doc['issue_certified_copy'],
                        'doc_id' => $doc['dbid']
                  );
               // }
               
               

            }

           
          }

       }

        
    }

    return $form_map;
}

function document_map($company_type){
      
      $form_map = array(
         'form_map_id' =>array(),
         'form_map_fee' => array(
         ),
      );

      $docs = $this->documents();
      foreach($docs as $doc_type=>$doc_val ){

         foreach($doc_val['upload'] as $doc ){

            if($doc_type == $company_type){

                $form_map['form_map_id'][$doc['type']] = $doc['dbid'];
                
                $form_map['form_map_fee'][$doc['type']] = array(
                    //'val' => floatval($doc['fee']),
                     'val' => floatval($doc['fee']),
                   // 'val' => mt_rand(10,100),
                    'copies' => 1,
                    'for' => $doc['specific']
              );

            }

         }

      }

      return $form_map;
}

    function documents_old(){
             $docs = array( 
                'COMPANY_TYPE_PRIVATE' => array(

                    'download' =>array(

                           array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                           array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                           array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                    ),
                        'upload' =>array( 
                            array('dbid' =>'18',  'name' =>'FORM 01','required' => true,'specific'=> '', 'type' => 'FORM01','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )

                    ),

                    'COMPANY_TYPE_PUBLIC' => array(

                        'download' =>array(

                            array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18 ', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                     ),
                        'upload' =>array( 
                            array('dbid' =>'18',  'name' =>'FORM 01','required' => true,'specific'=> '', 'type' => 'FORM01','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )
    
    
                    ),
                    'COMPANY_TYPE_UNLIMITED' => array(

                        'download' =>array(

                            array('name' =>'FORM 01', 'savedLocation' => "", 'view'=>'form1', 'specific' =>'','file_name_key' =>'form01' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                         ),
                         'upload' =>array( 
                             array('dbid' =>'18',  'name' =>'FORM 01','required' => true,'specific'=> '', 'type' => 'FORM01','uploaded_path' =>'' ),
                             array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                             array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                             array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                         )
        
        
                    ),
                    'COMPANY_TYPE_GUARANTEE_32' => array(

                       'download' =>array(

                            array('name' =>'FORM 05', 'savedLocation' => "", 'view'=>'form5', 'specific' =>'','file_name_key' =>'form05' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                        ),
                        'upload' =>array( 
                            array('dbid' =>'19',  'name' =>'FORM 05','required' => true,'specific'=> '', 'type' => 'FORM05','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )

            
                    ),  
                    'COMPANY_TYPE_GUARANTEE_34' => array(
   
                        'download' =>array(

                            array('name' =>'FORM 05', 'savedLocation' => "", 'view'=>'form5', 'specific' =>'','file_name_key' =>'form05' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'savedLocation' => "", 'view' => 'form18', 'specific'=> 'director','file_name_key' =>'form18' ),
                            array('name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19', 'savedLocation'=>"", 'view' => 'form19', 'specific'=> 'sec','file_name_key' =>'form19' )
                        ),
                        'upload' =>array( 
                            array('dbid' =>'19',  'name' =>'FORM 05','required' => true,'specific'=> '', 'type' => 'FORM05','uploaded_path' =>'' ),
                            array('dbid' =>'16','name' =>'CONSENT AND CERTIFICATE OF DIRECTOR - FORM 18', 'required' => true, 'specific'=> 'director', 'type' => 'FORM18','uploaded_path' =>''),
                            array('dbid' =>'17','name' =>'CONSENT AND CERTIFICATE OF SECRETARY - FORM 19','required' => true, 'specific'=> 'sec', 'type' => 'FORM19','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Articles of the Association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )
            
                    ),  
                    'COMPANY_TYPE_OVERSEAS' => array(

                        'download' =>array(
                            array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                            array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                            array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
                        ),
                        'upload' =>array( 
                            array('dbid' =>'100',  'name' =>'FORM 44','required' => true,'specific'=> '', 'type' => 'FORM44','uploaded_path' =>'' ),
                            array('dbid' =>'101','name' =>'FORM 45', 'required' => true, 'specific'=> '', 'type' => 'FORM45','uploaded_path' =>''),
                            array('dbid' =>'102','name' =>'FORM 46','required' => true, 'specific'=> '', 'type' => 'FORM46','uploaded_path' =>'' ),
                            array('dbid' =>'22','name' =>'Recently certified articles of association', 'required' => true,'specific'=> '' ,'type' => 'FORMAASSOC','uploaded_path' =>''),
                        )

            
                    ),
                    'COMPANY_TYPE_OFFSHORE' => array(

                        'download' =>array(  
            
                            array('name' =>'FORM 44', 'savedLocation' => "", 'view'=>'form44', 'specific' =>'','file_name_key' =>'form44' ),
                            array('name' =>'FORM 45', 'savedLocation' => "", 'view' => 'form45', 'specific'=> '','file_name_key' =>'form45' ),
                            array('name' =>'FORM 46', 'savedLocation'=>"", 'view' => 'form46', 'specific'=> '','file_name_key' =>'form46' )
                        ),
                        'upload' =>array(
            
                            array('dbid' =>'100',  'name' =>'FORM 44','required' => true,'specific'=> '', 'type' => 'FORM44','uploaded_path' =>'' ),
                            array('dbid' =>'101','name' =>'FORM 45', 'required' => true, 'specific'=> '', 'type' => 'FORM45','uploaded_path' =>''),
                            array('dbid' =>'102','name' =>'FORM 46','required' => true, 'specific'=> '', 'type' => 'FORM46','uploaded_path' =>'' ),
                            array('dbid' =>'103','name' =>'Recently certified copy of Company Incorporation certificate','required' => true, 'specific'=> '', 'type' => 'RCCCIC','uploaded_path' =>'' ),
                            array('dbid' =>'104','name' =>'Recently certified memorandum of association Copy','required' => true, 'specific'=> '', 'type' => 'RCMAC','uploaded_path' =>'' ),
                            array('dbid' =>'105','name' =>'Registered power of attorney Confirmation letter','required' => true, 'specific'=> '', 'type' => 'RPACL','uploaded_path' =>'' ),
 
                               
                                    
                        )
            
            
                    ), 

             );

             return $docs;
    }


    /**************************generate downloadable files***********************************/
        private function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
        }


        private function stakeholder_store($company_id){

            $director_list = CompanyMember::where('company_id',$company_id)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('status',1)
                                       ->orderBy('id', 'ASC')
                                       ->get();
        $directors = array();
        if(count($director_list )){
            foreach($director_list as $director){

                $address ='';
                $forAddress = '';
                if( $director->address_id) {
                    $address = Address::where('id',$director->address_id)->first();
                }
                if( $director->foreign_address_id) {
                    $forAddress = Address::where('id', $director->foreign_address_id)->first();
                }

                $rec = array(
                    'id' => $director->id,
                    'type' => ($director->is_srilankan == 'yes') ? 'local' : 'foreign',
                    'title' =>  $director->title,
                    'firstname' => $director->first_name,
                    'lastname' => $director->last_name,
                    'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
                    'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
                    'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
                    'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
                    'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
                    'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
    
                    'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
                    'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
                    'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
                    'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
                    'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',
                    'nic'       => $director->nic,
                    'passport'  => $director->passport_no,
                    'country'  => ( $director->foreign_address_id)  ? $forAddress->country : $address->country,
                    'passport_issued_country' => ( $director->foreign_address_id)  ? $director->passport_issued_country : 'Sri Lanka',
                    //'share'     => $director->no_of_shares,
                    'date'      => $director->date_of_appointment,
                    'phone' => $director->telephone,
                    'mobile' => $director->mobile,
                    'email' => $director->email,
                    'occupation' => $director->occupation

                );
                $directors[] = $rec;
            }
        }

        /******secretory list *****/
        $sec_list = CompanyMember::where('company_id',$company_id)
        ->where('designation_type',$this->settings('SECRETARY','key')->id)
        ->where('status',1)
        ->orderBy('id', 'ASC')
        ->get();
        $secs = array();

        if(count($sec_list )){
            foreach($sec_list as $sec){

          //  $address_id =  ($sec->foreign_address_id ) ? $sec->foreign_address_id : $sec->address_id;
            
         //   if(!$sec->foreign_address_id){
          //      $address = Address::where('id',$address_id)->first();
         //   }else{
           //   $address = Address::where('id',$address_id)->first();
          //  }

            $address ='';
            $forAddress = '';
            if( $sec->address_id) {
                $address = Address::where('id',$sec->address_id)->first();
            }
            if( $sec->foreign_address_id) {
                $forAddress = Address::where('id', $sec->foreign_address_id)->first();
            }

            $rec = array(
                'id' => $sec->id,
                'type' => ($sec->is_srilankan == 'yes' ) ? 'local' : 'foreign',
                'title' =>  $sec->title,
            'firstname' => $sec->first_name,
            'lastname' => $sec->last_name,

            'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
            'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
            'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
            'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
            'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
            'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',
    
            'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
            'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
            'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
            'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
            'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',

            'nic'       => $sec->nic,
            'passport'  => $sec->passport_no,
            'country'   => $sec->passport_issued_country,
            'country'  => ( $sec->foreign_address_id)  ? $forAddress->country : $address->country,
            'passport_issued_country' => ( $sec->foreign_address_id)  ? $sec->passport_issued_country : 'Sri Lanka',
            //'share'     =>0,
            'date'      => $sec->date_of_appointment,
            'isReg'        => ($sec->is_registered_secretary =='yes') ? true :false,
            'regDate'      => ($sec->is_registered_secretary =='yes') ? $sec->secretary_registration_no :'',
            'phone' => $sec->telephone,
            'mobile' => $sec->mobile,
            'email' => $sec->email,
            'occupation' => $sec->occupation

            );
            $secs[] = $rec;
            }

        }

         /******secretory firm list *****/
         $sec_list = CompanyFirms::where('company_id',$company_id)
         ->where('type_id',$this->settings('SECRETARY','key')->id)
         ->where('status',1)
         ->orderBy('id', 'ASC')
         ->get();

        
         $secFirms = array();

         if(count($sec_list )){
            foreach($sec_list as $sec){
    
            $address_id =  $sec->address_id;
            
            $address = Address::where('id',$address_id)->first();

            $rec = array(
                'id' => $sec->id,
                'type' => ($sec->is_srilankan == 'yes' ) ? 'local' : 'foreign',
                'title' =>  $sec->name,
            'registration_no' => $sec->registration_no,
            //'lastname' => $sec->last_name,
    
            'province' =>  ( $address->province) ? $address->province : '',
            'district' =>  ($address->district) ? $address->district : '',
            'city' =>  ( $address->city) ? $address->city : '',
            'localAddress1' => ($address->address1) ? $address->address1 : '',
            'localAddress2' => ($address->address2) ? $address->address2 : '',
            'postcode' => ($address->postcode) ? $address->postcode : '',
            'phone' => $sec->phone,
            'mobile' => $sec->mobile,
            'email' => $sec->email,
            'date'      => $sec->date_of_appointment,
        
            );
            $secFirms[] = $rec;
            }

        }

        $sh_core_groups = array();
        $single_share_total = 0;

        /******share holder list *****/
        $shareholder_list = CompanyMember::where('company_id',$company_id)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->whereNull('company_member_firm_id' )
        ->where('status',1)
        ->orderBy('id', 'ASC')
        ->get();
        $shareholders = array();

        if(count($shareholder_list )){

            foreach($shareholder_list as $shareholder){

                $address ='';
                $forAddress = '';
                if( $shareholder->address_id) {
                   $address = Address::where('id',$shareholder->address_id)->first();
                }
                if( $shareholder->foreign_address_id) {
                   $forAddress = Address::where('id', $shareholder->foreign_address_id)->first();
                }
        
            $shareRec = array(
                'value' => 0,
                'type' => ''
            );
            $shareRow = Share::where('company_member_id', $shareholder->id)->first();
            if(isset($shareRow->id) ){
                    $shareGroup = ShareGroup::where('id', $shareRow->group_id)->first();
                    
                    $shareRec['value'] = $shareGroup['no_of_shares'];
                    $shareRec['type'] = ($shareGroup['type'] == 'core_share') ? 'core share': 'single share';
                    $shareRec['id'] = $shareGroup['id'];

            }

            if(isset($shareRow->id) ){

                if($shareGroup['type'] == 'core_share') {
                    if(isset($sh_core_groups[$shareGroup['id']])) {
                      //  $sh_core_groups[$shareGroup['id']] = $sh_core_groups[$shareGroup['id']] +  floatval( $shareGroup['no_of_shares'] );
                        
                    } else {
                        $sh_core_groups[$shareGroup['id']] = floatval( $shareGroup['no_of_shares'] );
                       
                    }
                } else {
                    $single_share_total = $single_share_total + floatval($shareGroup['no_of_shares']);
                }

            }



            $rec = array(
                'id' => $shareholder->id,
                'type' => ($shareholder->is_srilankan == 'yes') ? 'local' : 'foreign',
                'title' =>  $shareholder->title,
            'firstname' => $shareholder->first_name,
            'lastname' => $shareholder->last_name,
        
            'province' =>  ( isset($address->province) &&  $address->province) ? $address->province : '',
        'district' =>  ( isset($address->district) && $address->district) ? $address->district : '',
        'city' =>  ( isset($address->city) && $address->city) ? $address->city : '',
        'localAddress1' => ( isset($address->address1) && $address->address1) ? $address->address1 : '',
        'localAddress2' => ( isset($address->address2) && $address->address2) ? $address->address2 : '',
        'postcode' => (isset($address->postcode) && $address->postcode) ? $address->postcode : '',

        'forProvince' =>  ( isset($forAddress->province) &&  $forAddress->province) ? $forAddress->province : '',
        'forCity' =>  ( isset($forAddress->city) && $forAddress->city) ? $forAddress->city : '',
        'forAddress1' => ( isset($forAddress->address1) && $forAddress->address1) ? $forAddress->address1 : '',
        'forAddress2' => ( isset($forAddress->address2) && $forAddress->address2) ? $forAddress->address2 : '',
        'forPostcode' => (isset($forAddress->postcode) && $forAddress->postcode) ? $forAddress->postcode : '',


            'nic'       => $shareholder->nic,
            'passport'  => $shareholder->passport_no,
            'country'  => ( $shareholder->foreign_address_id)  ? $forAddress->country : $address->country,
            'passport_issued_country' => ( $shareholder->foreign_address_id)  ? $shareholder->passport_issued_country : 'Sri Lanka',
            // 'share'     => $shareholder->no_of_shares,
            'date'      => $shareholder->date_of_appointment,
            'phone' => $shareholder->telephone,
            'mobile' => $shareholder->mobile,
            'email' => $shareholder->email,
            'occupation' => $shareholder->occupation,
            'share' => $shareRec

            );
            $shareholders[] = $rec;
            }

        }
        

        

        /******sh firm list *****/
        $sh_list = CompanyFirms::where('company_id',$company_id)
        ->where('type_id',$this->settings('SHAREHOLDER','key')->id)
        ->where('status',1)
        ->orderBy('id', 'ASC')
        ->get();
        $shFirms = array();

        if(count($sh_list )){
           foreach($sh_list as $sec){
   
           $address_id =  $sec->address_id;
           
           $address = Address::where('id',$address_id)->first();

           $shareRec = array(
            'value' => 0,
            'type' => ''
            );
            $shareRow = Share::where('company_firm_id', $sec->id)->first();
            if(isset($shareRow->id) ){
                    $shareGroup = ShareGroup::where('id', $shareRow->group_id)->first();
                    
                    $shareRec['value'] = $shareGroup['no_of_shares'];
                    $shareRec['type'] = ($shareGroup['type'] == 'core_share') ? 'core share': 'single share';
                    $shareRec['id'] = $shareGroup['id'];
                     

                    if($shareGroup['type'] == 'core_share') {
                        if(isset($sh_core_groups[$shareGroup['id']])) {
                          //  $sh_core_groups[$shareGroup['id']] = $sh_core_groups[$shareGroup['id']] +  floatval( $shareGroup['no_of_shares'] );
                        } else {
                            $sh_core_groups[$shareGroup['id']] = floatval( $shareGroup['no_of_shares'] );
                        }
                    } else {
                        $single_share_total = $single_share_total + floatval($shareGroup['no_of_shares']);
                    }
                    


            }

           $rec = array(
               'id' => $sec->id,
               'type' => 'local',
               'title' =>  $sec->name,
           'registration_no' => $sec->registration_no,
           //'lastname' => $sec->last_name,
   
           'province' =>  ( $address->province) ? $address->province : '',
           'district' =>  ($address->district) ? $address->district : '',
           'city' =>  ( $address->city) ? $address->city : '',
           'localAddress1' => ($address->address1) ? $address->address1 : '',
           'localAddress2' => ($address->address2) ? $address->address2 : '',
           'postcode' => ($address->postcode) ? $address->postcode : '',
           'phone' => $sec->phone,
           'mobile' => $sec->mobile,
           'email' => $sec->email,
           'share' => $shareRec,
           'date'      => $sec->date_of_appointment,
          

       
           );
           $shFirms[] = $rec;
           }

       }

       $total_core_share_value = 0;
       if(count($sh_core_groups)){
           foreach($sh_core_groups as $key=> $shares ) {
               $total_core_share_value += $shares;
           }
       }



        return array(

            'directors' => $directors,
            'secs'      => $secs,
            'secFirms'  => $secFirms,
            'shs'       => $shareholders,
            'shFirms'   => $shFirms,
            'total_shares' => ( $total_core_share_value + $single_share_total )
        );


        }

        function generate_files($doc_type,$companyId,$loginUserEmail){

            $loginUserInfo = User::where('email', $loginUserEmail)->first();
            $loginUserId = $loginUserInfo->people_id;
    
            $userPeople = People::where('id',$loginUserId)->first();
            $userAddressId = $userPeople->address_id;  
            $userAddress = Address::where('id', $userAddressId)->first();

           
            //get payment date
            $payment_date= '';
            $payment_row = Order::where('module_id', $comapanyId)
                            ->where('module', $this->settings('MODULE_INCORPORATION','key')->id)
                            ->first();
            $payment_date = isset($payment_row->updated_at) ? strtotime($payment_row->updated_at) : '';

           // $payment_date = strtotime('2018-10-24 20:05:58');


            $docs = $this->getDocs($doc_type, $companyId );

            $downloaded = $docs['download'];

            $generated_files = array(

                'other' => array(),
                'director' => array(),
                'sec'   => array()


            );
            $company_info = Company::where('id',$companyId)->first();
            $postfix_values = $this->getPostfixValues($company_info->postfix);


            if(count($downloaded)){
                foreach($downloaded as $file ){
                      
                    $name = $file['name'];
                    $file_name_key = $file['file_name_key'];

                    $stakeholder_store = $this->stakeholder_store($companyId);

                    
                    
                    
                    $postfix_arr = $this->getCompanyPostFix($company_info->type_id);

                    $company_address = Address::where('id',$company_info->address_id)->first();

                    if( isset( $company_info->foreign_address_id) && $company_info->foreign_address_id ) {
                        $company_for_address = Address::where('id',$company_info->foreign_address_id)->first();
                    }else {
                        $company_for_address = '';
                    }
                   


                if($file['specific']  == 'director'){

                    $companyType = $this->settings($company_info->type_id,'id');

                    if(count($stakeholder_store['directors'])) {
                        foreach( $stakeholder_store['directors'] as $director ){ 

                        $data = array(
                            'public_path' => public_path(),
                            'eroc_logo' => url('/').'/images/forms/eroc.png',
                            'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                            'css_file' => url('/').'/images/forms/form1/form1.css',
                            'director' => $director,
                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'company_type' => $companyType->value,
                            'loginUser' => $userPeople,
                            'loginUserAddress' => $userAddress,
                            'payment_date' => $payment_date,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],

                        );
                        
                            $directory = $companyId;
                            Storage::makeDirectory($directory);
            

                            $view = 'forms.'.$file['view'];
                            $director_id = $director['id'];
            
                            $pdf = PDF::loadView($view, $data);
                            $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'.$director_id.'.pdf');

                            $file_row = array();
                            $file_row['name'] = $file['name'];
                            $file_row['stakeholder_name'] = $director['firstname'].' '.$director['lastname'];
                            $file_row['stakeholder_id'] = $director['id'];
                            $file_row['file_name_key'] = $file_name_key;
    
                      
                          $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$director_id.pdf");
                      

                           $generated_files['director'][] = $file_row;

                        }
                    }
    
                }else if($file['specific']  == 'sec'){

                    $companyType = $this->settings($company_info->type_id,'id');

                    if( count($stakeholder_store['secs']) ) {
                        foreach( $stakeholder_store['secs'] as $sec ){

                        $data = array(
                            'public_path' => public_path(),
                            'eroc_logo' => url('/').'/images/forms/eroc.png',
                            'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                            'css_file' => url('/').'/images/forms/form1/form1.css',
                            'sec' => $sec,
                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'company_type' => $companyType->value,
                            'loginUser' => $userPeople,
                            'loginUserAddress' => $userAddress,
                            'sec_type' => 'natural',
                            'payment_date' => $payment_date,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],

                        );
                        
                            $directory = $companyId;
                            Storage::makeDirectory($directory);
                            $view = 'forms.'.$file['view'];
                         
                            $sec_id = $sec['id'];
            
                            $pdf = PDF::loadView($view, $data);
                            $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'. $sec['id'].'.pdf');

                            $file_row = array();
                            $file_row['name'] = $file['name'];
                            $file_row['stakeholder_name'] = $sec['firstname'].' '.$sec['lastname'];
                            $file_row['stakeholder_id'] = $sec['id'];
                            $file_row['file_name_key'] = $file_name_key;
                            $file_row['type'] = 'natural';
    
                         
                        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$sec_id.pdf");
                            $generated_files['sec'][] = $file_row;

                        }
                    }

                    if( count($stakeholder_store['secFirms']) ) {
                        foreach( $stakeholder_store['secFirms'] as $sec ){

                        $data = array(
                            'public_path' => public_path(),
                            'eroc_logo' => url('/').'/images/forms/eroc.png',
                            'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                            'css_file' => url('/').'/images/forms/form1/form1.css',
                            'sec' => $sec,
                            'company_info' => $company_info,
                            'company_address' => $company_address,
                            'company_type' => $companyType->value,
                            'loginUser' => $userPeople,
                            'loginUserAddress' => $userAddress,
                            'sec_type' => 'firm',
                            'payment_date' => $payment_date,
                            'postfix' => $company_info->postfix,
                            'postfix_si' => $postfix_values['postfix_si'],
                            'postfix_ta' => $postfix_values['postfix_ta'],
                        );
                        
                            $directory = $companyId;
                            Storage::makeDirectory($directory);
                            $view = 'forms.'.$file['view'];
                         
                            $sec_id = $sec['id'];
            
                            $pdf = PDF::loadView($view, $data);
                            $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'-'. $sec['id'].'.firm'.'.pdf');

                            $file_row = array();
                            $file_row['name'] = $file['name'];
                            $file_row['stakeholder_name'] = $sec['title'].' (Organization)';
                            $file_row['stakeholder_id'] = $sec['id'];
                            $file_row['file_name_key'] = $file_name_key;
                            $file_row['type'] = 'firm';
    
                         
                        $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key-$sec_id.firm.pdf");
                            $generated_files['sec'][] = $file_row;

                        }
                    }


                }else{
                    $companyType = $this->settings($company_info->type_id,'id');
                    $data = array(
                        'public_path' => public_path(),
                        'eroc_logo' => url('/').'/images/forms/eroc.png',
                        'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                        'css_file' => url('/').'/images/forms/form1/form1.css',
                        'directors' => $stakeholder_store['directors'],
                        'secs' => $stakeholder_store['secs'],
                        'secFirms' => $stakeholder_store['secFirms'],
                        'shs' => $stakeholder_store['shs'],
                        'shFirms' => $stakeholder_store['shFirms'],
                        'company_info' => $company_info,
                        'company_address' => $company_address,
                        'company_for_address' => $company_for_address,
                        'company_type' => $companyType->value,
                        'loginUser' => $userPeople,
                        'loginUserAddress' => $userAddress,
                        'payment_date' => $payment_date,
                        'postfix' => $company_info->postfix,
                        'postfix_si' => $postfix_values['postfix_si'],
                        'postfix_ta' => $postfix_values['postfix_ta'],
                        'total_shares' => $stakeholder_store['total_shares']
                       
                    );
            
                  //  dd($userPeople->first_name);
            
                    $directory = $companyId;
                    Storage::makeDirectory($directory);
  
                    $view = 'forms.'.$file['view'];
                    $pdf = PDF::loadView($view, $data);
                    $pdf->save(storage_path("app/$directory").'/'.$file_name_key.'.pdf');

                    $file_row = array();
                    $file_row['name'] = $file['name'];
                    $file_row['file_name_key'] = $file_name_key;
  
                 
                 $file_row['download_link']  = str_replace('public','',url('/')).Storage::url("app/$directory/$file_name_key.pdf");
                   
                    $generated_files['other'][] =  $file_row;
                }


                }
            }
            
            return $generated_files;
        }


        function files_for_upload($doc_type,$companyId){

            $docs = $this->getDocs($doc_type, $companyId );

            $uploaded = $docs['upload'];

            $generated_files = array(
                'other' => array(),
                'multiple_other1'=> array(),
                'multiple_other2'=> array(),
                'director' => array(),
                'sec'   => array(),
                'secFirm' => array(),
               
            );

            $document_resubmit_status = $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
            $document_request_status = $this->settings('DOCUMENT_REQUESTED','key')->id;
            $company_info = Company::where('id',$companyId)->first();
            $company_status = $this->settings($company_info->status,'id')->key;
            
         

            if(count($uploaded)){
                $stakeholder_store = $this->stakeholder_store($companyId);
                foreach($uploaded as $file ){
                      
                    $name = $file['name'];
                

                if($file['specific']  == 'director'){

                    foreach( $stakeholder_store['directors'] as $director ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){


                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('company_member_id',$director['id'])
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';
                            

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('company_member_id',$director['id'])
                            ->first();
                            
                            
                           

                            if( isset( $for_Resubmission_doc->id) ) {

                                

                               $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }
                           
                            $file_row['stakeholder_name'] = $director['firstname'].' '.$director['lastname'];
                            $file_row['stakeholder_id'] = $director['id'];
                            $file_row['is_required'] = $file['required'];
                            $file_row['file_name'] = $file['name'];
                            $file_row['file_type'] = $file['type'];
                            $file_row['dbid'] = $file['dbid'];
                            $file_row['description'] = $file['description'];
                            $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                          
                            $generated_files['director'][] = $file_row;

                    }

                }else if($file['specific']  == 'sec'){

                    foreach( $stakeholder_store['secs'] as $sec ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] ='DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('company_member_id',$sec['id'])
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                          //  $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('company_member_id',$sec['id'])
                            ->first();
                            
                            if( isset( $for_Resubmission_doc->id) ) {

                              $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }

                        $file_row['stakeholder_name'] = $sec['firstname'].' '.$sec['lastname'];
                        $file_row['stakeholder_id'] = $sec['id'];
                        $file_row['is_required'] = $file['required'];
                        $file_row['file_name'] = $file['name'];
                        $file_row['file_type'] = $file['type'];
                        $file_row['dbid'] = $file['dbid'];
                        $file_row['description'] = $file['description'];
                        $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                        $file_row['isFirm'] = false;
    
                        $generated_files['sec'][] = $file_row;
                    }

                    foreach( $stakeholder_store['secFirms'] as $sec ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] ='DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('company_firm_id',$sec['id'])
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                           // $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('company_firm_id',$sec['id'])
                            ->first();
                            
                            if( isset( $for_Resubmission_doc->id) ) {

                              $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }

                        $file_row['stakeholder_name'] = $sec['title'].' (Organization)';
                        $file_row['stakeholder_id'] = $sec['id'];
                        $file_row['stakeholder_prefix_id'] = 'firm-'.$sec['id'];
                        $file_row['is_required'] = $file['required'];
                        $file_row['file_name'] = $file['name'];
                        $file_row['file_type'] = $file['type'];
                        $file_row['dbid'] = $file['dbid'];
                        $file_row['isFirm'] = true;
                        $file_row['description'] = $file['description'];
                        $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
    
                        $generated_files['secFirm'][] = $file_row;
                    }

                }

                    else if($file['specific']  == 'multiple1'){

                        for( $i=0; $i < 5; $i++ ){
    
                            $file_row = array();
                            $file_row['doc_comment'] = '';
                            $file_row['doc_status'] = 'DOCUMENT_PENDING';
                            $file_row['company_status'] = $company_status;
                            if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                            $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                            $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                            $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                               // $file_row['doc_status'] = 'DOCUMENT_APPROVED';

                                $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                                ->where('company_id', $companyId)
                                ->where('multiple_id',$i)
                                ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                                ->first();
                               // $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                                $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';
    
                                $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                                ->where('company_id', $companyId)
                                ->whereIn('status', array($document_resubmit_status,$document_request_status))
                                ->where('multiple_id',$i)
                                ->first();
                                
                                
                               
    
                                if( isset( $for_Resubmission_doc->id) ) {
    
                                   $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;
    
                                   $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                        ->where('status', $for_Resubmission_doc->status)
                                                        ->orderBy('id', 'desc')
                                                        ->first();
                                   
                                   if( isset($doc_status_row->id) ){
    
                                    $file_row['doc_comment'] = $doc_status_row->comments;
                                   }else{
                                    $file_row['doc_comment'] = '';
                                   }
                                }
                            }
                               
                                $file_row['is_required'] = $file['required'];
                                $file_row['file_name'] = $file['name'];
                                $file_row['file_type'] = $file['type'];
                                $file_row['dbid'] = $file['dbid'];
                                $file_row['description'] = $file['description'];
                                $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                                $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                                $generated_files['multiple_other1'][] = $file_row;
    
                        }
    
                    

                }
                else if($file['specific']  == 'multiple2'){

                    for( $i=0; $i < 5; $i++ ){

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                          //  $file_row['doc_status'] = 'DOCUMENT_APPROVED';

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->where('multiple_id',$i)
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();
                           // $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->where('multiple_id',$i)
                            ->first();
                            
                            
                           

                            if( isset( $for_Resubmission_doc->id) ) {

                               $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }
                           
                            $file_row['is_required'] = $file['required'];
                            $file_row['file_name'] = $file['name'];
                            $file_row['file_type'] = $file['type'];
                            $file_row['dbid'] = $file['dbid'];
                            $file_row['description'] = $file['description'];
                            $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                            $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                            $generated_files['multiple_other2'][] = $file_row;

                    }

                

            }
                
                else{

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        

                        if( isset($file['doc_requested']) && $file['doc_requested'] == 'yes' ) {
                            $file_row['doc_status'] = 'DOCUMENT_REQUESTED';
                        }else {
                            $file_row['doc_status'] = 'DOCUMENT_PENDING';
                        }

                       // $file_row['doc_status'] = 'DOCUMENT_PENDING';
                      
                        $file_row['company_status'] = $company_status;
                        if( $company_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT1' ||
                        $company_status === 'COMPANY_FOREIGN_REQUEST_TO_RESUBMIT2'){

                            $for_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id, $this->settings('DOCUMENT_APPROVED','key')->id) )
                            ->first();

                          //  $file_row['doc_status'] = $this->settings($for_doc->status,'id')->key;
                            $file_row['doc_status'] = isset($for_doc->status) ? $this->settings($for_doc->status,'id')->key : 'DOCUMENT_PENDING';

                            $for_Resubmission_doc = CompanyDocuments::where('document_id', $file['dbid'] )
                            ->where('company_id', $companyId)
                            ->whereIn('status', array($document_resubmit_status,$document_request_status))
                            ->first();
                            
                            if( isset( $for_Resubmission_doc->id) ) {

                                $file_row['doc_status'] = $this->settings($for_Resubmission_doc->status,'id')->key;

                               $doc_status_row = CompanyDocumentStatus::where('company_document_id',$for_Resubmission_doc->id)
                                                    ->where('status', $for_Resubmission_doc->status)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                                $file_row['doc_statusssss'] = $doc_status_row;
                               
                               if( isset($doc_status_row->id) ){

                                $file_row['doc_comment'] = $doc_status_row->comments;
                               }else{
                                $file_row['doc_comment'] = '';
                               }
                            }
                        }

                    $file_row['is_required'] = $file['required'];
                    $file_row['file_name'] = $file['name'];
                    $file_row['file_type'] = $file['type'];
                    $file_row['dbid'] = $file['dbid'];
                    $file_row['description'] = $file['description'];
                    $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                    $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                    $generated_files['other'][] = $file_row;

                }
                }
            }



            return $generated_files;
        
        }

       /**********debugging forms */
        function checkform01(){

            $companyId = 1809136691;

            $stakeholder_store = $this->stakeholder_store($companyId);
            $company_info = Company::where('id',$companyId)->first(); 
            $company_address = Address::where('id',$company_info->address_id)->first();

           // print_r($stakeholder_store['secs'] );

            $data = array(
                'public_path' => public_path(),
                'eroc_logo' => url('/').'/images/forms/eroc.png',
                'gov_logo' => url('/').'/images/forms/govlogo.jpg',
                'css_file' => url('/').'/images/forms/form1/form1.css',
                'directors' => $stakeholder_store['directors'],
                'secs' => $stakeholder_store['secs'],
                'shs' => $stakeholder_store['shs'],
                'company_info' => $company_info,
                'company_address' => $company_address
            );

            return view('forms/test-forms/form1', $data);
        }

        function upload_file(){
            return view('forms/upload');
        } 

        function upload(Request $request){

           $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
           $file_type = $request->fileType;
           $file_user_id = $request->userId;
           $multiple_id = intval( $request->multipleId );
           $isFirm = $request->isFirm;
           $company_id = $request->companyId; 
           $company_info = Company::where('id',$company_id)->first();
           $companyType = $this->settings($company_info->type_id,'id')->key;
           $docStatus = $request->docStatus;


         //  print_r($docStatus);
          
           $file_type_id =  $request->file_type_id;
           
           if(isset($file_user_id)){
               $file_user_id = intval( $file_user_id );
           }

          //  $forms_log = $this->document_map( $companyType );
         //   $form_map = $forms_log['form_map_id'];

         //  $file_type_id = (isset($form_map[$file_type])) ? $form_map[$file_type] : 0;
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

            $pdftext = file_get_contents( $request->file('uploadFile'));
            $num_of_pages = preg_match_all("/\/Page\W/", $pdftext, $dummy);

            $path = 'company/'.substr($company_id,0,2).'/'.substr($company_id,2,2).'/'.$company_id.'/IC';
            $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');

         
        $document_resubmit_status_id =  $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id;
        $document_request_id =  $this->settings('DOCUMENT_REQUESTED','key')->id;
        $document_pending_id =  $this->settings('DOCUMENT_PENDING','key')->id;
        if( $file_user_id){

            $field = ($isFirm == 'yes') ? 'company_firm_id' : 'company_member_id';
            

            if($docStatus == 'DOCUMENT_REQUESTED') {


                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where( $field, $file_user_id)
                ->where('status', $document_request_id)
                ->delete();

            }

            if($docStatus == 'DOCUMENT_REQUEST_TO_RESUBMIT') {

                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where( $field, $file_user_id)
                ->where('status', $document_resubmit_status_id)
                ->delete();

            }

            if($docStatus == 'DOCUMENT_PENDING') {

                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where( $field, $file_user_id)
                ->where('status', $document_pending_id)
                ->delete();

            }


        }else if($multiple_id >=0 ) {


            if($docStatus == 'DOCUMENT_REQUESTED') {

                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where( 'multiple_id', $multiple_id)
                ->where('status', $document_request_id)
                ->delete();

            }

            if($docStatus == 'DOCUMENT_REQUEST_TO_RESUBMIT') {

                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where( 'multiple_id', $multiple_id)
                ->where('status', $document_resubmit_status_id)
                ->delete();

            }

            if($docStatus == 'DOCUMENT_PENDING') {

                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where( 'multiple_id', $multiple_id)
                ->where('status', $document_pending_id)
                ->delete();

            }
            
            
            
        }else{

            if($docStatus == 'DOCUMENT_REQUESTED') {


                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where('status',  $document_request_id )
                ->delete();

            }

            if($docStatus == 'DOCUMENT_REQUEST_TO_RESUBMIT') {

                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where('status',  $document_resubmit_status_id )
                ->delete();

            }

            if($docStatus == 'DOCUMENT_PENDING') {

                CompanyDocuments::where('document_id', $file_type_id)
                ->where('company_id', $company_id)
                ->where('status',  $document_pending_id )
                ->delete();

            }

           
        }
         
          $token = md5(uniqid());

          $doc = new CompanyDocuments;
          $doc->document_id = $file_type_id;
          $doc->path = $path;
          $doc->company_id = $company_id;
          $doc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
          $doc->file_token = $token;
          $doc->no_of_pages = $num_of_pages;
          
          if($file_user_id){
              if($isFirm == 'yes') {
                $doc->company_firm_id = $file_user_id;
              }else{
                $doc->company_member_id = $file_user_id;
              }
          }

          if($multiple_id >=0) {
              $doc->multiple_id = $multiple_id;
          }

          $doc->save();

          return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'error'  => 'no',
            'uploaded_pages' => $num_of_pages
        ], 200);
        }
        
        function removeIRDNICDoc( Request $request ) {

            
            $companyId = $request->companyId;
            $docTypeId = $request->docTypeId;
            $userId = $request->userId;


            $remove = CompanyDocuments::where('document_id', $docTypeId)
            ->where('company_id', $companyId)
            ->where( 'company_member_id', $userId)
            ->where('status', $this->settings('IRD_DOCUMENT_UPLOADED','key')->id)
            ->delete();

            if($remove) {
                return response()->json([
                    'message' => 'File removed successfully.',
                    'status' =>true,
                    
                ], 200);
            }else {
                return response()->json([
                    'message' => 'File removing Failed.',
                    'status' =>false,
                    
                ], 200);
            }
        }

        function removeDoc(Request $request){

            $companyId = $request->companyId;
            $docTypeId = $request->docTypeId;
            $userId = $request->userId;
            $multipleId = $request->multipleId;
            $isFirm = $request->isFirm;

            $uploaded_docs = array();
            $company_info = Company::where('id',$companyId)->first();
            $companyTypeKey = $this->settings($company_info->type_id,'id')->key;
            $docs = $this->documents();
           // $docs_type_ids=array();
      
            if( isset($docs[$companyTypeKey]['upload'])){
                foreach($docs[$companyTypeKey]['upload'] as $doc){
                ///  $docs_type_ids[] = $doc['dbid'];

                  if($docTypeId == $doc['dbid'] ) {

                        if($doc['required'] == 'yes') {

                            if( $userId){

                                $field = ($isFirm == 'yes') ? 'company_firm_id' : 'company_member_id';
                               // $remove = CompanyDocuments::where('document_id', $docTypeId)
                               //  ->where('company_id', $companyId)
                              //   ->where( $field, $userId)
                              //   ->delete();
                                $update_arr = array(

                                    'file_token' => null,
                                    'path' => null,
                                    'no_of_pages'=> 0,
                                
                                );
                                CompanyDocuments::where('document_id', $docTypeId)
                                                ->where('company_id', $companyId)
                                                ->where( $field, $userId)
                                                ->where('status', $this->settings('DOCUMENT_PENDING','key')->id)
                                                 ->update($update_arr);


                             } else if($multipleId >=0) {
                                 
                                $update_arr = array(

                                    'file_token' => null,
                                    'path' => null,
                                    'no_of_pages'=> 0,
                                
                                );
                                CompanyDocuments::where('document_id', $docTypeId)
                                                ->where('company_id', $companyId)
                                                ->where( 'multiple_id', $multipleId)
                                                ->where('status', $this->settings('DOCUMENT_PENDING','key')->id)
                                                 ->update($update_arr);
                                
                                
                             }else{
                              //  $remove = CompanyDocuments::where('document_id', $docTypeId)
                              //  ->where('company_id', $companyId)
                             //   ->delete();

                                $update_arr = array(

                                    'file_token' => null,
                                    'path' => null,
                                    'no_of_pages'=> 0,
                                
                                );

                                 CompanyDocuments::where('document_id', $docTypeId)
                                 ->where('company_id', $companyId)
                                 ->where('status', $this->settings('DOCUMENT_PENDING','key')->id)
                                  ->update($update_arr);
                             }



                        } else {

                            if( $userId){

                                $field = ($isFirm == 'yes') ? 'company_firm_id' : 'company_member_id';
                                $remove = CompanyDocuments::where('document_id', $docTypeId)
                                 ->where('company_id', $companyId)
                                 ->where( $field, $userId)
                                 ->where('status', $this->settings('DOCUMENT_PENDING','key')->id)
                                 ->delete();
                             }
                             else if($multipleId >=0) {
                                 
                                $remove = CompanyDocuments::where('document_id', $docTypeId)
                                 ->where('company_id', $companyId)
                                 ->where( 'multiple_id', $multipleId)
                                 ->where('status', $this->settings('DOCUMENT_PENDING','key')->id)
                                 ->delete();
                                
                                
                             }
                             
                             
                             else{
                 
                 
                 
                                $remove = CompanyDocuments::where('document_id', $docTypeId)
                                 ->where('company_id', $companyId)
                                 ->where('status', $this->settings('DOCUMENT_PENDING','key')->id)
                                 ->delete();
                             }

                        }
                  }
                }
            }

            

            return response()->json([
                'message' => 'File removed successfully.',
                'status' =>true,
                
            ], 200);

        }

        /*********** */


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


    /*  private function objective_level1() {

              $level1  = array();
             
              $level1_list = CompanyObjective::where('parent_id',0)->get();

              foreach($level1_list as $one ) {

                $rec = array(
                    'id' => $one->id,
                    'name' => $one->name_en,
                    'code' =>$one->code,
                    'parent_id' => 0
                 );
                 $level1[] = $rec;


              }

              return $level1;


      }*/
        /*   private function objective_level2() {

            $level2  = array();
           
            $level1_list = $this->objective_level1();

            foreach($level1_list as $one ) {

                $level2_list = CompanyObjective::where('parent_id',$one['id'] )->get();

             
                foreach($level2_list as $two ) {

                    $rec = array(
                        'id' => $two->id,
                        'name' => $two->name_en,
                        'code' =>$two->code,
                        'parent_id' => $one['id']
                     );
                     $level2[] = $rec;


                }
               


            }

            return $level2;


        }*/

        /*private function objective_level3() {

            $level3  = array();
           
            $level2_list = $this->objective_level2();
    
            foreach($level2_list as $two ) {
    
                $level3_list = CompanyObjective::where('parent_id',$two['id'] )->get();
    
             
                foreach($level3_list as $three ) {
    
                    $rec = array(
                        'id' => $three->id,
                        'name' => $three->name_en,
                        'code' =>$three->code,
                        'parent_id' => $two['id']
                     );
                     $level3[] = $rec;
    
    
                }
    
            }
    
            return $level3;
    
        }*/

        /*private function objective_level4() {

            $level4  = array();
           
            $level3_list = $this->objective_level3();
    
            foreach($level3_list as $three ) {
    
                $level4_list = CompanyObjective::where('parent_id',$three['id'] )->get();
    
             
                foreach($level4_list as $four ) {
    
                    $rec = array(
                        'id' => $four->id,
                        'name' => $four->name_en,
                        'code' =>$four->code,
                        'parent_id' => $three['id']
                     );
                     $level4[] = $rec;
    
    
                }
    
            }
    
            return $level4;
    
        }*/

       /* private function objective_level5() {

            $level5  = array();
           
            $level4_list = $this->objective_level4();
    
            foreach($level4_list as $four ) {
    
                $level5_list = CompanyObjective::where('parent_id',$four['id'] )->get();
    
             
                foreach($level5_list as $five ) {
    
                    $rec = array(
                        'id' => $five->id,
                        'name' => $five->name_en,
                        'code' =>$five->code,
                        'parent_id' => $four['id']
                     );
                     $level5[] = $rec;
    
    
                }
    
            }
    
            return $level5;
    
        }*/

        private function objective_level1() {

            $level1  = array();
            $level1_list = CompanyObjective1::all();

            foreach($level1_list as $one ) {
              $rec = array(
                  'id' => $one->id,
                  'name' => $one->ob_name_en,
                  'code' =>$one->industry_code,
                  'parent_id' => 0
               );
               $level1[] = $rec;
            }

            return $level1;

      }

        private function objective_level2() {

            $level2  = array();
           
            $level2_list = CompanyObjective2::all();

            foreach($level2_list as $two ) {

                $rec = array(
                    'id' => $two->id,
                    'name' => $two->ob_name_en,
                    'code' =>$two->industry_code,
                    'parent_id' => $two->ob1_code
                 );
                 $level2[] = $rec;
            }
            return $level2;
        }

        private function objective_level3() {

            $level3  = array();
            $level3_list = CompanyObjective3::all();

            foreach($level3_list as $three ) {
                $rec = array(
                    'id' => $three->id,
                    'name' => $three->ob_name_en,
                    'code' =>$three->industry_code,
                    'parent_id' => $three->ob2_code
                 );
                 $level3[] = $rec;
            }
            return $level3;
        }

        private function objective_level4() {

            $level4  = array();
            $level4_list = CompanyObjective4::all();

            foreach($level4_list as $four ) {
                $rec = array(
                    'id' => $four->id,
                    'name' => $four->ob_name_en,
                    'code' =>$four->industry_code,
                    'parent_id' => $four->ob3_code
                 );
                 $level4[] = $rec;
               
            }
            return $level4;
        }
      
    
        private function objective_level5() {

            $level5  = array();
            $level5_list = CompanyObjective5::all();

            foreach($level5_list as $five ) {

                $rec = array(
                    'id' => $five->id,
                    'name' => $five->ob_name_en,
                    'code' =>$five->industry_code,
                    'parent_id' => $five->ob4_code
                 );
                 $level5[] = $rec;
            }
            return $level5;
        }

    function get_company_objectives() {
        $objective_cache_records = Cache::rememberForever('objective_cache_records', function () {
            return array(
                'level1' => $this->objective_level1(),
                'level2' => $this->objective_level2(),
                'level3' => $this->objective_level3(),
                'level4' => $this->objective_level4(),
                'level5' => $this->objective_level5()
            );
        });
        return $objective_cache_records;
    }

   
  
    function saveNoOfCopies( Request $request ) {

        $copyRequest = $request->copiesArr;
        $companyId = $request->companyId;

        $incorp_module = $this->settings('MODULE_INCORPORATION','key')->id;

        if(is_array($copyRequest) && count($copyRequest)){

        CompanyDocumentCopies::where('module_id', $companyId)
                                ->where('module', $incorp_module)
                                ->delete();
            foreach($copyRequest as $r ) {

                if(!intval($r['copies'])) {
                    continue;
                }

                $rec = new CompanyDocumentCopies;
                $rec->module = $incorp_module;
                $rec->module_id = $companyId;
                $rec->document_id = $r['doc_id'];
                $rec->member_id = ($r['member_id']) ? $r['member_id'] : null;
                $rec->firm_id = ($r['firm_id']) ? $r['firm_id'] : null;
                $rec->no_of_copies = (int) $r['copies'];
                $rec->save();

            }
        }
    }

    function saveIRDInfo(Request $request){
        //InlandRevenueDetails
        $irdID;

        if(isset( $request->id ) && intval($request->id) ){
            $ird = InlandRevenueDetails::find(intval($request->id));
            $irdID = $request->id;
        }else{
            $ird = new InlandRevenueDetails;
        }


        $ird->company_id = $request->companyId;
        $ird->date_of_commencement = $request->commencementdate;
        $ird->business_activity_code = str_pad($request->bac,6,"0",STR_PAD_LEFT);
        $ird->preferred_language_id = intval( $request->preferredlanguage );
        $ird->preferred_mode_of_communication_id = intval( $request->preferredmodeofcommunication );
        $ird->boi_registered = ($request->isboireg) ? 'Y' : 'N';
        $ird->boi_start_date = $request->boistartdate;
        $ird->boi_end_date = $request->boienddate;
        $ird->company_salutation = intval( $request->companysalutation );
        $ird->purpose_of_registration_id = intval($request->purposeofregistration);
        $ird->other_purpose_of_registration = (isset($request->otherpurposeofregistration) && $request->purposeofregistration == 999 ) ? $request->otherpurposeofregistration :  null;
        $ird->foreign_company = ($request->isforiegncompany) ? 1 : 0;
        $ird->foreign_date_of_incorporation = ($request->isforiegncompany) ? $request->dateofincorporationforeign : null;
        $ird->foreign_country_of_origin = ($request->isforiegncompany) ? $request->countryoforigin : null;
        $ird->parent_company_exists = ($request->parentcompanyexists) ? 1 : 0;
        $ird->local_parent_company = ($request->parentcompanyexists) ? $request->localparentcompany  :  null;
        $ird->parent_company_reference = ($request->parentcompanyexists) ? $request->parentcompanyreference  :  null;
        $ird->parent_company_reference_id = ($request->parentcompanyexists) ? $request->parentcompanyreferenceid  :  null;
        $ird->parent_company_address = ($request->parentcompanyexists) ? $request->parentcompanyaddress  :  null;
        $ird->parent_company_name = ($request->parentcompanyexists) ? $request->parentcompanyname  :  null;
        $ird->parent_company_country_of_incorporation = ($request->parentcompanyexists) ? intval($request->countryofincorporation)  : null;
        $ird->parent_company_date_of_incorporation = ($request->parentcompanyexists) ? $request->dateofincorporationparentcompany  :  null;
        $ird->contact_fax_number = $request->fax;
        $ird->contact_email_address = $request->email;
        $ird->contact_mobile_number = $request->mobile;
        $ird->contact_office_tel_number = $request->office;
        $ird->contact_person_name = $request->contactpersonname;
        $ird->secretary_division = $request->addresssecdiv;
        $ird->status = $this->settings('IRD_PENDING','key')->id;

        $ird->save();
        if( !( isset( $request->id ) && intval($request->id)) ){
            $irdID = $ird->id;
        }

        
        return response()->json([
            'message' => 'Successfully update IRD records.',
            'status' =>true,
            'irdID' => $irdID
            
        ], 200);


       
    }

    function saveLabourInfo(Request $request){
       
        $labourID;

        
        $labour_record = $request->labour;

        if(isset( $labour_record['id'] ) && intval($labour_record['id']) ){
            $ird = LabourDetails::find(intval($labour_record['id']));
            $labourID = $labour_record['id'];
        }else{
            $ird = new LabourDetails;
        }

        $ird->company_id = $request->companyId;
        $ird->nature_category = $labour_record['nature_category'];
        $ird->sub_nature_category = intval( $labour_record['sub_nature_category'] ) ? intval( $labour_record['sub_nature_category'] ) : null ;
        $ird->total_no_emp = intval( $labour_record['total_no_emp'] ) ? intval( $labour_record['total_no_emp'] ) : null ;
        $ird->total_no_cov_emp = intval( $labour_record['total_no_cov_emp'] ) ? intval( $labour_record['total_no_cov_emp'] ) : null ;
        $ird->total_no_other_than_cov_emp = intval( $labour_record['total_no_other_than_cov_emp'] ) ? intval( $labour_record['total_no_other_than_cov_emp'] ) : null ;
        $ird->recruited_date = ( $labour_record['recruited_date'] ) ?  $labour_record['recruited_date'] : null;
        $ird->status = $this->settings('LABOUR_PENDING','key')->id;

        $ird->save();
        if( !( isset(  $labour_record['id'] ) && intval( $labour_record['id'])) ){
            $labourID = $ird->id;
        }

        
        return response()->json([
            'message' => 'Successfully update IRD records.',
            'status' =>true,
            'labourID' => $labourID
            
        ], 200);


       
    }

    /********* */
    function files_for_upload_docs_for_ird($company_id){


        $generated_files = array(
                'docs' => array(),
                'uploadedAll' => false
            
        );

        // documents list
        $ird_director_nic = Documents::where('key', 'IRD_DIRECTOR_NIC')->first();
        $has_all_uploaded_str = '';

        $director_list = CompanyMember::where('company_id',$company_id)
                                       ->where('designation_type',$this->settings('DERECTOR','key')->id)
                                       ->where('is_srilankan', 'yes')
                                       ->where('status',1)
                                       ->get();
        $directors = array();
        foreach($director_list as $director){

            $file_row = array();
            $file_row['doc_comment'] = '';
            $file_row['doc_status'] = 'IRD_DOCUMENT_UPLOADED';
            $file_row['is_required'] = true;
            $file_row['file_name'] = $ird_director_nic->name;
            $file_row['file_type'] = '';
            $file_row['dbid'] = $ird_director_nic->id;
            $file_row['file_description'] = $ird_director_nic->description;
            $file_row['member_id'] = $director->id;
            $file_row['member_name'] = $director->first_name.' '.$director->last_name;
            $file_row['uploaded_path'] = '';
                
            $uploadedDoc =  CompanyDocuments::where('company_id', $company_id)
                                        ->where('document_id', $ird_director_nic->id )
                                        ->where('company_member_id', $director->id )
                                        ->where('status',$this->settings('IRD_DOCUMENT_UPLOADED','key')->id)
                                        ->orderBy('id', 'DESC')
                                        ->first();

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
        }
    
        return $generated_files;
    
    }

    function upload_ird_nic(Request $request){


        $file_name =  uniqid().'.pdf'; uniqid().'.pdf';
        $real_file_name = $request->fileRealName;
        $member_id = $request->member_id;
        $company_id = $request->company_id;
        $ird_director_nic = Documents::where('key', 'IRD_DIRECTOR_NIC')->first();

        $member_info = CompanyMember::where('id', $member_id)->first();

        $file_name = 'ROC_'.$company_id.'_'.$member_info->nic.'.pdf';

        $size = $request->file('uploadFile')->getClientSize() ;
        $ext = $request->file('uploadFile')->getClientMimeType();
    

        $allowed_mimes = array(
          //  'image/bmp',
          //  'image/gif',
          //  'image/jpeg',
          //  'image/svg+xml',
          //  'image/png',
            'application/pdf',
          //  'application/msword',
          //  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
           // 'application/octet-stream'
        );

        if( !in_array($ext, $allowed_mimes) ){

            return response()->json([
                'message' => 'Please upload pdf document.',
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
    
        $path = 'IRD/directors/'.$company_id.'/'.$member_id;
        $path=  $request->file('uploadFile')->storeAs($path,$file_name,'sftp');
    
        $token = md5(uniqid());
    
        $query = CompanyDocuments::query();
        $query->where('company_id', $company_id );
        $query->where('company_member_id', $member_id);
        $query->where('document_id',$ird_director_nic->id);
        $query->where('status', $this->settings('IRD_DOCUMENT_UPLOADED','key')->id);
        $query->delete();
            
        $doc = new CompanyDocuments;
        $doc->document_id = $ird_director_nic->id;
        $doc->path = $path;
        $doc->company_id = $company_id;
        $doc->company_member_id = $member_id;
        $doc->status =   $this->settings('IRD_DOCUMENT_UPLOADED','key')->id;
        $doc->file_token = $token;
        $doc->name = $file_name;
        $doc->save();
       
        return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'error'  => 'no',
        ], 200);
    

    }


   /*function saveDivSec(){
       $file_path =  asset('other/divisional_sec_list.csv');
        if (($handle = fopen($file_path, "r")) !== FALSE) {

            $row = 1;
            
          //  print_r(fgetcsv($handle, 1000, ","));
           // die('fyck');

           

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if($row == 1) {
                    $row++;
                    continue;
                }
                $num = count($data);

                $district_info = District::where('ird_id', $data[1])->first();
                $province_info = Province::where('id', $district_info->province_code)->first();


                $s = new SecDivision;
                $s->id = $data[2]; //set bulk flag
                $s->district_id = $district_info->id;
                $s->province_id =  $province_info->id;
                $s->description_en =   $data[3];
                $s->save();
               
              
            }
        fclose($handle);
        }
    }*/



  
} // end class