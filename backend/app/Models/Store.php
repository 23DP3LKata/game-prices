<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'website_url',
        'itad_shop_id',
        'is_active',
        'sync_enabled',
        'priority',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sync_enabled' => 'boolean',
            'priority' => 'integer',
            'itad_shop_id' => 'integer',
        ];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(GameStoreListing::class);
    }
}
