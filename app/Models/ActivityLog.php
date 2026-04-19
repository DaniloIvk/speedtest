<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;

//use MongoDB\Laravel\Eloquent\HybridRelations;
//use MongoDB\Laravel\Eloquent\Model;

#[Unguarded]
class ActivityLog extends Model implements ActivityContract
{
//    use HybridRelations;

    // Force this model to use the MongoDB connection
//    protected $connection = 'mongodb';

    protected $table = 'activity_log';

    /*
    |--------------------------------------------------------------------------
    | Spatie Requirements
    |--------------------------------------------------------------------------
    */

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function getProperty(string $propertyName, mixed $defaultValue = null): mixed
    {
        return data_get($this->properties, $propertyName, $defaultValue);
    }

    public function getDescriptionAttribute(): string|null
    {
        if (! isset($this->attributes['description'])) {
            return null;
        }

        return __("activity.descriptions.{$this->attributes['description']}");
    }

    public function changes(): Collection
    {
        return collect($this->attributes['attribute_changes'] ?? []);
    }
}
