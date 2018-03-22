<?php

namespace ClassConfig\Exceptions;

/**
 * Class ClassConfigAlreadyRegisteredException
 * @package ClassConfig\Exceptions
 */
class ClassConfigAlreadyRegisteredException extends \RuntimeException
{
    /**
     * ClassConfigAlreadyRegisteredException constructor.
     */
    public function __construct()
    {
        parent::__construct('Class-config\'s environment is already registered.');
    }
}