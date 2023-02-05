<?php

namespace App\Services;

use App\Jobs\Bot\SendMessageJob;
use App\Models\BotUser;
use App\Services\Commands\ClearContextService;
use Illuminate\Support\Facades\Log;
use OpenAI;

class OpenAIService
{
    protected OpenAI\Client $AI;
    private BotUser $botUser;

    const DEFAULT_MODEL = 'text-davinci-003';

    public function __construct(BotUser $botUser)
    {
        $this->botUser = $botUser;
        $APIKey = env('OPENAI_API_KEY');
        $this->AI = OpenAI::client($APIKey);
    }

    /**
     * Генерирует сообщение через OpenAI API
     *
     * @param string $correspondingMessage
     * @return string
     */
    public function generateText(string $correspondingMessage): string
    {
        $aModelOptions = config('openai-models.models.' . $this->botUser->model, self::DEFAULT_MODEL);
        $aModelOptions['prompt'] = $correspondingMessage;

        $this->catchContextExceptions($correspondingMessage);

        $response = $this->AI->completions()->create($aModelOptions);

        return $response->choices[0]->text ?? '<i>Что-то пошло не так...</i>';
    }

    /**
     * Сообщает пользователю об ошибках связанных с длинной контекста и убивает скрипт
     *
     * @param string $correspondingMessage
     * @return void
     */
    private function catchContextExceptions(string $correspondingMessage): void
    {
        $sUserSelectedModel = $this->botUser->model;
        $aModelsRestrictions = config('openai-models.restrictions.' . $sUserSelectedModel, self::DEFAULT_MODEL);
        $iModelMaxTokensConfig = config('openai-models.models.' . $sUserSelectedModel, self::DEFAULT_MODEL)['max_tokens'];

        if (strlen($correspondingMessage) + $iModelMaxTokensConfig > $aModelsRestrictions['max_tokens']) {
            if ($this->botUser->condition === '/rewrite') {
                SendMessageJob::dispatch([
                    'chat_id' => $this->botUser->chat_id,
                    'text' => 'Абзацы слишком длинные для рерайтинга выбранной вами моделью(' . $sUserSelectedModel . '). Пожалуйста, разбейте текст на более мелкие абзацы. Максимально допустимая длина абзацев - ' . $aModelsRestrictions['max_tokens'] - $iModelMaxTokensConfig
                ]);
            } else {
                SendMessageJob::dispatch([
                    'chat_id' => $this->botUser->chat_id,
                    'text' => 'Контекст слишком велик для продолжения диалога при выбранном виде модели(' . $sUserSelectedModel . '). Нужно очистить контекст.',
                ]);
            }
            $this->botUser->update([
                'context' => null,
                'condition' => null,
                'condition_step' => 0
            ]);
            die();
        }
    }
}
