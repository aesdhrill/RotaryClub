<?php

namespace App\Enum;

use ReflectionClass;

class VoivodeshipType
{
    public const DOLNOSLASKIE = 1;
    public const KUJAWSKO_POMORSKIE = 2;
    public const LUBELSKIE = 3;
    public const LUBUSKIE = 4;
    public const LODZKIE = 5;
    public const MALOPOLSKIE = 6;
    public const MAZOWIECKIE = 7;
    public const OPOLSKIE = 8;
    public const PODKARPACKIE = 9;
    public const PODLASKIE = 10;
    public const POMORSKIE = 11;
    public const SLASKIE = 12;
    public const SWIETOKRZYSKIE = 13;
    public const WARMINSKO_MAZURSKIE = 14;
    public const WIELKOPOLSKIE = 15;
    public const ZACHODNIOPOMORSKIE = 16;

    public static function getValues(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    public static function getValuesTranslated(?string $prefix = 'day_of_week.'): array
    {
        $statuses = self::getValues();

        return array_combine(array_map(static fn($status) => $prefix.$status, array_keys($statuses)), $statuses);
    }
}