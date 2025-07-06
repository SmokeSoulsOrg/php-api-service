<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PornstarThumbnailUrl create(array $attributes = [])
 * @method static LengthAwarePaginator paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
 * @method static Builder where(string $column, mixed $value)
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

