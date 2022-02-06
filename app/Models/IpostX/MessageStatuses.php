<?php


namespace App\Models\IpostX;

use Illuminate\Database\Eloquent;

class MessageStatuses extends Eloquent\Model
{
    public const IS_SENDING = 2;
    public const IN_TREATMENT = 3;
    public const IS_DONE = 4;
    public const IS_TREATMENT_FAIL = 5;
    public const IS_SENDING_FAIL = 6;
    public const IS_CANCEL_TREATMENT = 7;

    public const IS_SENDING_FIRST_COMMENT_FAIL = 8;
    public const IS_SENDING_REPLY_COMMENT_FAIL = 9;
    public const IS_DELETING_COMMENT_FAIL = 10;

}