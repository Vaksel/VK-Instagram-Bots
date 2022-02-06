<?php

namespace App\Models\IpostX\Storages;

use App\Models\IpostX\MessageFromDB;
use App\Models\IpostX\Limits;
use App\Models\IpostX\AuthTokens;
use App\Models\IpostX\MessageStatuses;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MessageStorage
{
    public $msgId;
    public $mediaId;
    public $commentId;
    public $userId;
    public $firstComment;
    public $replyComment;
    public $deleteIsNeeded;

    public function __construct($messageJson)
    {
        if(empty($messageJson))
        {
            throw new \Exception('Обьект для сообщения пустой');
        }


        $message = json_decode($messageJson, true);

        if(!isset($message['deleteIsNeeded']))
        {
            if(count($message) !== 6)
            {
                throw new \Exception('Все параметры для MessageStorage обязательны!');
            }
        }
        else
        {
            if(count($message) !== 7)
            {
                throw new \Exception('Все параметры для MessageStorage обязательны!');
            }
        }


        $this->mediaId = $message['mediaId'];
        $this->msgId = $message['msgId'];
        $this->commentId = $message['commentId'];
        $this->userId = $message['userId'];
        $this->firstComment = $message['firstComment'];
        $this->replyComment = $message['replyComment'];
    }

    public function getJsonFormattedComment()
    {
        $commentArr = ['firstComment' => $this->firstComment, 'replyComment' => $this->replyComment,
            'commentId' => $this->commentId, 'userId' => $this->userId, 'deleteIsNeeded' => $this->deleteIsNeeded,
            'mediaId' => $this->mediaId, 'msgId' => $this->msgId];

        return json_encode($commentArr);
    }

    public function getArrayFormattedComment()
    {
        return ['mediaId' => $this->mediaId, 'firstComment' => $this->firstComment, 'replyComment' => $this->replyComment,
            'commentId' => $this->msgId, 'userId' => $this->userId];
    }

    /**
     * @return bool
     */
    public function checkMessageTreatmentStatus()
    {
        $messageFromDb = MessageFromDB::select('isChoosenForTreatment')->where(['id' => $this->msgId])->first();

        if(!empty($messageFromDb))
        {
            if($messageFromDb->isChoosenForTreatment)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool - если лимит не превышен возвращаем false, а иначе true
     */
    public function checkLimitAchievedForMessageLength()
    {
        try {
            $limitListLength = Redis::command('LLEN', ['limit_list_for_' . $this->userId]);

            if($limitListLength < 70)
            {
                return false;
            }
        } catch (\Exception $e)
        {
            Log::write('error', 'Проверка лимитов комментариев в час не удалась', [
                'place' => 'MessageStorage::checkLimitAchievedForMessageLength',
                'error' => $e->getMessage()] );

            return true;
        }


        return true;
    }

    public function commentsIsDone()
    {
        $comment = MessageFromDB::where(['id' => $this->msgId, 'uid' => $this->userId])->first();

        if(!empty($comment))
        {
            $comment->status_id = MessageStatuses::IS_DONE;
            $comment->isChoosenForTreatment = false;
            $comment->save();
        }
        else
        {
            Log::write('error', 'Комментарий для изменения статуса на выполнен, не найден!',
                ['id' => $this->msgId, 'uid' => $this->userId]);
        }
    }

    /**
     * @return bool - если лимит не превышен возвращаем false, а иначе true
     */
    public function checkLimitAchievedForMessageSecs()
    {
        try {
            if(Redis::command('HEXISTS', ['limit_secs_table', $this->userId]))
            {
                $secondsFromPrevSendingToInstagramByUserId = Redis::command('HGET', ['limit_secs_table', $this->userId]);

                if(time() - 80 >= $secondsFromPrevSendingToInstagramByUserId)
                {
                    return false;
                }
            }
            else
            {
                Redis::command('HSET', ['limit_secs_table', $this->userId, time()]);

                return false;
            }
        } catch (\Exception $e)
        {
            Log::write('error', 'Проверка задержи отправки комментариев не удалась', [
                'place' => 'MessageStorage::checkLimitAchievedForMessageSecs',
                'error' => $e->getMessage()] );

            return true;
        }


        return true;
    }

    /**
     *  Здесь точка входа для проверки лимитов комментариев
     * @return bool - true если лимиты есть и отправка не возможно, false если лимитов нету
     */
    public function checkMessageLimits()
    {
        $lengthLimitRes = $this->checkLimitAchievedForMessageLength();
        $secsTimeoutLimitRes = $this->checkLimitAchievedForMessageSecs();

        if($lengthLimitRes || $secsTimeoutLimitRes)
        {
            return true;
        }

        return false;
    }

    /**
     * @param $msgId - id модели MessageFromDB
     * @param $userId - id из таблицы users
     * @return bool|\Generator
     * Description: используется для того чтобы пролистать очередь с лимитами
     * и удалить просроченные (тоесть такие у которых время жизни больше 60 минут)
     */
    public function recursiveTimeChecker($msgId, $userId)
    {
        $lastElement = Redis::command('RPOP', ['limit_list_for_' . $userId]);

        if(empty($lastElement))
        {
            return true;
        }

        $lastElement = json_decode($lastElement, true);

        if((time() - $lastElement['time']) > 3600)
        {
            $this->recursiveTimeChecker($msgId, $userId);
        }
        else
        {
            Redis::command('RPUSH',['limit_list_for_' . $userId, json_encode($lastElement)]);
        }

        return true;
    }

    public function pushCommentIdToLimits($instagramCommentId)
    {
        try {
            $this->recursiveTimeChecker($this->commentId, $this->userId);

            Redis::command('LPUSH', ['limit_list_for_' . $this->userId, json_encode(['commentId' => $this->commentId,
                'time' => time(), 'instagramCommentId' => $instagramCommentId])]);

            return ['status' => 'success'];
        } catch (\Exception $e)
        {
            Log::write('error', 'Во время записи комментария в id лимиты произошла ошибка', ['error' => $e->getMessage()]);
            return ['status' => 'fail', 'place' => 'MessageStorage::pushCommentIdToLimits', 'errorInPushCommentIdToLimits' => $e->getMessage()];
        }
    }

    protected function instagramApiErrorHandler($error)
    {
        if($error === 'Похоже, что вы злоупотребляли этой функцией. Она для вас теперь временно заблокирована.')
        {
            Redis::command('HSET', ['limit_secs_table', $this->userId, time() + 4500]);
        }
    }


    public function startCommentTreatment()
    {
        $accessToken = AuthTokens::select('value')->where(['uId' => $this->userId, 'name' => 'facebookAccessToken'])->first();

        if(empty($accessToken))
        {
            Log::write('error', 'accessTokenNotFound-params:', ['uId' => $this->userId, 'name' => 'facebookAccessToken']);

            return false;
        }

        $accessToken = $accessToken->value;

        Log::write('debug', 'accessToken', ['accessToken' => $accessToken]);

        $firstCommentRes = MessageFromDB::SendCommentStatic($this->mediaId, $this->firstComment, $accessToken);

        Log::write('debug', 'firstCommentRes', ['firstCommentRes' => $firstCommentRes]);

        if(!empty($firstCommentRes))
        {
            if($error = MessageFromDB::checkInstagramApiErrorsAndGetIfExist($firstCommentRes))
            {
                $this->instagramApiErrorHandler($error);

                Limits::writeMessageDebugToDBStatic($error, $this->msgId, MessageStatuses::IS_SENDING_FIRST_COMMENT_FAIL);
            }
            else
            {
                $pushCommentToLimitRes = $this->pushCommentIdToLimits($firstCommentRes['id']);
                if($pushCommentToLimitRes['status'] !== 'success') return false;

                Log::write('debug', 'replyCommentResParams', ['commentRes' => $firstCommentRes['id'],
                    'msg' => $this->replyComment, 'accessToken' => $accessToken]);


                $replyCommentRes = MessageFromDB::ReplyCommentStatic($firstCommentRes['id'], $this->replyComment, $accessToken);
                Log::write('debug', 'replyCommentRes', ['replyCommentRes' => $replyCommentRes]);


                if($error = MessageFromDB::checkInstagramApiErrorsAndGetIfExist($replyCommentRes))
                {
                    Limits::writeMessageDebugToDBStatic($error, $this->msgId, MessageStatuses::IS_SENDING_REPLY_COMMENT_FAIL);
                }
                else
                {
                    $pushCommentToLimitRes = $this->pushCommentIdToLimits($replyCommentRes['id']);
                    if($pushCommentToLimitRes['status'] !== 'success') return false;

                    if($this->deleteIsNeeded)
                    {
                        $resCommentDelete = MessageFromDB::DeleteCommentStatic($firstCommentRes['id'], $accessToken);

                        Log::write('debug', 'resCommentDelete', ['resCommentDelete' => $resCommentDelete]);

                        if($error = MessageFromDB::checkInstagramApiErrorsAndGetIfExist($resCommentDelete))
                        {
                            Limits::writeMessageDebugToDBStatic($error, $this->msgId, MessageStatuses::IS_DELETING_COMMENT_FAIL);
                        }
                    }

                    return true;
                }
            }
        }

        return false;
    }

}