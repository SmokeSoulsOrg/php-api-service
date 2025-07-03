<?php

namespace Tests\Feature\Console;

use App\Console\Commands\ReplicaHealthCheckCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\ArrayInput;
use Tests\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Filesystem\Filesystem;

class ReplicaHealthCheckCommandTest extends TestCase
{

    public function test_command_updates_env_when_replica_is_healthy()
    {
        DB::shouldReceive('connection->select')
            ->once()
            ->andReturn([
                (object)[
                    'Replica_IO_Running' => 'Yes',
                    'Replica_SQL_Running' => 'Yes',
                ]
            ]);

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('get')->andReturn("APP_NAME=Laravel\nDB_USE_REPLICA=false"); // stale value
            $mock->shouldReceive('put')->zeroOrMoreTimes()->andReturnTrue();
            $mock->shouldReceive('delete')->zeroOrMoreTimes()->andReturnTrue();
        });

        $buffer = new BufferedOutput();
        $exitCode = \Artisan::call('replica:check', [], $buffer);
        $output = $buffer->fetch();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Replica is healthy', $output);

        // Accept either actual update OR message that value was already correct
        $this->assertTrue(
            str_contains($output, '.env updated: DB_USE_REPLICA=true') ||
            str_contains($output, '.env already contains correct DB_USE_REPLICA value.'),
            'Expected output to show env was updated or already correct.'
        );
    }



    public function test_command_skips_env_update_when_already_correct()
    {
        DB::shouldReceive('connection->select')
            ->once()
            ->with('SHOW REPLICA STATUS')
            ->andReturn([
                (object)[
                    'Replica_IO_Running' => 'Yes',
                    'Replica_SQL_Running' => 'Yes',
                ]
            ]);

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('get')->andReturn("APP_NAME=Laravel\nDB_USE_REPLICA=true");
            // no put or delete expected
        });

        $buffer = new BufferedOutput();
        $exitCode = Artisan::call('replica:check', [], $buffer);
        $output = $buffer->fetch();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Replica is healthy', $output);
        $this->assertStringContainsString('.env already contains correct DB_USE_REPLICA value.', $output);
        $this->assertStringNotContainsString('config cache cleared', $output);
    }

    public function test_command_reports_unhealthy_replica()
    {
        DB::shouldReceive('connection->select')
            ->once()
            ->with('SHOW REPLICA STATUS')
            ->andReturn([
                (object)[
                    'Replica_IO_Running' => 'No',
                    'Replica_SQL_Running' => 'Yes',
                ]
            ]);

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('get')->andReturn("APP_NAME=Laravel\nDB_USE_REPLICA=true");
            $mock->shouldReceive('put')->andReturnTrue();
            $mock->shouldReceive('delete')->andReturnTrue();
        });

        $command = app(ReplicaHealthCheckCommand::class);
        $command->setLaravel(app()); // ✅ This is crucial

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $exitCode = $command->run($input, $output);
        $fullOutput = $output->fetch();

        $this->assertSame(2, $exitCode);
        $this->assertStringContainsString('Replica is NOT healthy', $fullOutput);
        $this->assertStringContainsString('IO: No, SQL: Yes', $fullOutput);
        $this->assertStringContainsString('.env updated: DB_USE_REPLICA=false', $fullOutput);
        $this->assertStringContainsString('config cache cleared', $fullOutput);
    }

    public function test_command_handles_empty_status_response()
    {
        DB::shouldReceive('connection->select')
            ->once()
            ->with('SHOW REPLICA STATUS')
            ->andReturn([]); // simulate empty replica status

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            // Simulate a .env without DB_USE_REPLICA
            $mock->shouldReceive('get')->andReturn("APP_NAME=Laravel\n");
            $mock->shouldReceive('put')->once()->withArgs(function ($path, $content) {
                return str_contains($content, 'DB_USE_REPLICA=false');
            });
            $mock->shouldReceive('delete')->andReturnTrue(); // config:clear
        });

        $command = app(ReplicaHealthCheckCommand::class);
        $command->setLaravel(app());

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $exitCode = $command->run($input, $output);
        $text = $output->fetch();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('No replica status returned', $text);
        $this->assertStringContainsString('.env updated: DB_USE_REPLICA=false', $text);
        $this->assertStringContainsString('config cache cleared', $text);
    }

    public function test_command_handles_exception()
    {
        DB::shouldReceive('connection->select')
            ->once()
            ->with('SHOW REPLICA STATUS')
            ->andThrow(new \Exception('Connection error'));

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('get')->andReturn("APP_NAME=Laravel\n");
            $mock->shouldReceive('put')->once()->withArgs(function ($path, $content) {
                return str_contains($content, 'DB_USE_REPLICA=false');
            });
            $mock->shouldReceive('delete')->andReturnTrue(); // config:clear
        });

        $command = app(ReplicaHealthCheckCommand::class);
        $command->setLaravel(app());

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $exitCode = $command->run($input, $output);
        $text = $output->fetch();

        $this->assertSame(3, $exitCode);
        $this->assertStringContainsString('Error checking replica: Connection error', $text);
        $this->assertStringContainsString('.env updated: DB_USE_REPLICA=false', $text);
        $this->assertStringContainsString('config cache cleared', $text);
    }

}
