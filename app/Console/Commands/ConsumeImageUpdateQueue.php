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
    protected $description = 'Consume image-update queue and update local_path for all matching thumbnail URLs';

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

        // Declare only the dead-letter queue; producer sets DLX
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

            $thumbnails = PornstarThumbnailUrl::where('url', $url)->get();

            if ($thumbnails->isEmpty()) {
                $this->warn("⚠️ No matches for URL: {$url}");
                $msg->nack(false); // send to DLX
                return;
            }

            foreach ($thumbnails as $thumb) {
                $thumb->update(['local_path' => $path]);
            }

            $this->info("✅ Updated {$thumbnails->count()} entries for URL: {$url}");
            $msg->ack();
        };

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }
}
