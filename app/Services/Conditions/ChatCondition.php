<?php
namespace App\Services\Conditions;

use App\Jobs\Bot\SendMessageJob;
use App\Models\BotUser;
use App\Services\OpenAIService;
use Illuminate\Http\Request;

class ChatCondition
{
    private BotUser $botUser;
    private array $messageData;

    public function __construct(Request $request, BotUser $botUser)
    {
        $this->botUser = $botUser;
        $this->messageData = $request->all();
        if(isset($this->messageData['message']['text'])) $this->generateText(new OpenAIService());
    }

    private function generateText(OpenAIService $AI): void
    {
        $sContext = $this->getCurrentContext();
        $generatedText = $AI->generateText($sContext);

        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => $generatedText,
        ]);

        $this->contextUpdate($sContext, $generatedText);
    }

    private function getCurrentContext(): string
    {
        $sUserContext = $this->botUser->context;
        $sContext = !empty($sUserContext) ? $sUserContext : "";
        return $sContext . 'Human: ' . $this->messageData['message']['text'] . "\nAI: ";
    }

    private function contextUpdate(string $context, string $generatedText): void
    {
        $sContext = $context . str_replace("\n", '', $generatedText) . "\n";
        $this->botUser->context = $sContext;
        $this->botUser->save();
    }
}
