<?php

namespace ClassConfig\Traits;

use ClassConfig\AbstractConfig;
use ClassConfig\ClassConfig;

/**
 * Trait ConfigTrait
 * @package ClassConfig\Traits
 */
trait ConfigTrait
{
    /**
     * @var AbstractConfig
     */
    protected $__config__;

    /**
     * @return AbstractConfig
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function config(): AbstractConfig
    {
        if (!isset($this->__config__)) {
            $this->__config__ = ClassConfig::createInstance(get_class($this), $this);
        }
        return $this->__config__;
    }
}