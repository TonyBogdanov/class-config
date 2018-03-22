<?php

namespace ClassConfig;

use ClassConfig\Exceptions\MissingConfigException;

/**
 * Class Config
 * @package ClassConfig
 */
abstract class AbstractConfig
{
    /**
     * @var null|AbstractConfig
     */
    protected $___parent;

    /**
     * @var null|string
     */
    protected $___key;

    /**
     * AbstractConfig constructor.
     *
     * @param AbstractConfig|null $parent
     * @param string|null $key
     */
    public function __construct(AbstractConfig $parent = null, string $key = null)
    {
        $this->___parent = $parent;
        $this->___key = $key;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return property_exists($this, '__' . $key . '__');
    }

    /**
     * @param string $key
     * @return AbstractConfig
     * @throws MissingConfigException
     */
    public function depend(string $key): AbstractConfig
    {
        if (!isset($this->$key)) {
            $trail = [$key];

            $config = $this;
            while ($config->___parent) {
                $trail[] = $config->___key;
                $config = $config->___parent;
            }

            throw new MissingConfigException(array_reverse($trail));
        }
        return $this;
    }
}