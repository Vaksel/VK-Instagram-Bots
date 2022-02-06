<?php

namespace App\Models\IpostX;

use http\Env\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Itstructure\GridView\Actions\Delete;

use App\Models\IpostX\MessageStatuses;

class MessageFromDB extends Model
{
    use HasFactory;

    protected $table = 'messages';


    /**
    public function GetMessage(){
    $id = $this->id;
    $id_com = $this->id_com;
    $first_com = $this->first_com;
    $second_com = $this->second_com;
    $isChoosen = $this->isChoosen;
    $isChoosenSmile = $this->isChoosenSmile;
    }
     */

    public static function MakeApiCall($url,$type,$data){
        $ch = curl_init($url);
        if('POST' == $type){
            $headers = array(
                "Content-Type: application/x-www-form-urlencoded",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch,CURLOPT_POST,1);

        }else if('DELETE' == $type){
            $headers = array(
                "Content-Type: application/x-www-form-urlencoded",
            );
            curl_setopt($ch, CURLOPT_DELETE, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        $responce = curl_exec($ch);
        curl_close($ch);
        return json_decode($responce,true);

    }

    public static function SendCommentStatic($igMediaId,$message,$accesstoken){

        if(empty($igMediaId) || empty($message) || empty($accesstoken))
        {
            throw new \Exception('Для отправки комментария обязательны все параметры!');
        }

        $message = urlencode($message);

        $url = "https://graph.facebook.com/v12.0/{$igMediaId}/comments?message={$message}&access_token={$accesstoken}";

        $postdata = [
            'message' => $message,
        ];

        $answ = self::MakeApiCall($url,'POST',$postdata);
        return $answ;
    }

    public static function ReplyCommentStatic($commentId, $message, $accesstoken){

        if(empty($commentId) || empty($message) || empty($accesstoken))
        {
            throw new \Exception('Для отправки комментария обязательны все параметры!');
        }

        $message = urlencode($message);

        $url = "https://graph.facebook.com/v12.0/{$commentId}/replies?message={$message}&access_token={$accesstoken}";

        $postdata = [
            'message' => $message,
        ];

        $answ = self::MakeApiCall($url,'POST',$postdata);
        return $answ;
    }

    protected function SendComment($igMediaId,$message,$accesstoken){

        if(empty($igMediaId) || empty($message) || empty($accesstoken))
        {
            throw new \Exception('Для отправки комментария обязательны все параметры!');
        }

        $message = urlencode($message);

        $url = "https://graph.facebook.com/v12.0/{$igMediaId}/comments?message={$message}&access_token={$accesstoken}";

        $postdata = [
            'message' => $message,
        ];

        $answ = $this->MakeApiCall($url,'POST',$postdata);
        return $answ;
    }

    public static function DeleteCommentStatic($igCommentId,$accesstoken){

        if(empty($igCommentId) || empty($accesstoken))
        {
            throw new \Exception('Для отправки комментария обязательны все параметры!');
        }

        $url = "https://graph.facebook.com/v12.0/{$igCommentId}?access_token={$accesstoken}";

        $result = self::MakeApiCall($url,'DELETE');
        return $result;
    }

    protected function DeleteComment($igMediaId,$accesstoken){

        if(empty($igMediaId) || empty($accesstoken))
        {
            throw new \Exception('Для отправки комментария обязательны все параметры!');
        }

        $url = "https://graph.facebook.com/v12.0/{$igMediaId}?access_token={$accesstoken}";

        $result = $this->MakeApiCall($url,'DELETE');
        return $result;
    }

    public static function checkInstagramApiErrorsAndGetIfExist($apiRes)
    {
        if(!empty($apiRes))
        {
            if(!empty($apiRes['error']))
            {
                Log::write('debug', 'Была обнаружена ошибка во время получения ответа от API Instagram', ['error' => $apiRes['error']]);

                return $apiRes['error']['message'];
            }
        }
    }

    public static function getJsonFormattedCommentStatic($firsComment, $replyComment, $commentId, $userId)
    {
        $commentArr = ['firstComment' => $firsComment, 'replyComment' => $replyComment,
            'commentId' => $commentId, 'userId' => $userId];

        return json_encode($commentArr);
    }

    public function getJsonFormattedComment()
    {
        $commentArr = ['firstComment' => $this->firstMessage, 'replyComment' => $this->replyMessage,
            'commentId' => $this->commentId, 'userId' => $this->uid, 'deleteIsNeeded' => $this->deleteIsNeeded,
            'mediaId' => $this->mediaId, 'msgId' => $this->id];

        return json_encode($commentArr);
    }

    public function status()
    {
        return $this->belongsTo(MessageStatuses::class, 'status_id');
    }
}
