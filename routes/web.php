<?php

use App\Http\Controllers\BotController;
use App\Services\BotActionsService;
use Illuminate\Support\Facades\Route;

Route::post('/'.env('TELEGRAM_WEBHOOK_SLUG').'/webhook', [BotController::class, 'handleRequest'])->name('webhook');

Route::get('/', function(BotActionsService $bot){

})->name('index');

