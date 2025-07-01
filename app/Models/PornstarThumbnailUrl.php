<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \App\Models\PornstarThumbnailUrl create(array $attributes = [])
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
 * @property int $id
 * @property int $thumbnail_id
 * @property string $url
 * @property string|null $local_path
 */
class PornstarThumbnailUrl extends Model
{
    use HasFactory;

    protected $fillable = ['thumbnail_id', 'url', 'local_path'];

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(PornstarThumbnail::class, 'thumbnail_id');
    }
}

