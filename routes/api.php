<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\NotisController;
use App\Http\Controllers\MymemoController;
use App\Http\Controllers\GroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [AuthController::class, 'register']);

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/checklogin', function () {
        return response()->json(["message" => 'logged in', 'status' => 200], 200);
    });

    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('noti/add', [NotisController::class, 'addNoti']);

    Route::get('noti/get', [NotisController::class, 'NotiIndex']);

    Route::get('admin/getuser', [AuthController::class, 'getUsers']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/test', [MymemoController::class,'test']);
Route::post('/mymemoshow', [MymemoController::class,'MymemoShow']);
Route::post('/deletememo/{id}',[MymemoController::class, 'MymemoDelete']);
Route::post('/postmymemo',[MymemoController::class,'storeMymemo']);
Route::post('/editmymemo',[MymemoController::class,'editMymemo']);

Route::post('user/follow', [FollowsController::class, 'store'])->name('storeFollow');
Route::post('/board/show/{category}', [BoardController::class, "BoardShow"]);
Route::post('/show/comment/{board_id}', [BoardController::class, "ShowComment"]);
Route::post('/post/comment', [BoardController::class, "PostComment"]);
Route::post('/show/papago', [BoardController::class, "ShowPapago"]);
Route::post('/show/username/{user_id}', [BoardController::class, "ShowUserName"]);
Route::post('/update/comment', [BoardController::class, "UpdateComment"]);
Route::post('/delete/comment/{comment_id}', [BoardController::class, "DeleteComment"]);
Route::post('/post/like', [BoardController::class, "PostLike"]);
Route::get('/show/like/{board_id}', [BoardController::class, "ShowLike"]);
Route::post('upload_post', [BoardController::class, "Store"]);
Route::post('upload_image', [ImageController::class, 'Store']);
Route::get('upload_image/{post_id}', [ImageController::class, 'show']);
Route::get('/show/samplecomment/{board_id}',[BoardController::class,'ShowSampleComment']);
Route::post('/delete/like', [BoardController::class, "DeleteLike"]);


Route::post('/post/memo',[MemoController::class,"PostMemo"]);
Route::post('/update/memo',[MemoController::class,"UpdateMemo"]);
//내 메모 수정*(Update)
Route::get('/show/memo/{user_id}', [MemoController::class, 'ShowMemo']);
Route::get('/get/board/{user_id}',[MemoController::class,'GetMyBoard']);
Route::get('/show/memos/{memo_id}', [MemoController::class, 'editMemoView']);
//내 메모 수정 할때 수정 페이지에 기존 내용을 표시해주는 것
Route::post('/deletememo',[MemoController::class, 'DeleteMymemo']);
// Route::get('/show/like', [BoardController::class, "ShowLike"]);
// Route::post('user/follow', [FollowsController::class, 'store'])->name('storeFollow');
// Route::post('/board/show/{category}', [BoardController::class, "BoardShow"]);
// Route::post('/show/comment/{board_id}', [BoardController::class, "ShowComment"]);
// Route::post('/post/comment', [BoardController::class, "PostComment"]);
// Route::post('/show/papago', [BoardController::class, "ShowPapago"]);
// Route::post('/show/username/{user_id}', [BoardController::class, "ShowUserName"]);
// Route::post('/update/comment', [BoardController::class, "UpdateComment"]);
// Route::post('/delete/comment/{comment_id}', [BoardController::class, "DeleteComment"]);
// Route::post('/post/like', [BoardController::class, "PostLike"]);
// Route::post('/show/like', [BoardController::class, "ShowLike"]);



// 그룹

Route::get('/show/detail_group/{group_id}',[GroupController::class,'ShowGroupDetail']);
Route::get('/show/group/{search}',[GroupController::class,'ShowGroup']);
Route::get('/show/groupdata/{group_id}',[GroupController::class,'ShowGroupData']);
Route::get('/show/groupcomment/{group_id}',[GroupController::class,'ShowGroupComment']);
Route::get('/show/grouplike/{board_id}',[GroupController::class,'ShowGroupLike']);
Route::get('/show/groupuser/{board_id}',[GroupController::class,'ShowGroupUser']);


Route::post('/show/groupnotice',[GroupController::class,'ShowGroupNotice']);
Route::post('/post/groupnotice',[GroupController::class,'PostGroupNotice']);
Route::post('/show/groupboard/{group_id}',[GroupController::class,'ShowGroupBoard']);
Route::post('/post/category',[GroupController::class,'PostCategory']);
Route::post('/post/introimage',[GroupController::class.'PostIntroImage']);
Route::post('/post/intro/',[GroupController::class,'PostGroupIntro']);
Route::post('/post/groupuser/',[GroupController::class,'PostGroupUser']);
Route::post('/delete/groupuser/',[GroupController::class,'DeleteGroupUser']);
Route::post('/delete/dashgroupuser/{groupUser_id}',[GroupController::class,'DeleteDashGroupUser']);
Route::post('/post/group',[GroupController::class,'PostGroup']);
Route::post('/update/group',[GroupController::class,'UpdateGroup']);
Route::post('/post/grouplike',[GroupController::class,'PostGroupLike']);
Route::post('/delete/grouplike',[GroupController::class,'DeleteGroupLike']);
Route::post('/update/groupcomment',[GroupController::class,'UpdateGroupComment']);
Route::post('/update/groupuser',[GroupController::class,'UpdateGroupUser']);
Route::post('/post/groupcomment',[GroupController::class,'PostGroupComment']);
Route::post('/post/groupboard',[GroupController::class,'PostGroupBoard']);
Route::post('/post/groupboardimage',[GroupController::class,'PostGroupBoardImage']);
Route::post('/delete/groupcomment/{comment_id}',[GroupController::class,'DeleteGroupComment']);
