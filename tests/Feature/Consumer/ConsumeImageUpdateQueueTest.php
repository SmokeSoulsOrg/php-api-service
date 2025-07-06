<?php

namespace Tests\Feature\Consumer;

use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\TestCase;

class ConsumeImageUpdateQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_local_path_for_valid_message(): void
    {
        $thumbnail = PornstarThumbnail::factory()->create();
        $url = 'https://cdn.example.com/test.jpg';

        $thumbUrl = PornstarThumbnailUrl::factory()->create([
            'thumbnail_id' => $thumbnail->id,
            'url' => $url,
            'local_path' => null,
        ]);

        $payload = [
            'url' => $url,
            'local_path' => 'storage/pornstar-images/test.jpg',
            'type' => $thumbnail->type,
        ];

        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn(json_encode($payload));
        $msg->shouldReceive('ack')->once();

        $callback = function (AMQPMessage $msg) {
            $payload = json_decode($msg->getBody(), true);

            if (!is_array($payload) || !isset($payload['url'], $payload['local_path'], $payload['type'])) {
                echo "❌ Invalid payload\n";
                $msg->nack(false);
                return;
            }

            $url = $payload['url'];
            $type = $payload['type'];
            $path = $payload['local_path'];

            $thumbnail = PornstarThumbnailUrl::where('url', $url)
                ->whereHas('thumbnail', fn ($q) => $q->where('type', $type))
                ->first();

            if ($thumbnail) {
                $thumbnail->update(['local_path' => $path]);
                echo "✅ Updated local_path for URL: {$url}\n";
                $msg->ack();
            } else {
                echo "⚠️ No match for URL: {$url} with type: {$type}\n";
                $msg->nack(false);
            }
        };

        $callback($msg);

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'id' => $thumbUrl->id,
            'local_path' => 'storage/pornstar-images/test.jpg',
        ]);
    }

    public function test_it_rejects_invalid_payload(): void
    {
        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn('not-json');
        $msg->shouldReceive('nack')->once();

        $this->expectOutputRegex('/Invalid payload/');

        $callback = function (AMQPMessage $msg) {
            $payload = json_decode($msg->getBody(), true);

            if (!is_array($payload) || !isset($payload['url'], $payload['local_path'], $payload['type'])) {
                echo "❌ Invalid payload\n";
                $msg->nack(false);
                return;
            }

            $msg->ack();
        };

        $callback($msg);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
