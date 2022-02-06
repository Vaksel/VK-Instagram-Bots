<?php

namespace App\Http\Controllers\IpostX;

use App\Http\Controllers\Controller;

use App\Models\IpostX\MessageFromDB;
use App\Models\IpostX\MessageStatuses;
use App\Models\IpostX\Limits;


use http\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isEmpty;

/**
валидация для boolean
 */

class CopyMessageController extends Controller
{

    public function testMessageSending()
    {
        $commentRes = MessageFromDB::SendCommentStatic(17872310026981186, 'test', 'EAAMaz64zyMIBAL1urPc44abEY78ZBWoSAYxGpCZC4gAovgjTjNwOWHUYUGZCikDWGYVteZBfwd8YZBww6yEa6AvTKdmGLgxI1NR0tSmGAh5V8vYy2z1334qyseD2BsvT9UpjsxhJFlrXZCtZCMqWLVkAHqHhCPASVkjrS6BvNaul79PAp8UipD2cYtfCe9yYSkXUAZBgQw4HEp5OiOWcSuCw');

        print_r($commentRes);
    }

    public function messages(Request  $request)
    {
//        $request->validate([
//            'userId' => 'required'
//        ]);

        $messages = MessageFromDB::where(['uId' => $request->userId])->with('status')->get();

        $messagesForResponse = [];

        foreach ($messages as $msg)
        {
            Log::write('debug', $msg->status);
            $status = json_decode($msg->status);

            $messageBuffer = [];

            $messageBuffer['id']                = $msg->commentId;
            $messageBuffer['chosen']            = $msg->isChoosenForTreatment;
            $messageBuffer['firstComment']      = $msg->firstMessage;
            $messageBuffer['replyComment']      = $msg->replyMessage;
            $messageBuffer['status']            = $status->name;

            $messagesForResponse[] = $messageBuffer;
        }

        if(!empty($messagesForResponse))
        {
            return response()->json(['messages' => $messagesForResponse], 200);
        }
        else
        {
            return response()->json(['messages' => []], 200);
        }
    }

