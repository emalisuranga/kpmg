<?php

namespace App\Http\Controllers\API\v1\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthRefreshToken;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\v1\Auth\Access\AuthorizesToken;
use App\User;

class LoginController extends Controller
{
    use AuthorizesToken;

    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
    }

    protected function validateLogin(array $request)
    {
        return Validator::make($request, [
            'email' => 'required',
            'password' => 'required',
        ]);
    }

    public function authenticate(Request $request)
    {
        $user = User::where('email', $this->clearEmail($request->email))->where('migrated_status', 1)->first();
     
        if($request->clEmail == true){
            if(!is_null($user)){
                $this->requestLink($user->email,'/migrate/infor', 'Updating your profile', 'migrate');
                return response()->json(['status' => true], 200);
            }else{
                return response()->json(['status' => false], 200);
            }
        }else{

            $this->validateLogin($request->all())->validate();

            $request->request->add(['reg' => true]);
            
            return $this->getLogUserCredentials($request);
        }
    }

    protected function refreshTokenValidate(array $request)
    {
        return Validator::make($request, [
            'refresh_token' => 'required',
        ]);
    }

    public function logout()
    {
        $this->guard()->logout();
        return response()->json($this->prepareResult(200, 'success', [], "Successfully logged out"), 200);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }
   

}
