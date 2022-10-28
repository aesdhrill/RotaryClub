<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StaticExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('static', [$this, 'getStatic']),
        ];
    }

    public function getStatic($class, $function, $args = [])
    {
        if (class_exists($class) && method_exists($class, $function)) {
            return call_user_func_array(array($class, $function), $args);
        }

        return null;
    }
}
