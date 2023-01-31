<?php
namespace App\Services;

use App\Models\BotUser;
use Illuminate\Http\Request;
use App\Jobs\Bot\SendMessageJob;
use App\Traits\Bot\MakeAction;

class BotActionsService
{
    use MakeAction;
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
        if (!empty($this->botUser->context)) {
            $this->botUser->update(['context' => null]);
            $sResponseMessage = '<i>Контекст успешно очищен</i>';
        }

        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => $sResponseMessage ?? '<i>Контекст пуст</i>',
            'parse_mode' => 'html',
        ]);
    }

    private function sayHi() : void
    {
        SendMessageJob::dispatch([
            'chat_id' => $this->chatId,
            'text' => "Привет!\nТы пишешь мне сообщения - я отвечаю\nДля того чтобы стереть мне память, используй /clear_context",
            'parse_mode' => 'html',
        ]);
    }

    private function generateText(OpenAIService $AI) : void
    {
        $sContext = $this->getCurrentContext();
        $generatedText = $AI->generateText($sContext);

        SendMessageJob::dispatch([
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
