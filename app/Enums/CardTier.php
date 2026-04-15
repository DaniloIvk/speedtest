<?php

namespace App\Enums;

enum CardTier: int
{
    case STANDARD = 1;
    case SUPERIOR = 2;
    case DELUXE = 3;
    case SUITE = 4;
    case AMBASSADOR = 5;
}
