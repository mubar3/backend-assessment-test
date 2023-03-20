<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController
{
    public function reg_user($email)
    {
        if(User::where('email',$email)->first()){
            return 'failed, data exist';
        }
        
        $data=[
            'name' => $email,
            'email' => $email,
            'password' => bcrypt('asd')
        ];
        if(User::create($data)){
            return 'done, username : '.$email;
        }else{
            return 'failed, data exist';
        }
    }

    public function login(Request $r){
        $email = strip_tags($r->email);
        $pass  = strip_tags($r->password);

        $validasi = Validator::make($r->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if($validasi->fails()){
            return response()->json(['status'=>false,'data'=>$validasi->errors()]);
        }

        if(Auth::attempt(['email' => $email, 'password' => $pass])){
            $user = Auth::user();
            $response = [
                'token' => $user->createToken('GeoApp')->accessToken,
                'email' => $user->email,
                'nama'  => $user->name,
            ];

            return response()->json(['status'=>true,'data'=>$response]);
        }else{
            return response()->json(['status'=>false,'data'=>'Email atau Password Salah']);
        }
    }
    
    public function logout() {
        try {
            Auth::user()->token()->revoke();
            Auth::user()->token()->delete();
            return response()->json(['status'=>true,'data'=>'Logout berhasil']);
        } catch (Exception $e) {
            return response()->json(['status'=>false,'data'=>'Terjadi Kesalahan']);
        }
    }
}
