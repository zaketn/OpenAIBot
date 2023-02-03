<?php

namespace App\Services;

use OpenAI;

class OpenAIService
{
    protected OpenAI\Client $AI;

    public function __construct()
    {
        $APIKey = env('OPENAI_API_KEY');
        $this->AI = OpenAI::client($APIKey);
    }

    /**
     * Генерирует сообщение через OpenAI API
     *
     * @param string $correspondingMessage
     * @return string
     */
    public function generateText(string $correspondingMessage) : string
    {
        $response = $this->AI->completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => $correspondingMessage,
            'max_tokens' => 1024,
            'n' => 1,
            'temperature' => 0.6,
            'stop' => ['AI: ', 'Human: '],
        ]);

        return $response->choices[0]->text ?? '<i>Что-то пошло не так...</i>';
    }
}
