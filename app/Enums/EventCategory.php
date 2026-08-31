<?php

namespace App\Enums;

enum EventCategory: string
{
    case CAREER = 'career';
    case EDUCATION = 'education';
    case RELATIONSHIP = 'relationship';
    case HEALTH = 'health';
    case FINANCE = 'finance';
    case LOCATION = 'location';
    case PERSONAL = 'personal';
    case ACHIEVEMENT = 'achievement';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CAREER => 'Career',
            self::EDUCATION => 'Education',
            self::RELATIONSHIP => 'Relationship',
            self::HEALTH => 'Health',
            self::FINANCE => 'Finance',
            self::LOCATION => 'Location',
            self::PERSONAL => 'Personal',
            self::ACHIEVEMENT => 'Achievement',
            self::OTHER => 'Other',
        };
    }
}
