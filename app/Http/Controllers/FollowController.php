<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request) {
        $user = User::find($request->user_id);
        $to_user = User::find($request->to_user_id);

        $user->following()->toggle($to_user);
        return $user;

    }
}
