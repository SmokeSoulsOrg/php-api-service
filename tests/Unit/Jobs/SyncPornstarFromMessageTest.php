<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncPornstarFromMessage;
use App\Models\Pornstar;
use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Throwable;

class SyncPornstarFromMessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Throwable
     */
    public function test_it_creates_pornstar_and_related_entities()
    {
        $payload = [
            'id' => 1234,
            'name' => 'Jane Doe',
            'link' => 'https://example.com/jane-doe', // ✅ now included
            'license' => 'CC',
            'aliases' => ['JD', 'J-Dizzle'],
            'thumbnails' => [
                [
                    'type' => 'main',
                    'width' => 640,
                    'height' => 480,
                    'urls' => [
                        'https://cdn.example.com/image1.jpg',
                        'https://cdn.example.com/image2.jpg',
                    ],
                ],
            ],
            'attributes' => [
                'hairColor' => 'Red',
                'ethnicity' => 'Asian',
                'tattoos' => true,
                'piercings' => false,
                'breastSize' => 36,
                'breastType' => 'C',
                'gender' => 'female',
                'orientation' => 'bisexual',
                'age' => 28,
                'stats' => [
                    'subscriptions' => 3000,
                    'monthlySearches' => 8000,
                    'views' => 222222,
                    'videosCount' => 45,
                    'premiumVideosCount' => 20,
                    'whiteLabelVideoCount' => 12,
                    'rank' => 321,
                    'rankPremium' => 332,
                    'rankWl' => 310,
                ],
            ]
        ];

        (new SyncPornstarFromMessage($payload))->handle();

        $this->assertDatabaseHas('pornstars', [
            'external_id' => 1234,
            'name' => 'Jane Doe',
            'age' => 28,
            'ethnicity' => 'Asian',
        ]);

        $this->assertDatabaseHas('pornstar_aliases', [
            'alias' => 'JD',
        ]);

        $this->assertDatabaseHas('pornstar_thumbnails', [
            'type' => 'main',
            'width' => 640,
            'height' => 480,
        ]);

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'url' => 'https://cdn.example.com/image1.jpg',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_it_preserves_existing_local_path()
    {
        $pornstar = Pornstar::factory()->create([
            'external_id' => 9999,
            'link' => 'https://example.com/original',
        ]);

        $thumbnail = PornstarThumbnail::factory()->create([
            'pornstar_id' => $pornstar->id,
            'type' => 'main',
            'width' => 100,
            'height' => 100,
        ]);

        PornstarThumbnailUrl::factory()->create([
            'thumbnail_id' => $thumbnail->id,
            'url' => 'https://static.example.com/keep.jpg',
            'local_path' => '/local/keep.jpg',
        ]);

        $payload = [
            'id' => 9999,
            'name' => 'Updated Jane',
            'link' => 'https://example.com/updated',
            'thumbnails' => [[
                'type' => 'main',
                'width' => 100,
                'height' => 100,
                'urls' => ['https://static.example.com/keep.jpg'],
            ]],
            'attributes' => ['stats' => []],
        ];

        (new SyncPornstarFromMessage($payload))->handle();

        $this->assertDatabaseHas('pornstars', [
            'external_id' => 9999,
            'name' => 'Updated Jane',
        ]);

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'url' => 'https://static.example.com/keep.jpg',
            'local_path' => '/local/keep.jpg', // ✅ should be preserved
        ]);
    }
}
