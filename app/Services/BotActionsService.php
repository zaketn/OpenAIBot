<?php

namespace App\Services;

use App\Models\BotUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Jobs\Bot\SendMessageJob;
use App\Traits\Bot\MakeAction;
use App\Services\Conditions\ChatCondition;
use App\Services\Conditions\RewriteCondition;
use App\Services\Commands\SayHiService;
use App\Services\Commands\ClearContextService;
use Illuminate\Support\Facades\Log;


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
        Log::debug('Создан пользователь ' . $iChatId);
        return BotUser::query()->firstOrCreate(
            ['chat_id' => $iChatId],
            [
                'chat_id' => $iChatId,
                'condition' => 'start',
                'condition_step' => 0,
            ]
        );
    }

    private function handleMessage(Request $request)
    {
        $commands = [
            '/start', '/clear_context', '/rewrite'
        ];

        $condition = null;
        if(isset($this->messageData['message']['text']) && in_array($this->messageData['message']['text'], $commands)){
            $condition = $this->messageData['message']['text'];
        } else if(in_array($this->botUser->condition, $commands)){
            $condition = $this->botUser->condition;
        }

        if(!empty($condition)){
            return match ($condition) {
                '/start' => new SayHiService($this->chatId),
                '/clear_context' => new ClearContextService($this->botUser),
                '/rewrite' => new RewriteCondition($this->botUser, $this->messageData),
                default => new ChatCondition($request, $this->botUser)
            };
        }
        else {
            return new ChatCondition($request, $this->botUser);
        }
    }
}
