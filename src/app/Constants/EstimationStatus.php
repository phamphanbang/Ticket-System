<?php

namespace App\Constants;

enum EstimationStatus: string
{
    case ASSIGNED = 'assigned';
    case READY_FOR_REVIEW = 'ready_for_review';
    case NEEDS_REVISION = 'needs_revision';
    case FINALIZED = 'finalized';

    public function label(): string
    {
        return match($this) {
            self::ASSIGNED => 'Assigned',
            self::READY_FOR_REVIEW => 'Ready for Review',
            self::NEEDS_REVISION => 'Needs Revision',
            self::FINALIZED => 'Finalized'
        };
    }
}
