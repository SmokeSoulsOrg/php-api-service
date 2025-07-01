<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \App\Models\Pornstar create(array $attributes)
 */
class Pornstar extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'name', 'link', 'license', 'wl_status',
        'hair_color', 'ethnicity', 'has_tattoos', 'has_piercings', 'breast_size', 'breast_type',
        'gender', 'orientation', 'age', 'subscriptions', 'monthly_searches', 'views',
        'videos_count', 'premium_videos_count', 'white_label_video_count',
        'rank', 'rank_premium', 'rank_wl',
    ];

    protected $casts = [
        'has_tattoos' => 'boolean',
        'has_piercings' => 'boolean',
        'wl_status' => 'boolean',
        'breast_size' => 'integer',
        'age' => 'integer',
        'subscriptions' => 'integer',
        'monthly_searches' => 'integer',
        'views' => 'integer',
        'videos_count' => 'integer',
        'premium_videos_count' => 'integer',
        'white_label_video_count' => 'integer',
        'rank' => 'integer',
        'rank_premium' => 'integer',
        'rank_wl' => 'integer',
    ];

    public function aliases(): HasMany
    {
        return $this->hasMany(PornstarAlias::class);
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany(PornstarThumbnail::class);
    }
}
