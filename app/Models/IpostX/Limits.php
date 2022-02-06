<?php

namespace App\Models\IpostX;

use Amp\Redis\Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use Amp;
use Amp\Delayed;
use Amp\Loop;
use function Amp\Websocket\Client\connect;

use App\Models\IpostX\Storages\MessageStorage;
use App\Models\IpostX\MessagesDebug;
use App\Models\IpostX\MessageFromDB;

use App\Jobs\CommentTreatment;



class Limits extends Model
{
    use HasFactory;

    public $dbInstance;
    public $redisInstance;

    public function __construct(array $attributes = [])
    {
        $this->initializeDBConnections();

        parent::__construct($attributes);
    }

    protected function initializeDBConnections()
    {
        try {
            $dbHost = env('DB_HOST');
            $dbUsername = env('DB_USERNAME');
            $dbDatabase = env('DB_DATABASE');
            $dbPassword = env('DB_PASSWORD');
            $dbConfig = Amp\Mysql\ConnectionConfig::fromString(
                "host={$dbHost} user={$dbUsername} password={$dbPassword} db={$dbDatabase}"
            );

            $redisPass = env('REDIS_PASSWORD');
            $redisHost = env('REDIS_HOST');
            $redisPort = env('REDIS_PORT');


            $this->redisInstance = new Amp\Redis\Redis(new Amp\Redis\RemoteExecutor(Config::fromUri("redis://null:{$redisPass}@{$redisHost}:{$redisPort}")));
            $this->dbInstance = Amp\Mysql\pool($dbConfig);
        }
        catch (\Exception $e)
        {
            Log::write('debug', 'Во время инициализации баз данных для лимитов случилась ошибка', ['error' => $e]);
            return false;
        }

        return true;

    }

    //CommentMessage - это json обьект который вмещает в себе такие свойства как первый комментарий и reply комментарий
    //также id комментария и accessToken
    //        ['firstComment' => $this->firstMessage, 'replyComment' => $this->replyMessage,
    //            'commentId' => $this->commentId, 'userId' => $this->uid, 'deleteIsNeeded' => $this->deleteIsNeeded,
    //            'mediaId' => $this->mediaId, 'msgId' => $this->id];
    /**
     * @param $commentMessage - json обьект
     */
    public static function insertCommentMessageToTailOfQueue(string $commentMessage)
    {
        if(empty($commentMessage))
        {
            Log::write('error',
                'CommentMessage не может быть отправлен, так как пустой обьект');
        }

        try {
            Redis::command('RPUSH', ['commentMessageQueue', $commentMessage]);

            return ['status' => 'success', 'msg' => 'Successfully added to queue'];
        }
        catch (\Exception $e)
        {
            Log::write('error',
                'Произошла ошибка при попытке добавить commentMessage в очередь для отправки комментов', ['error' => $e->getMessage()]);

            return ['status' => 'fail', 'msg' => 'Comment not added to queue'];
        }
    }

    public function getCommentMessageFromBeginningOfQueue()
    {
        try {
            $commentMessage = yield $this->redisInstance->query('LPOP', 'vkipostxru_database_commentMessageQueue');

            return $commentMessage;
        }
        catch (\Exception $e)
        {
            Log::write('error',
                'Произошла ошибка при попытке получить commentMessage с очереди для отправки комментов');
            return false;
        }
    }

//    /**
//     * @param $userId
//     * @param $redis - экземпляр класса Amp\Redis\Redis так как работа происходит в
//     * асинхронном режиме с помощью AmPHP
//     * @return bool - если лимит не превышен возвращаем false, а иначе true
//     */
//    public function checkLimitAchievedForMessage($userId)
//    {
//        $limitListLength = $this->redisInstance->query('LLEN', 'limit_list_for_' . $userId);
//
//        if($limitListLength < 70)
//        {
//            return false;
//        }
//
//        return true;
//    }

    /**
     * @param string $error
     * @param int $msgId - id из модели MessageFromDB
     * @param int $status - id из модели MessageStatuses
     * @return bool
     */
    public static function writeMessageDebugToDBStatic(string $error, int $msgId, int $status_id)
    {
        if(!empty($error) && !empty($msgId))
        {
            try {
                $debug = new MessagesDebug();
                $debug->msgId = $msgId;
                $debug->text = $error;
                $debug->status_id = $status_id;

                $debug->save();
            }
            catch (\Exception $e)
            {
                Log::write('error',
                    'Запись статуса сообщения в базу данных не удалась!',
                    ['error' => $e->getMessage()]);

                return false;
            }

            return true;
        }
        else
        {
            Log::write('error',
                'Для записи статуса сообщения в базу данных нужны параметры: 
                error, msgId, status один из них отсутствует!',
                ['status_id' => !empty($status_id), 'error' => !empty($error), 'msgId' => !empty($msgId)]);
        }

        return false;

    }

    protected function writeMessageDebugToDB($error, $msgId, $status = 'success')
    {
        $dbClient = $this->dbInstance;

        if(!empty($dbClient) && !empty($error) && !empty($msgId))
        {
            try {
                $dbPrepare = $dbClient->prepare("INSERT INTO messages_debug  VALUES(msgId=:msgId, text=:error, status=:status)");

                $dbPrepare->execute([':msgId' => $msgId, ':text' => $error, ':status' => $status]);
            }
            catch (\Exception $e)
            {
                Log::write('error',
                    'Запись статуса сообщения в базу данных не удалась!',
                    ['error' => $e->getMessage()]);

                return false;
            }

            return true;
        }
        else
        {
            Log::write('error',
                'Для записи статуса сообщения в базу данных нужны параметры: 
                dbClient, error, msgId, один из них отсутствует!',
                ['dbClient' => !empty($dbClient), 'error' => !empty($error), 'msgId' => !empty($msgId)]);
        }

        return false;

    }

    public function limitHandler()
    {
        Loop::run(function ()
        {
            $redisOptions = new Amp\Redis\SetOptions();

            yield $this->redisInstance->set('loopIsStarted' . time(), 1, $redisOptions->withTtl(36000));

            while (true)
            {
                if(yield $this->redisInstance->has('ipostxvk_database_limitHandlerFlag'))
                {
                    yield $this->redisInstance->set('connectionIsCloseDelete'. time(), true, $redisOptions->withTtl(36000));

                    yield $this->redisInstance->delete('ipostxvk_database_limitHandlerFlag');
                    Loop::stop();

                    break;
                }

                $message = yield $this->redisInstance->query('LPOP', 'vkipostxru_database_commentMessageQueue');

                if(!empty($message))
                {
                    $commentTreatmentJob = new CommentTreatment($message, $this);
                    dispatch($commentTreatmentJob);
                }

                yield new Delayed(1000);
            }
        });
    }

//    /**
//     * @param int $msgId - id модели MessageFromDB
//     * @return bool
//     */
//    public function checkMessageTreatmentStatus(int $msgId)
//    {
//        $messageFromDb = MessageFromDB::select('isChoosenForTreatment')::where(['id' => $msgId])->first();
//
//        if(!empty($messageFromDb))
//        {
//            if($messageFromDb->isChoosenForTreatment)
//            {
//                return true;
//            }
//        }
//
//        return false;
//    }
}
