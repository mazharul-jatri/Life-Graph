<?php

namespace App\Enums;

enum BenchmarkSource: string
{
    case WHO = 'WHO';
    case WORLD_BANK = 'WorldBank';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::WHO => 'World Health Organization',
            self::WORLD_BANK => 'World Bank',
            self::CUSTOM => 'Custom',
        };
    }
}
