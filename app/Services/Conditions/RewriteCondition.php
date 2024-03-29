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
        $sText = <<<END
<strong>Отправьте файл в формате txt.</strong>

Файл должен быть разбит на небольшие абзацы для того чтобы ИИ мог их обработать, абзацы которые не должны быть переписаны нужно заключить в две фигурные скобки, например {{ Заголовок }}.
END;


        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => $sText,
            'parse_mode' => 'html'
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

        $aUserFiles = scandir(storage_path('app/public/') . $this->botUser->chat_id . '/download_files', SCANDIR_SORT_DESCENDING);
        $sCurrentFile = file_get_contents(storage_path("app/public/" . $this->botUser->chat_id . '/download_files/' . $aUserFiles[0]));

        RewriteTextJob::dispatch($rewriteIterations, $sCurrentFile, $this->botUser);

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
