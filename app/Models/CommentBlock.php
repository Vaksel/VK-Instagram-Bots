<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

require_once __DIR__ . '/vendor/autoload.php';

class CommentBlock extends Model
{
    use HasFactory;

    protected $fb;

    public function construct()
    {
        $this->fb = new \Facebook\Facebook([
            'app_id' => '{app-id}',
            'app_secret' => '{app-secret}',
            'default_graph_version' => 'v2.10',
            //'default_access_token' => '{access-token}', // optional
        ]);
    }

    public function sendCommentBlock()
    {
        $url = "https://reqbin.com/curl";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        var_dump($resp);
    }
}