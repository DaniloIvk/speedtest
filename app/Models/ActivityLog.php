<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]
class ActivityLog extends Model
{
    protected $table = 'activity_log';

    public function getDescriptionAttribute(): string|null
    {
        if (! array_key_exists('description', $this->attributes)) {
            return null;
        }

        return __("activity.descriptions.{$this->attributes['description']}");
    }
}
