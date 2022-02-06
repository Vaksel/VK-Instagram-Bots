<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserActionsController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AutoLikesController;
use App\Http\Controllers\AutoCommentsController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProfileController;

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//IpostXVK
Route::post('/registerFromMainSite', [RegisterController::class,'registerFromMainSite'])->name('registerFromMainSite');

Route::post('/checkRegistration', [LoginController::class,'checkRegistration'])->name('checkRegistration');

//Route::post('/registerFromMainSite', function () {
//    return 123;
//});


Route::get('/', function () {
    return redirect('dashboard');
})->name('index');

Route::get('/testVerstka', [AuthorizationController::class,'testVerstka'])->name('testVerstka');

Route::get('/captchaRequest', [UserActionsController::class,'captchaRequest'])->name('captchaRequest');

Route::get('/captchaLoader', [UserActionsController::class,'captchaLoader'])->name('captchaLoader');

Route::post('/postsLike', [UserActionsController::class,'likeWallPost'])->name('postsLike');

Route::post('/photosLike', [UserActionsController::class,'photosLike'])->name('photosLike');

Route::post('/writeCommentsOnConcurrents', [AutoCommentsController::class, 'writeCommentsOnConcurrents'])->name('writeCommentsOnConcurrents');
Route::post('/giveCommentsOnSearchRecords', [AutoCommentsController::class, 'writeCommentsByTags'])->name('giveCommentsOnSearchRecords');

Route::post('/likeAllConcurrents', [AutoLikesController::class, 'likeAllConcurrents'])->name('likeAllConcurrents');
Route::post('/likeAllMembers', [AutoLikesController::class, 'likeAllMembersByCriteries'])->name('likeAllMembers');
Route::post('/likeAllFriends', [AutoLikesController::class, 'likeAllFollowersFriends'])->name('likeAllFriends');
Route::get('/getAllPosts', [AutoLikesController::class, 'getAllSearchRecords'])->name('getAllPosts');
Route::post('/likeAllSearchRecordsByTags', [AutoLikesController::class, 'likeAllSearchRecordsByTags'])->name('likeAllSrchRdsByTags');

Route::get('/authorization', [AuthorizationController::class,'index'])->name('authorization');
Route::get('/authorize', [AuthorizationController::class,'getAndWriteAccessToken'])->name('authorize');


Route::post('/saveToken', [AuthorizationController::class,'getTokenFromUrl'])->name('saveToken');
Route::get('/tokens', [ProfileController::class,'tokens'])->name('tokens');
Route::post('/tokens/{id}/view', [ProfileController::class,'view_token'])->name('tokenView');
Route::get('/tokens/{id}/edit', [ProfileController::class,'edit_token'])->name('tokenEdit');
Route::get('/tokens/{id}/delete', [ProfileController::class,'delete_token'])->name('tokenDelete');
Route::post('/tokens/change-token', [ProfileController::class,'change_token'])->name('tokenChange');
Route::any('/tokens/add-token', [ProfileController::class,'add_token'])->name('tokenAdd');

Route::get('/dashboard', [UserActionsController::class,'index'])->name('dashboard');
Route::post('/addToFriend', [UserActionsController::class,'addFriends'])->name('addToFriend');
Route::post('/deleteFromFriends', [UserActionsController::class,'deleteFriends'])->name('deleteFromFriends');

Route::get('/test', function (){
    phpinfo();
})->name('test');



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/friends', [UserActionsController::class, 'acceptFriendsRequest'])->name('friends');
Route::post('/delfriends', [UserActionsController::class, 'deleteBannedFriends'])->name('delfriends');
//






Auth::routes();
