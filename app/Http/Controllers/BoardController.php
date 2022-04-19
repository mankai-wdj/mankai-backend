<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\FreeBoard;
use App\Models\FreeBoardImage;
use App\Models\FreeBoardLike;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BoardController extends Controller
{
    public function Test()
    {
        return "wda";
    }

    public function showMyPosts($user_id)
    {
        $boards_post = DB::table("free_boards")
            ->where('free_boards.user_id', $user_id)
            ->latest()
            ->paginate(5);


        return $boards_post;
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
    // 아직 미완성이지만 editMyPost가 아니라 editPost여야한다.
    // myPosts-Update

    // myPosts-Delete




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
    public function ShowCategoryUser($user_id)
    {
        $category = DB::table("user_categories")->where("user_id", "=", $user_id)->get();
        return $category;
    }
    public function PostBoardCategory(Request $request)
    {
        $category = DB::table("user_categories")->where("user_id", "=", $request->user_id)->delete();


        $array = $request->data;
        foreach ($array as $data) {
            if ($data != '전체') {
                $category = new UserCategory();
                $category->user_id = $request->user_id;
                $category->name = $data;
                $category->save();
            }
        }
    }
    public function BoardShow($category)
    {
        if ($category == "전체") {
            $boards = DB::table("free_boards")
                ->join('users', 'free_boards.user_id', "=", 'users.id')
                ->select('free_boards.*', 'users.name','users.profile')
                ->latest()
                ->paginate(5);
        } else {
            $boards = DB::table("free_boards")
                ->where("category", "=", $category)
                ->join('users', 'free_boards.user_id', "=", 'users.id')
                ->select('free_boards.*', 'users.name','users.profile')
                ->latest()
                ->paginate(5);
        }
        return $boards;
    }
    public function ShowLike($board_id)
    {
        $board = DB::table("free_board_likes")
            ->where("freeboard_id", "=", $board_id)->get();

        return $board;
    }
    public function ShowComment($board_id)
    {
        $comments = DB::table("comments")
            ->where('freeboard_id', '=', $board_id)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select('comments.*', 'users.name','user.profile')
            ->latest()
            ->paginate(5);
        return $comments;
    }
    public function ShowSampleComment($board_id)
    {
        $comments = DB::table('comments')->where("freeboard_id", "=", $board_id)->limit(3)->get();
        $len = count($comments);
        $clen = DB::table('comments')->where("freeboard_id", "=", $board_id)->count();

        for ($i = 0; $i < $len; $i++) {
            $count = DB::table('users')->where("id", '=', $comments[$i]->user_id)->value("name");
            $comments[$i]->user_name = $count;
            $comments[$i]->length = $clen;
        }

        return $comments;
    }

    public function PostLike(Request $request)
    {
        $like = new FreeBoardLike;
        $like->user_id = $request->user_id;
        $like->freeboard_id = $request->board_id;
        $like->save();

        $likes = DB::table('free_board_likes')
            ->where([["user_id", "=", $request->user_id], ["freeboard_id", "=", $request->board_id]])->get();

        return $likes;
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

        $likes = DB::table('free_board_likes')
            ->where([["user_id", "=", $request->user_id], ["freeboard_id", "=", $request->board_id]])->get();


        return $likes;
    }

    public function deletePosts(Request $request)
    {
        $board = FreeBoard::find($request->boardId);
        $board->delete();

        return "나의 게시글 삭제완료";
    }

    public function ShowPapago(Request $request)
    {
        $text = $request->text;
        $client_id = "W67VxGiecQuxoWQaqZ02"; // 네이버 개발자센터에서 발급받은 CLIENT ID
        $client_secret = "BxA1eiUXuT"; // 나중에 가릴것 ㅋㅋ
        $encText = urlencode($text);
        $postvars = "query=" . $encText;
        $mycountry = $request->mycountry;

        $url = "https://openapi.naver.com/v1/papago/detectLangs";
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
        curl_close($ch);
        $json = json_decode($response);
        if ($status_code == 200) {
            $langCode = $json->langCode;
        } else {
            return $response;
        }


        $postvars = "source=" . $langCode . "&target=" . $mycountry . "&text=" . $encText;
        $url = "https://openapi.naver.com/v1/papago/n2mt";
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
        curl_close($ch);

        return $response;
    }

    public function BoardUpdate(Request $request)
    {
        $post = FreeBoard::find($request->post_id)
            ->update([
                'content_text' => $request->content_text,
            ]);
        // ?! 해당게시글을 $request->post_id로 찾고
        // ?! content_text를 수정

        $images = FreeBoardImage::where('free_boards_id', $request->post_id);
        $images->delete();
        // ?! $reqeust->post_id에 해당하는 BoardImage삭제

        $url_images = explode(',', $request->url_images);
        if (strlen($url_images[0]) >= 1) {
            for ($i = 0; $i < count($url_images); $i++) {
                $url_image = new FreeBoardImage();
                $url_image->free_boards_id = $request->post_id;
                $url_image->url = $url_images[$i];
                $url_image->save();
            }
        }

        $j = 0;
        while ($request->hasFile("file_images$j")) {
            $path[$j] = $request->file("file_images$j")->store('image', 's3');
            $j++;
        }
        $z = 0;
        while ($z < $j) {
            $file_images = new FreeBoardImage();
            $file_images->url = Storage::url($path[$z]);
            $file_images->free_boards_id = $request->post_id;
            $file_images->save();
            $z++;
        }

        return $request->all();
    }

    public function getUser($user_id)
    {
        $profile = User::find($user_id)->profile;

        return $profile;
    }
}
