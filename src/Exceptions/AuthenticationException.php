<?php

declare(strict_types=1);

namespace EZUnsub\Exceptions;

class AuthenticationException extends EZUnsubException
{
    public function __construct(string $message = 'Authentication required')
    {
        parent::__construct($message, 401);
    }
}
