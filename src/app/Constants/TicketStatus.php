<?php

namespace App\Constants;

enum TicketStatus: int
{
    case New = 1;
    case InProgress = 2;
    case Resolved = 3;
    case Closed = 4;

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}
