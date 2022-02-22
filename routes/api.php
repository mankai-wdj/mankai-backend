<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotisController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
