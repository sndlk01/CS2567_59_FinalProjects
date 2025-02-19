<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TDBMSyncService;

class SyncTDBMData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tdbm:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from TDBM API to local database';

    private $syncService;

    public function __construct(TDBMSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting TDBM data sync...');

        try {
            $this->syncService->syncAll();
            $this->info('TDBM data sync completed successfully');
        } catch (\Exception $e) {
            $this->error('TDBM sync failed: ' . $e->getMessage());
        }
    }
}
