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
        // 얘가 follower
        $to_user = User::find($request->to_user_id);
        // 얘가 나.

        $user->following()->toggle($to_user);
        return $user;
    }

    public function getFollows($id)
    {
        $followers = User::find($id)->following()->get();

        return $followers;
    }

    public function getFollowings($id)
    {
        $followings = User::find($id)->followed()->get();

        return $followings;
    }

    public function getFollow($id)
    {
        $name = User::find($id);

        return $name;
    }

    public function getFollows($id) {
        return User::find($id)->following()->get();
    }
}
