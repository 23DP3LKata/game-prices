<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMinPrice extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'game_store_listing_id',
        'price',
        'original_price',
        'discount_percent',
        'is_on_sale',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percent' => 'integer',
            'is_on_sale' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo { return $this->belongsTo(Game::class); }
    public function gameStoreListing(): BelongsTo { return $this->belongsTo(GameStoreListing::class); }
}
