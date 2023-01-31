<?php

namespace App\Jobs\Bot;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
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

    private string $response;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->response = $this->makeAction('sendMessage', $this->args);
    }

    public function failed(Throwable $exception)
    {
        Log::debug($exception->getMessage());
    }
}
