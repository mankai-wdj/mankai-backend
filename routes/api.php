<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\NotisController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MymemoController;
use App\Http\Controllers\ChatController;

use App\Http\Controllers\TranslationController;
use App\Http\Controllers\VideoController;
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

Route::get('test/{id}', [ChatController::class, 'test']);

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

Route::post('user/follow', [FollowController::class, 'store'])->name('storeFollow'); //follow 저장

Route::get('follows/{id}', [FollowController::class, 'getFollows']);

Route::get('memo/{id}', [MemoController::class, 'showMemos']);

Route::get('sidebar/{id}', [ChatController::class, 'sidebarData']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('followers/{user_id}', [FollowController::class, 'getFollowers']);
Route::get('memo/{id}', [MemoController::class, 'showMemos']);
Route::post('user/follow', [FollowController::class, 'store'])->name('storeFollow');
Route::get('/getuserprofile/{user_id}', [BoardController::class, 'getUser']);

Route::get('messages/{id}/{userId}', [ChatController::class, 'getMessages'])->name('messages'); // room messages 가져오기

Route::get('rooms/{id}', [ChatController::class, 'getRooms'])->name('rooms'); // rooms 가져오기

Route::post('room/create', [ChatController::class, 'createRoom'])->name('createRoom'); // room create

Route::post('message/send', [ChatController::class, 'messageSend'])->name('sendMessage');  //message post

Route::post('messageBot/send', [ChatController::class, 'sendMessageBot'])->name('sendMessageBot');  //message post

Route::post('room/check', [ChatController::class, 'deleteRoom'])->name('deleteRoom'); // room 있는지 없는지 확인

Route::post('user/invite', [ChatController::class, 'inviteUser'])->name('userInvite'); // user 초대

Route::post('/translate/text', [TranslationController::class, 'translation']);  // translation

Route::get('room/find/{id}', [ChatController::class, 'getChatRoomById']); // room id로 찾기

// Board Controller
Route::post('updatepost', [BoardController::class, "BoardUpdate"]);
Route::post('/board/show/{category}', [BoardController::class, "BoardShow"]);
Route::get('/getpostimages/{post_id}', [ImageController::class, "ShowPostImages"]);
Route::post('/show/comment/{board_id}', [BoardController::class, "ShowComment"]);
Route::post('/post/comment', [BoardController::class, "PostComment"]);
Route::post('/show/papago', [BoardController::class, "ShowPapago"]);
Route::post('/show/username/{user_id}', [BoardController::class, "ShowUserName"]);
Route::post('/update/comment', [BoardController::class, "UpdateComment"]);
Route::post('/delete/comment/{comment_id}', [BoardController::class, "DeleteComment"]);
Route::post('/post/like', [BoardController::class, "PostLike"]);
Route::get('/show/like/{board_id}', [BoardController::class, "ShowLike"]);
Route::post('upload_post', [BoardController::class, "Store"]);
Route::get('upload_image/{post_id}', [ImageController::class, 'show']);
Route::get('all_comments/{post_id}', [ImageController::class, 'allComments']);
Route::get('/show/samplecomment/{board_id}', [BoardController::class, 'ShowSampleComment']);
Route::get('/show/category/{user_id}', [BoardController::class, 'ShowCategoryUser']);
Route::post('/delete/like', [BoardController::class, "DeleteLike"]);
Route::post('/post/boardcategory', [BoardController::class, 'PostBoardCategory']);
Route::post('upload_image', [ImageController::class, 'Store']);
// MyPage-MyPosts (RU)
Route::get('myposts/{user_id}', [BoardController::class, "showMyPosts"]);
Route::post('myposts/{post_id}', [BoardController::class, 'editMyPosts']);
Route::post('mypost/delete', [BoardController::class, 'deletePosts']);

// MyPage-MyMemos(CRUD)
Route::post('storememo/', [MemoController::class, "storePostMemo"]);
Route::post('deletememos/{post_id}', [MemoController::class, 'deletePostMemos']);

Route::post('updatememo', [MemoController::class, 'updateMemo']);

// MyPage-YouUser
Route::get('follow/{follow_id}', [FollowController::class, 'getFollow']);

Route::post('profile', [UserController::class, 'update']);
Route::get('getmemoimages/{memo_id}', [MemoController::class, 'getMemoImages']);
Route::post('/post/memo', [MemoController::class, "PostMemo"]);
//내 메모 수정*(Update)
Route::get('/show/memo/{user_id}', [MemoController::class, 'ShowMemo']);
Route::get('/get/board/{user_id}', [MemoController::class, 'GetMyBoard']);
Route::get('/show/memos/{memo_id}', [MemoController::class, 'editMemoView']);
//내 메모 수정 할때 수정 페이지에 기존 내용을 표시해주는 것
Route::post('/deletememo', [MemoController::class, 'DeleteMymemo']);
Route::post('video/filesave', [VideoController::class, 'videoChatFileSave']);

// 그룹


Route::get('/show/mygroup/{user_id}', [GroupController::class, 'ShowMyGroup']);
Route::get('/show/detail_group/{group_id}', [GroupController::class, 'ShowGroupDetail']);
Route::get('/show/group/{search}', [GroupController::class, 'ShowGroup']);
Route::get('/show/groupdata/{group_id}', [GroupController::class, 'ShowGroupData']);
Route::get('/show/groupcomment/{group_id}', [GroupController::class, 'ShowGroupComment']);
Route::get('/show/grouplike/{board_id}', [GroupController::class, 'ShowGroupLike']);
Route::get('/show/groupuser/{board_id}', [GroupController::class, 'ShowGroupUser']);
Route::get('/show/groupnoticeweb/{notice_id}', [GroupController::class, 'ShowGroupNoticeWeb']);
Route::post('/update/category', [GroupController::class, 'UpdateGroupCategory']);
Route::post('/delete/groupcategory', [GroupController::class, 'DeleteGroupCategory']);
Route::post('/show/groupnotice', [GroupController::class, 'ShowGroupNotice']);
Route::post('/post/groupnotice', [GroupController::class, 'PostGroupNotice']);
Route::post('/show/groupboard/{group_id}', [GroupController::class, 'ShowGroupBoard']);
Route::post('/post/category', [GroupController::class, 'PostCategory']);
Route::post('/post/introimage', [GroupController::class . 'PostIntroImage']);
Route::post('/post/intro/', [GroupController::class, 'PostGroupIntro']);
Route::post('/post/groupuser/', [GroupController::class, 'PostGroupUser']);
Route::post('/delete/groupuser/', [GroupController::class, 'DeleteGroupUser']);
Route::post('/delete/dashgroupuser/{groupUser_id}', [GroupController::class, 'DeleteDashGroupUser']);
Route::post('/post/group', [GroupController::class, 'PostGroup']);
Route::post('/update/group', [GroupController::class, 'UpdateGroup']);
Route::post('/post/grouplike', [GroupController::class, 'PostGroupLike']);
Route::post('/delete/grouplike', [GroupController::class, 'DeleteGroupLike']);
Route::post('/update/groupcomment', [GroupController::class, 'UpdateGroupComment']);
Route::post('/update/groupuser', [GroupController::class, 'UpdateGroupUser']);
Route::post('/post/groupcomment', [GroupController::class, 'PostGroupComment']);
Route::post('/post/groupboard', [GroupController::class, 'PostGroupBoard']);
Route::post('/post/groupboardimage', [GroupController::class, 'PostGroupBoardImage']);
Route::post('/delete/groupcomment/{comment_id}', [GroupController::class, 'DeleteGroupComment']);


//알림

Route::post('/fcm/message', [NotisController::class, 'messageNoti']);
