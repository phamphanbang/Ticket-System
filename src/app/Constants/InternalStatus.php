<?php

namespace App\Constants;

enum InternalStatus: string
{
    case NEW = 'new';
    case IN_ANALYSIS = 'in_analysis';
    case AWAITING_ESTIMATION_APPROVAL = 'awaiting_estimation_approval';
    case AWAITING_CLIENT_APPROVAL = 'awaiting_client_approval';
    case IN_PROGRESS = 'in_progress';
    case UNDER_REVIEW = 'under_review';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::IN_ANALYSIS => 'In Analysis',
            self::AWAITING_ESTIMATION_APPROVAL => 'Awaiting Estimation Approval',
            self::AWAITING_CLIENT_APPROVAL => 'Awaiting Client Approval',
            self::IN_PROGRESS => 'In Progress',
            self::UNDER_REVIEW => 'Under Review',
            self::COMPLETED => 'Completed',
            self::CLOSED => 'Closed'
        };
    }
}
