<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BotActionsService
{
    /**
     * Токен используемого бота
     *
     * @var string
     */
    protected string $botToken;

    /**
     * ID чата с пользователем, которому будут отправляться тестовые сообщения
     *
     * @var int
     */
    public int $debugChatID;

    public function __construct(){
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->debugChatID = env('TELEGRAM_DEBUG_CHAT_ID');
    }

    /**
     * Выполняет запрос к Telegram API
     *
     * @param string $name
     * @param array $args
     * @return Response
     */
    public function makeAction(string $name, array $args = []) : Response
    {
        $sApiLink = 'https://api.telegram.org/bot' . $this->botToken . "/$name";

        return Http::post($sApiLink, $args);
    }

    /**
     * Установливает WebHook для нового бота
     *
     * @return Response|null
     */
    public function setWebhook(){
        return self::makeAction('setWebhook', [
            'url' => route('webhook')
        ]);
    }
}
