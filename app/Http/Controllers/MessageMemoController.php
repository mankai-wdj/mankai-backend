<?php

namespace App\Http\Controllers;

use App\Models\MessageMemo;
use Illuminate\Support\Facades\DB;

class MessageMemoController extends Controller
{

    public function showMessageMemos($user_id)
    {
        $chatMemos = DB::table('message_memos')
            ->where('memo_user_id', $user_id)
            ->join('users', 'message_memos.user_id', '=', 'users.id')
            ->select('message_memos.*', 'users.name')
            ->get();

        return $chatMemos;
    }

    public function deleteMessageMemos($message_id)
    {
        $chatMemos = MessageMemo::find($message_id);
        $chatMemos->delete();


        return "삭제성공";
    }
}
