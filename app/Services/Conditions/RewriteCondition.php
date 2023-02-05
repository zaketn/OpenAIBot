<?php

namespace App\Services\Conditions;

use App\Jobs\Bot\DownloadFileJob;
use App\Jobs\Bot\SendMessageJob;
use App\Jobs\Bot\SendFileJob;
use App\Jobs\Bot\RewriteTextJob;
use App\Models\BotUser;
use App\Services\OpenAIService;
use App\Traits\Bot\MakeAction;
use Illuminate\Http\File;

/**
 * Состояние рерайтинга
 */
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

        $this->validateFields((int)$this->botUser->condition_step, $messageData);

        if (!$this->botUser->is_started) {
            $this->messageData = $messageData;
            $this->iStep = $botUser->condition_step;

            match ($this->botUser->condition_step) {
                0 => $this->fileRequest(),
                1 => $this->downloadFile($messageData),
                2 => $this->generate(),
            };

            $this->botUser->update([
                'condition' => '/rewrite',
                'condition_step' => ++$this->iStep,
                'is_started' => true,
            ]);
        }
    }

    /**
     * Валидирует ответ пользователя в соответствии с шагом
     *
     * @param int $step
     * @param array $messageData
     * @return void
     */
    private function validateFields(int $step, array $messageData) : void
    {
        switch ($step) {
            case 1:
                if (!isset($messageData['message']['document']['file_id'])) {
                    SendMessageJob::dispatch([
                        'chat_id' => $this->botUser->chat_id,
                        'text' => 'Вы должны отправить файл',
                    ]);
                    die();
                }
                break;
            case 2:
                if (!is_numeric($messageData['message']['text']) && (int)$messageData['message']['text'] <= 0) {
                    SendMessageJob::dispatch([
                        'chat_id' => $this->botUser->chat_id,
                        'text' => 'Отправьте простое натуральное число',
                    ]);
                    die();
                }
                break;
        }
    }

    /**
     * Отправляет сообщение с запросом файла
     *
     * @return void
     */
    private function fileRequest(): void
    {
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => 'Отправьте файл в формате txt.',
        ]);
    }

    /**
     * Скачивает отправленный пользователем файл
     *
     * @param array $messageData
     * @return void
     */
    private function downloadFile(array $messageData): void
    {
        DownloadFileJob::dispatch($this->botUser->chat_id, ['file_id' => $messageData['message']['document']['file_id']]);
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => 'Введите кол-во повторений рерайтинга.',
        ]);
    }

    /**
     * Переписывает отправленный текст
     *
     * @return void
     */
    private function generate(): void
    {
        if ($this->messageData['message']['text'] === $this->botUser->context) die();

        $this->botUser->update([
            'context' => $this->messageData['message']['text']
        ]);

        $rewriteIterations = $this->messageData['message']['text'];

        $AI = new OpenAIService($this->botUser);

        $aUserFiles = scandir(storage_path('app/public/') . $this->botUser->chat_id . '/download_files', SCANDIR_SORT_DESCENDING);
        $sCurrentFile = file_get_contents(storage_path("app/public/" . $this->botUser->chat_id . '/download_files/' . $aUserFiles[0]));

        RewriteTextJob::dispatch($rewriteIterations, $sCurrentFile, $this->botUser);

//        preg_match_all('/(.*)\s/u', $sCurrentFile, $aParagraphs);
//        $aParagraphs = $aParagraphs[1];
//
//        foreach (range(1, $rewriteIterations) as $iIteration) {
//            $sRewritedText = [];
//            SendMessageJob::dispatch([
//                'chat_id' => $this->botUser->chat_id,
//                'text' => "<i>Генерация $iIteration копии</i>",
//                'parse_mode' => 'html',
//            ]);
//            foreach ($aParagraphs as $index => $sParagraph) {
//                if (!empty(trim($sParagraph))) {
//                    if (str_contains($sParagraph, '{{'))
//                        $sRewritedText[$index] = str_replace(['{{', '}}'], '', $sParagraph);
//                    else
//                        $sRewritedText[$index] = $AI->generateText("Перепиши текст: \"$sParagraph\"");
//                }
//            }
//            SendFileJob::dispatch(
//                implode("\n", $sRewritedText), [
//                    'chat_id' => $this->botUser->chat_id,
//                ]
//            );
//        }
        $this->closeRewright();
    }

    /**
     * Убирает у пользователя состояние рерайтинга, возвращает в режим чата
     *
     * @return void
     */
    private function closeRewright(): void
    {
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => '<i>Генерация закончена</i>',
            'parse_mode' => 'html',
        ]);
        $this->botUser->update([
            'condition' => null,
            'condition_step' => 0,
            'is_started' => false,
            'context' => null,
        ]);
        exit;
    }
}
