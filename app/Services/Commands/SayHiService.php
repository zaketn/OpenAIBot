<?php

namespace App\Services\Commands;

use App\Jobs\Bot\SendMessageJob;

class SayHiService
{
    /**
     * Отправляет приветственное сообщение при команде /start
     *
     * @param int $chatId
     */
    public function __construct(int $chatId)
    {
        SendMessageJob::dispatch([
            'chat_id' => $chatId,
            'text' => "Привет!\nТы пишешь мне сообщения - я отвечаю\nДля того чтобы стереть мне память, используй /clear_context",
            'parse_mode' => 'html',
        ]);
    }
}
