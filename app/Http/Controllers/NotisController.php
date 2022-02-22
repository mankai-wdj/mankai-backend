<?php

namespace App\Http\Controllers;

use App\Models\Noti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotisController extends Controller
{
    public function addNoti(Request $request) {
        $noti = Noti::create([
            'noti_title'=>$request->noti_title,
            'noti_message'=>$request->noti_message,
            'noti_link'=>$request->noti_link,
            'user_id'=>$request->user_id,            
        ]);

        return response()->json([
            'status'=>200,
            'noti'=>$noti,
            'message'=>"success"
        ]);
    }

    public function NotiIndex() {
       
        $noti =Noti::where('user_id',Auth::user()->id)->orderBy('created_at',"desc")->get();
        $navNoti = Noti::where('user_id',Auth::user()->id)->where('read',0)->take(5)->get();
        return response()->json([
            'status'=>200,
            'noti'=>$noti,
            'navNoti'=>$navNoti,
            'total_count'=>$noti->count(),
            'count'=>$navNoti->count(),
        ]);
    }

    public function allRead() {

    }
}
