<?php

use App\Http\Controllers\BotController;
use App\Services\BotActionsService;
use Illuminate\Support\Facades\Route;

Route::post('/'.env('TELEGRAM_WEBHOOK_SLUG').'/webhook', [BotController::class, 'handleRequest'])->name('webhook');

Route::get('/', function(BotActionsService $bot){
    \App\Models\BotUser::query()->delete();
//    dd(\App\Models\BotUser::all());
})->name('index');

Route::get('/test', [BotController::class, 'test']);
