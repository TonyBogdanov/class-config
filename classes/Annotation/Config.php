<?php

namespace ClassConfig\Annotation;

/**
 * Class Config
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class Config implements ConfigEntryInterface
{
    /**
     * @var \ClassConfig\Annotation\ConfigEntryInterface[]
     */
    public $value;
}