<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePornstarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_id' => 'required|string|unique:pornstars,external_id',
            'name' => 'required|string|max:255',
            'link' => 'required|url',
            'license' => 'nullable|string',
            'wl_status' => 'boolean',
            'hair_color' => 'nullable|string',
            'ethnicity' => 'nullable|string',
            'has_tattoos' => 'boolean',
            'has_piercings' => 'boolean',
            'breast_size' => 'nullable|integer',
            'breast_type' => 'nullable|string|max:4',
            'gender' => 'nullable|string|max:16',
            'orientation' => 'nullable|string|max:16',
            'age' => 'nullable|integer|min:18',
            'subscriptions' => 'nullable|integer',
            'monthly_searches' => 'nullable|integer',
            'views' => 'nullable|integer',
            'videos_count' => 'nullable|integer',
            'premium_videos_count' => 'nullable|integer',
            'white_label_video_count' => 'nullable|integer',
            'rank' => 'nullable|integer',
            'rank_premium' => 'nullable|integer',
            'rank_wl' => 'nullable|integer',

            // Nested
            'aliases' => ['array'],
            'aliases.*.alias' => ['required', 'string'],

            'thumbnails' => ['array'],
            'thumbnails.*.type' => ['required', 'string'],
            'thumbnails.*.width' => ['required', 'integer'],
            'thumbnails.*.height' => ['required', 'integer'],

            'thumbnails.*.urls' => ['array'],
            'thumbnails.*.urls.*.url' => ['required', 'url'],
            'thumbnails.*.urls.*.local_path' => ['nullable', 'string'],
        ];
    }
}
