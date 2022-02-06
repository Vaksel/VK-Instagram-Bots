<?php


namespace App\Http\Controllers;


use ATehnix\VkClient\Client;
use ATehnix\VkClient\Requests\ExecuteRequest;
use ATehnix\VkClient\Requests\Request;
use Carbon\Traits\Date;
use DateTime;
use Illuminate\Http\Request as BaseRequest;

use Illuminate\Support\Facades\Auth;

use App\Models\Flood;
use App\Models\Statistic;

use App\Http\Controllers\Service\ServiceActionsController;

class AutoLikesController extends ServiceActionsController
{
    protected $apiConnection;

    public function __construct()
    {
        $this->middleware('auth');
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

    protected function getUsersWithAttributes($user_ids, $params = [], $fields = '')
    {
        $userQty = count($user_ids);

        $request = [];
        $response = [];
        $usersList = [];

        foreach (array_chunk($user_ids, 20) as $chunk) {
            foreach ($chunk as $thousandIds) {
                $user_ids_string = implode(',', $thousandIds);
                $request[] = new Request('users.get', ['user_ids' => $user_ids_string, 'fields' => $fields]);
            }
            $response = $this->apiConnection->send(ExecuteRequest::make($request))['response']; // array with 1000 users array, 0 => 1000, 1 => 1000

            foreach ($response as $userChunk) {
                $usersList = array_merge($usersList, $userChunk);
            }
            unset($response);

            unset($request);

            sleep(1);
        }

        return $usersList;
    }

    protected function getAllWallPosts($users, $record_count)
    {
        $posts = [];

        foreach ($users as $user) {

                $request = new Request('wall.get', ['owner_id' => $user['id'], 'count' => $record_count]);
                try
                {
                    $response = $this->apiConnection->send($request);
                }
                catch (\Exception $e)
                {
                    continue;
                }

                sleep(1);

                $items = $response['response']['items'];
                $posts = array_merge($posts, $items);

        }

        return $posts;
    }

    protected function getAllPhotos($users, $record_count)
    {

        $photos = [];
        foreach ($users as $user) {

                $request = new Request('photos.getAll', ['owner_id' => $user['id'], 'count' => $record_count,
                    'extended' => 1]);

                sleep(2);
                try
                {
                    $response = $this->apiConnection->send($request);
                }
                catch (\Exception $e)
                {
                    continue;
                }

                $items = $response['response']['items'];
                $photos = array_merge($photos, $items);

        }

        return $photos;
    }

    protected function getAllPhotosById($users, $recordCount)
    {
        $response = [
            'response' => [
                'count' => 1
            ]
        ];

        $offset = 0;
        $photos = [];
        foreach ($users as $user) {

                $request = new Request('photos.getAll', ['owner_id' => $user, 'count' => $recordCount, 'offset' => $offset,
                    'extended' => 1]);

                sleep(2);
                try{
                    $response = $this->apiConnection->send($request);

                    $items = $response['response']['items'];
                    $photos = array_merge($photos, $items);

                    $offset = $offset + count($items);
                }
                catch (\Exception $e)
                {
                    if($e->getCode() == 30 || $e->getCode() == 18 || $e->getCode() == 15)
                    {
                        continue;
                    }
                }

        }

        return $photos;
    }

    protected function getCommentsFromPost($post_id, $params = [], $fields = '')
    {
        $request = new Request('wall.getComments', ['owner_id' => $post_id, 'extended' => 1, 'fields' => $fields]);
    }

    protected function likePosts($posts, $params, $fields = '')
    {

        set_time_limit(0);

        sleep(1);

        $request = [];
        $response = [];
        $captcha = $params['captcha'];

        $chunkReturnPosition = 0;
        $itemReturnPosition = 0;


        $chunkCounter = 0;
        $itemCounter = 0;

        $likeCounter = 0;

        if(isset($params['captcha']->post_comments_position))
        {
            $chunkReturnPosition = $params['captcha']->post_comments_position;
            $itemReturnPosition = $params['captcha']->post_comment_position;
        }

        foreach (array_chunk($posts, 25, true) as $chunk) {

            if($chunkReturnPosition >= $chunkCounter)
            {
                foreach ($chunk as $item) {

                    if($itemReturnPosition >= $itemCounter) {

                        if ($likeCounter < $params['like_limit'] || $params['like_limit'] === null)
                        {
//                            ddd(['likeCounter' => $likeCounter, 'likeLimit' => $params['like_limit'], 'chunkReturnPos' => $chunkReturnPosition, 'item' => $item]);
                            if (isset($item['likes']['user_likes']) && $item['likes']['user_likes'] === 0) {
                                $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                    'item_id' => $item['id'], 'type' => 'post']);
//
//                                try {
                                    if($params['like_limit'] === null)
                                    {
                                        $dirtyResponse = $this->captchaSkipper($request, $response, $chunk, $chunkCounter, $itemCounter,
                                            ['record_type' => 'post', 'flood_type' => 'posts.autolike_members', 'owner_id' => $params['owner_id']]);

                                        $response = array_merge($response, $dirtyResponse['response']);

                                        $itemCounter = $dirtyResponse['item_counter'];

                                        $chunkCounter = $dirtyResponse['chunk_counter'];

                                        sleep(1);
                                    }
                                    else
                                    {
                                        $dirtyResponse = $this->captchaSkipperFriendsMembers($request, $response, $chunk, $chunkCounter, $itemCounter,
                                            ['record_type' => 'post', 'flood_type' => 'posts.autolike_members', 'owner_id' => $params['owner_id'], 'like_counter' => $likeCounter,
                                                'like_limit' => $params['like_limit']]);

                                        $response = array_merge($response, $dirtyResponse['responseFromVK']);
                                        $likeCounter = $dirtyResponse['qtyOfSuccessLikes'];
                                    }

                                    sleep(1);

                                    $itemCounter++;
                                    $itemReturnPosition++;

                                    sleep(1);
//
//                                } catch (\Exception $e) {
//                                    if ($e->getCode() == 14) {
//                                        $request = [];
//                                        $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response,
//                                            ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'posts.autoLike']);
//
//                                        foreach ($chunk as $innerItem) {
//                                            if ($innerItem['likes']['user_likes'] === 0) {
//                                                if ($captcha_answer !== '') {
//                                                    $request = new Request('likes.add', ['owner_id' => $innerItem['owner_id'],
//                                                        'item_id' => $innerItem['id'], 'type' => 'post',
//                                                        'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);
//
//                                                    $response = array_merge($response, $this->captchaSkipper($request, $response, $chunk, $chunkCounter, $itemCounter, ['record_type' => 'post', 'flood_type' => 'posts.autolike_members', 'owner_id' => $params['owner_id']]));
//                                                    $captcha_answer = '';
//                                                } else {
//                                                    $request = new Request('likes.add', ['owner_id' => $innerItem['owner_id'],
//                                                        'item_id' => $innerItem['id'], 'type' => 'post']);
//                                                    $response = array_merge($response, $this->captchaSkipper($request, $response, $chunk, $chunkCounter, $itemCounter, ['record_type' => 'post', 'flood_type' => 'posts.autolike_members', 'owner_id' => $params['owner_id']]));
//                                                }
//                                            }
//                                        }
//                                    } else if ($e->getCode() == 9) {
//
//                                        $flood = new Flood();
//                                        $flood->flood_type = $params['flood_type'];
//                                        $flood->owner_id = $params['owner_id'];
//                                        $flood->user_id = auth()->user()->getAuthIdentifier();
//                                        $flood->post_comments_position = $chunkCounter;
//                                        $flood->post_comment_position = $itemCounter;
//                                        $flood->save();
//
//                                        session()->flash($params['flood_type'], 'Вы поймали флуд-бан, но лайки были
//                                    проставлены в количестве' . ($chunkCounter * 25 - 25 + $itemCounter));
//
//                                        return $response;
//                                    } else if ($e->getCode() == 15) {
//                                        continue;
//                                    } else if ($e->getCode() == 18) {
//                                        continue;
//                                    } else if ($e->getCode() == 30) {
//                                        continue;
//                                    } else {
//                                        continue;
//                                    }
//                                }
                            }
                        }
                        else
                        {
                            break;
                        }
                    }
                }
                $itemCounter = 0;
                $itemReturnPosition = 0;
            }

//            }

            $chunkCounter++;
            $chunkReturnPosition++;
        }

