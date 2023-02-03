<?php
namespace App\Services\Commands;

use App\Jobs\Bot\SendMessageJob;
use App\Models\BotUser;

class ClearContextService
{
    /**
     * Очищает контекст пользователя
     *
     * @param BotUser $botUser
     */
    public function __construct(BotUser $botUser)
    {
        if (!empty($botUser->context)) {
            $botUser->update(['context' => null]);
            $sResponseMessage = '<i>Контекст успешно очищен</i>';
        }

        SendMessageJob::dispatch([
            'chat_id' => $botUser->chat_id,
            'text' => $sResponseMessage ?? '<i>Контекст пуст</i>',
            'parse_mode' => 'html',
        ]);
    }
}
