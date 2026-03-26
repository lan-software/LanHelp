<?php

namespace App\Exceptions;

use Exception;

class LanCoreDisabledException extends Exception
{
    public function __construct()
    {
        parent::__construct('LanCore integration is disabled.');
    }
}
