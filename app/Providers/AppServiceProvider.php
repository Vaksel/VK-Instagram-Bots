<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('selectedWhen', function ($parameters) {
            $explodeRes = explode(',', $parameters);
//            ddd($parameters);
            $value = $explodeRes[0];
            $expected = $explodeRes[1];
            $value = trim($value, '\'');
            return "value=\"$value\" <?php if ('$value' == $expected) {
                echo 'selected';
            }?>";
        });
    }
}
