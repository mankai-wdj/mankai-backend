<?php

namespace App\Http\Controllers;

use App\Events\ClientSignal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function videoChatFileSave(Request $request) {
        if($request->hasFile("file")) {
            $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
            $path = $request->file('file')->storeAs('files/'.$request->room_id.'/'.date('Y-m-d').'/', $fileName, 's3');
            $file_path = [];
              // return $request->file('file')->getSize();
            array_push($file_path, (object)array('path'=>Storage::url($path), 'size' =>$request->file('file')->getSize(), 'name' =>$request->file('file')->getClientOriginalName(), 'type'=>explode(".",$request->file('file')->getClientOriginalName())[count(explode(".",$request->file('file')->getClientOriginalName()))-1]));
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
