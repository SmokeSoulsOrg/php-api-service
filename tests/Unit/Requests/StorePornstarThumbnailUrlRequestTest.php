<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StorePornstarThumbnailUrlRequest;
use App\Models\Pornstar;
use App\Models\PornstarThumbnail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePornstarThumbnailUrlRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new StorePornstarThumbnailUrlRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_passes_with_valid_data()
    {
        $thumbnail = PornstarThumbnail::factory()->for(Pornstar::factory())->create();

        $valid = [
            'thumbnail_id' => $thumbnail->id,
            'url' => 'https://cdn.example.com/image.jpg',
            'local_path' => '/images/image.jpg',
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_passes_without_local_path()
    {
        $thumbnail = PornstarThumbnail::factory()->for(Pornstar::factory())->create();

        $valid = [
            'thumbnail_id' => $thumbnail->id,
            'url' => 'https://cdn.example.com/image.jpg',
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_fails_with_missing_required_fields()
    {
        $validator = $this->validate([]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('thumbnail_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('url', $validator->errors()->toArray());
    }

    public function test_fails_with_nonexistent_thumbnail_id()
    {
        $invalid = [
            'thumbnail_id' => 99999, // non-existent
            'url' => 'https://cdn.example.com/broken.jpg',
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('thumbnail_id', $validator->errors()->toArray());
    }

    public function test_fails_with_invalid_url()
    {
        $thumbnail = PornstarThumbnail::factory()->for(Pornstar::factory())->create();

        $invalid = [
            'thumbnail_id' => $thumbnail->id,
            'url' => 'not-a-url',
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('url', $validator->errors()->toArray());
    }
}