        $likeQty = $chunkCounter * 25 - (25 - $itemCounter);

        $likeCounter = $likeQty + $likeCounter;


        return ['response' => $response, 'like_qty' => $likeCounter];

    }

    protected function captchaSkipperFriendsMembers($request, $response, $chunk, $chunkCounter, $itemCounter, $params = [])
    {
        $params['record_type'] === 'post' ? $captchaType = 'posts.autoLike' : $captchaType = 'photos.autoLike';
//        ddd(['likeCounter'  => $params['like_counter'], 'like_limit' => $params['like_limit']]);
        if($params['like_counter'] < $params['like_limit'])
        {
//            ddd('test');
            try {
                $response = array_merge($response, $this->apiConnection->send($request));
                $request = [];
                $params['like_counter']++;


                sleep(1);
            }
            catch (\Exception $e) {

                if ($e->getCode() == 14) {
                    $request = [];
                    $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response, [
                        'owner_id' => $e->request_params[0]['value'], 'captchaType' => $captchaType]);

                    foreach ($chunk as $item) {
                        if ($item['likes']['user_likes'] === 0) {
                            if ($captcha_answer !== '') {
                                $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                    'item_id' => $item['id'], 'type' => $params['record_type'],
                                    'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                                $captcha_answer = '';
                            } else {
                                $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                    'item_id' => $item['id'], 'type' => $params['record_type']]);
                            }
                        }
                    }

                    /**
                     *  $dirtyResponse - ['responseFromVK' => (array)$sth, 'qtyOfSuccessLikes' => (int)number]
                     */
                    $dirtyResponse = $this->captchaSkipperFriendsMembers($request, $response, $chunk, $chunkCounter, $itemCounter, $params);

                    $response = array_merge($response, $dirtyResponse['responseFromVK']);
                    $params['like_counter'] = $dirtyResponse['qtyOfSuccessLikes'];
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
                проставлены в количестве" . ($chunkCounter * 25 - 25 + $itemCounter));

                    return $response;
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
        }


        return ['responseFromVK' => $response, 'qtyOfSuccessLikes' => $params['like_counter']];
    }

    protected function captchaSkipper($request, $response, $chunk, $chunkCounter, $itemCounter, $params = [])
    {
        $params['record_type'] === 'post' ? $captchaType = 'posts.autoLike' : $captchaType = 'photos.autoLike';
            try {
                $response = array_merge($response, $this->apiConnection->send($request));
                $request = [];

                $itemCounter++;

                sleep(1);
            } catch (\Exception $e) {

                if ($e->getCode() == 14) {
                    $request = [];
                    $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response, [
                        'owner_id' => $e->request_params[0]['value'], 'captchaType' => $captchaType]);

                    foreach ($chunk as $item)
                    {
                        if ($item['likes']['user_likes'] === 0)
                        {
                            if ($captcha_answer !== '')
                            {
                                $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                    'item_id' => $item['id'], 'type' => $params['record_type'],
                                    'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                                $captcha_answer = '';
                            }
                            else
                            {
                                $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                    'item_id' => $item['id'], 'type' => $params['record_type']]);
                            }
                        }
                    }

                    $dirtyResponse = $this->captchaSkipper($request, $response, $chunk, $chunkCounter, $itemCounter, $params);

                    $itemCounter = $dirtyResponse['item_counter'];

                    $chunkCounter = $dirtyResponse['chunk_counter'];

                    $response = array_merge($response, $dirtyResponse['response']);
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
                проставлены в количестве" . ($chunkCounter * 25 - 25 + $itemCounter));

                    return ['response' => $response, 'item_counter' => $itemCounter, 'chunk_counter' => $chunkCounter];
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

        return ['response' => $response, 'item_counter' => $itemCounter, 'chunk_counter' => $chunkCounter];
    }

    protected function captchaSkipperSearchRecords($request, $response, $records, $itemCounter, $params = [])
    {
        $params['record_type'] === 'post' ? $captchaType = 'posts.autoLike' : $captchaType = 'photos.autoLike';
        try {
            $response = array_merge($response, $this->apiConnection->send($request));
            $request = [];

            $itemCounter++;

            sleep(1);
        } catch (\Exception $e) {
            if ($e->getCode() == 14) {
                $request = [];
                $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response, [
                    'owner_id' => $e->request_params[0]['value'], 'captchaType' => $captchaType]);

                foreach ($records as $key => $record) {
                    if($itemCounter >= $key)
                    {
                        if ($record['likes']['user_likes'] === 0) {
                            if ($captcha_answer !== '') {
                                $request = new Request('likes.add', ['owner_id' => $record['owner_id'],
                                    'item_id' => $record['id'], 'type' => $params['record_type'],
                                    'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                                $captcha_answer = '';
                            } else {
                                $request = new Request('likes.add', ['owner_id' => $record['owner_id'],
                                    'item_id' => $record['id'], 'type' => $params['record_type']]);
                            }

                            $dirtyResponse =  $this->captchaSkipperSearchRecords($request, $response, $records, $itemCounter, $params);

                            $response = array_merge($response,$dirtyResponse['response']);

                            $itemCounter = $dirtyResponse['item_counter'];
                        }
                    }
                }

            } else if ($e->getCode() == 9) {

                $flood = new Flood();
                $flood->flood_type = $params['flood_type'];
                $flood->owner_id = $params['owner_id'];
                $flood->user_id = auth()->user()->getAuthIdentifier();
                $flood->post_comments_position = null;
                $flood->post_comment_position = $itemCounter;
                $flood->save();

                return ['response' => $response, 'item_counter' => $itemCounter];
            } else if ($e->getCode() == 15)
            {
                null;
            }
            else if ($e->getCode() == 18){
                null;
            } else if ($e->getCode() == 30){
                null;
            } else {
                null;
            }
        }

        return ['response' => $response, 'item_counter' => $itemCounter];
    }

    protected function likePhotos($photo, $params = [], $fields = '')
    {
        set_time_limit(0);

        sleep(1);

        $request = [];
        $response = [];
        $captcha = $params['captcha'];

        $chunkReturnPosition = 0;
        $itemReturnPosition = 0;


        $chunkCounter = 0;
        $itemCounter = 0;

        if(isset($params['captcha']->post_comments_position))
        {
            $chunkReturnPosition = $params['captcha']->post_comments_position;
            $itemReturnPosition = $params['captcha']->post_comment_position;
        }

        foreach (array_chunk($photo, 25) as $chunk)
        {
            if($chunkReturnPosition >= $chunkCounter)
            {
                foreach ($chunk as $item)
                {
                    if($itemReturnPosition >= $itemCounter)
                    {
                        if ($item['likes']['user_likes'] === 0)
                        {
                            $request = new Request('likes.add', ['owner_id' => $item['owner_id'],
                                'item_id' => $item['id'], 'type' => 'photo']);

                            try
                            {

                                $dirtyResponse = $this->captchaSkipper($request, $response, $chunk, $chunkCounter, $itemCounter, ['record_type' => 'photo', 'flood_type' => 'photos.autolike_members', 'owner_id' => $params['owner_id']]);

                                $response = array_merge($response, $dirtyResponse['response']);

                                $itemCounter = $dirtyResponse['item_counter'];

                                $chunkCounter = $dirtyResponse['chunk_counter'];

                                sleep(1);

                            }
                            catch (\Exception $e)
                            {
                                if ($e->getCode() == 14) {
                                    $request = [];
                                    $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response,
                                        ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'photos.autoLike']);

                                    foreach ($chunk as $innerItem) {
                                        if ($innerItem['likes']['user_likes'] === 0) {
                                            if ($captcha_answer !== '') {
                                                $request = new Request('likes.add', ['owner_id' => $innerItem['owner_id'],
                                                    'item_id' => $innerItem['id'], 'type' => 'photo',
                                                    'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                                                $captcha_answer = '';
                                            } else {
                                                $request = new Request('likes.add', ['owner_id' => $innerItem['owner_id'],
                                                    'item_id' => $innerItem['id'], 'type' => 'photo']);
                                            }

                                        }

                                    }

                                    $dirtyResponse = $this->captchaSkipper($request, $response, $chunk,$chunkCounter, $itemCounter, ['record_type' => 'photo', 'flood_type' => 'photos.autolike_members', 'owner_id' => $params['owner_id']]);

                                    $itemCounter = $dirtyResponse['item_counter'];

                                    $chunkCounter = $dirtyResponse['chunk_counter'];

                                    $response = array_merge($response, $dirtyResponse['response']);


                                } else if ($e->getCode() == 9) {
                                    $flood = new Flood();
                                    $flood->flood_type = $params['flood_type'];
                                    $flood->owner_id = $params['owner_id'];
                                    $flood->user_id = auth()->user()->getAuthIdentifier();
                                    $flood->post_comments_position = $chunkCounter;
                                    $flood->post_comment_position = $itemCounter;
                                    $flood->save();

                                    session()->flash($params['flood_type'], 'Вы получили флуд-бан, продолжайте когда будете готовы');

                                    return $response;
                                }
                            }
                        }
                    }
                }
            }
//            }
            $chunkCounter++;
        }

        $likeQty = $chunkCounter * 25 - (25 - $itemCounter);



        return ['response' => $response, 'like_qty' => $likeQty, 'chunkCounter' => $chunkCounter, 'itemCounter' => $itemCounter];

    }

    protected function filterUsers($users, $attributes = [])
    {
        $filteredUsers = [];
        if (isset($attributes['minFollowers'])) {
            if (isset($attributes['hasPhoto'])) {
                foreach ($users as $user) {
                    if (isset($user['followers_count']) && isset($user['has_photo'])) {
                        if ($user['followers_count'] >= $attributes['minFollowers'] && $user['has_photo'] === 1) {
                            $filteredUsers[] = $user;
                        }
                    }
                }
            } else {
                foreach ($users as $user) {
                    if (isset($user['followers_count']) && isset($user['has_photo'])) {
                        if ($user['followers_count'] >= $attributes['minFollowers']) {
                            $filteredUsers[] = $user;
                        }
                    }
                }
            }
        }

        return $filteredUsers;
    }

    protected function likeRecords($records, $params = [], $fields = '')
    {
        set_time_limit(0);

        $oldDate = time();

        $request = [];
        $response = [];
        $successCounter = 0;
        $captcha_answer = '';


        $itemReturnPosition = 0;


        $itemCounter = 0;


        if(isset($params['captcha']->post_comment_position))
        {
            $itemReturnPosition = $params['captcha']->post_comment_position;
        }


        foreach ($records as $record)
        {
            if($itemReturnPosition >= $itemCounter)
            {
                if ($record['likes']['user_likes'] === 0)
                {
                    $request = new Request('likes.add', ['owner_id' => $record['owner_id'], 'item_id' => $record['id'],
                        'type' => $record['post_type']]);

                    sleep(1);

                    try
                    {
                        $responseFromVk = $this->apiConnection->send($request);

                        $response = array_merge($response, $responseFromVk);

                        sleep(1);

                        $itemCounter++;
                        $itemReturnPosition++;

                        sleep(1);

                    }
                    catch (\Exception $e)
                    {
                        if ($e->getCode() == 14)
                        {
                            $request = [];
                            $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response,
                                ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'posts.autoLike']);

                            foreach ($records as $key => $innerRecord)
                            {
                                if($itemCounter >= $key)
                                {
                                    if($innerRecord['likes']['user_likes'] === 0)
                                    {
                                        if ($captcha_answer !== '')
                                        {
                                            $request = new Request('likes.add', ['owner_id' => $innerRecord['owner_id'],
                                                'item_id' => $innerRecord['id'], 'type' => $innerRecord['post_type'],
                                                'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                                            $captcha_answer = '';
                                        }
                                        else
                                        {
                                            $request = new Request('likes.add', ['owner_id' => $innerRecord['owner_id'],
                                                'item_id' => $innerRecord['id'], 'type' => 'post']);
                                        }
                                    }
                                }
                            }

                            $dirtyResponse = $this->captchaSkipperSearchRecords($request, $response, $records,
                                $itemCounter, ['record_type' => 'post', 'flood_type' => $params['flood_type'], 'owner_id' => $params['owner_id']]);

                            $response = array_merge($response, $dirtyResponse['response']);

                            $itemCounter = $dirtyResponse['item_counter'];

                            $captcha_answer = '';
                        } else if ($e->getCode() == 9) {

                            $flood = new Flood();
                            $flood->flood_type = $params['flood_type'];
                            $flood->owner_id = $params['owner_id'];
                            $flood->user_id = auth()->user()->getAuthIdentifier();
                            $flood->post_comments_position = null;
                            $flood->post_comment_position = $itemCounter;
                            $flood->save();

                            session()->flash('search_records_flood.auto_likes', 'Вы поймали флуд-бан и лайки на записи были проставлены в количестве' . $itemCounter); //сделать корректный вывод количества

                            return ['response' => $response, 'like_qty' => $itemCounter];
                        } else {
                            continue;
                        }
                    }
                }
            }
        }

//        session()->flash('successTagRec', $successCounter . ' лайков было проставлено по тегам');

//        return redirect('dashboard');

        return ['response' => $response, 'like_qty' => $itemCounter];
    }

    public function likeAllMembersByCriteries(BaseRequest $request)
    {
        set_time_limit(0);

        $group_url = $request->input('group_id');

        $group_id = $this->getGroupID($group_url);

        $record_count = $request->input('record_count');
//
//        if($record_count > 3 || $record_count < 1)
//        {
//            session()->flash('auto_like_criteries_success', 'Вы ввели неправильное к-во записей, разрешенное от 1 до 3');
//
//            return redirect('dashboard');
//        }

        $flood = auth()->user()->flood()
            ->where('flood_type', '=', 'posts.autolike_members')
            ->where('owner_id', '=', $group_id)
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($flood) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'posts.autolike_members')
                ->where('owner_id', '=', $group_id)
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }

        $floodPhoto = auth()->user()->flood()
            ->where('flood_type', '=', 'photos.autolike_members')
            ->where('owner_id', '=', $group_id)
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($floodPhoto) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'photos.autolike_members')
                ->where('owner_id', '=', $group_id)
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }


        $request->input('auto_like_min_followers') != '' ? $minFollowers = $request->input('auto_like_min_followers') :
            $minFollowers = 0;

        $request->input('hasPhoto') == 1 ? $hasPhoto = 1 :
            $hasPhoto = 0;

        $request->input('captcha_key') ? $captcha['key'] = $request->input('captcha_key') :
            $captcha['key'] = '';
        $request->input('captcha_sid') ? $captcha['sid'] = $request->input('captcha_sid') :
            $captcha['sid'] = '';
        $request->input('captcha_chunk_pause') ? $captcha['captcha_chunk_pause'] = $request->input('captcha_chunk_pause') :
            $captcha['captcha_chunk_pause'] = '';
        $request->input('captcha_chunk_item_pause') ? $captcha['captcha_chunk_item_pause'] = $request->input('captcha_chunk_item_pause') :
            $captcha['captcha_chunk_item_pause'] = '';

        $request->input('captcha_key_posts') ? $captcha['key_posts'] = $request->input('captcha_key_posts') :
            $captcha['key_posts'] = '';
        $request->input('captcha_sid') ? $captcha['sid_posts'] = $request->input('captcha_sid_posts') :
            $captcha['sid_posts'] = '';
        $request->input('captcha_chunk_pause') ? $captcha['captcha_chunk_pause_posts'] = $request->input('captcha_chunk_pause_posts') :
            $captcha['captcha_chunk_pause_posts'] = '';
        $request->input('captcha_chunk_item_pause') ? $captcha['captcha_chunk_item_pause_posts'] = $request->input('captcha_chunk_item_pause_posts') :
            $captcha['captcha_chunk_item_pause_posts'] = '';

        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }


        try
        {
            $groupMembers = $this->getAllMembers($group_id, $this->apiConnection);
        }
        catch (\Exception $e)
        {
            session()->flash('invalid_members_by_criteries', 'Произошла ошибка при получении подписчиков, проверьте введенный ID группы');
            return redirect('dashboard');
        }


        $usersWithAttributes = $this->getUsersWithAttributes($groupMembers, [], 'has_photo, followers_count');
        $filteredUsers = $this->filterUsers($usersWithAttributes, ['minFollowers' => $minFollowers, 'hasPhoto' => $hasPhoto]);

