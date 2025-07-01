<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \App\Models\PornstarAlias create(array $attributes = [])
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate(int|null $perPage = null, array $columns = ['*'], string $pageName = 'page', int|null $page = null)
 * @property int $id
 * @property int $pornstar_id
 * @property string $alias
 */
class PornstarAlias extends Model
{
    use HasFactory;

    protected $fillable = ['pornstar_id', 'alias'];

    public function pornstar(): BelongsTo
    {
        return $this->belongsTo(Pornstar::class);
    }
}
