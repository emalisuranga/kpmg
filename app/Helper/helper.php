<?php

namespace App\Http\Helper;

use App\Company;
use App\Order;
use App\Mail\MailTemplate;
use App\People;
use App\Rule;
use App\Setting;
use App\Tender;
use App\TokenIssues;
use App\TenderApplication;
use App\NumberSequence;
use Cache;
use Mail;
use Auth;
trait _helper
{

    function genarateCompanyId(){
        $number = $this->getNumberSequence('COMPANY_ID');
        $id = Date('ymd').$number->next_no;
        return $id;
    }

    function getNumberSequence($key){
        $number = NumberSequence::where('key',$key)->first();
        NumberSequence::where('key',$key)->update(['next_no' => $number->next_no + 1]);
        return $number;
    }

    public function genarateTenderId()
    {
        $id = Date('ymd') . rand(1000, 9999);
        $is_exists = Tender::where('id', $id)->count();
        if ($is_exists > 0) {
            $id = $this->genarateTenderId();
        }
        return $id;
    }
    
	public function genarateInvoiceNumber()
    {
        $count = Order::whereDate('created_at', '=', date('Y-m-d'))->count() + 1;
        return 'INV' . date("ymd") . str_pad($count, 3, "0", STR_PAD_LEFT);
    }

    public function genarateUserRefNo()
    {
        return strtoupper(uniqid());
    }

    public function settings($key, $type = "type")
    {
        static $settings;
        if ($type == 'key') {
            $data = array();
            $settings = Cache::remember('settings_keys', 24 * 60, function () {
                $data = array();
                $sett = Setting::leftJoin('setting_types', 'settings.setting_type_id', '=', 'setting_types.id')
                //->where('setting_types.output','string')
                    ->select('setting_types.key as type', 'settings.id', 'settings.key', 'settings.value', 'settings.value_si', 'settings.value_ta')
                    ->get();
                foreach ($sett as $value) {
                    $data[$value->key] = (object) ['id' => $value->id, 'value' => $value->value, 'value_si' => $value->value_si, 'value_ta' => $value->value_ta];
                }
                return $data;
            });
        } else if ($type == 'id') {
            $settings = Cache::remember('settings_by_id', 24 * 60, function () {
                $data = array();
                $sett = Setting::select('settings.id', 'settings.key', 'settings.value', 'settings.value_si', 'settings.value_ta')->get();
                foreach ($sett as $value) {
                    $data[$value->id] = (object) ['id' => $value->id, 'key' => $value->key, 'value' => $value->value, 'value_si' => $value->value_si, 'value_ta' => $value->value_ta];
                }
                return $data;
            });
        } else {
            $settings = Cache::remember('settings_types', 24 * 60, function () {
                $data = array();
                $sett = Setting::leftJoin('setting_types', 'settings.setting_type_id', '=', 'setting_types.id')
                    ->where('setting_types.output', 'array')
                    ->select('setting_types.key as type', 'settings.id', 'settings.key', 'settings.value', 'settings.value_si', 'settings.value_ta')
                    ->orderBy('settings.id')
                    ->get();
                foreach ($sett as $value) {
                    $data[$value->type][] = (object) ['id' => $value->id, 'key' => $value->key, 'value' => $value->value, 'value_si' => $value->value_si, 'value_ta' => $value->value_ta];
                }
                return $data;
            });
        }
        return is_array($settings) ? !empty($settings[$key]) ? $settings[$key] : "" : "";
    }

    public function ship($email, $template, $link = null, $token = null, $attachdata = null, $attchName = null, $subject = 'General',$applicantName = null, $bcc = false, $bccEmail = null)
    {
        $data = [
            'activationLink' => $link, 
            'template' => $template, 
            'token' => $token, 
            'attachdata' => $attachdata,
            'attchName' => $attchName,
            'subject' => $subject,
            'applicantName' => $applicantName,
        ];

        if($bcc){
            if(is_null($bccEmail)){
                $bccEmail = env('BCC_MAIL', '');
            }
            return Mail::to($email)->bcc($bccEmail)->send(new MailTemplate($data));
        }
        return Mail::to($email)->send(new MailTemplate($data));
    }

