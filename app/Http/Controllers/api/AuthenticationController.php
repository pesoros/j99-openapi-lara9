<?php
   
namespace App\Http\Controllers\api;
   
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
   
class AuthenticationController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $result['token'] =  $user->createToken('MyApp')->plainTextToken;
        $result['name'] =  $user->name;
   
        return $this->sendResponse($result, 'User register successfully.');
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        if(Auth::attempt(['email' => $email, 'password' => $password])){ 
            $expiredTime = now()->addDay(7);
            $ability = ['*'];
            $user = Auth::user(); 
            $token=  $user->createToken($email, $ability, $expiredTime); 

            $result['name'] =  $user->name;
            $result['token'] = $token->plainTextToken;
            $result['token_expired_at'] = $token->accessToken->expires_at;

            return $this->sendResponse($result, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }
}