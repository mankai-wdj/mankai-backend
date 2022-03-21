<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\NotisController;
use App\Http\Controllers\TranslationController;
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
Route::get('test/{id}' ,[ChatController::class,'test']);

Route::post('register' ,[AuthController::class,'register']);

Route::post('login' ,[AuthController::class,'login'])->name('login');

Route::middleware('auth:sanctum')->group(function() {

    Route::get('/checklogin', function() {
        return response()->json(["message"=>'logged in','status'=>200],200);
    });

    Route::post('logout', [AuthController::class,'logout']);

    Route::post('noti/add', [NotisController::class,'addNoti']);

    Route::get('noti/get', [NotisController::class,'NotiIndex']);

    Route::get('admin/getuser', [AuthController::class,'getUsers']);
});

Route::post('user/follow' ,[FollowController::class,'store'])->name('storeFollow'); //follow 저장

Route::get('follows/{id}', [FollowController::class, 'getFollows']);

Route::get('memo/{id}', [MemoController::class, 'showMemos']);

Route::get('sidebar/{id}', [ChatController::class, 'sidebarData']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('messages/{id}/{userId}' ,[ChatController::class,'getMessages'])->name('messages'); // room messages 가져오기

Route::get('rooms/{id}' ,[ChatController::class,'getRooms'])->name('rooms'); // rooms 가져오기

Route::post('room/create' ,[ChatController::class,'createRoom'])->name('createRoom'); // room create

Route::post('message/send' ,[ChatController::class,'messageSend'])->name('sendMessage');  //message post

Route::post('messageBot/send' ,[ChatController::class,'sendMessageBot'])->name('sendMessageBot');  //message post

Route::post('room/check' ,[ChatController::class,'deleteRoom'])->name('deleteRoom'); // room 있는지 없는지 확인

Route::post('user/invite' ,[ChatController::class,'inviteUser'])->name('userInvite'); // user 초대

Route::post('/translate/text', [TranslationController::class, 'translation']);  // translation

Route::post('/storememo', [MemoController::class, 'storePostMemo']);

Route::get('room/find/{id}' ,[ChatController::class,'getChatRoomById']); // room id로 찾기
