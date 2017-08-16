<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('datalist', function () {
            return new \App\Helps\DataList;
        });

        $this->app->singleton('tool', function () {
            return new \App\Helps\Tool;
        });

        $this->app->singleton('translation', function () {
            return new \App\Helps\Translation;
        });
    }
}
