<?php

namespace App\Constants;

enum TaskPhase: string
{
    case ESTIMATION = 'estimation';
    case EXECUTION = 'execution';

    public function label(): string
    {
        return match($this) {
            self::ESTIMATION => 'Estimation',
            self::EXECUTION => 'Execution'
        };
    }
}
