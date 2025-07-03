<?php

namespace Feature\Api\v1\Controllers;

use App\Models\Pornstar;
use App\Models\PornstarThumbnail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PornstarControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_pornstars()
    {
        Pornstar::factory()->count(3)->hasAliases(1)->hasThumbnails(1)->create();

        $this->getJson('/api/v1/pornstars')
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('message', 'Pornstars retrieved successfully.')
                ->has('data')
                ->has('links')
                ->has('meta')
                ->has('errors')
            );
    }

    public function test_index_respects_per_page_query()
    {
        Pornstar::factory()->count(30)->create();

        $this->getJson('/api/v1/pornstars?per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_store_creates_pornstar_with_relations()
    {
        $payload = [
            'name' => 'Jane Doe',
            'external_id' => 'ext123',
            'link' => 'https://example.com/jane-doe',
            'aliases' => [
                ['alias' => 'JD'],
                ['alias' => 'Jay D.'],
            ],
            'thumbnails' => [
                [
                    'type' => 'portrait',
                    'width' => 500,
                    'height' => 750,
                    'urls' => [
                        ['url' => 'https://example.com/thumb1.jpg'],
                        ['url' => 'https://example.com/thumb2.jpg'],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/pornstars', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Pornstar created successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'external_id',
                    'link',
                    'aliases' => [
                        ['id', 'alias']
                    ],
                    'thumbnails' => [
                        [
                            'id',
                            'type',
                            'width',
                            'height',
                            'urls' => [
                                ['id', 'url']
                            ]
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pornstars', ['name' => 'Jane Doe']);
        $this->assertDatabaseHas('pornstar_aliases', ['alias' => 'JD']);
        $this->assertDatabaseHas('pornstar_thumbnail_urls', ['url' => 'https://example.com/thumb1.jpg']);
    }

    public function test_store_fails_with_missing_name()
    {
        $payload = ['aliases' => [['alias' => 'Alias']]];

        $this->postJson('/api/v1/pornstars', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'external_id', 'link']);
    }

    public function test_store_fails_with_invalid_url_format()
    {
        $payload = [
            'name' => 'John Doe',
            'external_id' => 'ext456',
            'link' => 'https://example.com/john',
            'thumbnails' => [
                [
                    'type' => 'landscape',
                    'width' => 640,
                    'height' => 480,
                    'urls' => [['url' => 'not-a-valid-url']]
                ]
            ]
        ];

        $this->postJson('/api/v1/pornstars', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['thumbnails.0.urls.0.url']);
    }

    public function test_show_returns_single_pornstar()
    {
        $pornstar = Pornstar::factory()
            ->hasAliases(2)
            ->hasThumbnails(
                PornstarThumbnail::factory()->count(1)->hasUrls(2)
            )
            ->create();


        $response = $this->getJson("/api/v1/pornstars/{$pornstar->id}");

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('message', 'Pornstar retrieved successfully.')
                ->has('data.id')
                ->has('data.name')
                ->has('data.aliases')
                ->has('data.thumbnails')
                ->etc()
            );
    }

    public function test_update_works_with_only_name()
    {
        $pornstar = Pornstar::factory()
            ->hasAliases(1)
            ->hasThumbnails(1)
            ->create();

        $payload = [
            'name' => 'Name Only',
            'external_id' => $pornstar->external_id,
            'link' => $pornstar->link,
        ];

        $this->putJson("/api/v1/pornstars/{$pornstar->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.name', 'Name Only');
    }


    public function test_update_modifies_pornstar_and_relations()
    {
        $pornstar = Pornstar::factory()
            ->hasAliases(1)
            ->hasThumbnails(
                PornstarThumbnail::factory()->count(1)->hasUrls(1)
            )
            ->create();


        $payload = [
            'name' => 'Updated Name',
            'external_id' => $pornstar->external_id,
            'link' => $pornstar->link,
            'aliases' => [
                ['alias' => 'New Alias']
            ],
            'thumbnails' => [
                [
                    'type' => 'updated',
                    'width' => 800,
                    'height' => 600,
                    'urls' => [
                        ['url' => 'https://example.com/updated.jpg']
                    ]
                ]
            ]
        ];

        $response = $this->putJson("/api/v1/pornstars/{$pornstar->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Pornstar updated successfully.',
                'data' => ['name' => 'Updated Name']
            ]);

        $this->assertDatabaseHas('pornstars', ['id' => $pornstar->id, 'name' => 'Updated Name']);
        $this->assertDatabaseHas('pornstar_aliases', ['alias' => 'New Alias']);
        $this->assertDatabaseHas('pornstar_thumbnails', ['type' => 'updated']);
        $this->assertDatabaseHas('pornstar_thumbnail_urls', ['url' => 'https://example.com/updated.jpg']);
    }

    public function test_destroy_deletes_pornstar()
    {
        $pornstar = Pornstar::factory()->create();

        $response = $this->deleteJson("/api/v1/pornstars/{$pornstar->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Pornstar deleted successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('pornstars', ['id' => $pornstar->id]);
    }

    public function test_destroy_cascades_to_relations()
    {
        $pornstar = Pornstar::factory()
            ->hasAliases(1)
            ->hasThumbnails(
                PornstarThumbnail::factory()
                    ->count(1)
                    ->hasUrls(1)
            )
            ->create();

        $this->deleteJson("/api/v1/pornstars/{$pornstar->id}")->assertOk();

        $this->assertDatabaseMissing('pornstars', ['id' => $pornstar->id]);
        $this->assertDatabaseCount('pornstar_aliases', 0);
        $this->assertDatabaseCount('pornstar_thumbnails', 0);
        $this->assertDatabaseCount('pornstar_thumbnail_urls', 0);
    }
}
