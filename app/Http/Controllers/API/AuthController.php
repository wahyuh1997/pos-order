<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:50',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);
        
        if($validator->fails()){
            return $this->return_failed($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return $this->return_success('Data berhasil didaftarkan!',$user);
    }
    
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('username', 'password')))
        {
            return response()
            ->json(['message' => 'Unauthorized'], 401);
        }
        
        $user = User::where('username', $request['username'])->firstOrFail();
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // return response()
        //     ->json(['message' => 'Hi '.$user->name.', welcome to home','access_token' => $token, 'token_type' => 'Bearer', ]);
        $data = [
            'access_token' => $token, 'token_type' => 'Bearer', 
            'detail_user' => $user
        ];
        return $this->return_success('Hi '.$user->name.', kamu sudah login',$data);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $message = 'You have successfully logged out and the token was successfully deleted';

        return $this->return_success($message,[]);
    }

    public function set_role()
    {
        $data = [
            'kasir', 'dapur', 'owner'
        ];

        return $this->return_success('',$data);
    }



}
