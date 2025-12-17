<?php

declare(strict_types=1);

namespace EZUnsub\Exceptions;

use Exception;

class EZUnsubException extends Exception
{
    protected ?int $statusCode;

    public function __construct(string $message = '', ?int $statusCode = null, ?\Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
