<?php

namespace App\Http\Controllers\IpostX;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

use App\Models\IpostX\AuthTokens;
use App\Jobs\CommentsHandler;


class ServiceController extends Controller
{
    public function startLimitQueue()
    {
        Redis::set('ipostxvk_database_limitHandlerFlag', 1);

        sleep(5);

        if(Redis::del('ipostxvk_database_limitHandlerFlag'))
        {
            $commentHandler = new CommentsHandler();
            $this->dispatch($commentHandler);

            return 'Лимитная очередь успешно запущена!';
        }
    }
}
