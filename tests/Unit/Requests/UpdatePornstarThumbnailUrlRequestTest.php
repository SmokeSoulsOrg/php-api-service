<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdatePornstarThumbnailUrlRequest;
use App\Models\Pornstar;
use App\Models\PornstarThumbnail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePornstarThumbnailUrlRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new UpdatePornstarThumbnailUrlRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_passes_with_valid_data()
    {
        $thumbnail = PornstarThumbnail::factory()->for(Pornstar::factory())->create();

        $valid = [
            'thumbnail_id' => $thumbnail->id,
            'url' => 'https://cdn.example.com/updated.jpg',
            'local_path' => '/images/updated.jpg',
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_passes_without_local_path()
    {
        $thumbnail = PornstarThumbnail::factory()->for(Pornstar::factory())->create();

        $valid = [
            'thumbnail_id' => $thumbnail->id,
            'url' => 'https://cdn.example.com/only-url.jpg',
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
            'thumbnail_id' => 99999,
            'url' => 'https://cdn.example.com/missing-thumb.jpg',
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('thumbnail_id', $validator->errors()->toArray());
    }

    public function test_fails_with_invalid_url_format()
    {
        $thumbnail = PornstarThumbnail::factory()->for(Pornstar::factory())->create();

        $invalid = [
            'thumbnail_id' => $thumbnail->id,
            'url' => 'invalid-url',
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('url', $validator->errors()->toArray());
    }
}
