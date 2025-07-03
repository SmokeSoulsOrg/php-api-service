<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdatePornstarRequest;
use App\Models\Pornstar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePornstarRequestTest extends TestCase
{
    use RefreshDatabase;
    private function validate(array $data, ?Pornstar $pornstar = null)
    {
        $request = new UpdatePornstarRequest();

        $route = new Route(['PUT'], '/fake/pornstars/{pornstar}', []);
        $route->bind($request);
        $route->setParameter('pornstar', $pornstar);

        $request->setRouteResolver(fn () => $route);

        return Validator::make($data, $request->rules());
    }


    public function test_passes_with_valid_update_data()
    {
        // Create one pornstar with a unique external_id
        $pornstar = Pornstar::factory()->create([
            'external_id' => 'ext-unique-' . uniqid(),
        ]);

        // Prepare valid update payload using the same external_id
        $payload = [
            'external_id' => $pornstar->external_id,
            'name' => 'Updated Name',
            'link' => 'https://example.com/profile',
        ];

        $validator = $this->validate($payload, $pornstar);

        $this->assertTrue($validator->passes());
    }



    public function test_fails_with_duplicate_external_id()
    {
        // First: create a pornstar that already uses 'ext-used'
        $existing = Pornstar::factory()->create([
            'external_id' => 'ext-used',
        ]);

        // Second: create a different pornstar with a different external_id
        $target = Pornstar::factory()->create([
            'external_id' => 'ext-unique-' . uniqid(),
        ]);

        // Now try to update the second one to use the same external_id as the first
        $payload = [
            'external_id' => 'ext-used', // this should now trigger a validation failure
            'name' => 'Should Fail',
            'link' => 'https://example.com/fail',
        ];

        $validator = $this->validate($payload, $target);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('external_id', $validator->errors()->toArray());
    }



    public function test_fails_with_missing_required_fields()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [];

        $validator = $this->validate($invalid, $pornstar);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('external_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('link', $validator->errors()->toArray());
    }

    public function test_passes_with_nested_aliases_and_thumbnails()
    {
        $pornstar = Pornstar::factory()->create();

        $valid = [
            'external_id' => $pornstar->external_id,
            'name' => 'Updated',
            'link' => 'https://example.com/updated',
            'aliases' => [
                ['alias' => 'New A'],
                ['alias' => 'New B'],
            ],
            'thumbnails' => [
                [
                    'type' => 'preview',
                    'width' => 600,
                    'height' => 400,
                    'urls' => [
                        ['url' => 'https://cdn.example.com/1.jpg'],
                        ['url' => 'https://cdn.example.com/2.jpg', 'local_path' => '/local/2.jpg']
                    ]
                ]
            ]
        ];

        $validator = $this->validate($valid, $pornstar);
        $this->assertTrue($validator->passes());
    }

    public function test_fails_with_invalid_nested_data()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'external_id' => $pornstar->external_id,
            'name' => 'X',
            'link' => 'https://example.com/x',
            'aliases' => [
                [] // missing alias
            ],
            'thumbnails' => [
                [
                    'type' => 'poster',
                    'width' => 600,
                    'height' => 400,
                    'urls' => [
                        ['url' => 'invalid-url'] // not a valid URL
                    ]
                ]
            ]
        ];

        $validator = $this->validate($invalid, $pornstar);
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('aliases.0.alias', $validator->errors()->toArray());
        $this->assertArrayHasKey('thumbnails.0.urls.0.url', $validator->errors()->toArray());
    }
}
