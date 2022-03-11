<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\FreeBoard;
use App\Models\FreeBoardLike;
use App\Models\FreeBoardMemo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoardController extends Controller
{
    public function Test()
    {

        return "wda";
    }

    public function showMyPosts($user_id)
    {
        $boards = DB::table("free_boards")
            ->where("free_boards.user_id", $user_id)
            ->get();

        foreach ($boards as $board) {
            $images = DB::table('free_board_images')
                ->where('free_board_images.free_boards_id', $board->id)
                ->get();

            $comments = DB::table('comments')
                ->where('comments.freeboard_id', $board->id)
                ->get();

            $board->images = $images;
            $board->comments = $comments;
        }


        return $boards;
    }
    // myPosts-Read

    public function editMyPosts(Request $request, $post_id)
    {
        $board = DB::table('free_boards')
            ->where('post_id', $post_id)
            ->get();

        $board->content_text = $request->content_text;
        $board->save();

        $existedImages = DB::table('free_board_images')
            ->where('post_id', $post_id)
            ->get();

        $updatedImages = $request->images;

        $board->content_text = $request->content_text;
    }
    // myPosts-Update

    // myPosts-Delete

    public function storePostMemo(Request $request, $post_id, $user_id, $memo_user_id)
    {
        $postMemo = new FreeBoardMemo();
        $postMemo->user_id = $user_id;
        $postMemo->memo_user_id = $memo_user_id;
        $postMemo->content_text = $request->content_text;
        $postMemo->category = $request->category;
        $postMemo->post_id = $post_id;

        $postMemo->save();

        return $postMemo;
    }
    //Memo-PostMemo-Create

    public function showPostMemos($user_id)
    {
        $postMemos = DB::table('free_board_memos')
            ->where('user_id', $user_id)
            ->join('users', 'free_board_memos.user_id', '=', 'users.id')
            ->select('free_board_memos.*', 'users.name')
            ->get();

        return $postMemos;
    }
    //Memo-PostMemo-Read

    public function editPostMemos(Request $request, $post_id)
    {
        $postMemo = FreeBoardMemo::where('post_id', $post_id)
            ->update(['content_text' => $request->content_text]);

        return $postMemo;
    }
    //Memo-PostMemo-Update

    public function deletePostMemos($post_id)
    {
        $postMemo = FreeBoardMemo::find($post_id);
        $postMemo->delete();

        return "삭제성공";
    }
    //Memo-PostMemo-Delete



    public function Store(Request $request)
    {
        $request->validate([
            'selectedImages' => 'required_without:textfieldvalue',
            'textfieldvalue' => 'required_without:selectedImages'
        ]);

        $free_board = new Freeboard();
        $free_board->user_id = $request->user["id"];
        if ($request->textfieldvalue != null) {
            $free_board->content_text = $request->textfieldvalue;
        }
        $free_board->category = $request->muiSelectValue;
        $free_board->save();

        return $free_board;
    }

    public function BoardShow($category)
    {
        if ($category == "전체") {
            $boards = DB::table("free_boards")
                ->join('users', 'free_boards.user_id', "=", 'users.id')
                ->select('free_boards.*', 'users.name')
                ->latest()
                ->paginate(5);
        } else {
            $boards = DB::table("free_boards")
                ->where("category", "=", $category)
                ->join('users', 'free_boards.user_id', "=", 'users.id')
                ->select('free_boards.*', 'users.name')
                ->latest()
                ->paginate(5);
        }
        return $boards;
    }
    public function ShowLike(Request $request)
    {
        $array = $request->data;
        $boardArray = [];

        foreach ($array as $data) {
            $board = DB::table("free_board_likes")
                ->where("freeboard_id", "=", $data)->get();
            array_push($boardArray, $board);
        }
        return $boardArray;
    }
    public function ShowComment($board_id)
    {
        $comments = DB::table("comments")
            ->where('freeboard_id', '=', $board_id)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select('comments.*', 'users.name')
            ->latest()
            ->paginate(5);
        return $comments;
    }

    public function PostLike(Request $request)
    {
        $like = new FreeBoardLike;
        $like->user_id = $request->user_id;
        $like->freeboard_id = $request->board_id;
        $like->save();
        return $request;
    }
    public function PostComment(Request $request)
    {
        $comments = new Comment();
        $comments->freeboard_id = $request->board_id;
        $comments->comment = $request->content;
        $comments->user_id = $request->user_id;
        $comments->save();
    }
    public function UpdateComment(Request $request)
    {
        $comment = Comment::find($request->comment_id);
        $comment->comment = $request->updateText;
        $comment->save();
    }
    public function DeleteComment($comment_id)
    {
        $comment = Comment::find($comment_id);
        $comment->delete();
    }
    public function DeleteLike(Request $request)
    {
        $like = DB::table('free_board_likes')
            ->where([
                ["freeboard_id", "=", $request->board_id],
                ["user_id", "=", $request->user_id]
            ])
            ->delete();

        // return $like;
    }

    public function ShowPapago(Request $request)
    {
        $text = $request->text;

        $client_id = "W67VxGiecQuxoWQaqZ02"; // 네이버 개발자센터에서 발급받은 CLIENT ID
        $client_secret = "BxA1eiUXuT"; // 나중에 가릴것 ㅋㅋ
        $encText = urlencode($text);
        $postvars = "source=ko&target=ja&text=" . $encText;
        $url = "https://openapi.naver.com/v1/papago/n2mt";
        $is_post = true;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
        $headers = array();
        $headers[] = "X-Naver-Client-Id: " . $client_id;
        $headers[] = "X-Naver-Client-Secret: " . $client_secret;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // echo "status_code:".$status_code."<br>";
        curl_close($ch);
        if ($status_code == 200) {
            return $response;
        } else {
            return "Error!!";
        }
    }
}
