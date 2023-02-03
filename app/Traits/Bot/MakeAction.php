<?php

namespace App\Traits\Bot;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait MakeAction
{

    /**
     * Выполняет запрос к Telegram API
     *
     * @param string $name
     * @param array $args
     * @param array $attachData
     * @return Response
     */
    public function makeAction(string $name, array $args, array $attachData = []): Response
    {
        $sApiLink = 'https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . "/$name";

        if (!empty($attachData)) return Http
            ::attach($attachData['fileType'], $attachData['contents'], $attachData['fileName'])
            ->post($sApiLink, $args);

        return Http::post($sApiLink, $args);
    }
}
