<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $thumbnail_id
 */
class PornstarThumbnailUrlResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'thumbnail_id' => $this->thumbnail_id,
            'url' => $this->resource->url,
            'local_path' => $this->resource->local_path,
        ];
    }
}
