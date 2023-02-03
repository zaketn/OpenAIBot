<?php

namespace App\Jobs\Bot;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\Bot\MakeAction;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MakeAction;

    private array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * Отправляет пользователю сообщение
     *
     * @return false|mixed|void
     */
    public function handle()
    {
        try{
            $response = $this
                ->makeAction('sendMessage', $this->args)
                ->throw()
                ->json();
            return $response['ok'] ?? false;
        } catch (Throwable $exception){
            Log::debug($exception->getMessage());
        }
    }
}
