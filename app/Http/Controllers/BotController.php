<?php

namespace App\Http\Controllers;

use App\Services\BotActionsService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;

class BotController extends Controller
{
    function handleRequest(Request $request, BotActionsService $bot, OpenAIService $AI){
        $bot->init($request, $AI);
    }
}
