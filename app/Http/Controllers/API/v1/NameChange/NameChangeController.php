<?php

namespace App\Http\Controllers\API\v1\NameChange;

use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use Illuminate\Http\Request;
use PDF;
use App\ChangeName;
use App\CompanyMember;
use App\CompanyFirms;

use App\Documents;
use App\CompanyDocuments;

class NameChangeController extends Controller
{
    use _helper;

    public function getNameChangeformWithfill(Request $request)
    {

        if ($request->data['oldRefid'] != null && $request->data['newRefid'] != null) {
            
            $arrId = explode("-", $request->data['dirId']);
            
            ChangeName::where('new_company_id', $request->data['newRefid'])->update(['resolution_date' =>  date('Y-m-d', strtotime($request->data['resolutiondate']))]);

           
           
            $user = $this->getAuthUser();
            $oldData = Company::leftJoin('people', 'companies.created_by', '=', 'people.id')
                ->leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                ->where('companies.id', $request->data['oldRefid'])->first();
            $newData = Company::where('id', $request->data['newRefid'])->first();
            $changeName = ChangeName::where('new_company_id', $request->data['newRefid'])->first();



            //update change id for  prior approval letter
            $prior_approval_doc = Documents::where('key', 'NAME_CHANGE_LETTER')->first();
            $update_company_doc = array(
                'change_id' => isset($changeName->id) && $changeName->id ? $changeName->id : null
            );
            CompanyDocuments::where('company_id', $request->data['newRefid'])
                            ->where('document_id', $prior_approval_doc->id)
                            ->update($update_company_doc);



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
                'refId' => $oldData->registration_no,
                'oldName' => $oldData->name . ' ' . $oldData->postfix,
                'newName' => $newData->name . ' ' . $newData->postfix,
                'username' => $user->title . $user->first_name . ' ' . $user->last_name,
                'email' =>  $user->email,
                'telephonenumber' =>  $user->telephone,
                'address' =>  $addaress,
                'resolution_date' => date('Ymd', strtotime($changeName->resolution_date))
            ];
            $data = $data + $memberData;
            $pdf = PDF::loadView('vendor.form.name-change.form3', $data);
            return $pdf->stream('from3.pdf');
        } else {
            return response()->json(['error' => 'Can\'t access for your request'], 200);
        }
    }


    function updateCourtInfo(Request $request ) {

        $update_arr = array(
            'court_status' => $request->court_status,
            'court_name' => $request->court_name,
            'court_case_no' => $request->court_case_no,
            'court_date' => ($request->court_date) ? date('Y-m-d', strtotime($request->court_date)) : null,
            'court_penalty' => floatval( $request->court_penalty),
            'court_period' => $request->court_period,
            'court_discharged' => $request->court_discharged
            
        );
       $update =  ChangeName::where('new_company_id', $request->new_company_id)->update($update_arr);

       return response()->json(['status' => ($update)], 200);
    }


    function getResolutionDateGapisExeed(Request $request ){
            
           if($request->type == 'MODULE_NAME_RESERVATION' || $request->type == 'MODULE_NAME_RENEWAL') {
            return response()->json(['status' => false, 'gap'=>0, 'penaly_charge'=>0 ], 200);
           }

           $new_company_id = $request->company_id;

           $changeNameData = ChangeName::where('new_company_id', $new_company_id)->first();

        
          
           if( 
               $changeNameData->court_status == 'yes' &&
               $changeNameData->court_name &&
               $changeNameData->court_case_no &&
               $changeNameData->court_date
             //  $changeNameData->court_penalty && floatval($changeNameData->court_penalty)

            ) {
                return response()->json(['status' => false, 'gap'=>null, 200, 'penaly_charge'=>0]);
            }

           if(!isset($changeNameData->resolution_date)) {
             return response()->json(['status' => false, 'gap'=>null, 200, 'penaly_charge'=>0]);
           }
           if(!$changeNameData->resolution_date) {
            return response()->json(['status' => false, 'gap'=>null,'penaly_charge'=>0], 200);
           }

           $form_3 = Documents::where('key', 'FORM_3')->first();

           $form3_upload_data = CompanyDocuments::where('company_id', $new_company_id)
                                        ->where('document_id', $form_3->id )
                                        ->orderBy('id', 'DESC')
                                        ->first();
           if( !isset($form3_upload_data->updated_at)) {
            return response()->json(['status' => false, 'gap'=>null, 'penaly_charge'=>0 ], 200 );
           }

           $updated_date = strtotime($form3_upload_data->updated_at);
           $resolution_date = strtotime($changeNameData->resolution_date);


           $date_gaps = ($updated_date - $resolution_date) / (60*60*24);
           $date_gaps = intval($date_gaps);


           $min_date_gap = 10;
           $increment_gap_dates = 30;
           $init_panalty = floatval( $this->settings('PAYMENT_PENALTY_NAME_CHANGE_INITIAL','key')->value );
           $increment_penalty = floatval( $this->settings('PAYMENT_PENALTY_NAME_CHANGE_INCREMENT','key')->value );
           $max_penalty = floatval( $this->settings('PAYMENT_PENALTY_NAME_CHANGE_MAX','key')->value );
   
           $increment_gaps = 0;
   
           $penalty_value = 0;
   
            if($date_gaps < $min_date_gap ) {
                return response()->json(['status' => true, 'gap'=>$date_gaps, 'penaly_charge'=>0 ], 200);
            }
   
            $increment_gaps = ( $date_gaps % $increment_gap_dates == 0 ) ? $date_gaps / $increment_gap_dates : intval($date_gaps / $increment_gap_dates) + 1;
            $penalty_value  = $penalty_value + $init_panalty;
   
               if($increment_gaps > 1 ) { // more than 30 days, it means 2 months
                   $penalty_value = $penalty_value + $increment_penalty * ($increment_gaps - 1 ) ;
               }
   
               $penalty_value =  ( $penalty_value > $max_penalty ) ? $max_penalty : $penalty_value;

               return response()->json(['status' => true, 'gap'=>$date_gaps, 'penaly_charge'=>$penalty_value ], 200);



    }

    function checkHasSetResolutionDate(Request $request ) {
        $new_company_id = $request->company_id;
        $changeNameData = ChangeName::where('new_company_id', $new_company_id)->first();

        if($changeNameData->resolution_date) {
            return response()->json(['status' => true], 200);
        }else{
            return response()->json(['status' => false], 200);
        }
    }

    public function setResubmit(Request $request)
    {
        try {
            $data = ChangeName::find($request->data['refId']);
            $data->status = $this->settings('COMPANY_NAME_CHANGE_RESUBMITTED', 'key')->id;
            $data->save();

            $oldCom = Company::find($data->new_company_id);
            $oldCom->status = $this->settings('COMPANY_NAME_CHANGE_RESUBMITTED', 'key')->id;
            $oldCom->save();

            return response()->json(['success' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
