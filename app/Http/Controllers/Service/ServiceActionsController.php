<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;

use App\Models\Captcha;
use App\Models\Flood;
use App\Models\Statistic;
use App\Models\Limits;
use ATehnix\VkClient\Requests\Request;
use function PHPUnit\Framework\throwException;

class ServiceActionsController extends Controller
{
    protected function stateChecker($params)
    {
        $captchaDBPost = auth()->user()->captcha()
            ->where('captcha_type', '=', 'posts.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $captchaDBPhoto = auth()->user()->captcha()
            ->where('captcha_type', '=', 'photos.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $captchaDBAutoLikesPhoto = auth()->user()->captcha()
            ->where('captcha_type', '=', 'photos.autoLike')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $captchaDBAutoLikesPosts = auth()->user()->captcha()
            ->where('captcha_type', '=', 'posts.autoLike')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $unhandled = auth()->user()->captcha()
            ->where('captcha_type', '=', 'unhandled.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $floodPost = auth()->user()->flood()
            ->where('flood_type', '=', 'posts.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $floodPhoto = auth()->user()->flood()
            ->where('flood_type', '=', 'photos.like')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $floodAutoLikes = auth()->user()->flood()
            ->where('flood_type', '=', 'photos.autoLike')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->first();

        $captcha = [
            'captcha_post' => null,
            'captcha_photo' => null,
            'captcha_autolikes' => null,
            'flood_autolikes' => null,
        ];


        if($captchaDBPost)
        {

            session()->flash('com_post_like_captcha', 'Во время предыдущей попытки вы поймали капчу, пройдите проверку и работа возобновится с последнего проставленного лайка');
            $captcha['captcha_post'] = $captchaDBPost;
//            return view('dashboard', ['captcha_post' => $captchaDBPost]);

        }
        else if($captchaDBPhoto)
        {
            session()->flash('com_photo_like_captcha', 'Во время предыдущей попытки вы поймали капчу, пройдите проверку и работа возобновится с последнего проставленного лайка');
            $captcha['captcha_photo'] = $captchaDBPhoto;
            //return view('dashboard', ['captcha_photo' => $captchaDBPhoto]);
        }
        else if($unhandled)
        {
            session()->flash('com_photo_like_unhandled', 'Вконтакте разорвал предыдущее подключение, лайки были проставлены не всем комментариям, попробуйте снова и запись начнется с последних');
//            return view('dashboard', ['captcha' => []]);
        }

        if($floodPost)
        {
            session()->flash('com_post_like_flood', 'Во время предыдущей попытки вы поймали флуд бан, если вы считаете что время пришло, то продолжайте');
//            return view('dashboard', ['captcha' => []]);
        }
        else if($floodPhoto)
        {
            session()->flash('com_photo_like', 'Во время предыдущей попытки вы поймали флуд бан, если вы считаете что время пришло, то продолжайте');
//            return view('dashboard', ['captcha' => []]);
        }

        if($captchaDBAutoLikesPhoto)
        {
            session()->flash('auto_like_captcha', 'При автопроставлении лайков вы получили капчу на выставление лайков фотографиям');
            $captcha['captcha_autolikes'] = $captchaDBAutoLikesPhoto;
            //return view('dashboard', ['captcha' => $captchaDBAutoLikesPhoto]);
        }

        if($captchaDBAutoLikesPosts)
        {
            session()->flash('auto_like_captcha_posts', 'При автопроставлении лайков вы получили капчу на выставление лайков постам');
            $captcha['captcha_autolikes_posts'] = $captchaDBAutoLikesPosts;
            //return view('dashboard', ['captcha' => $captchaDBAutoLikesPhoto]);
        }

        if($floodAutoLikes)
        {
            session()->flash('auto_like_flood', 'Во время предыдущей попытки вы поймали флуд бан, если вы считаете что время пришло, то продолжайте');
            $captcha['flood_autolikes'] = $floodAutoLikes;
//            return view('dashboard', ['captcha' => $floodAutoLikes]);
        }

        $statisticRecord = Statistic::where(['user_id' => auth()->user()->id, ['created_at', '>', date('Y-m-d H:i:s', time() - 86400)]])->first();

        //Перенести на подгрузку с модели
        $tags = [
            'Бизнес',
            'Life',
            'СтильЖизни',
            'МожноВсё',
            'BixUp',
            'IpostX'
        ];

        $comments = [
            'Отличный пост, спасибо👍',
            'Этот пост заслуживает аплодисментов👏👏👏',
            'Вау, ты прекрасно выглядишь👏💐',
            'Именно поэтому я подписан на тебя😉',
            'Улыбка на миллион долларов😃',
            'Это фото огонь🔥🔥🔥',
            'Красота фотографии не имеет границ💔',
            'Тебе можно позавидовать💭💭💭',
            'Огонь🔥',
            'Неподражаемо',
            'Ты давно в этой теме?',
            'Wooow!🌟',
            'Круто!👏🏻👍🏻',
        ];

        $avatarUrl = $this->get_avatar(auth()->user()->name);

        if(!empty($params['apiConnection']))
        {
            if(!empty($vkPhoto = $this->getVkPhoto($params['apiConnection'])['response']))
            {
                if(!empty($vkPhoto[0]))
                {
                    $vkPhotoUrl = $vkPhoto[0]['photo_200_orig'];
                }
                else
                {
                    $vkPhotoUrl = 'none';
                }
            }
            else
            {
                $vkPhotoUrl = 'none';
            }
        }
        else
        {
            $vkPhotoUrl = 'none';
        }



        return view('dashboard', array_merge(['data' => $statisticRecord],
            compact('captcha', 'tags', 'comments', 'avatarUrl', 'vkPhotoUrl')));
    }

    protected function getVkPhoto($apiConnection)
    {
        $request = new Request('users.get', ['fields' => 'photo_200_orig']);

        try {
            $photo = $apiConnection->send($request);

            return $photo;
        }
        catch(\Exception $e)
        {
            return ['response' => false];
        }

    }

    protected function get_avatar($text)
    {
        return "https://ui-avatars.com/api/?name=".urldecode($text)."&background=5578eb&color=fff&font-size=0.5&rounded=true";
    }

    protected function validateAccessToken($token, $apiConnection)
    {
        try
        {
            $request = new Request('account.getInfo', ['access_token' => $token,'fields' => 'own_posts_default', 'v' => '5.130']);

            $validateResult = $apiConnection->send($request);

            return true;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    protected function getPostParams($url)
    {
        if(strpos($url, 'photo'))
        {
            $startPos = strpos($url, 'photo') + 5;
        }
        else
        {
            $startPos = strpos($url, 'wall') + 4;
        }

        $paramsBlock = mb_substr($url, $startPos, strlen($url));

        $owner_id_endpos = strpos($paramsBlock, '_');

        $owner_id = mb_substr($paramsBlock, 0, $owner_id_endpos);

        $item_id_block = mb_substr($paramsBlock, $owner_id_endpos, strlen($paramsBlock));

        preg_match("/([^0-9])/", $item_id_block,$firstNotNumericPos, PREG_OFFSET_CAPTURE);

        preg_match("/([^0-9])/", $item_id_block,$secondNotNumericPos, PREG_OFFSET_CAPTURE, 1);

        !empty($secondNotNumericPos) ? $endPosition = $secondNotNumericPos[0][1] - 1 : $endPosition = strlen($item_id_block);

        $item_id = mb_substr($item_id_block, $firstNotNumericPos[0][1] + 1, $endPosition);

        $owner_id = (integer)$owner_id;

        $item_id = (integer)$item_id;

            if(gettype($owner_id) === 'integer' && gettype($item_id) === 'integer')
            {
                return ['owner_id' => $owner_id, 'item_id' => $item_id];
            }
            else
            {
                return ['not_right' => true];
            }
    }

    protected function getAllSortedComments($userComments, $params)
    {
        $ids = [];
        foreach ($userComments as $key => $comment) {
            if ($comment['from_id'] === $params['from_id']) {
                unset($userComments[$key]);
            } else {
                $ids[] = $comment['from_id'];
            }
        }

        return $ids;
    }

    protected function getAllValues($method, $params)
    {
        sleep(1);
        $values = [];
        $offset = 0;

        do {
            try
            {
                $request = new Request($method, $params);
                $response = $this->apiConnection->send($request);
                sleep(1);

                $items = $response['response']['items'];
                $values = array_merge($values, $items);

                $offset = $offset + count($items);
            }
            catch(\Exception $e)
            {
                return $values;
            }

        } while ($response['response']['count'] > $offset);

        return $values;
    }

    protected function getValues($method, $params, $apiConnection)
    {

        $request = new Request($method, $params);
//        ddd($request);
        $response = $apiConnection->send($request);
        sleep(1);

        return $response['response']['items'];
    }

    protected function sendGetToRuCaptcha($key, $id)
    {
        $query = http_build_query([
            'key' => $key,
            'action' => 'get',
            'id' => $id,
            'json' => 1,
        ]);
        $url = "http://rucaptcha.com/res.php?$query";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER , [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $json = json_decode($response, true);

        return $json;
    }

    protected function sendPostToRuCaptcha($key, $body64Img)
    {

        $query = http_build_query([
            'key' => $key,
            'method' => 'base64',
            'regsense' => 1,
            'json' => 1,
        ]);

        $body = http_build_query([
            'body' => $body64Img,
        ]);
        $url = "http://rucaptcha.com/in.php?$query";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_HTTPHEADER , [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $json = json_decode($response, true);

        return $json;
    }

    protected function exceptionHandlerAutoLikes($e, $response, $params = [])
    {
        set_time_limit(0);

        $owner_id = $params['owner_id'];
        $counterCommentsPlace = 'test';
        $counterComment = 'test';
        $captchaType = $params['captchaType'];
        $ruCaptchaKey = '3d7ae9859d9210d36d5a52b535fb8bd8';
        $ruCaptchaResponse = ['id'];
        $captchaAnswer = '';

        switch ($e->getCode())
        {
            case 14 :
            {
                $url = $e->captcha_img;
                $image = file_get_contents($url);

                if ($image !== false){
                    $finalImg = 'data:image/jpg;base64,'.base64_encode($image);
                    if($ruCaptchaResponse = $this->sendPostToRuCaptcha($ruCaptchaKey, $finalImg))
                    {
                        if($ruCaptchaResponse['status'] === 1)
                        {
                            $imgId = $ruCaptchaResponse['request'];

                            do
                            {
                                sleep(5);
                                $captchaAnswer = $this->sendGetToRuCaptcha($ruCaptchaKey, $imgId);
                                if($captchaAnswer['status'] === 1)
                                {
                                    return $captchaAnswer['request'];
                                }
                            }
                            while($captchaAnswer['status'] === 0);
                        }
                    }
                    else
                    {
                        return 'Что-то пошло не так при передаче капчи на сервер дешифровки, вернитесь назад и попробуйте снова';
                    }
                }
            }
            case 9 :
            {
                $this->saveFloodInDB($owner_id, $captchaType, $counterCommentsPlace, $counterComment);

                return $response;
            }
            default :
            {
                $this->saveCaptchaInDB('null', 'null', 'unhandled.like', [
                    'owner_id'               => $owner_id,
                    'post_comments_position' => $counterCommentsPlace,
                    'post_comment_position'  => $counterComment
                ]);

                return $response;
            }
        }
    }

    protected function friendRequestCaptchaSkipper($request, $response, $usersID, $limit_counter, $success_requests, $messageText, $connection)
    {
        $captchaType = 'new_friends.requests';

        $limit_counterInner = $limit_counter;

        try {
            $requestFromQuery = $connection->send($request);
            $response = array_merge($response, $requestFromQuery);

            $limit_counterInner++;

            $success_requests++;

            $request = [];
        }
        catch (\Exception $e)
        {
            switch ($e->getCode())
            {
                case 14 : {
                    $request = [];
                    $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response,
                        ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'new_friends.requests']);

                    foreach ($usersID as $key => $userID)
                    {
                        if($limit_counterInner !== 50)
                        {
                            if ($key === $success_requests)
                            {
                                if ($captcha_answer !== '')
                                {
                                    $request = new Request('friends.add', ['user_id' => $userID,'text' => $messageText,
                                        'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);
                                }
                                else
                                {
                                    $request = new Request('friends.add', ['user_id' => $userID,'text' => $messageText]);
                                }

                                $friendRequestResponse = $this->friendRequestCaptchaSkipper(
                                    $request, $response, $usersID, $limit_counter, $success_requests, $messageText, $connection);

                                $response = array_merge($response, $friendRequestResponse['response']);

                                $success_requests = $friendRequestResponse['success_requests'];

                                $limit_counterInner = $friendRequestResponse['limit_counter'];
                            }
                        }
                    }

                    break;
                }
                default : {
                    sleep(30);
                };
            }
        }

        return ['response' => $response, 'success_requests' => $success_requests, 'limit_counter' => $limit_counterInner];
    }

    protected function exceptionHandlerAutoComments($e, $response, $params = [])
    {
        set_time_limit(0);

        $owner_id = $params['owner_id'];
        $counterCommentsPlace = 'test';
        $counterComment = 'test';
        $captchaType = $params['captchaType'];
        $ruCaptchaKey = '3d7ae9859d9210d36d5a52b535fb8bd8';
        $ruCaptchaResponse = ['id'];
        $captchaAnswer = '';

        switch ($e->getCode())
        {
            case 14 :
            {
                $url = $e->captcha_img;
                $image = file_get_contents($url);

                if ($image !== false){
                    $finalImg = 'data:image/jpg;base64,'.base64_encode($image);
                    if($ruCaptchaResponse = $this->sendPostToRuCaptcha($ruCaptchaKey, $finalImg))
                    {
                        if($ruCaptchaResponse['status'] === 1)
                        {
                            $imgId = $ruCaptchaResponse['request'];

                            do
                            {
                                sleep(5);
                                $captchaAnswer = $this->sendGetToRuCaptcha($ruCaptchaKey, $imgId);
                                if($captchaAnswer['status'] === 1)
                                {
                                    return $captchaAnswer['request'];
                                }
                            }
                            while($captchaAnswer['status'] === 0);
                        }
                    }
                    else
                    {
                        'Что-то пошло не так при передаче капчи на сервер дешифровки, вернитесь назад и попробуйте снова';
                    }
                }
            }
            case 9 :
            {
                $this->saveFloodInDB($owner_id, $captchaType, $counterCommentsPlace, $counterComment);

                return $response;
            }
            default :
            {
                $this->saveCaptchaInDB('null', 'null', 'unhandled.like', [
                    'owner_id'               => $owner_id,
                    'post_comments_position' => $counterCommentsPlace,
                    'post_comment_position'  => $counterComment
                ]);

                return $response;
            }
        }
    }

    protected function exceptionHandler($e, $response, $params = [])
    {
        $owner_id = $params['owner_id'];
        $counterCommentsPlace = $params['counterCommentsPlace'];
        $counterComment = $params['counterComment'];
        $captchaType = $params['captchaType'];
        $ruCaptchaKey = '3d7ae9859d9210d36d5a52b535fb8bd8';
        $ruCaptchaResponse = ['id'];
        $captchaAnswer = '';

        switch ($e->getCode())
        {
            case 14 :
            {
                $this->saveCaptchaInDB($e->captcha_img, $e->captcha_sid, $captchaType, [
                    'owner_id'               => $owner_id,
                    'post_comments_position' => $counterCommentsPlace,
                    'post_comment_position'  => $counterComment
                ]);

                return $response;
            }
            case 9 :
            {
                $this->saveFloodInDB($owner_id, $captchaType, $counterCommentsPlace, $counterComment);

                return $response;
            }
            default :
            {
                $this->saveCaptchaInDB('null', 'null', 'unhandled.like', [
                    'owner_id'               => $owner_id,
                    'post_comments_position' => $counterCommentsPlace,
                    'post_comment_position'  => $counterComment
                ]);

                return $response;
            }
        }

    }

    protected function getAllCommentsFromRecords($records, $params, $apiConnection)
    {
        sleep(1);
        $comments = [];
        $offset = 0;

        foreach ($records as $record)
        {
            if($record['comments']['can_post'] === 1)
            {
//                do
//                {
                    $request = new Request('wall.getComments', ['owner_id' => $record['owner_id'],'post_id' => $record['id'],
                        'count' => 100, 'filters' => $record['post_type'], 'offset' => $offset]);

                    $response = $apiConnection->send($request);

                    sleep(1);

                    $comments[$record['owner_id']][$record['id']] = ['comments' => []];

                    $comments
                    [
                        $record['owner_id']
                    ]
                    [
                        $record['id']
                    ]
                    ['comments'] = array_merge($comments[$record['owner_id']][$record['id']]['comments'], $response['response']['items']);

                    $offset = count($comments[$record['owner_id']][$record['id']]['comments']);

//                }
//                while($response['response']['count'] > $offset);
                $offset = 0;
            }
        }

        return $comments;
    }

    protected function getAllWallPostsById($users, $recordCount)
    {
        $response = [
            'response' => [
                'count' => 1
            ]
        ];

        sleep(1);

        $offset = 0;
        $posts = [];
        foreach ($users as $userId)
        {

                $request = new Request('wall.get', ['owner_id' => $userId, 'count' => $recordCount, 'offset' => $offset]);
                try
                {
                    sleep(1);
                    $response = $this->apiConnection->send($request);

                    $items = $response['response']['items'];
                    $posts = array_merge($posts, $items);

                    $offset = $offset+count($items);
                }
                catch(\Exception $e)
                {
                    if($e->getCode() == 30 || $e->getCode() == 18 || $e->getCode() == 15)
                    {
                        null;
                    }
                    else
                    {
//                        ddd($e);
                        continue;
                    }
                }


            $offset = 0;
        }

        return $posts;
    }

    protected function getAllFriends($fields, $connection, $count = 5000)
    {
        $users = [];
        $offset = 0;

        do
        {
            sleep(1);

            $request = new Request('friends.get', ['fields' => $fields, 'offset' => $offset, 'count' => $count]);

            $response = $connection->send($request);

            $users = array_merge($users, $response['response']['items']);

            $offset = count($users);
        }
        while($response['response']['count'] > $offset);

        return $users;
    }

    protected function getAllFollowers($fields, $connection)
    {
        $users = [];
        $offset = 0;

        do
        {
            $request = new Request('users.getFollowers', ['fields' => $fields, 'count' => 1000, 'offset' => $offset]);

            $response = $connection->send($request);

            $followers = array_merge($users, $response['response']['items']);

            $offset = count($followers);

            sleep(1);
        }
        while($response['response']['count'] > $offset);

        return $followers;
    }

    public function saveFloodInDB($owner_id, $floodType, $counterCommentsPlace, $counterComment)
    {
        $captcha_record = new Flood();

        $captcha_record->user_id = \auth()->user()->getAuthIdentifier();
        $captcha_record->owner_id = $owner_id;
        $captcha_record->flood_type = $floodType;
        $captcha_record->post_comments_position = $counterCommentsPlace;
        $captcha_record->post_comment_position = $counterComment;

        $captcha_record->save();
    }

    public function saveCaptchaInDB($captcha_img, $captcha_sid, $captchaType, $returnParams)
    {
        $captcha_record = new Captcha();

        $captcha_record->user_id = \auth()->user()->getAuthIdentifier();
        $captcha_record->owner_id = $returnParams['owner_id'];
        $captcha_record->captcha_type = $captchaType;
        $captcha_record->captcha_sid = $captcha_sid;
        $captcha_record->captcha_img = $captcha_img;
        $captcha_record->post_comments_position = $returnParams['post_comments_position'];
        $captcha_record->post_comment_position = $returnParams['post_comment_position'];

        $captcha_record->save();
    }

    public function transformTextToTags($text)
    {
        $text = htmlspecialchars($text);

        $tagItems = explode(',', $text);

        foreach ($tagItems as $key => $item) {
            $tagItems[$key] = '#' . $item;
        }

        $tags = implode(' ', $tagItems);

        return $tags;
    }

    protected function getAllSearchRecords($params, $connection)
    {
        $request = new Request('newsfeed.search', ['q' => $params['tags'], 'count' => $params['count'], 'start_time' => $params['start_time'],
            'end_time' => $params['end_time'],'extended' => 1, 'fields' => $params['fields']]);

        $response = $connection->send($request);
        sleep(1);

        if(isset($params['profiles']))
        {
            return $response['response']['profiles'];
        }
        else
        {
            return $response['response']['items'];
        }
    }

    protected function getUsersIDfromRecords($records)
    {
        $userIDs = [];
        foreach ($records as $record)
        {
            if
            (
                !isset($record['deactivated'])                                          &&
                ($record['friend_status'] === 0 || $record['friend_status'] === 2)      &&
                ($record['blacklisted'] === 0   || $record['blacklisted_by_me'] === 0)
            )
                {
                    $userIDs[] = $record['id'];
                }
            else
            {
                continue;
            }

        }

        return $userIDs;
    }

    protected function sendFriendRequests($params, $connection)
    {
        $usersID = $params['users_id'];
        $messageText = $params['message_text'];
        $limit_counter = $params['limit_counter'];

//        ddd($limit_counter);

        $response = [];

        $success_requests = 0;

        foreach ($usersID as $key => $userID)
        {
            if($limit_counter !== 50)
            {
                if($key === $success_requests)
                {
                    $request = new Request('friends.add', ['user_id' => $userID,'text' => $messageText]);

                    try
                    {
                        sleep(1);
                        $response = $connection->send($request);
                        $success_requests++;
                        $limit_counter++;
                    }
                    catch (\Exception $e)
                    {
                        if($e->getCode() === 14)
                        {
//                            ddd($e);
                            $request = [];

                            $captcha_answer = $this->exceptionHandlerAutoLikes($e, $response,
                                ['owner_id' => $e->request_params[2]['value'], 'captchaType' => 'new_friends.requests']);

                            foreach ($usersID as $insideKey => $insideUserID)
                            {
                                if($limit_counter !== 50)
                                {
//                                    if ($insideKey === $success_requests)
//                                    {
                                        if ($captcha_answer !== '')
                                        {
                                            $request = new Request('friends.add', ['user_id' => $insideUserID,'text' => $messageText,
                                                'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);
                                        }
                                        else
                                        {
                                            $request = new Request('friends.add', ['user_id' => $insideUserID,'text' => $messageText]);
                                        }

                                        $dirtyResponse = $this->friendRequestCaptchaSkipper($request, $response, $usersID, $limit_counter, $success_requests, $messageText, $connection);

                                        $response = array_merge($response, $dirtyResponse['response']);
                                        $success_requests = $dirtyResponse['success_requests'];

                                        $limit_counter = $dirtyResponse['limit_counter'];
//                                    }

//                                    ddd(['insideKey' => $insideKey, 'success_requests' => $success_requests]);
                                }
                            }
                        }
                        else
                        {
                            sleep(30);
                        }
                    }
                }

            }
            else
            {
                break;
            }
        }

        return ['response' => $response, 'limit_counter' => $limit_counter];
    }

    protected function changeFriendRequestLimitInDB($sended_friends_requests_qty)
    {
        try
        {
            $previous_record = Limits::where(['user_id' => auth()->user()->getAuthIdentifier(),
                'type' => 'friend_request_limit', ['created_at', '>', date('Y-m-d H:i:s', time() - 86400)]])->latest()->first();

            if(!empty($previous_record))
            {
                $previous_record->delete();
            }

            $limit_record = new Limits();

            $limit_record->type = 'friend_request_limit';
            $limit_record->qty = $sended_friends_requests_qty;
            $limit_record->user_id = auth()->user()->getAuthIdentifier();

            $limit_record->save();

            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    protected function getGroupID($url)
    {
        $owner_id_startpos = strpos($url, 'vk.com/') + 7;
        $owner_id_endpos = strpos($url, '_');

        $string_id = preg_replace("/https?:\/\/vk\.com\/id/", '', $url);

        if($string_id === $url)
        {
            $string_id = preg_replace("/https?:\/\/vk\.com\//", '', $url);
        }

        return $string_id;
    }

    protected function getObjectUserLikes($params, $api_connection)
    {
        $request = new Request('likes.getList', ['type' => $params['object_type'], 'owner_id' => $params['owner_id'],
        'item_id' => $params['item_id'], 'count' => $params['count'], 'offset' => $params['offset'], 'skip_own' => 1]);

        sleep(1);
        $response = $api_connection->send($request);

        return $response['response']['items'];
    }


    protected function getMembers($group_id, $params = ['count' => 1000], $fields = '')
    {
        $query = new Request('groups.getMembers', ['group_id' => $group_id, 'count' => $params['count'], 'offset' => $params['offset'], 'fields' => $fields]);

        return $params['api_connection']->send($query);
    }

    protected function getAllMembers($group_id, $apiConnection)
    {

        $request = $this->getMembers($group_id, ['offset' => 0, 'api_connection' => $apiConnection, 'count' => 1000]);
//
//        $requestQty = (int)ceil($request['response']['count'] / 1000);

        sleep(1);

        $membersID = [];
        for ($i = 0; $i < 2; $i++) {
            sleep(1);
            $membersID[$i] = $this->getMembers($group_id, ['offset' => ($i * 1000), 'api_connection' => $apiConnection, 'count' => 1000])['response']['items'];
        }

        return $membersID;
    }
}
