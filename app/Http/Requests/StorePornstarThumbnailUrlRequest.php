<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePornstarThumbnailUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'thumbnail_id' => 'required|exists:pornstar_thumbnails,id',
            'url' => 'required|url',
            'local_path' => 'nullable|string',
        ];
    }
}
