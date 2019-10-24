<?php

namespace App\Http\Controllers\API\v1\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users;
use App\Http\Helper\_helper;
use Storage;
use App\Address;
use App\People;
class UserController extends Controller
{
  use _helper;

  public function getUser()
  {
    return $this->getAuthUser();
  }

  public function getAvater(Request $request)
  {
    return Storage::disk('sftp')->get($request->path);
  }

  public function updateUserProfile(Request $request) {
    
    \DB::beginTransaction();
    
    $information = Json_decode($request['Info']);

    try {
        foreach ($information->address as $key => $value) {
            if(!is_null($value->id)){
              $addNumber = Address::find($value->id);
            }else{
              $addNumber = new Address();
            }
            $addNumber->address1 = $value->address01;
            $addNumber->address2 = $value->address02;
            $addNumber->gn_division = is_object($value->gndivision) ?  $value->gndivision->description_en : $value->gndivision;
            $addNumber->city = is_object($value->city) ?  $value->city->description_en : $value->city;
            $addNumber->district = is_object($value->district) ?  $value->district->description_en : $value->district;
            $addNumber->province = is_object($value->province) ?  $value->province->description_en : $value->province;
            $addNumber->country = is_object($value->country) ?  $value->country->description_en : $value->country;
            $addNumber->postcode = $value->postCode;
            $addNumber->save();
        }

        $member = People::find($information->details->people);
        $member->title = $information->details->title;
        $member->first_name = $information->details->firstName;
        $member->last_name = $information->details->lastName;
        $member->other_name = $information->details->otherName;
        $member->telephone = $information->details->telephoneNumber;
        $member->mobile = $information->details->mobileNumber;
        $member->occupation = $information->details->occupation;
        $member->address_id = $addNumber->id;
        $member->save();
        
        if ($request->file('avater') != null) {
            $deleted = Storage::disk('sftp')->delete($member->profile_pic);
            $storagePath = 'user/' . $member->id . '/avater';
            $path = $request->file('avater')->storeAs($storagePath, uniqid() . '.jpg', 'sftp');
            People::where('id', $member->id)->update(['profile_pic' => $path]);
        }
        
        \DB::commit();
    } catch (\ErrorException $e) {
        \DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 401);
    }
    return $this->getAuthUser();
  }
}
