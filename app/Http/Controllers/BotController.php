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

    function test(){
        $rewriteIterations = 1;

        $AI = new OpenAIService();

        $aUserFiles = scandir(storage_path('app/public/').'437033680', SCANDIR_SORT_DESCENDING);
        $sCurrentFile = file_get_contents(storage_path("app/public/".'437033680'.'/'.$aUserFiles[0]));

        $aParagraphs = explode("\n", $sCurrentFile);

        foreach(range(1, $rewriteIterations) as $iIteration){
            $sRewritedText = [];
            foreach($aParagraphs as $index => $sParagraph){
                if(!empty(trim($sParagraph))){
                    $sRewritedText[] = $AI->generateText("Перепиши текст: \"$sParagraph\"");
                }
            }
            Log::debug($sRewritedText);
        }
    }
}
