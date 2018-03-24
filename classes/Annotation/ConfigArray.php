<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigArray
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigArray extends Config
{
    /**
     * @var \ClassConfig\Annotation\ConfigEntryTypeHintInterface
     */
    public $value;
}