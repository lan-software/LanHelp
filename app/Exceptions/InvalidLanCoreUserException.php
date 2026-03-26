<?php

namespace App\Exceptions;

use Exception;

class InvalidLanCoreUserException extends Exception
{
    public function __construct()
    {
        parent::__construct('LanCore returned an incomplete or invalid user.');
    }
}
