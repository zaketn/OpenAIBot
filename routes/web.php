<?php

use App\Http\Controllers\BotController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Services\BotActionsService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('/telegram/get', [BotController::class, 'handleRequest'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->name('webhook');

Route::get('/', function(BotActionsService $bot){
    
})->name('index');

