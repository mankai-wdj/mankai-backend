<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chatmemo;
use App\Models\Mymemo;
use App\Models\Postmemo;
use Illuminate\Support\Facades\DB;

class MymemoController extends Controller
{
    public function test() {
        return "wda";
    }


    public function MymemoShow() {
        return Mymemo::select('user_id','mymemo','mymemotitle')->get();
    }

    public function PostmemoShow() {
        $postmemo =DB::table('postmemos')
            ->join('users','postmemos.user_id',"=",'users_id')
            ->select('postmemos.*','users.name')
            ->latest();
        return $postmemo;
    }
}
