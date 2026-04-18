<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Game extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'genre',
        'image_url',
        'developer',
        'publisher',
        'release_date',
        'itad_id',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function storeListings(): HasMany
    {
        return $this->hasMany(GameStoreListing::class);
    }

    public function priceHistory(): HasManyThrough
    {
        return $this->hasManyThrough(GamePrice::class, GameStoreListing::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }
}
