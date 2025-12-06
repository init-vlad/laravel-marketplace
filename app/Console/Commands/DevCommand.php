<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class DevCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Development command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = env('OCTANE_HOST', '0.0.0.0');
        $port = env('OCTANE_PORT', 8000);

        $this->info("Starting Octane (FrankenPHP) on {$host}:{$port} with --watch");

        $command = [
            'php',
            'artisan',
            'octane:start',
            '--server=frankenphp',
            "--host={$host}",
            "--port={$port}",
            '--watch',
        ];

        Process::forever()->run($command);

        return Command::SUCCESS;
    }
}
