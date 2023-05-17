<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'competition_id';

    protected $attributes = [
        'competition_id',
    ];

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    public function saison(): BelongsTo
    {
        return $this->belongsTo(Saison::class);
    }

    public function mandant(): BelongsTo
    {
        return $this->belongsTo(Mandant::class);
    }

    public function competitionType(): BelongsTo
    {
        return $this->belongsTo(CompetitionType::class);
    }

    public function league(): HasMany
    {
        return $this->hasMany(League::class);
    }



}
