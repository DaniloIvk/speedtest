<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

//use MongoDB\Laravel\Eloquent\Model;

#[Unguarded]
class Device extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
