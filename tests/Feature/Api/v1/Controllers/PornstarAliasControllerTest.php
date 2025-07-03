<?php

namespace Feature\Api\v1\Controllers;

use App\Models\Pornstar;
use App\Models\PornstarAlias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PornstarAliasControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_aliases()
    {
        PornstarAlias::factory()->count(5)->create();

        $this->getJson('/api/v1/pornstar-aliases')
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                ->where('message', 'Aliases retrieved successfully.')
                ->has('data')
                ->has('meta')
                ->has('links')
                ->etc()
            );
    }

    public function test_index_respects_per_page()
    {
        PornstarAlias::factory()->count(20)->create();

        $this->getJson('/api/v1/pornstar-aliases?per_page=5')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonCount(5, 'data');
    }

    public function test_store_creates_alias()
    {
        $pornstar = Pornstar::factory()->create();

        $payload = [
            'alias' => 'AliasX',
            'pornstar_id' => $pornstar->id,
        ];

        $this->postJson('/api/v1/pornstar-aliases', $payload)
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Alias created successfully.',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'alias', 'pornstar_id']
            ]);

        $this->assertDatabaseHas('pornstar_aliases', [
            'alias' => 'AliasX',
            'pornstar_id' => $pornstar->id,
        ]);
    }

    public function test_store_requires_required_fields()
    {
        $this->postJson('/api/v1/pornstar-aliases', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['alias', 'pornstar_id']);
    }

    public function test_show_returns_alias()
    {
        $alias = PornstarAlias::factory()->create();

        $this->getJson("/api/v1/pornstar-aliases/{$alias->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Alias retrieved successfully.',
            ])
            ->assertJsonPath('data.id', $alias->id)
            ->assertJsonPath('data.alias', $alias->alias);
    }

    public function test_update_modifies_alias()
    {
        $alias = PornstarAlias::factory()->create();

        $payload = [
            'alias' => 'UpdatedAlias',
            'pornstar_id' => $alias->pornstar_id, // provide existing value
        ];

        $this->putJson("/api/v1/pornstar-aliases/{$alias->id}", $payload)
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Alias updated successfully.',
            ])
            ->assertJsonPath('data.alias', 'UpdatedAlias');

        $this->assertDatabaseHas('pornstar_aliases', [
            'id' => $alias->id,
            'alias' => 'UpdatedAlias',
        ]);
    }

    public function test_destroy_deletes_alias()
    {
        $alias = PornstarAlias::factory()->create();

        $this->deleteJson("/api/v1/pornstar-aliases/{$alias->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Alias deleted successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('pornstar_aliases', ['id' => $alias->id]);
    }
}
