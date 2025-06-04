<?php

namespace App\Constants;

enum TicketStatus :string
{
    
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case WAITING = 'waiting';
    case ASSIGNED = 'assigned';
    case COMPLETE = 'complete';
    case FORCE_CLOSED = 'force_closed';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING => 'Waiting',
            self::ASSIGNED => 'Assigned',
            self::COMPLETE => 'Complete',
            self::FORCE_CLOSED => 'Force Closed',
        };
    }

    public static function all(): array
    {
        return [
            self::NEW,
            self::IN_PROGRESS,
            self::WAITING,
            self::ASSIGNED,
            self::COMPLETE,
            self::FORCE_CLOSED,
        ];
    }

}
