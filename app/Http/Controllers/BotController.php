<?php

namespace App\Http\Controllers;

use App\Models\BotUser;
use App\Services\BotActionsService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;

class BotController extends Controller
{
    function handleRequest(Request $request, BotActionsService $bot, OpenAIService $AI){
        $bot->init($request, $AI);

//        $aMessageData = $request->all();
//        $iChatId = (int)$aMessageData['message']['from']['id'];
//
//        $oBotUser = BotUser::find($iChatId);
//        if($oBotUser == null){
//            $oBotUser = BotUser::create([
//                'chat_id' => $iChatId,
//            ]);
//        }
//
//        if($aMessageData['message']['text'] == '/start'){
//            $bot->makeAction('sendMessage', [
//                'chat_id' => $iChatId,
//                'text' => "Привет!\nТы пишешь мне сообщения - я отвечаю\nДля того чтобы стереть мне память, используй /clear_context",
//                'parse_mode' => 'html',
//            ]);
//
//            die();
//        }
//
//        if($aMessageData['message']['text'] == '/clear_context'){
//            $sResponseMessage = '<i>Контекст пуст</i>';
//            if(!empty($oBotUser->context)){
//                $oBotUser->context = null;
//                $oBotUser->save();
//                $sResponseMessage = '<i>Контекст успешно очищен</i>';
//            }
//
//            $bot->makeAction('sendMessage', [
//                'chat_id' => $iChatId,
//                'text' => $sResponseMessage,
//                'parse_mode' => 'html',
//            ]);
//
//            die();
//        }
//
//        $sUserContext = $oBotUser->context;
//        $sContext = !empty($sUserContext) ? $sUserContext : "";
//
//        $sContext = $sContext . 'Human: ' . $aMessageData['message']['text'] . "\nAI: ";
//
//        $generatedText = $AI->generateText($sContext);
//
//        $sContext = $sContext . str_replace("\n", '', $generatedText) . "\n";
//
//        $bot->makeAction('sendMessage', [
//            'chat_id' => $aMessageData['message']['from']['id'],
//            'text' => $generatedText,
//        ]);
//
//        $oBotUser->context = $sContext;
//        $oBotUser->save();
    }
}
