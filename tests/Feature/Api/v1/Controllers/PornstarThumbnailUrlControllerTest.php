<?php

namespace Feature\Api\v1\Controllers;

use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PornstarThumbnailUrlControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_urls()
    {
        PornstarThumbnailUrl::factory()->count(5)->create();

        $this->getJson('/api/v1/pornstar-thumbnail-urls')
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('message', 'Thumbnail URLs retrieved successfully.')
                ->has('data')
                ->has('meta')
                ->has('links')
                ->etc()
            );
    }

    public function test_index_respects_per_page()
    {
        PornstarThumbnailUrl::factory()->count(20)->create();

        $this->getJson('/api/v1/pornstar-thumbnail-urls?per_page=7')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 7)
            ->assertJsonCount(7, 'data');
    }

    public function test_store_creates_thumbnail_url()
    {
        $thumbnail = PornstarThumbnail::factory()->create();

        $payload = [
            'thumbnail_id' => $thumbnail->id,
            'url' => 'https://cdn.example.com/thumb.jpg',
            'local_path' => '/thumbs/thumb.jpg',
        ];

        $this->postJson('/api/v1/pornstar-thumbnail-urls', $payload)
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail URL created successfully.',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'url', 'thumbnail_id', 'local_path']
            ]);

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'url' => 'https://cdn.example.com/thumb.jpg',
            'thumbnail_id' => $thumbnail->id,
        ]);
    }

    public function test_store_requires_required_fields()
    {
        $this->postJson('/api/v1/pornstar-thumbnail-urls', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url', 'thumbnail_id']);
    }

    public function test_show_returns_single_url()
    {
        $url = PornstarThumbnailUrl::factory()->create();

        $this->getJson("/api/v1/pornstar-thumbnail-urls/{$url->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail URL retrieved successfully.',
            ])
            ->assertJsonPath('data.id', $url->id)
            ->assertJsonPath('data.url', $url->url);
    }

    public function test_update_modifies_url()
    {
        $url = PornstarThumbnailUrl::factory()->create();

        $payload = [
            'url' => 'https://new.example.com/thumb.jpg',
            'thumbnail_id' => $url->thumbnail_id,
            'local_path' => '/updated/path.jpg',
        ];

        $this->putJson("/api/v1/pornstar-thumbnail-urls/{$url->id}", $payload)
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail URL updated successfully.',
            ])
            ->assertJsonPath('data.url', 'https://new.example.com/thumb.jpg');

        $this->assertDatabaseHas('pornstar_thumbnail_urls', [
            'id' => $url->id,
            'url' => 'https://new.example.com/thumb.jpg',
            'local_path' => '/updated/path.jpg',
        ]);
    }

    public function test_destroy_deletes_thumbnail_url()
    {
        $url = PornstarThumbnailUrl::factory()->create();

        $this->deleteJson("/api/v1/pornstar-thumbnail-urls/{$url->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Thumbnail URL deleted successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('pornstar_thumbnail_urls', ['id' => $url->id]);
    }
}
