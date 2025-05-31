<?php

namespace App\Constants;

enum UserRoles: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case LEADER = 'leader';
    case SUPPORTER = 'supporter';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'admin',
            self::STAFF => 'staff',
            self::LEADER => 'leader',
            self::SUPPORTER => 'supporter'
        };
    }
}
