<?php

namespace App\Constants;

enum ExternalStatus: string
{
    case RECEIVED = 'received';
    case PROCESSING = 'processing';
    case AWAITING_YOUR_APPROVAL = 'awaiting_your_approval';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::RECEIVED => 'Received',
            self::PROCESSING => 'Processing',
            self::AWAITING_YOUR_APPROVAL => 'Awaiting Your Approval',
            self::COMPLETED => 'Completed',
            self::CLOSED => 'Closed'
        };
    }
}
