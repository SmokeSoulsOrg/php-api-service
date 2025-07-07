<?php

namespace App\Console\Commands;

use App\Models\PornstarThumbnailUrl;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumeImageUpdateDeadQueue extends Command
{
    protected $signature = 'consume:image-update-dead';
    protected $description = 'Re-consume the image-update-dead queue and retry setting local_path for all matching thumbnail URLs';

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
        $queue = 'image-update-dead';

        $channel->queue_declare($queue, false, true, false, false);
        $this->info("🟠 Reprocessing messages from DLQ: {$queue}");

        $callback = function (AMQPMessage $msg) {
            $payload = json_decode($msg->getBody(), true);

            if (!is_array($payload) || !isset($payload['url'], $payload['local_path'])) {
                $this->error('❌ Invalid payload, dropping.');
                $msg->ack(); // don't retry malformed message
                return;
            }

            $url = $payload['url'];
            $path = $payload['local_path'];

            $thumbnails = PornstarThumbnailUrl::where('url', $url)->get();

            if ($thumbnails->isEmpty()) {
                $this->warn("⚠️ Still missing: {$url}, will retry later");
                $msg->nack(true); // requeue
                return;
            }

            foreach ($thumbnails as $thumb) {
                $thumb->update(['local_path' => $path]);
            }

            $this->info("✅ Recovered: set local_path for {$thumbnails->count()} entries with URL: {$url}");
            $msg->ack();
        };

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }
}
