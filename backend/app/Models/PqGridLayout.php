<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqGridLayout extends Model
{
    protected $table = 'pq_grid_layouts';

    protected $fillable = [
        'user_id',
        'proceso',
        'grid_id',
        'layout_name',
        'state_json',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
