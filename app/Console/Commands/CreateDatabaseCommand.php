<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:database_sqlite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database.sqlite file in database directory';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        File::put(base_path().'/database/database.sqlite', '');

        return Command::SUCCESS;
    }
}
