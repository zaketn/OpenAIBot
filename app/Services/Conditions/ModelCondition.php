<?php

namespace App\Services\Conditions;

use App\Jobs\Bot\SendMessageJob;
use App\Models\BotUser;

class ModelCondition
{
    private BotUser $botUser;
    private array $messageData;
    private array $aKeyboard;
    private int $iStep;

    public function __construct(BotUser $botUser, array $messageData)
    {
        $this->botUser = $botUser;
        $this->messageData = $messageData;
        $this->iStep = $this->botUser->condition_step;
        $this->aKeyboard = [
            'keyboard' => [
                [
                    ['text' => 'text-davinci-003'], ['text' => 'text-curie-001']
                ],
                [
                    ['text' => 'text-babbage-001'], ['text' => 'text-ada-001']
                ]
            ]
        ];

        match ($this->botUser->condition_step) {
            0 => $this->showButtons(),
            1 => $this->changeModel(),
        };

        $this->botUser->update([
            'condition' => '/model',
            'condition_step' => ++$this->iStep,
        ]);
    }

    private function showButtons(): void
    {
        $sUserModel = $this->botUser->model;
        $sText = <<<END
<strong>Информация о каждой модели:</strong>

<strong>text-davinci-003</strong>: Самая мощная модель GPT-3. Может выполнять любую задачу, которую могут выполнять другие модели, часто с более высоким качеством, более длительным выходом и лучшим выполнением инструкций
Лучше всех работает с русским языком, как результат такой мощности - самое долгое время исполнения.
Хорошо умеет: сложное намерение, причина и следствие, подведение итогов для аудитории.

<strong>text-curie-001</strong>: Очень способный, но быстрее, чем Davinci.
Сurie чрезвычайно мощный, но очень быстрый. В то время как Davinci сильнее, когда дело доходит до анализа сложного текста, Сurie вполне способен решать многие тонкие задачи, такие как классификация настроений и обобщение. Сurie также неплохо отвечает на вопросы и выполняет вопросы и ответы, а также работает в качестве чат-бота общего назначения.
Хорошо умеет: языковой перевод, сложная классификация, тональность текста, обобщение.

<strong>text-babbage-001</strong>: Способен решать простые задачи, очень быстро.
Babbage может выполнять простые задачи, такие как простая классификация. Он также вполне способен, когда дело доходит до ранжирования семантического поиска, насколько хорошо документы соответствуют поисковым запросам.
Хорошо умеет: умеренная классификация, классификация семантического поиска.

<strong>text-ada-001</strong>: Способен выполнять очень простые задачи, обычно это самая быстрая модель в серии GPT-3.
Ada обычно является самой быстрой моделью и может выполнять такие задачи, как синтаксический анализ текста, исправление адреса и определенные виды задач классификации, которые не требуют особых нюансов. Производительность Ada часто можно улучшить, предоставив больше контекста.
Хорошо умеет: парсинг текста, простая классификация, исправление адресов, ключевые слова.

Все модели кроме Davinci более ограничены в количестве контекста, рассчитывается он по такой формуле:
<i>(Размер контекста) + 1024 = (предел модели)</i>
У Davinci предел равен 4096 символам, у остальных - 2048.

Текущая модель: $sUserModel
END;

        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => $sText,
            'parse_mode' => 'html',
            'reply_markup' => $this->aKeyboard,
            'one_time_keyboard' => true,
        ]);
    }

    private function changeModel(): void
    {

        if ($this->checkIfKeyExists($this->messageData['message']['text'])) {
            $this->botUser->update([
                'model' => $this->messageData['message']['text']
            ]);
            $this->closeModel();
        } else {
            SendMessageJob::dispatch([
                'chat_id' => $this->botUser->chat_id,
                'text' => 'Выберите модель',
            ]);
            exit;
        }
    }

    private function checkIfKeyExists(string $search) : bool
    {
        foreach($this->aKeyboard['keyboard'] as $aRow){
            foreach($aRow as $aButton){
                if($aButton['text'] === $search) return true;
            }
        }
        return false;
    }

    private function closeModel(): void
    {
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => '<i>Модель успешно заменена</i>',
            'reply_markup' => ['remove_keyboard' => true],
            'parse_mode' => 'html',
        ]);
        $this->botUser->resetCondition();
        exit;
    }
}
