<?php

namespace App\Http\Controllers;

use App\Services\BotActionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    function handleRequest(Request $request, BotActionsService $bot){
        $oMessageData = $request->all();
        $bot->makeAction('sendMessage', [
            'chat_id' => $oMessageData['message']['from']['id'],
            'text' => 'Привет',
        ]);
    }
}
