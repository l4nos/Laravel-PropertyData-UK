<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Enums;

enum FinishQuality: string
{
    case PREMIUM = 'premium';
    case MEDIUM = 'medium';
    case BASIC = 'basic';
}
