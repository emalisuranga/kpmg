<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Address;
use App\Http\Controllers\API\v1\Auth\Access\AuthorizesToken;
use App\Http\Controllers\Controller;
use App\People;
use App\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\CountValidator\Exception;

use App\AppointmentOfAdmins;
use App\CompanyChangeRequestItem;
use App\AppointedAdmins;
use App\Company;
use App\CompanyItemChange;
use App\UserAttachedCompanies;

class RegisterController extends Controller
{
    use AuthorizesToken;

    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */
    // private $client;

    // public function __construct()
    // {
    //     $this->client = Client::where('password_client', 1)->first();
    // }
    /**
     * Get a validator for an incoming registration request
     *
     * @return \Illuminate\Http\Response
     */
    protected function Validator(array $data)
    {
        // return Validator::make($data, [
        //     'first_name' => 'required|max:50',
        //     'last_name' => 'required|max:50',
        //     'email' => 'required|email|max:255|unique:users',
        //     'password' => 'required|min:3|confirmed',
        // ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \Illuminate\Http\Response
     */
    protected function create(Request $request)
    {

        

        \DB::beginTransaction();
        $information = Json_decode($request['Info']);


       $stakeholder_type = 0;
       if(isset($information->registerData->details->isAdminUser) && $information->registerData->details->isAdminUser){
           $stakeholder_type = $this->settings('ADMINISTRATOR', 'key')->id;
       }
       if(isset($information->registerData->details->isOtherStakeholder) && $information->registerData->details->isOtherStakeholder){
        $stakeholder_type = $this->settings('LAWYER_BANKER_OTHER_STAKEHOLDER', 'key')->id;
    }


        try {

            foreach ($information->registerData->address as $key => $value) {
                
                if ($value->address01 != null) {
                    $addNumber[$key] = Address::create([
                        'address1' => $value->address01,
                        'address2' => $value->address02,
                        'gn_division' => isset($value->gndivision) ? $value->gndivision : '',
                        'city' => $value->city,
                        'district' => $value->district,
                        'province' => $value->province,
                        'country' => $value->country,
                        'postcode' => $value->postCode,
                    ]);
                }
            }

           
            
            $member = People::create([
                'title' => $information->registerData->details->title,
                'first_name' => $information->registerData->details->firstname,
                'last_name' => $information->registerData->details->lastname,
                'other_name' => $information->registerData->details->otherName,
                'address_id' => (!isset($addNumber[0]) ? null : $addNumber[0]->id),
                'foreign_address_id' => (!isset($addNumber[1]) ? null : $addNumber[1]->id),
                'nic' => $information->registerData->details->nic,
                'passport_no' => (empty($information->registerData->details->passportid) ? null : $information->registerData->details->passportid),
                'passport_issued_country' => (empty($information->registerData->details->passportIssueCountry) ? null : $information->registerData->details->passportIssueCountry),
                'telephone' => $information->registerData->details->telephoneNumber,
                'mobile' => $information->registerData->details->mobileNumber,
                'occupation' => $information->registerData->details->occupation,
                'is_srilankan' => $information->registerData->details->isSrilanka,
                'email' => $information->credential->email
            ]);
            

            if(isset($information->registerData->details->adminAssignCompanies) &&
              is_array($information->registerData->details->adminAssignCompanies) &&
              count($information->registerData->details->adminAssignCompanies)
              ) {
                
                  $assignedCompanies = $information->registerData->details->adminAssignCompanies;

                  

                  foreach($assignedCompanies as $company_id) {


                    if($stakeholder_type == $this->settings('ADMINISTRATOR', 'key')->id ) {

                        $request_id = $this->valid_admin_request_operation($company_id);

                        $record = AppointmentOfAdmins::where('company_id', $company_id)
                                    ->where('request_id', $request_id)
                                    ->first();


                        $newSr = new AppointedAdmins;
                        $newSr->first_name = $information->registerData->details->firstname;
                        $newSr->last_name = $information->registerData->details->lastname;
                        $newSr->address_id =(!isset($addNumber[0]) ? null : $addNumber[0]->id);
                        $newSr->foreign_address_id =  (!isset($addNumber[1]) ? null : $addNumber[1]->id);
                        $newSr->office_address_id =  null;
                        $newSr->is_srilankan =  ($information->registerData->details->nic) ?  'yes' : 'no';
                        $newSr->nic = strtoupper($information->registerData->details->nic);
                        $newSr->passport_no = (empty($information->registerData->details->passportid) ? null : $information->registerData->details->passportid);
                        $newSr->passport_issued_country = (empty($information->registerData->details->passportIssueCountry) ? null : $information->registerData->details->passportIssueCountry);
                        $newSr->tel = $information->registerData->details->telephoneNumber;
                        $newSr->mobile =$information->registerData->details->mobileNumber;
                        $newSr->email = $information->credential->email;
                        $newSr->date_of_appointment = null;
                        $newSr->status =  $this->settings('APPOINTMENT_OF_ADMIN','key')->id;
                        $newSr->appointment_record_id =  $record->id;
                        $newSr->save();
                        $new_sr_id = $newSr->id;

                        $change = new CompanyItemChange;
                        $change->request_id = $request_id;
                        $change->changes_type = $this->settings('ADD','key')->id;
                        $change->item_id = $new_sr_id;
                        $change->item_table_type = $this->settings('APPOINTMENT_OF_ADMIN_TABLE','key')->id;
                        $change->save();
                        $change_id = $change->id;

                    }
 

                  }
              }

              

            if ($request->file('avater') != null) {
                $storagePath = 'user/' . $member->id . '/avater';
                $path = $request->file('avater')->storeAs($storagePath, uniqid() . '.jpg', 'sftp');

                People::where('id', $member->id)->update(['profile_pic' => $path]);
            }
            

            $user = User::create([
                'people_id' => $member->id,
                'email' => $information->credential->email,
                'password' => bcrypt($information->credential->password_confirmation),
                'is_activation' => $this->settings('COMMON_STATUS_PENDING', 'key')->id,
            ]);
            User::where('id', $user->id)->update(['stakeholder_role' => $stakeholder_type]);

           
             if( isset($assignedCompanies) && is_array($assignedCompanies ) && count($assignedCompanies)) {
                foreach($assignedCompanies as $company_id) {

                    $attach  = new UserAttachedCompanies;
                    $attach->user_id = $user->id;
                    $attach->company_id = $company_id;
                    $attach->save();
                }
             }
           
           
            // 'is_activation' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id,
            // 'is_activation' => $this->settings('COMMON_STATUS_PENDING', 'key')->id,
     
            \DB::commit();
       } catch (\ErrorException $e) {
            \DB::rollBack();
           return response()->json(['error' => $e->getMessage()], 400);
        }
      
        return $user;
    }



    private function has_admin_request_record($company_id) {

        $accepted_request_statuses = array(
            $this->settings('APPOINTMENT_OF_ADMIN_APPROVED','key')->id,
            $this->settings('APPOINTMENT_OF_ADMIN_REJECTED','key')->id
        );
       
        $record_count = AppointmentOfAdmins::where('company_id', $company_id)
                                  ->whereNotIn('status', $accepted_request_statuses )
                                   ->count();
        if( $record_count === 1 ) {
            $record = AppointmentOfAdmins::where('company_id', $company_id)
            ->whereNotIn('status', $accepted_request_statuses )
             ->first();
    
            return $record->request_id;
        } else {
            return false;
        }
    }
    
      private function valid_admin_request_operation($company_id){
    
      
        $accepted_request_statuses = array(
            $this->settings('APPOINTMENT_OF_ADMIN_APPROVED','key')->id,
            $this->settings('APPOINTMENT_OF_ADMIN_REJECTED','key')->id
        );
        $request_type =  $this->settings('APPOINTMENT_OF_ADMIN','key')->id;
    
        $exist_request_id = $this->has_admin_request_record($company_id);
    
        if($exist_request_id) {
    
            $request_count = CompanyChangeRequestItem::where('request_type',$request_type)
                               ->where('company_id', $company_id)
                               ->where('id', $exist_request_id)
                               ->whereNotIn('status', $accepted_request_statuses )
                               ->count();
            if($request_count !== 1) { // request not in processing or  resubmit stage
                return false;
            } else {
                return $exist_request_id;
            }
             
        } else {
                $company_info = Company::where('id', $company_id)->first();
                $year = date('Y',time());
    
                $request = new CompanyChangeRequestItem;
                $request->company_id = $company_id;
                $request->request_type = $request_type;
                $request->status = $this->settings('APPOINTMENT_OF_ADMIN_PROCESSING','key')->id;
                $request->request_by = isset($company_info->created_by) ? $company_info->created_by : 1 ;
                $request->save();
                $request_id =  $request->id;
    
                $record = new AppointmentOfAdmins;
                $record->request_id = $request_id;
                $record->company_id = $company_id;
                $record->date_of = date('Y-m-d', time());
                $record->status = $this->settings('APPOINTMENT_OF_ADMIN_PROCESSING','key')->id;
                $record->save();
                $record_id =  $record->id;
    
                if($record_id && $request_id ) {
                    return $request_id;
                }else{
                    return false;
                }
    
        }
        
    }

    public function register(Request $request)
    {

      //  $infor = Json_decode($request['Info']);
      //  print_r( $infor);
        try {
            $infor = Json_decode($request['Info']);

            $isHas = User::where('email', $this->clearEmail($infor->credential->email))->first();

         //   print_r($isHas);
        //    return response()->json(['error' => 'Error : ereee', 400]);
         //   die();


            if($isHas == null){
                $password = Json_decode($request->all()['Info'])->credential->password_confirmation;

                $user = $this->create($request);

                if ($user) {

                    $request->request->add([
                        'email' => $user->email,
                        'password' => $password,
                        'reg' => true,
                    ]);

                    $this->requestLink($user->email, '/user/redirect/activation', 'Account Registration');

                    return $this->getLogUserCredentials($request);
                }
            }
            return response()->json(['status' => false], 400);
        } catch (\ErrorException $e) {
            return response()->json(['error' => 'Error : ' . $e->getMessage()], 400);
        }
    }

    public function requestLinkWithToken($email){
        $this->requestLink($email, '/user/redirect/activation', 'Account Registration');
    }

    public function verifyAccount(Request $request)
    {

        $email = $request->email;
        $token = $request->token;

        $verifyToken = $this->getSecToken($email, $this->settings('TOKEN_ACTIVATION', 'key')->id, $token);

        if (!is_null($verifyToken)) {

            $interval = (new DateTime($verifyToken->updated_at))->diff(new DateTime());
            $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + ($interval->i);

            if ($minutes < 60000) {
                    $user = User::where('email', $email)->first();
                    if ($user->is_activation == $this->settings('COMMON_STATUS_ACTIVE', 'key')->id) {
                        return response()->json(['warning' => 'User are already activated!'], 200);
                    } else {
                        if(!isset($request->mgr)){
                            $user->is_activation = $this->settings('COMMON_STATUS_ACTIVE', 'key')->id;
                            $user->save();
                            $verifyToken->delete();

                            $other_stakeholder_types = array($this->settings('ADMINISTRATOR', 'key')->id,
                                                      $this->settings('LAWYER_BANKER_OTHER_STAKEHOLDER', 'key')->id
                                                    );
                            if(!in_array($user->stakeholder_role,$other_stakeholder_types)) {
                                $this->ship($email, 'general', null, null, null, null, 'Thank You');
                            }
                           
                        }
                        return response()->json(['success' => 'Successfully Activated your Account. Please Login'], 200);
                    }
            } else {
                $verifyToken->delete();
                return response()->json(['error' => 'Your account activation link has been expired.'], 400);
            }
        } else {
            return response()->json(['error' => 'Your account activation link has been expired. Please request new activation link.'], 400);
        }

    }

    public function checkExisitsEmail(Request $request)
    {
        try {
            return User::where('email', $request->email)->first();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    protected function migrateUserCreate(Request $request)
    {

        \DB::beginTransaction();
        $information = Json_decode($request['Info']);

        try {
            foreach ($information->registerData->address as $key => $value) {
                    if($value->addressId != null){
                        $addNumber[$key] = Address::updateOrCreate(
                            [
                                'id' =>   $value->addressId
                            ],
                            [
                            'address1' => $value->address01,
                            'address2' => $value->address02,
                            'gn_division' => is_object($value->gndivision) ?  $value->gndivision->description_en : $value->gndivision,
                            'city' => is_object($value->city) ?  $value->city->description_en : $value->city,
                            'district' => is_object($value->district) ?  $value->district->description_en : $value->district,
                            'province' =>   is_object($value->province) ?  $value->province->description_en : $value->province,
                            'country' => is_object($value->country) ?  $value->country->description_en : $value->country,
                            'postcode' => $value->postCode,
                        ]);
                }
            }

            $member = People::updateOrCreate(
                [
                    'email' =>  $this->clearEmail($information->credential->email)
                ],
                [
                'title' => $information->registerData->details->title,
                'first_name' => $information->registerData->details->firstname,
                'last_name' => $information->registerData->details->lastname,
                'other_name' => $information->registerData->details->otherName,
                'address_id' => (!isset($addNumber[0]) ? null : $addNumber[0]->id),
                'foreign_address_id' => (!isset($addNumber[1]) ? null : $addNumber[1]->id),
                'nic' => $information->registerData->details->nic,
                'passport_no' => (empty($information->registerData->details->passportid) ? null : $information->registerData->details->passportid),
                'passport_issued_country' => (empty($information->registerData->details->passportIssueCountry) ? null : $information->registerData->details->passportIssueCountry),
                'telephone' => $information->registerData->details->telephoneNumber,
                'mobile' => $information->registerData->details->mobileNumber,
                'occupation' => $information->registerData->details->occupation,
                'is_srilankan' => $information->registerData->details->isSrilanka,
                'email' => $information->credential->email
            ]);

            if ($request->file('avater') != null) {
                $storagePath = 'user/' . $member->id . '/avater';
                $path = $request->file('avater')->storeAs($storagePath, uniqid() . '.jpg', 'sftp');

                People::where('id', $member->id)->update(['profile_pic' => $path]);
            }

            $user = User::updateOrCreate(
                [
                    'email' =>  $this->clearEmail($information->credential->email),
                    'is_activation' => $this->settings('COMMON_STATUS_PENDING', 'key')->id
                ],
                [
                'password' => bcrypt($information->credential->password_confirmation),
                'migrated_status' => 0
            ]);

            // 'is_activation' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id,
            // 'is_activation' => $this->settings('COMMON_STATUS_PENDING', 'key')->id,
            \DB::commit();
        } catch (\ErrorException $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return $user;
    }

    public function setMigrateRegister(Request $request){
        $infor = Json_decode($request['Info']);
        try {

            if($this->clearEmail($infor->credential->email) == ''){
                return response()->json(['status' => 'Unauthorised request'], 400);
            }

            $isUser = User::where('email',$this->clearEmail($infor->credential->email))
                ->where('is_activation', $this->settings('COMMON_STATUS_PENDING', 'key')->id)
                ->where('migrated_status', 1)
                ->first();

            if($isUser){

                $user = $this->migrateUserCreate($request);

                if ($user) {
                    $user->is_activation = $this->settings('COMMON_STATUS_ACTIVE', 'key')->id;
                    $user->save();

                    $this->ship($user->email, 'migrate-success', null, null, null, null, 'Thank You');

                    return response()->json(['status' => 'success'], 200);
                }
                return response()->json(['error' => 'Unauthorised request'], 400);
            }

            return response()->json(['error' => 'Unauthorised request'], 400);
        } catch (\ErrorException $e) {
            return response()->json(['error' => 'Error : ' . $e->getMessage()], 400);
        }
    }

    public function verifyMigrateAccount(Request $request)
    {

        $email = $request->email;
        $token = $request->token;
        $verifyToken = $this->getSecToken($email, $this->settings('TOKEN_ACTIVATION', 'key')->id, $token);

        if (!is_null($verifyToken)) {

            $interval = (new DateTime($verifyToken->updated_at))->diff(new DateTime());
            $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + ($interval->i);

            if ($minutes < 60000) {
                    $user = User::where('email', $email)->first();
                    if($user){
                        if ($user->is_activation == $this->settings('COMMON_STATUS_ACTIVE', 'key')->id) {
                            return response()->json(['warning' => 'User are already activated!'], 400);
                        } else {
                            //$verifyToken->delete();

                            return  $this->getUserInfor($email);
                        }
                    }else{
                        return response()->json(['error' => 'Sorry. Can\'t find your account.'], 400);
                    }
            } else {
                $verifyToken->delete();
                return response()->json(['error' => 'Your account activation link has been expired.'], 400);
            }
        } else {
            return response()->json(['error' => 'Your account activation link has been expired. Please request new activation link.'], 400);
        }

    }

    public function getUserInfor($email){
        $user = People::leftjoin('users', 'users.people_id', '=', 'people.id')
                ->leftJoin('settings', 'settings.id', '=', 'people.title')
                ->where('users.email', $this->clearEmail($email))
                ->select(
                    'users.people_id as id',
                    'users.id as userid',
                    'settings.id as key',
                    'settings.value as title',
                    'people.first_name',
                    'people.last_name',
                    'people.other_name',
                    'people.nic',
                    'people.passport_no',
                    'people.passport_issued_country',
                    'people.telephone',
                    'people.mobile',
                    'people.email',
                    'people.address_id',
                    'people.foreign_address_id',
                    'people.is_srilankan',
                    'people.occupation',
                    'people.profile_pic'
                )
                ->first();

        $localAddress = Address::where('id',$user->address_id)->first();
        $foreignAddress = Address::where('id',$user->foreign_address_id)->first();

        $data['user'] = $user;
        //$data['user']['is_srilankan'] = 'no';
        $data['localAddress'] = $localAddress;
        $data['foreignAddress'] = $foreignAddress;

        return $data;

    }
}
