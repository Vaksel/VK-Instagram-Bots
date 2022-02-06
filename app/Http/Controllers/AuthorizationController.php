<?php

namespace App\Http\Controllers;

use App\Models\Token;
use http\Env\Response;
use Illuminate\Http\Request;
use ATehnix\VkClient\Auth as ApiAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\Integer;

class AuthorizationController extends Controller
{

    public function index() {
        return view('vk_authorization');
    }

    public function testVerstka()
    {
        return view('verstka');
    }

    public function getAndWriteAccessToken()
    {
//        $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//
//        ddd(parse_url($url));
//        $vk_app_id = htmlspecialchars($_COOKIE["vk_app_id"]);
//        $vk_app_secret = htmlspecialchars($_COOKIE["vk_app_secret"]);
//        $vk_token_name = htmlspecialchars($_COOKIE["vk_token_name"]);
//        $vk_app_scope = htmlspecialchars($_COOKIE["vk_app_scope"]);
//        $vk_token_type = htmlspecialchars($_COOKIE["vk_token_type"]);

//        $url = 'https://oauth.vk.com/access_token?client_id='.$vk_app_id.'&client_secret='.$vk_app_secret.'&scope'.$vk_app_scope.'&code='.$_REQUEST['code'].'&redirect_uri=http://'.$_SERVER['SERVER_NAME'].'/authorize';
//        $result = file_get_contents($url);
//        $result = json_decode($result, true);
//        $access_token = $result['access_token'];

//        $auth = new ApiAuth(7762245, 'R3MrxJRukGrCoyxTpqfQ', 'http://'.$_SERVER['SERVER_NAME'].'/authorize', 'friends');
////
////        echo "<a href='{$auth->getUrl()}'>ClickMe<a>";
//
//        $token = $auth->getToken($_GET['code']);
//
//        ddd($token);

//        $user_id = Auth::user()->getAuthIdentifier();
//
//        if($this->writeAccessTokenToDb($access_token, $vk_token_name, $user_id, $vk_token_type)) {
//            session()->flash('status', 'Токен успешно добавлен');
//            return redirect('/home');
//        } else {
//            session()->flash('status', 'Токен не был добавлен, попробуйте снова');
//            return redirect('/authorization');
//        }

        session()->flash('sendtoken', true);

        return view('vk_authorization');
    }

    public function getTokenFromUrl(Request $request)
    {
        $token = $request->data['url'];
        return Response($token);
    }

    /*
     * $token_type - токен пользователя или же токен сообщества
     */

    protected function writeAccessTokenToDb($access_token, $token_name,$user_id, $token_type)
    {
        try
        {
            if($access_token && $user_id) {

                $tokenRecord = new Token();

                $tokenRecord->token_value = $access_token;
                $tokenRecord->name = $token_name ? $token_name : md5(time());
                $tokenRecord->user_id = $user_id;
                $tokenRecord->type = $token_type;
                $tokenRecord->active = false;

                $tokenRecord->save();
            }
        }
        catch (\Exception $e) {
            return $e;
        }

        return true;
    }
}
