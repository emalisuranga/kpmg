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
use App\Province;
use App\District;
use App\City;
use App\GNDivision;
use App\CompanyDocumentCopies;
use App\CompanyPublicRequest;
use App\CompanyPublicRequestDocument;
use App\ChangeName;
use Storage;
use Cache;
use App;
use URL;
use App\Http\Helper\_helper;
use PDF;

class CertifiedCopiesController extends Controller
{
    use _helper;
    private $items_per_page;

    function __construct() {
        
        $this->items_per_page = 3;
    }



    private function getCompanyPaginatePages($name_part,$registration_no){
        
        $approved_statuses = array( 
            $this->settings('COMPANY_STATUS_APPROVED','key')->id,
            $this->settings('COMPANY_FOREIGN_STATUS_APPROVED','key')->id,
           // $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id
         );
        $query = null;
        $query = Company::query();
        $query->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id');
       // $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');

        if ($name_part) {
            $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');
        }
 
        if ($registration_no) {
             $query->where('company_certificate.registration_no', 'ilike', '%' . strtoupper($registration_no ). '%');
        }

        $query->whereIn('companies.status', $approved_statuses );
        $query->orderby('companies.id', 'DESC');
      
        $result_count = $query->count();

       return  ($result_count % $this->items_per_page == 0  )
                        ? $result_count / $this->items_per_page
                        : intval($result_count / $this->items_per_page) + 1;

    }


    function getCompnanies(Request $request ){


        $name_part = trim( $request->namePart);
        $registration_no = trim( $request->registration_no);
        $page = intval($request->page);
        $offset = $page*$this->items_per_page;

        $approved_statuses = array( 
            $this->settings('COMPANY_STATUS_APPROVED','key')->id,
            $this->settings('COMPANY_FOREIGN_STATUS_APPROVED','key')->id,
          //  $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id
         );
        
         $query = null;
         $query = Company::query();
         $query->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id');
         if ($name_part) {
            $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');
         }
 
         if ($registration_no) {
             $query->where('company_certificate.registration_no', 'ilike', '%' . strtoupper($registration_no ). '%');
          }
       
         $query->whereIn('companies.status', $approved_statuses );
 
         $query->orderby('companies.id', 'DESC');
 
         $query->select(
             'companies.id as id',
             'companies.name as name',
             'companies.name_si as name_si',
             'companies.name_ta as name_ta',
             'companies.postfix as postfix',
             'companies.incorporation_at as incorporation_at',
             'company_certificate.registration_no as registration_no',
             'companies.status as status'
            
          );
       
        $companies = $query->limit($this->items_per_page)->offset($offset)->get();
        $companyList = array();
        if(isset($companies[0]->id)){
            foreach($companies as $c ) {

                
                $row = array();
                $row['id'] = $c->id;
                $row['name'] = $c->name;
                $row['name_si'] = $c->name_si;
                $row['name_ta'] = $c->name_ta;
                $row['name_si'] = $c->name_si;
                $row['registration_no'] = $c->registration_no;
                $row['postfix'] = $c->postfix;
                $row['incorporation_at'] = $c->incorporation_at;
                $row['is_name_change_company_instant'] = false;
                $row['init_name_of_the_company'] = '';
                $row['init_name_of_the_company_id'] = '';
                $row['init_name_of_the_company_incorporation_at'] = '';
                $row['init_name_of_the_company_postfix'] ='';
                if($this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id == $c->status) {
                    $row['is_name_change_company_instant'] = true;

                    $namechange = ChangeName::where('new_company_id', $c->id)
                                  ->where('change_type', $this->settings('NAME_CHANGE','key')->id)
                                  ->where('status', $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id)
                                  ->first();
                    if(isset($namechange->old_company_id) && $namechange->old_company_id ){
                        $old_com_info = Company::where('id',$namechange->old_company_id)->first();
                    }
                    $row['init_name_of_the_company'] = $old_com_info->name;
                    $row['init_name_of_the_company_id'] = $old_com_info->id;
                    $row['init_name_of_the_company_incorporation_at'] = $old_com_info->incorporation_at;
                    $row['init_name_of_the_company_postfix'] = $old_com_info->postfix;

                    
                }


                $companyList[] = $row;

            }
        }

        //print_r($companies);

        return response()->json([
            'message'       => "Successfully listed companies.",
            'companyList'    => $companyList,
            'status'        => true,
            'count'         => $query->count(),
            'total_pages'   => $this->getCompanyPaginatePages($name_part,$registration_no),
            'current_page'  => ($page+1)
            ], 200);

    }




    /*************extract from incorp controller */

    function uploadedDocs($companyId){

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
        $requested_doc_status = array(
            $this->settings('DOCUMENT_PENDING','key')->id,
            $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
            $this->settings('DOCUMENT_APPROVED','key')->id
        );
    
        $doc =CompanyDocuments::where('company_id', $companyId)
                        ->whereIn('status', array( $this->settings('DOCUMENT_PENDING','key')->id,  $this->settings('DOCUMENT_APPROVED','key')->id ))
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
        return $uploaded_docs;
    }
    
    function uploadedDocsWithToken($companyId){
       
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

        return $uploaded_docs;
    }

    function uploadedDocsWithNoOfPages($companyId){
       
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
    
        return $uploaded_docs;
    }

    public function loadHeavyData(Request $request){

        if(!$request->companyId){
                return response()->json([
                    'message' => 'We can \'t find a company.',
                    'status' =>false
                ], 200);
        }
        
        return response()->json([
                'message' => 'Data Loaded.',
                'status' =>true,
                'data'   => array(
                        'pdc' => $this->getProvincesDisctrictsCities(),
                        'objectives' => $this->get_company_objectives()
                )
        ], 200);
    
      }

