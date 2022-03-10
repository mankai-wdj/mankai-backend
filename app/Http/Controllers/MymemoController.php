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
        return Mymemo::all();
    }

    public function MymemoDelete($id) {
        $mymemoDelete = Mymemo:: find($id);
        $mymemoDelete->delete();
    }

    public function storeMymemo(Request $request){
        $request->vaildate([
            'mymemo' => 'required|string',
            'mymemotitle' => 'required|string'
        ]);

        $newmymemo = new Mymemo();
        $newmymemo->id = $request->id;
        $newmymemo->user_id = $request->user_id;
        $newmymemo->mymemo = $request->mymemo;
        $newmymemo->mymemotitle = $request->mymemotitle;
        $newmymemo->save();

        return $newmymemo;
    }

    public function editMymemo(Request $request, $id){
        $editmymemo = Mymemo::where('id',$id)
            ->update(
                [
                    'mymemotitle' => $request->mymemotitle,
                    'mymemo' => $request->mymemo,
                ]);

        return $editmymemo;
    }

    

    // public function MymemoUpdate(Request $request)) {
    //     $mymemoUpdate = Mymemo::find($request->id);
    // }


    public function PostmemoShow() {
        $postmemo =DB::table('postmemos')
            ->join('users','postmemos.user_id',"=",'users_id')
            ->select('postmemos.*','users.name')
            ->latest();
        return $postmemo;
    }
}
