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

    public function column_label(): string
    {
        return match ($this) {
            self::New => 'new',
            self::InProgress => 'in_progress',
            self::Resolved => 'resolved',
            self::Closed => 'closed',
        };
    }

    public static function list()
    {
        return [
            self::New,
            self::InProgress,
            self::Resolved,
            self::Closed,
        ];
    }
}
