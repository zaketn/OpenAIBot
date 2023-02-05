<?php

namespace App\Jobs\Bot;

use App\Traits\Bot\MakeAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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

    /**
     * Скачивает отправленный пользователем файл в локальное хранилище
     *
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handle() : void
    {
        try{
            $sStorageLink = storage_path("app/public/").$this->userId.'/download_files';
            if(!is_dir(storage_path("app/public/").$this->userId)) mkdir(storage_path("app/public/").$this->userId);
            if(!is_dir($sStorageLink)) mkdir($sStorageLink);
            File::cleanDirectory($sStorageLink);

            $response = $this
                ->makeAction('getFile', $this->args)
                ->throw()
                ->json();
            $sFilePath = $response['result']['file_path'];
            $sDownloadLink = 'https://api.telegram.org/file/bot' . env('TELEGRAM_BOT_TOKEN').'/'.$sFilePath;
            $sFile = file_get_contents($sDownloadLink);
            file_put_contents($sStorageLink.'/'.basename($sFilePath), $sFile);
        } catch (Throwable $exception){
            Log::debug($exception->getMessage());
        }
    }
}
