<?php

namespace Tests\Unit\Providers;

use App\Models\Pornstar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ResponseMacroServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_macro_returns_standard_json_structure()
    {
        $raw = Response::api(
            data: ['foo' => 'bar'],
            success: true,
            message: 'Success!',
            status: 200,
            errors: []
        );

        $response = TestResponse::fromBaseResponse($raw);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Success!',
                'data' => ['foo' => 'bar'],
                'errors' => [],
            ]);
    }

    public function test_api_macro_formats_paginated_resource_correctly()
    {
        // Simulated paginated data
        $collection = new Collection([
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ]);

        $paginator = new LengthAwarePaginator(
            items: $collection,
            total: 10,
            perPage: 2,
            currentPage: 1,
            options: ['path' => '/']
        );

        // Anonymous resource collection
        $resource = new class($paginator) extends ResourceCollection {
            public function toArray($request)
            {
                return $this->collection->map(fn ($item) => [
                    'id' => $item['id'],
                    'name' => $item['name'],
                ]);
            }
        };

        $raw = Response::api(
            data: $resource,
            success: true,
            message: 'Paginated results.',
            status: 200,
            errors: []
        );

        $response = TestResponse::fromBaseResponse($raw);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Paginated results.',
                'errors' => [],
            ])
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
                'success',
                'message',
                'errors',
            ]);
    }
}
