<?php

namespace ClassConfig;

/**
 * Class Config
 * @package ClassConfig
 */
abstract class AbstractConfig
{
    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return property_exists($this, '__' . $key . '__');
    }
}