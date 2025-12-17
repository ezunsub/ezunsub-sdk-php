<?php

declare(strict_types=1);

namespace EZUnsub\Exceptions;

class NotFoundException extends EZUnsubException
{
    public function __construct(string $message = 'Resource not found')
    {
        parent::__construct($message, 404);
    }
}
