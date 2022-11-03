<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class EmailUnverifiedException extends AuthenticationException
{
}