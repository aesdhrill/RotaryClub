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

    public static function getRolesForUserRegistration(): array
    {
        return array_map(fn ($rg) => array_intersect(self::getRoles(), $rg), [[
                self::ROLE_INVESTIGATOR,
                self::ROLE_ADMINISTRATION,
                self::ROLE_COORDINATOR,
        ]]);
    }

    public static function getRolesForUserRegistrationTranslated(?string $prefix = 'user.roles.'): array
    {
        $roles = self::getRolesForUserRegistration();

        return array_map(fn ($rg) => array_combine(array_map(static fn($title) => $prefix.$title, array_keys($rg)), $rg), $roles);
    }

}
