<?php

namespace App\Services\Conditions;

use App\Jobs\Bot\DownloadFileJob;
use App\Jobs\Bot\SendMessageJob;
use App\Models\BotUser;
use App\Services\OpenAIService;
use App\Traits\Bot\MakeAction;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RewriteCondition
{
    use MakeAction;
    private BotUser $botUser;
    private array $messageData;
    private int $iStep;
    private int $rewriteIterations;
    private File $handledFile;


    public function __construct(BotUser $botUser, array $messageData)
    {
        $this->botUser = $botUser;
        $this->messageData = $messageData;
        $this->iStep = $botUser->condition_step;

        $AI = new OpenAIService();

        match ($this->botUser->condition_step) {
            0 => $this->fileRequest(),
            1 => $this->downloadFile($messageData),
            2 => $this->generate($messageData, $AI),
        };

        Log::debug($this->botUser);

        $this->botUser->update([
            'condition' => '/rewrite',
            'condition_step' => ++$this->iStep
        ]);
    }

    private function fileRequest()
    {
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => 'Отправьте файл в формате txt.',
        ]);
    }

    private function downloadFile(array $messageData)
    {
        DownloadFileJob::dispatch($this->botUser->chat_id, ['file_id' => $messageData['message']['document']['file_id']]);
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => 'Введите кол-во повторений рерайтинга.',
        ]);
    }

    private function generate(array $messageData, OpenAIService $AI)
    {
        $rewriteIterations = 1;

        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => 'Генерация...',
        ]);

        $AI = new OpenAIService();

        $aUserFiles = scandir(storage_path('app/public/').'437033680', SCANDIR_SORT_DESCENDING);
        $sCurrentFile = file_get_contents(storage_path("app/public/".'437033680'.'/'.$aUserFiles[0]));

        preg_match_all('/(.*)\s/u', $sCurrentFile, $aParagraphs);
        $aParagraphs = $aParagraphs[1];

        foreach(range(1, $rewriteIterations) as $iIteration){
            $sRewritedText = [];
            foreach($aParagraphs as $index => $sParagraph){
                if(!empty(trim($sParagraph))){
                    $sRewritedText[$index] = $AI->generateText("Перепиши текст: \"$sParagraph\"");
                }
            }
            SendMessageJob::dispatch([
                'chat_id' => $this->botUser->chat_id,
                'text' => implode("\n", $sRewritedText)
            ]);
        }
        $this->closeRewright();
    }

    private function closeRewright()
    {
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => 'Я закончил'
        ]);
        $this->botUser->update([
            'condition' => null,
            'condition_step' => 0
        ]);
        exit;
    }
}
