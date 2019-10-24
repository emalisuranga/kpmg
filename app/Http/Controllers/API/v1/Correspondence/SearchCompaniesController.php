<?php
namespace App\Http\Controllers\API\v1\Correspondence;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyPostfix;
use App\Company;
use App\CompanyCertificate;
use App\Address;
use App\Setting;
USE App\SettingType;
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
use App\CompanyStatus;
use Storage;
use Cache;
use App;
use URL;
use App\Http\Helper\_helper;
use PDF;
use App\CompanyChangeRequestItem;
use App\CompanyItemChange;
use App\ChangeName;

use App\Correspondence;

class SearchCompaniesController extends Controller
{
    use _helper;

    private $items_per_page;

    function __construct() {
        
        $this->items_per_page = 3;
        $this->corr_items_per_page = 20;
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

       // print_r($companies);
        $companyList = array();
        $user = $this->getAuthUser();
        if(isset($companies[0]->id)){
            foreach($companies as $c ) {


               
                $request_type =  $this->settings('CORRESPONDENCE','key')->id;
                $ongoingCorrespondence = CompanyChangeRequestItem::where('request_type',$request_type)
                                       ->where('company_id', $c->id)
                                      // ->whereIn('status', array($this->settings('CORRESPONDENCE_PROCESSING','key')->id, $this->settings('CORRESPONDENCE_RESUBMIT','key')->id))
                                       ->where('request_by', $user->userid)
                                       ->get()->toarray();
                $Correspondence = array();
                $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;

                if(count($ongoingCorrespondence)) {

                    foreach($ongoingCorrespondence as $corr ) {

                        $external_comment_query = CompanyStatus::where('company_id',$corr['company_id'])
                        ->where('comment_type', $external_comment_type_id )
                        ->where('request_id', $corr['id'])
                        ->orderBy('id', 'DESC')
                        ->first();
                        $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                        ?  $external_comment_query->comments
                        : '';

                        $row = array(
                            'company_id' => $corr['company_id'],
                            'request_id' => $corr['id'],
                            'status' => $this->settings($corr['status'],'id')->key,
                            'date' => $corr['updated_at'],
                            'comment' => $external_global_comment,
                        );

                        $Correspondence[] = $row;
    
                    }

                }
                
            //    $companyCertificate = CompanyCertificate::where('company_id', $c->id)
                                      //        ->where('is_sealed', 'yes')
                                      //        ->first();
            //    $certificate_no = isset($companyCertificate->registration_no) && $companyCertificate->registration_no ? $companyCertificate->registration_no : '';
                
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

                $row['correspondence'] = $Correspondence;


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





    function getUserCorrespondenceRequests(Request $request ){


        $name_part = trim( $request->namePart);
        $registration_no = trim( $request->registration_no);
        $request_id = trim(intval($request->request_id));
        $page = intval($request->page);
        $offset = $page*$this->corr_items_per_page;
       
        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        
        $query = null;

         $user = $this->getAuthUser();
         $request_type =  $this->settings('CORRESPONDENCE','key')->id;
         $query = CompanyChangeRequestItem::query();
         $query->leftJoin('companies', 'company_change_requests.company_id', '=', 'companies.id');
         $query->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id');

         if ($name_part) {
            $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');
         }
 
         if ($registration_no) {
             $query->where('company_certificate.registration_no', 'ilike', '%' . strtoupper($registration_no ). '%');
         }
         if ($request_id) {
            $query->where('company_change_requests.id',  $request_id);
         }
         $query->where('company_change_requests.request_by',$user->userid );
         $query->where('company_change_requests.request_type',$request_type );
         $query->orderby('company_change_requests.id', 'DESC');

         $query->select(
            'companies.id as company_id',
            'company_change_requests.id as request_id',
            'companies.name as company_name',
            'company_certificate.registration_no as reg_no',
            'company_change_requests.status as status',
            'company_change_requests.updated_at as updated_at'
         );

         $correspondence = $query->limit($this->corr_items_per_page)->offset($offset)->get();

         $CorrespondenceList = array();

         if(isset($correspondence[0]->company_id)){
             foreach($correspondence as $corr) {

                $external_comment_query = CompanyStatus::where('company_id',$corr->company_id)
                ->where('comment_type', $external_comment_type_id )
                ->where('request_id', $corr->request_id)
                ->orderBy('id', 'DESC')
                ->first();
                $external_global_comment = ( isset($external_comment_query->comments) && $external_comment_query->comments ) 
                ?  $external_comment_query->comments
                : '';

                $row = array(
                    'company_id' => $corr->company_id,
                    'company_name' => $corr->company_name,
                    'reg_no' => $corr->reg_no,
                    'request_id' => $corr->request_id,
                    'status' => $this->settings($corr->status ,'id')->key,
                    'date' => date('Y-m-d H:i:s', strtotime($corr->updated_at)),
                    'comment' => $external_global_comment,
                );
        
                $CorrespondenceList[] = $row;

             }
         }

         return response()->json([
            'message'       => "Successfully listed correspondences.",
            'CorrespondenceList'    => $CorrespondenceList,
            'status'        => true,
            'count'         => $query->count(),
            'total_pages'   => $this->getCorrespondencePaginatePages($name_part,$registration_no, $request_id),
            'current_page'  => ($page+1)
            ], 200);

            
    }


    private function getCorrespondencePaginatePages($name_part,$registration_no, $request_id) {
        $name_part = trim( $name_part);
        $registration_no = trim( $registration_no);
        $request_id = trim(intval($request_id));
      

        $external_comment_type_id = $this->settings('COMMENT_EXTERNAL','key')->id;
        
        $query = null;

        
         $user = $this->getAuthUser();
         $request_type =  $this->settings('CORRESPONDENCE','key')->id;
         $query = CompanyChangeRequestItem::query();
         $query->leftJoin('companies', 'company_change_requests.company_id', '=', 'companies.id');
         $query->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id');

         if ($name_part) {
            $query->where('companies.name', 'ilike', '%' . strtoupper($name_part ). '%');
         }
 
         if ($registration_no) {
             $query->where('company_certificate.registration_no', 'ilike', '%' . strtoupper($registration_no ). '%');
         }
         if ($request_id) {
            $query->where('company_change_requests.id',  $request_id);
         }
         $query->where('company_change_requests.request_by',$user->userid );
         $query->where('company_change_requests.request_type',$request_type );
         $query->orderby('company_change_requests.id', 'DESC');

         $result_count = $query->count();

         return  ($result_count % $this->corr_items_per_page == 0  )
                          ? $result_count / $this->corr_items_per_page
                          : intval($result_count / $this->corr_items_per_page) + 1;
    
    }

} // end class