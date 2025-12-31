<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prayer extends Model
{
    protected $fillable = ['title', 'content'];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'current_prayer_id');
    }
}
