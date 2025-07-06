<?php

namespace Tests\Feature\Consumer;

use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\TestCase;
use Mockery;

class ConsumeImageUpdateDeadQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function simulateCallback(AMQPMessage $msg): void
    {
        $payload = json_decode($msg->getBody(), true);

        if (!is_array($payload) || !isset($payload['url'], $payload['local_path'], $payload['type'])) {
            echo "❌ Invalid payload\n";
            $msg->ack();
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
            echo "✅ Recovered: local_path set for {$url}\n";
            $msg->ack();
        } else {
            echo "⚠️ Still missing: {$url}, will retry later\n";
            $msg->nack(true);
        }
    }

    public function test_it_updates_existing_thumbnail_url(): void
    {
        $thumbnail = PornstarThumbnail::factory()->create(['type' => 'pc']);
        $url = 'https://cdn.example.com/test.jpg';

        $thumbUrl = PornstarThumbnailUrl::factory()->create([
            'thumbnail_id' => $thumbnail->id,
            'url' => $url,
            'local_path' => null,
        ]);

        $payload = [
            'url' => $url,
            'local_path' => 'storage/pornstar-images/test.jpg',
            'type' => 'pc',
        ];

        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn(json_encode($payload));
        $msg->shouldReceive('ack')->once();

        $this->simulateCallback($msg);

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'id' => $thumbUrl->id,
            'local_path' => 'storage/pornstar-images/test.jpg',
        ]);
    }

    public function test_it_requeues_when_thumbnail_not_found(): void
    {
        $payload = [
            'url' => 'https://cdn.example.com/missing.jpg',
            'local_path' => 'storage/pornstar-images/missing.jpg',
            'type' => 'pc',
        ];

        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn(json_encode($payload));
        $msg->shouldReceive('nack')->with(true)->once();

        $this->expectOutputRegex('/Still missing/');

        $this->simulateCallback($msg);
    }

    public function test_it_drops_invalid_payload(): void
    {
        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn('invalid');
        $msg->shouldReceive('ack')->once();

        $this->expectOutputRegex('/Invalid payload/');

        $this->simulateCallback($msg);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
