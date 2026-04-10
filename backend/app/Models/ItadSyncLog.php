<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItadSyncLog extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'sync_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sync_type',
        'status',
        'command',
        'stores_total',
        'games_total',
        'output',
        'started_at',
        'finished_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stores_total' => 'integer',
            'games_total' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
