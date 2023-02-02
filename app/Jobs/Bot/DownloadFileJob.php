<?php

namespace App\Jobs\Bot;

use App\Traits\Bot\MakeAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class DownloadFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MakeAction;

    private array $args;
    private int $userId;

    public function __construct(int $userId, array $args)
    {
        $this->args = $args;
        $this->userId = $userId;
    }

    public function handle()
    {
        try{
            $sStorageLink = storage_path("app/public/").$this->userId;

            File::cleanDirectory($sStorageLink);

            $response = $this
                ->makeAction('getFile', $this->args)
                ->throw()
                ->json();
            $sFilePath = $response['result']['file_path'];
            $sDownloadLink = 'https://api.telegram.org/file/bot' . env('TELEGRAM_BOT_TOKEN').'/'.$sFilePath;
            $sFile = file_get_contents($sDownloadLink);
            if(!is_dir($sStorageLink)) mkdir($sStorageLink);
            file_put_contents($sStorageLink.'/'.basename($sFilePath), $sFile);
        } catch (Throwable $exception){
            Log::debug($exception->getMessage());
        }
    }
}
