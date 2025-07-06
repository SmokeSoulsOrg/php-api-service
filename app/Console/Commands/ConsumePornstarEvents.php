<?php

namespace App\Console\Commands;

use App\Jobs\SyncPornstarFromMessage;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumePornstarEvents extends Command
{
    protected $signature = 'consume:pornstar-events';
    protected $description = 'Consume pornstar metadata from RabbitMQ and store in the database';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $connection = new AMQPStreamConnection(
            config('services.rabbitmq.host'),
            config('services.rabbitmq.port'),
            config('services.rabbitmq.user'),
            config('services.rabbitmq.password')
        );

        $channel = $connection->channel();
        $queue = config('services.rabbitmq.pornstar_queue', 'pornstar-events');

        $channel->queue_declare($queue, false, true, false, false);

        $this->info(" [*] Waiting for messages on '{$queue}'...");

        $callback = function (AMQPMessage $msg) {
            $data = json_decode($msg->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('❌ Invalid JSON: ' . json_last_error_msg());
                $msg->nack();
                return;
            }

            try {
                (new SyncPornstarFromMessage($data))->handle();
                $id = $data['id'] ?? '???';
                $name = $data['name'] ?? 'Unknown';
                $this->info("✅ Synced pornstar: {$name} [ID {$id}]");
                $msg->ack();
            } catch (\Throwable $e) {
                $id = $data['id'] ?? '???';
                $name = $data['name'] ?? 'Unknown';
                $this->error("❌ Sync failed for {$name} [ID {$id}]: " . $e->getMessage());
                $msg->nack();
            }
        };

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }
}
