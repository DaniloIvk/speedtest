<?php

namespace App\Enums\Activity;

use App\Models\User;

enum Causer: string
{
    case DEVICE = 'device';
    case USER = User::class;
}
