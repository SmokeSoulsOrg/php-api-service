<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \App\Models\PornstarThumbnail create(array $attributes = [])
 * @property int $id
 * @property int $pornstar_id
 * @property string $type
 * @property int $width
 * @property int $height
 */
class PornstarThumbnail extends Model
{
    use HasFactory;

    protected $fillable = ['pornstar_id', 'type', 'width', 'height'];

    public function pornstar(): BelongsTo
    {
        return $this->belongsTo(Pornstar::class);
    }

    public function urls(): HasMany
    {
        return $this->hasMany(PornstarThumbnailUrl::class, 'thumbnail_id');
    }
}

