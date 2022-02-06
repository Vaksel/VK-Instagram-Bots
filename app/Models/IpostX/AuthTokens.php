<?php

namespace App\Models\IpostX;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\IpostX\Options;
use Illuminate\Support\Facades\Log;

class AuthTokens extends Model
{
    use HasFactory;
    protected $table = 'user_tokens';

    public function validateAuthToken()
    {
        if($this->name === 'instagramIpostxAuthToken')
        {
            $token = $this->value;

            $userId = '';
            $sourceKey = '';

            //5022-1 bf72-2 e7b0-3 d7c0-4 33a8-5 bb30-6 aeb5-7 8078-8

            //50221bf722e7b03d7c0433a85bb306aeb5780788

            for($i = 0; $i < 8; $i++)
            {
                $tokenPart = substr($token, 5 * $i, 5);

                $sourceKey .= substr($tokenPart, -1, 1);

                $userId .= substr($tokenPart, 0, 4);
            }


            $RequestToken = Options::where(['option_name' => 'auth_source_key'])->first();


            if(!empty($RequestToken->option_value))
            {
                if($RequestToken->option_value === $sourceKey)
                {
                    $this->uId = $userId;
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * @param $token
     * @return false|string
     */
    public static function validateAuthTokenAndReturnUserId($token)
    {
            $userId = '';
            $sourceKey = '';

            for($i = 0; $i < 8; $i++)
            {
                $tokenPart = substr($token, 5 * $i, 5);

                $sourceKey .= substr($tokenPart, -1, 1);

                $userId .= substr($tokenPart, 0, 4);
            }



        $RequestToken = Options::where(['option_name' => 'auth_source_key'])->first();


        if(!empty($RequestToken->option_value))
            {
                if($RequestToken->option_value === $sourceKey)
                {
                    return $userId;
                }
            }

            return false;
    }
}
