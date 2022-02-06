<?php

namespace App\Http\Middleware;

use App\Models\IpostX\AuthTokens;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckMessageValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $uId = AuthTokens::validateAuthTokenAndReturnUserId($request->instAuthToken);

        if(empty($uId))
        {
            return response('Wrong token',401);
        }

        $request->request->add(['userId' => $uId]);

        return $next($request);
    }
}
