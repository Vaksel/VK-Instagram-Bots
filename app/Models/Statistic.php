<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Statistic extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array'
    ];

    // protected $attributes = [
    //     'data' => '{
    //         "deleted_friends": "0",
    //         "accept_followers": "0"
    //     }'
    // ];

    protected $fillable = [
        'data',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    static function update_likes($data_params)
    {
        $currentUserId = auth()->user()->id;

        $record = Statistic::where(['user_id' => $currentUserId, ['created_at', '>', date('Y-m-d H:i:s', time() - 86400)]])->first();

        if (!empty($record))
        {
            foreach ($data_params as $key => $value)
            {
                if(!empty($record->data[$key]))
                {
                    $record->forceFill(["data->{$key}" =>
                        $record->data[$key] + $value])->save();
                }
                else
                {
                    $dataArr = $record->data;
                    $dataArr[$key] = $value;
                    $record->data = $dataArr;
                    $record->save();
                }

            }
        }
        else
        {

            $model = Statistic::create([
                'user_id' => $currentUserId,
                'data' => $data_params,
            ]);
        }
    }
}
