<?php

namespace App\Constants;

enum ExecutionStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case CHANGE_REQUESTED = 'change_requested';
    case RE_ESTIMATION_PENDING = 're_estimation_pending';
    case DONE = 'done';

    public function label(): string
    {
        return match($this) {
            self::NOT_STARTED => 'Not Started',
            self::IN_PROGRESS => 'In Progress',
            self::BLOCKED => 'Blocked',
            self::CHANGE_REQUESTED => 'Change Requested',
            self::RE_ESTIMATION_PENDING => 'Re-estimation Pending',
            self::DONE => 'Done'
        };
    }
}
