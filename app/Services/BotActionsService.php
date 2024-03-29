<?php

namespace App\Services;

use App\Models\BotUser;
use Illuminate\Http\Request;
use App\Traits\Bot\MakeAction;
use App\Services\Conditions\ChatCondition;
use App\Services\Conditions\RewriteCondition;
use App\Services\Commands\SayHiService;
use App\Services\Commands\ClearContextService;
use App\Services\Conditions\ModelCondition;
use App\Services\Commands\InfoService;
use Illuminate\Support\Facades\Log;


class BotActionsService
{
    use MakeAction;

    protected string $botToken;

    protected BotUser $botUser;

    protected array $messageData;

    protected int $chatId;

    public function init(Request $request, OpenAIService $AI): void
    {
        Log::debug($request);



        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->debugChatID = env('TELEGRAM_DEBUG_CHAT_ID');
        $this->messageData = $request->all();

        $this->chatId = isset($this->messageData['message']) ? (int)$this->messageData['message']['from']['id'] : (int)$this->messageData['edited_message']['from']['id'];
        $this->botUser = $this->getOrCreateUser($this->chatId);

        $this->handleMessage($request);
    }

    /**
     * Возвращает существующего пользователя или создает нового
     *
     * @param int $iChatId
     * @return BotUser
     */
    private function getOrCreateUser(int $iChatId): BotUser
    {
        return BotUser::query()->firstOrCreate(
            ['chat_id' => $iChatId],
            [
                'chat_id' => $iChatId,
                'condition_step' => 0,
                'model' => 'text-davinci-003'
            ]
        );
    }

    /**
     * Выбирает обработчик в соответствии с сообщением пользователя
     *
     * @param Request $request
     * @return void
     */
    private function handleMessage(Request $request) : void
    {
        $commands = [
            '/start', '/clear_context', '/rewrite', '/model', '/info',
        ];

        $condition = null;
        if (isset($this->messageData['message']['text']) && in_array($this->messageData['message']['text'], $commands)) {
            $condition = $this->messageData['message']['text'];
        } else if (in_array($this->botUser->condition, $commands)) {
            $condition = $this->botUser->condition;
        }

        if (!empty($condition)) {
            match ($condition) {
                '/start', '/info' => new InfoService($this->chatId),
                '/clear_context' => new ClearContextService($this->botUser),
                '/rewrite' => new RewriteCondition($this->botUser, $this->messageData),
                '/model' => new ModelCondition($this->botUser, $this->messageData),
                default => new ChatCondition($request, $this->botUser)
            };
        } else {
            new ChatCondition($request, $this->botUser);
        }
    }
}
