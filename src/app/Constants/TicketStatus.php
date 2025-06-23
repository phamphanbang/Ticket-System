<?php

namespace App\Constants;

enum TicketStatus :string
{
    
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case COMPLETE = 'complete';
    case ARCHIVED = 'archived';

    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::IN_PROGRESS => 'In Progress',
            self::PENDING => 'Pending',
            self::ASSIGNED => 'Assigned',
            self::COMPLETE => 'Complete',
            self::ARCHIVED => 'Archived',
        };
    }

    public static function all(): array
    {
        return [
            self::NEW->value,
            self::IN_PROGRESS->value,
            self::PENDING->value,
            self::ASSIGNED->value,
            self::COMPLETE->value,
            self::ARCHIVED->value,
        ];
    }

}
