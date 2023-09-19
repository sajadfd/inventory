<?php

namespace App\Console\Commands;

use App\Models\GlobalOption;
use App\Services\GlobalOptionsSync;
use Illuminate\Console\Command;

class SyncGlobalOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(GlobalOptionsSync $globalOptionsSync)
    {
        $globalOptionsSync->run(command: $this);

        $this->newLine();
        $this->info('All Global Options are synced.');
    }
}
