<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Release extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'published_at'
    ];

    /**
     * Get the repository that owns the release.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }
}
