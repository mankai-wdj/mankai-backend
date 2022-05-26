<?php

namespace App\Http\Controllers;

use App\Models\Noti;
use App\Models\Room;
use App\Models\User;
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

    public function userTokenSet(Request $request, $id) {
        $user = User::find($id);
        $user->fcm_token = $request->fcm_token;
        $user->save();
        return "fcm token set success";
    }

    public function messageNoti(Request $request) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        // $user = User::find($request->title);
        $room = Room::find($request->room_id);
        $user = User::find($request->user_id);
        $data = [
            "to" => $request->token,
            "notification" => [
                "title" => $user->name,
                "body" => $request->body,
                "click_action" => "https://mankai.shop/chat",
            ],
            "data" => [
                "type" => $request->type,
                "user" => $user,
                "room" => $room,
            ]
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $request->serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);

    }
}
