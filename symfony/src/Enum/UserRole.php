<?php

namespace App\Enum;

use ReflectionClass;

class UserRole
{
    public const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    public const ROLE_ADMINISTRATION = 'ROLE_ADMINISTRATION';

    public static function getRoles(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    public static function getRolesWithTranslationPrefix(?string $prefix = 'user.roles.'): array
    {
        $roles = self::getRoles();

        return array_combine(array_map(static fn($title) => $prefix.$title, array_keys($roles)), $roles);
    }
}
