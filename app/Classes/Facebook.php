<?php

namespace App\Classes;

use App\Models\Options;

class Facebook
{
    protected $app_id;
    protected $app_secret;
    protected $access_token;

    /**
     * @param string $access_token
     */
    public function construct(string $access_token)
    {

    }

    protected function getAppSettings()
    {
        $appId = Options::find()->where(['option_name' => 'instagram_app_id'])->first();
        $appSecret = Options::find()->where(['option_name' => 'instagram_app_secret'])->first();

        $this->app_id =
    }
}