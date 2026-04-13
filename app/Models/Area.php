<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Unguarded]
class Area extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        parent::booted();

        static::created(function (self $area) {
            $area->path = isset($area->parent_id)
                ? "{$area->parent()->value('path')}{$area->id}/"
                : "/{$area->id}/";

            $area->save();
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            related: Area::class,
            foreignKey: 'parent_id',
            ownerKey: 'id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            related: Area::class,
            foreignKey: 'parent_id',
            localKey: 'id'
        );
    }

    public function devices(): HasMany
    {
        return $this->hasMany(
            related: Device::class,
            foreignKey: 'area_id',
            localKey: 'id'
        );
    }
}
