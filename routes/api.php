<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\IpostX\TokenAuthController;
use App\Http\Controllers\IpostX\CopyMessageController;
use App\Http\Controllers\IpostX\ServiceController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Instagram Limits
Route::get('/startLimitHandler', [ServiceController::class, 'startLimitQueue'])->name('startLimitHandler');
//

//Instagram API
Route::post('/register',[TokenAuthController::class,'index'])->name('RegisterServerTreatment');
Route::post('/add-comment',[CopyMessageController::class,'index'])->middleware('inst_auth_token_checker')->name('AddCommentOnServer');
Route::post('/add-comments',[CopyMessageController::class,'addComments'])->middleware('inst_auth_token_checker')->name('AddCommentsOnServer');
Route::post('/edit-comment',[CopyMessageController::class,'edit'])->middleware('inst_auth_token_checker')->name('EditCommentOnServer');
Route::post('/edit-action-comment',[CopyMessageController::class,'index'])->middleware('inst_auth_token_checker')->name('EditActionOnServer');
Route::post('/delete-comment',[CopyMessageController::class,'delete'])->middleware('inst_auth_token_checker')->name('DeleteCommentOnServer');
Route::post('/toggle-comment-treatment',[CopyMessageController::class,'toggleCommentTreatmentBySingle'])->middleware('inst_auth_token_checker')->name('ToggleCommentTreatmentOnServer');
Route::post('/check-comment', [CopyMessageController::class,'checkIsCommentExist'])->name('checkIsCommentExist');
Route::post('/comments', [CopyMessageController::class,'messages'])->middleware('inst_auth_token_checker')->name('getMessages');
Route::post('/start-treatment', [CopyMessageController::class,'startTreatment'])->middleware('inst_auth_token_checker')->name('startTreatment');
Route::post('/get-comment-statuses', [CopyMessageController::class,'getStatuses'])->middleware('inst_auth_token_checker')->name('getStatuses');

//