//        print_r($filteredUsers);
        $photo = $this->getAllPhotos($filteredUsers, $record_count);
        $posts = $this->getAllWallPosts($filteredUsers, $record_count);

        if ($flood) {
            $likedPosts = $this->likePosts($posts, ['captcha' => $flood, 'flood_type' => 'posts.autolike_members', 'owner_id' => $group_id, 'like_limit' => null])['likesQty'];
            $likedPhotos = $this->likePhotos($photo, ['captcha' => $floodPhoto, 'flood_type' => 'photos.autolike_members', 'owner_id' => $group_id]);
        } else {
            $likedPosts = $this->likePosts($posts, ['captcha' => $captcha, 'flood_type' => 'posts.autolike_members', 'owner_id' => $group_id, 'like_limit' => null])['likesQty'];
            $likedPhotos = $this->likePhotos($photo, ['captcha' => $captcha, 'flood_type' => 'photos.autolike_members', 'owner_id' => $group_id]);
        }

        if(!empty($likedPhotos['like_qty']))
        {
            Statistic::update_likes(['subscribers_by_criteries_photos_likes' => $likedPhotos['like_qty'], 'all_likes' => $likedPhotos['like_qty']]);
        }

        if(!empty($likedPosts['like_qty']))
        {
            Statistic::update_likes(['subscribers_by_criteries_posts_likes' => $likedPosts['like_qty'], 'all_likes' => $likedPosts['like_qty']]);
        }

        session()->flash('auto_like_criteries_success', 'Лайки были успешно проставлены');

        return redirect('dashboard');
    }

    public function likeAllFollowersFriends(BaseRequest $request)
    {
        set_time_limit(0);

        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }

        $recordCount = $request->input('likeCount', 20);
        $like_limit = $request->input('likeLimit', 1);

        $friends = $this->getAllFriends([], $this->apiConnection);
        $followers = $this->getAllFollowers([], $this->apiConnection);

        $posts = array_merge($this->getAllWallPostsById($friends, $recordCount), $this->getAllWallPostsById($followers, $recordCount));

        $flood = auth()->user()->flood()
            ->where('flood_type', '=', 'posts.autolike_followers')
            ->where('owner_id', '=', 0)
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($flood) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'posts.autolike_followers')
                ->where('owner_id', '=', 0)
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }

