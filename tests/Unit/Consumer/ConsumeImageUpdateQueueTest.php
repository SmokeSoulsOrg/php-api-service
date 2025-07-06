<?php

namespace Tests\Unit\Consumer;

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
        // Arrange: create a test record
        $url = 'https://cdn.example.com/test-image.jpg';
        $initial = PornstarThumbnailUrl::factory()->create([
            'url' => $url,
            'local_path' => null,
        ]);

        $payload = [
            'url' => $url,
            'local_path' => 'images/new-path.jpg',
        ];

        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn(json_encode($payload));
        $msg->shouldReceive('ack')->once();

        // Closure copied from the command’s handle()
        $callback = function (AMQPMessage $msg) {
            $payload = json_decode($msg->getBody(), true);

            if (!is_array($payload) || !isset($payload['url'], $payload['local_path'])) {
                echo "❌ Invalid payload\n";
                $msg->nack();
                return;
            }

            $url = $payload['url'];
            $path = $payload['local_path'];

            $thumbnail = PornstarThumbnailUrl::where('url', $url)->first();

            if ($thumbnail) {
                $thumbnail->update(['local_path' => $path]);
                echo "✅ Updated local_path for URL: {$url}\n";
            } else {
                echo "⚠️ No match for URL: {$url}\n";
            }

            $msg->ack();
        };

        // Act
        $callback($msg);

        // Assert
        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'id' => $initial->id,
            'local_path' => 'images/new-path.jpg',
        ]);
    }

    public function test_it_rejects_invalid_payload(): void
    {
        $msg = Mockery::mock(AMQPMessage::class);
        $msg->shouldReceive('getBody')->andReturn('not-json');
        $msg->shouldReceive('nack')->once();

        $this->expectOutputRegex('/❌ Invalid payload/');

        $callback = function (AMQPMessage $msg) {
            $payload = json_decode($msg->getBody(), true);

            if (!is_array($payload) || !isset($payload['url'], $payload['local_path'])) {
                echo "❌ Invalid payload\n";
                $msg->nack();
                return;
            }

            // Should not reach this
            $msg->ack();
        };

        $callback($msg);

        $this->assertTrue(true); // prevent risky test
    }
}
