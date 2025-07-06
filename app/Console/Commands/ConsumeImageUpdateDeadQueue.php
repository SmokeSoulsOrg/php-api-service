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
    protected $description = 'Re-consume the image-update-dead queue and retry setting local paths';

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
                $msg->ack(); // don't retry bad structure
                return;
            }

            $url = $payload['url'];
            $type = $payload['type'] ?? null;
            $path = $payload['local_path'];

            $thumbnail = PornstarThumbnailUrl::where('url', $url)
                ->whereHas('thumbnail', fn($q) => $q->where('type', $type))
                ->first();

            if ($thumbnail) {
                $thumbnail->update(['local_path' => $path]);
                $this->info("✅ Recovered: local_path set for {$url} [type: {$type}]");
                $msg->ack();
            } else {
                $this->warn("⚠️ Still missing: {$url} [type: {$type}] → will retry later");
                $msg->nack(true); // requeue
            }
        };

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }
}
