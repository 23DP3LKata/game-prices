<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameStoreListing extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'game_id',
        'store_id',
        'external_game_id',
        'title_in_store',
        'external_url',
        'is_active',
        'is_available',
        'current_price',
        'original_price',
        'discount_percent',
        'is_on_sale',
        'last_synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'current_price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percent' => 'integer',
            'is_on_sale' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(GamePrice::class);
    }
}
