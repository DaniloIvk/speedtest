<?php

namespace App\Enums\Activity;

enum Event: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case ACCESS = 'access';
    case INVALID_ACCESS = 'invalid_access';
}
