<?php

namespace App\Jobs\Bot;

use App\Jobs\Bot\SendMessageJob;
use App\Models\BotUser;
use App\Services\OpenAIService;
use App\Traits\Bot\MakeAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RewriteTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MakeAction;

    private int $rewriteIterations;

    private string $sCurrentFile;

    private BotUser $botUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $rewriteIterations, string $sCurrentFile, BotUser $botUser)
    {
        $this->rewriteIterations = $rewriteIterations;
        $this->sCurrentFile = $sCurrentFile;
        $this->botUser = $botUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        preg_match_all('/(.*)\s/u', $this->sCurrentFile, $aParagraphs);
        $aParagraphs = $aParagraphs[1];

        foreach (range(1, $this->rewriteIterations) as $iIteration) {
            $AI = new OpenAIService($this->botUser);
            $sRewritedText = [];
            SendMessageJob::dispatch([
                'chat_id' => $this->botUser->chat_id,
                'text' => "<i>Генерация $iIteration копии</i>",
                'parse_mode' => 'html',
            ]);
            foreach ($aParagraphs as $index => $sParagraph) {
                if (!empty(trim($sParagraph))) {
                    if (str_contains($sParagraph, '{{'))
                        $sRewritedText[$index] = str_replace(['{{', '}}'], '', $sParagraph);
                    else
                        if(isset($sParagraph)){
                            $sRewritedText[$index] = $AI->generateText("Перепиши текст: \"$sParagraph\"");
                        }
                }
            }
            SendFileJob::dispatch(
                implode("\n", $sRewritedText), [
                    'chat_id' => $this->botUser->chat_id,
                ]
            );
        }
    }

    public function failed(Throwable $exception)
    {
        SendMessageJob::dispatch([
            'chat_id' => $this->botUser->chat_id,
            'text' => 'Возникла ошибка. Отмена рерайтинга.',
        ]);

        SendMessageJob::dispatch([
            'chat_id' => env('TELEGRAM_DEBUG_CHAT_ID'),
            'text' => 'Ошибка при рерайтинге.\n' . $exception->getMessage(),
        ]);

        $this->botUser->condition = null;
        $this->botUser->condition_step = 0;
        $this->botUser->save();
    }
}
