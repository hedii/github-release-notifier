<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
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
     * Get the releases for the repository.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function releases(): HasMany
    {
        return $this->hasMany(Release::class);
    }

    /**
     * Get the repository full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->owner}/{$this->name}";
    }
}
