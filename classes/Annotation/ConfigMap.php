<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigMap
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigMap extends Config
{
    /**
     * @var \ClassConfig\Annotation\ConfigEntryTypeHintInterface
     */
    public $value;

    /**
     * @var array
     */
    public $default;
}