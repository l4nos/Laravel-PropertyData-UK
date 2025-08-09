<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Enums;

enum ProjectType: string
{
    case REFURBISH = 'refurbish';
    case DEMOLITION = 'demolition';
}