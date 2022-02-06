<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Token extends Model
{
    use HasFactory;

    public const USER_TOKEN = 0;
    public const COMMUNITY_TOKEN = 1;

    protected $guarded = [];

    protected function getRecordBy($token_search_params)
    {
        return $this::find()->where($token_search_params)->one();
    }

    public function getAllRecords()
    {
        return $this::find()->all();
    }

    public function change($params)
    {
        foreach ($params as $key => $value)
        {
            $this->setTokenFields($key, $value);
        }
        
        if($this->save())
        {
            return true;
        }
        else
        {
            return [false, 'errors' => $this->getErrors()];
        }
    }

    protected function setTokenFields($key, $value)
    {
        switch($key)
        {
            case 'token_value' : {
                $this->token_value = $value;
                break;
            }
            case 'name' : {
                $this->name = $value;
                break;
            }
            case 'type' : {
                $this->type = $value;
                break;
            }
            case 'active' : {
                $this->active = $value;
                break;
            }
            default : {
                break;
            }
        }
    }
}
