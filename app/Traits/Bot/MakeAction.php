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
     * @return Response
     */
    public function makeAction(string $name, array $args) : Response
    {
        $sApiLink = 'https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . "/$name";

        return Http::post($sApiLink, $args);
    }
}
