<?php

namespace App\Facades;


use App\Services\BotActionsService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static makeAction(string $name, array|null $args)
 *
 * @see BotActionsService
 */
class Bot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BotActionsService::class;
    }
}
