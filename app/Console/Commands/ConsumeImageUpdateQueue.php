<?php

namespace App\Console\Commands;

use App\Models\PornstarThumbnailUrl;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumeImageUpdateQueue extends Command
{
    protected $signature = 'consume:image-update';
    protected $description = 'Consume image-update queue and update local_path for thumbnail URLs';

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
        $queue = config('services.rabbitmq.image_update_queue', 'image-update');

        // Only declare dead-letter queue here; main queue is declared in the producer
        $channel->queue_declare('image-update-dead', false, true, false, false);

        $this->info("🟢 Listening for messages on '{$queue}'");

        $callback = function (AMQPMessage $msg) {
            $payload = json_decode($msg->getBody(), true);

            if (!is_array($payload) || !isset($payload['url'], $payload['local_path'])) {
                $this->error('❌ Invalid payload');
                $msg->nack(false); // send to DLX
                return;
            }

            $url = $payload['url'];
            $path = $payload['local_path'];

            $thumbnail = PornstarThumbnailUrl::where('url', $url)->first();

            if ($thumbnail) {
                $thumbnail->update(['local_path' => $path]);
                $this->info("✅ Updated local_path for URL: {$url}");
                $msg->ack();
            } else {
                $this->warn("⚠️ No match for URL: {$url} → will be dead-lettered");
                $msg->nack(false); // send to DLX
            }
        };

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }
}
