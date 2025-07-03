<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ReplicaHealthCheckCommand extends Command
{
    protected $signature = 'replica:check';
    protected $description = 'Check if the MySQL replica is available and update .env accordingly';

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

        if (!file_exists($envPath)) {
            $this->warn('⚠️ .env file not found.');
            return;
        }

        $content = file_get_contents($envPath);

        if (preg_match('/^DB_USE_REPLICA=.*/m', $content)) {
            $content = preg_replace(
                '/^DB_USE_REPLICA=.*/m',
                'DB_USE_REPLICA=' . ($useReplica ? 'true' : 'false'),
                $content
            );
        } else {
            $content .= "\nDB_USE_REPLICA=" . ($useReplica ? 'true' : 'false') . "\n";
        }

        file_put_contents($envPath, $content);

        $this->info('🔧 .env updated: DB_USE_REPLICA=' . ($useReplica ? 'true' : 'false'));

        // Clear cached config to apply changes
        Artisan::call('config:clear');
        $this->info('🧹 Laravel config cache cleared.');
    }
}
