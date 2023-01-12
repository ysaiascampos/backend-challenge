<?php

use App\Http\Controllers\PostsApiController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/update-posts', [PostsController::class,'updatePostsToApi'])->name('updatePostsToApi');
Route::get('/create-users', [UserController::class,'createUserToApi'])->name('createUserToApi');
Route::get('/users', [UserController::class,'getUsersPosts'])->name('getUsersPosts');
Route::get('/posts/top', [PostsController::class,'getBestPosts'])->name('getBestPosts');
Route::get('/posts/{id}', [PostsController::class,'getPost'])->name('getPost');
