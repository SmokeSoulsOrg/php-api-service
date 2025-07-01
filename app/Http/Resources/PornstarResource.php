<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PornstarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'external_id' => $this->resource->external_id,
            'name' => $this->resource->name,
            'link' => $this->resource->link,
            'license' => $this->resource->license,
            'wl_status' => $this->resource->wl_status,
            'attributes' => [
                'hair_color' => $this->resource->hair_color,
                'ethnicity' => $this->resource->ethnicity,
                'has_tattoos' => $this->resource->has_tattoos,
                'has_piercings' => $this->resource->has_piercings,
                'breast_size' => $this->resource->breast_size,
                'breast_type' => $this->resource->breast_type,
                'gender' => $this->resource->gender,
                'orientation' => $this->resource->orientation,
                'age' => $this->resource->age,
            ],
            'stats' => [
                'subscriptions' => $this->resource->subscriptions,
                'monthly_searches' => $this->resource->monthly_searches,
                'views' => $this->resource->views,
                'videos_count' => $this->resource->videos_count,
                'premium_videos_count' => $this->resource->premium_videos_count,
                'white_label_video_count' => $this->resource->white_label_video_count,
                'rank' => $this->resource->rank,
                'rank_premium' => $this->resource->rank_premium,
                'rank_wl' => $this->resource->rank_wl,
            ],
            'aliases' => PornstarAliasResource::collection($this->whenLoaded('aliases')),
            'thumbnails' => PornstarThumbnailResource::collection($this->whenLoaded('thumbnails')),
        ];
    }
}

