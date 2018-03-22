<?php

namespace ClassConfig\Exceptions;

/**
 * Class MissingConfigException
 * @package ClassConfig\Exceptions
 */
class MissingConfigException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string[]
     */
    protected $trail;

    /**
     * MissingConfigException constructor.
     *
     * @param string[] $trail
     */
    public function __construct(array $trail)
    {
        $this->key = array_pop($trail);
        $this->trail = $trail;

        parent::__construct(sprintf(
            'Missing required config entry: "%s"%s.',
            $this->key,
            0 < count($this->trail) ? sprintf(' (%s)', implode('.', array_merge($this->trail, [$this->key]))) : ''
        ));
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string[]
     */
    public function getTrail(): array
    {
        return $this->trail;
    }
}