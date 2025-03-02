<?php

namespace App\Exceptions;

use Exception;

class UserExistsException extends Exception
{

    protected $message = 'User already exists!';
}
