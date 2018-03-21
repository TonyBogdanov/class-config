<?php

namespace ClassConfig\Annotation;

/**
 * Class ConfigBoolean
 * @package ClassConfig\Annotation
 *
 * @Annotation
 */
class ConfigBoolean implements ConfigEntryTypeHintInterface
{
    /**
     * @var bool
     */
    public $default;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'bool';
    }
}