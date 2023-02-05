<?php

namespace App\Services\Commands;

use App\Jobs\Bot\SendMessageJob;

class InfoService
{
    /**
     * Отправляет сообщение с обзором возможностей бота по команде /info
     *
     * @param int $chatId
     */
    public function __construct(int $chatId)
    {
        $sText = <<<END
Бот работает с нейронной сетью GPT-3 от Open AI. При отправке сообщения боту, он выдает сгенерированный ИИ ответ. Каждое сообщение записывается в память(контекст) бота для того, чтобы он был способен вести диалог, а не отвечать на каждое сообщение с чистого листа, например:
Вы: <i>Меня зовут Иван</i>
Бот: <i>Приятно познакомиться, как я могу вам помочь?</i>
Вы: <i>Как меня зовут?</i>
Бот: <i>Иван</i>
Для того чтобы бот очистил свою память и отвечал не основываясь на предыдущей переписке используйте команду /clear_context

У ИИ существует несколько моделей, на основании которых он генерирует ответы, есть более продвинутые, но долгие, есть менее продвинутые, но быстрые.
Для того чтобы получить более подробную информацию по доступным моделям и сменить модель, используйте команду /model.

Также ботом поддерживается рерайтинг текстов. Вы подготавливаете файл в формате .txt, разбив текст на абзацы для того чтобы ИИ мог их обработать, отправляете боту и указываете сколько раз ИИ перепишет текст. По истечении времени, которое зависит от размера текста, бот отправит вам столько переписанных ИИ файлов, сколько раз вы указали чтобы он переписал текст. Текст будет переписан той моделью которая отвечает Вам в режиме чата.
Для запуска рерайтинга используйте /rewrite.

Приятного пользования!
END;
        
        SendMessageJob::dispatch([
            'chat_id' => $chatId,
            'text' => $sText,
            'parse_mode' => 'html',
        ]);
    }
}
