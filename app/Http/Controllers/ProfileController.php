<?php

namespace App\Http\Controllers;

use App\Models\Token;

use Itstructure\GridView\DataProviders\EloquentDataProvider;
use ATehnix\VkClient\Auth as ApiAuth;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\Integer;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('vk_authorization');
    }

    public function tokens()
    {
        $dataProvider = new EloquentDataProvider(Token::query()->select(['id','token_value', 'type', 'active', 'created_at'])->where([
            'user_id' => auth()->user()->id,
        ]));
        
        return view('tokens', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function view_token(Request $request)
    {
        if(!empty($request->id))
        {
            $token_record = Token::find($request->id);

            if(!empty($token_record))
            {
                $res = ['success' => true, 'token' => $token_record->token_value];
            }
            else
            {
                $res = ['success' => false, 'error' => 'неизвестная ошибка сервера'];
            }

            return response()->json($res);
        }

        $res = ['success' => false, 'error' => 'не был указан id токена, переход не с таблицы токенов воспрещен!'];

        return response()->json($res);
    }

    public function edit_token(Request $request)
    {
        if(!empty($request->id))
        {
            $token_record = Token::find($request->id);

            if(!empty($token_record))
            {
                return view('token/edit', compact('token_record'));
            }
            else
            {
                abort(406);
            }
        }

        abort(404);

    }

    public function change_token(Request $request)
    {
        $request->input('id') ? $id = $request->input('id') : abort(404);

        $validatedData = $request->validate([
            'id'            => ['required'],
            'name'          => ['required', 'string', 'max:255'],
            'token_value'   => ['required', 'string', 'max:255'],
            'type'          => ['required', 'boolean'],
            'active'        => ['required', 'boolean']
        ]);

        $token = Token::find($id);

        $changeResult = $token->change($request->all());

        if( !isset($changeResult['errors']) )
        {
            session()->flash('token_change_success', 'Токен был успешно отредактирован');
            return redirect('tokens');
        }
        else
        {
            session()->flash('token_change_fail', "При редактировании токена произошли ошибки: {$changeResult['errors']}");
            return redirect('tokens');
        }
    }

    public function delete_token(Request $request)
    {
        if(!empty($request->id))
        {
            $token = Token::find($request->id);
            $token->delete();

            session()->flash('token_change_success', 'Токен был успешно удален');
            return redirect('tokens');
        }
        else
        {
            session()->flash('token_change_fail', "Токен не был найден, попробуйте снова");
            return redirect('tokens');
        }
    }

    public function add_token(Request $request)
    {
        if($request->isMethod('post'))
        {
            $validatedData = $request->validate([
                'name'          => ['required', 'string', 'max:255'],
                'token_value'   => ['required', 'string', 'max:255'],
                'type'          => ['required', 'boolean'],
                'active'        => ['required', 'boolean']
            ]);

            $token = new Token();
            $token->user_id = auth()->user()->id;
            $token->name = $request->name;
            $token->token_value = $request->token_value;
            $token->type = $request->type;
            $token->active = $request->active;

            $token->save();

            return redirect('tokens');
        }
        else
        {
            return view('token/add-token');
        }
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