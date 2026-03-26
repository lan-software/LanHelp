<?php

namespace App\Exceptions;

use Exception;

class LanCoreRequestException extends Exception
{
    public function __construct(string $message, public readonly int $statusCode = 0)
    {
        parent::__construct($message);
    }
}
