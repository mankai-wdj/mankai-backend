<?php

namespace App\Http\Controllers;
use App\Models\Group;
use App\Models\GroupBoard;
use App\Models\GroupBoardImage;
use App\Models\GroupBoardLike;
use App\Models\GroupCategory;
use App\Models\GroupComment;
use App\Models\GroupNotice;
use App\Models\GroupUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function PostGroupIntro(Request $request){
        $group = GROUP::find($request->group_id);
        $group -> intro = $request -> text;
        $group -> save();
    }
    public function PostIntroImage(Request $request){
        $abc = $request->file("file-0")->store('image', 's3');
        return $abc;

    }
    public function PostGroupUser(Request $request){
        $groupuser = new GroupUser;
        $groupuser -> group_id = $request -> group_id;
        $groupuser -> user_id = $request -> user_id;
        $groupuser -> position = "user";
        $groupuser -> save();
    }
    public function DeleteGroupUser(Request $request){
        // 가입해지 버튼 눌렀을때임
        $groupuser = DB::table("group_users")
            ->where([
                ["group_id","=",$request->group_id],
                ["user_id","=",$request->user_id]]
            );
        $groupuser -> delete();
    }
    public function DeleteDashGroupUser($groupUser_id){
        // 강제로 해지 시킬때임
        $groupUser = GroupUser::find($groupUser_id);
        $group_id = $groupUser -> group_id;
        $groupUser -> delete();

        $master = DB::table("group_users")
            ->where("group_users.position","=","master")
            ->where("group_id","=",$group_id)
            ->join("users","users.id","=","group_users.user_id")
            ->select("group_users.*","users.name")
            ->get();

        $user = DB::table("group_users")
            ->where("group_users.position","=","user")
            ->where("group_id","=",$group_id)
            ->join("users","users.id","=","group_users.user_id")
            ->select("group_users.*","users.name")
            ->get();

        $group = new GroupUser;
        $group -> master = $master;
        $group -> user = $user;

        return $group;
    }
    public function ShowGroupUser($board_id){
        $group_users = DB::table('group_users')
            ->where("group_id","=",$board_id)
            ->join("users","users.id","=","group_users.user_id")
            ->select("group_users.*","users.name")
            ->get();

        return $group_users;
    }
    public function ShowGroup($search){
        if($search == "NULLDATA")
            $groups=Group::all();
        else
            $groups = DB::table("groups")->where("name","like","%".$search."%") -> get();

        for($i=0 ; $i<count($groups) ; $i++)
        {
            $group_id = $groups[$i]->id;
            $groupUsers = DB::table('group_users') -> where("group_id","=",$group_id) -> get();
            $groups[$i]->length = count($groupUsers);
        }
        return $groups;
    }
    public function ShowGroupBoard(Request $request,$group_id){
        $groups = DB::table('group_boards')
            ->where([["group_id","=",$group_id],["category","=",$request->category]])
            ->join("users","users.id","=","group_boards.user_id")
            ->select("group_boards.*",'users.name')
            ->latest()
            ->paginate(5);

        return $groups;
    }
    public function PostGroupComment(Request $request){
        $comments = new GroupComment;
        $comments->group_board_id = $request->board_id;
        $comments->comment = $request->content;
        $comments->user_id = $request->user_id;
        $comments->save();

    }
    public function UpdateGroupUser(Request $request){
        $groupUser = GroupUser::find($request->data);
        if($groupUser -> position == "master")
            $groupUser -> position = "user";
        else
            $groupUser -> position = "master";

        $groupUser -> save();

        $master = DB::table("group_users")
            ->where("group_users.position","=","master")
            ->where("group_id","=",$groupUser->group_id)
            ->join("users","users.id","=","group_users.user_id")
            ->select("group_users.*","users.name")
            ->get();

        $user = DB::table("group_users")
            ->where("group_users.position","=","user")
            ->where("group_id","=",$groupUser->group_id)
            ->join("users","users.id","=","group_users.user_id")
            ->select("group_users.*","users.name")
            ->get();



        $group = new GroupUser;
        $group -> master = $master;
        $group -> user = $user;

        return $group;
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
        $group_board->category = $request->category_id;
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
    public function UpdateGroup(Request $request){
        $group = Group::find($request->group_id);

        if($request -> file('img')){
            $path = $request->file('img')->store('images','s3');
            $url = Storage::url($path);
        }
        else
            $url = $request->img;


        $group->logoImage=  $url;
        $group->category = $request->category;
        $group->name = $request -> text;
        $group->master = $request -> user_id;
        if($request->password)
            $group->password = $request -> password;
        else
            $group->password=null;
        $group->save();


    }
    public function DeleteGroupCategory(Request $request){
        $category = GroupCategory::find($request->category_id);
        $category -> delete();

    }
    public function UpdateGroupCategory(Request $request){
        $category = GroupCategory::find($request->category_id);
        $category -> title = $request->category_title;
        $category -> save();
    }

    public function ShowGroupNotice(Request $request){
        $notice = DB::table("group_notices")
            ->where([["category","=",$request->category_id],["group_id","=",$request->group_id]])
            ->latest()
            ->get();

        return $notice;
    }
    public function PostGroupNotice(Request $request){
        $notice = new GroupNotice();
        $notice -> user_id = $request -> user_id;
        $notice -> group_id = $request -> group_id;
        $notice -> category = $request -> category_id;
        $notice -> title = $request -> title;
        $notice -> content = $request -> content;

        $notice -> save();

    }

    public function PostGroup(Request $request){
        $group = new Group;
        $path = $request->file('img')->store('images','s3');
        $url = Storage::url($path);

        $group->logoImage=  $url;
        $group->category = $request->category;
        $group->name = $request -> text;
        $group->master = $request -> user_id;
        if($request -> password)
            $group->password = $request->password;

        $group->save();


        $group_user = new GroupUser;
        $group_user -> group_id =  $group -> id;
        $group_user -> user_id =  $request -> user_id;
        $group_user -> position = "master";
        $group_user -> save();

        return $group->id;
    }
    public function PostCategory(Request $request){
        $category = new GroupCategory();
        $category -> group_id = $request-> group_id;
        $category -> title = $request -> title;
        $category -> type = $request -> type;
        $category -> save();

        return $category;
    }

    public function ShowGroupDetail($group_id){
        $group = Group::find($group_id);
        $category = DB::table("group_categories")->where("group_id","=",$group_id)->get();

        $count = new Group;

        $count->group = $group;
        $count->category = $category;

        return $count;
    }

}
