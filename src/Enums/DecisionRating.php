<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Enums;

enum DecisionRating: string
{
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';
    case NEUTRAL = 'neutral';
}