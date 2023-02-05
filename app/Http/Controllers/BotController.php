<?php

namespace App\Http\Controllers;

use App\Jobs\Bot\SendMessageJob;
use App\Services\BotActionsService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    function handleRequest(Request $request, BotActionsService $bot, OpenAIService $AI)
    {
        $bot->init($request, $AI);
    }
}
