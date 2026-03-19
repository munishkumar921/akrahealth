<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:auto-clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clear all application caches (config, route, view, application, and permissions)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing application cache...');
        Artisan::call('cache:clear');
        $this->info('✓ Application cache cleared');

        $this->info('Clearing configuration cache...');
        Artisan::call('config:clear');
        $this->info('✓ Configuration cache cleared');

        $this->info('Clearing route cache...');
        Artisan::call('route:clear');
        $this->info('✓ Route cache cleared');

        $this->info('Clearing view cache...');
        Artisan::call('view:clear');
        $this->info('✓ View cache cleared');

        $this->info('Clearing permission cache...');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('✓ Permission cache cleared');

        $this->info('Clearing compiled class files...');
        Artisan::call('clear-compiled');
        $this->info('✓ Compiled files cleared');

        $this->newLine();
        $this->info('All caches cleared successfully!');

        return Command::SUCCESS;
    }
}
