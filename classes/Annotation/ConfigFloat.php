<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigFloat
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigFloat implements ConfigEntryTypeHintInterface
{
    /**
     * @var float
     */
    public $default;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'float';
    }
}