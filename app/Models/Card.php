<?php

namespace App\Models;

use App\Enums\CardTier;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Unguarded]
class Card extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'tier' => CardTier::class,
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function canAccess(Device $device): bool
    {
        return str_contains($device->area->path, "/{$this->id}/");
    }
}
