<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigString
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigString implements ConfigEntryTypeHintInterface
{
    /**
     * @var string
     */
    public $default;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'string';
    }
}