<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\NotisController;
use App\Http\Controllers\MessageMemoController;
use App\Http\Controllers\MymemoController;
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
Route::get('follows/{id}', [FollowController::class, 'getFollows']);
Route::get('memo/{id}', [MemoController::class, 'showMemos']);
Route::post('user/follow', [FollowsController::class, 'store'])->name('storeFollow');
Route::post('/board/show/{category}', [BoardController::class, "BoardShow"]);
Route::post('/show/comment/{board_id}', [BoardController::class, "ShowComment"]);
Route::post('/post/comment', [BoardController::class, "PostComment"]);
Route::post('/show/papago', [BoardController::class, "ShowPapago"]);
Route::post('/show/username/{user_id}', [BoardController::class, "ShowUserName"]);
Route::post('/update/comment', [BoardController::class, "UpdateComment"]);
Route::post('/delete/comment/{comment_id}', [BoardController::class, "DeleteComment"]);
Route::post('/post/like', [BoardController::class, "PostLike"]);
Route::post('/show/like', [BoardController::class, "ShowLike"]);
Route::post('upload_post', [BoardController::class, "Store"]);
Route::post('upload_image', [ImageController::class, 'Store']);
Route::get('upload_image/{post_id}', [ImageController::class, 'show']);
Route::post('/delete/like', [BoardController::class, "DeleteLike"]);

// MyPage-MyPosts (RU)
Route::get('myposts/{user_id}', [BoardController::class, "showMyPosts"]);
Route::post('myposts/{post_id}', [Boardcontroller::class, 'editMyPosts']);

// MyPage-MyMemos(CRUD)
Route::post('storememo/', [MemoController::class, "storePostMemo"]);
Route::post('deletememos/{post_id}', [MemoController::class, 'deletePostMemos']);

Route::get('getmemoimages/{memo_id}', [MemoController::class, 'getMemoImages']);
Route::post('updatememo', [MemoController::class, 'updateMemo']);
