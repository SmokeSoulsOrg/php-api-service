<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdatePornstarAliasRequest;
use App\Models\Pornstar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePornstarAliasRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new UpdatePornstarAliasRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_passes_with_valid_data()
    {
        $pornstar = Pornstar::factory()->create();

        $valid = [
            'pornstar_id' => $pornstar->id,
            'alias' => 'Updated Alias',
        ];

        $this->assertTrue($this->validate($valid)->passes());
    }

    public function test_fails_if_pornstar_id_is_missing()
    {
        $invalid = [
            'alias' => 'Alias Only',
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('pornstar_id', $validator->errors()->toArray());
    }

    public function test_fails_if_pornstar_id_does_not_exist()
    {
        $invalid = [
            'pornstar_id' => 9999, // non-existent
            'alias' => 'Invalid Ref',
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('pornstar_id', $validator->errors()->toArray());
    }

    public function test_fails_if_alias_is_missing()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('alias', $validator->errors()->toArray());
    }

    public function test_fails_if_alias_exceeds_max_length()
    {
        $pornstar = Pornstar::factory()->create();

        $invalid = [
            'pornstar_id' => $pornstar->id,
            'alias' => str_repeat('A', 256),
        ];

        $validator = $this->validate($invalid);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('alias', $validator->errors()->toArray());
    }
}
