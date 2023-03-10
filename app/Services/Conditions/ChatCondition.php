<?php
namespace App\Services\Conditions;

use App\Jobs\Bot\SendMessageJob;
use App\Models\BotUser;
use App\Services\OpenAIService;
use Illuminate\Http\Request;

/**
 * Логика состояния чата
 */
class ChatCondition
{
    private BotUser $botUser;
    private array $messageData;

    public function __construct(Request $request, BotUser $botUser)
    {
        $this->botUser = $botUser;
        $this->messageData = $request->all();
        if(isset($this->messageData['message']['text'])) $this->generateText(new OpenAIService($botUser));
    }

    /**
     * Генерация ответа для пользователя
     *
     * @param OpenAIService $AI
     * @return void
     */
    private function generateText(OpenAIService $AI): void
    {
        $sContext = $this->getCurrentContext();
        $generatedText = $AI->generateText($sContext);

        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => $generatedText,
        ]);

        $this->contextUpdate($sContext, $generatedText);
    }

    /**
     * Получает текущий контекст
     *
     * @return string
     */
    private function getCurrentContext(): string
    {
        $sUserContext = $this->botUser->context;
        $sContext = !empty($sUserContext) ? $sUserContext : "";
        return $sContext . 'Human: ' . $this->messageData['message']['text'] . "\nAI: ";
    }

    /**
     * Записывает в контекст сгенерированный ответ
     *
     * @param string $context
     * @param string $generatedText
     * @return void
     */
    private function contextUpdate(string $context, string $generatedText): void
    {
        $sContext = $context . str_replace("\n", '', $generatedText) . "\n";
        $this->botUser->context = $sContext;
        $this->botUser->save();
    }
}
