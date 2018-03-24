<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigList
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigList extends Config
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