      private function getCompanyPostFix($type_id) {
        $company_types = CompanyPostfix::all();
   
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
                'country'  => ( $director->foreign_address_id)  ? $forAddress->country : $address->country,
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
        'type' => 'local',
        'pvNumber' => $sec->registration_no,
        'firm_name' => $sec->name,
        'firm_province' =>  ( $address->province) ? $address->province : '',
        'firm_district' =>  ($address->district) ? $address->district : '',
        'firm_city' =>  ( $address->city) ? $address->city : '',
        'firm_localAddress1' => ($address->address1) ? $address->address1 : '',
        'firm_localAddress2' => ($address->address2) ? $address->address2 : '',
        'firm_postcode' => ($address->postcode) ? $address->postcode : '',
        'firm_email' => $sec->email,
        'firm_phone' => $sec->phone,
        'firm_mobile' => $sec->mobile,
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
                'country'  => ( $sec->foreign_address_id && isset( $forAddress->country) )  ? $forAddress->country : $address->country,
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
                'secType' => ( $sec->is_natural_person == 'yes') ? 'natural' : 'firm',
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
        'country'  => ( $shareholder->foreign_address_id)  ? $forAddress->country : $address->country,
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
              'group_name' => $g->name
          );
          $core_groups_list[] = $grec;
        }
      }

   //  $payment_row =  $this->document_map($companyType->key);

    // $payment = $payment_row['form_map_fee'];

     $payment_new_row = $this->document_map_new($request->companyId,$companyType->key,$directors,$secs,$secs_firms);

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

    }else {
        $stakehodlerE = true;
    }

    $external_global_comment = '';

    if(  $process_status === 'COMPANY_STATUS_REQUEST_TO_RESUBMIT' ){
           $resumbmit_key_id = $this->settings('COMPANY_STATUS_REQUEST_TO_RESUBMIT','key')->id;
           $external_comment_key_id = $this->settings('COMMENT_EXTERNAL','key')->id;
           $external_comment_query = CompanyStatus::where('company_id',$request->companyId)
                                                    ->where('comment_type', $external_comment_key_id )
                                                    ->where('status', $resumbmit_key_id )
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


    return response()->json([
            'message' => 'Company Data is successfully loaded.',
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
                            'payment' => null,
                           // 'payment_new' => $payment_new,
                            'directors' => $directors,
                            'secs' => $secs,
                            'secs_firms' => $secs_firms,
                            'shareholders' => $shareholders,
                            'shareholderFirms' => $shareholderFirms,
                            'documents' =>$documentList,
                            'public_path' =>  storage_path(),
                           // 'companyTypes' => $this->settings('COMPANY_TYPES','key'),
                            'postfix' => $postfix_arr['postfix'],
                            'postfix_si' => $postfix_arr['postfix_si'],
                            'postfix_ta' => $postfix_arr['postfix_ta'],
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
                       // 'incorporationConvenienceFee' =>0.5,
                       'incorporationConvenienceFee' =>floatval($this->settings('PAYMENT_CONVENIENCE_FEE','key')->value),
                        'companyObjectiveList' => CompanyObjective::where('company_id', $request->companyId)->get(),
                          'companyObjectiveListCount' => CompanyObjective::where('company_id', $request->companyId)->count(),
                          'payment_new' => $this->certifiedCopiesInfo($request->companyId)
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

    function getDocs($doc_type, $companyId= null){
        $docs = $this->documents(false, $companyId);
       //print_r($docs[$doc_type]);
        return isset(  $docs[$doc_type] ) ?   $docs[$doc_type]  : false;
    }

    function documents($is_resubmission=false, $companyId= null){

        $form_15 = Documents::where('key', 'FORM_15')->first();
        $form_14 = Documents::where('key', 'FORM_14')->first();
        $form_7 = Documents::where('key', 'FORM_7')->first();
        $form_13 = Documents::where('key', 'FORM_13')->first();
        $form_3 = Documents::where('key', 'FORM_3')->first();
        $form_prior_approval_letter = Documents::where('key', 'NAME_CHANGE_LETTER')->first();
        $form_20 = Documents::where('key', 'FORM_20')->first();
        $form_11 = Documents::where('key', 'FORM_11')->first();
        $form_10A = Documents::where('key', 'ISSUE_OF_DEBENTURES_FORM10A')->first();
        $form_10 = Documents::where('key', 'FORM_10')->first();
        $form_6 = Documents::where('key', 'ISSUE_OF_SHARES_FORM6')->first();
        $form_8 = Documents::where('key', 'REDUCTION_STATED_CAPITAL_FORM8')->first();
        $form_16_add = Documents::where('key', 'FORM_16_ADD')->first();
        $form_16_remove = Documents::where('key', 'FORM_16_REMOVE')->first();
        $form_prospectus = Documents::where('key', 'PROSPECTUS_DOC')->first();
        $form_other_court = Documents::where('key', 'OTHERS_COURT_ORDER_DOC')->first();
        $form_34 = Documents::where('key', 'FORM_34')->first();
        $form_annual_accout = Documents::where('key', 'ANNUAL_ACCOUNTS_DOC')->first();
        $form_39 = Documents::where('key', 'FORM_39')->first();
        $form_35 = Documents::where('key', 'FORM_35')->first();
        $form_23 = Documents::where('key', 'FORM_23')->first();
        $form_45_overseas = Documents::where('key', 'OVERSEAS_FORM45')->first();
        $form_45_offshore = Documents::where('key', 'OFFSHORE_FORM45')->first();
        $form_46_overseas = Documents::where('key', 'OVERSEAS_FORM46')->first();
        $form_46_offshore = Documents::where('key', 'OFFSHORE_FORM46')->first();
        

        $docs = array();
        $private_public_unlimited = array('COMPANY_TYPE_PRIVATE', 'COMPANY_TYPE_PUBLIC','COMPANY_TYPE_UNLIMITED');

       
        $name_change_group = DocumentsGroup::where('request_type', 'NAME_CHANGE')
        ->first();
            $name_change_group_id = @$name_change_group->id;

        $uploads_name_change = Documents::where('document_group_id', $name_change_group_id)
            ->where('status', 1)
            ->get();
       

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

           
            $rec = array(
                'dbid' => $form_3->id,
                'name' => $form_3->name,
                'savedLocation' => '',
                'required' => ($form_3->is_required == 'yes' ) ? true : false,
                'specific' => 'name_change',
                'type'   =>$this->slugify($form_3->name),
                'fee'    => $form_3->fee,
                'key' => $form_3->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_3->description,
                'issue_certified_copy' => $form_3->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',

        );
        $docs[$type]['upload'][] = $rec;


        $rec = array(
                'dbid' => $form_prior_approval_letter->id,
                'name' => $form_prior_approval_letter->name,
                'savedLocation' => '',
                'required' => ($form_prior_approval_letter->is_required == 'yes' ) ? true : false,
                'specific' => 'name_change',
                'type'   =>$this->slugify($form_prior_approval_letter->name),
                'fee'    => $form_prior_approval_letter->fee,
                'key' => $form_prior_approval_letter->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_prior_approval_letter->description,
                'issue_certified_copy' => $form_prior_approval_letter->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',

        );
        $docs[$type]['upload'][] = $rec;

            //form 13

            $rec = array(
                'dbid' => $form_13->id,
            'name' => $form_13->name,
            'savedLocation' => '',
            'required' => ($form_13->is_required == 'yes' ) ? true : false,
            'specific' => $form_13->specific_company_member_type,
            'type'   =>$this->slugify($form_13->name),
            'fee'    => $form_13->fee,
            'key' => $form_13->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_13->description,
            'issue_certified_copy' => $form_13->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

            //form 20

            $rec = array(
                'dbid' => $form_20->id,
            'name' => $form_20->name,
            'savedLocation' => '',
            'required' => ($form_20->is_required == 'yes' ) ? true : false,
            'specific' => $form_20->specific_company_member_type,
            'type'   =>$this->slugify($form_20->name),
            'fee'    => $form_20->fee,
            'key' => $form_20->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_20->description,
            'issue_certified_copy' => $form_20->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;


            //form 6

            $rec = array(
                'dbid' => $form_6->id,
            'name' => $form_6->name,
            'savedLocation' => '',
            'required' => ($form_6->is_required == 'yes' ) ? true : false,
            'specific' => $form_6->specific_company_member_type,
            'type'   =>$this->slugify($form_6->name),
            'fee'    => $form_6->fee,
            'key' => $form_6->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_6->description,
            'issue_certified_copy' => $form_6->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

             //form 7

             $rec = array(
                'dbid' => $form_7->id,
            'name' => $form_7->name,
            'savedLocation' => '',
            'required' => ($form_7->is_required == 'yes' ) ? true : false,
            'specific' => $form_7->specific_company_member_type,
            'type'   =>$this->slugify($form_7->name),
            'fee'    => $form_7->fee,
            'key' => $form_7->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_7->description,
            'issue_certified_copy' => $form_7->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;


              //form 8

              $rec = array(
                'dbid' => $form_8->id,
            'name' => $form_8->name,
            'savedLocation' => '',
            'required' => ($form_8->is_required == 'yes' ) ? true : false,
            'specific' => $form_8->specific_company_member_type,
            'type'   =>$this->slugify($form_8->name),
            'fee'    => $form_8->fee,
            'key' => $form_8->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_8->description,
            'issue_certified_copy' => $form_8->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

            //form 11

            $rec = array(
            'dbid' => $form_11->id,
            'name' => $form_11->name,
            'savedLocation' => '',
            'required' => ($form_11->is_required == 'yes' ) ? true : false,
            'specific' => $form_11->specific_company_member_type,
            'type'   =>$this->slugify($form_11->name),
            'fee'    => $form_11->fee,
            'key' => $form_11->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_11->description,
            'issue_certified_copy' => $form_11->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;


             //form 10

             $rec = array(
                'dbid' => $form_10->id,
                'name' => $form_10->name,
                'savedLocation' => '',
                'required' => ($form_10->is_required == 'yes' ) ? true : false,
                'specific' => 'form_10',
                'type'   =>$this->slugify($form_10->name),
                'fee'    => $form_10->fee,
                'key' => $form_10->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_10->description,
                'issue_certified_copy' => $form_10->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',
    
            );
            $docs[$type]['upload'][] = $rec;

            //form 10A

            $rec = array(
                'dbid' => $form_10A->id,
                'name' => $form_10A->name,
                'savedLocation' => '',
                'required' => ($form_10A->is_required == 'yes' ) ? true : false,
                'specific' => $form_10A->specific_company_member_type,
                'type'   =>$this->slugify($form_10A->name),
                'fee'    => $form_10A->fee,
                'key' => $form_10A->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_10A->description,
                'issue_certified_copy' => $form_10A->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',
    
            );
            $docs[$type]['upload'][] = $rec;

            //form 15

            $rec = array(
                'dbid' => $form_15->id,
               'name' => $form_15->name,
               'savedLocation' => '',
               'required' => ($form_15->is_required == 'yes' ) ? true : false,
               'specific' => $form_15->specific_company_member_type,
               'type'   =>$this->slugify($form_15->name),
               'fee'    => $form_15->fee,
               'key' => $form_15->key,
              // 'fee'    =>mt_rand(2000, 5000),
               'uploaded_path' => '',
               'comments' =>'',
               'description' => $form_15->description,
               'issue_certified_copy' => $form_15->issue_certified_copy,
               'doc_requested' => 'no',
               'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

             //form 13

            $rec = array(
                'dbid' => $form_13->id,
            'name' => $form_13->name,
            'savedLocation' => '',
            'required' => ($form_13->is_required == 'yes' ) ? true : false,
            'specific' => $form_13->specific_company_member_type,
            'type'   =>$this->slugify($form_13->name),
            'fee'    => $form_13->fee,
            'key' => $form_13->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_13->description,
            'issue_certified_copy' => $form_13->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

             //form 34
             $rec = array(
                'dbid' => $form_34->id,
            'name' => $form_34->name,
            'savedLocation' => '',
            'required' => ($form_34->is_required == 'yes' ) ? true : false,
            'specific' => $form_34->specific_company_member_type,
            'type'   =>$this->slugify($form_34->name),
            'fee'    => $form_34->fee,
            'key' => $form_34->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_34->description,
            'issue_certified_copy' => $form_34->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;


            //form 16 add
            $rec = array(
            'dbid' => $form_16_add->id,
            'name' => $form_16_add->name,
            'savedLocation' => '',
            'required' => ($form_16_add->is_required == 'yes' ) ? true : false,
            'specific' => $form_16_add->specific_company_member_type,
            'type'   =>$this->slugify($form_16_add->name),
            'fee'    => $form_16_add->fee,
            'key' => $form_16_add->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_16_add->description,
            'issue_certified_copy' => $form_16_add->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

             //form 16 remove
             $rec = array(
                'dbid' => $form_16_remove->id,
                'name' => $form_16_remove->name,
                'savedLocation' => '',
                'required' => ($form_16_remove->is_required == 'yes' ) ? true : false,
                'specific' => $form_16_remove->specific_company_member_type,
                'type'   =>$this->slugify($form_16_remove->name),
                'fee'    => $form_16_remove->fee,
                'key' => $form_16_remove->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_16_remove->description,
                'issue_certified_copy' => $form_16_remove->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',
    
                );
                $docs[$type]['upload'][] = $rec;

                //form 14 
                $rec = array(
                    'dbid' => $form_14->id,
                    'name' => $form_14->name,
                    'savedLocation' => '',
                    'required' => ($form_14->is_required == 'yes' ) ? true : false,
                    'specific' => $form_14->specific_company_member_type,
                    'type'   =>$this->slugify($form_14->name),
                    'fee'    => $form_14->fee,
                    'key' => $form_14->key,
                    // 'fee'    =>mt_rand(2000, 5000),
                    'uploaded_path' => '',
                    'comments' =>'',
                    'description' => $form_14->description,
                    'issue_certified_copy' => $form_14->issue_certified_copy,
                    'doc_requested' => 'no',
                    'admin_set'  => 'no',

                    );
                    $docs[$type]['upload'][] = $rec;
            
            //form prospectus
            $rec = array(
                'dbid' => $form_prospectus->id,
                'name' => $form_prospectus->name,
                'savedLocation' => '',
                'required' => ($form_prospectus->is_required == 'yes' ) ? true : false,
                'specific' => $form_prospectus->specific_company_member_type,
                'type'   =>$this->slugify($form_prospectus->name),
                'fee'    => $form_prospectus->fee,
                'key' => $form_prospectus->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_prospectus->description,
                'issue_certified_copy' => $form_prospectus->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',

        );

        //form other court order
        $rec = array(
            'dbid' => $form_other_court->id,
            'name' => $form_other_court->name,
            'savedLocation' => '',
            'required' => ($form_other_court->is_required == 'yes' ) ? true : false,
            'specific' => $form_other_court->specific_company_member_type,
            'type'   =>$this->slugify($form_other_court->name),
            'fee'    => $form_other_court->fee,
            'key' => $form_other_court->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_other_court->description,
            'issue_certified_copy' => $form_other_court->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

        $rec = array(
            'dbid' => $form_annual_accout->id,
            'name' => $form_annual_accout->name,
            'savedLocation' => '',
            'required' => ($form_annual_accout->is_required == 'yes' ) ? true : false,
            'specific' => $form_annual_accout->specific_company_member_type,
            'type'   =>$this->slugify($form_annual_accout->name),
            'fee'    => $form_annual_accout->fee,
            'key' => $form_annual_accout->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_annual_accout->description,
            'issue_certified_copy' => $form_annual_accout->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );
         $docs[$type]['upload'][] = $rec;

         $rec = array(
            'dbid' => $form_39->id,
            'name' => $form_39->name,
            'savedLocation' => '',
            'required' => ($form_39->is_required == 'yes' ) ? true : false,
            'specific' => $form_39->specific_company_member_type,
            'type'   =>$this->slugify($form_39->name),
            'fee'    => $form_39->fee,
            'key' => $form_39->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_39->description,
            'issue_certified_copy' => $form_39->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;
            
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
            //form 3

            $rec = array(
                'dbid' => $form_3->id,
            'name' => $form_3->name,
            'savedLocation' => '',
            'required' => ($form_3->is_required == 'yes' ) ? true : false,
            'specific' => 'name_change',
            'type'   =>$this->slugify($form_3->name),
            'fee'    => $form_3->fee,
            'key' => $form_3->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_3->description,
            'issue_certified_copy' => $form_3->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

            $rec = array(
                'dbid' => $form_prior_approval_letter->id,
                'name' => $form_prior_approval_letter->name,
                'savedLocation' => '',
                'required' => ($form_prior_approval_letter->is_required == 'yes' ) ? true : false,
                'specific' => 'name_change',
                'type'   =>$this->slugify($form_prior_approval_letter->name),
                'fee'    => $form_prior_approval_letter->fee,
                'key' => $form_prior_approval_letter->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_prior_approval_letter->description,
                'issue_certified_copy' => $form_prior_approval_letter->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',

        );
        $docs[$type]['upload'][] = $rec;


            //form 13

            $rec = array(
                'dbid' => $form_13->id,
            'name' => $form_13->name,
            'savedLocation' => '',
            'required' => ($form_13->is_required == 'yes' ) ? true : false,
            'specific' => $form_13->specific_company_member_type,
            'type'   =>$this->slugify($form_13->name),
            'fee'    => $form_13->fee,
            'key' => $form_13->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_13->description,
            'issue_certified_copy' => $form_13->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

            //form 20

            $rec = array(
            'dbid' => $form_20->id,
            'name' => $form_20->name,
            'savedLocation' => '',
            'required' => ($form_20->is_required == 'yes' ) ? true : false,
            'specific' => $form_20->specific_company_member_type,
            'type'   =>$this->slugify($form_20->name),
            'fee'    => $form_20->fee,
            'key' => $form_20->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_20->description,
            'issue_certified_copy' => $form_20->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;


              //form 8

              $rec = array(
                'dbid' => $form_8->id,
            'name' => $form_8->name,
            'savedLocation' => '',
            'required' => ($form_8->is_required == 'yes' ) ? true : false,
            'specific' => $form_8->specific_company_member_type,
            'type'   =>$this->slugify($form_8->name),
            'fee'    => $form_8->fee,
            'key' => $form_8->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_8->description,
            'issue_certified_copy' => $form_8->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

            //form 11

            $rec = array(
                'dbid' => $form_11->id,
                'name' => $form_11->name,
                'savedLocation' => '',
                'required' => ($form_11->is_required == 'yes' ) ? true : false,
                'specific' => $form_11->specific_company_member_type,
                'type'   =>$this->slugify($form_11->name),
                'fee'    => $form_11->fee,
                'key' => $form_11->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_11->description,
                'issue_certified_copy' => $form_11->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',
    
            );
            $docs[$type]['upload'][] = $rec;

            //form 10

            $rec = array(
                'dbid' => $form_10->id,
                'name' => $form_10->name,
                'savedLocation' => '',
                'required' => ($form_10->is_required == 'yes' ) ? true : false,
                'specific' => 'form_10',
                'type'   =>$this->slugify($form_10->name),
                'fee'    => $form_10->fee,
                'key' => $form_10->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_10->description,
                'issue_certified_copy' => $form_10->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',
    
            );
            $docs[$type]['upload'][] = $rec;
            
            //form 10A

            $rec = array(
                'dbid' => $form_10A->id,
                'name' => $form_10A->name,
                'savedLocation' => '',
                'required' => ($form_10A->is_required == 'yes' ) ? true : false,
                'specific' => $form_10A->specific_company_member_type,
                'type'   =>$this->slugify($form_10A->name),
                'fee'    => $form_10A->fee,
                'key' => $form_10A->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_10A->description,
                'issue_certified_copy' => $form_10A->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',
    
            );
            $docs[$type]['upload'][] = $rec;

            //form 15

            $rec = array(
                'dbid' => $form_15->id,
               'name' => $form_15->name,
               'savedLocation' => '',
               'required' => ($form_15->is_required == 'yes' ) ? true : false,
               'specific' => $form_15->specific_company_member_type,
               'type'   =>$this->slugify($form_15->name),
               'fee'    => $form_15->fee,
               'key' => $form_15->key,
              // 'fee'    =>mt_rand(2000, 5000),
               'uploaded_path' => '',
               'comments' =>'',
               'description' => $form_15->description,
               'issue_certified_copy' => $form_15->issue_certified_copy,
               'doc_requested' => 'no',
               'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;


             //form 34
             $rec = array(
                'dbid' => $form_34->id,
            'name' => $form_34->name,
            'savedLocation' => '',
            'required' => ($form_34->is_required == 'yes' ) ? true : false,
            'specific' => $form_34->specific_company_member_type,
            'type'   =>$this->slugify($form_34->name),
            'fee'    => $form_34->fee,
            'key' => $form_34->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_34->description,
            'issue_certified_copy' => $form_34->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

            );
            $docs[$type]['upload'][] = $rec;

             //form 16 add
             $rec = array(
                'dbid' => $form_16_add->id,
                'name' => $form_16_add->name,
                'savedLocation' => '',
                'required' => ($form_16_add->is_required == 'yes' ) ? true : false,
                'specific' => $form_16_add->specific_company_member_type,
                'type'   =>$this->slugify($form_16_add->name),
                'fee'    => $form_16_add->fee,
                'key' => $form_16_add->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_16_add->description,
                'issue_certified_copy' => $form_16_add->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',
    
                );
                $docs[$type]['upload'][] = $rec;
    
                 //form 16 remove
                 $rec = array(
                    'dbid' => $form_16_remove->id,
                    'name' => $form_16_remove->name,
                    'savedLocation' => '',
                    'required' => ($form_16_remove->is_required == 'yes' ) ? true : false,
                    'specific' => $form_16_remove->specific_company_member_type,
                    'type'   =>$this->slugify($form_16_remove->name),
                    'fee'    => $form_16_remove->fee,
                    'key' => $form_16_remove->key,
                    // 'fee'    =>mt_rand(2000, 5000),
                    'uploaded_path' => '',
                    'comments' =>'',
                    'description' => $form_16_remove->description,
                    'issue_certified_copy' => $form_16_remove->issue_certified_copy,
                    'doc_requested' => 'no',
                    'admin_set'  => 'no',
        
                    );
                    $docs[$type]['upload'][] = $rec;

                     //form 14 
                $rec = array(
                    'dbid' => $form_14->id,
                    'name' => $form_14->name,
                    'savedLocation' => '',
                    'required' => ($form_14->is_required == 'yes' ) ? true : false,
                    'specific' => $form_14->specific_company_member_type,
                    'type'   =>$this->slugify($form_14->name),
                    'fee'    => $form_14->fee,
                    'key' => $form_14->key,
                    // 'fee'    =>mt_rand(2000, 5000),
                    'uploaded_path' => '',
                    'comments' =>'',
                    'description' => $form_14->description,
                    'issue_certified_copy' => $form_14->issue_certified_copy,
                    'doc_requested' => 'no',
                    'admin_set'  => 'no',

                    );
                    $docs[$type]['upload'][] = $rec;

             //form prospectus
             $rec = array(
                'dbid' => $form_prospectus->id,
                'name' => $form_prospectus->name,
                'savedLocation' => '',
                'required' => ($form_prospectus->is_required == 'yes' ) ? true : false,
                'specific' => $form_prospectus->specific_company_member_type,
                'type'   =>$this->slugify($form_prospectus->name),
                'fee'    => $form_prospectus->fee,
                'key' => $form_prospectus->key,
                // 'fee'    =>mt_rand(2000, 5000),
                'uploaded_path' => '',
                'comments' =>'',
                'description' => $form_prospectus->description,
                'issue_certified_copy' => $form_prospectus->issue_certified_copy,
                'doc_requested' => 'no',
                'admin_set'  => 'no',

        );
        $docs[$type]['upload'][] = $rec;

        //form other court order
        $rec = array(
            'dbid' => $form_other_court->id,
            'name' => $form_other_court->name,
            'savedLocation' => '',
            'required' => ($form_other_court->is_required == 'yes' ) ? true : false,
            'specific' => $form_other_court->specific_company_member_type,
            'type'   =>$this->slugify($form_other_court->name),
            'fee'    => $form_other_court->fee,
            'key' => $form_other_court->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_other_court->description,
            'issue_certified_copy' => $form_other_court->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

        $rec = array(
            'dbid' => $form_annual_accout->id,
            'name' => $form_annual_accout->name,
            'savedLocation' => '',
            'required' => ($form_annual_accout->is_required == 'yes' ) ? true : false,
            'specific' => $form_annual_accout->specific_company_member_type,
            'type'   =>$this->slugify($form_annual_accout->name),
            'fee'    => $form_annual_accout->fee,
            'key' => $form_annual_accout->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_annual_accout->description,
            'issue_certified_copy' => $form_annual_accout->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

        $rec = array(
            'dbid' => $form_39->id,
            'name' => $form_39->name,
            'savedLocation' => '',
            'required' => ($form_39->is_required == 'yes' ) ? true : false,
            'specific' => $form_39->specific_company_member_type,
            'type'   =>$this->slugify($form_39->name),
            'fee'    => $form_39->fee,
            'key' => $form_39->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_39->description,
            'issue_certified_copy' => $form_39->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

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

        $rec = array(
            'dbid' => $form_annual_accout->id,
            'name' => $form_annual_accout->name,
            'savedLocation' => '',
            'required' => ($form_annual_accout->is_required == 'yes' ) ? true : false,
            'specific' => $form_annual_accout->specific_company_member_type,
            'type'   =>$this->slugify($form_annual_accout->name),
            'fee'    => $form_annual_accout->fee,
            'key' => $form_annual_accout->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_annual_accout->description,
            'issue_certified_copy' => $form_annual_accout->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;


        $rec = array(
            'dbid' => $form_35->id,
            'name' => $form_35->name,
            'savedLocation' => '',
            'required' => ($form_35->is_required == 'yes' ) ? true : false,
            'specific' => $form_35->specific_company_member_type,
            'type'   =>$this->slugify($form_35->name),
            'fee'    => $form_35->fee,
            'key' => $form_35->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_35->description,
            'issue_certified_copy' => $form_35->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

        $rec = array(
            'dbid' => $form_45_overseas->id,
            'name' => $form_45_overseas->name,
            'savedLocation' => '',
            'required' => ($form_45_overseas->is_required == 'yes' ) ? true : false,
            'specific' => $form_45_overseas->specific_company_member_type,
            'type'   =>$this->slugify($form_45_overseas->name),
            'fee'    => $form_45_overseas->fee,
            'key' => $form_45_overseas->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_45_overseas->description,
            'issue_certified_copy' => $form_45_overseas->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;


        $rec = array(
            'dbid' => $form_46_overseas->id,
            'name' => $form_46_overseas->name,
            'savedLocation' => '',
            'required' => ($form_46_overseas->is_required == 'yes' ) ? true : false,
            'specific' => $form_46_overseas->specific_company_member_type,
            'type'   =>$this->slugify($form_46_overseas->name),
            'fee'    => $form_46_overseas->fee,
            'key' => $form_46_overseas->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_46_overseas->description,
            'issue_certified_copy' => $form_46_overseas->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

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
               'uploaded_path' => '',
               'description' => $d->description,
               'issue_certified_copy' => $d->issue_certified_copy

            );
        $docs[$type]['upload'][] = $rec;
        
        }

        $rec = array(
            'dbid' => $form_annual_accout->id,
            'name' => $form_annual_accout->name,
            'savedLocation' => '',
            'required' => ($form_annual_accout->is_required == 'yes' ) ? true : false,
            'specific' => $form_annual_accout->specific_company_member_type,
            'type'   =>$this->slugify($form_annual_accout->name),
            'fee'    => $form_annual_accout->fee,
            'key' => $form_annual_accout->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_annual_accout->description,
            'issue_certified_copy' => $form_annual_accout->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;


        $rec = array(
            'dbid' => $form_23->id,
            'name' => $form_23->name,
            'savedLocation' => '',
            'required' => ($form_23->is_required == 'yes' ) ? true : false,
            'specific' => $form_23->specific_company_member_type,
            'type'   =>$this->slugify($form_23->name),
            'fee'    => $form_23->fee,
            'key' => $form_23->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_23->description,
            'issue_certified_copy' => $form_23->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

        $rec = array(
            'dbid' => $form_45_offshore->id,
            'name' => $form_45_offshore->name,
            'savedLocation' => '',
            'required' => ($form_45_offshore->is_required == 'yes' ) ? true : false,
            'specific' => $form_45_offshore->specific_company_member_type,
            'type'   =>$this->slugify($form_45_offshore->name),
            'fee'    => $form_45_offshore->fee,
            'key' => $form_45_offshore->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_45_offshore->description,
            'issue_certified_copy' => $form_45_offshore->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;


        $rec = array(
            'dbid' => $form_46_offshore->id,
            'name' => $form_46_offshore->name,
            'savedLocation' => '',
            'required' => ($form_46_offshore->is_required == 'yes' ) ? true : false,
            'specific' => $form_46_offshore->specific_company_member_type,
            'type'   =>$this->slugify($form_46_offshore->name),
            'fee'    => $form_46_offshore->fee,
            'key' => $form_46_offshore->key,
            // 'fee'    =>mt_rand(2000, 5000),
            'uploaded_path' => '',
            'comments' =>'',
            'description' => $form_46_offshore->description,
            'issue_certified_copy' => $form_46_offshore->issue_certified_copy,
            'doc_requested' => 'no',
            'admin_set'  => 'no',

         );

        $docs[$type]['upload'][] = $rec;

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

    function document_map_new($companyId,$company_type, $dirList, $secList,$secFirms ){
      
        $form_map = array(
           'form_map_id' =>array(),
           'form_map_fee' => array(
           ),
        );
    
        $docs = $this->documents();
        foreach($docs as $doc_type=>$doc_val ){
    
           foreach($doc_val['upload'] as $doc ){
    
              if($doc_type == $company_type  ){
   
    
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
                   
                }else if($doc['specific'] == 'name_change' ) {

                    $form3_company_recs = ChangeName::where('old_company_id', $companyId )
                    ->where('status', $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id)
                   ->orderby('id', 'DESC')->get();

                   if(isset($form3_company_recs[0]->new_company_id)) {
                       foreach($form3_company_recs as $form3_company_rec ){

                        $name_change_company_info = Company::where('id', $form3_company_rec->new_company_id)->first();

                        $form_map['form_map_fee']['name_change'][$form3_company_rec->new_company_id][$doc['dbid']] = array(
                            'val' =>floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                           'copies' => 0,
                           'original_val' => $payment_value,
                           'key'       => $payment_key,
                           'original_copies' => 1,
                           'for' => $doc['specific'],
                           'name_change_company' => $form3_company_rec->new_company_id,
                           'name_change_company_name' => $name_change_company_info->name. ' '. $name_change_company_info->postfix,
                           'issue_certified_copy' => $doc['issue_certified_copy'],
                           'doc_id' => $doc['dbid']
                        );    
                           
                       }

                   }

                    
                }else if($doc['specific'] == 'form_10') {
                    
                    $query = CompanyDocuments::query();
                    $query->where('document_id', $doc['dbid'] );
                    $query->where('company_id', $companyId );
                    $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                    $result_docs = $query->get();

                    if(isset($result_docs[0]->id)) {

                        foreach($result_docs as $result_doc) {
                            if( isset($result_doc->id) && $result_doc->id ) {

                                $form_map['form_map_fee']['form_10'][$doc['dbid']][$result_doc->id] = array(
                                    'val' =>floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value ),
                                   'copies' => 0,
                                   'original_val' => $payment_value,
                                   'key'       => $payment_key,
                                   'original_copies' => 1,
                                   'for' => $doc['specific'],
                                   'doc_row_id' =>$result_doc->id,
                                   'issue_certified_copy' => $doc['issue_certified_copy'],
                                   'doc_id' => $doc['dbid']
                                );    
    
        
                            }
                        }

                    }
                    
                }else {

                    if(!(  isset($doc['key']) && $doc['key']  && $doc['issue_certified_copy'] == 'yes') ) {
                        continue;
                    }


                    $query = CompanyDocuments::query();
                    $query->where('document_id', $doc['dbid'] );
                    $query->where('company_id', $companyId );
                    $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                    $result_doc = $query->first();

                    if( isset($result_doc->id) && $result_doc->id ) {

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


                    }


                    
    
                    
    
                }
    
               
              }
    
           }
    
            
        }
    
        return $form_map;
    }

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
                    'type' => ($director->is_srilankan) ? 'local' : 'foreign',
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
        ->get();
        $secs = array();

        if(count($sec_list )){
            foreach($sec_list as $sec){

            $address_id =  ($sec->foreign_address_id ) ? $sec->foreign_address_id : $sec->address_id;
            
            if(!$sec->foreign_address_id){
                $address = Address::where('id',$address_id)->first();
            }else{
              //  $address = ForeignAddress::where('id',$address_id)->first();
              $address = Address::where('id',$address_id)->first();
            }

            $rec = array(
                'id' => $sec->id,
                'type' => ($sec->is_srilankan) ? 'local' : 'foreign',
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
         ->get();
         $secFirms = array();

         if(count($sec_list )){
            foreach($sec_list as $sec){
    
            $address_id =  $sec->address_id;
            
            $address = Address::where('id',$address_id)->first();

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
        
            );
            $secFirms[] = $rec;
            }

        }

        /******share holder list *****/
        $shareholder_list = CompanyMember::where('company_id',$company_id)
        ->where('designation_type',$this->settings('SHAREHOLDER','key')->id)
        ->whereNull('company_member_firm_id' )
        ->where('status',1)
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


            }

            $rec = array(
                'id' => $shareholder->id,
                'type' => ($shareholder->is_srilankan) ? 'local' : 'foreign',
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
           'share' => $shareRec

       
           );
           $shFirms[] = $rec;
           }

       }
        return array(

            'directors' => $directors,
            'secs'      => $secs,
            'secFirms'  => $secFirms,
            'shs'       => $shareholders,
            'shFirms'   => $shFirms
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

            if(count($downloaded)){
                foreach($downloaded as $file ){
                      
                    $name = $file['name'];
                    $file_name_key = $file['file_name_key'];

                    $stakeholder_store = $this->stakeholder_store($companyId);

                    $company_info = Company::where('id',$companyId)->first();
                    
                    
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
                            'postfix' => $postfix_arr['postfix'],
                            'postfix_si' => $postfix_arr['postfix_si'],
                            'postfix_ta' => $postfix_arr['postfix_ta'],

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
                             'postfix' => $postfix_arr['postfix'],
                            'postfix_si' => $postfix_arr['postfix_si'],
                            'postfix_ta' => $postfix_arr['postfix_ta'],

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
                            'postfix' => $postfix_arr['postfix'],
                            'postfix_si' => $postfix_arr['postfix_si'],
                            'postfix_ta' => $postfix_arr['postfix_ta'],
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
                        'postfix' => $postfix_arr['postfix'],
                        'postfix_si' => $postfix_arr['postfix_si'],
                        'postfix_ta' => $postfix_arr['postfix_ta'],
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

           // print_r($uploaded);

            $generated_files = array(
                'other' => array(),
                'multiple_other1'=> array(),
                'multiple_other2'=> array(),
                'director' => array(),
                'sec'   => array(),
                'secFirm' => array(),
                'form_10' => array(),
                'name_change' => array(),
               
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

                        $query = CompanyDocuments::query();
                        $query->where('company_id', $companyId );
                        $query->where('document_id',$file['dbid'] );
                        $query->where('company_member_id', $director['id'] );
                        $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                        $result_doc = $query->first();

                        if(isset($result_doc->id) && $result_doc->id) {


                            $file_row = array();
                            $file_row['doc_comment'] = '';
                            $file_row['doc_status'] = 'DOCUMENT_PENDING';
                            $file_row['company_status'] = $company_status;
                            if($company_status == 'COMPANY_STATUS_REQUEST_TO_RESUBMIT'){


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

                    }

                }else if($file['specific']  == 'sec'){

                    foreach( $stakeholder_store['secs'] as $sec ){

                        $query = CompanyDocuments::query();
                        $query->where('company_id', $companyId );
                        $query->where('document_id',$file['dbid'] );
                        $query->where('company_member_id', $sec['id'] );
                        $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                        $result_doc = $query->first();

                        if(isset($result_doc->id) && $result_doc->id) {

                            $file_row = array();
                            $file_row['doc_comment'] = '';
                            $file_row['doc_status'] ='DOCUMENT_PENDING';
                            $file_row['company_status'] = $company_status;
                            if($company_status == 'COMPANY_STATUS_REQUEST_TO_RESUBMIT'){

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
                    }

                    foreach( $stakeholder_store['secFirms'] as $sec ){

                        $query = CompanyDocuments::query();
                        $query->where('company_id', $companyId );
                        $query->where('document_id',$file['dbid'] );
                        $query->where('company_firm_id', $sec['id'] );
                        $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                        $result_doc = $query->first();

                        if( isset($result_doc->id) && $result_doc->id ) {

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['doc_status'] ='DOCUMENT_PENDING';
                        $file_row['company_status'] = $company_status;
                        if($company_status == 'COMPANY_STATUS_REQUEST_TO_RESUBMIT'){

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

                }
                else if( $file['specific']  == 'name_change') {

                    $form3_company_recs = ChangeName::where('old_company_id', $companyId )
                    ->where('status', $this->settings('COMPANY_NAME_CHANGE_APPROVED','key')->id)
                   ->orderby('id', 'DESC')->get();

                   if(isset($form3_company_recs[0]->new_company_id)) {

                    foreach($form3_company_recs as $form3_company_rec) {
                        $query = CompanyDocuments::query();
                        $query->where('document_id', $file['dbid'] );
                        $query->where('company_id', $form3_company_rec->new_company_id );
                        $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                        $result_doc = $query->first();

                        if( isset($result_doc->id) && $result_doc->id ) {

                           
                            $file_row = array();
                            $file_row['doc_comment'] = '';
                            $file_row['company_status'] = $company_status;
                            $file_row['is_required'] = $file['required'];
                            $file_row['file_name'] = $file['name'];
                            $file_row['file_type'] = $file['type'];
                            $file_row['dbid'] = $file['dbid'];
                            $file_row['description'] = $file['description'];
                            $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                            $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                            $file_row['name_change_company'] = $form3_company_rec->new_company_id;

                            $name_change_company_info = Company::where('id', $form3_company_rec->new_company_id)->first();
                            $file_row['name_change_company_name'] = $name_change_company_info->name. ' '. $name_change_company_info->postfix;
                           
                            $generated_files['name_change'][] = $file_row;
    
                        }
                    }

                   }


                } else if($file['specific']  == 'form_10') { /// aaa

                    $query = CompanyDocuments::query();
                    $query->where('document_id', $file['dbid'] );
                    $query->where('company_id', $companyId );
                    $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                    $result_docs = $query->get();

                    if(isset($result_docs[0]->id)) {

                        foreach($result_docs as $result_doc) {
                            if( isset($result_doc->id) && $result_doc->id ) {
    
                                $file_row = array();
                                $file_row['doc_comment'] = '';
                                $file_row['company_status'] = $company_status;
                                $file_row['is_required'] = $file['required'];
                                $file_row['file_name'] = $file['name'];
                                $file_row['file_type'] = $file['type'];
                                $file_row['dbid'] = $file['dbid'];
                                $file_row['description'] = $file['description'];
                                $file_row['issue_certified_copy'] = $file['issue_certified_copy'];
                                $file_row['admin_set'] = isset($file['admin_set'])  ? $file['admin_set'] : 'no';
                                $file_row['doc_row_id'] = $result_doc->id;
                                $generated_files['form_10'][] = $file_row;
        
                            }
                        }

                    }
                    

                }
                else{

                    if(!(  isset($file['key']) && $file['key']  && $file['issue_certified_copy'] == 'yes') ) {
                        continue;
                    }


                    $query = CompanyDocuments::query();
                    $query->where('document_id', $file['dbid'] );
                    $query->where('company_id', $companyId );
                    $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                    $result_doc = $query->first();

                    if( isset($result_doc->id) && $result_doc->id ) {

                        $file_row = array();
                        $file_row['doc_comment'] = '';
                        $file_row['company_status'] = $company_status;
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
            }
            return $generated_files;
        
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

         function getProvincesDisctrictsCities() {

            $provinces = array();
            $districts = array();
            $cities = array();
            $gns = array();

           /* $provinces_cache = Cache::rememberForever('provinces_cache', function () {
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
              */
            return array(
                'provinces' => array(),
                'districts' => array(),
                'cities' => array(),
                'gns'   => array()
            );
            

             
        }

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
        return array(
            'level1' => array(),
            'level2' => array(),
            'level3' => array(),
            'level4' => array(),
            'level5' =>array()
        );
        /*
        $objective_cache_records = Cache::rememberForever('objective_cache_records', function () {
            return array(
                'level1' => $this->objective_level1(),
                'level2' => $this->objective_level2(),
                'level3' => $this->objective_level3(),
                'level4' => $this->objective_level4(),
                'level5' => $this->objective_level5()
            );
        });
        return $objective_cache_records;*/
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

    function saveNoOfPublicCopies(Request $request) {

        $copyRequest = $request->copiesArr;
        
        $companyId = $request->companyId;
        $loginUserEmail = $this->clearEmail($request->loginUser);
        $loginUserInfo = User::where('email', $loginUserEmail)->first();
        $userId = $loginUserInfo->id;

        if(is_array($copyRequest) && count($copyRequest)){

                $copyReq = new CompanyPublicRequest;
                $copyReq->company_id = $companyId;
                $copyReq->request_by = $userId;
                $copyReq->status = $this->settings('PRINT_PENDING','key')->id;
                $copyReq->save();
                $reqId = $copyReq->id;

                if(!( isset($copyReq->id) && intval($reqId)) ) {
                    return response()->json([
                        'message' => 'Copies request failed1.',
                        'status' =>false,
                        'req_id' => 0
                    ], 200);
                }


                foreach($copyRequest as $r ) {
    
                    if(!intval($r['copies'])) {
                        continue;
                    }

                    $noOfCopies = $r['copies'];

                    $query = CompanyDocuments::query();
                    $query->where('id', $r['row_id'] );
                    
                 /*   if( isset($r['member_id']) && intval($r['member_id'] ) ) {
                        $query->where('company_member_id', $r['member_id'] );
                        $query->where('company_id', $companyId );
                    }
                    else if( isset($r['firm_id']) && intval($r['firm_id'] ) ) {
                        $query->where('company_firm_id', $r['firm_id'] );
                        $query->where('company_id', $companyId );
                    }

                    else if( isset($r['new_company']) && $r['new_company']  ) { // name change list
                        $query->where('company_id', $r['new_company'] );
                    }
                    else if( isset($r['row_id']) && $r['row_id']  ) { // name change list
                        $query->where('id', $r['row_id'] );
                    }
                    else {
                        $query->where('company_id', $companyId );
                    } */
                    $query->where('status', $this->settings('DOCUMENT_SEALED','key')->id );
                    $result_doc = $query->first();

                    if(isset($result_doc->id)) {

                       $reqDoc = new CompanyPublicRequestDocument;
                       $reqDoc->request_id = $reqId;
                       $reqDoc->company_document_id = $result_doc->id;
                       $reqDoc->no_of_copies = $noOfCopies;
                       $reqDoc->save();

                    }

                }


                


                return response()->json([
                    'message' => 'Copies request success.',
                    'status' =>true,
                    'req_id' => $reqId
                ], 200);
            } else {

                return response()->json([
                    'message' => 'Copies request failed2.',
                    'status' =>false,
                    'req_id' => 0
                ], 200);
            }
    }


    function certifiedCopiesInfo($companyId){
        //$companyId = $request->companyId;

        $all_records = array();

        $records = CompanyDocuments::leftJoin('documents', 'company_documents.document_id', '=', 'documents.id')
                    ->leftJoin('company_changes','company_documents.company_id', '=', 'company_changes.new_company_id' )
                    ->leftJoin('companies','company_changes.new_company_id', '=', 'companies.id'  )
                    ->whereIn('documents.key', array('FORM_3', 'FORM_37B'))
                    ->where('company_changes.old_company_id', $companyId)
                    ->where('company_documents.status', $this->settings('DOCUMENT_SEALED', 'key')->id)
                    ->select(

                        'documents.name as document_name',
                        'companies.name as company_name',
                        'company_documents.updated_at as sealed_date',
                        'company_documents.id as row_id'
                    );

        $name_changes =  $records->get()->toArray();

        if(isset($name_changes[0])) {
            foreach($name_changes as $rec ) {
                $row['document_name'] = $rec['document_name'];
                $row['changed_name'] = $rec['company_name'];
                $row['sealed_date'] = date('Y-m-d',strtotime($rec['sealed_date']));
                $row['member_name'] = '';
                $row['row_id'] = $rec['row_id'];
                $row['copies'] = 0;
                $row['val'] = floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value );
                $all_records[] = $row;
            }
        }



        $records = CompanyDocuments::leftJoin('documents', 'company_documents.document_id', '=', 'documents.id')
                    ->leftJoin('companies','company_documents.company_id', '=', 'companies.id'  )
                    ->whereNotIn('documents.key', array('FORM_3', 'FORM_37B'))
                    ->where('company_documents.company_id', $companyId)
                    ->where('company_documents.status', $this->settings('DOCUMENT_SEALED', 'key')->id)
                    ->select(

                        'documents.name as document_name',
                        'companies.name as company_name',
                        'company_documents.updated_at as sealed_date',
                        'company_documents.company_member_id as member_id',
                        'company_documents.company_firm_id as firm_id',
                        'company_documents.id as row_id'
                    );

        $docs =  $records->get()->toArray();

                    if(isset($docs[0])) {
                        foreach($docs as $rec ) {

                            $row['member_name'] = '';

                            if($rec['member_id']){
                                $aa = CompanyMember::where('id', $rec['member_id'])->first();
                                $row['member_name'] = isset($aa->id) ? $aa->first_name . ' ' . $aa->last_name : '';
                            }

                            if($rec['firm_id']){
                                $bb = CompanyFirms::where('id', $rec['firm_id'])->first();
                                $row['member_name'] = isset($bb->id) ? $bb->name : '';

                            }

                            $row['document_name'] = $rec['document_name'];
                            $row['changed_name'] = '';
                            $row['sealed_date'] = date('Y-m-d',strtotime($rec['sealed_date']));
                            $row['row_id'] = $rec['row_id'];
                            $row['copies'] = 0;
                            $row['val'] = floatval( $this->settings('PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM','key')->value );
                            $all_records[] = $row;
                        }
                    }


        return $all_records;



    }





}