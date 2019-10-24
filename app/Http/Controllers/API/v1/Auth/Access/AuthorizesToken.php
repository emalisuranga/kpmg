<?php

namespace App\Http\Controllers\API\v1\Auth\Access;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery\CountValidator\Exception;
use Illuminate\Support\Facades\Auth;
use phpseclib\Crypt\Hash;
use App\People;
use App\Http\Helper\_helper;
trait AuthorizesToken
{
    use _helper;

    public function getAccessTokenWithUser($user, $token)
    {
        return $scopes = [
            'user' => $user,
            'accessToken' => $token,
        ];
    }

    public function getLogUserCredentials(Request $request)
    {
        if(!isset($request->reg)){
            $request->request->add( [
                'is_activation' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id
            ]);
            $credentials = array('email', 'password','is_activation');
        }else{
            $credentials = array('email', 'password');
        }
    
        $credentials = $request->only($credentials);
  
        if ($token = $this->guard()->attempt($credentials)) {

            $user = $this->getAuthUser($this->guard()->user()->email);
            
            return response()->json($this->prepareResult(200, $this->getAccessTokenWithUser($user, $token), [], "User Verified"), 200);

        }
        return response()->json(['error' => 'Unauthorized Service request'], 401);

    }

    private function prepareResult($status, $data, $errors, $msg)
    {
        return ['status' => $status, 'data' => $data, 'message' => $msg, 'errors' => $errors];
    }

    public function me()
    {
        return response()->json($this->guard()->user());
    }
    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
  
    public function guard()
    {
        return Auth::guard('api');
    }
}
