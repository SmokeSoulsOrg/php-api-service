<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePornstarThumbnailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pornstar_id' => 'required|exists:pornstars,id',
            'type' => 'required|string|in:pc,mobile,tablet',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',

            //Nested
            'urls' => ['array'],
            'urls.*.url' => ['required', 'url'],
            'urls.*.local_path' => ['nullable', 'string'],
        ];
    }
}
