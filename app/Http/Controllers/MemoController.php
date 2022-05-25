<?php

namespace App\Http\Controllers;

use App\Models\AllMemo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MemoImage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MemoController extends Controller
{

    public function storePostMemo(Request $request)
    {
        // 모든 메모가 all_memos에 공통으로 저장하는 내용
        $memo = new AllMemo();
        $memo->memo_title = $request->memo_title;
        $memo->user_id = $request->user_id;
        $memo->content_text = $request->content_text;
        $memo->type = $request->memo_type;

        $memo->save();

        // BoardType게시글 저장할 때
        if ($request->groupboard_memo_id != null) {
            $groupBoardImages = DB::table('group_board_images')
                ->where('group_board_id', $request->groupboard_memo_id)
                ->get();

            foreach ($groupBoardImages as $groupBoardImage) {
                $memoImages = new MemoImage();
                $memoImages->url = $groupBoardImage->url;
                $memoImages->memo_id = $memo->id;
                $memoImages->save();
            }
        }


        // SNSType게시글 저장할 때
        if ($request->post_memo_id != null) {
            $images = DB::table('free_board_images')
                ->where('free_boards_id', $request->post_memo_id)
                ->get();



            foreach ($images as $image) {
                $memoImages = new MemoImage();
                $memoImages->url = $image->url;
                $memoImages->memo_id = $memo->id;
                $memoImages->save();
            }
        }

        if ($request->hasFile("images0")) {
            $i = 0;
            $path = array();
            while ($request->hasFile("images{$i}") == true) {
                $path[$i] = $request->file("images{$i}")->store('image', 's3');
                $i++;
            }
            $j = 0;
            while ($j < $i) {
                $image = MemoImage::create([
                    'url' => Storage::url($path[$j]),
                    'memo_id' => $memo->id,
                ]);
                $j++;
            }
        }


        return $memo;
    }
    //MyMemo-PostMemo-Create

    public function showMemos($user_id)
    {
        $user = User::find($user_id);
        return $user->myMemos()->get();
    }
    //Memo-PostMemo-Read

    public function updateMemo(Request $request)
    {


        $postMemo = AllMemo::find($request->memo_id)
            ->update([
                'content_text' => $request->content_text,
                'memo_title' => $request->memo_title
            ]);

        if (gettype($request->memo_id) == "string") {
            $images = MemoImage::where("memo_id", intval($request->memo_id));
            $images->delete();
        } else {
            $images = MemoImage::where('memo_id', $request->memo_id);
            $images->delete();
        }


        $url_images = explode(',', $request->url_images);
        Log::info($url_images);
        if (strlen($url_images[0]) >= 1) {
            for ($i = 0; $i < count($url_images); $i++) {
                $url_image = new MemoImage();
                $url_image->memo_id = $request->memo_id;
                $url_image->url = $url_images[$i];
                $url_image->save();
            }
        }
        // 기존것이였던 애들


        $j = 0;
        while ($request->hasFile("file_images$j")) {
            $path[$j] = $request->file("file_images$j")->store('image', 's3');
            $j++;
        }
        $z = 0;
        while ($z < $j) {
            $file_images = new MemoImage();
            $file_images->url = Storage::url($path[$z]);
            $file_images->memo_id = $request->memo_id;
            $file_images->save();
            $z++;
        }
        // 새로 추가된 애들


        return "이미지 업데이트 성공";
    }

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

    public function showBoardMemo($memo_id)
    {
        $memo = AllMemo::find($memo_id);
        return $memo;
    }

    public function editBoardMemo($memo_id)
    {
        $memo = AllMemo::find($memo_id);
        return $memo;
    }
}
