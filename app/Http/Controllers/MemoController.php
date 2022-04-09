<?php

namespace App\Http\Controllers;

use App\Models\AllMemo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MemoImage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MemoController extends Controller
{
    public function storePostMemo(Request $request)
    {
        $postMemo = new AllMemo();
        $postMemo->user_id = $request->user_id;
        $postMemo->content_text = $request->content_text;
        $postMemo->memo_title = $request->memo_title;

        $postMemo->save();


        // $images = DB::table('free_board_images')
        //     ->where('free_boards_id', $memo_post_id)
        //     ->get();


        // foreach ($images as $image) {
        //     $memoImages = new MemoImage();
        //     $memoImages->url = $image->url;
        //     $memoImages->memo_id = $postMemo->id;
        //     $memoImages->save();
        // }

        return $postMemo;
    }

    public function showMemos($user_id)
    {
        $user = User::find($user_id);

        // Log::showMemosinfo($user->myMemos()->get());

        return $user->myMemos()->get();
    }
    //Memo-PostMemo-Read

    public function editPostMemos(Request $request, $post_id)
    {
        $postMemo = AllMemo::where('post_id', $post_id)
            ->update(['content_text' => $request->content_text]);

        return $postMemo;
    }
    //Memo-PostMemo-Update

    public function deletePostMemos($id)
    {

        $Memo = AllMemo::find($id);

        $Memo->delete();

        return "삭제성공백엔드";
    }
    //Memo-Memo-Delete(공통)

    public function getMemoImages($memo_id)
    {
        Log::info($memo_id);
        $images = MemoImage::where('memo_id', $memo_id)->get();
        return $images;
    }

}
