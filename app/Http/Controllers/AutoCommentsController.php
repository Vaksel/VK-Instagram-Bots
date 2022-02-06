<?php


namespace App\Http\Controllers;


use App\Http\Controllers\Service\ServiceActionsController;
use ATehnix\VkClient\Client;
use ATehnix\VkClient\Requests\ExecuteRequest;
use \ATehnix\VkClient\Requests\Request;
use Illuminate\Http\Request as BasicRequest;
use Illuminate\Support\Facades\Auth;

class AutoCommentsController extends ServiceActionsController
{
    protected $apiConnection;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function writeCommentsToPosts($records, $params)
    {
        set_time_limit(0);

        $oldDate = time();

        $request = [];
        $response = [];
        $successCounter = 0;
        $captcha_answer = '';
        $finalCounter = 0;
        $itemCounter = 0;

    foreach (array_chunk($records, 25, true) as $record_chunk)
    {
        foreach ($record_chunk as $key => $record) {
            foreach ($record as $community_item_id => $community_item)
            {
                $request[] = new Request('wall.createComment', ['owner_id' => $key, 'post_id' => $community_item_id,
                    'message' => $params['message']]);

                $itemCounter++;
                $finalCounter++;


                if($itemCounter === 25)
                {
                    $execute_request = ExecuteRequest::make($request);
                    try {
                        sleep(1);
                        $this->apiConnection->send($execute_request);

                        $request = [];

                        $successCounter++;
                        $itemCounter = 0;

                    } catch (\Exception $e) {
                        if ($e->getCode() == 14) {

                            $request = [];

                            $captcha_answer = $this->exceptionHandlerAutoComments($e, $response,
                                ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'posts.autoComments']);

                            $request[] = new Request('wall.createComment', ['owner_id' => $key,
                                'post_id' => $community_item_id, 'message' => $params['message'],
                                'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                            sleep(1);

                            $this->apiConnection->send($request);

                            $request = [];

//                        ddd($this->apiConnection->send($request));

                            $execute_request = $this->apiConnection->send($request);

//                            sleep(1);

                            $captcha_answer = '';
                        } else if ($e->getCode() == 9){

                            return 'Флуд';
                        } else {
                            continue;
                        }
                    }
                }
            }
            $execute_request = ExecuteRequest::make($request);

            try {
                sleep(1);
                $this->apiConnection->send($execute_request);

                $request = [];

                $successCounter++;
                $itemCounter = 0;

            } catch (\Exception $e) {
                if ($e->getCode() == 14) {

                    $request = [];

                    $captcha_answer = $this->exceptionHandlerAutoComments($e, $response,
                        ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'posts.autoComments']);

                    $request = new Request('wall.createComment', ['owner_id' => $key,
                        'post_id' => $community_item_id, 'message' => $params['message'],
                        'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

                    sleep(1);

                    $this->apiConnection->send($request);

                    $request = [];

//                        ddd($this->apiConnection->send($request));

//                    sleep(1);

                    $captcha_answer = '';
                } else if ($e->getCode() == 9){

                    return 'Флуд';
                } else {
                    null;
//                    ddd($e);
                }
            }


        }
    }

        return $finalCounter;
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

    protected function getAllSortedPosts($comments, $params)
    {
        foreach ($comments as $community_id => $community_items) {
            foreach ($community_items as $community_item_id => $community_item) {
                foreach ($community_item['comments'] as $comment)
                {
                    if ($comment['from_id'] === $params['from_id']) {
                        unset($community_items[$community_item_id]);
                    }
                }
            }
        }

        return $comments;
    }

//    protected function commentRecords($filteredRecords, $params)
//    {
//        foreach ($filteredRecords as $record)
//        {
//            $request = new \ATehnix\VkClient\Requests\Request('wall.createComment', ['owner_id' => $record['owner_id']])
//        }
//
//    }

    public function writeCommentsByTags(BasicRequest $request)
    {

        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }


        $currentUserId = $this->apiConnection->send(new \ATehnix\VkClient\Requests\Request('account.getProfileInfo', []))['response']['id'];

        $request->input('tags') ? $params['tags'] = $this->transformTextToTags($request->input('tags')) : $params['tags'] = '';

        $request->input('requests') ? $params['count'] = $request->input('requests') : $params['count'] = 1;

        $params['fields'] = '';

        $params['start_time'] = strtotime($request->input('searchStart', ''));

        $params['end_time'] = strtotime($request->input('searchEnd', ''));

        if($params['end_time'] === $params['start_time'])
        {
            $params['end_time'] += 86400;
        }

        if($request->input('comment'))
        {
            $comment = $request->input('comment');
        }
        else
        {
            session()->flash('commentFieldRequired', 'Введите комментарий');

            return redirect('dashboard');
        }



        $records = $this->getAllSearchRecords($params, $this->apiConnection);

        $recordsComments = $this->getAllCommentsFromRecords($records, $params, $this->apiConnection);

        $fiteredRecords = $this->getAllSortedPosts($recordsComments, ['from_id' => $currentUserId]);

        $requestCount = $this->writeCommentsToPosts($fiteredRecords, ['message' => $comment]);

        session()->flash('successTagRec', $requestCount.' комментов было проставлено по тегам');

        return redirect('dashboard');
    }

    protected function getWallPostFromUsers($users, $params = [])
    {
        $response = [];
        $request = [];

//        ddd($users);
        foreach(array_chunk($users, 25) as $users_chunk)
        {
            foreach ($users_chunk as $user)
            {
                $request[] = new Request('wall.get', ['owner_id' => $user['id'], 'count' => $params['count']]);
            }

            sleep(1);

            $execute = ExecuteRequest::make($request);

            $executeResponse = $this->apiConnection->send($execute)['response'];

            $request = [];
            foreach ($executeResponse as $record)
            {
                $response = array_merge($response,$record['items']);
            }

        }

        return $response;
    }

    protected function filterUsers($users)
    {
        $filteredUsers = [];
        foreach(array_chunk($users, 1000) as $userChunk)
        {
            $userIds = [];

            foreach($userChunk as $userItem)
            {
                $userIds[] = $userItem['id'];
            }

            $usersImplode = implode(',', $userIds);
            $request = new Request('users.get', ['user_ids' => $usersImplode, 'fields' => 'blacklisted']);

            $response = $this->apiConnection->send($request)['response'];

            $userIds = [];

            foreach ($response as $key => $user)
            {
                if(isset($user['deactivated']) || $user['blacklisted'] === 1)
                {
                    unset($response[$key]);
                }
                if(isset($user['is_closed']) && $user['is_closed'] == true && isset($user['can_access_closed']) && $user['can_access_closed'] == false)
                {
                    unset($response[$key]);
                }
            }

            $filteredUsers = array_merge($filteredUsers, $response);

        }

        return $filteredUsers;
    }

    protected function commentCaptchaSkipper($e, $response, $post, $params, $successCounter)
    {
        $captcha_answer = $this->exceptionHandlerAutoComments($e, $response,
            ['owner_id' => $e->request_params[0]['value'], 'captchaType' => 'posts.autoComments']);

        $request = new Request('wall.createComment', ['owner_id' => $post['owner_id'],
            'post_id' => $post['id'], 'message' => $params['message'],
            'captcha_sid' => $e->captcha_sid, 'captcha_key' => $captcha_answer]);

//                        ddd($this->apiConnection->send($request));

        try
        {
            sleep(1);
            $response[] = $this->apiConnection->send($request);
            $captcha_answer = '';
            $successCounter++;
        }
        catch(\Exception $e)
        {
            if ($e->getCode() == 14) {

                $result = $this->commentCaptchaSkipper($e, $response, $post, $params, $successCounter);

                $response[] = $result['response'];
                $successCounter = $result['success_counter'];
            } else if ($e->getCode() == 9){
                return 'Флуд';
            } else if ($e->getCode() == 213){
                null;
            } else if ($e->getCode() == 10){
                null;
            } else if ($e->getCode() == 15){
                null;
            } else {
                null;
            }
        }


        sleep(1);



        return ['response' => $response, 'success_counter' => $successCounter];
    }


    protected function writeCommentOnWallForConcurrents($posts, $params)
    {
        set_time_limit(0);

        $oldDate = time();

        $request = [];
        $response = [];
        $successCounter = 0;
        $captcha_answer = '';


        foreach ($posts as $key => $post) {
                $request = new Request('wall.createComment', ['owner_id' => $post['owner_id'], 'post_id' => $post['id'],
                    'message' => $params['message']]);

                sleep(1);

                try {

                    $response[] = $this->apiConnection->send($request);
                    $successCounter++;

                } catch (\Exception $e) {
                    if ($e->getCode() == 14) {

                        $successCounter = $this->commentCaptchaSkipper($e, $response, $post, $params, $successCounter)['success_counter'];

                    } else if ($e->getCode() == 9){

                        return 'Флуд';
                    } else if ($e->getCode() == 213){
                        continue;
                    } else if ($e->getCode() == 10){
                        continue;
                    } else if ($e->getCode() == 15){
                        continue;
                    } else {
                        continue;
                    }
                }
        }

        return ['response' => $response, 'success_counter' => $successCounter];
    }

    public function writeCommentsOnConcurrents(BasicRequest $request)
    {
        if(!$this->makeConnection())
        {
            return redirect()->route('dashboard');
        }

        $currentUserId = $this->apiConnection->send(new \ATehnix\VkClient\Requests\Request('account.getProfileInfo', []))['response']['id'];


        $postUrl = $request->input('post_url', '');

        if($request->input('comment'))
        {
            $comment = $request->input('comment');
        }
        else
        {
            session()->flash('commentFieldRequired', 'Введите комментарий');

            return redirect('dashboard');
        }


        $postParams = $this->getPostParams($postUrl);

        if(isset($postParams['not_right']))
        {
            session()->flash('not_valid_comment_concurrents', 'Вы ввели url записи в неправильном формате, попробуйте снова');
            return redirect('dashboard');
        }

        $userLikes = $this->getAllValues('likes.getList', ['type' => 'post', 'owner_id' => $postParams['owner_id'],
            'item_id' => $postParams['item_id'], 'count' => 1000, 'skip_own' => 1, 'extended' => 1]);

        $userComments = $this->getAllValues('wall.getComments', ['owner_id' => $postParams['owner_id'],
            'post_id' => $postParams['item_id'], 'count' => 100]);

        $userSortedComments = $this->getAllSortedComments($userComments, ['from_id' => $currentUserId]);

        $allUsers = array_merge($userLikes, $userComments);

//        ddd($allUsers);

        $filteredUsers = $this->filterUsers($allUsers);
//
//        ddd($filteredUsers);

        $posts = $this->getWallPostFromUsers($filteredUsers, ['count' => 1]);

//        ddd($posts);


        $result = $this->writeCommentOnWallForConcurrents($posts, ['message' => $comment]);

        session()->flash('concurrentCommentsSuccess', "Комментарии были успешно проставлены, к-во:{$result['success_counter']}");

        return redirect('dashboard');
    }
}
