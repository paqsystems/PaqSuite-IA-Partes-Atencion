<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPivotsConfig extends Model
{
    protected $table = 'pq_pivots_config';

    protected $fillable = [
        'consulta_id',
        'layout_name',
        'state_json',
        'created_by_user_id',
    ];

    protected $casts = [
        'created_by_user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
