<?php

use App\Http\Controllers\BotController;
use App\Services\BotActionsService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/set_webhook', function(BotActionsService $bot){
    $http = $bot->setWebhook();
    dd(json_decode($http->body()));
})->name('setWebhook');

Route::post('/'.env('TELEGRAM_WEBHOOK_SLUG').'/webhook', [BotController::class, 'handleRequest'])->name('webhook');