//        ddd($posts);

        $likesResult = $this->likePosts($posts, ['captcha' => $flood, 'flood_type' => 'posts.autolike_followers', 'owner_id' => 0, 'like_limit' => $like_limit]);

        if(!empty($likesResult['like_qty']))
        {
            Statistic::update_likes(['friends_followers_likes' => $likesResult['like_qty'], 'all_likes' => $likesResult['like_qty']]);
        }

        session()->flash('posts.autolike_followers_success', 'Лайки вашим друзьям и подписчикам были поставлены');

        return redirect('dashboard');
    }

    public function likeAllSearchRecordsByTags(BaseRequest $request)
    {
        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }

        $request->input('tags') ? $params['tags'] = $this->transformTextToTags($request->input('tags')) : $params['tags'] = '';

        $params['fields'] = '';


        $flood = auth()->user()->flood()
            ->where('flood_type', '=', 'posts.autolike_followers')
            ->where('owner_id', '=', 0)
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($flood) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'posts.autolike_followers')
                ->where('owner_id', '=', 0)
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }


        $params['start_time'] = strtotime($request->input('searchStart', ''));

        $params['end_time'] = strtotime($request->input('searchEnd', ''));

        if($params['end_time'] === $params['start_time'])
        {
            $params['end_time'] += 86400;
        }

        $request->input('requests') ? $params['count'] = $request->input('requests') :
            $params['count'] = '200';


        $records = $this->getAllSearchRecords($params, $this->apiConnection);

        $likedRecords = $this->likeRecords($records, ['captcha' => $flood, 'flood_type' => 'search_records.flood', 'owner_id' => $params['tags']]);

        if(!empty($likedRecords['like_qty']))
        {
            Statistic::update_likes(['tags_likes' => $likedRecords['like_qty'], 'all_likes' => $likedRecords['like_qty']]);
        }

        session()->flash('search_records.auto_likes', 'Лайки на записи были проставлены');

        return redirect('dashboard');

    }

    public function likeAllConcurrents(BaseRequest $request)
    {
        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }

        sleep(1);
        $currentUserId = $this->apiConnection->send(new \ATehnix\VkClient\Requests\Request('account.getProfileInfo', []))['response']['id'];
        sleep(1);
        $request->input('url') ? $url = $request->input('url') : $url = '';

        $request->input('postscount') ? $count = $request->input('postscount') : $count = 100;

        $params = $this->getPostParams($url);

        if(isset($params['not_right']))
        {
            session()->flash('not_valid_like_concurrents', 'Вы ввели url записи в неправильном формате, попробуйте снова');
            return redirect('dashboard');
        }

        $flood = auth()->user()->flood()
            ->where('flood_type', '=', 'posts.autolike_concurrents')
            ->where('owner_id', '=', $url)
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($flood) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'posts.autolike_concurrents')
                ->where('owner_id', '=', $url)
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }

        $floodPhoto = auth()->user()->flood()
            ->where('flood_type', '=', 'photos.autolike_concurrents')
            ->where('owner_id', '=', $url)
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        if ($floodPhoto) {
            auth()->user()->flood()
                ->where('flood_type', '=', 'photos.autolike_concurrents')
                ->where('owner_id', '=', $url)
                ->orderBy('created_at', 'desc')
                ->latest()
                ->first()
                ->delete();
        }

        $userLikes = $this->getAllValues('likes.getList', ['type' => 'post', 'owner_id' => $params['owner_id'], //Потенциально - проблема с получением списка людей лайкнувших фотографию
            'item_id' => $params['item_id'], 'count' => 1000, 'skip_own' => 1]);

        $userComments = $this->getAllValues('wall.getComments', ['owner_id' => $params['owner_id'],
            'post_id' => $params['item_id'], 'count' => 100]);

        $userSortedComments = $this->getAllSortedComments($userComments, ['from_id' => $currentUserId]);

        $userIds = array_merge($userSortedComments, array_diff($userLikes, $userSortedComments));

        $posts = $this->getAllWallPostsById($userIds, $count);

        $photos = $this->getAllPhotosById($userIds, $count);

        $likedPosts = $this->likePosts($posts, ['captcha' => $flood, 'flood_type' => 'posts.autolike_concurrents', 'owner_id' => $url, 'like_limit' => null]);

        $likedPhotos = $this->likePhotos($photos, ['captcha' => $floodPhoto, 'flood_type' => 'photos.autolike_concurrents', 'owner_id' => $url]);

        if(!empty($likedPhotos['like_qty']))
        {
            Statistic::update_likes(['concurrent_photo_likes' => $likedPhotos['like_qty'], 'all_likes' => $likedPhotos['like_qty']]);
        }

        if(!empty($likedPosts['like_qty']))
        {
            Statistic::update_likes(['concurrent_post_likes' => $likedPosts['like_qty'], 'all_likes' => $likedPosts['like_qty']]);
        }

        session()->flash('autoLikesSuccess', 'Лайки по конкурентам были проставлены');

        return redirect('dashboard');

    }
}
