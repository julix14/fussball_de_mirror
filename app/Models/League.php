<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class League extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'league_id';

    protected $fillable = [
        'league_id',
        'data'
    ];

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function teamKind(): BelongsTo
    {
        return $this->belongsTo(TeamKind::class);
    }

    public function gameClass(): BelongsTo
    {
        return $this->belongsTo(GameClass::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
