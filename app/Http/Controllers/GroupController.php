<?php

namespace App\Http\Controllers;
use App\Models\Group;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function ShowGroup(){
        $groups=Group::all();
        return $groups;
    }

    public function PostGroup(Request $request){
        $group = new Group;
        $path = $request->file('img')->store('images','s3');
        $url = Storage::url($path);

        $group->logoImage=  $url;
        $group->category = $request->category;
        $group->name = $request -> text;
        $group->save();

        return $url;
    }

    public function ShowGroupDetail($group_id){
        $group = Group::find($group_id);
        return $group;
    }

}
