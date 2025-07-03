<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StorePornstarRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePornstarRequestTest extends TestCase
{
    private function validate(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $request = new StorePornstarRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_passes_with_minimal_valid_data()
    {
        $valid = [
            'external_id' => 'ext-001',
            'name' => 'Jane Doe',
            'link' => 'https://example.com',
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_fails_with_missing_required_fields()
    {
        $invalid = [];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('external_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('link', $validator->errors()->toArray());
    }

    public function test_fails_with_invalid_url()
    {
        $invalid = [
            'external_id' => 'ext-002',
            'name' => 'Jane',
            'link' => 'not-a-url',
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('link', $validator->errors()->toArray());
    }

    public function test_passes_with_valid_nested_aliases_and_thumbnails()
    {
        $valid = [
            'external_id' => 'ext-003',
            'name' => 'Jane D.',
            'link' => 'https://example.com/jane',
            'aliases' => [
                ['alias' => 'JD'],
                ['alias' => 'J.D.']
            ],
            'thumbnails' => [
                [
                    'type' => 'portrait',
                    'width' => 640,
                    'height' => 480,
                    'urls' => [
                        ['url' => 'https://cdn.example.com/1.jpg', 'local_path' => '/path/1.jpg'],
                        ['url' => 'https://cdn.example.com/2.jpg']
                    ]
                ]
            ]
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_fails_with_missing_alias_or_url_fields()
    {
        $invalid = [
            'external_id' => 'ext-004',
            'name' => 'Jane X',
            'link' => 'https://example.com',
            'aliases' => [
                [], // missing 'alias'
            ],
            'thumbnails' => [
                [
                    'type' => 'poster',
                    'width' => 1280,
                    'height' => 720,
                    'urls' => [
                        ['url' => 'not-a-url'] // invalid URL
                    ]
                ]
            ]
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('aliases.0.alias', $validator->errors()->toArray());
        $this->assertArrayHasKey('thumbnails.0.urls.0.url', $validator->errors()->toArray());
    }
}
