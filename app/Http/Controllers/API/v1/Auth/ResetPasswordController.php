<?php
namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\PasswordReset;
use App\Http\Helper\_helper;
use Illuminate\Support\Facades\Validator;
use DateTime;
class ResetPasswordController extends Controller
{
    use _helper;

    public function validateEmail(array $data){
        return Validator::make($data, [
            'email' => 'required|string|email',
        ]);
    }

    // public function validateEmailWithToken(array $data){
    //     return Validator::make($data, [
    //         'email' => 'required|string|email',
    //         'token' => 'required|string',
    //         'password' => 'required|string|confirmed',
    //     ]);
    // }

    public function sendLink(Request $request)
    {
        try{

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['warning' => 'We couldn\'t find your account with that information.'], 200);
            } else {

                $mguser = User::where('email', $this->clearEmail($request->email))->where('migrated_status', 1)->first();

                if($mguser){
                    $this->requestLink($user->email,'/migrate/infor', 'Updating your profile', 'migrate');
                    return response()->json(['status' => true], 200);
                }else{

                    $this->validateEmail($request->all())->validate();

                    $passwordReset = PasswordReset::Create([
                        'email' => $user->email,
                        'token' => str_random(60)
                    ]);

                    if ($passwordReset->save()) {
                        $link = env('FRONT_APP_URL', '') . '/forgot/password/reset?email=' . $user->email . '&token=' . $passwordReset->token.'&sp=uir';

                        $this->ship($user->email, 'forgotpassword', $link, null, null, null, 'Reset Password');

                        return response()->json(['status' => false, 'message' => 'We have e-mailed your password reset link!'], 200);
                    }
                }

            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function resetMyPassword(Request $request, LoginController $login)
    {
        try{

            // $this->validateEmailWithToken($request->all())->validate();

            $passwordReset = PasswordReset::where('token', $request->token)->where('email',$request->email)->first();
            if(!is_null($passwordReset)){
                $interval = (new DateTime($passwordReset->created_at))->diff(new DateTime());
                $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + ($interval->i);

               // if ($minutes < 15 || $passwordReset->type != $this->settings('TOKEN_NOT_EXPIRE', 'key')->id) {
                if ($minutes < 15 || $passwordReset->type == $this->settings('TOKEN_NOT_EXPIRE', 'key')->id) {
                    // if (!$passwordReset) {
                    //     return response()->json(['error' => 'Unknown requested.', 'status' => false], 200);
                    // } else {
                        $user = User::where('email', $request->email)->first();
                        if (!$user) {
                            return response()->json(['error' => 'We can\'t find a user with that e-mail address.', 'status' => false], 200);
                        } else {
                            $user->password = bcrypt($request->password);
                            if($user->save()){
                                $passwordReset->delete();
                                return $login->authenticate($request);
                            }else{
                                return response()->json(['error' => 'Unsuccessfully reset your password.', 'status' => false], 200);
                            }
                        }
                    // }
                }else{
                    $passwordReset->delete();
                    return response()->json(['error' => 'Your password reset link has been expired.', 'status' => false], 200);
                }
            }else{
                return response()->json(['error' => 'Unauthorized request','status' => false], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

}
