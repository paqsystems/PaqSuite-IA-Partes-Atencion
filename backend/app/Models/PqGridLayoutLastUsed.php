<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqGridLayoutLastUsed extends Model
{
    protected $table = 'pq_grid_layout_last_used';

    protected $fillable = [
        'user_id',
        'proceso',
        'grid_id',
        'layout_id',
    ];

    public function layout(): BelongsTo
    {
        return $this->belongsTo(PqGridLayout::class, 'layout_id');
    }
}
