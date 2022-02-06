<?php

namespace App\Jobs;

use App\Models\IpostX\Limits;
use App\Models\IpostX\MessageFromDB;

use App\Models\IpostX\Storages\MessageStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CommentTreatment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;

    /**
     * Создать новый экземпляр задания.
     *
     * @param $message
     */
    public function __construct($message)
    {
        $this->onQueue('comment_treatment');
        $this->message = $message;
    }

    /**
     * Выполнить задание.
     *
     * @return void
     */
    public function handle()
    {
        $this->message = new MessageStorage($this->message);

        $commentIsInTreatment = $this->message->checkMessageTreatmentStatus();

        Log::write('debug', 'commentIsInTreatment', ['commentIsInTreatment' => $commentIsInTreatment]);

        if($commentIsInTreatment)
        {
            $limitIsAchieved = $this->message->checkMessageLimits();

            if(!$limitIsAchieved)
            {
                if($commentTreatmentIsSuccessful = $this->message->startCommentTreatment())
                {
                    $this->message->commentsIsDone();
                }
            }
            else
            {
                Limits::insertCommentMessageToTailOfQueue($this->message->getJsonFormattedComment());
            }
        }
    }
}