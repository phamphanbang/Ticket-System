<?php

namespace App\Constants;

enum UserRoles: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'admin',
            self::STAFF => 'staff'
        };
    }
}
