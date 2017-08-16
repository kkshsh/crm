<?php

namespace App\Modules\Channel;

use Illuminate\Support\ServiceProvider;

class  ChannelServiceProvider extends ServiceProvider
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
        $this->app->singleton('channel', function () {
            return new ChannelModule();
        });
    }
}
