<?php

namespace ClassConfig\Exceptions;

use ClassConfig\ClassConfig;

/**
 * Class ClassConfigNotRegisteredException
 * @package ClassConfig\Exceptions
 */
class ClassConfigNotRegisteredException extends \RuntimeException
{
    /**
     * ClassConfigNotRegisteredException constructor.
     */
    public function __construct()
    {
        parent::__construct(sprintf(
            'Class-config\'s environment is not registered, did you forget to call "%s"?',
            ClassConfig::class . '::register'
        ));
    }
}