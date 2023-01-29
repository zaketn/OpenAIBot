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

Route::post('/'.env('TELEGRAM_WEBHOOK_SLUG').'/webhook', [BotController::class, 'handleRequest'])->name('webhook');

Route::get('/set_webhook', function(BotActionsService $bot){
    $http = $bot->setWebhook();
    dd(json_decode($http->body()));
})->name('setWebhook');

Route::get('/webhook_info', function(BotActionsService $bot){
    $http = $bot->makeAction('getWebhookInfo');
    dd(json_decode($http->body()));
})->name('getWebhookInfo');

Route::get('/test', function(){

});
