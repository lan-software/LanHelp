<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Staff = 'staff';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Staff => 'Staff',
            self::Admin => 'Admin',
        };
    }
}
