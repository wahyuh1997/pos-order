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
            'password' => 'required|string'
        ]);
        
        if($validator->fails()){
            return $this->return_failed($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return $this->return_success('Data berhasil didaftarkan!',$user);
    }
    
    public function login(Request $request)
    {
        // return $request;
        if (!Auth::attempt($request->only('username', 'password')))
        {
            return $this->return_failed('Username atau password salah',[]);
        }
        
        $user = User::where('username', $request['username'])->firstOrFail();
        
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

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

    public function get_user($username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();
            
            return $this->return_success('',$user);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }
    
    public function get_all_user()
    {
        try {
            $user = User::select('*')->orderBy('created_at', 'desc')->get();
            return $this->return_success('',$user);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }

    public function edit_user($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            $valid = [
                'name' => 'required|string|max:50',
                'role' => 'required|string|max:255'
            ];

            $update = ['name' => $request->name,
                'role' => $request->role];

            if ($user->username != $request->username && strlen(trim($request->username)) > 0) {
                $valid['username'] =  'required|string|unique:users';
                $update['username'] = $request->username;
            }
            $validator = Validator::make($request->all(),$valid);

            if($validator->fails()){
                return $this->return_failed($validator->errors());
            }

            $user->update($update);
            
            if (strlen($request->role) > 1) {
                $user->update([
                    'role' => $request->role
                ]);
            }

            $user = User::findOrFail($id);

            return $this->return_success('data berhasil diubah!',$user);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }

    public function change_password(Request $request)
    {
        try {
            $user = User::where('username', $request->username)->firstOrFail();
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
        
        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            return $this->return_success('Update password berhasil',[]);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }
    
    public function reset_password(Request $request)
    {
        // return $request;
        try {
            $user = User::where('username', $request->username)->firstOrFail();
            
            $user->update([
                'password' => Hash::make('12345678'),
            ]);

            return $this->return_success('password di reset menjadi : 12345678',$user);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }
    
    public function delete_user(Request $request)
    {
        try {
            $user = User::where('username', $request->username)->firstOrFail();
            
            $user->delete();

            return $this->return_success('user dihapus',[]);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }



}
