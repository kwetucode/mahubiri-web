<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorySermon extends Model
{

    protected $fillable = ['name'];

    /**
     * Get all of the sermons for the CategorySermon
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class);
    }
}
