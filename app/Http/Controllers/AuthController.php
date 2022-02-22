<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'email'=>'required|email|max:191|unique:users,email',
            'password'=>'required|min:6|required_with:password_confirmation|same:password_confirmation',

        ]);


        if($validator->fails()) {
            return response()->json([
                'error'=>$validator->errors()
            ]);
        } else {
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password)
            ]);

            $token = $user->createToken($user->email.'_Token')->plainTextToken;
            
            return response()->json([
                'status'=>200,
                'username'=>$user->name,
                'token'=>$token,
                'message'=>"success"
            ]);
        }
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email'=>'required|email|max:191',
            'password'=>'required|min:8'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status'=>400,
                'error'=>$validator->errors()
            ]);
        } else {

            $user = User::where('email', $request->email)->first();

            if(! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'=>401,
                    'message'=>"잘못된 정보입니다",
                    'error'=> true,
                ]);
            } else {
                $token = $user->createToken($user->email.'_Token')->plainTextToken;

                return response()->json([
                    'status'=>200,
                    'user'=>$user,
                    'token'=>$token,
                    'message'=>"로그인 성공"
                ]);
            }

        }
    }

    public function logout() {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status'=>200,
            'message'=>'success'
        ]);
    }

    public function getUsers() {
        if (!auth()->user()) {
            return response()->json([
                'message'=>'please login',
                'status'=>401,
            ]);
        };
        $user = auth()->user();
        if($user->position === 'admin') {
            return response()->json([
                'user' => $user,
                'users' => User::all(),
                'status'=>200,
                'message'=>'success'
            ]);
        } else {
            return response()->json([
                'user' => $user,
                'status'=>401,
                'message'=>'you are not admin'
            ]);
        }
    }
}
