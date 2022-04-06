<?php

namespace App\Http\Controllers;

use App\Models\FreeBoardImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function Store(Request $request)
    {
        $i = 0;
        $path = array();
        while ($request->hasFile("images{$i}") == true) {
            $path[$i] = $request->file("images{$i}")->store('image', 's3');
            $i++;
        }
        $j = 0;
        while ($j < $i) {
            $image = FreeBoardImage::create([
                // 'filename' => basename($path[$j]),
                'url' => Storage::url($path[$j]),
                'free_boards_id' => $request->post_id,
            ]);
            $j++;
        }
        // 이제 Read/Update/Delete를 할 수 있게 하면된다.
        return $path;
    }
    public function show($post_id)
    {
        $images = FreeBoardImage::where('free_boards_id', $post_id)->get();
        $board = DB::table("free_board_likes")
        ->where("freeboard_id", "=", $post_id)->get();

        // 코맨트 작업
        $comments = DB::table('comments')->where("freeboard_id","=",$post_id)->limit(3)->get();
        $len = count($comments);
        $clen = DB::table('comments')->where("freeboard_id","=",$post_id)->count();

        for($i=0; $i<$len;$i++){
            $count = DB::table('users')->where("id",'=',$comments[$i] -> user_id)->value("name");
            $comments[$i]-> user_name = $count;
        }

        $array = new FreeBoardImage;
        $array -> images = $images;
        $array -> likes = $board;
        $array -> comments = $comments;
        $array -> comment_length = $clen;

        return $array;
    }
}
