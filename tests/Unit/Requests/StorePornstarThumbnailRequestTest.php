<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StorePornstarThumbnailRequest;
use App\Models\Pornstar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePornstarThumbnailRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new StorePornstarThumbnailRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_passes_with_valid_data()
    {
        $pornstar = Pornstar::factory()->create();

        $valid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'pc',
            'width' => 800,
            'height' => 600,
            'urls' => [
                ['url' => 'https://cdn.example.com/thumb.jpg', 'local_path' => '/images/thumb.jpg'],
            ],
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_fails_with_missing_required_fields()
    {
        $invalid = [];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('pornstar_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
        $this->assertArrayHasKey('width', $validator->errors()->toArray());
        $this->assertArrayHasKey('height', $validator->errors()->toArray());
    }

    public function test_fails_with_invalid_type_value()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'desktop',
            'width' => 100,
            'height' => 100,
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_fails_with_negative_dimensions()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'mobile',
            'width' => 0,
            'height' => -1,
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('width', $validator->errors()->toArray());
        $this->assertArrayHasKey('height', $validator->errors()->toArray());
    }

    public function test_fails_with_invalid_url_in_nested_urls()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'tablet',
            'width' => 300,
            'height' => 400,
            'urls' => [
                ['url' => 'not-a-valid-url'],
            ],
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('urls.0.url', $validator->errors()->toArray());
    }
}
