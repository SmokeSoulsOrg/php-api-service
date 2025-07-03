<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;

class ReplicaHealthCheckCommand extends Command
{
    protected $signature = 'replica:check';
    protected $description = 'Check if the MySQL replica is available and update .env accordingly';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $status = DB::connection('mysql_read_direct')->select("SHOW REPLICA STATUS");

            if (empty($status)) {
                $this->error('❌ No replica status returned.');
                $this->updateEnv(false);
                return 1;
            }

            $row = (array) $status[0];

            if (
                $row['Replica_IO_Running'] === 'Yes' &&
                $row['Replica_SQL_Running'] === 'Yes'
            ) {
                $this->info('✅ Replica is healthy.');
                $this->updateEnv(true);
                return 0;
            } else {
                $this->error('❌ Replica is NOT healthy.');
                $this->line("IO: {$row['Replica_IO_Running']}, SQL: {$row['Replica_SQL_Running']}");
                $this->updateEnv(false);
                return 2;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error checking replica: ' . $e->getMessage());
            $this->updateEnv(false);
            return 3;
        }
    }

    protected function updateEnv(bool $useReplica): void
    {
        $envPath = base_path('.env');

        if (! $this->files->exists($envPath)) {
            $this->warn('⚠️ .env file not found.');
            return;
        }

        $content = $this->files->get($envPath);
        $desired = 'DB_USE_REPLICA=' . ($useReplica ? 'true' : 'false');

        if (preg_match('/^DB_USE_REPLICA=(.*)$/m', $content, $matches)) {
            $current = trim($matches[1]);

            if ($current === ($useReplica ? 'true' : 'false')) {
                $this->info('ℹ️  .env already contains correct DB_USE_REPLICA value.');
                return;
            }

            $content = preg_replace('/^DB_USE_REPLICA=.*/m', $desired, $content);
        } else {
            $content .= "\n$desired\n";
        }

        $this->files->put($envPath, $content);

        $this->info("🔧 .env updated: $desired");

        Artisan::call('config:clear');
        $this->info('🧹 Laravel config cache cleared.');
    }
}
