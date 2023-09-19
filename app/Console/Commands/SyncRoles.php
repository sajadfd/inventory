<?php

namespace App\Console\Commands;

use App\Services\PermissionsRolesSync;
use Illuminate\Console\Command;

class SyncRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(PermissionsRolesSync $permissionsRolesSync)
    {
        $permissionsRolesSync->run(command: $this);

        $this->newLine();
        $this->info('All Roles & Permissions are synced.');
    }
}
