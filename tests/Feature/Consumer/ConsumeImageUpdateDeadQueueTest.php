<?php

namespace Tests\Feature\Consumer;

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

        if (!is_array($payload) || !isset($payload['url'], $payload['local_path'])) {
            echo "❌ Invalid payload\n";
            $msg->ack();
            return;
        }

        $url = $payload['url'];
        $path = $payload['local_path'];

        $thumbnail = PornstarThumbnailUrl::where('url', $url)->first();

        if ($thumbnail) {
            $thumbnail->update(['local_path' => $path]);
            echo "✅ Recovered: local_path set for {$url}\n";
        } else {
            echo "⚠️ Still missing: {$url}, will retry later\n";
            $msg->nack(true); // requeue
            return;
        }

        $msg->ack();
    }

    public function test_it_updates_existing_thumbnail_url(): void
    {
        $url = 'https://cdn.example.com/image.jpg';

        $record = PornstarThumbnailUrl::factory()->create([
            'url' => $url,
            'local_path' => null,
        ]);

        $payload = [
            'url' => $url,
            'local_path' => 'storage/pornstar-images/test.jpg',
        ];

        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn(json_encode($payload));
        $msg->shouldReceive('ack')->once();
        $msg->shouldNotReceive('nack');

        $this->simulateCallback($msg);

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'id' => $record->id,
            'local_path' => 'storage/pornstar-images/test.jpg',
        ]);
    }

    public function test_it_requeues_when_thumbnail_not_found(): void
    {
        $payload = [
            'url' => 'https://cdn.example.com/missing.jpg',
            'local_path' => 'storage/pornstar-images/missing.jpg',
        ];

        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn(json_encode($payload));
        $msg->shouldReceive('nack')->with(true)->once();
        $msg->shouldNotReceive('ack');

        $this->expectOutputRegex('/Still missing/');

        $this->simulateCallback($msg);
    }

    public function test_it_drops_invalid_payload(): void
    {
        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn('not-json');
        $msg->shouldReceive('ack')->once();
        $msg->shouldNotReceive('nack');

        $this->expectOutputRegex('/Invalid payload/');

        $this->simulateCallback($msg);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
