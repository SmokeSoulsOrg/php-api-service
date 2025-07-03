<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdatePornstarThumbnailRequest;
use App\Models\Pornstar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePornstarThumbnailRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new UpdatePornstarThumbnailRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_passes_with_valid_data()
    {
        $pornstar = Pornstar::factory()->create();

        $valid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'mobile',
            'width' => 480,
            'height' => 800,
            'urls' => [
                ['url' => 'https://cdn.example.com/mobile.jpg', 'local_path' => '/images/mobile.jpg'],
                ['url' => 'https://cdn.example.com/alt.jpg'],
            ],
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_fails_if_required_fields_missing()
    {
        $invalid = [];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('pornstar_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
        $this->assertArrayHasKey('width', $validator->errors()->toArray());
        $this->assertArrayHasKey('height', $validator->errors()->toArray());
    }

    public function test_fails_with_invalid_type()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'wide',
            'width' => 300,
            'height' => 300,
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_fails_with_non_positive_dimensions()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'pc',
            'width' => 0,
            'height' => -100,
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('width', $validator->errors()->toArray());
        $this->assertArrayHasKey('height', $validator->errors()->toArray());
    }

    public function test_fails_with_invalid_nested_url()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
            'type' => 'tablet',
            'width' => 300,
            'height' => 500,
            'urls' => [
                ['url' => 'invalid-url'],
            ],
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('urls.0.url', $validator->errors()->toArray());
    }
}
