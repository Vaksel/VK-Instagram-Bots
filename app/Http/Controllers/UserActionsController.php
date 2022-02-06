<?php


namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Flood;
use App\Models\Captcha;
use App\Models\Limits;

use App\Models\Statistic;
use http\Client\Response;

use ATehnix\VkClient\Client;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use ATehnix\VkClient\Requests\Request;
use Illuminate\Http\Request as BasicRequest;

use ATehnix\VkClient\Requests\ExecuteRequest;
use App\Http\Controllers\Service\ServiceActionsController;

class UserActionsController extends ServiceActionsController
{
    protected $apiConnection;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->makeConnection();
//        // Check DB on availability of captcha or flood or unhandled stops and render right view
        return $this->stateChecker(['apiConnection' => $this->apiConnection]);
//        return view('dashboard');

    }

    protected function makeConnection()
    {
        if (Auth::check()) {
            $api = new Client('5.130');

            $token = auth()->user()->tokens->where('active', 1)->where('type', 0)->first();

            if(empty($token->token_value))
            {
                session()->flash('token_fail', 'Токен не был добавлен, проверьте правильность ввода токена. 
( Добавить токен можно в верхней части страницы нажав на кнопку «Токены». )');

                return false;
            }

            $tokenIsValid = $this->validateAccessToken($token->token_value, $api);

            if ($token->token_value && $tokenIsValid) {
                $api->setDefaultToken($token->token_value);
                $this->apiConnection = $api;
                return true;
            } else {
                session()->flash('token_fail', 'Токен не был добавлен, проверьте правильность ввода токена');

                return false;
            }
        } else {
            session()->flash('auth_fail', 'Возникла проблема аутентификации, пользователь найден не был');

            return false;
        }
    }

    protected function getFriendRequests($count = 1, $need_viewed = 1, $fields)
    {
        $request = new Request('friends.getRequests', ['count' => $count, 'extended' => 1, 'out' => 0, 'need_viewed' => $need_viewed, 'fields' => $fields]);

        sleep(1);
        $users = $this->apiConnection->send($request);

        return ($users['response']['items']);
    }

    protected function getUsersInfo($id, $fields)
    {
        $request = new Request('users.get', ['user_ids' => $id, 'fields' => $fields]);
        $response = $this->apiConnection->send($request);


        return $response['response'];
    }

    protected function getWallInfo($owner_id, $offset, $count = 100)
    {
        sleep(1);
        $request = new Request('wall.get', ['owner_id' => $owner_id, 'offset' => $offset, 'count' => $count]);
        $response = $this->apiConnection->send($request);


        return $response['response'];
    }

    protected function getUserStatistics($user_id)
    {
        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }
        $execute = ExecuteRequest::make([
            $user_counters = new Request('users.get', ['user_ids' => $user_id, 'fields' => 'counters']),
            $user_posts = new Request('wall.get', ['owner_id' => $user_id]),
        ]);

        $response = $this->apiConnection->send($execute);

        return $response['response'];
    }

    // TODO: Функции выше перенести в FACADE

    protected function getRightID($dirty_owner_id)
    {
        $request = new Request('utils.resolveScreenName', ['screen_name' => $dirty_owner_id]);
        try
        {
            $response = $this->apiConnection->send($request);
        }
        catch(\Exception $e)
        {
            return $dirty_owner_id;
        }

        if (empty($response['response'])) {
            return (int)$dirty_owner_id;
        }

        if ($response['response']['type'] === 'group') {
            return $response['response']['object_id'] * -1;
        } else {
            return $response['response']['object_id'];
        }

    }

    public function likeWallPost(BasicRequest $request)
    {
        $extreme_resume = auth()->user()->captcha()
            ->where('captcha_type', '=', 'unhandled.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($extreme_resume) {
            auth()->user()->captcha()
                ->where('captcha_type', '=', 'unhandled.like')
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }

        $flood = auth()->user()->flood()
            ->where('flood_type', '=', 'posts.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($flood) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'posts.like')
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }

        $request->input('owner_id') ? $owner_id = $request->input('owner_id') :
            $owner_id = '';

        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }

        $owner_id = $this->getRightID($owner_id);

        $request->input('captcha_key') ? $captcha['key'] = $request->input('captcha_key') :
            $captcha['key'] = '';
        $request->input('captcha_sid') ? $captcha['sid'] = $request->input('captcha_sid') :
            $captcha['sid'] = '';
        $request->input('last_comments') ? $captcha['last_comments'] = $request->input('last_comments') :
            $captcha['last_comments'] = '';
        $request->input('last_comment') ? $captcha['last_comment'] = $request->input('last_comment') :
            $captcha['last_comment'] = '';
        $request->input('record_qty') ? $record_qty = $request->input('record_qty') :
            $record_qty = '';
        $request->input('comment_qty') ? $comment_qty = $request->input('comment_qty') :
            $comment_qty = '';

