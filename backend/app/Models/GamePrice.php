<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePrice extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'game_store_listing_id',
        'price',
        'original_price',
        'discount_percent',
        'is_available',
        'is_on_sale',
        'recorded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percent' => 'integer',
            'is_available' => 'boolean',
            'is_on_sale' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function gameStoreListing(): BelongsTo
    {
        return $this->belongsTo(GameStoreListing::class);
    }
}
