<?php

namespace App\Providers;

use App\Services\BotActionsService;
use Illuminate\Support\ServiceProvider;

class BotActionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BotActionsService::class, function($app){
           return new BotActionsService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
