<?php

namespace App\Enums;

enum Role: string
{
    case PARENT = 'parent';
    case RELATIVE = 'relative';
    case CHILD = 'child';

    public function label(): string
    {
        return match ($this) {
            self::PARENT => 'Parent',
            self::RELATIVE => 'Relative',
            self::CHILD => 'Child',
        };
    }
}
