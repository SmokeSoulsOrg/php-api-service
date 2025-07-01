<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePornstarThumbnailRequest extends FormRequest
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
        ];
    }
}
