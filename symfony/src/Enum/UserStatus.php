<?php

namespace App\Enum;

use ReflectionClass;

class UserStatus
{
    public const BLOCKED = -1;
    public const INACTIVE = 0;
    public const ACTIVE = 1;


    public static function getStatuses(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    public static function getStatusesTranslated(?string $prefix = 'user.statuses.'): array
    {
        $statuses = self::getStatuses();

        return array_combine(array_map(static fn($status) => $prefix.$status, array_keys($statuses)), $statuses);
    }

    public static function getStatusesFlipped(): array
    {
        return array_flip(self::getStatuses());
    }
}