<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $pornstar_id
 */
class PornstarAliasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'pornstar_id' => $this->pornstar_id,
            'alias' => $this->resource->alias,
        ];
    }
}
