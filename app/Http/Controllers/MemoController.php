<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemoController extends Controller
{
    public function PostMemo(Request $request) {
        $memo = new Memo();
        $memo -> memo = $request ->content;
        $memo -> user_id = $request -> writer;
        $memo -> save(); 
    }
    public function ShowMemo($user_id){
        $memos = DB::table('memos')
        ->where("user_id","=",$user_id)->get();

        return $memos;
    }
    public function editMemoView(Request $request) {
        $memo = Memo::find($request->memo_id);
        return $memo;
    }
    public function GetMyBoard($user_id){
        $boards = DB::table('free_boards')
        ->where("user_id","=",$user_id)
        ->join('users', 'free_boards.user_id', "=", 'users.id')
        ->select('free_boards.*','users.name')->get();

        return $boards;
    }
}
