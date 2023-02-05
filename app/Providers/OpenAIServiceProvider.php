<?php

namespace App\Providers;

use App\Models\BotUser;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class OpenAIServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot(Request $request)
    {
        $this->app->singleton(OpenAIService::class, function($app) use($request){
            return new OpenAIService(BotUser::query()->find($request->all()['chat']['id'])->get());
        });
    }
}
