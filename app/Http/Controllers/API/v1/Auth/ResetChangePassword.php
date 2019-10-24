<?php

namespace App\Http\Controllers\API\v1\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Helper\_helper;
use Illuminate\Support\Facades\DB;

class ResetChangePassword extends Controller
{
    use _helper;

    public function isCheckOldPassword(Request $request)
    {

        $User = User::where('email', '=', $this->clearEmail($request->email))->first();

        if (!(Hash::check($request->password, $User->password))) {
            return response()->json('true', 200);
        } else {
            return response()->json('false', 200);
        }
    }

    public function changePassword(Request $request)
    {
        if (strcmp($request['data']['oldPassword'], $request['data']['confirmPassword']) == 0) {
            return response()->json(["error", "New Password cannot be same as your current password. Please choose a different password."],200);
        }

        DB::table('users')->where('email', '=', $this->clearEmail($request['data']['email']))->update(['password' => bcrypt($request['data']['confirmPassword'])]);
        return response()->json(["success", "Password changed successfully !"], 200);
    }

}
