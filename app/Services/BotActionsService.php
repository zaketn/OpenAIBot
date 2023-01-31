<?php
//declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\OpenAIService;

class BotActionsService
{
    /**
     * Токен используемого бота
     *
     * @var string
     */
    protected string $botToken;

    protected BotUser $botUser;

    protected array $messageData;

    protected int $chatId;

    public function init(Request $request, OpenAIService $AI)
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->debugChatID = env('TELEGRAM_DEBUG_CHAT_ID');
        $this->messageData = $request->all();

        $this->chatId = (int)$this->messageData['message']['from']['id'];
        $this->botUser = $this->getOrCreateUser($this->chatId);

        $this->handleMessage($AI);
    }

    /**
     * Выполняет запрос к Telegram API
     *
     * @param string $name
     * @param array $args
     * @return Response
     */
    private function makeAction(string $name, array $args = []): Response
    {
        $sApiLink = 'https://api.telegram.org/bot' . $this->botToken . "/$name";

        return Http::post($sApiLink, $args);
    }

    /**
     * Возвращает существующего пользователя или создает нового
     *
     * @param int $iChatId
     * @return BotUser
     */
    private function getOrCreateUser(int $iChatId): BotUser
    {
        return BotUser::query()->firstOrCreate(['chat_id' => $iChatId], ['chat_id' => $iChatId]);
    }

    private function handleMessage(OpenAIService $AI): void
    {
        match ($this->messageData['message']['text']) {
            '/start' => $this->sayHi(),
            '/clear_context' => $this->clearContext(),
            default => $this->generateText($AI)
        };
    }

    private function clearContext(): void
    {
        $sResponseMessage = '<i>Контекст пуст</i>';
        if (!empty($oBotUser->context)) {
            $oBotUser->context = null;
            $oBotUser->save();
            $sResponseMessage = '<i>Контекст успешно очищен</i>';
        }

        $this->makeAction('sendMessage', [
            'chat_id' => $this->botUser->chat_id,
            'text' => $sResponseMessage,
            'parse_mode' => 'html',
        ]);
    }

    private function sayHi() : void
    {
        $this->makeAction('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => "Привет!\nТы пишешь мне сообщения - я отвечаю\nДля того чтобы стереть мне память, используй /clear_context",
            'parse_mode' => 'html',
        ]);
    }

    private function generateText(OpenAIService $AI) : void
    {
        $this->makeAction('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => 'Начинается генерация',
        ]);

        $sContext = $this->getCurrentContext();
        $generatedText = $AI->generateText($sContext);

        $this->makeAction('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => $generatedText,
        ]);

        $this->contextUpdate($sContext, $generatedText);
    }

    private function getCurrentContext() : string
    {
        $sUserContext = $this->botUser->context;
        $sContext = !empty($sUserContext) ? $sUserContext : "";
        return $sContext . 'Human: ' . $this->messageData['message']['text'] . "\nAI: ";
    }

    private function contextUpdate(string $context, string $generatedText) : void
    {
        $sContext = $context . str_replace("\n", '', $generatedText) . "\n";
        $this->botUser->context = $sContext;
        $this->botUser->save();
    }
}
