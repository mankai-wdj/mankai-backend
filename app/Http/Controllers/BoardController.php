<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\FreeBoard;
use App\Models\FreeBoardLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    public function Test(){

        return "wda";

    }
    public function BoardShow($category){
        if($category == "전체"){
            $boards = DB::table("free_boards")
                ->join('users','free_boards.user_id',"=",'users.id')
                ->select('free_boards.*','users.name')
                ->latest()
                ->paginate(5);
        }
        else{
            $boards = DB::table("free_boards")
                ->where("category","=",$category)
                ->join('users','free_boards.user_id',"=",'users.id')
                ->select('free_boards.*','users.name')
                ->latest()
                ->paginate(5);
        }
        return $boards;
}
    public function ShowLike(Request $request){
        $array = $request->data;
        $boardArray = [];

        foreach($array as $data)
        {
            $board = DB::table("free_board_likes")
                ->where("freeboard_id","=",$data)->get();
            array_push($boardArray,$board);
        }
        return $boardArray;

    }
    public function ShowComment($board_id){
            $comments = DB::table("comments")
                ->where('freeboard_id','=',$board_id)
                ->join('users','comments.user_id','=','users.id')
                ->select('comments.*','users.name')
                ->latest()
                ->paginate(5);
            return $comments;

    }

    public function PostLike(Request $request){
        $like = new FreeBoardLike;
        $like -> user_id = $request->user_id;
        $like -> freeboard_id = $request -> board_id;
        $like -> save();

        return $request;
    }
    public function PostComment(Request $request){
        $comments = new Comment();
        $comments -> freeboard_id = $request->board_id;
        $comments -> comment = $request->content;
        $comments -> user_id = $request->user_id;
        $comments -> save();

    }
    public function UpdateComment(Request $request){
        $comment = Comment::find($request->comment_id);
        $comment -> comment = $request -> updateText;
        $comment -> save();
    }
    public function DeleteComment($comment_id){
        $comment = Comment::find($comment_id);
        $comment-> delete();
    }

    public function ShowPapago(Request $request){
        $text = $request-> text;

        $client_id = "W67VxGiecQuxoWQaqZ02"; // 네이버 개발자센터에서 발급받은 CLIENT ID
        $client_secret = "BxA1eiUXuT";// 나중에 가릴것 ㅋㅋ
        $encText = urlencode($text);
        $postvars = "source=ko&target=ja&text=".$encText;
        $url = "https://openapi.naver.com/v1/papago/n2mt";
        $is_post = true;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $postvars);
        $headers = array();
        $headers[] = "X-Naver-Client-Id: ".$client_id;
        $headers[] = "X-Naver-Client-Secret: ".$client_secret;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // echo "status_code:".$status_code."<br>";
        curl_close ($ch);
        if($status_code == 200) {
            return $response;
        } else {
            return "Error!!";
        }
    }
}
