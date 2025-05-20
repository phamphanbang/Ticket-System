<?php 

namespace App\Constants;

class TicketPriorities
{
    public const LOW = 1;
    public const MEDIUM = 2;
    public const HIGH = 3;

    public static function getAll(): array
    {
        return [
            self::LOW,
            self::MEDIUM,
            self::HIGH,
        ];
    }
}