<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetEnvExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_env', [$this, 'getEnv']),
        ];
    }

    public function getEnv($envName): string
    {
        return $_ENV[$envName];
    }
}
