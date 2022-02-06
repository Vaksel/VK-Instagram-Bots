<?php

namespace App\Jobs;

use App\Models\IpostX\Limits;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CommentsHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Создать новый экземпляр задания.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('comment_handler');
    }

    /**
     * Выполнить задание.
     *
     * @return void
     */
    public function handle()
    {
        $limit = new Limits();

        $limit->limitHandler();
    }
}