    public function clearEmail($email)
    {
        return strtolower(preg_replace('/"+/', '', $email));
    }

    public function getRestrictedWord()
    {
        $word = array();
        $data = Rule::select('word')->get();
        foreach ($data as $key => $value) {
            $word[$key] = strtoupper($value->word);
        }
        return $word;
    }

    public function getAuthUser($email = null)
    {
        if($email == null){
            $email = Auth::guard('api')->user()->email;
        }
        return People::leftjoin('users', 'users.people_id', '=', 'people.id')
            ->leftJoin('addresses', 'people.address_id', '=', 'addresses.id')
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
                'people.dob',
                'people.sex',
                'people.is_srilankan',
                'people.occupation',
                'people.profile_pic',
                'people.is_srilankan',
                'people.status',
                'people.created_at',
                'people.updated_at',
                'users.is_activation',
                'addresses.id as address_id',
                'addresses.address1',
                'addresses.address2',
                'addresses.city',
                'addresses.district',
                'addresses.province',
                'addresses.country',
                'addresses.postcode',
                'addresses.gn_division',
                'users.is_tender_user as tender_user',
                'users.stakeholder_role'
            )
            ->first();
    }


    public function getTenderUser($id)
    {
        return TenderApplication::find($id);
    }

    public function getPagesCount($file)
    {
        if (file_exists($file)) {
            if ($handle = @fopen($file, "rb")) {
                $count = 0;
                $i = 0;
                while (!feof($handle)) {
                    if ($i > 0) {
                        $contents .= fread($handle, 8152);
                    } else {
                        $contents = fread($handle, 1000);
                        if (preg_match("/\/N\s+([0-9]+)/", $contents, $found)) {
                            return $found[1];
                        }
                    }
                    $i++;
                }
                fclose($handle);
                if (preg_match_all("/\/Type\s*\/Pages\s*.*\s*\/Count\s+([0-9]+)/", $contents, $capture, PREG_SET_ORDER)) {
                    foreach ($capture as $c) {
                        if ($c[1] > $count) {
                            $count = $c[1];
                        }

                    }
                    return $count;
                }
            }
        }
        return 0;
    }

    public function setSecToken($email, $activation_token, $token_type)
    {
        return TokenIssues::create([
            'email' => $email,
            'token' => $activation_token,
            'token_type' => $token_type,
        ]);
    }

    public function getSecToken($email, $token_type, $token = null)
    {
        $data = new TokenIssues();
         if(!is_null($email)){
             $data = $data->where('email', $this->clearEmail($email));
         }
        $data = $data->where('token_type', $token_type);
        if (!is_null($token)) {
            $data = $data->where('token', $token);
        }
        $data = $data->first();
        if ($data) {
             $data->delete();
        }

        return $data;
    }


    public function requestLink($email, $url = '/user/redirect/activation', $subject = null, $template = 'verification')
    {
        $email = $this->clearEmail($email);
        $verifyToken = $this->getSecToken($email, $this->settings('TOKEN_ACTIVATION', 'key')->id);
        if (is_null($verifyToken)) {
            $activation_token = str_random(64);
            $token = $this->setSecToken($email, $activation_token,  $this->settings('TOKEN_ACTIVATION', 'key')->id);
        } else {
            $verifyToken->updated_at = date('Y-m-d H:i:s');
            $verifyToken->save();

            $activation_token = $verifyToken->token;
        }

        $link = env('FRONT_APP_URL', '') . $url . '?email=' . $email . '&token=' . $activation_token;
        $this->ship($email, $template, $link,null, null, null, $subject);
    }


}
