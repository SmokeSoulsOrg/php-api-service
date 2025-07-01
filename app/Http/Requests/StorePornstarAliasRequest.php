<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePornstarAliasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pornstar_id' => 'required|exists:pornstars,id',
            'alias' => 'required|string|max:255',
        ];
    }
}
