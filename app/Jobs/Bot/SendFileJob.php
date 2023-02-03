<?php

namespace App\Jobs\Bot;

use App\Traits\Bot\MakeAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MakeAction;

    private $args;

    private string $textToUpload;

    public function __construct(string $textToUpload, array $args)
    {
        $this->args = $args;
        $this->textToUpload = $textToUpload;
    }

    /**
     * Отправляет готовый документ в телеграм
     *
     * @return false|mixed|void
     */
    public function handle()
    {
        $sUserStorageLink = storage_path("app/public/") . $this->args['chat_id'];
        $sUploadFileName = scandir($sUserStorageLink . '/download_files', SCANDIR_SORT_DESCENDING)[0];

        try{
            $response = $this
                ->makeAction('sendDocument', $this->args, [
                    'fileType' => 'document',
                    'contents' =>  $this->textToUpload,
                    'fileName' => $sUploadFileName,
                ])
                ->throw()
                ->json();
            return $response['ok'] ?? false;
        } catch (Throwable $exception){
            Log::debug($exception->getMessage());
        }
    }
}
