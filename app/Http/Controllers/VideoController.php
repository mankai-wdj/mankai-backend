<?php

namespace App\Http\Controllers;

use App\Events\ClientSignal;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function videoChatFileSave(Request $request) {
        if($request->hasFile("file")) {
            $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
            $request->file('file')->storeAs('/public/files/'.$request->room_id.'/'.date('Y-m-d').'/', $fileName);
            $file_path = [];
              // return $request->file('file')->getSize();
            array_push($file_path, (object)array('path'=>'files/'.$request->room_id.'/'.date('Y-m-d').'/'.$fileName, 'size' =>$request->file('file')->getSize(), 'name' =>$request->file('file')->getClientOriginalName(), 'type'=>explode(".",$request->file('file')->getClientOriginalName())[count(explode(".",$request->file('file')->getClientOriginalName()))-1]));
            $file_path = json_encode($file_path);
            return $file_path;
        } else {
          $file_path = [];
          array_push($file_path, (object)array('path'=>'files/'.$request->room_id.'/'.date('Y-m-d').'/', 'size' =>1000, 'name' =>$request->file('file')->getClientOriginalName(), 'type'=>explode(".",$request->file('file')->getClientOriginalName())[count(explode(".",$request->file('file')->getClientOriginalName()))-1]));
          $file_path = json_encode($file_path);
          return $file_path;
        }
    }
}
