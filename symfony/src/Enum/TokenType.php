<?php

namespace App\Enum;

use ReflectionClass;

class TokenType
{
    public const ACTIVATE_ACCOUNT = 1;
    public const FORGOT_PASSWORD = 2;

    public static function getTokenTypes(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    public static function getTokenTypesForSettingPassword(): array
    {
        return [
            self::ACTIVATE_ACCOUNT,
            self::FORGOT_PASSWORD,
        ];
    }

    public static function isValidForSettingPassword(int $tokenType): bool
    {
        return in_array($tokenType, self::getTokenTypesForSettingPassword());
    }
}