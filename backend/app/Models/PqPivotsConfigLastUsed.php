<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPivotsConfigLastUsed extends Model
{
    protected $table = 'pq_pivots_config_last_used';

    protected $fillable = [
        'user_id',
        'consulta_id',
        'layout_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'layout_id' => 'integer',
    ];

    public function layout(): BelongsTo
    {
        return $this->belongsTo(PqPivotsConfig::class, 'layout_id');
    }
}