    public function getStatuses(Request  $request)
    {
        try {
            $messages = MessageFromDB::select(['commentId', 'status_id'])->with('status')->where('uid', $request->userId)->get();

            $statuses = [];
            foreach ($messages as $msg)
            {
                $statuses[$msg->commentId] = $msg->status;
            }
        } catch (\Exception $e)
        {
            Log::write('debug', 'Произошла ошибка при попытке получить статусы сообщения!',
                ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error', 'msg' => 'Произошла ошибка при попытке получить обновленные статусы, 
            попробуйте перезапустить страницу и продолжить поссле перезапуска']);
        }

        return response()->json(['status' => 'success', 'statuses' => $statuses]);
    }

    public function index(Request  $request)
    {
        $request->validate([
            'id' => 'required',
            'userId' => 'required',
            'firstComment' => 'required|min:2',
            'replyComment' => 'required|min:2',
            'mediaId' => 'required|min:2',
            'deleteIsNeeded' => 'required',
            'isChoosen' => '',
            'isChoosenSmile' => '',
        ]);

        $message = new MessageFromDB();
        $message->uid = $request->userId;
        $message->commentId = $request->id;
        $message->mediaId = $request->mediaId;
        $message->firstMessage = $request->firstComment;
        $message->replyMessage = $request->replyComment;
        $message->deleteIsNeeded = (bool)$request->deleteIsNeeded;
        $message->save();

        return response()->json(['status' => 'success']);
    }

    public function addComments(Request  $request)
    {
        $request->validate([
            'comments' => 'required',
        ]);

        Log::write('debug', 'addCommentsCalled' . time(), ['comment' => $request->comments]);


        foreach ($request->comments as $key => $comment)
        {
//            Log::write('debug', 'comment' . time(), ['comment' => $comment]);

            if(!isset($comment['id']) || !isset($comment['mediaId']) || !isset($comment['firstComment']) || !isset($comment['replyComment'])
                || !isset($comment['deleteIsNeeded']))
            {
                return response()->json(['status' => 'paused', 'lastProcessedComment' => $key]);
            }

            $message = new MessageFromDB();
            $message->uid = $request->userId;
            $message->commentId = $comment['id'];
            $message->mediaId = $comment['mediaId'];
            $message->firstMessage = $comment['firstComment'];
            $message->replyMessage = $comment['replyComment'];
            $message->deleteIsNeeded = (bool)$comment['deleteIsNeeded'];
            $message->save();
        }

        return response()->json(['status' => 'success']);
    }


    public function edit(Request $request){
        $request->validate([
            'id_com' => 'required',
            'firstComment' => 'required|min:2',
            'replyComment' => 'required|min:2',
        ]);

        $message = MessageFromDB::find()->where(['commentId' => $request->id_com])->first();
        if(empty($message)){
            return response('Message not found',404);
        }

        $message->firstMessage = $request->firstComment;
        $message->replyMessage = $request->replyComment;
        $message->save();
        return response()->json(['status' => 'success', 'msg' => 'Сообщение успешно отредактировано']);
    }

    public function editAction(Request $request){
        $request->validate([
            'id_com' => 'required',
            'smile_state' => 'required',
            'check_state' => 'required',
        ]);

        $message = MessageFromDB::find()->where(['id_com' => $request->id_com])->first();
        if(empty($message)){
            return response('Message not found',404);
        }

        $message->isChoosenForTreatment = $request->isChoosenForTreatment;
        $message->isChoosenSmile = $request->isChoosenSmile;
        $message->save();
        return response()->json(['status' => 'success']);
    }

    public function delete(Request $request){
        $request->validate([
            'id_com' => 'required'
        ]);
        $message = MessageFromDB::find()->where(['id_com' => $request->id_com])->first();
        if(empty($message)){
            return response('Message not found',404);
        }

        $message->delete();
        return response()->json(['status' => 'success', 'msg' => 'Message deleted']);
    }

    public function checkIsCommentExist(Request $request)
    {
        $request->validate([
            'firstComment' => 'required',
            'replyComment' => 'required',
            'mediaId' => 'required',
        ]);
    }

    public function startTreatment(Request $request)
    {
        $comments = MessageFromDB::where(['uId' => $request->userId, 'isChoosenForTreatment' => true])->get();

        if(empty($comments))
        {
            return response()->json(['status' => 'error', 'msg' => 'Сообщения для обработки найдены не были, 
            попробуйте выбрать сообщения для обработки в меню "Избранные"'],404);
        }

        foreach ($comments as $comment)
        {
            $insertingCommentResponse = Limits::insertCommentMessageToTailOfQueue(
                $comment->getJsonFormattedComment());

            if($insertingCommentResponse['status'] === 'success')
            {
                $comment->status_id = MessageStatuses::IN_TREATMENT;
                $comment->save();
                Limits::writeMessageDebugToDBStatic($insertingCommentResponse['msg'], $comment->id, $comment->status_id);
            }
            else
            {
                $comment->status_id = MessageStatuses::IS_SENDING_FAIL;
                $comment->save();
                Limits::writeMessageDebugToDBStatic($insertingCommentResponse['msg'], $comment->id, $comment->status_id);
                return response(['status' => 'fail', 'Во время добавления одного из комментариев в обработку произошла ошибка, попробуйте снова!'], 500);
            }
        }

        return response(['status' => 'success', 'Добавление комментариев в обработку прошло успешно!']);
    }

    public function toggleCommentTreatmentBySingle(Request $request)
    {
        $request->validate([
            'commentId' => 'required',
        ]);

        $comment = MessageFromDB::where(['commentId' => $request->commentId, 'uId' => $request->userId])->first();

        if(empty($comment)){
            return response()->json('Message not found',404);
        }

        if($comment->isChoosenForTreatment)
        {
            $comment->isChoosenForTreatment = false;
            $comment->status_id = MessageStatuses::IS_CANCEL_TREATMENT;

            $comment->save();

            Limits::writeMessageDebugToDBStatic('Изъятие комментария из обработки прошло успешно!', $comment->id, $comment->status_id);

            return response()->json(['status' => 'success', 'Изъятие комментария из обработки прошло успешно!']);
        }
        else
        {
            if(!empty($request->mediaId))
            {
                $comment->mediaId = $request->mediaId;
            }
            
            $comment->isChoosenForTreatment = true;
            $comment->status_id = MessageStatuses::IS_SENDING;

            $comment->save();

            Limits::writeMessageDebugToDBStatic('Избрание комментария в обработку прошло успешно!', $comment->id, $comment->status_id);
            return response()->json(['status' => 'success', 'Избрание комментария в обработку прошло успешно!']);
        }
    }
}
