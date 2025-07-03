<?php

namespace Feature\Api\v1\Controllers;

use App\Models\Pornstar;
use App\Models\PornstarThumbnail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PornstarThumbnailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_thumbnails()
    {
        PornstarThumbnail::factory()->count(5)->create();

        $this->getJson('/api/v1/pornstar-thumbnails')
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('message', 'Thumbnails retrieved successfully.')
                ->has('data')
                ->has('meta')
                ->has('links')
                ->etc()
            );
    }

    public function test_index_respects_per_page()
    {
        PornstarThumbnail::factory()->count(20)->create();

        $this->getJson('/api/v1/pornstar-thumbnails?per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_store_creates_thumbnail_with_urls()
    {
        $pornstar = Pornstar::factory()->create();

        $payload = [
            'pornstar_id' => $pornstar->id,
            'type' => 'pc',
            'width' => 800,
            'height' => 600,
            'urls' => [
                ['url' => 'https://cdn.example.com/thumb1.jpg'],
                ['url' => 'https://cdn.example.com/thumb2.jpg'],
            ],
        ];

        $response = $this->postJson('/api/v1/pornstar-thumbnails', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail created.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'width',
                    'height',
                    'pornstar_id',
                    'urls' => [
                        ['id', 'url']
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pornstar_thumbnails', [
            'type' => 'pc',
            'pornstar_id' => $pornstar->id,
        ]);

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'url' => 'https://cdn.example.com/thumb1.jpg',
        ]);
    }

    public function test_store_requires_required_fields()
    {
        $this->postJson('/api/v1/pornstar-thumbnails', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'width', 'height', 'pornstar_id']);
    }

    public function test_show_returns_thumbnail_with_urls()
    {
        $thumbnail = PornstarThumbnail::factory()
            ->hasUrls(2)
            ->create();

        $this->getJson("/api/v1/pornstar-thumbnails/{$thumbnail->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail retrieved successfully.',
            ])
            ->assertJsonPath('data.id', $thumbnail->id)
            ->assertJsonCount(2, 'data.urls');
    }

    public function test_update_modifies_thumbnail_and_urls()
    {
        $thumbnail = PornstarThumbnail::factory()
            ->hasUrls(2)
            ->create();

        $payload = [
            'type' => 'mobile',
            'width' => 400,
            'height' => 300,
            'pornstar_id' => $thumbnail->pornstar_id,
            'urls' => [
                ['url' => 'https://example.com/new1.jpg'],
                ['url' => 'https://example.com/new2.jpg'],
            ]
        ];

        $oldUrl = $thumbnail->urls->first()->url;

        $this->putJson("/api/v1/pornstar-thumbnails/{$thumbnail->id}", $payload)
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail updated successfully.',
            ])
            ->assertJsonPath('data.type', 'mobile')
            ->assertJsonCount(2, 'data.urls');

        $this->assertDatabaseHas('pornstar_thumbnail_urls', ['url' => 'https://example.com/new1.jpg']);
        $this->assertDatabaseMissing('pornstar_thumbnail_urls', ['url' => $oldUrl]);

    }

    public function test_destroy_deletes_thumbnail()
    {
        $thumbnail = PornstarThumbnail::factory()
            ->hasUrls(1)
            ->create();

        $this->deleteJson("/api/v1/pornstar-thumbnails/{$thumbnail->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail deleted successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('pornstar_thumbnails', ['id' => $thumbnail->id]);
        $this->assertDatabaseCount('pornstar_thumbnail_urls', 0);
    }
}
