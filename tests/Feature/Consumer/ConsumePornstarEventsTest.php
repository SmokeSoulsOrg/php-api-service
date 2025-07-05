<?php

namespace Tests\Feature\Consumer;

use App\Jobs\SyncPornstarFromMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Throwable;

class ConsumePornstarEventsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Throwable
     */
    public function test_it_processes_a_valid_message()
    {
        $data = [
            'id' => 888,
            'name' => 'From RabbitMQ',
            'link' => 'https://example.com/from-rmq',
            'aliases' => ['Alias1'],
            'thumbnails' => [[
                'type' => 'preview',
                'width' => 300,
                'height' => 200,
                'urls' => ['https://cdn.example.com/image.jpg'],
            ]],
            'attributes' => [
                'ethnicity' => 'Latina',
                'stats' => []
            ]
        ];

        // Simulate what the RabbitMQ consumer would do
        $job = new SyncPornstarFromMessage($data);
        $job->handle();

        $this->assertDatabaseHas('pornstars', ['external_id' => 888]);
        $this->assertDatabaseHas('pornstar_aliases', ['alias' => 'Alias1']);
        $this->assertDatabaseHas('pornstar_thumbnail_urls', ['url' => 'https://cdn.example.com/image.jpg']);
    }
}
