<?php

namespace App\Http\Controllers;
use App\Models\Group;
use App\Models\GroupBoard;
use App\Models\GroupBoardImage;
use App\Models\GroupBoardLike;
use App\Models\GroupComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function ShowGroup(){
        $groups=Group::all();
        return $groups;
    }
    public function ShowGroupBoard($group_id){
        $groups = DB::table('group_boards')
            ->where("group_id","=",$group_id)
            ->join("users","users.id","=","group_boards.user_id")
            ->select("group_boards.*",'users.name')
            ->get();

        return $groups;
    }
    public function PostGroupComment(Request $request){
        $comments = new GroupComment;
        $comments->group_board_id = $request->board_id;
        $comments->comment = $request->content;
        $comments->user_id = $request->user_id;
        $comments->save();

    }

    public function ShowGroupComment($group_id){
        $comments = DB::table("group_comments")
            ->where('group_board_id', '=', $group_id)
            ->join('users', 'group_comments.user_id', '=', 'users.id')
            ->select('group_comments.*', 'users.name')
            ->latest()
            ->paginate(5);
        return $comments;
    }
    public function PostGroupLike(Request $request){
        $like = new GroupBoardLike;
        $like-> user_id = $request->user_id;
        $like-> group_board_id = $request->board_id;
        $like->save();

        $likes = DB::table('group_board_likes')
            ->where([["user_id","=",$request->user_id],["group_board_id","=",$request->board_id]])->get();

        return $likes;
    }
    public function DeleteGroupLike(Request $request){
        $like = DB::table('group_board_likes')
        ->where([
            ["group_board_id","=",$request->board_id],
            ["user_id","=",$request->user_id]
            ])
        ->delete();

        $likes = DB::table('group_board_likes')
            ->where([["user_id","=",$request->user_id],["group_board_id","=",$request->board_id]])->get();
        return $likes;

    }

    public function ShowGroupData($group_id){

        // 이미지 가져오기
        $images = GroupBoardImage::where('group_board_id', $group_id)->get();

        // 좋아요 정보
        $board = DB::table("group_board_likes")
        ->where("group_board_id", "=", $group_id)->get();

        // 코맨트 작업
        $comments = DB::table('group_comments')->where("group_board_id","=",$group_id)->limit(3)->get();
        $len = count($comments);
        // 코맨트 유저 닉네임 가져오기
        for($i=0; $i<$len;$i++){
            $count = DB::table('users')->where("id",'=',$comments[$i] -> user_id)->value("name");
            $comments[$i]-> user_name = $count;
        }

        // 댓글 총 길이
        $clen = DB::table('group_comments')->where("group_board_id","=",$group_id)->count();

        // 임의 DB
        $array = new GroupBoardImage;
        $array -> images = $images;
        $array -> likes = $board;
        $array -> comments = $comments;
        $array -> comment_length = $clen;

        return $array;

    }
    public function UpdateGroupComment(Request $request){
        $comment = GroupComment::find($request->comment_id);
        $comment-> comment = $request->updateText;
        $comment->save();

    }
    public function DeleteGroupComment($comment_id){
        $comment = GroupComment::find($comment_id);
        $comment->delete();
    }
    public function ShowGroupLike($board_id){
        $board = DB::table("group_board_likes")
        ->where("group_board_id", "=", $board_id)->get();

        return $board;
    }
    public function PostGroupBoard(Request $request){
        $request->validate([
            'selectedImages' => 'required_without:textfieldvalue',
            'textfieldvalue' => 'required_without:selectedImages'
        ]);

        $group_board = new GroupBoard;
        $group_board->user_id = $request->user["id"];
        if ($request->textfieldvalue != null) {
            $group_board->content_text = $request->textfieldvalue;
        }
        // $group_board->category = $request->muiSelectValue;
        $group_board->group_id = $request->group_id;
        $group_board->save();

        return $group_board;
    }
    public function PostGroupBoardImage(Request $request){


        $i = 0;
        $path = array();
        while ($request->hasFile("images{$i}") == true) {
            $path[$i] = $request->file("images{$i}")->store('image', 's3');
            $i++;
        }
        $j = 0;
        while ($j < $i) {
            $image = new GroupBoardImage;
            $image -> url = Storage::url($path[$j]);
            $image -> group_board_id = $request ->post_id;
            $image -> save();
            $j++;
        }
        // 이제 Read/Update/Delete를 할 수 있게 하면된다.
        return $path;
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
