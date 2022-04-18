<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function store(Request $request)
    {
        $user = User::find($request->user_id);
        $to_user = User::find($request->to_user_id);

        $user->following()->toggle($to_user);
        return $user;
    }

    public function getFollows($id)
    {
        $followings = User::find($id)->following()->get();
        // followings로 바꾸기

        return $followings;
    }

    public function getFollowers($id)

    {
        $followers = User::find($id)->followers()->get();

        return $followers;
    }

    public function getFollow($id)
    {
        $name = User::find($id);

        return $name;
    }
}
