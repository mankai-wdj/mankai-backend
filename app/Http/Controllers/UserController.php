<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = User::find($request->user_id);
        $user->name = $request->name;
        $user->country = $request->country;
        $user->description = $request->description;
        if (gettype($request->image) != 'string') {
            $path = "";
            $path = $request->file('image')->store('image', 's3');
            $profile = Storage::url($path);
            $user->profile = $profile;
            $user->save();
        } else {
            $profile = $request->image;
            $user->profile = $profile;
            $user->save();
        }

        return $profile;
    }

    public function getUser()
    {
        if(Auth::check()) {
            $user = Auth::user();
            $res = response()-> json([
                'status' => 200,
                'user' => $user,
            ]);
    
            return $res;
        } else {
            $user = null;
            $res = response()-> json([
                'status' => 401,
                'user' => $user,
            ]);
    
            return $res;
        }


    }
}
