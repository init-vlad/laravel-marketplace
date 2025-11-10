<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateAutocomplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-autocomplete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет IDE helper автодополнения';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Генерация IDE helper файлов...');

        $this->call('ide-helper:generate');
        $this->call('ide-helper:models', ['--write-mixin' => true]);

        $this->info('✅ Автодополнения успешно обновлены!');
    }
}
