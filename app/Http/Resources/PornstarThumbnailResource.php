<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $pornstar_id
 */
class PornstarThumbnailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'pornstar_id' => $this->pornstar_id,
            'type' => $this->resource->type,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'urls' => PornstarThumbnailUrlResource::collection(
                $this->resource->relationLoaded('urls') ? $this->resource->urls : []
            ),
        ];
    }
}
