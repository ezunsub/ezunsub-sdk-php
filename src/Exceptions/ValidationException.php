<?php

declare(strict_types=1);

namespace EZUnsub\Exceptions;

class ValidationException extends EZUnsubException
{
    public function __construct(string $message = 'Invalid request')
    {
        parent::__construct($message, 400);
    }
}
