<?php 

namespace App\Constants;

enum TicketPriorities: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'low',
            self::MEDIUM => 'medium',
            self::HIGH => 'high'
        };
    }

    public static function list()
    {
        return [
            self::LOW,
            self::MEDIUM,
            self::HIGH
        ];
    }
}