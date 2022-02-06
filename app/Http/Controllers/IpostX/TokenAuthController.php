<?php

namespace App\Http\Controllers\IpostX;

use App\Http\Controllers\Controller;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\IpostX\AuthTokens;

class TokenAuthController extends Controller
{
    public function index(Request $request)
    {

        $request->validate([
            'instagramIpostxAuthToken' => 'required',
            'facebookAccessToken' => 'required',
            'instagramUserToken' => 'required',
            'instagramProfileToken' => 'required',
        ]);

        $checkingRes = $this->checkIsAuthTokensExistAndRefreshThemIfExist($request);

        if(!$checkingRes['status'])
        {
            $writeTokens = $this->writeTokensToModel($request);

            $responseMsg[] = $writeTokens['status'];
            $responseMsg[] = $writeTokens['msg'];
        }
        else
        {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => $writeTokens['status']], $writeTokens['code']);
    }

    protected function checkIsAuthTokensExistAndRefreshThemIfExist($request)
    {
        $instAuthToken = new AuthTokens();
        $instAuthToken->name = 'instagramIpostxAuthToken';
        $instAuthToken->value = $request->instagramIpostxAuthToken;

        Log::write('debug', 'accessToken', [$request->instagramIpostxAuthToken]);


        $isAuthTokenValid = $instAuthToken->validateAuthToken();

        if($isAuthTokenValid)
        {
            $tokens = AuthTokens::where(['uId' => $instAuthToken->uId])->get();

            if(!empty($tokens))
            {
                $this->refreshTokens($tokens, $request);

                return ['status' => true, 'msg' => 'Well done, your tokens refreshed!','code' => 200];
            }
            else
            {
                return ['status' => false, 'msg' => 'Tokens is empty','code' => 404];
            }
        }

        return ['status' => false, 'msg' => 'Token not valid','code' => 401];
    }

    protected function refreshTokens($tokensOld, $requestWithTokens)
    {
        foreach ($tokensOld as $token)
        {
            if($token->name === 'facebookAccessToken')
            {
                $token->value = $requestWithTokens->facebookAccessToken;
            }

            if($token->name === 'instagramUserToken')
            {
                $token->value = $requestWithTokens->instagramUserToken;
            }

            if($token->name === 'instagramProfileToken')
            {
                $token->value = $requestWithTokens->instagramProfileToken;
            }

            $token->save();
        }
    }

    protected function writeTokensToModel($request)
    {
        $instAuthToken = new AuthTokens();
        $instAuthToken->name = 'instagramIpostxAuthToken';
        $instAuthToken->value = $request->instagramIpostxAuthToken;

        Log::write('debug', 'accessToken', [$request->instagramIpostxAuthToken]);


        $isAuthTokenValid = $instAuthToken->validateAuthToken();

        if($isAuthTokenValid)
        {
            $instAuthToken->save();

            $fbAccessToken = new AuthTokens();
            $fbAccessToken->uId = $instAuthToken->uId;
            $fbAccessToken->name = 'facebookAccessToken';
            $fbAccessToken->value = $request->facebookAccessToken;
            $fbAccessToken->save();

            $instUserToken = new AuthTokens();
            $instUserToken->uId = $instAuthToken->uId;
            $instUserToken->name = 'instagramUserToken';
            $instUserToken->value = $request->instagramUserToken;
            $instUserToken->save();

            $instProfileToken = new AuthTokens();
            $instProfileToken->uId = $instAuthToken->uId;
            $instProfileToken->name = 'instagramProfileToken';
            $instProfileToken->value = $request->instagramProfileToken;
            $instProfileToken->save();
        }
        else
        {
            return ['status' => 'error', 'msg' => 'Token not valid','code' => 401];
        }

        return ['status' => 'success', 'msg' => 'Well done, your register successful!','code' => 200];
    }
}