//        ddd([$record_qty, $comment_qty]);

        $likesWereGiven = true;

        if ($owner_id !== 0 || $owner_id === '') {

            if (!empty($captcha['key'])) {
                $posts = $this->getWallInfo($owner_id, $captcha['last_comments'], $record_qty)['items'];
                $comments = $this->getCommentsFromPosts($posts, ['last_comment' => $captcha['last_comment'], 'post_comment_count' => $comment_qty, 'fields' => '']);
                $likesResponse = $this->likeCommentsForPostsAutoCaptcha($comments, ['owner_id' => $owner_id, 'flood_type' => 'com_post_like_flood','captcha' => $captcha]);
            } else if ($extreme_resume) {
                $posts = $this->getWallInfo($owner_id, $extreme_resume->post_comments_position, $record_qty)['items'];
                $comments = $this->getCommentsFromPosts($posts, ['last_comment' => $extreme_resume->post_comment_position, 'post_comment_count' => $comment_qty, 'fields' => '']);
                $likesResponse = $this->likeCommentsForPostsAutoCaptcha($comments, ['owner_id' => $owner_id, 'flood_type' => 'com_post_like_flood', 'captcha' => $captcha]);
            } else if ($flood) {
                $posts = $this->getWallInfo($owner_id, 0, $record_qty)['items'];
                $comments = $this->getCommentsFromPosts($posts, ['last_comment' => [], 'post_comment_count' => $comment_qty, 'fields' => '']);
                $likesResponse = $this->likeCommentsForPostsAutoCaptcha($comments, ['owner_id' => $owner_id, 'flood_type' => 'com_post_like_flood', 'captcha' => $captcha]);
            } else {
                $posts = $this->getWallInfo($owner_id, 0, $record_qty)['items'];
                $comments = $this->getCommentsFromPosts($posts, ['last_comment' => [], 'post_comment_count' => $comment_qty, 'fields' => '']);
                $likesResponse = $this->likeCommentsForPostsAutoCaptcha($comments,  ['owner_id' => $owner_id, 'flood_type' => 'com_post_like_flood', 'captcha' => $captcha]);
            }
        }
        else
        {
            $likesWereGiven = false;
        }

        if(!empty($likesResponse['likesQty']))
        {
            Statistic::update_likes(['post_comments_likes' => $likesResponse['likesQty'], 'all_likes' => $likesResponse['likesQty']]);
        }


        $captchaDB = auth()->user()->captcha()
            ->where('captcha_type', '=', 'posts.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $unhandledDB = auth()->user()->captcha()
            ->where('captcha_type', '=', 'unhandled.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $floodDB = auth()->user()->flood()
            ->where('flood_type', '=', 'posts.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($captchaDB) {
            session()->flash('com_post_like_captcha', 'Лайки были проставлены не всем комментариям, вы получили капчу, продолжение будет с последнего выставленого лайка');

            return view('dashboard', ['captcha' => $captchaDB]);
        } else if ($unhandledDB) {
            session()->flash('com_post_like_unhandled', 'Вконтакте разорвал подключение, лайки были проставлены не всем комментариям, попробуйте снова и запись начнется с последних');

            return redirect('dashboard');
        } else if ($floodDB) {
            session()->flash('com_post_like_flood', 'Лайки были проставлены, но вконтакте остановил вас за флуд, поумерьте свой пыл и продолжите через минимум час');

            return redirect('dashboard');
        } elseif(!$likesWereGiven) {
            session()->flash('com_post_like_unhandled', 'Лайки не проставлены, проверьте правильность ввода id или короткого имени');

            return redirect('dashboard');
        }
        else {
            session()->flash('com_post_like', 'Лайки были проставлены');

            return redirect('dashboard');
        }

    }

    protected function getCommentsFromPosts($posts, $fields = ['fields' => ''], $response = [])
    {
        $executeRequests = [];
        $response = [];

        sleep(1);

        foreach (array_chunk($posts, 25) as $chunk) {
            foreach ($chunk as $post) {
                $fields['last_comment'] ? $executeRequests[] = new Request('wall.getComments',
                    ['owner_id' => $post['owner_id'], 'post_id' => $post['id'], 'offset' => $fields['last_comment'],
                        'count' => $fields['post_comment_count'], 'need_likes' => true, 'fields' => $fields['fields']]) :
                    $executeRequests[] = new Request('wall.getComments',
                        ['owner_id' => $post['owner_id'], 'post_id' => $post['id'],
                            'count' => $fields['post_comment_count'], 'need_likes' => true, 'fields' => $fields['fields']]);
            }


            $vkResponse = $this->apiConnection->send(ExecuteRequest::make($executeRequests))['response'];

            $response = array_merge($response, $vkResponse);
            unset($executeRequests);
            sleep(1);
        }

        return $response;
    }

    protected function likeCommentsForPosts($owner_id, $commentsPlace, $params = [], $response = [])
    {
        $counterCommentsPlace = 0;
        $counterComment = 0;
        $executeRequests = [];
        foreach ($commentsPlace as $comments) {
            $counterComment = 0;
            if (!empty($comments['count'])) {
                foreach ($comments['items'] as $comment) {
                    try {
                        sleep(1);
                        if (!empty($params['captcha']['key'])) {
                            $response[] = $this->apiConnection->send(new Request('likes.add',
                                ['item_id' => $comment['id'], 'type' => 'comment', 'owner_id' => $owner_id,
                                    'captcha_sid' => $params['captcha']['sid'], 'captcha_key' => $params['captcha']['key']]));


                            auth()->user()->captcha()
                                ->where('captcha_type', '=', 'posts.like')
                                ->orderBy('created_at', 'desc')
                                ->latest()
                                ->first()
                                ->delete();

                            $params = [];
                        } else {
                            $response[] = $this->apiConnection->send(new Request('likes.add',
                                ['item_id' => $comment['id'], 'type' => 'comment', 'owner_id' => $owner_id]));
                        }

                    } catch (\Exception $e) {

                        return $this->exceptionHandler($e, $response, ['owner_id' => $owner_id, 'counterCommentsPlace' => $counterCommentsPlace,
                            'counterComment' => $counterComment, 'captchaType' => 'posts.like']);

                    }
                    $counterComment++;
                }
            }
            $counterCommentsPlace++;
        }

        return $response;
    }

    protected function likeCommentsForPostsAutoCaptcha($commentsBlock, $params = [], $fields = '')
    {
        set_time_limit(0);

        sleep(1);

        $response = [];
        $counterCommentsPlace = 0;

        $chunkCounter = 0;
        $itemCounter = 0;

        $params['flood_type'] === 'com_photo_like_flood' ? $type = 'photo_comment' : $type = 'comment';

        foreach (array_chunk(array_reverse($commentsBlock), 25) as $commentsBlockChunk) {
//            ddd($commentsBlockChunk);
            foreach ($commentsBlockChunk as $comments) {
                $counterComment = 0;

                if (!empty($comments['count'])) {
//                    ddd($comments['items']);
                    foreach ($comments['items'] as $comment) {
                        
                        $request = new Request('likes.add',
                            ['item_id' => $comment['id'], 'type' => $type, 'owner_id' => $params['owner_id']]);

                        try {

                            $dirtyResponse = $this->captchaSkipperComments($request, $response, $comments, $chunkCounter, $itemCounter, ['record_type' => 'post', 'flood_type' => $params['flood_type'], 'owner_id' => $params['owner_id']]);

                            if(isset($dirtyResponse['flood']))
                            {
                                session()->flash($params['flood_type'], 'Вы поймали флуд-бан, но лайки были
                                    проставлены в количестве ' . ($itemCounter));

                                return $response;
                            }

                            $response = array_merge($response, $dirtyResponse['response']);

                            $itemCounter    =   $dirtyResponse['itemCounter'];

                            $chunkCounter   =   $dirtyResponse['chunkCounter'];
                            sleep(1);

                        } catch (\Exception $e) {
                            if ($e->getCode() == 14) {
                                $request = [];
                                $captcha_answer = $this->exceptionHandlerAutoLikesComments($e, $response,
                                    ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'posts.autoLike-comments']);

                                foreach ($comments['items'] as $innerItem) {
                                    if ($innerItem['likes']['user_likes'] === 0) {

                                        $params['flood_type'] === 'com_photo_like_flood' ? $innerItem['owner_id'] = $params['owner_id'] : null;

                                        if ($captcha_answer !== '') {
                                            $request = new Request('likes.add', ['owner_id' => $innerItem['owner_id'],
                                                'item_id' => $innerItem['id'], 'type' => 'comment',
                                                'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);
                                            $captcha_answer = '';
                                        } else {
                                            $request = new Request('likes.add', ['owner_id' => $innerItem['owner_id'],
                                                'item_id' => $innerItem['id'], 'type' => 'comment']);
                                        }
                                    }
                                }

                                $dirtyResponse = $this->captchaSkipperComments($request, $response, $comments, $chunkCounter, $itemCounter, ['record_type' => 'post', 'flood_type' => $params['flood_type'], 'owner_id' => $params['owner_id']]);

                                $itemCounter    =   $dirtyResponse['itemCounter'];

                                $chunkCounter   =   $dirtyResponse['chunkCounter'];

                                $response = array_merge($response, $dirtyResponse['response']);

                            } else if ($e->getCode() == 9) {

                                $flood = new Flood();
                                $flood->flood_type = $params['flood_type'];
                                $flood->owner_id = $params['owner_id'];
                                $flood->user_id = auth()->user()->getAuthIdentifier();
                                $flood->post_comments_position = $chunkCounter;
                                $flood->post_comment_position = $itemCounter;
                                $flood->save();

                                session()->flash($params['flood_type'], 'Вы поймали флуд-бан, но лайки были
                                    проставлены в количестве' . ($chunkCounter * 25 - 25 + $itemCounter));

                                return $response;
                            } else {
                                continue;
//                                ddd($e);
                            }
                        }
                    }
                }
            }

            $chunkCounter++;
        }
        session()->flash('posts.autolike_members', 'Лайки на комментарии к постам были выставлены в к-ве:'.$itemCounter);

        $chunkCountable = $chunkCounter - 1;

        $likesQty = $chunkCountable * 25;

        $likesQty += $itemCounter;
        return ['response' => $response, 'likesQty' => $likesQty];
    }

    // Like photos

    protected function captchaSkipperComments($request, $response, $chunk, $chunkCounter, $itemCounter, $params = [])
    {
        $params['record_type'] === 'post' ? $captchaType = 'posts.autoLike-comments' : $captchaType = 'photos.autoLike-comments';
        try {

            $response = array_merge($response, $this->apiConnection->send($request));
            $request = [];

            $itemCounter++;


            sleep(1);
        } catch (\Exception $e) {
            if ($e->getCode() == 14) {
                $request = [];
                $captcha_answer = $this->exceptionHandlerAutoLikesComments($e, $response, [
                    'owner_id' => $e->request_params[0]['value'], 'captchaType' => $captchaType]);

                $params['flood_type'] === 'com_photo_like_flood' ? $type = 'photo_comment' : $type = 'comment';

                foreach ($chunk['items'] as $item)
                {
                    $params['flood_type'] === 'com_photo_like_flood' ? $comment['owner_id'] = $params['owner_id'] : null;
                    if ($item['likes']['user_likes'] === 0)
                    {
                        if ($captcha_answer !== '')
                        {
                            $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                'item_id' => $item['id'], 'type' => $type,
                                'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                            $dirtyResponse = $this->captchaSkipperComments($request, $response, $chunk, $chunkCounter, $itemCounter, $params);

                            $response = array_merge($response, $dirtyResponse['response']);

                            $itemCounter    = $dirtyResponse['itemCounter'];

                            $chunkCounter   = $dirtyResponse['chunkCounter'];

                            $captcha_answer = '';
                        }
                        else
                        {
                            $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                'item_id' => $item['id'], 'type' => $type]);

                            $dirtyResponse = $this->captchaSkipperComments($request, $response, $chunk, $chunkCounter, $itemCounter, $params);

                            $response = array_merge($response, $dirtyResponse['response']);

                            $itemCounter    = $dirtyResponse['itemCounter'];

                            $chunkCounter   = $dirtyResponse['chunkCounter'];
                        }
                    }
                }

                $chunkCounter++;

            } else if ($e->getCode() == 9) {

                $flood = new Flood();
                $flood->flood_type = $params['flood_type'];
                $flood->owner_id = $params['owner_id'];
                $flood->user_id = auth()->user()->getAuthIdentifier();
                $flood->post_comments_position = $chunkCounter;
                $flood->post_comment_position = $itemCounter;
                $flood->save();

                $params['record_type'] === 'post' ? $liked_record_type = 'постов' : $liked_record_type = 'фото';

                session()->flash($params['flood_type'], "Вы поймали флуд-бан при лайкинге {$liked_record_type}, но лайки были
                проставлены в количестве" . ($itemCounter));

                return ['response' => $response, 'flood' => true, 'itemCounter' => $itemCounter, 'chunkCounter' => $chunkCounter];
            } else if ($e->getCode() == 15) {
                null;
            } else if ($e->getCode() == 18) {
                null;
            } else if ($e->getCode() == 30) {
                null;
            } else {
                null;
            }
        }

        return ['response' => $response, 'itemCounter' => $itemCounter, 'chunkCounter' => $chunkCounter];
    }

    protected function exceptionHandlerAutoLikesComments($e, $response, $params = [])
    {
        set_time_limit(0);

        $owner_id = $params['owner_id'];
        $counterCommentsPlace = 'test';
        $counterComment = 'test';
        $captchaType = $params['captchaType'];
        $ruCaptchaKey = '3d7ae9859d9210d36d5a52b535fb8bd8';
        $ruCaptchaResponse = ['id'];
        $captchaAnswer = [
            'status' => 0
        ];

        $url = $e->captcha_img;
        $image = file_get_contents($url);

        if ($image !== false) {
            $finalImg = 'data:image/jpg;base64,' . base64_encode($image);
            if ($ruCaptchaResponse = $this->sendPostToRuCaptcha($ruCaptchaKey, $finalImg)) {
                if ($ruCaptchaResponse['status'] === 1) {
                    $imgId = $ruCaptchaResponse['request'];

                    do {
                        sleep(5);
                        $captchaAnswer = $this->sendGetToRuCaptcha($ruCaptchaKey, $imgId);
                        if ($captchaAnswer['status'] === 1) {
                            return $captchaAnswer['request'];
                        }
                    } while ($captchaAnswer['status'] === 0);
                }
            } else {
                'Что-то пошло не так при передаче капчи на сервер дешифровки, вернитесь назад и попробуйте снова';
            }
        }
    }

    public function photosLike(BasicRequest $request)
    {
        $request->input('owner_id') ? $owner_id = $request->input('owner_id') :
            $owner_id = '';

        $extreme_resume = auth()->user()->captcha()
            ->where('captcha_type', '=', 'unhandled.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($extreme_resume) {
            auth()->user()->captcha()
                ->where('captcha_type', '=', 'unhandled.like')
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }

        $flood = auth()->user()->flood()
            ->where('flood_type', '=', 'photos.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($flood) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'photos.like')
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }
        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }

        $owner_id = $this->getRightID($owner_id);

        $request->input('captcha_key') ? $captcha['key'] = $request->input('captcha_key') :
            $captcha['key'] = '';
        $request->input('captcha_sid') ? $captcha['sid'] = $request->input('captcha_sid') :
            $captcha['sid'] = '';
        $request->input('last_comments') ? $captcha['last_comments'] = $request->input('last_comments') :
            $captcha['last_comments'] = '';
        $request->input('last_comment') ? $captcha['last_comment'] = $request->input('last_comment') :
            $captcha['last_comment'] = '';
        $request->input('record_qty') ? $record_qty = $request->input('record_qty') :
            $record_qty = '';
        $request->input('comment_qty') ? $comment_qty = $request->input('comment_qty') :
            $comment_qty = '';

        if ($owner_id !== 0 || $owner_id === '') {

            if (!empty($captcha['key'])) {
                $photos = $this->getPhotosFromUserAccount($owner_id, ['last_comments' => $captcha['last_comments'], 'photo_comments_count' => $record_qty]);
                $comments = $this->getCommentsFromPhotos($photos, ['last_comment' => $captcha['last_comment'], 'photo_comment_count' => $comment_qty]);
                $likesResponse = $this->likeCommentsForPostsAutoCaptcha($comments,  ['owner_id' => $owner_id, 'flood_type' => 'com_photo_like_flood', 'captcha' => $captcha]);
            } else if ($extreme_resume) {
                $photos = $this->getPhotosFromUserAccount($owner_id, ['last_comments' => $extreme_resume->post_comments_position, 'photo_comments_count' => $record_qty]);
                $comments = $this->getCommentsFromPhotos($photos, ['last_comment' => $extreme_resume->post_comment_position, 'photo_comment_count' => $comment_qty]);
                $likesResponse =  $this->likeCommentsForPostsAutoCaptcha($comments,  ['owner_id' => $owner_id, 'flood_type' => 'com_photo_like_flood', 'captcha' => $captcha]);
            } else if ($flood) {
                $photos = $this->getPhotosFromUserAccount($owner_id, ['last_comments' => $flood->post_comments_position, 'photo_comments_count' => $record_qty]);
                $comments = $this->getCommentsFromPhotos($photos, ['last_comment' => $flood->post_comment_position, 'photo_comment_count' => $comment_qty]);
                $likesResponse =  $this->likeCommentsForPostsAutoCaptcha($comments,  ['owner_id' => $owner_id, 'flood_type' => 'com_photo_like_flood', 'captcha' => $captcha]);
            } else {
                $photos = $this->getPhotosFromUserAccount($owner_id, ['last_comments' => 0, 'photo_comments_count' => $record_qty]);
                $comments = $this->getCommentsFromPhotos($photos, ['last_comment' => [], 'photo_comment_count' => $comment_qty]);
                $likesResponse =  $this->likeCommentsForPostsAutoCaptcha($comments,  ['owner_id' => $owner_id, 'flood_type' => 'com_photo_like_flood', 'captcha' => $captcha]);
            }
        }

        if(!empty($likesResponse['likesQty']))
        {
            Statistic::update_likes(['photo_comments_likes' => $likesResponse['likesQty'], 'all_likes' => $likesResponse['likesQty']]);
        }

        $captchaDB = auth()->user()->captcha()
            ->where('captcha_type', '=', 'photos.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $unhandledDB = auth()->user()->captcha()
            ->where('captcha_type', '=', 'unhandled.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $floodDB = auth()->user()->flood()
            ->where('flood_type', '=', 'photos.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($captchaDB) {
            session()->flash('com_photo_like_captcha', 'Лайки были проставлены не всем комментариям, вы получили капчу, продолжение будет с последнего выставленого лайка');

            return view('dashboard', ['captcha' => $captchaDB]);
        } else if ($unhandledDB) {
            session()->flash('com_photo_like_unhandled', 'Вконтакте разорвал подключение, лайки были проставлены не всем комментариям, попробуйте снова и запись начнется с последних');

            return redirect('dashboard');
        } else if ($floodDB) {
            session()->flash('com_photo_like_flood', 'Лайки были проставлены, но вконтакте остановил вас за флуд, поумерьте свой пыл и продолжите минимум через  час');

            return redirect('dashboard');
        } else {
            session()->flash('com_photo_like', 'Лайки были проставлены');

            return redirect('dashboard');
        }
    }

    protected function getPhotosFromUserAccount($user_id = '', $fields = [])
    {
        $items = [];
        $fields['last_comments'] ? $offset = $fields['last_comments'] : $offset = 0;

        if ($fields['photo_comments_count'] > 200)
        {
            $count = 200;
        }
        else
        {
            $count = $fields['photo_comments_count'];
        }

        do {
            $request = new Request('photos.getAll',
                ['owner_id' => $user_id, 'offset' => $offset, 'count' => $count]);

            $response = $this->apiConnection->send($request);

            $items = array_merge($items, $response['response']['items']);

            $offset = count($items);

            sleep(1);
        } while ($response['response']['count'] > $offset && $fields['photo_comments_count'] > $offset);

        return $items;
    }

    protected function getCommentsFromPhotos($photos, $fields = [], $response = [])
    {
        $executeRequests = [];
        $response = [];

        foreach (array_chunk($photos, 25) as $photo_chunk) {
            foreach ($photo_chunk as $photo) {
                $fields['last_comment'] ? $executeRequests[] = new Request('photos.getComments',
                    ['owner_id' => $photo['owner_id'], 'photo_id' => $photo['id'], 'offset' => $fields['last_comment'],
                        'count' => $fields['photo_comment_count'], 'need_likes' => true]) :
                    $executeRequests[] = new Request('photos.getComments',
                        ['owner_id' => $photo['owner_id'], 'need_likes' => true,
                            'photo_id' => $photo['id'], 'count' => $fields['photo_comment_count']]);
            }
            sleep(1);

            $vkResponse = $this->apiConnection->send(ExecuteRequest::make($executeRequests))['response'];

            $response = array_merge($response, $vkResponse);
            unset($executeRequests);

        }

        return $response;
    }

    protected function likeCommentsForPhoto($owner_id, $commentsPlace, $params = [], $response = [])
    {
        $counterCommentsPlace = 0;
        $counterComment = 0;
        $executeRequests = [];
        foreach ($commentsPlace as $comments) {
            $counterComment = 0;
            if (!empty($comments['count'])) {
                foreach ($comments['items'] as $comment) {
                    try {
                        sleep(1);
                        if (!empty($params['captcha']['key'])) {
                            $response[] = $this->apiConnection->send(new Request('likes.add',
                                ['item_id' => $comment['id'], 'type' => 'photo_comment', 'owner_id' => $owner_id,
                                    'captcha_sid' => $params['captcha']['sid'], 'captcha_key' => $params['captcha']['key']]));

                            auth()->user()->captcha()
                                ->where('captcha_type', '=', 'photos.like')
                                ->orderBy('created_at', 'desc')
                                ->latest()
                                ->first()
                                ->delete();

                            $params = [];
                        } else {
                            $response[] = $this->apiConnection->send(new Request('likes.add',
                                ['item_id' => $comment['id'], 'type' => 'photo_comment', 'owner_id' => $owner_id]));
                        }

                    } catch (\Exception $e) {

                        return $this->exceptionHandler($e, $response, ['owner_id' => $owner_id, 'counterCommentsPlace' => $counterCommentsPlace,
                            'counterComment' => $counterComment, 'captchaType' => 'photos.like']);

                    }
                    $counterComment++;
                }
            }
            $counterCommentsPlace++;
        }

        return $response;
    }

    //TODO: Сделать одну общую функцию для проставки лайков

    /////

    public function addFriends(BasicRequest $request)
    {
        $tags = $this->transformTextToTags($request->input('tags'));

        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }
        $unsortedRequests = $this->getFriendRequests($request->input('requests'), 1, []);

        $sortedRequests = $this->getSortedUsersByTags($tags, $unsortedRequests, true);

        $response = [];
        foreach ($sortedRequests as $item) {
            $user_id = $item['user_id'];
            try {
                sleep(1);
                $apiRequest = new Request('friends.add', ['user_id' => $user_id]);
            } catch (\Exception $e) {
                if ($e->getCode() == 15) {
                    continue;
                } else if ($e->getCode() == 18) {
                    continue;
                } else if ($e->getCode() == 30) {
                    continue;
                } else {
                    null;
                }
            }


            $response[$user_id] = $this->apiConnection->send($apiRequest);
        }

        session()->flash('adding_friend', 'Друзья успешно добавлены');

        return redirect('dashboard');
    }

    //$user_id_format - если true, значит vk отправил ответ по id пользователя в формате user_id, если false то в формате - id

    protected function getSortedUsersByTags($tags, $users, $user_id_format)
    {
        $sortedUsers = [];

        foreach ($users as $user) {
            try {
                $user_id_format ? $posts = $this->searchPostsOnWallByTags($tags, $user['user_id']) :
                    $posts = $this->searchPostsOnWallByTags($tags, $user);

                sleep(1);

                if (!empty($posts['response']['items'])) {
                    $sortedUsers[] = $user;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $sortedUsers;
    }

    protected function searchPostsOnWallByTags($tags, $user_id, $params = [])
    {
        $request = new Request('wall.search', ['owner_id' => $user_id, 'query' => $tags]);

        $response = $this->apiConnection->send($request);

        return $response;
    }

    public function deleteFriends(BasicRequest $request)
    {
        $tags = $this->transformTextToTags($request->input('tags'));

        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }
        $unsortedRequests = $this->getAllFriends([], $this->apiConnection);

        $sortedRequests = $this->getSortedUsersByTags($tags, $unsortedRequests, false);

        $response = [];
        foreach ($sortedRequests as $item) {
            sleep(1);
            $user_id = $item;
            $apiRequest = new Request('friends.delete', ['user_id' => $user_id]);

            try {
                $response[$user_id] = $this->apiConnection->send($apiRequest);
            } catch (\Exception $e) {
                if ($e->getCode() == 15) {
                    continue;
                } else if ($e->getCode() == 18) {
                    continue;
                } else if ($e->getCode() == 30) {
                    continue;
                } else {
                    null;
                }
            }
        }

        session()->flash('delete_friend', 'Друзья успешно удалены');

        return redirect('dashboard');
    }


    public function acceptFriendsRequest(BasicRequest $request) //
    {
        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }
        $requests_count = $request->input('requests');
        $friends_count = $request->input('friends');
        $followers_count = $request->input('followers');
        $posts_count = $request->input('posts');

        $friends_requests_fromDB = Limits::where(['user_id' => auth()->user()->getAuthIdentifier(),
            'type' => 'friend_request_limit', ['created_at', '>', date('Y-m-d H:i:s', time() - 86400)]])->latest()->first();

        if(empty($friends_requests_fromDB))
        {
            $friends_requests_fromDB = 0;
        }
        else
        {
            $friends_requests_fromDB = $friends_requests_fromDB->qty;
        }
//
//        ddd($friends_requests_fromDB);

        $usersIDs = [];
        $paramsForCheck = 'deactivated,friend_status,blacklisted_by_me,blacklisted';

        $acceptingMessage = $request->input('message_text', '');



        //Tag params
        if($request->input('tagSearchAllowed') === 'on')
        {
            $tag_params = [];

            $request->input('tags') ? $tag_params['tags'] = $this->transformTextToTags($request->input('tags')) : $tag_params['tags'] = '';

            $tag_params['start_time'] = strtotime($request->input('searchStart', ''));
            $tag_params['end_time'] = strtotime($request->input('searchEnd', ''));

            $tag_params['fields'] = $paramsForCheck;
            $tag_params['profiles'] = true;

            $tag_params['tag_requests'] = $request->input('tag_requests', 1);

            if($tag_params['end_time'] === $tag_params['start_time'])
            {
                $tag_params['end_time'] += 86400;
            }

            $tag_params['count'] = $request->input('tag_requests', '5');

            $records = $this->getAllSearchRecords($tag_params, $this->apiConnection);

            $tagUsersID = $this->getUsersIDfromRecords($records);

//            ddd($tagUsersID);

            $usersIDs = array_merge($usersIDs, array_slice($tagUsersID,0,$tag_params['tag_requests']));
        }
        //


        //Group members
        if($request->input('groupMembersSearchAllowed') === 'on')
        {
            $group_url = $request->input('members_group_id');

            $group_id = $this->getGroupID($group_url);

            $group_members_qty = $request->input('group_members_qty', 1);

            $group_members_offset = $request->input('group_members_offset', 0);

            try
            {
                $groupMembers = $this->getMembers($group_id, [ 'offset' => $group_members_offset,
                    'count' => $group_members_qty, 'api_connection' => $this->apiConnection ])['response']['items'];
            }
            catch (\Exception $e)
            {
                session()->flash('new_friends_by_group_members_invalid', 'Произошла ошибка при получении подписчиков, проверьте введенный ID группы');
                return redirect('dashboard');
            }

            $usersIDs = array_merge($usersIDs, $groupMembers);
        }
        //

        //Group post comments
        if($request->input('groupPostCommentsSearchAllowed') === 'on')
        {
            $findFriendsInCommentsParams =
            [
                'owner_id' => $this->getRightID($request->input('owner_id_for_findFriendsInPostComments', '')),
                'request_qty' => $request->input('findFriendsInPostCommentsQty', 1),
                'record_qty' => $request->input('record_qty_for_findFriendsInPostComments', ''),
                'comment_qty' => $request->input('comment_qty_for_findFriendsInPostComments', ''),
                'record_offset' => $request->input('record_offset_for_findFriendsInPostComments', ''),
                'comment_offset' => $request->input('comment_offset_for_findFriendsInPostComments', ''),
            ];

            $posts = $this->getWallInfo($findFriendsInCommentsParams['owner_id'], $findFriendsInCommentsParams['record_offset'], $findFriendsInCommentsParams['record_qty'])['items'];
            $comments_block = $this->getCommentsFromPosts($posts, ['last_comment' => $findFriendsInCommentsParams['comment_offset'],
                'post_comment_count' => $findFriendsInCommentsParams['comment_qty'], 'fields' => $paramsForCheck]);

            $users_from_comments = [];

            foreach ($comments_block as $comment_block)
            {
                foreach ($comment_block['items'] as $comment)
                {
                    if(!isset($comment['deleted']) && isset($comment['from_id']))
                    {
                        if($comment['from_id'] > 0)
                        {
                            $users_from_comments[] = $comment['from_id'];
                        }
                    }
                }
            }

            $usersIDs = array_merge($usersIDs, array_slice($users_from_comments, 0, $findFriendsInCommentsParams['request_qty']));
        }
        //

        //Group photos comments
        if($request->input('groupPhotosCommentsSearchAllowed') === 'on')
        {
            $findFriendsInPhotosCommentsParams =
                [
                    'owner_id' => $this->getRightID($request->input('owner_id_for_findFriendsInPhotosComments', '')),
                    'request_qty' => $request->input('findFriendsPhotosInCommentsQty', 1),
                    'record_qty' => $request->input('record_qty_for_findFriendsInPhotosComments', ''),
                    'comment_qty' => $request->input('comment_qty_for_findFriendsInPhotosComments', ''),
                    'record_offset' => $request->input('record_offset_for_findFriendsInPhotosComments', ''),
                    'comment_offset' => $request->input('comment_offset_for_findFriendsInPhotosComments', ''),
                ];

            $photos = $this->getPhotosFromUserAccount($findFriendsInPhotosCommentsParams['owner_id'],
                ['last_comments' => $findFriendsInPhotosCommentsParams['record_offset'],
                 'photo_comments_count' => $findFriendsInPhotosCommentsParams['record_qty']]);

//            ddd($photos);

            $photos_comments_block = $this->getCommentsFromPhotos($photos,
                ['last_comment' => $findFriendsInPhotosCommentsParams['comment_offset'],
                 'photo_comment_count' => $findFriendsInPhotosCommentsParams['comment_qty']]);

//            ddd($photos_comments_block);

            $users_from_photosComments = [];
            foreach ($photos_comments_block as $comment_block)
            {
                foreach ($comment_block['items'] as $comment)
                {
                    if($comment['from_id'] > 0)
                    {
                        $users_from_photosComments[] = $comment['from_id'];
                    }
                }
            }

//            ddd($users_from_photosComments);

            $usersIDs = array_merge($usersIDs, array_slice($users_from_photosComments,0,
                $findFriendsInPhotosCommentsParams['request_qty']));

//            ddd($usersIDs);
        }
        //

        //Лайкающие посты
        if($request->input('groupLikedPostsAllow') === 'on')
        {
            $findFriendsInPostsLikesParams =
                [
                    'owner_id' => $this->getRightID($request->input('owner_id_for_findFriendsInPostsLikes', '')),
                    'request_qty' => $request->input('findFriendsInPostsLikesQty', 1),
                    'record_qty' => $request->input('record_qty_for_findFriendsInPostsLikes', ''),
                    'likes_qty' => $request->input('likes_qty_for_findFriendsInPostsLikes', ''),
                    'record_offset' => $request->input('record_offset_for_findFriendsInPostsLikes', ''),
                    'likes_offset' => $request->input('likes_offset_for_findFriendsInPostsLikes', ''),
                ];

            $posts = $this->getWallInfo($findFriendsInPostsLikesParams['owner_id'],
                $findFriendsInPostsLikesParams['record_offset'],
                    $findFriendsInPostsLikesParams['record_qty'])['items'];

            $postsUserLikes = [];
            foreach ($posts as $post)
            {
                $postsUserLikes = array_merge($postsUserLikes, $this->getObjectUserLikes(['object_type' => 'post',
                    'owner_id' => $post['owner_id'], 'item_id' => $post['id'],
                    'count' => $findFriendsInPostsLikesParams['likes_qty'],
                    'offset' => $findFriendsInPostsLikesParams['likes_offset']
                    ], $this->apiConnection));
            }

            $usersIDs = array_merge($usersIDs, array_slice($postsUserLikes, 0,
                $findFriendsInPostsLikesParams['request_qty']));
        }
        //

        //Лайкающие фотографии
        if($request->input('groupLikedPhotosAllow') === 'on')
        {
            $findFriendsInPhotosLikesParams =
                [
                    'owner_id' => $this->getRightID($request->input('owner_id_for_findFriendsInPhotosLikes', '')),
                    'request_qty' => $request->input('findFriendsInPhotosLikesQty', 1),
                    'record_qty' => $request->input('record_qty_for_findFriendsInPhotosLikes', ''),
                    'likes_qty' => $request->input('likes_qty_for_findFriendsInPhotosLikes', ''),
                    'record_offset' => $request->input('record_offset_for_findFriendsInPhotosLikes', ''),
                    'likes_offset' => $request->input('likes_offset_for_findFriendsInPhotosLikes', ''),
                ];

            $photos = $this->getPhotosFromUserAccount($findFriendsInPhotosLikesParams['owner_id'],
                ['last_comments' => $findFriendsInPhotosLikesParams['record_offset'],
                    'photo_comments_count' => $findFriendsInPhotosLikesParams['record_qty']]);

            $photosUserLikes = [];
            foreach ($photos as $photo)
            {
                $usersIDs = array_merge($photosUserLikes, $this->getObjectUserLikes(['object_type' => 'photo',
                    'owner_id' => $photo['owner_id'], 'item_id' => $photo['id'],
                    'count' => $findFriendsInPhotosLikesParams['likes_qty'],
                    'offset' => $findFriendsInPhotosLikesParams['likes_offset']
                ], $this->apiConnection));
            }

            $usersIDs = array_merge($usersIDs, array_slice($photosUserLikes, 0,
                $findFriendsInPhotosLikesParams['request_qty']));
        }
        //

        //Получение списка пользователей конкурентов
        if($request->input('concurrentsSearchAllow') === 'on')
        {
            $vk_record_url_params = $this->getPostParams($request->input('owner_id_for_findFriendsRecordsConcurrents', ''));

            if (isset($vk_record_url_params['not_right']))
            {
                session()->flash('not_valid_new_friends_concurrents', 'Вы ввели url записи в неправильном формате, попробуйте снова');
                return redirect('dashboard');
            }

            $request->input('recordTypeIsPhoto') === 'on' ? $record_type = 'photo' : $record_type = 'post';

            $findFriendsInVkRecordConcurrents =
                [
                    'owner_id' => $vk_record_url_params['owner_id'],
                    'item_id' => $vk_record_url_params['item_id'],
                    'request_qty' => $request->input('findFriendsInRecordConcurrents', 1),
                    'comments_qty' => $request->input('comments_qty_for_findFriendsInRecordConcurrents', ''),
                    'likes_qty' => $request->input('likes_qty_for_findFriendsInRecordConcurrents', ''),
                    'comments_offset' => $request->input('comments_offset_for_findFriendsInRecordConcurrents', ''),
                    'likes_offset' => $request->input('likes_offset_for_findFriendsInRecordConcurrents', ''),
                ];
            $currentUserId = $this->apiConnection->send(new \ATehnix\VkClient\Requests\Request('account.getProfileInfo', []))['response']['id'];

            $userLikes = $this->getValues('likes.getList', ['type' => $record_type,
                'owner_id' => $findFriendsInVkRecordConcurrents['owner_id'],
                'item_id' => $findFriendsInVkRecordConcurrents['item_id'],
                'count' => $findFriendsInVkRecordConcurrents['likes_qty'],
                'offset' => $findFriendsInVkRecordConcurrents['likes_offset'],
                'skip_own' => 1], $this->apiConnection);


            if ($record_type === 'post')
            {
                $userComments = $this->getValues('wall.getComments',
                    [
                        'owner_id' => $findFriendsInVkRecordConcurrents['owner_id'],
                        'post_id' => $findFriendsInVkRecordConcurrents['item_id'],
                        'count' => $findFriendsInVkRecordConcurrents['comments_qty'],
                        'offset' => $findFriendsInVkRecordConcurrents['comments_offset']
                    ], $this->apiConnection);
            }
            else
            {
                $userComments = $this->getValues('photos.getComments',
                    [
                        'owner_id' => $findFriendsInVkRecordConcurrents['owner_id'],
                        'photo_id' => $findFriendsInVkRecordConcurrents['item_id'],
                        'count' => $findFriendsInVkRecordConcurrents['comments_qty'],
                        'offset' => $findFriendsInVkRecordConcurrents['comments_offset']
                    ], $this->apiConnection);
            }

            $userSortedComments = $this->getAllSortedComments($userComments, ['from_id' => $currentUserId]);

            $usersIDs = array_merge($usersIDs, array_slice(array_merge($userSortedComments, array_diff($userLikes, $userSortedComments)), 0, $findFriendsInVkRecordConcurrents['request_qty']));
        }
        //

        //Добавление пользователей по загруженому списку пользователей
        if($request->input('loadedListAllow') === 'on')
        {
            $owner_names_list = $request->input('owner_ids_list', '');
            $owner_names_array = explode(',', $owner_names_list);

            $owner_ids_array = [];
            foreach ($owner_names_array as $owner_name)
            {
                $owner_ids_array[] = $this->getRightID($owner_name);
            }

            $usersIDs = array_merge($usersIDs, $owner_ids_array);
        }
        //

//
//        ddd($usersIDs);

//        ddd([
//                $request->input('loadedListAllow'),
//                $request->input('concurrentsSearchAllow'),
//                $request->input('tagSearchAllowed'),
//                $request->input('groupMembersSearchAllowed'),
//                $request->input('groupPostCommentsSearchAllowed'),
//                $request->input('groupPhotosCommentsSearchAllowed'),
//                $request->input('groupLikedPostsAllow'),
//                $request->input('groupLikedPhotosAllow')
//            ]);



            $friend_req_response = $this->sendFriendRequests(['users_id' => $usersIDs,
                'message_text' => $acceptingMessage, 'limit_counter' => $friends_requests_fromDB], $this->apiConnection);



        $sended_friends_requests_qty = $friend_req_response['limit_counter'];

        $friend_req_response_success = $friend_req_response['response'];

        $saveInDbStatus = $this->changeFriendRequestLimitInDB($sended_friends_requests_qty);

        if($saveInDbStatus)
        {
            session(['accepting_friends' => 'Друзья успешно добавлены, количество добавленых друзей за день - ' . $sended_friends_requests_qty . ' при лимите 50 друзей в сутки']);
        }
        else if ($friend_req_response_success)
        {
            session(['accepting_friends' => 'Друзья были добавлены, но произошла ошибка при записе результатов в базу данных']);
        }
        else
        {
            session(['accepting_friends' => 'Друзья не были добавлены, попробуйте снова']);
        }


        $add_friend_request = [];
        $delete_friend_request = [];

        $friend_requests = $this->getFriendRequests($requests_count, 1, 'photo_200_orig');

        foreach ($friend_requests as $friend) {

            $delete_friend_request[] = $friend['user_id'];

            if ($request->input('delete-banned') == true) {
                if (!array_key_exists('deactivated', $friend)) {
                    if ($request->input('photo') == true) {
                        if (!preg_match('/vk.com/', $friend['photo_200_orig'])) {
                            if ($request->input('hidden') == true) {
                                if ($friend['is_closed'] == false) {
                                    $user_info = $this->getUserStatistics($friend['user_id']);
                                    $user_friends = $user_info[0][0]['counters']['friends'];
                                    $user_followers = $user_info[0][0]['counters']['followers'];
                                    $user_posts = $user_info[1]['count'];

                                    if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                        $add_friend_request[] = $friend['user_id'];
                                    }
                                }
                            } elseif ($request->input('hidden') == false) {
                                if ($friend['is_closed'] == true) {

                                    $add_friend_request[] = $friend['user_id'];
                                } else {
                                    $user_info = $this->getUserStatistics($friend['user_id']);
                                    $user_friends = $user_info[0][0]['counters']['friends'];
                                    $user_followers = $user_info[0][0]['counters']['followers'];
                                    $user_posts = $user_info[1]['count'];

                                    if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                        $add_friend_request[] = $friend['user_id'];
                                    }
                                }
                            }
                        }
                    } elseif ($request->input('photo') == false) {
                        if ($request->input('hidden') == true) {
                            if ($friend['is_closed'] == false) {
                                $user_info = $this->getUserStatistics($friend['user_id']);
                                $user_friends = $user_info[0][0]['counters']['friends'];
                                $user_followers = $user_info[0][0]['counters']['followers'];
                                $user_posts = $user_info[1]['count'];

                                if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                    $add_friend_request[] = $friend['user_id'];
                                }
                            }
                        } elseif ($request->input('hidden') == false) {
                            if ($friend['is_closed'] == true) {

                                $add_friend_request[] = $friend['user_id'];
                            } else {
                                $user_info = $this->getUserStatistics($friend['user_id']);
                                $user_friends = $user_info[0][0]['counters']['friends'];
                                $user_followers = $user_info[0][0]['counters']['followers'];
                                $user_posts = $user_info[1]['count'];

                                if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                    $add_friend_request[] = $friend['user_id'];
                                }
                            }
                        }
                    }
                }
            } elseif ($request->input('delete-banned') == false) {
                if (array_key_exists('deactivated', $friend)) {

                    $add_friend_request[] = $friend['user_id'];
                } else {
                    if ($request->input('photo') == true) {
                        if (!preg_match('/vk.com/', $friend['photo_200_orig'])) {
                            if ($request->input('hidden') == true) {
                                if ($friend['is_closed'] == false) {
                                    $user_info = $this->getUserStatistics($friend['user_id']);
                                    $user_friends = $user_info[0][0]['counters']['friends'];
                                    $user_followers = $user_info[0][0]['counters']['followers'];
                                    $user_posts = $user_info[1]['count'];

                                    if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                        $add_friend_request[] = $friend['user_id'];
                                    }
                                }
                            } elseif ($request->input('hidden') == false) {
                                if ($friend['is_closed'] == true) {

                                    $add_friend_request[] = $friend['user_id'];
                                } else {
                                    $user_info = $this->getUserStatistics($friend['user_id']);
                                    $user_friends = $user_info[0][0]['counters']['friends'];
                                    $user_followers = $user_info[0][0]['counters']['followers'];
                                    $user_posts = $user_info[1]['count'];

                                    if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                        $add_friend_request[] = $friend['user_id'];
                                    }
                                }
                            }
                        }
                    } elseif ($request->input('photo') == false) {
                        if ($request->input('hidden') == true) {
                            if ($friend['is_closed'] == false) {
                                $user_info = $this->getUserStatistics($friend['user_id']);
                                $user_friends = $user_info[0][0]['counters']['friends'];
                                $user_followers = $user_info[0][0]['counters']['followers'];
                                $user_posts = $user_info[1]['count'];

                                if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                    $add_friend_request[] = $friend['user_id'];
                                }
                            }
                        } elseif ($request->input('hidden') == false) {
                            if ($friend['is_closed'] == true) {

                                $add_friend_request[] = $friend['user_id'];
                            } else {
                                $user_info = $this->getUserStatistics($friend['user_id']);
                                $user_friends = $user_info[0][0]['counters']['friends'];
                                $user_followers = $user_info[0][0]['counters']['followers'];
                                $user_posts = $user_info[1]['count'];

                                if ($user_friends >= $friends_count && $user_followers >= $followers_count && $user_posts >= $posts_count) {

                                    $add_friend_request[] = $friend['user_id'];
                                }
                            }
                        }
                    }
                }
            }
            sleep(1);
        }

        $delete_friend_request = array_diff($delete_friend_request, $add_friend_request);

        call_user_func(function () use ($add_friend_request, $delete_friend_request) {
            foreach (array_chunk($add_friend_request, 25) as $chuck) {
//                $data = [];
                foreach ($chuck as $id) {
                    sleep(1);
                    $dataReq = new Request('friends.add', ['user_id' => $id]);
                    try {
                        $response = $this->apiConnection->send($dataReq);
                    }
                    catch (\Exception $e)
                    {
                        continue;
                    }
                }
//                $execute = ExecuteRequest::make($data);
//                $response = $this->apiConnection->send($execute);
                sleep(1);
            }

            foreach (array_chunk($delete_friend_request, 25) as $chuck) {
//                $data = [];
                foreach ($chuck as $id) {
                    sleep(1);
                    $dataReq = new Request('friends.delete', ['user_id' => $id]);
                    try {
                        $response = $this->apiConnection->send($dataReq);
                    }
                    catch (\Exception $e)
                    {
                        continue;
                    }
                }
//                $execute = ExecuteRequest::make($data);
//                $response = $this->apiConnection->send($execute);
                sleep(1);
            }
        });


        $data = Statistic::where('user_id', $request->user()->id)->first();

        if (isset($data)) {
            if (isset($data->data['accept_followers'])) {
                $data->forceFill(['data->accept_followers' => $data->data['accept_followers'] + count($add_friend_request)])->save();
            } else {
                $data->forceFill(['data->deleted_friends' => count($add_friend_request)])->save();
            }
        } else {
            $data = Statistic::create([
                'user_id' => $request->user()->id,
                'data' => ['accept_followers' => count($add_friend_request)],
            ]);
        }

        return redirect()->route('dashboard', ['captcha' => [], 'data' => $data]);
    }

    public function deleteBannedFriends(BasicRequest $request) //
    {
        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }
        $friends = $this->getAllFriends('photo_200_orig', $this->apiConnection);
        $delete_friends = [];

        foreach ($friends as $friend) {

            if (array_key_exists('deactivated', $friend)) {
                if ($request->input('banned') == true || $request->input('deleted') == true) {
                    if ($friend['deactivated'] == 'banned') {
                        // echo "delete banned friend " . $friend['id'] . "<br>";
                        // $api_request = new Request('friends.delete', ['user_id' => $friend['id']]);
                        $delete_friends[] = $friend['id'];
                    } elseif ($friend['deactivated'] == 'deleted') {
                        // echo "delete deleted friend " . $friend['id'] . "<br>";
                        // $api_request = new Request('friends.delete', ['user_id' => $friend['id']]);
                        $delete_friends[] = $friend['id'];
                    }
                    // $response = $this->apiConnection->send($api_request);
                    // sleep(1);
                }
            }
        }

        call_user_func(function () use ($delete_friends) {
            foreach (array_chunk($delete_friends, 25) as $chuck) {
                $data = [];
                foreach ($chuck as $id) {
                    $data[] = new Request('friends.delete', ['user_id' => $id]);
                }
                $execute = ExecuteRequest::make($data);
                $response = $this->apiConnection->send($execute);
                sleep(1);
            }
        });

        $data = Statistic::where('user_id', $request->user()->id)->first();

        if (isset($data)) {
            if (isset($data->data['deleted_friends'])) {
                $data->forceFill(['data->deleted_friends' => $data->data['deleted_friends'] + count($delete_friends)])->save();
            } else {
                $data->forceFill(['data->deleted_friends' => count($delete_friends)])->save();
            }
        } else {
            $data = Statistic::create([
                'user_id' => $request->user()->id,
                'data' => [
                    'deleted_friends' => count($delete_friends)
                ],
            ]);
        }

        return redirect()->route('dashboard', ['captcha' => [], 'data' => $data]);
    }
}
